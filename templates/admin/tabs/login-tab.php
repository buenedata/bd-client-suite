<?php
// Get current options
$login_options = bd_client_suite()->get_option('login', array());
$custom_login_page = isset($login_options['custom_login_page']) ? $login_options['custom_login_page'] : false;
$background_image = isset($login_options['background_image']) ? $login_options['background_image'] : '';
$background_color = isset($login_options['background_color']) ? $login_options['background_color'] : '#f1f1f1';
$form_background = isset($login_options['form_background']) ? $login_options['form_background'] : '#ffffff';
$form_border_color = isset($login_options['form_border_color']) ? $login_options['form_border_color'] : '#dddddd';
$button_color = isset($login_options['button_color']) ? $login_options['button_color'] : '#0073aa';
$custom_logo = isset($login_options['custom_logo']) ? $login_options['custom_logo'] : '';
$logo_width = isset($login_options['logo_width']) ? $login_options['logo_width'] : '80';
$logo_height = isset($login_options['logo_height']) ? $login_options['logo_height'] : '80';
?>

<h2><?php _e('Login Page Settings', 'bd-client-suite'); ?></h2>
<p><?php _e('Customize the WordPress login page appearance and behavior.', 'bd-client-suite'); ?></p>

<table class="form-table">
    <tr>
        <th scope="row"><?php _e('Custom Login Page', 'bd-client-suite'); ?></th>
        <td>
            <label>
                <input type="checkbox" name="login[custom_login_page]" id="bd-custom-login-toggle" value="1" <?php checked($custom_login_page); ?> />
                <?php _e('Enable custom login page styling', 'bd-client-suite'); ?>
            </label>
            <p class="description"><?php _e('When enabled, additional styling options will be available below.', 'bd-client-suite'); ?></p>
        </td>
    </tr>
</table>

<div id="bd-custom-login-options" style="<?php echo !$custom_login_page ? 'display: none;' : ''; ?>">
    <h3><?php _e('Login Page Styling', 'bd-client-suite'); ?></h3>
    <table class="form-table">
        <tr>
            <th scope="row"><?php _e('Background Image', 'bd-client-suite'); ?></th>
            <td>
                <div class="bd-media-uploader" data-media-type="image">
                    <div class="bd-media-preview-container">
                        <?php if ($background_image): ?>
                            <img src="<?php echo esc_url($background_image); ?>" class="bd-media-preview" alt="Background Image" style="max-width: 200px; height: auto;">
                        <?php endif; ?>
                    </div>
                    <div class="bd-media-actions">
                        <button type="button" class="button bd-upload-button">
                            <?php echo $background_image ? __('Change Background', 'bd-client-suite') : __('Upload Background', 'bd-client-suite'); ?>
                        </button>
                        <?php if ($background_image): ?>
                            <button type="button" class="button bd-remove-media"><?php _e('Remove', 'bd-client-suite'); ?></button>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="login[background_image]" value="<?php echo esc_attr($background_image); ?>" />
                    <p class="description"><?php _e('Upload a background image for the login page', 'bd-client-suite'); ?></p>
                </div>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php _e('Background Color', 'bd-client-suite'); ?></th>
            <td>
                <input type="text" name="login[background_color]" value="<?php echo esc_attr($background_color); ?>" class="bd-color-field" />
                <p class="description"><?php _e('Background color for the login page', 'bd-client-suite'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php _e('Custom Logo', 'bd-client-suite'); ?></th>
            <td>
                <div class="bd-media-uploader" data-media-type="image">
                    <div class="bd-media-preview-container">
                        <?php if ($custom_logo): ?>
                            <img src="<?php echo esc_url($custom_logo); ?>" class="bd-media-preview" alt="Custom Logo" style="max-width: 150px; height: auto;">
                        <?php endif; ?>
                    </div>
                    <div class="bd-media-actions">
                        <button type="button" class="button bd-upload-button">
                            <?php echo $custom_logo ? __('Change Logo', 'bd-client-suite') : __('Upload Logo', 'bd-client-suite'); ?>
                        </button>
                        <?php if ($custom_logo): ?>
                            <button type="button" class="button bd-remove-media"><?php _e('Remove', 'bd-client-suite'); ?></button>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="login[custom_logo]" value="<?php echo esc_attr($custom_logo); ?>" />
                    <p class="description"><?php _e('Upload a custom logo for the login form', 'bd-client-suite'); ?></p>
                </div>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php _e('Logo Dimensions', 'bd-client-suite'); ?></th>
            <td>
                <input type="number" name="login[logo_width]" value="<?php echo esc_attr($logo_width); ?>" placeholder="80" style="width: 80px;" /> px
                <span style="margin: 0 10px;">×</span>
                <input type="number" name="login[logo_height]" value="<?php echo esc_attr($logo_height); ?>" placeholder="80" style="width: 80px;" /> px
                <p class="description"><?php _e('Set the width and height for your custom logo', 'bd-client-suite'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php _e('Form Background Color', 'bd-client-suite'); ?></th>
            <td>
                <input type="text" name="login[form_background]" value="<?php echo esc_attr($form_background); ?>" class="bd-color-field" />
                <p class="description"><?php _e('Background color for the login form', 'bd-client-suite'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php _e('Form Border Color', 'bd-client-suite'); ?></th>
            <td>
                <input type="text" name="login[form_border_color]" value="<?php echo esc_attr($form_border_color); ?>" class="bd-color-field" />
                <p class="description"><?php _e('Border color for the login form', 'bd-client-suite'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php _e('Button Color', 'bd-client-suite'); ?></th>
            <td>
                <input type="text" name="login[button_color]" value="<?php echo esc_attr($button_color); ?>" class="bd-color-field" />
                <p class="description"><?php _e('Color for login buttons and links', 'bd-client-suite'); ?></p>
            </td>
        </tr>
    </table>
</div>

<script>
jQuery(document).ready(function($) {
    // Toggle custom login options
    $('#bd-custom-login-toggle').on('change', function() {
        if ($(this).is(':checked')) {
            $('#bd-custom-login-options').slideDown();
        } else {
            $('#bd-custom-login-options').slideUp();
        }
    });
    
    // Initialize color pickers for login page
    $('.bd-color-field').wpColorPicker();
});
</script>
