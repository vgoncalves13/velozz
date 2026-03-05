(function() {
    var widgetId = {{ $widget->id }};
    var baseUrl = '{{ config('app.url') }}';
    var position = '{{ $widget->position ?? 'bottom-right' }}';
    var btnColor = '{{ $widget->appearance['button_color'] ?? '#25d366' }}';
    var btnSize = '{{ $widget->appearance['button_size'] ?? '60px' }}';
    var borderRadius = '{{ $widget->appearance['border_radius'] ?? '50%' }}';
    var animation = '{{ $widget->appearance['animation'] ?? 'none' }}';
    var btnText = '{{ addslashes($widget->appearance['button_text'] ?? '') }}';
    var showText = {{ $widget->appearance['show_text'] ?? false ? 'true' : 'false' }};

    // Styles
    var style = document.createElement('style');
    style.textContent = [
        '#velozz-wa-btn-' + widgetId + ' {',
        '  position: fixed;',
        '  z-index: 9999;',
        '  cursor: pointer;',
        '  display: flex;',
        '  align-items: center;',
        '  justify-content: center;',
        '  gap: 8px;',
        '  background: ' + btnColor + ';',
        '  width: ' + btnSize + ';',
        '  height: ' + btnSize + ';',
        '  border-radius: ' + borderRadius + ';',
        '  box-shadow: 0 4px 12px rgba(0,0,0,0.2);',
        '  transition: transform 0.2s, box-shadow 0.2s;',
        (position.includes('bottom') ? '  bottom: 24px;' : '  top: 24px;'),
        (position.includes('right') ? '  right: 24px;' : '  left: 24px;'),
        '}',
        '#velozz-wa-btn-' + widgetId + ':hover { transform: scale(1.08); box-shadow: 0 6px 16px rgba(0,0,0,0.25); }',
        animation === 'pulse' ? '#velozz-wa-btn-' + widgetId + ' { animation: velozz-pulse 2s infinite; }' : '',
        animation === 'bounce' ? '#velozz-wa-btn-' + widgetId + ' { animation: velozz-bounce 1.5s infinite; }' : '',
        '@keyframes velozz-pulse { 0%,100%{box-shadow:0 0 0 0 ' + btnColor + '88} 50%{box-shadow:0 0 0 10px transparent} }',
        '@keyframes velozz-bounce { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }',
        '#velozz-wa-modal-' + widgetId + ' {',
        '  display: none;',
        '  position: fixed;',
        '  z-index: 10000;',
        '  inset: 0;',
        '  background: rgba(0,0,0,0.5);',
        '  align-items: center;',
        '  justify-content: center;',
        '}',
        '#velozz-wa-modal-' + widgetId + '.open { display: flex; }',
        '#velozz-wa-box-' + widgetId + ' {',
        '  background: #fff;',
        '  border-radius: 12px;',
        '  padding: 24px;',
        '  width: 90%;',
        '  max-width: 360px;',
        '  box-shadow: 0 8px 32px rgba(0,0,0,0.2);',
        '}',
        '#velozz-wa-box-' + widgetId + ' h3 { margin-bottom: 16px; font-size: 18px; color: #111; }',
        '#velozz-wa-box-' + widgetId + ' input {',
        '  width: 100%;',
        '  padding: 9px 12px;',
        '  margin-bottom: 12px;',
        '  border: 1px solid #ccc;',
        '  border-radius: 6px;',
        '  font-size: 14px;',
        '  outline: none;',
        '}',
        '#velozz-wa-box-' + widgetId + ' input:focus { border-color: ' + btnColor + '; }',
        '#velozz-wa-box-' + widgetId + ' button.submit {',
        '  width: 100%;',
        '  padding: 10px;',
        '  background: ' + btnColor + ';',
        '  color: #fff;',
        '  border: none;',
        '  border-radius: 6px;',
        '  font-size: 15px;',
        '  cursor: pointer;',
        '  font-weight: 600;',
        '}',
        '#velozz-wa-box-' + widgetId + ' button.submit:hover { opacity: 0.9; }',
        '#velozz-wa-box-' + widgetId + ' button.close-btn {',
        '  position: absolute;',
        '  top: 12px;',
        '  right: 16px;',
        '  background: none;',
        '  border: none;',
        '  font-size: 22px;',
        '  cursor: pointer;',
        '  color: #666;',
        '}',
        '#velozz-wa-box-' + widgetId + ' { position: relative; }',
        '.velozz-wa-error { color: #dc2626; font-size: 12px; margin-top: -8px; margin-bottom: 8px; }',
        '.velozz-wa-success { color: #065f46; background: #d1fae5; padding: 10px; border-radius: 6px; text-align: center; }',
    ].join('\n');
    document.head.appendChild(style);

    // WhatsApp SVG Icon
    var waIcon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="28" height="28" fill="#fff"><path d="M16 0C7.164 0 0 7.164 0 16c0 2.822.736 5.473 2.027 7.773L0 32l8.47-2.004A15.932 15.932 0 0016 32c8.836 0 16-7.164 16-16S24.836 0 16 0zm0 29.3a13.24 13.24 0 01-6.745-1.84l-.484-.287-4.997 1.182 1.21-4.864-.318-.503A13.27 13.27 0 012.7 16C2.7 8.654 8.654 2.7 16 2.7S29.3 8.654 29.3 16 23.346 29.3 16 29.3zm7.27-9.775c-.396-.198-2.344-1.157-2.708-1.288-.363-.13-.628-.198-.893.199-.264.396-1.025 1.288-1.257 1.553-.23.264-.463.297-.859.099-.397-.198-1.675-.618-3.19-1.97-1.179-1.052-1.975-2.351-2.207-2.748-.23-.396-.025-.611.174-.808.178-.177.396-.462.594-.694.199-.23.264-.396.397-.661.132-.264.066-.496-.033-.694-.1-.198-.893-2.153-1.224-2.949-.323-.775-.65-.67-.893-.682l-.76-.013c-.264 0-.694.099-1.057.496-.363.396-1.387 1.355-1.387 3.303 0 1.948 1.42 3.831 1.618 4.096.198.264 2.793 4.264 6.767 5.981.946.407 1.684.65 2.26.832.95.302 1.815.259 2.499.157.762-.114 2.344-.958 2.674-1.882.33-.924.33-1.716.231-1.882-.099-.165-.363-.264-.76-.462z"/></svg>';

    // Create button
    var btn = document.createElement('div');
    btn.id = 'velozz-wa-btn-' + widgetId;
    btn.innerHTML = waIcon + (showText && btnText ? '<span style="color:#fff;font-size:14px;font-weight:600;white-space:nowrap">' + btnText + '</span>' : '');
    if (showText && btnText) {
        btn.style.width = 'auto';
        btn.style.padding = '0 18px';
        btn.style.borderRadius = borderRadius === '50%' ? '30px' : borderRadius;
    }
    document.body.appendChild(btn);

    // Create modal
    var modal = document.createElement('div');
    modal.id = 'velozz-wa-modal-' + widgetId;
    modal.innerHTML = [
        '<div id="velozz-wa-box-' + widgetId + '">',
        '  <button class="close-btn" id="velozz-wa-close-' + widgetId + '">&times;</button>',
        '  <h3>{{ __('Talk to us on WhatsApp') }}</h3>',
        '  <div id="velozz-wa-form-' + widgetId + '">',
        '    <input type="text" id="velozz-wa-nome-' + widgetId + '" placeholder="{{ __('Your name') }}" />',
        '    <div class="velozz-wa-error" id="velozz-wa-nome-err-' + widgetId + '"></div>',
        '    <input type="tel" id="velozz-wa-tel-' + widgetId + '" placeholder="{{ __('Your phone') }}" />',
        '    <div class="velozz-wa-error" id="velozz-wa-tel-err-' + widgetId + '"></div>',
        '    <input type="email" id="velozz-wa-email-' + widgetId + '" placeholder="{{ __('Email (optional)') }}" />',
        '    <button class="submit" id="velozz-wa-submit-' + widgetId + '">{{ __('Start conversation') }}</button>',
        '  </div>',
        '  <div class="velozz-wa-success" id="velozz-wa-success-' + widgetId + '" style="display:none">{{ __('Done! We will contact you shortly.') }}</div>',
        '</div>',
    ].join('');
    document.body.appendChild(modal);

    btn.addEventListener('click', function() {
        modal.classList.add('open');
    });

    document.getElementById('velozz-wa-close-' + widgetId).addEventListener('click', function() {
        modal.classList.remove('open');
    });

    modal.addEventListener('click', function(e) {
        if (e.target === modal) modal.classList.remove('open');
    });

    document.getElementById('velozz-wa-submit-' + widgetId).addEventListener('click', async function() {
        var nome = document.getElementById('velozz-wa-nome-' + widgetId).value.trim();
        var tel = document.getElementById('velozz-wa-tel-' + widgetId).value.trim();
        var email = document.getElementById('velozz-wa-email-' + widgetId).value.trim();

        document.getElementById('velozz-wa-nome-err-' + widgetId).textContent = '';
        document.getElementById('velozz-wa-tel-err-' + widgetId).textContent = '';

        var valid = true;
        if (!nome) { document.getElementById('velozz-wa-nome-err-' + widgetId).textContent = '{{ __('Name is required') }}'; valid = false; }
        if (!tel) { document.getElementById('velozz-wa-tel-err-' + widgetId).textContent = '{{ __('Phone is required') }}'; valid = false; }
        if (!valid) return;

        var submitBtn = document.getElementById('velozz-wa-submit-' + widgetId);
        submitBtn.disabled = true;

        try {
            var res = await fetch(baseUrl + '/api/widgets/whatsapp/' + widgetId + '/submit', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ nome: nome, telefone: tel, email: email || null }),
            });
            var data = await res.json();
            if (res.ok && data.success) {
                document.getElementById('velozz-wa-form-' + widgetId).style.display = 'none';
                document.getElementById('velozz-wa-success-' + widgetId).style.display = 'block';
            } else if (data.errors) {
                if (data.errors.nome) document.getElementById('velozz-wa-nome-err-' + widgetId).textContent = data.errors.nome[0];
                if (data.errors.telefone) document.getElementById('velozz-wa-tel-err-' + widgetId).textContent = data.errors.telefone[0];
                submitBtn.disabled = false;
            }
        } catch(e) {
            submitBtn.disabled = false;
        }
    });
})();
