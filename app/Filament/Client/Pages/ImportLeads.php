<?php

namespace App\Filament\Client\Pages;

use App\Jobs\ProcessImport;
use App\Models\Import;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Maatwebsite\Excel\Facades\Excel;

class ImportLeads extends Page
{
    protected string $view = 'filament.client.pages.import-leads';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static ?string $navigationLabel = 'Import Leads';

    protected static ?string $title = 'Import Leads';

    protected static ?int $navigationSort = 2;

    public ?array $data = [];

    public ?array $preview = null;

    public ?array $headers = null;

    public ?array $suggestedMapping = null;

    /**
     * Get available Lead fields for mapping
     */
    protected function getAvailableFields(): array
    {
        return [
            'ignore' => '-- Ignore this column --',
            'full_name' => 'Full Name',
            'email' => 'Email',
            'phones' => 'Phone',
            'whatsapps' => 'WhatsApp',
            'street_type' => 'Street Type',
            'street_name' => 'Street Name',
            'number' => 'Number',
            'complement' => 'Complement',
            'district' => 'District',
            'neighborhood' => 'Neighborhood',
            'region' => 'Region',
            'city' => 'City',
            'postal_code' => 'Postal Code',
            'country' => 'Country',
            'tags' => 'Tags',
            'notes' => 'Notes',
            'custom_field' => 'Custom Field',
        ];
    }

    /**
     * Auto-map headers to Lead fields
     */
    protected function autoMapHeaders(array $headers): array
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
            'estado' => 'region',
            'state' => 'region',
            'pais' => 'country',
            'país' => 'country',
            'country' => 'country',
            'codigo postal' => 'postal_code',
            'código postal' => 'postal_code',
            'cep' => 'postal_code',
            'postal code' => 'postal_code',
            'zip' => 'postal_code',
            'empresa' => 'company_name',
            'company' => 'company_name',
            'endereco' => 'street_name',
            'endereço' => 'street_name',
            'address' => 'street_name',
            'rua' => 'street_name',
            'street' => 'street_name',
            'numero' => 'number',
            'número' => 'number',
            'number' => 'number',
            'complemento' => 'complement',
            'bairro' => 'district',
            'neighborhood' => 'neighborhood',
            'notas' => 'notes',
            'notes' => 'notes',
            'observacoes' => 'notes',
            'observações' => 'notes',
        ];

        $mapping = [];
        foreach ($headers as $header) {
            $normalized = strtolower(trim($header));
            $mapping[$header] = $map[$normalized] ?? 'ignore';
        }

        return $mapping;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Start Import')
                ->icon('heroicon-o-arrow-up-tray')
                ->iconPosition(IconPosition::After)
                ->modalSubmitAction(false)
                ->schema([
                    Wizard::make([
                        Step::make('Upload')
                            ->description('Upload Excel or CSV file')
                            ->schema([
                                FileUpload::make('file')
                                    ->label('File')
                                    ->required()
                                    ->acceptedFileTypes([
                                        'application/vnd.ms-excel',
                                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                        'text/csv',
                                        'text/plain',
                                    ])
                                    ->maxSize(10240)
                                    ->disk('local')
                                    ->directory('imports')
                                    ->preserveFilenames()
                                    ->helperText('Accepted formats: .xlsx, .xls, .csv (max 10MB)')
                                    ->afterStateUpdated(function ($state, $set) {
                                        if (! $state) {
                                            return;
                                        }

                                        try {
                                            // Get the real path from TemporaryUploadedFile object
                                            if (is_string($state)) {
                                                // State is already a path string
                                                $filePath = Storage::disk('local')->path($state);
                                            } else {
                                                // State is TemporaryUploadedFile object
                                                $filePath = $state->getRealPath();
                                            }

                                            if (! file_exists($filePath)) {
                                                Notification::make()
                                                    ->title('File not found')
                                                    ->body('Unable to read uploaded file. Please try again.')
                                                    ->danger()
                                                    ->send();

                                                return;
                                            }

                                            $data = Excel::toArray([], $filePath);
                                            $rows = $data[0] ?? [];

                                            if (empty($rows)) {
                                                Notification::make()
                                                    ->title('Empty file')
                                                    ->body('The uploaded file has no data')
                                                    ->warning()
                                                    ->send();

                                                return;
                                            }

                                            // First row = headers
                                            $headers = array_shift($rows);

                                            // Get first 20 rows for preview
                                            $previewRows = array_slice($rows, 0, 20);

                                            // Auto-map headers
                                            $mapping = $this->autoMapHeaders($headers);

                                            // Set state for mapping step
                                            $set('headers', $headers);
                                            $set('mapping', $mapping);
                                            $set('preview_rows', $previewRows);

                                            Notification::make()
                                                ->title('File processed!')
                                                ->body('Found '.count($headers).' columns and '.count($rows).' rows. Proceed to mapping step.')
                                                ->success()
                                                ->send();
                                        } catch (\Exception $e) {
                                            Notification::make()
                                                ->title('Error reading file')
                                                ->body($e->getMessage())
                                                ->danger()
                                                ->send();
                                        }
                                    }),
                            ]),

                        Step::make('Mapping')
                            ->description('Map columns to Lead fields')
                            ->schema(function ($get) {
                                $headers = $get('headers') ?? [];
                                $mapping = $get('mapping') ?? [];
                                $previewRows = $get('preview_rows') ?? [];

                                if (empty($headers)) {
                                    return [
                                        TextEntry::make('no_headers')
                                            ->label('')
                                            ->state('Please upload a file first'),
                                    ];
                                }

                                $fields = [];

                                // Add preview table
                                $fields[] = View::make('filament.components.import-preview')
                                    ->viewData([
                                        'headers' => $headers,
                                        'rows' => $previewRows,
                                    ]);

                                // Add mapping selects
                                $fields[] = Section::make('Column Mapping')
                                    ->description('Select which Lead field each column should map to')
                                    ->schema(function () use ($headers, $mapping) {
                                        $mappingFields = [];

                                        foreach ($headers as $header) {
                                            $mappingFields[] = Select::make("mapping.{$header}")
                                                ->label("Column: \"{$header}\"")
                                                ->options($this->getAvailableFields())
                                                ->default($mapping[$header] ?? 'ignore')
                                                ->searchable()
                                                ->helperText('Select which Lead field this column should map to');
                                        }

                                        return $mappingFields;
                                    })
                                    ->columns(2);

                                return $fields;
                            }),

                        Step::make('Settings')
                            ->description('Configure import options')
                            ->schema([
                                CheckboxList::make('deduplication_rules')
                                    ->label('Deduplication Rules')
                                    ->options([
                                        'email' => 'Email',
                                        'phone' => 'Phone',
                                        'whatsapp' => 'WhatsApp',
                                    ])
                                    ->descriptions([
                                        'email' => 'Skip if lead with same email exists',
                                        'phone' => 'Skip if lead with same phone exists',
                                        'whatsapp' => 'Skip if lead with same WhatsApp exists',
                                    ])
                                    ->default(['email'])
                                    ->columns(1),

                                Select::make('assigned_operator_id')
                                    ->label('Assign to operator')
                                    ->options(
                                        User::query()
                                            ->where('tenant_id', auth()->user()->tenant_id)
                                            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['operador', 'supervisor']))
                                            ->pluck('name', 'id')
                                    )
                                    ->searchable()
                                    ->nullable()
                                    ->helperText('Leave empty to not assign'),

                                TagsInput::make('tags')
                                    ->label('Tags')
                                    ->placeholder('Add tags')
                                    ->helperText('Will be added to all imported leads'),
                            ]),
                    ])->submitAction(new HtmlString(Blade::render(<<<'BLADE'
                        <x-filament::button
                            type="submit"
                            size="sm"
                            icon="heroicon-o-arrow-up-tray"
                        >
                            Start Import
                        </x-filament::button>
                    BLADE))),
                ])
                ->action(function (array $data) {
                    // Get file path (can be string or object)
                    $file = $data['file'];
                    $filePath = is_string($file) ? $file : $file->getFilename();

                    // Ensure file exists in imports directory
                    if (! Storage::disk('local')->exists($filePath)) {
                        Notification::make()
                            ->title('File not found')
                            ->body('The uploaded file could not be found.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $extension = pathinfo($filePath, PATHINFO_EXTENSION);

                    // Get mapping from form data
                    $mapping = $data['mapping'] ?? [];

                    $import = Import::create([
                        'tenant_id' => auth()->user()->tenant_id,
                        'user_id' => auth()->id(),
                        'filename' => $filePath,
                        'type' => $extension,
                        'status' => 'pending',
                        'mapping' => $mapping,
                        'deduplication_rules' => $data['deduplication_rules'] ?? [],
                        'tags' => $data['tags'] ?? [],
                        'assigned_operator_id' => $data['assigned_operator_id'] ?? null,
                    ]);

                    ProcessImport::dispatch($import);

                    Notification::make()
                        ->title('Import started!')
                        ->body('Your leads are being imported. Check history below.')
                        ->success()
                        ->send();

                    $this->redirect(static::getUrl());
                }),
        ];
    }
}
