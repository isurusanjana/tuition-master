<h4 class="mb-3">Theme & Branding Settings</h4>
<div class="tm-card p-4">
<form method="POST" action="<?= route('theme.update') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row g-4">
        <div class="col-md-3 col-6 text-center">
            <label class="form-label d-block">Primary Color</label>
            <input type="color" name="theme_primary_color" class="form-control form-control-color color-swatch" value="<?= e($current['theme_primary_color'] ?? '#4f46e5') ?>" data-preview-var="tm-primary">
        </div>
        <div class="col-md-3 col-6 text-center">
            <label class="form-label d-block">Secondary Color</label>
            <input type="color" name="theme_secondary_color" class="form-control form-control-color color-swatch" value="<?= e($current['theme_secondary_color'] ?? '#0ea5e9') ?>" data-preview-var="tm-secondary">
        </div>
        <div class="col-md-3 col-6 text-center">
            <label class="form-label d-block">Header Color</label>
            <input type="color" name="theme_header_color" class="form-control form-control-color color-swatch" value="<?= e($current['theme_header_color'] ?? '#ffffff') ?>" data-preview-var="tm-header">
        </div>
        <div class="col-md-3 col-6 text-center">
            <label class="form-label d-block">Sidebar Color</label>
            <input type="color" name="theme_sidebar_color" class="form-control form-control-color color-swatch" value="<?= e($current['theme_sidebar_color'] ?? '#1e1b4b') ?>" data-preview-var="tm-sidebar">
        </div>
        <div class="col-md-3 col-6 text-center">
            <label class="form-label d-block">Button Color</label>
            <input type="color" name="theme_button_color" class="form-control form-control-color color-swatch" value="<?= e($current['theme_button_color'] ?? '#4f46e5') ?>" data-preview-var="tm-button">
        </div>
        <div class="col-md-3 col-6 text-center">
            <label class="form-label d-block">Text Color</label>
            <input type="color" name="theme_text_color" class="form-control form-control-color color-swatch" value="<?= e($current['theme_text_color'] ?? '#1f2937') ?>" data-preview-var="tm-text">
        </div>
    </div>

    <hr class="my-4">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Site / Center Display Name</label>
            <input name="site_name" class="form-control" value="<?= e($current['site_name'] ?? APP_NAME) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Menu Orientation</label>
            <select name="menu_orientation" class="form-select">
                <option value="vertical" <?= ($current['menu_orientation'] ?? 'vertical')==='vertical'?'selected':'' ?>>Vertical (Sidebar)</option>
                <option value="horizontal" <?= ($current['menu_orientation'] ?? '')==='horizontal'?'selected':'' ?>>Horizontal (Top Menu)</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Logo</label>
            <input type="file" name="site_logo" class="form-control">
            <?php if (!empty($current['site_logo'])): ?><img src="<?= asset('uploads/'.e($current['site_logo'])) ?>" class="mt-2" style="height:40px;"><?php endif; ?>
        </div>
        <div class="col-md-6">
            <label class="form-label">Footer Text</label>
            <input name="theme_footer_text" class="form-control" value="<?= e($current['theme_footer_text'] ?? '') ?>">
        </div>
    </div>
    <div class="mt-4"><button class="btn btn-tm text-white">Save Theme Settings</button></div>
</form>
</div>
