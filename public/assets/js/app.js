document.addEventListener('DOMContentLoaded', function () {
    // Mobile sidebar toggle
    const toggleBtn = document.getElementById('tmSidebarToggle');
    const sidebar = document.getElementById('tmSidebar');
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', () => sidebar.classList.toggle('show'));
    }

    // Auto-init any table with class "datatable"
    if (window.jQuery && jQuery.fn.DataTable) {
        jQuery('.datatable').each(function () {
            jQuery(this).DataTable({
                pageLength: 10,
                responsive: true,
                language: { search: '', searchPlaceholder: 'Search...' }
            });
        });
    }

    // Confirm dialogs for delete forms
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!confirm(form.getAttribute('data-confirm') || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });

    // Live theme color preview (theme settings page)
    document.querySelectorAll('[data-preview-var]').forEach(function (input) {
        input.addEventListener('input', function () {
            document.documentElement.style.setProperty('--' + input.dataset.previewVar, input.value);
        });
    });
});
