<?php
/**
 * BD Client Suite
 * 
 * Professional WordPress branding and customization plugin that transforms 
 * the WordPress admin experience for a polished, client-friendly environment.
 *
 * @package BD_Client_Suite
 * @version 1.1.0
 */

/*
Plugin Name: BD Client Suite
Plugin URI: https://github.com/buenedata/bd-client-suite
Description: Professional WordPress branding and customization plugin that transforms the admin experience. Create custom login pages, admin shortcuts, smart redirections, and client-friendly interfaces.
Version: 1.1.0
Author: Buene Data
Author URI: https://buenedata.no/
Update URI: https://github.com/buenedata/bd-client-suite
Text Domain: bd-client-suite
Domain Path: /languages
Requires at least: 5.0
Tested up to: 6.6
Requires PHP: 7.4
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Network: false
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('BD_CLIENT_SUITE_VERSION', '1.1.0');
define('BD_CLIENT_SUITE_FILE', __FILE__);
define('BD_CLIENT_SUITE_PATH', plugin_dir_path(__FILE__));
define('BD_CLIENT_SUITE_URL', plugin_dir_url(__FILE__));
define('BD_CLIENT_SUITE_BASENAME', plugin_basename(__FILE__));
define('BD_CLIENT_SUITE_SLUG', 'bd-client-suite');
define('BD_CLIENT_SUITE_DEBUG', true); // Enable debug mode

// Enable WordPress debug mode for this plugin
if (defined('BD_CLIENT_SUITE_DEBUG') && BD_CLIENT_SUITE_DEBUG) {
    error_log('BD Client Suite: Debug mode enabled');
}

// Autoload classes
spl_autoload_register(function ($class) {
    if (strpos($class, 'BD_Client_Suite') === 0) {
        $file = BD_CLIENT_SUITE_PATH . 'includes/class-' . strtolower(str_replace('_', '-', $class)) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

// Also include core files manually to ensure they're loaded
require_once BD_CLIENT_SUITE_PATH . 'includes/class-bd-client-suite-admin.php';
require_once BD_CLIENT_SUITE_PATH . 'includes/class-bd-client-suite-branding.php';
require_once BD_CLIENT_SUITE_PATH . 'includes/class-bd-client-suite-shortcuts.php';
require_once BD_CLIENT_SUITE_PATH . 'includes/class-bd-client-suite-login.php';
require_once BD_CLIENT_SUITE_PATH . 'includes/class-bd-client-suite-redirects.php';

// Initialize updater
if (is_admin()) {
    require_once BD_CLIENT_SUITE_PATH . 'includes/class-bd-updater.php';
    new BD_Plugin_Updater(BD_CLIENT_SUITE_FILE, 'buenedata', 'bd-client-suite');
}

// Load menu helper
require_once BD_CLIENT_SUITE_PATH . 'includes/bd-menu-helper.php';

/**
 * Main plugin class
 */
class BD_Client_Suite {
    
    /**
     * Plugin instance
     */
    private static $instance = null;
    
    /**
     * Plugin components
     */
    public $admin;
    public $branding;
    public $shortcuts;
    public $login;
    public $redirects;
    
    /**
     * Get plugin instance
     */
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->init_components();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        add_action('init', array($this, 'init'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('wp_loaded', array($this, 'loaded'));
        add_action('bd_plugin_registry_init', array($this, 'register_with_bd_system'));
    }
    
    /**
     * Initialize plugin components
     */
    private function init_components() {
        try {
            if (class_exists('BD_Client_Suite_Admin')) {
                $this->admin = new BD_Client_Suite_Admin();
            }
            
            if (class_exists('BD_Client_Suite_Branding')) {
                $this->branding = new BD_Client_Suite_Branding();
            }
            
            if (class_exists('BD_Client_Suite_Shortcuts')) {
                $this->shortcuts = new BD_Client_Suite_Shortcuts();
            }
            
            if (class_exists('BD_Client_Suite_Login')) {
                $this->login = new BD_Client_Suite_Login();
            }
            
            if (class_exists('BD_Client_Suite_Redirects')) {
                $this->redirects = new BD_Client_Suite_Redirects();
            }
        } catch (Exception $e) {
            error_log('BD Client Suite Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load textdomain
        load_plugin_textdomain('bd-client-suite', false, dirname(BD_CLIENT_SUITE_BASENAME) . '/languages');
        
        // Initialize components
        do_action('bd_client_suite_init', $this);
    }
    
    /**
     * Admin initialization
     */
    public function admin_init() {
        // Admin-specific initialization
        do_action('bd_client_suite_admin_init', $this);
    }
    
    /**
     * Plugin loaded
     */
    public function loaded() {
        do_action('bd_client_suite_loaded', $this);
    }
    
    /**
     * Register with BD plugin system
     */
    public function register_with_bd_system($registry) {
        if (method_exists($registry, 'register_plugin')) {
            $registry->register_plugin(array(
                'slug' => 'bd-client-suite',
                'name' => 'BD Client Suite',
                'description' => 'Professional WordPress branding and customization plugin for client-friendly admin experiences.',
                'icon' => '🎨',
                'version' => BD_CLIENT_SUITE_VERSION,
                'status' => 'auto',
                'admin_url' => admin_url('admin.php?page=bd-client-suite'),
                'settings_url' => admin_url('admin.php?page=bd-client-suite'),
                'plugin_file' => BD_CLIENT_SUITE_BASENAME,
                'docs_url' => 'https://buenedata.no/docs/bd-client-suite/',
                'support_url' => 'https://buenedata.no/support/'
            ));
        }
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create default options
        $default_options = array(
            'version' => BD_CLIENT_SUITE_VERSION,
            'branding' => array(
                'login_logo' => '',
                'admin_logo' => '',
                'color_scheme' => 'default',
                'custom_colors' => array(),
                'hide_wp_branding' => false
            ),
            'shortcuts' => array(
                'enabled' => true,
                'categories' => array(),
                'custom_shortcuts' => array()
            ),
            'login' => array(
                'custom_login_page' => false,
                'login_redirect' => 'default',
                'custom_login_css' => ''
            ),
            'redirects' => array(
                'after_login' => 'dashboard',
                'role_redirects' => array(),
                'custom_welcome_page' => ''
            ),
            'advanced' => array(
                'custom_css' => '',
                'custom_footer' => '',
                'hide_admin_bar' => false
            )
        );
        
        add_option('bd_client_suite_options', $default_options);
        
        // Create custom tables if needed
        $this->create_tables();
        
        // Set activation flag
        set_transient('bd_client_suite_activated', true, 30);
        
        do_action('bd_client_suite_activated');
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clean up transients
        delete_transient('bd_client_suite_activated');
        
        do_action('bd_client_suite_deactivated');
    }
    
    /**
     * Create custom database tables
     */
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Shortcuts table
        $table_name = $wpdb->prefix . 'bd_client_suite_shortcuts';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            url varchar(500) NOT NULL,
            icon varchar(100) DEFAULT '',
            category_id mediumint(9) DEFAULT NULL,
            user_roles text DEFAULT NULL,
            sort_order int(11) DEFAULT 0,
            active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY category_id (category_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Categories table
        $table_name = $wpdb->prefix . 'bd_client_suite_categories';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            slug varchar(255) NOT NULL UNIQUE,
            icon varchar(100) DEFAULT '',
            color varchar(7) DEFAULT '#667eea',
            sort_order int(11) DEFAULT 0,
            active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug)
        ) $charset_collate;";
        
        dbDelta($sql);
        
        // Insert default categories only if they don't exist
        $existing_categories = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}bd_client_suite_categories");
        
        if ($existing_categories == 0) {
            $wpdb->insert(
                $wpdb->prefix . 'bd_client_suite_categories',
                array(
                    'name' => 'General',
                    'slug' => 'general',
                    'icon' => '⚙️',
                    'color' => '#667eea',
                    'sort_order' => 1
                )
            );
            
            $wpdb->insert(
                $wpdb->prefix . 'bd_client_suite_categories',
                array(
                    'name' => 'Content',
                    'slug' => 'content',
                    'icon' => '📝',
                    'color' => '#10b981',
                    'sort_order' => 2
                )
            );
            
            $wpdb->insert(
                $wpdb->prefix . 'bd_client_suite_categories',
                array(
                    'name' => 'Media',
                    'slug' => 'media',
                    'icon' => '🖼️',
                    'color' => '#f59e0b',
                    'sort_order' => 3
                )
            );
        }
    }
    
    /**
     * Get plugin option
     */
    public function get_option($key = '', $default = false) {
        $options = get_option('bd_client_suite_options', array());
        
        if (empty($key)) {
            return $options;
        }
        
        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);
            $value = $options;
            
            foreach ($keys as $k) {
                if (!isset($value[$k])) {
                    return $default;
                }
                $value = $value[$k];
            }
            
            return $value;
        }
        
        return isset($options[$key]) ? $options[$key] : $default;
    }
    
    /**
     * Update plugin option
     */
    public function update_option($key, $value) {
        $options = get_option('bd_client_suite_options', array());
        
        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);
            $current = &$options;
            
            foreach ($keys as $k) {
                if (!isset($current[$k]) || !is_array($current[$k])) {
                    $current[$k] = array();
                }
                $current = &$current[$k];
            }
            
            $current = $value;
        } else {
            $options[$key] = $value;
        }
        
        return update_option('bd_client_suite_options', $options);
    }
}

/**
 * Initialize the plugin
 */
function bd_client_suite() {
    return BD_Client_Suite::instance();
}

// Start the plugin
bd_client_suite();
