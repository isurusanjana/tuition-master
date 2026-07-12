<?php
class ThemeController extends Controller
{
    private array $keys = [
        'theme_primary_color', 'theme_secondary_color', 'theme_header_color', 'theme_sidebar_color',
        'theme_footer_text', 'theme_button_color', 'theme_text_color', 'menu_orientation', 'site_name',
    ];

    public function index(): void
    {
        $this->authorize('theme', 'view');
        $settingsModel = new Setting();
        $current = $settingsModel->allFor(Auth::centerId());
        $this->view('theme/index', ['title' => 'Theme Settings', 'current' => $current]);
    }

    public function update(): void
    {
        $this->authorize('theme', 'edit');
        $this->validateCsrf();
        $settingsModel = new Setting();

        foreach ($this->keys as $key) {
            $value = Request::input($key);
            if ($value !== null && $value !== '') {
                $settingsModel->set(Auth::centerId(), $key, $value);
            }
        }

        // logo upload
        $logo = Request::file('site_logo');
        if ($logo) {
            $path = FileUpload::upload($logo, 'logos', ['png','jpg','jpeg','svg','webp'], 5);
            if ($path) $settingsModel->set(Auth::centerId(), 'site_logo', $path);
        }

        log_activity('update', 'theme', 'Updated theme/branding settings');
        redirect_with_success('/theme', 'Theme settings saved.');
    }
}
