<?php
// Get current options
$branding_options = bd_client_suite()->get_option('branding', array());
$login_logo = isset($branding_options['login_logo']) ? $branding_options['login_logo'] : '';
$admin_logo = isset($branding_options['admin_logo']) ? $branding_options['admin_logo'] : '';
$hide_wp_branding = isset($branding_options['hide_wp_branding']) ? $branding_options['hide_wp_branding'] : false;
$color_scheme = isset($branding_options['color_scheme']) ? $branding_options['color_scheme'] : 'default';
$primary_color = isset($branding_options['custom_colors']['primary']) ? $branding_options['custom_colors']['primary'] : '#667eea';
$accent_color = isset($branding_options['custom_colors']['accent']) ? $branding_options['custom_colors']['accent'] : '#764ba2';
?>

<h2><?php _e('Branding Settings', 'bd-client-suite'); ?></h2>
<p><?php _e('Customize the WordPress branding and appearance.', 'bd-client-suite'); ?></p>

<table class="form-table">
    <tr>
        <th scope="row"><?php _e('Login Logo', 'bd-client-suite'); ?></th>
        <td>
            <div class="bd-media-uploader" data-media-type="image">
                <div class="bd-media-preview-container">
                    <?php if ($login_logo): ?>
                        <img src="<?php echo esc_url($login_logo); ?>" class="bd-media-preview" alt="Login Logo">
                    <?php endif; ?>
                </div>
                <div class="bd-media-actions">
                    <button type="button" class="button bd-upload-button">
                        <?php echo $login_logo ? __('Change Logo', 'bd-client-suite') : __('Upload Logo', 'bd-client-suite'); ?>
                    </button>
                    <?php if ($login_logo): ?>
                        <button type="button" class="button bd-remove-media"><?php _e('Remove', 'bd-client-suite'); ?></button>
                    <?php endif; ?>
                </div>
                <input type="hidden" name="branding[login_logo]" value="<?php echo esc_attr($login_logo); ?>" />
                <p class="description"><?php _e('Upload a custom logo for the login page', 'bd-client-suite'); ?></p>
            </div>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php _e('Admin Bar Logo', 'bd-client-suite'); ?></th>
        <td>
            <div class="bd-media-uploader" data-media-type="image">
                <div class="bd-media-preview-container">
                    <?php if ($admin_logo): ?>
                        <img src="<?php echo esc_url($admin_logo); ?>" class="bd-media-preview" alt="Admin Logo">
                    <?php endif; ?>
                </div>
                <div class="bd-media-actions">
                    <button type="button" class="button bd-upload-button">
                        <?php echo $admin_logo ? __('Change Logo', 'bd-client-suite') : __('Upload Logo', 'bd-client-suite'); ?>
                    </button>
                    <?php if ($admin_logo): ?>
                        <button type="button" class="button bd-remove-media"><?php _e('Remove', 'bd-client-suite'); ?></button>
                    <?php endif; ?>
                </div>
                <input type="hidden" name="branding[admin_logo]" value="<?php echo esc_attr($admin_logo); ?>" />
                <p class="description"><?php _e('Upload a custom logo for the admin bar', 'bd-client-suite'); ?></p>
            </div>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php _e('Color Scheme', 'bd-client-suite'); ?></th>
        <td>
            <select name="branding[color_scheme]" id="bd-color-scheme">
                <?php
                $color_schemes = array(
                    'default' => __('Default WordPress', 'bd-client-suite'),
                    'modern-blue' => __('Modern Blue', 'bd-client-suite'),
                    'professional-gray' => __('Professional Gray', 'bd-client-suite'),
                    'elegant-purple' => __('Elegant Purple', 'bd-client-suite'),
                    'clean-green' => __('Clean Green', 'bd-client-suite'),
                    'minimal-dark' => __('Minimal Dark', 'bd-client-suite'),
                    'custom' => __('Custom Colors', 'bd-client-suite')
                );
                foreach ($color_schemes as $scheme_key => $scheme_name) {
                    echo '<option value="' . esc_attr($scheme_key) . '"' . selected($color_scheme, $scheme_key, false) . '>' . esc_html($scheme_name) . '</option>';
                }
                ?>
            </select>
            <p class="description"><?php _e('Choose a predefined color scheme or use custom colors', 'bd-client-suite'); ?></p>
        </td>
    </tr>
    <tr id="bd-custom-colors" style="<?php echo $color_scheme !== 'custom' ? 'display: none;' : ''; ?>">
        <th scope="row"><?php _e('Custom Colors', 'bd-client-suite'); ?></th>
        <td>
            <div class="bd-color-picker">
                <label><?php _e('Primary Color', 'bd-client-suite'); ?></label>
                <input type="text" name="branding[custom_colors][primary]" value="<?php echo esc_attr($primary_color); ?>" class="bd-color-field" />
            </div>
            <div class="bd-color-picker">
                <label><?php _e('Accent Color', 'bd-client-suite'); ?></label>
                <input type="text" name="branding[custom_colors][accent]" value="<?php echo esc_attr($accent_color); ?>" class="bd-color-field" />
            </div>
            <p class="description"><?php _e('Set custom colors for buttons, links, and highlights', 'bd-client-suite'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php _e('WordPress Branding', 'bd-client-suite'); ?></th>
        <td>
            <label>
                <input type="checkbox" name="branding[hide_wp_branding]" value="1" <?php checked($hide_wp_branding); ?> />
                <?php _e('Hide WordPress branding elements', 'bd-client-suite'); ?>
            </label>
            <p class="description"><?php _e('Remove WordPress logos, version info, and news widgets', 'bd-client-suite'); ?></p>
        </td>
    </tr>
</table>

<script>
jQuery(document).ready(function($) {
    // Color scheme toggle
    $('#bd-color-scheme').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#bd-custom-colors').show();
        } else {
            $('#bd-custom-colors').hide();
        }
    });
    
    // Initialize color pickers
    $('.bd-color-field').wpColorPicker();
});
</script>
