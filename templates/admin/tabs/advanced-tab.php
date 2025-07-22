<?php
// Get current options
$advanced_options = bd_client_suite()->get_option('advanced', array());
$custom_css = isset($advanced_options['custom_css']) ? $advanced_options['custom_css'] : '';
$custom_footer = isset($advanced_options['custom_footer']) ? $advanced_options['custom_footer'] : '';
?>

<h2><?php _e('Advanced Settings', 'bd-client-suite'); ?></h2>
<p><?php _e('Advanced customization options and custom code.', 'bd-client-suite'); ?></p>

<table class="form-table">
    <tr>
        <th scope="row"><?php _e('Custom CSS', 'bd-client-suite'); ?></th>
        <td>
            <textarea name="advanced[custom_css]" rows="10" cols="50" class="large-text code"><?php echo esc_textarea($custom_css); ?></textarea>
            <p class="description"><?php _e('Add custom CSS to the admin area', 'bd-client-suite'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php _e('Custom Footer Text', 'bd-client-suite'); ?></th>
        <td>
            <input type="text" name="advanced[custom_footer]" value="<?php echo esc_attr($custom_footer); ?>" class="regular-text" />
            <p class="description"><?php _e('Custom footer text for admin pages', 'bd-client-suite'); ?></p>
        </td>
    </tr>
</table>
</div>
