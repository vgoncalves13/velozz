<?php

return [
    // Page
    'title' => 'Importar Leads',
    'navigation' => 'Importar Leads',

    // Actions
    'actions' => [
        'start_import' => 'Iniciar Importação',
        'fetch_data' => 'Obter Dados',
    ],

    // Wizard Steps
    'steps' => [
        'source' => 'Origem',
        'source_description' => 'Escolher origem da importação',
        'mapping' => 'Mapeamento',
        'mapping_description' => 'Mapear colunas para campos de Lead',
        'settings' => 'Definições',
        'settings_description' => 'Configurar opções de importação',
    ],

    // Import Source
    'source' => [
        'label' => 'Origem da Importação',
        'file' => 'Carregar Ficheiro (.xlsx, .csv)',
        'file_description' => 'Carregar um ficheiro Excel ou CSV do seu computador',
        'google_sheets' => 'URL Google Sheets',
        'google_sheets_description' => 'Importar diretamente de uma folha Google publicada',
    ],

    // File Upload
    'file' => [
        'label' => 'Ficheiro',
        'helper' => 'Formatos aceites: .xlsx, .xls, .csv (máx 10MB)',
    ],

    // Google Sheets
    'google_sheets' => [
        'label' => 'URL Google Sheets',
        'placeholder' => 'https://docs.google.com/spreadsheets/d/...',
        'how_to_title' => 'Como tornar a sua folha pública:',
        'step_1' => 'Abra a sua Folha Google',
        'step_2' => 'Clique em "Ficheiro" → "Partilhar" → "Publicar na Web"',
        'step_3' => 'Escolha a folha e clique em "Publicar"',
        'step_4' => 'Copie o URL e cole-o aqui',
    ],

    // Mapping
    'mapping' => [
        'section_title' => 'Mapeamento de Colunas',
        'section_description' => 'Selecionar para que campo de Lead cada coluna deve mapear',
        'column_label' => 'Coluna: ":header"',
        'helper' => 'Selecionar para que campo de Lead esta coluna deve mapear',
        'no_file' => 'Por favor, carregue primeiro um ficheiro',
    ],

    // Available Fields
    'fields' => [
        'ignore' => '-- Ignorar esta coluna --',
        'full_name' => 'Nome Completo',
        'email' => 'E-mail',
        'phones' => 'Telefone',
        'whatsapps' => 'WhatsApp',
        'street_type' => 'Tipo de Rua',
        'street_name' => 'Nome da Rua',
        'number' => 'Número',
        'complement' => 'Complemento',
        'district' => 'Distrito',
        'neighborhood' => 'Bairro',
        'region' => 'Região',
        'city' => 'Cidade',
        'postal_code' => 'Código Postal',
        'country' => 'País',
        'tags' => 'Etiquetas',
        'notes' => 'Notas',
        'custom_field' => 'Campo Personalizado',
    ],

    // Settings
    'settings' => [
        'deduplication_rules' => 'Regras de Desduplicação',
        'dedup_email' => 'E-mail',
        'dedup_email_description' => 'Ignorar se lead com mesmo e-mail existir',
        'dedup_phone' => 'Telefone',
        'dedup_phone_description' => 'Ignorar se lead com mesmo telefone existir',
        'dedup_whatsapp' => 'WhatsApp',
        'dedup_whatsapp_description' => 'Ignorar se lead com mesmo WhatsApp existir',
        'assign_operator' => 'Atribuir ao operador',
        'assign_operator_helper' => 'Deixe vazio para não atribuir',
        'tags_label' => 'Etiquetas',
        'tags_placeholder' => 'Adicionar etiquetas',
        'tags_helper' => 'Serão adicionadas a todos os leads importados',
    ],

    // Notifications
    'notifications' => [
        'file_processed_title' => 'Ficheiro processado!',
        'file_processed_body' => 'Encontradas :columns colunas e :rows linhas. Prossiga para o passo de mapeamento.',
        'sheets_fetched_title' => 'Google Sheets obtido!',
        'sheets_fetched_body' => 'Encontradas :columns colunas e :rows linhas. Prossiga para o passo de mapeamento.',
        'import_started_title' => 'Importação iniciada!',
        'import_started_body' => 'Os seus leads estão a ser importados. Verifique o histórico abaixo.',
        'error_reading_file' => 'Erro ao ler ficheiro',
        'error_fetching_sheets' => 'Erro ao obter Google Sheets',
        'url_required_title' => 'URL obrigatório',
        'url_required_body' => 'Por favor, insira um URL Google Sheets',
        'error_title' => 'Erro',
        'fetch_first_body' => 'Por favor, obtenha primeiro os dados do Google Sheets.',
        'file_not_found_title' => 'Ficheiro não encontrado',
        'file_not_found_body' => 'O ficheiro carregado não pôde ser encontrado.',
    ],

    // Errors
    'errors' => [
        'unable_to_read' => 'Não foi possível ler o ficheiro carregado. Por favor, tente novamente.',
        'no_data' => 'O ficheiro carregado não tem dados',
        'invalid_url' => 'URL Google Sheets inválido. Por favor, verifique o URL e tente novamente.',
        'download_failed' => 'Falha ao descarregar Google Sheets. Certifique-se de que a folha está publicada como pública.',
        'sheets_no_data' => 'A Folha Google não tem dados',
    ],
];
