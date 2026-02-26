<?php

namespace App\Jobs;

use App\Helpers\NormalizationHelper;
use App\Models\Import;
use App\Models\Lead;
use App\Models\LeadActivity;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProcessImport implements ShouldQueue
{
    use Queueable;

    public $tries = 1;

    public $timeout = 600; // 10 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Import $import
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->import->update(['status' => 'processing']);

            $filePath = Storage::disk('local')->path($this->import->filename);

            if (! Storage::disk('local')->exists($this->import->filename)) {
                throw new \Exception('File not found: '.$filePath);
            }

            // Load Excel file
            $data = Excel::toArray([], $filePath);
            $rows = $data[0] ?? []; // First sheet

            if (empty($rows)) {
                throw new \Exception('Empty file or no data');
            }

            // First row = headers
            $headers = array_shift($rows);

            $this->import->update(['total_rows' => count($rows)]);

            $mapping = $this->import->mapping ?? [];

            // Auto-map if mapping is empty
            if (empty($mapping)) {
                $mapping = $this->autoMapHeaders($headers);
            }

            $deduplicationRules = $this->import->deduplication_rules ?? [];
            $tags = $this->import->tags ?? [];
            $operatorId = $this->import->assigned_operator_id;

            $imported = 0;
            $duplicated = 0;
            $errors = 0;
            $report = [];

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +2 because we start from 1 and removed header

                try {
                    $leadData = $this->mapRow($headers, $row, $mapping);

                    // Normalize data
                    if (isset($leadData['email'])) {
                        $leadData['email'] = NormalizationHelper::normalizeEmail($leadData['email']);
                    }

                    if (isset($leadData['full_name'])) {
                        $leadData['full_name'] = NormalizationHelper::capitalizeName($leadData['full_name']);
                    }

                    // Normalize phones and whatsapps
                    if (isset($leadData['phones']) && is_array($leadData['phones'])) {
                        $leadData['phones'] = array_filter(array_map(function ($phone) {
                            return NormalizationHelper::normalizePhone($phone);
                        }, $leadData['phones']));
                    }

                    if (isset($leadData['whatsapps']) && is_array($leadData['whatsapps'])) {
                        $leadData['whatsapps'] = array_filter(array_map(function ($whatsapp) {
                            return NormalizationHelper::normalizePhone($whatsapp);
                        }, $leadData['whatsapps']));
                    }

                    // Check deduplication
                    $existingLead = $this->checkDuplicate($leadData, $deduplicationRules);

                    if ($existingLead) {
                        $duplicated++;
                        $report[] = [
                            'row' => $rowNumber,
                            'type' => 'duplicate',
                            'lead_id' => $existingLead->id,
                            'message' => 'Lead already exists in system',
                        ];

                        continue;
                    }

                    // Add additional data
                    $leadData['tenant_id'] = $this->import->tenant_id;
                    $leadData['source'] = 'import';

                    if ($operatorId) {
                        $leadData['assigned_user_id'] = $operatorId;
                    }

                    if (! empty($tags)) {
                        $leadData['tags'] = array_merge($leadData['tags'] ?? [], $tags);
                    }

                    // Create lead
                    $lead = Lead::create($leadData);

                    // Register activity
                    LeadActivity::create([
                        'tenant_id' => $this->import->tenant_id,
                        'lead_id' => $lead->id,
                        'type' => 'created',
                        'description' => 'Lead created via import',
                        'metadata' => [
                            'import_id' => $this->import->id,
                            'row' => $rowNumber,
                        ],
                        'user_id' => $this->import->user_id,
                    ]);

                    $imported++;

                    $report[] = [
                        'row' => $rowNumber,
                        'type' => 'success',
                        'lead_id' => $lead->id,
                        'message' => 'Lead imported successfully',
                    ];
                } catch (\Exception $e) {
                    $errors++;
                    $report[] = [
                        'row' => $rowNumber,
                        'type' => 'error',
                        'message' => $e->getMessage(),
                    ];

                    Log::error('Error importing row', [
                        'import_id' => $this->import->id,
                        'row' => $rowNumber,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Update import with results
            $this->import->update([
                'status' => 'completed',
                'imported' => $imported,
                'duplicated' => $duplicated,
                'errors' => $errors,
                'report' => $report,
            ]);

            // Clean up file after processing
            if (Storage::disk('local')->exists($this->import->filename)) {
                Storage::disk('local')->delete($this->import->filename);
            }
        } catch (\Exception $e) {
            $this->import->update([
                'status' => 'failed',
                'report' => [
                    [
                        'type' => 'general_error',
                        'message' => $e->getMessage(),
                    ],
                ],
            ]);

            Log::error('Error processing import', [
                'import_id' => $this->import->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Map Excel row to Lead data
     */
    private function mapRow(array $headers, array $row, array $mapping): array
    {
        $leadData = [];

        foreach ($headers as $index => $header) {
            $value = $row[$index] ?? null;
            $targetField = $mapping[$header] ?? null;

            if (! $targetField || $targetField === 'ignore' || empty($value)) {
                continue;
            }

            $value = NormalizationHelper::cleanValue($value);

            if (empty($value)) {
                continue;
            }

            // If custom field
            if ($targetField === 'custom_field') {
                $leadData['custom_fields'][$header] = $value;

                continue;
            }

            // Special fields that are arrays (phones, whatsapps, tags)
            if (in_array($targetField, ['phones', 'whatsapps', 'tags'])) {
                if (! isset($leadData[$targetField])) {
                    $leadData[$targetField] = [];
                }
                $leadData[$targetField][] = $value;

                continue;
            }

            // Normal field
            $leadData[$targetField] = $value;
        }

        return $leadData;
    }

    /**
     * Check if lead already exists (deduplication)
     */
    private function checkDuplicate(array $leadData, array $rules): ?Lead
    {
        if (empty($rules)) {
            return null;
        }

        $query = Lead::query()
            ->where('tenant_id', $this->import->tenant_id)
            ->where(function ($query) use ($leadData, $rules) {
                foreach ($rules as $field) {
                    if ($field === 'email' && ! empty($leadData['email'])) {
                        $query->orWhere('email', $leadData['email']);
                    }

                    if ($field === 'phone' && ! empty($leadData['phones'])) {
                        foreach ($leadData['phones'] as $phone) {
                            $query->orWhereJsonContains('phones', $phone);
                        }
                    }

                    if ($field === 'whatsapp' && ! empty($leadData['whatsapps'])) {
                        foreach ($leadData['whatsapps'] as $whatsapp) {
                            $query->orWhereJsonContains('whatsapps', $whatsapp);
                        }
                    }
                }
            });

        return $query->first();
    }

    /**
     * Auto-map headers to Lead fields
     */
    private function autoMapHeaders(array $headers): array
    {
        $map = [
            'nome' => 'full_name',
            'name' => 'full_name',
            'nome completo' => 'full_name',
            'full name' => 'full_name',
            'email' => 'email',
            'e-mail' => 'email',
            'telefone' => 'phones',
            'phone' => 'phones',
            'celular' => 'phones',
            'mobile' => 'phones',
            'whatsapp' => 'whatsapps',
            'cidade' => 'city',
            'city' => 'city',
            'estado' => 'state',
            'state' => 'state',
            'pais' => 'country',
            'country' => 'country',
            'codigo postal' => 'postal_code',
            'cep' => 'postal_code',
            'postal code' => 'postal_code',
            'zip' => 'postal_code',
            'empresa' => 'company_name',
            'company' => 'company_name',
        ];

        $mapping = [];
        foreach ($headers as $header) {
            $normalized = strtolower(trim($header));
            $mapping[$header] = $map[$normalized] ?? 'ignore';
        }

        return $mapping;
    }
}
