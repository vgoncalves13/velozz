<?php

return [
    'sections' => [
        'basic_info' => 'Informações Básicas',
        'form_builder' => 'Construtor de Formulário',
        'appearance' => 'Aparência',
        'config' => 'Configuração',
        'position_appearance' => 'Posição e Aparência',
        'embed_code' => 'Código de Incorporação',
        'preview' => 'Pré-visualização',
    ],

    'labels' => [
        'name' => 'Nome',
        'slug' => 'Slug',
        'description' => 'Descrição',
        'active' => 'Ativo',
        'redirect_url' => 'URL de Redirecionamento',
        'fields' => 'Campos',
        'fields_count' => 'Campos',
        'field_type' => 'Tipo de Campo',
        'field_label' => 'Rótulo',
        'field_name' => 'Nome do Campo',
        'placeholder' => 'Placeholder',
        'required' => 'Obrigatório',
        'default_value' => 'Valor Padrão',
        'help_text' => 'Texto de Ajuda',
        'options' => 'Opções',
        'option_value' => 'Valor',
        'order' => 'Ordem',
        'validation_min' => 'Mínimo',
        'validation_max' => 'Máximo',
        'validation_regex' => 'Regex',
        'width' => 'Largura',
        'alignment' => 'Alinhamento',
        'input_size' => 'Tamanho do Input',
        'border_radius' => 'Arredondamento',
        'text_color' => 'Cor do Texto',
        'border_color' => 'Cor da Borda',
        'background_color' => 'Cor de Fundo',
        'font_size' => 'Tamanho da Fonte',
        'button_text' => 'Texto do Botão',
        'button_color' => 'Cor do Botão',
        'button_text_color' => 'Cor do Texto do Botão',
        'button_size' => 'Tamanho do Botão',
        'embed_script' => 'Script Tag (recomendado)',
        'embed_iframe' => 'iFrame Tag',
        'whatsapp_number' => 'Número WhatsApp',
        'auto_message' => 'Mensagem Automática',
        'position' => 'Posição',
        'animation' => 'Animação',
        'button_text_label' => 'Texto do Botão (opcional)',
        'show_text' => 'Mostrar Texto',
        'status' => 'Estado',
        'created' => 'Criado em',
    ],

    'helpers' => [
        'slug' => 'Usado no URL público do formulário. Apenas letras, números e hífens.',
        'redirect_url' => 'URL para redirecionar após submissão. Deixe vazio para mostrar mensagem de sucesso.',
        'field_name' => 'Identificador interno do campo. Use snake_case (ex: nome_completo).',
        'whatsapp_number' => 'Formato internacional (ex: +351912345678).',
        'auto_message' => 'Use {{nome}} como marcador para o nome do contacto.',
        'button_text' => 'Deixe vazio para mostrar apenas o ícone do WhatsApp.',
        'order' => 'Ordem em que os campos serão exibidos.',
    ],

    'field_types' => [
        'text' => 'Texto',
        'email' => 'Email',
        'phone' => 'Telefone',
        'number' => 'Número',
        'textarea' => 'Área de Texto',
        'select' => 'Seleção',
        'checkbox' => 'Caixas de Seleção',
        'radio' => 'Rádio',
        'file' => 'Upload de Ficheiro',
    ],

    'alignments' => [
        'left' => 'Esquerda',
        'center' => 'Centro',
        'right' => 'Direita',
    ],

    'sizes' => [
        'sm' => 'Pequeno',
        'md' => 'Médio',
        'lg' => 'Grande',
    ],

    'border_radius' => [
        'none' => 'Nenhum',
        'sm' => 'Pequeno',
        'md' => 'Médio',
        'lg' => 'Grande',
        'full' => 'Total (Círculo)',
    ],

    'positions' => [
        'bottom_right' => 'Inferior Direito',
        'bottom_left' => 'Inferior Esquerdo',
        'top_right' => 'Superior Direito',
        'top_left' => 'Superior Esquerdo',
    ],

    'animations' => [
        'none' => 'Nenhuma',
        'pulse' => 'Pulsar',
        'bounce' => 'Saltar',
    ],

    'status' => [
        'active' => 'Ativo',
        'inactive' => 'Inativo',
    ],

    'actions' => [
        'add_field' => 'Adicionar Campo',
        'add_option' => 'Adicionar Opção',
        'open_preview' => 'Abrir Pré-visualização',
    ],

    'embedded_forms' => [
        'navigation' => 'Formulários Incorporados',
        'label' => 'Formulário Incorporado',
        'plural' => 'Formulários Incorporados',
        'notifications' => [
            'created' => 'Formulário criado com sucesso.',
            'updated' => 'Formulário atualizado com sucesso.',
            'deleted' => 'Formulário eliminado.',
        ],
        'empty' => [
            'title' => 'Sem formulários ainda',
            'description' => 'Crie o primeiro formulário para incorporar no seu site.',
        ],
        'actions' => [
            'create' => 'Criar Formulário',
        ],
    ],

    'whatsapp_widgets' => [
        'navigation' => 'Widgets WhatsApp',
        'label' => 'Widget WhatsApp',
        'plural' => 'Widgets WhatsApp',
        'notifications' => [
            'created' => 'Widget WhatsApp criado com sucesso.',
            'updated' => 'Widget WhatsApp atualizado com sucesso.',
            'deleted' => 'Widget WhatsApp eliminado.',
        ],
        'empty' => [
            'title' => 'Sem widgets ainda',
            'description' => 'Crie o primeiro widget para adicionar um botão WhatsApp ao seu site.',
        ],
        'actions' => [
            'create' => 'Criar Widget',
        ],
    ],
];
