<?php
/**
 * BD Client Suite Branding Class
 * 
 * Handles WordPress branding customization and white-labeling
 *
 * @package BD_Client_Suite
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BD_Client_Suite_Branding {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_branding_styles'));
        add_action('login_enqueue_scripts', array($this, 'enqueue_login_styles'));
        add_filter('login_headerurl', array($this, 'custom_login_logo_url'));
        add_filter('login_headertext', array($this, 'custom_login_logo_title'));
        add_action('admin_bar_menu', array($this, 'customize_admin_bar'), 999);
        add_filter('admin_footer_text', array($this, 'custom_admin_footer'));
        add_action('wp_before_admin_bar_render', array($this, 'hide_wp_logo'));
    }
    
    /**
     * Initialize branding
     */
    public function init() {
        // Remove WordPress branding if enabled
        if (bd_client_suite()->get_option('branding.hide_wp_branding', false)) {
            $this->remove_wp_branding();
        }
        
        // Apply custom color scheme
        $this->apply_color_scheme();
    }
    
    /**
     * Enqueue branding styles for admin
     */
    public function enqueue_branding_styles() {
        $custom_css = bd_client_suite()->get_option('advanced.custom_css', '');
        $branding_options = bd_client_suite()->get_option('branding', array());
        
        // Generate dynamic CSS
        $dynamic_css = $this->generate_dynamic_css($branding_options);
        
        if (!empty($dynamic_css) || !empty($custom_css)) {
            wp_add_inline_style('admin-menu', $dynamic_css . "\n" . $custom_css);
        }
    }
    
    /**
     * Enqueue login styles
     */
    public function enqueue_login_styles() {
        $login_logo = bd_client_suite()->get_option('branding.login_logo', '');
        $custom_login_css = bd_client_suite()->get_option('login.custom_login_css', '');
        
        if (!empty($login_logo) || !empty($custom_login_css)) {
            $login_css = $this->generate_login_css($login_logo);
            wp_add_inline_style('login', $login_css . "\n" . $custom_login_css);
        }
    }
    
    /**
     * Generate dynamic CSS based on branding options
     */
    private function generate_dynamic_css($options) {
        $css = '';
        
        // Custom colors
        if (!empty($options['custom_colors'])) {
            $colors = $options['custom_colors'];
            
            if (!empty($colors['primary'])) {
                $css .= "
                .wp-core-ui .button-primary {
                    background: {$colors['primary']} !important;
                    border-color: {$colors['primary']} !important;
                }
                
                #adminmenu .wp-has-current-submenu .wp-submenu .wp-submenu-head,
                #adminmenu .wp-menu-arrow,
                #adminmenu .wp-menu-arrow div,
                #adminmenu li.current a.menu-top,
                #adminmenu li.wp-has-current-submenu a.wp-has-current-submenu {
                    background: {$colors['primary']} !important;
                }
                
                .wp-core-ui .button-primary:hover {
                    background: " . $this->darken_color($colors['primary'], 10) . " !important;
                }";
            }
            
            if (!empty($colors['accent'])) {
                $css .= "
                .wp-core-ui .button-secondary:hover,
                .wp-core-ui .button:hover {
                    border-color: {$colors['accent']} !important;
                    color: {$colors['accent']} !important;
                }";
            }
        }
        
        // Admin logo
        $admin_logo = bd_client_suite()->get_option('branding.admin_logo', '');
        if (!empty($admin_logo)) {
            $css .= "
            #wpadminbar #wp-admin-bar-wp-logo > .ab-item .ab-icon {
                background-image: url('{$admin_logo}') !important;
                background-size: contain !important;
                background-repeat: no-repeat !important;
                background-position: center !important;
            }
            
            #wpadminbar #wp-admin-bar-wp-logo > .ab-item .ab-icon:before {
                content: '' !important;
            }";
        }
        
        return $css;
    }
    
    /**
     * Generate login page CSS
     */
    private function generate_login_css($logo_url) {
        if (empty($logo_url)) {
            return '';
        }
        
        return "
        .login h1 a {
            background-image: url('{$logo_url}') !important;
            background-size: contain !important;
            background-repeat: no-repeat !important;
            width: 100% !important;
            height: 80px !important;
        }
        
        .login form {
            border-radius: 8px !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
        }
        
        .login .button-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            border: none !important;
            border-radius: 6px !important;
            transition: all 0.3s ease !important;
        }
        
        .login .button-primary:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%) !important;
            transform: translateY(-1px) !important;
        }";
    }
    
    /**
     * Custom login logo URL
     */
    public function custom_login_logo_url() {
        return home_url();
    }
    
    /**
     * Custom login logo title
     */
    public function custom_login_logo_title() {
        return get_bloginfo('name');
    }
    
    /**
     * Customize admin bar
     */
    public function customize_admin_bar($wp_admin_bar) {
        $admin_logo = bd_client_suite()->get_option('branding.admin_logo', '');
        
        if (!empty($admin_logo)) {
            $wp_admin_bar->add_node(array(
                'id' => 'custom-logo',
                'title' => '<img src="' . esc_url($admin_logo) . '" style="height: 20px; width: auto; vertical-align: middle;">',
                'href' => admin_url(),
                'meta' => array('class' => 'bd-custom-logo')
            ));
        }
    }
    
    /**
     * Custom admin footer
     */
    public function custom_admin_footer() {
        // Don't override footer on BD Client Suite settings pages
        if (isset($_GET['page']) && $_GET['page'] === 'bd-client-suite') {
            return false;
        }
        
        $custom_footer = bd_client_suite()->get_option('advanced.custom_footer', '');
        
        if (!empty($custom_footer)) {
            return $custom_footer;
        }
        
        return sprintf(
            __('Powered by <a href="%s" target="_blank">WordPress</a> | Customized with <a href="%s" target="_blank">BD Client Suite</a>', 'bd-client-suite'),
            'https://wordpress.org/',
            'https://buenedata.no/bd-client-suite/'
        );
    }
    
    /**
     * Hide WordPress logo from admin bar
     */
    public function hide_wp_logo() {
        if (bd_client_suite()->get_option('branding.hide_wp_branding', false)) {
            global $wp_admin_bar;
            $wp_admin_bar->remove_menu('wp-logo');
        }
    }
    
    /**
     * Remove WordPress branding
     */
    private function remove_wp_branding() {
        // Remove version info
        add_filter('the_generator', '__return_empty_string');
        
        // Remove WordPress welcome panel
        remove_action('welcome_panel', 'wp_welcome_panel');
        
        // Remove only WordPress news dashboard widgets, not all dashboard widgets
        add_action('wp_dashboard_setup', function() {
            // Only remove WordPress-specific widgets, not custom ones
            remove_meta_box('dashboard_primary', 'dashboard', 'side');   // WordPress Events and News
            remove_meta_box('dashboard_secondary', 'dashboard', 'side'); // Other WordPress News
        });
        
        // Hide update notifications for non-admins
        if (!current_user_can('update_core')) {
            add_action('init', function() {
                remove_action('init', 'wp_version_check');
            }, 2);
            
            add_filter('pre_option_update_core', '__return_null');
            add_filter('pre_transient_update_core', '__return_null');
        }
    }
    
    /**
     * Apply custom color scheme
     */
    private function apply_color_scheme() {
        $scheme = bd_client_suite()->get_option('branding.color_scheme', 'default');
        
        if ($scheme !== 'default') {
            add_action('admin_enqueue_scripts', function() use ($scheme) {
                wp_enqueue_style(
                    'bd-color-scheme-' . $scheme,
                    BD_CLIENT_SUITE_URL . 'assets/css/color-schemes/' . $scheme . '.css',
                    array(),
                    BD_CLIENT_SUITE_VERSION
                );
            });
        }
    }
    
    /**
     * Darken a hex color
     */
    private function darken_color($hex, $percent) {
        $hex = str_replace('#', '', $hex);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        $r = max(0, min(255, $r - ($r * $percent / 100)));
        $g = max(0, min(255, $g - ($g * $percent / 100)));
        $b = max(0, min(255, $b - ($b * $percent / 100)));
        
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
    
    /**
     * Get available color schemes
     */
    public function get_color_schemes() {
        return array(
            'default' => __('Default WordPress', 'bd-client-suite'),
            'modern-blue' => __('Modern Blue', 'bd-client-suite'),
            'professional-gray' => __('Professional Gray', 'bd-client-suite'),
            'elegant-purple' => __('Elegant Purple', 'bd-client-suite'),
            'clean-green' => __('Clean Green', 'bd-client-suite'),
            'minimal-dark' => __('Minimal Dark', 'bd-client-suite')
        );
    }
}
