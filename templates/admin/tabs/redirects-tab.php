<?php
// Get current options
$redirects_options = bd_client_suite()->get_option('redirects', array());
$enable_role_redirects = isset($redirects_options['enable_role_redirects']) ? $redirects_options['enable_role_redirects'] : false;
$role_redirects = isset($redirects_options['role_redirects']) ? $redirects_options['role_redirects'] : array();
$logout_redirect = isset($redirects_options['logout_redirect']) ? $redirects_options['logout_redirect'] : 'home';
$custom_logout_url = isset($redirects_options['custom_logout_url']) ? $redirects_options['custom_logout_url'] : '';
?>

<h2><?php _e('Redirects Settings', 'bd-client-suite'); ?></h2>
<p><?php _e('Configure login and logout redirect behavior based on user roles.', 'bd-client-suite'); ?></p>

<table class="form-table">
    <tr>
        <th scope="row"><?php _e('Role-Based Redirects', 'bd-client-suite'); ?></th>
        <td>
            <label>
                <input type="checkbox" name="redirects[enable_role_redirects]" id="bd-enable-role-redirects" value="1" <?php checked($enable_role_redirects); ?> />
                <?php _e('Enable different redirect rules per user role', 'bd-client-suite'); ?>
            </label>
            <p class="description"><?php _e('When enabled, you can set specific redirect destinations for each user role.', 'bd-client-suite'); ?></p>
        </td>
    </tr>
</table>

<div id="bd-role-redirects-section" style="<?php echo !$enable_role_redirects ? 'display: none;' : ''; ?>">
    <h3><?php _e('Login Redirects by Role', 'bd-client-suite'); ?></h3>
    <p><?php _e('Set where users should be redirected after login based on their role.', 'bd-client-suite'); ?></p>
    
    <div id="bd-role-redirects-list">
        <?php
        $roles = wp_roles()->get_names();
        foreach ($roles as $role_key => $role_name) {
            $redirect_type = isset($role_redirects[$role_key]['type']) ? $role_redirects[$role_key]['type'] : 'dashboard';
            $custom_url = isset($role_redirects[$role_key]['custom_url']) ? $role_redirects[$role_key]['custom_url'] : '';
        ?>
            <div class="bd-role-redirect-item">
                <h4><?php echo esc_html($role_name); ?> <span class="bd-role-key">(<?php echo esc_html($role_key); ?>)</span></h4>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Redirect To', 'bd-client-suite'); ?></th>
                        <td>
                            <select name="redirects[role_redirects][<?php echo esc_attr($role_key); ?>][type]" class="bd-redirect-type">
                                <option value="dashboard" <?php selected($redirect_type, 'dashboard'); ?>><?php _e('Dashboard', 'bd-client-suite'); ?></option>
                                <option value="admin" <?php selected($redirect_type, 'admin'); ?>><?php _e('Admin Area', 'bd-client-suite'); ?></option>
                                <option value="home" <?php selected($redirect_type, 'home'); ?>><?php _e('Home Page', 'bd-client-suite'); ?></option>
                                <option value="profile" <?php selected($redirect_type, 'profile'); ?>><?php _e('User Profile', 'bd-client-suite'); ?></option>
                                <option value="custom" <?php selected($redirect_type, 'custom'); ?>><?php _e('Custom URL', 'bd-client-suite'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr class="bd-custom-url-row" style="<?php echo $redirect_type !== 'custom' ? 'display: none;' : ''; ?>">
                        <th scope="row"><?php _e('Custom URL', 'bd-client-suite'); ?></th>
                        <td>
                            <input type="url" name="redirects[role_redirects][<?php echo esc_attr($role_key); ?>][custom_url]" value="<?php echo esc_attr($custom_url); ?>" class="regular-text" placeholder="https://example.com/dashboard" />
                            <p class="description"><?php _e('Enter the full URL where this role should be redirected', 'bd-client-suite'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        <?php } ?>
    </div>
</div>

<h3><?php _e('Logout Settings', 'bd-client-suite'); ?></h3>
<table class="form-table">
    <tr>
        <th scope="row"><?php _e('After Logout Redirect', 'bd-client-suite'); ?></th>
        <td>
            <select name="redirects[logout_redirect]" id="bd-logout-redirect">
                <option value="home" <?php selected($logout_redirect, 'home'); ?>><?php _e('Home Page', 'bd-client-suite'); ?></option>
                <option value="login" <?php selected($logout_redirect, 'login'); ?>><?php _e('Login Page', 'bd-client-suite'); ?></option>
                <option value="custom" <?php selected($logout_redirect, 'custom'); ?>><?php _e('Custom URL', 'bd-client-suite'); ?></option>
            </select>
        </td>
    </tr>
    <tr id="bd-custom-logout-url" style="<?php echo $logout_redirect !== 'custom' ? 'display: none;' : ''; ?>">
        <th scope="row"><?php _e('Custom Logout URL', 'bd-client-suite'); ?></th>
        <td>
            <input type="url" name="redirects[custom_logout_url]" value="<?php echo esc_attr($custom_logout_url); ?>" class="regular-text" placeholder="https://example.com" />
            <p class="description"><?php _e('Enter the full URL where users should be redirected after logout', 'bd-client-suite'); ?></p>
        </td>
    </tr>
</table>

<style>
.bd-role-redirect-item {
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 20px;
    margin-bottom: 15px;
}

.bd-role-redirect-item h4 {
    margin-top: 0;
    color: #23282d;
}

.bd-role-key {
    font-weight: normal;
    color: #666;
    font-size: 0.9em;
}

.bd-role-redirect-item .form-table {
    margin-bottom: 0;
}

.bd-role-redirect-item .form-table th {
    width: 150px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Toggle role redirects section
    $('#bd-enable-role-redirects').on('change', function() {
        if ($(this).is(':checked')) {
            $('#bd-role-redirects-section').slideDown();
        } else {
            $('#bd-role-redirects-section').slideUp();
        }
    });
    
    // Toggle custom URL fields for role redirects
    $('.bd-redirect-type').on('change', function() {
        var $customRow = $(this).closest('table').find('.bd-custom-url-row');
        if ($(this).val() === 'custom') {
            $customRow.show();
        } else {
            $customRow.hide();
        }
    });
    
    // Toggle custom logout URL
    $('#bd-logout-redirect').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#bd-custom-logout-url').show();
        } else {
            $('#bd-custom-logout-url').hide();
        }
    });
});
</script>
