<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview — {{ $widget->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f3f4f6;
            min-height: 100vh;
        }
        .preview-bar {
            background: #1e293b;
            color: #94a3b8;
            font-size: 13px;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .preview-bar span { color: #f8fafc; font-weight: 600; }
        .preview-bar .dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: {{ $widget->appearance['button_color'] ?? '#25d366' }};
            display: inline-block;
        }
        .preview-content {
            padding: 40px 24px;
            max-width: 900px;
            margin: 0 auto;
        }
        .preview-card {
            background: #fff;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0,0,0,.1);
            margin-bottom: 24px;
        }
        .preview-card h2 { font-size: 20px; color: #111; margin-bottom: 8px; }
        .preview-card p { color: #6b7280; line-height: 1.6; }
        .placeholder-text { background: #e5e7eb; border-radius: 4px; height: 14px; margin-bottom: 8px; }
        .placeholder-text.w-3-4 { width: 75%; }
        .placeholder-text.w-1-2 { width: 50%; }
        .placeholder-text.w-full { width: 100%; }
        .preview-note {
            background: #fef9c3;
            border: 1px solid #fde047;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 13px;
            color: #713f12;
            margin-bottom: 24px;
            display: flex;
            gap: 8px;
            align-items: flex-start;
        }
    </style>
</head>
<body>
    <div class="preview-bar">
        <span class="dot"></span>
        Preview — <span>{{ $widget->name }}</span>
        &nbsp;·&nbsp; Posição: {{ $widget->position ?? 'bottom-right' }}
        &nbsp;·&nbsp; Status:
        @if($widget->status === 'active')
            <span style="color:#4ade80">Ativo</span>
        @else
            <span style="color:#f87171">Inativo</span>
        @endif
    </div>

    <div class="preview-content">
        <div class="preview-note">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 20 20" style="flex-shrink:0;margin-top:1px"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/></svg>
            Este é um preview do widget. O botão aparece no canto {{ $widget->position ?? 'bottom-right' }} da página. Clique nele para ver o modal de captura.
        </div>

        <div class="preview-card">
            <h2>Exemplo de página do cliente</h2>
            <p>O widget WhatsApp aparece sobre o conteúdo do site, no canto configurado.</p>
            <br>
            <div class="placeholder-text w-full"></div>
            <div class="placeholder-text w-3-4"></div>
            <div class="placeholder-text w-full"></div>
            <div class="placeholder-text w-1-2"></div>
        </div>

        <div class="preview-card">
            <div class="placeholder-text w-3-4"></div>
            <div class="placeholder-text w-full"></div>
            <div class="placeholder-text w-full"></div>
            <div class="placeholder-text w-1-2"></div>
        </div>
    </div>

    {{-- Load the actual widget script --}}
    <script src="{{ url('/embed/whatsapp-' . $widget->id . '.js') }}"></script>
</body>
</html>
