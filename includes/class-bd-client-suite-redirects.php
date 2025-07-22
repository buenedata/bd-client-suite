<?php
/**
 * BD Client Suite Redirects Class
 * 
 * Handles login redirects and custom welcome pages
 *
 * @package BD_Client_Suite
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BD_Client_Suite_Redirects {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_filter('login_redirect', array($this, 'custom_login_redirect'), 10, 3);
        add_action('wp_login', array($this, 'after_login_actions'), 10, 2);
        add_action('init', array($this, 'handle_logout_redirect'));
        add_action('template_redirect', array($this, 'handle_welcome_page'));
    }
    
    /**
     * Custom login redirect
     */
    public function custom_login_redirect($redirect_to, $request, $user) {
        // Check if user login was successful
        if (!is_wp_error($user)) {
            $redirect_settings = bd_client_suite()->get_option('redirects', array());
            $enable_role_redirects = isset($redirect_settings['enable_role_redirects']) ? $redirect_settings['enable_role_redirects'] : false;
            
            if ($enable_role_redirects) {
                $custom_redirect = $this->get_user_redirect($user);
                
                if (!empty($custom_redirect)) {
                    return $custom_redirect;
                }
            }
        }
        
        return $redirect_to;
    }
    
    /**
     * Get redirect URL for user based on role
     */
    private function get_user_redirect($user) {
        $redirect_settings = bd_client_suite()->get_option('redirects', array());
        $role_redirects = isset($redirect_settings['role_redirects']) ? $redirect_settings['role_redirects'] : array();
        
        // Get user's primary role
        $user_roles = $user->roles;
        
        if (empty($user_roles)) {
            return '';
        }
        
        // Check for role-specific redirect (prioritize first role)
        foreach ($user_roles as $role) {
            if (isset($role_redirects[$role]) && is_array($role_redirects[$role])) {
                $redirect_config = $role_redirects[$role];
                $redirect_type = isset($redirect_config['type']) ? $redirect_config['type'] : 'dashboard';
                
                switch ($redirect_type) {
                    case 'dashboard':
                        return admin_url();
                        
                    case 'admin':
                        return admin_url('index.php');
                        
                    case 'home':
                        return home_url();
                        
                    case 'profile':
                        return admin_url('profile.php');
                        
                    case 'custom':
                        $custom_url = isset($redirect_config['custom_url']) ? $redirect_config['custom_url'] : '';
                        if (!empty($custom_url)) {
                            return esc_url($custom_url);
                        }
                        break;
                }
            }
        }
        
        return '';
    }
    
    /**
     * Process redirect URL
     */
    private function process_redirect_url($redirect_type, $user) {
        switch ($redirect_type) {
            case 'dashboard':
                return admin_url();
                
            case 'profile':
                return admin_url('profile.php');
                
            case 'welcome':
                $welcome_page = bd_client_suite()->get_option('redirects.custom_welcome_page', 0);
                if ($welcome_page) {
                    return get_permalink($welcome_page);
                }
                return admin_url();
                
            case 'home':
                return home_url();
                
            case 'custom':
                $custom_url = bd_client_suite()->get_option('redirects.custom_url', '');
                return !empty($custom_url) ? $custom_url : admin_url();
                
            case 'last_page':
                $last_page = get_user_meta($user->ID, 'bd_last_admin_page', true);
                return !empty($last_page) ? $last_page : admin_url();
                
            default:
                // Check if it's a custom URL
                if (filter_var($redirect_type, FILTER_VALIDATE_URL)) {
                    return $redirect_type;
                }
                return admin_url();
        }
    }
    
    /**
     * Actions after login
     */
    public function after_login_actions($user_login, $user) {
        // Store login timestamp
        update_user_meta($user->ID, 'bd_last_login', current_time('timestamp'));
        
        // Store login count
        $login_count = get_user_meta($user->ID, 'bd_login_count', true);
        update_user_meta($user->ID, 'bd_login_count', absint($login_count) + 1);
        
        // Set welcome flag for first-time users
        if (absint($login_count) === 0) {
            update_user_meta($user->ID, 'bd_show_welcome', true);
        }
        
        // Log login for security (if enabled)
        if (bd_client_suite()->get_option('security.log_logins', false)) {
            $this->log_user_login($user);
        }
        
        do_action('bd_client_suite_after_login', $user);
    }
    
    /**
     * Handle logout redirect
     */
    public function handle_logout_redirect() {
        if (isset($_GET['action']) && $_GET['action'] === 'logout') {
            $redirect_settings = bd_client_suite()->get_option('redirects', array());
            $logout_redirect = isset($redirect_settings['logout_redirect']) ? $redirect_settings['logout_redirect'] : 'home';
            
            if ($logout_redirect !== 'default') {
                add_filter('logout_redirect', function($redirect_to) use ($logout_redirect, $redirect_settings) {
                    switch ($logout_redirect) {
                        case 'home':
                            return home_url();
                        case 'login':
                            return wp_login_url();
                        case 'custom':
                            $custom_url = isset($redirect_settings['custom_logout_url']) ? $redirect_settings['custom_logout_url'] : '';
                            return !empty($custom_url) ? esc_url($custom_url) : home_url();
                        default:
                            return $redirect_to;
                    }
                });
            }
        }
    }
    
    /**
     * Handle welcome page display
     */
    public function handle_welcome_page() {
        if (!is_admin() || !is_user_logged_in()) {
            return;
        }
        
        $current_user = wp_get_current_user();
        $show_welcome = get_user_meta($current_user->ID, 'bd_show_welcome', true);
        
        if ($show_welcome && !isset($_GET['bd_welcome_seen'])) {
            $welcome_page = bd_client_suite()->get_option('redirects.custom_welcome_page', 0);
            
            if ($welcome_page && get_post_status($welcome_page) === 'publish') {
                // Mark welcome as seen
                update_user_meta($current_user->ID, 'bd_show_welcome', false);
                
                // Redirect to welcome page
                wp_redirect(add_query_arg('bd_welcome', '1', get_permalink($welcome_page)));
                exit;
            }
        }
    }
    
    /**
     * Log user login
     */
    private function log_user_login($user) {
        $log_data = array(
            'user_id' => $user->ID,
            'username' => $user->user_login,
            'ip_address' => $this->get_client_ip(),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '',
            'login_time' => current_time('mysql'),
            'session_token' => wp_get_session_token()
        );
        
        // Store in user meta (last 10 logins)
        $login_history = get_user_meta($user->ID, 'bd_login_history', true);
        if (!is_array($login_history)) {
            $login_history = array();
        }
        
        array_unshift($login_history, $log_data);
        $login_history = array_slice($login_history, 0, 10); // Keep only last 10
        
        update_user_meta($user->ID, 'bd_login_history', $login_history);
        
        do_action('bd_client_suite_login_logged', $log_data);
    }
    
    /**
     * Get client IP address
     */
    private function get_client_ip() {
        $ip_sources = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );
        
        foreach ($ip_sources as $source) {
            if (!empty($_SERVER[$source])) {
                $ip = $_SERVER[$source];
                
                // Handle comma-separated IPs
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                // Validate IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    }
    
    /**
     * Track admin page visits for last page redirect
     */
    public function track_admin_pages() {
        if (is_admin() && !wp_doing_ajax() && !wp_doing_cron()) {
            $current_user = wp_get_current_user();
            
            if ($current_user->ID) {
                $current_url = admin_url('admin.php?' . $_SERVER['QUERY_STRING']);
                update_user_meta($current_user->ID, 'bd_last_admin_page', $current_url);
            }
        }
    }
    
    /**
     * Get redirect options for settings
     */
    public function get_redirect_options() {
        return array(
            'dashboard' => __('WordPress Dashboard', 'bd-client-suite'),
            'profile' => __('User Profile', 'bd-client-suite'),
            'welcome' => __('Custom Welcome Page', 'bd-client-suite'),
            'home' => __('Site Homepage', 'bd-client-suite'),
            'last_page' => __('Last Visited Admin Page', 'bd-client-suite'),
            'custom' => __('Custom URL', 'bd-client-suite')
        );
    }
    
    /**
     * Get logout redirect options
     */
    public function get_logout_redirect_options() {
        return array(
            'default' => __('WordPress Default', 'bd-client-suite'),
            'home' => __('Site Homepage', 'bd-client-suite'),
            'login' => __('Login Page', 'bd-client-suite'),
            'custom' => __('Custom URL', 'bd-client-suite')
        );
    }
    
    /**
     * Get user roles for redirect settings
     */
    public function get_user_roles() {
        global $wp_roles;
        
        $roles = array();
        
        foreach ($wp_roles->roles as $role_key => $role_data) {
            $roles[$role_key] = $role_data['name'];
        }
        
        return $roles;
    }
    
    /**
     * Get login statistics for current user
     */
    public function get_user_login_stats($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $last_login = get_user_meta($user_id, 'bd_last_login', true);
        $login_count = get_user_meta($user_id, 'bd_login_count', true);
        $login_history = get_user_meta($user_id, 'bd_login_history', true);
        
        return array(
            'last_login' => $last_login ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $last_login) : __('Never', 'bd-client-suite'),
            'login_count' => absint($login_count),
            'login_history' => is_array($login_history) ? $login_history : array()
        );
    }
}
