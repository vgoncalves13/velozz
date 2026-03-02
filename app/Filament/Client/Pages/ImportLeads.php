<?php

namespace App\Filament\Client\Pages;

use App\Helpers\GoogleSheetsHelper;
use App\Jobs\ProcessImport;
use App\Models\Import;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
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

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('import_leads.navigation');
    }

    public function getTitle(): string
    {
        return __('import_leads.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.system');
    }

    public ?array $data = [];

    public ?array $preview = null;

    public ?array $headers = null;

    public ?array $suggestedMapping = null;

    /**
     * Process uploaded file and extract headers/data
     */
    protected function processFile($state, $set): void
    {
        // Get the real path from TemporaryUploadedFile object
        if (is_string($state)) {
            // State is already a path string
            $filePath = Storage::disk('local')->path($state);
        } else {
            // State is TemporaryUploadedFile object
            $filePath = $state->getRealPath();
        }

        if (! file_exists($filePath)) {
            throw new \Exception(__('import_leads.errors.unable_to_read'));
        }

        $data = Excel::toArray([], $filePath);
        $rows = $data[0] ?? [];

        if (empty($rows)) {
            throw new \Exception(__('import_leads.errors.no_data'));
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
        $set('total_rows', count($rows));

        Notification::make()
            ->title(__('import_leads.notifications.file_processed_title'))
            ->body(__('import_leads.notifications.file_processed_body', ['columns' => count($headers), 'rows' => count($rows)]))
            ->success()
            ->send();
    }

    /**
     * Fetch data from Google Sheets URL
     */
    protected function fetchGoogleSheets(string $url, $set): void
    {
        // Validate URL
        if (! GoogleSheetsHelper::isValidGoogleSheetsUrl($url)) {
            throw new \Exception(__('import_leads.errors.invalid_url'));
        }

        // Download as CSV
        $filename = GoogleSheetsHelper::downloadAsCsv($url, auth()->user()->tenant_id);

        // Get file path
        $filePath = Storage::disk('local')->path($filename);

        if (! file_exists($filePath)) {
            throw new \Exception(__('import_leads.errors.download_failed'));
        }

        // Process the downloaded CSV
        $data = Excel::toArray([], $filePath);
        $rows = $data[0] ?? [];

        if (empty($rows)) {
            // Clean up file
            Storage::disk('local')->delete($filename);
            throw new \Exception(__('import_leads.errors.sheets_no_data'));
        }

        // First row = headers
        $headers = array_shift($rows);

        // Get first 20 rows for preview
        $previewRows = array_slice($rows, 0, 20);

        // Auto-map headers
        $mapping = $this->autoMapHeaders($headers);

        // Set state for mapping step
        $set('file', $filename);
        $set('headers', $headers);
        $set('mapping', $mapping);
        $set('preview_rows', $previewRows);
        $set('total_rows', count($rows));
        $set('is_google_sheets', true);

        Notification::make()
            ->title(__('import_leads.notifications.sheets_fetched_title'))
            ->body(__('import_leads.notifications.sheets_fetched_body', ['columns' => count($headers), 'rows' => count($rows)]))
            ->success()
            ->send();
    }

    /**
     * Get available Lead fields for mapping
     */
    protected function getAvailableFields(): array
    {
        return [
            'ignore' => __('import_leads.fields.ignore'),
            'full_name' => __('import_leads.fields.full_name'),
            'email' => __('import_leads.fields.email'),
            'phones' => __('import_leads.fields.phones'),
            'whatsapps' => __('import_leads.fields.whatsapps'),
            'street_type' => __('import_leads.fields.street_type'),
            'street_name' => __('import_leads.fields.street_name'),
            'number' => __('import_leads.fields.number'),
            'complement' => __('import_leads.fields.complement'),
            'district' => __('import_leads.fields.district'),
            'neighborhood' => __('import_leads.fields.neighborhood'),
            'region' => __('import_leads.fields.region'),
            'city' => __('import_leads.fields.city'),
            'postal_code' => __('import_leads.fields.postal_code'),
            'country' => __('import_leads.fields.country'),
            'tags' => __('import_leads.fields.tags'),
            'notes' => __('import_leads.fields.notes'),
            'custom_field' => __('import_leads.fields.custom_field'),
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
                ->label(__('import_leads.actions.start_import'))
                ->icon('heroicon-o-arrow-up-tray')
                ->iconPosition(IconPosition::After)
                ->modalSubmitAction(false)
                ->schema([
                    Wizard::make([
                        Step::make(__('import_leads.steps.source'))
                            ->description(__('import_leads.steps.source_description'))
                            ->schema([
                                Radio::make('import_source')
                                    ->label(__('import_leads.source.label'))
                                    ->options([
                                        'file' => __('import_leads.source.file'),
                                        'google_sheets' => __('import_leads.source.google_sheets'),
                                    ])
                                    ->descriptions([
                                        'file' => __('import_leads.source.file_description'),
                                        'google_sheets' => __('import_leads.source.google_sheets_description'),
                                    ])
                                    ->default('file')
                                    ->required()
                                    ->live()
                                    ->columnSpanFull(),

                                FileUpload::make('file')
                                    ->label(__('import_leads.file.label'))
                                    ->required(fn ($get) => $get('import_source') === 'file')
                                    ->visible(fn ($get) => $get('import_source') === 'file')
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
                                    ->helperText(__('import_leads.file.helper'))
                                    ->afterStateUpdated(function ($state, $set) {
                                        if (! $state) {
                                            return;
                                        }

                                        try {
                                            $this->processFile($state, $set);
                                        } catch (\Exception $e) {
                                            Notification::make()
                                                ->title(__('import_leads.notifications.error_reading_file'))
                                                ->body($e->getMessage())
                                                ->danger()
                                                ->send();
                                        }
                                    }),

                                TextInput::make('google_sheets_url')
                                    ->label(__('import_leads.google_sheets.label'))
                                    ->required(fn ($get) => $get('import_source') === 'google_sheets')
                                    ->visible(fn ($get) => $get('import_source') === 'google_sheets')
                                    ->url()
                                    ->placeholder(__('import_leads.google_sheets.placeholder'))
                                    ->helperText(new HtmlString(
                                        '<strong>'.__('import_leads.google_sheets.how_to_title').'</strong><br>'.
                                        '1. '.__('import_leads.google_sheets.step_1').'<br>'.
                                        '2. '.__('import_leads.google_sheets.step_2').'<br>'.
                                        '3. '.__('import_leads.google_sheets.step_3').'<br>'.
                                        '4. '.__('import_leads.google_sheets.step_4')
                                    ))
                                    ->suffixAction(
                                        \Filament\Actions\Action::make('fetch')
                                            ->label(__('import_leads.actions.fetch_data'))
                                            ->icon('heroicon-o-arrow-down-tray')
                                            ->action(function ($get, $set) {
                                                $url = $get('google_sheets_url');

                                                if (! $url) {
                                                    Notification::make()
                                                        ->title(__('import_leads.notifications.url_required_title'))
                                                        ->body(__('import_leads.notifications.url_required_body'))
                                                        ->warning()
                                                        ->send();

                                                    return;
                                                }

                                                try {
                                                    $this->fetchGoogleSheets($url, $set);
                                                } catch (\Exception $e) {
                                                    Notification::make()
                                                        ->title(__('import_leads.notifications.error_fetching_sheets'))
                                                        ->body($e->getMessage())
                                                        ->danger()
                                                        ->send();
                                                }
                                            })
                                    ),
                            ]),

                        Step::make(__('import_leads.steps.mapping'))
                            ->description(__('import_leads.steps.mapping_description'))
                            ->schema(function ($get) {
                                $headers = $get('headers') ?? [];
                                $mapping = $get('mapping') ?? [];
                                $previewRows = $get('preview_rows') ?? [];

                                if (empty($headers)) {
                                    return [
                                        TextEntry::make('no_headers')
                                            ->label('')
                                            ->state(__('import_leads.mapping.no_file')),
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
                                $fields[] = Section::make(__('import_leads.mapping.section_title'))
                                    ->description(__('import_leads.mapping.section_description'))
                                    ->schema(function () use ($headers, $mapping) {
                                        $mappingFields = [];

                                        foreach ($headers as $header) {
                                            $mappingFields[] = Select::make("mapping.{$header}")
                                                ->label(__('import_leads.mapping.column_label', ['header' => $header]))
                                                ->options($this->getAvailableFields())
                                                ->default($mapping[$header] ?? 'ignore')
                                                ->searchable()
                                                ->helperText(__('import_leads.mapping.helper'));
                                        }

                                        return $mappingFields;
                                    })
                                    ->columns(2);

                                return $fields;
                            }),

                        Step::make(__('import_leads.steps.settings'))
                            ->description(__('import_leads.steps.settings_description'))
                            ->schema([
                                CheckboxList::make('deduplication_rules')
                                    ->label(__('import_leads.settings.deduplication_rules'))
                                    ->options([
                                        'email' => __('import_leads.settings.dedup_email'),
                                        'phone' => __('import_leads.settings.dedup_phone'),
                                        'whatsapp' => __('import_leads.settings.dedup_whatsapp'),
                                    ])
                                    ->descriptions([
                                        'email' => __('import_leads.settings.dedup_email_description'),
                                        'phone' => __('import_leads.settings.dedup_phone_description'),
                                        'whatsapp' => __('import_leads.settings.dedup_whatsapp_description'),
                                    ])
                                    ->default(['email'])
                                    ->columns(1),

                                Select::make('assigned_operator_id')
                                    ->label(__('import_leads.settings.assign_operator'))
                                    ->options(
                                        User::query()
                                            ->where('tenant_id', auth()->user()->tenant_id)
                                            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['operador', 'supervisor']))
                                            ->pluck('name', 'id')
                                    )
                                    ->searchable()
                                    ->nullable()
                                    ->helperText(__('import_leads.settings.assign_operator_helper')),

                                TagsInput::make('tags')
                                    ->label(__('import_leads.settings.tags_label'))
                                    ->placeholder(__('import_leads.settings.tags_placeholder'))
                                    ->helperText(__('import_leads.settings.tags_helper')),
                            ]),
                    ])->submitAction(new HtmlString(Blade::render(<<<'BLADE'
                        <x-filament::button
                            type="submit"
                            size="sm"
                            icon="heroicon-o-arrow-up-tray"
                        >
                            {{ __('import_leads.actions.start_import') }}
                        </x-filament::button>
                    BLADE))),
                ])
                ->action(function (array $data) {
                    $importSource = $data['import_source'] ?? 'file';

                    // Get file path
                    if ($importSource === 'google_sheets') {
                        // For Google Sheets, file was already downloaded
                        $filePath = $data['file'] ?? null;

                        if (! $filePath || ! Storage::disk('local')->exists($filePath)) {
                            Notification::make()
                                ->title(__('import_leads.notifications.error_title'))
                                ->body(__('import_leads.notifications.fetch_first_body'))
                                ->danger()
                                ->send();

                            return;
                        }

                        $type = 'url';
                    } else {
                        // For uploaded file
                        $file = $data['file'];
                        $filePath = is_string($file) ? $file : $file->getFilename();

                        // Ensure file exists in imports directory
                        if (! Storage::disk('local')->exists($filePath)) {
                            Notification::make()
                                ->title(__('import_leads.notifications.file_not_found_title'))
                                ->body(__('import_leads.notifications.file_not_found_body'))
                                ->danger()
                                ->send();

                            return;
                        }

                        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                        $type = $extension;
                    }

                    // Get mapping from form data
                    $mapping = $data['mapping'] ?? [];

                    $import = Import::create([
                        'tenant_id' => auth()->user()->tenant_id,
                        'user_id' => auth()->id(),
                        'filename' => $filePath,
                        'type' => $type,
                        'status' => 'pending',
                        'mapping' => $mapping,
                        'deduplication_rules' => $data['deduplication_rules'] ?? [],
                        'tags' => $data['tags'] ?? [],
                        'assigned_operator_id' => $data['assigned_operator_id'] ?? null,
                    ]);

                    ProcessImport::dispatch($import);

                    Notification::make()
                        ->title(__('import_leads.notifications.import_started_title'))
                        ->body(__('import_leads.notifications.import_started_body'))
                        ->success()
                        ->send();

                    $this->redirect(static::getUrl());
                }),
        ];
    }
}
