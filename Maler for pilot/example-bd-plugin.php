<?php
/**
 * BD Test Plugin - Example Integration
 * 
 * This is an example plugin showing how to integrate with the BD unified menu system.
 * This file can be used as a template for other BD plugins.
 *
 * @package BD_Test_Plugin
 * @version 1.0.0
 */

/*
Plugin Name: BD Test Plugin
Description: Example plugin showing BD menu integration. Use this as a template for other BD plugins.
Version: 1.0.0
Author: Buene Data
Author URI: https://buenedata.no/
Text Domain: bd-test-plugin
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

define('BD_TEST_PLUGIN_VERSION', '1.0.0');
define('BD_TEST_PLUGIN_FILE', __FILE__);

/**
 * Example BD Plugin Class
 */
class BD_Test_Plugin {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('bd_plugin_registry_init', array($this, 'register_with_bd_system'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }
    
    public function init() {
        // Plugin initialization
    }
    
    /**
     * Register this plugin with the BD unified system
     */
    public function register_with_bd_system($registry) {
        $registry->register_plugin(array(
            'slug' => 'bd-test-plugin',
            'name' => 'BD Test Plugin',
            'description' => 'Example plugin demonstrating BD menu integration and plugin registry system.',
            'icon' => '🧪', // Test tube emoji
            'version' => BD_TEST_PLUGIN_VERSION,
            'status' => 'auto', // Auto-detect status
            'admin_url' => admin_url('admin.php?page=bd-test-plugin'),
            'plugin_file' => plugin_basename(__FILE__),
            'docs_url' => 'https://buenedata.no/docs/bd-test-plugin/',
            'support_url' => 'https://buenedata.no/support/'
        ));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Check if BD main menu exists
        $bd_menu_exists = false;
        global $menu;
        if (is_array($menu)) {
            foreach ($menu as $menu_item) {
                if (isset($menu_item[2]) && $menu_item[2] === 'buene-data') {
                    $bd_menu_exists = true;
                    break;
                }
            }
        }
        
        // Create BD main menu if it doesn't exist
        // (This usually happens when BD CleanDash is installed, but this is a fallback)
        if (!$bd_menu_exists) {
            add_menu_page(
                __('Buene Data', 'bd-test-plugin'),
                __('Buene Data', 'bd-test-plugin'),
                'manage_options',
                'buene-data',
                array($this, 'render_overview_fallback'),
                'data:image/svg+xml;base64,' . base64_encode('<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 2L3 7V18H7V14H13V18H17V7L10 2Z" fill="currentColor"/></svg>'),
                58.5
            );
        }
        
        // Add this plugin as submenu
        add_submenu_page(
            'buene-data',
            __('BD Test Plugin', 'bd-test-plugin'),
            __('🧪 Test Plugin', 'bd-test-plugin'),
            'manage_options',
            'bd-test-plugin',
            array($this, 'render_admin_page')
        );
    }
    
    /**
     * Render the overview page (fallback if BD CleanDash isn't active)
     */
    public function render_overview_fallback() {
        // Check if BD CleanDash is available to handle this
        if (class_exists('BD_Plugin_Registry')) {
            // Redirect to proper overview if available
            echo '<script>window.location.href = "' . admin_url('admin.php?page=buene-data') . '";</script>';
            return;
        }
        
        // Simple fallback overview
        echo '<div class="wrap">';
        echo '<h1>' . __('Buene Data Plugins', 'bd-test-plugin') . '</h1>';
        echo '<p>' . __('This is a basic overview. Install BD CleanDash for a better experience.', 'bd-test-plugin') . '</p>';
        
        // List active BD plugins
        if (function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
            $all_plugins = get_plugins();
            $bd_plugins = array();
            
            foreach ($all_plugins as $plugin_file => $plugin_data) {
                if (stripos($plugin_data['Name'], 'BD ') === 0 || 
                    stripos($plugin_data['Name'], 'Buene Data') !== false) {
                    $bd_plugins[] = array(
                        'name' => $plugin_data['Name'],
                        'version' => $plugin_data['Version'],
                        'active' => is_plugin_active($plugin_file)
                    );
                }
            }
            
            if (!empty($bd_plugins)) {
                echo '<h2>' . __('Installed BD Plugins', 'bd-test-plugin') . '</h2>';
                echo '<ul>';
                foreach ($bd_plugins as $plugin) {
                    $status = $plugin['active'] ? __('Active', 'bd-test-plugin') : __('Inactive', 'bd-test-plugin');
                    echo '<li><strong>' . esc_html($plugin['name']) . '</strong> v' . esc_html($plugin['version']) . ' - ' . $status . '</li>';
                }
                echo '</ul>';
            }
        }
        
        echo '</div>';
    }
    
    /**
     * Render the plugin's admin page
     */
    public function render_admin_page() {
        echo '<div class="wrap">';
        echo '<h1>' . __('BD Test Plugin', 'bd-test-plugin') . '</h1>';
        echo '<p>' . __('This is an example BD plugin showing how to integrate with the unified BD menu system.', 'bd-test-plugin') . '</p>';
        
        echo '<h2>' . __('Integration Features', 'bd-test-plugin') . '</h2>';
        echo '<ul>';
        echo '<li>' . __('Registers with BD Plugin Registry', 'bd-test-plugin') . '</li>';
        echo '<li>' . __('Appears in unified BD overview page', 'bd-test-plugin') . '</li>';
        echo '<li>' . __('Uses consistent BD menu structure', 'bd-test-plugin') . '</li>';
        echo '<li>' . __('Provides fallback for standalone usage', 'bd-test-plugin') . '</li>';
        echo '</ul>';
        
        echo '<h2>' . __('Registry Status', 'bd-test-plugin') . '</h2>';
        if (class_exists('BD_Plugin_Registry')) {
            $registry = BD_Plugin_Registry::get_instance();
            $plugin_data = $registry->get_plugin('bd-test-plugin');
            
            if ($plugin_data) {
                echo '<p style="color: green;">' . __('✅ Successfully registered with BD Plugin Registry', 'bd-test-plugin') . '</p>';
                echo '<p><strong>' . __('Status:', 'bd-test-plugin') . '</strong> ' . esc_html($plugin_data['status']) . '</p>';
                echo '<p><strong>' . __('Name:', 'bd-test-plugin') . '</strong> ' . esc_html($plugin_data['name']) . '</p>';
                echo '<p><strong>' . __('Version:', 'bd-test-plugin') . '</strong> ' . esc_html($plugin_data['version']) . '</p>';
            } else {
                echo '<p style="color: orange;">' . __('⚠️ Not registered with BD Plugin Registry', 'bd-test-plugin') . '</p>';
            }
        } else {
            echo '<p style="color: red;">' . __('❌ BD Plugin Registry not available (BD CleanDash not active)', 'bd-test-plugin') . '</p>';
        }
        
        echo '<p><a href="' . admin_url('admin.php?page=buene-data') . '" class="button button-primary">' . __('View BD Overview', 'bd-test-plugin') . '</a></p>';
        
        echo '</div>';
    }
}

// Initialize the plugin
new BD_Test_Plugin();
