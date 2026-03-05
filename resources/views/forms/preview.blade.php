<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview — {{ $form->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        /* ---------- Preview chrome ---------- */
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
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .preview-bar span { color: #f8fafc; font-weight: 600; }
        .preview-bar .dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: {{ $form->styles['button_color'] ?? '#3b82f6' }};
            display: inline-block;
        }
        .preview-bar .badge {
            padding: 2px 8px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-active { background: #166534; color: #86efac; }
        .badge-inactive { background: #7f1d1d; color: #fca5a5; }

        /* ---------- Fake website ---------- */
        .site-header {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .site-logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .site-logo-icon {
            width: 32px; height: 32px;
            background: {{ $form->styles['button_color'] ?? '#3b82f6' }};
            border-radius: 8px;
        }
        .site-logo-name { font-weight: 700; font-size: 16px; color: #111; }
        .site-nav { display: flex; gap: 24px; }
        .site-nav a {
            color: #6b7280;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .site-layout {
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px 24px;
            display: grid;
            grid-template-columns: 1fr 420px;
            gap: 40px;
            align-items: start;
        }
        @media (max-width: 768px) {
            .site-layout { grid-template-columns: 1fr; }
            .site-nav { display: none; }
        }

        /* Left content */
        .content-section h1 { font-size: 28px; font-weight: 700; color: #111; margin-bottom: 12px; line-height: 1.3; }
        .content-section > p { color: #6b7280; line-height: 1.7; margin-bottom: 20px; font-size: 15px; }
        .placeholder-block { margin-bottom: 24px; }
        .placeholder-line {
            background: #e5e7eb;
            border-radius: 4px;
            height: 13px;
            margin-bottom: 8px;
        }
        .placeholder-line.w-full { width: 100%; }
        .placeholder-line.w-3-4 { width: 75%; }
        .placeholder-line.w-2-3 { width: 66%; }
        .placeholder-line.w-1-2 { width: 50%; }
        .placeholder-line.w-1-3 { width: 33%; }
        .feature-list { display: flex; flex-direction: column; gap: 12px; margin-bottom: 28px; }
        .feature-item { display: flex; align-items: center; gap: 10px; color: #374151; font-size: 14px; }
        .feature-dot {
            width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
            background: {{ $form->styles['button_color'] ?? '#3b82f6' }};
        }

        /* Form card (right) */
        .form-card {
            background: {{ $form->styles['background_color'] ?? '#ffffff' }};
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,.1);
            overflow: hidden;
        }
        .form-card-header {
            background: {{ $form->styles['button_color'] ?? '#3b82f6' }};
            padding: 20px 24px;
        }
        .form-card-header h3 { color: #fff; font-size: 18px; font-weight: 700; margin-bottom: 4px; }
        .form-card-header p { color: rgba(255,255,255,.8); font-size: 13px; }
        .form-card-body {
            padding: 24px;
            font-family: {{ $form->styles['font_family'] ?? 'inherit' }};
            font-size: {{ is_numeric($form->styles['font_size'] ?? null) ? ($form->styles['font_size'].'pt') : ($form->styles['font_size'] ?? '14pt') }};
            color: {{ $form->styles['text_color'] ?? '#111' }};
        }

        /* ---------- Form fields ---------- */
        .form-group { margin-bottom: {{ $form->styles['field_spacing'] ?? '16px' }}; }
        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            font-size: 0.9em;
        }
        .form-control {
            width: 100%;
            padding: {{ match($form->styles['input_size'] ?? 'md') {
                'sm' => '6px 10px',
                'lg' => '11px 14px',
                default => '8px 12px',
            } }};
            border: 1px solid {{ $form->styles['border_color'] ?? '#d1d5db' }};
            border-radius: {{ match($form->styles['border_radius'] ?? 'md') {
                'none' => '0',
                'sm' => '4px',
                'lg' => '12px',
                default => '6px',
            } }};
            font-size: inherit;
            font-family: inherit;
            color: inherit;
            background: #fff;
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
            appearance: none;
        }
        .form-control:focus {
            border-color: {{ $form->styles['button_color'] ?? '#3b82f6' }};
            box-shadow: 0 0 0 3px {{ $form->styles['button_color'] ?? '#3b82f6' }}22;
        }
        .form-control::placeholder { color: {{ $form->styles['placeholder_color'] ?? '#9ca3af' }}; }
        textarea.form-control { resize: vertical; min-height: 80px; }
        select.form-control { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; background-size: 16px; padding-right: 32px; }
        .form-check { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; font-size: 0.9em; cursor: pointer; }
        .form-check input { width: 15px; height: 15px; accent-color: {{ $form->styles['button_color'] ?? '#3b82f6' }}; }
        .form-help { font-size: 0.8em; color: #9ca3af; margin-top: 4px; }
        .form-error { color: #dc2626; font-size: 0.8em; margin-top: 4px; min-height: 1em; }

        .btn-submit {
            width: 100%;
            padding: {{ match($form->styles['button_size'] ?? 'md') {
                'sm' => '8px 16px',
                'lg' => '14px 24px',
                default => '10px 20px',
            } }};
            background: {{ $form->styles['button_color'] ?? '#3b82f6' }};
            color: {{ $form->styles['button_text_color'] ?? '#ffffff' }};
            border: none;
            border-radius: {{ match($form->styles['button_border_radius'] ?? $form->styles['border_radius'] ?? 'md') {
                'none' => '0',
                'sm' => '4px',
                'lg' => '12px',
                default => '6px',
            } }};
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.15s, transform 0.1s;
            margin-top: 4px;
        }
        .btn-submit:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-submit:active { transform: translateY(0); }
        .btn-submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

        .form-success {
            padding: 16px;
            background: #d1fae5;
            color: #065f46;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
        }

        /* Preview note */
        .preview-note {
            background: #fef9c3;
            border: 1px solid #fde047;
            border-radius: 8px;
            padding: 10px 16px;
            font-size: 12px;
            color: #713f12;
            margin: 12px 24px;
            display: flex;
            gap: 8px;
            align-items: center;
        }
    </style>
</head>
<body>

    {{-- Preview bar --}}
    <div class="preview-bar">
        <span class="dot"></span>
        Preview — <span>{{ $form->name }}</span>
        &nbsp;·&nbsp;
        <span class="badge {{ $form->status === 'active' ? 'badge-active' : 'badge-inactive' }}">
            {{ $form->status === 'active' ? 'Ativo' : 'Inativo' }}
        </span>
        &nbsp;·&nbsp; /forms/{{ $form->slug }}
    </div>

    {{-- Fake site header --}}
    <div class="site-header">
        <div class="site-logo">
            <div class="site-logo-icon"></div>
            <span class="site-logo-name">Exemplo de Site</span>
        </div>
        <nav class="site-nav">
            <a href="#">Início</a>
            <a href="#">Sobre</a>
            <a href="#">Serviços</a>
            <a href="#" style="color:{{ $form->styles['button_color'] ?? '#3b82f6' }}; font-weight:700">Contacto</a>
        </nav>
    </div>

    {{-- Two-column layout --}}
    <div class="site-layout">

        {{-- Left: fake content --}}
        <div class="content-section">
            <h1>Transforme visitantes em clientes</h1>
            <p>Preencha o formulário ao lado e entraremos em contacto brevemente para perceber como podemos ajudar o seu negócio a crescer.</p>

            <div class="feature-list">
                @foreach(['Resposta em menos de 24h', 'Sem compromisso', 'Consultoria personalizada', 'Equipa especializada'] as $item)
                    <div class="feature-item">
                        <div class="feature-dot"></div>
                        {{ $item }}
                    </div>
                @endforeach
            </div>

            <div class="placeholder-block">
                <div class="placeholder-line w-full"></div>
                <div class="placeholder-line w-3-4"></div>
                <div class="placeholder-line w-full"></div>
                <div class="placeholder-line w-2-3"></div>
            </div>
            <div class="placeholder-block">
                <div class="placeholder-line w-full"></div>
                <div class="placeholder-line w-1-2"></div>
                <div class="placeholder-line w-3-4"></div>
                <div class="placeholder-line w-1-3"></div>
            </div>
        </div>

        {{-- Right: actual form --}}
        <div>
            <div class="form-card">
                <div class="form-card-header">
                    <h3>{{ $form->name }}</h3>
                    @if($form->description)
                        <p>{{ $form->description }}</p>
                    @else
                        <p>Preencha os dados abaixo</p>
                    @endif
                </div>

                @if($form->status !== 'active')
                    <div class="preview-note">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                        Formulário inativo — o preview funciona mas o formulário público está desativado.
                    </div>
                @endif

                <div class="form-card-body">
                    @php $sortedFields = collect($form->fields ?? [])->sortBy('order')->values(); @endphp

                    @if($sortedFields->isEmpty())
                        <p style="color:#9ca3af;text-align:center;padding:24px 0;">Sem campos configurados ainda.</p>
                    @else
                        <form id="velozz-preview-form" action="/api/forms/{{ $form->slug }}/submit" method="POST">
                            @foreach($sortedFields as $field)
                                <div class="form-group">
                                    <label class="form-label" for="pf_{{ $field['name'] }}">
                                        {{ $field['label'] }}
                                        @if(!empty($field['required']))<span style="color:#dc2626"> *</span>@endif
                                    </label>

                                    @if($field['type'] === 'textarea')
                                        <textarea id="pf_{{ $field['name'] }}" name="{{ $field['name'] }}" class="form-control" placeholder="{{ $field['placeholder'] ?? '' }}" {{ !empty($field['required']) ? 'required' : '' }}>{{ $field['default_value'] ?? '' }}</textarea>

                                    @elseif($field['type'] === 'select')
                                        <select id="pf_{{ $field['name'] }}" name="{{ $field['name'] }}" class="form-control" {{ !empty($field['required']) ? 'required' : '' }}>
                                            <option value="">{{ $field['placeholder'] ?? 'Escolher...' }}</option>
                                            @foreach($field['options'] ?? [] as $option)
                                                <option value="{{ $option['value'] ?? $option }}" {{ ($field['default_value'] ?? '') === ($option['value'] ?? $option) ? 'selected' : '' }}>{{ $option['value'] ?? $option }}</option>
                                            @endforeach
                                        </select>

                                    @elseif($field['type'] === 'checkbox')
                                        @foreach($field['options'] ?? [] as $option)
                                            <label class="form-check">
                                                <input type="checkbox" name="{{ $field['name'] }}[]" value="{{ $option['value'] ?? $option }}">
                                                {{ $option['value'] ?? $option }}
                                            </label>
                                        @endforeach

                                    @elseif($field['type'] === 'radio')
                                        @foreach($field['options'] ?? [] as $option)
                                            <label class="form-check">
                                                <input type="radio" name="{{ $field['name'] }}" value="{{ $option['value'] ?? $option }}" {{ ($field['default_value'] ?? '') === ($option['value'] ?? $option) ? 'checked' : '' }}>
                                                {{ $option['value'] ?? $option }}
                                            </label>
                                        @endforeach

                                    @elseif($field['type'] === 'file')
                                        <input type="file" id="pf_{{ $field['name'] }}" name="{{ $field['name'] }}" class="form-control" {{ !empty($field['required']) ? 'required' : '' }}>

                                    @else
                                        <input
                                            type="{{ $field['type'] }}"
                                            id="pf_{{ $field['name'] }}"
                                            name="{{ $field['name'] }}"
                                            class="form-control"
                                            placeholder="{{ $field['placeholder'] ?? '' }}"
                                            value="{{ $field['default_value'] ?? '' }}"
                                            {{ !empty($field['required']) ? 'required' : '' }}
                                        >
                                    @endif

                                    @if(!empty($field['help_text']))
                                        <p class="form-help">{{ $field['help_text'] }}</p>
                                    @endif
                                    <div class="form-error" id="perr_{{ $field['name'] }}"></div>
                                </div>
                            @endforeach

                            <button type="submit" class="btn-submit" id="preview-submit-btn">
                                {{ $form->styles['button_text'] ?? 'Enviar' }}
                            </button>
                        </form>
                        <div class="form-success" id="preview-success" style="display:none;">
                            ✓ {{ __('Thank you! Your submission was received.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        const previewForm = document.getElementById('velozz-preview-form');
        if (previewForm) {
            previewForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const btn = document.getElementById('preview-submit-btn');
                const successDiv = document.getElementById('preview-success');
                const formData = new FormData(previewForm);
                const data = Object.fromEntries(formData.entries());

                document.querySelectorAll('.form-error').forEach(el => el.textContent = '');
                btn.disabled = true;

                try {
                    const res = await fetch(previewForm.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify(data),
                    });
                    const result = await res.json();

                    if (res.ok && result.success) {
                        if (result.redirect_url) {
                            window.location.href = result.redirect_url;
                        } else {
                            previewForm.style.display = 'none';
                            successDiv.style.display = 'block';
                        }
                    } else if (result.errors) {
                        Object.entries(result.errors).forEach(([field, messages]) => {
                            const el = document.getElementById('perr_' + field);
                            if (el) el.textContent = messages[0];
                        });
                        btn.disabled = false;
                    }
                } catch (err) {
                    btn.disabled = false;
                }
            });
        }
    </script>
</body>
</html>
