(function() {
    var formId = {{ $form->id }};
    var formSlug = '{{ $form->slug }}';
    var baseUrl = '{{ config('app.url') }}';
    var iframeSrc = baseUrl + '/forms/' + formSlug;

    var containers = document.querySelectorAll('[data-form="{{ $form->id }}"]');

    containers.forEach(function(container) {
        var iframe = document.createElement('iframe');
        iframe.src = iframeSrc;
        iframe.style.width = '100%';
        iframe.style.border = 'none';
        iframe.style.minHeight = '400px';
        iframe.setAttribute('frameborder', '0');
        iframe.setAttribute('scrolling', 'no');
        iframe.setAttribute('title', '{{ addslashes($form->name) }}');

        // Auto-resize iframe based on content
        window.addEventListener('message', function(e) {
            if (e.data && e.data.type === 'velozz-form-resize' && e.data.formId === formId) {
                iframe.style.height = e.data.height + 'px';
            }
        });

        container.appendChild(iframe);
    });
})();
