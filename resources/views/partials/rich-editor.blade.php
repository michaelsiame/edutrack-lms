{{--
    Shared self-hosted TinyMCE for admin/staff rich-text fields.
    Usage: add class="rich-editor" to a <textarea>, then inside the page's
    @push('scripts') add:  @include('partials.rich-editor')
    Mirrors the proven course-builder config (minus the instructor-only image
    upload route) so it's compatible with the bundled self-hosted build.
--}}
<script src="{{ asset('assets/js/tinymce/tinymce.min.js') }}"></script>
<script>
(function () {
    function initRichEditors() {
        if (typeof tinymce === 'undefined') return;
        if (!document.querySelector('textarea.rich-editor')) return;
        var isDark = document.documentElement.classList.contains('dark');
        tinymce.init({
            selector: 'textarea.rich-editor',
            height: 320,
            menubar: false,
            plugins: 'advlist autolink lists link image table codesample fullscreen wordcount searchreplace code preview',
            toolbar: 'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright | bullist numlist outdent indent | link image table codesample | fullscreen code preview',
            content_style: 'body { font-family: ui-sans-serif, system-ui, sans-serif; font-size: 14px; line-height: 1.6; color: #374151; } body.dark { color: #d1d5db; }',
            skin: isDark ? 'oxide-dark' : 'oxide',
            content_css: isDark ? 'dark' : 'default',
            relative_urls: false,
            remove_script_host: false,
            convert_urls: true,
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initRichEditors);
    } else {
        initRichEditors();
    }
})();
</script>
