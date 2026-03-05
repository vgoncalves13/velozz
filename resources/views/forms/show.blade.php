<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $form->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: {{ $form->styles['font_family'] ?? 'inherit' }};
            font-size: {{ is_numeric($form->styles['font_size'] ?? null) ? ($form->styles['font_size'] . 'pt') : ($form->styles['font_size'] ?? '14pt') }};
            background: transparent;
            padding: {{ $form->styles['padding'] ?? '16px' }};
        }
        .form-container {
            width: {{ $form->styles['width'] ?? '100%' }};
            text-align: {{ $form->styles['alignment'] ?? 'left' }};
            background-color: {{ $form->styles['background_color'] ?? '#ffffff' }};
            color: {{ $form->styles['text_color'] ?? '#000000' }};
        }
        .form-group {
            margin-bottom: {{ $form->styles['field_spacing'] ?? '16px' }};
        }
        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
        }
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid {{ $form->styles['border_color'] ?? '#cccccc' }};
            border-radius: {{ match($form->styles['border_radius'] ?? 'md') {
                'none' => '0',
                'sm' => '4px',
                'lg' => '12px',
                default => '6px',
            } }};
            font-size: inherit;
            color: inherit;
            background-color: {{ $form->styles['background_color'] ?? '#ffffff' }};
            outline: none;
            transition: border-color 0.2s;
        }
        .form-control:focus {
            border-color: {{ $form->styles['button_color'] ?? '#3b82f6' }};
        }
        .form-control::placeholder {
            color: {{ $form->styles['placeholder_color'] ?? '#999999' }};
        }
        textarea.form-control { resize: vertical; min-height: 80px; }
        .btn-submit {
            padding: {{ match($form->styles['button_size'] ?? 'md') {
                'sm' => '6px 14px',
                'lg' => '12px 28px',
                default => '9px 20px',
            } }};
            background-color: {{ $form->styles['button_color'] ?? '#3b82f6' }};
            color: {{ $form->styles['button_text_color'] ?? '#ffffff' }};
            border: none;
            border-radius: {{ match($form->styles['button_border_radius'] ?? 'md') {
                'none' => '0',
                'sm' => '4px',
                'lg' => '12px',
                default => '6px',
            } }};
            font-size: inherit;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .btn-submit:hover { opacity: 0.9; }
        .btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }
        .form-help { font-size: 0.85em; color: #666; margin-top: 4px; }
        .form-success { padding: 12px; background: #d1fae5; color: #065f46; border-radius: 6px; text-align: center; }
        .form-error { color: #dc2626; font-size: 0.85em; margin-top: 4px; }
    </style>
</head>
<body>
    <div class="form-container">
        <form id="velozz-form" action="/api/forms/{{ $form->slug }}/submit" method="POST">
            @csrf
            @php $sortedFields = collect($form->fields ?? [])->sortBy('order')->values(); @endphp

            @foreach($sortedFields as $field)
                <div class="form-group">
                    <label class="form-label" for="field_{{ $field['name'] }}">
                        {{ $field['label'] }}
                        @if(!empty($field['required'])) <span style="color:#dc2626">*</span> @endif
                    </label>

                    @if($field['type'] === 'textarea')
                        <textarea
                            id="field_{{ $field['name'] }}"
                            name="{{ $field['name'] }}"
                            class="form-control"
                            placeholder="{{ $field['placeholder'] ?? '' }}"
                            {{ !empty($field['required']) ? 'required' : '' }}
                        >{{ $field['default_value'] ?? '' }}</textarea>

                    @elseif($field['type'] === 'select')
                        <select id="field_{{ $field['name'] }}" name="{{ $field['name'] }}" class="form-control" {{ !empty($field['required']) ? 'required' : '' }}>
                            <option value="">{{ $field['placeholder'] ?? __('Choose...') }}</option>
                            @foreach($field['options'] ?? [] as $option)
                                <option value="{{ $option['value'] ?? $option }}" {{ ($field['default_value'] ?? '') === ($option['value'] ?? $option) ? 'selected' : '' }}>
                                    {{ $option['value'] ?? $option }}
                                </option>
                            @endforeach
                        </select>

                    @elseif($field['type'] === 'checkbox')
                        @foreach($field['options'] ?? [] as $option)
                            <div>
                                <label>
                                    <input type="checkbox" name="{{ $field['name'] }}[]" value="{{ $option['value'] ?? $option }}">
                                    {{ $option['value'] ?? $option }}
                                </label>
                            </div>
                        @endforeach

                    @elseif($field['type'] === 'radio')
                        @foreach($field['options'] ?? [] as $option)
                            <div>
                                <label>
                                    <input type="radio" name="{{ $field['name'] }}" value="{{ $option['value'] ?? $option }}" {{ ($field['default_value'] ?? '') === ($option['value'] ?? $option) ? 'checked' : '' }}>
                                    {{ $option['value'] ?? $option }}
                                </label>
                            </div>
                        @endforeach

                    @elseif($field['type'] === 'file')
                        <input type="file" id="field_{{ $field['name'] }}" name="{{ $field['name'] }}" class="form-control" {{ !empty($field['required']) ? 'required' : '' }}>

                    @else
                        <input
                            type="{{ $field['type'] }}"
                            id="field_{{ $field['name'] }}"
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
                    <div class="form-error" id="error_{{ $field['name'] }}"></div>
                </div>
            @endforeach

            <button type="submit" class="btn-submit" id="submit-btn">
                {{ $form->styles['button_text'] ?? __('Submit') }}
            </button>
        </form>
        <div class="form-success" id="form-success" style="display:none;">
            {{ __('Thank you! Your submission was received.') }}
        </div>
    </div>

    <script>
        document.getElementById('velozz-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const form = e.target;
            const btn = document.getElementById('submit-btn');
            const successDiv = document.getElementById('form-success');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            // Clear previous errors
            document.querySelectorAll('.form-error').forEach(el => el.textContent = '');

            btn.disabled = true;

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(data),
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    if (result.redirect_url) {
                        window.top.location.href = result.redirect_url;
                    } else {
                        form.style.display = 'none';
                        successDiv.style.display = 'block';
                    }
                } else if (result.errors) {
                    Object.entries(result.errors).forEach(([field, messages]) => {
                        const el = document.getElementById('error_' + field);
                        if (el) el.textContent = messages[0];
                    });
                    btn.disabled = false;
                }
            } catch (err) {
                btn.disabled = false;
            }
        });
    </script>
</body>
</html>
