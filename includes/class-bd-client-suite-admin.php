<?php

require_once __DIR__ . '/bd-menu-helper.php';

/**
 * BD Client Suite Admin Class
 * 
 * Handles admin interface, menus, and settings pages
 *
 * @package BD_Client_Suite
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BD_Client_Suite_Admin {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Debug: Log that admin class is being constructed
        error_log('BD Client Suite Admin: Constructing admin class');
        file_put_contents(BD_CLIENT_SUITE_PATH . 'debug_constructor.log', date('Y-m-d H:i:s') . " - Admin class constructed\n", FILE_APPEND);
        
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_init', array($this, 'init_settings'));
        add_action('wp_ajax_bd_client_suite_save_settings', array($this, 'ajax_save_settings'));
        // New AJAX handlers for shortcuts and categories
        add_action('wp_ajax_bd_client_suite_save_category', array($this, 'ajax_save_category'));
        add_action('wp_ajax_bd_client_suite_delete_category', array($this, 'ajax_delete_category'));
        add_action('wp_ajax_bd_client_suite_save_shortcut', array($this, 'ajax_save_shortcut'));
        add_action('wp_ajax_bd_client_suite_delete_shortcut', array($this, 'ajax_delete_shortcut'));
        add_action('admin_notices', array($this, 'admin_notices'));
        
        // Debug: Log that AJAX actions have been registered
        error_log('BD Client Suite Admin: AJAX actions registered');
        file_put_contents(BD_CLIENT_SUITE_PATH . 'debug_constructor.log', date('Y-m-d H:i:s') . " - Actions registered\n", FILE_APPEND);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Bruk BD menu helper for konsistent meny-håndtering på tvers av alle BD plugins
        bd_add_buene_data_menu(
            __('BD Client Suite', 'bd-client-suite'),
            'bd-client-suite',
            array($this, 'render_settings_page'),
            '🎨'
        );
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        // Debug: Log which hook is being called
        error_log('BD Client Suite: enqueue_admin_scripts called with hook: ' . $hook);
        
        // Create a debug file to confirm this function is being called
        file_put_contents(BD_CLIENT_SUITE_PATH . 'debug_enqueue.log', date('Y-m-d H:i:s') . " - Hook: $hook\n", FILE_APPEND);
        
        // Load on all admin pages for now to debug
        // TODO: Restrict to BD pages only after fixing tabs
        error_log('BD Client Suite: Enqueueing admin assets for hook: ' . $hook);
        
        // Enqueue WordPress media uploader
        wp_enqueue_media();
        
        // Debug file paths
        $admin_css_path = BD_CLIENT_SUITE_URL . 'assets/css/admin.css';
        $shortcuts_css_path = BD_CLIENT_SUITE_URL . 'assets/css/shortcuts.css';
        $admin_js_path = BD_CLIENT_SUITE_URL . 'assets/js/admin.js';
        
        error_log('BD Client Suite: Admin CSS path: ' . $admin_css_path);
        error_log('BD Client Suite: Shortcuts CSS path: ' . $shortcuts_css_path);
        error_log('BD Client Suite: Admin JS path: ' . $admin_js_path);
        
        // Log that we're actually enqueueing
        file_put_contents(BD_CLIENT_SUITE_PATH . 'debug_enqueue.log', date('Y-m-d H:i:s') . " - Enqueueing CSS/JS\n", FILE_APPEND);
        
        wp_enqueue_style(
            'bd-client-suite-admin',
            $admin_css_path,
            array(),
            BD_CLIENT_SUITE_VERSION . '.' . time() // Add timestamp to force cache refresh
        );
        
        wp_enqueue_style(
            'bd-client-suite-shortcuts',
            $shortcuts_css_path,
            array(),
            BD_CLIENT_SUITE_VERSION . '.' . time() // Add timestamp to force cache refresh
        );
        
        wp_enqueue_script(
            'bd-client-suite-admin',
            $admin_js_path,
            array('jquery', 'media-upload', 'media-views'),
            BD_CLIENT_SUITE_VERSION . '.' . time(), // Add timestamp to force cache refresh
            true
        );
        
        wp_localize_script('bd-client-suite-admin', 'bdClientSuite', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bd_client_suite_nonce'),
            'strings' => array(
                'saving' => __('Saving...', 'bd-client-suite'),
                'saved' => __('Settings saved!', 'bd-client-suite'),
                'error' => __('Error saving settings.', 'bd-client-suite'),
                'confirm_delete' => __('Are you sure you want to delete this item?', 'bd-client-suite'),
                'addShortcut' => __('Add Shortcut', 'bd-client-suite'),
                'editShortcut' => __('Edit Shortcut', 'bd-client-suite'),
                'addCategory' => __('Add Category', 'bd-client-suite'),
                'editCategory' => __('Edit Category', 'bd-client-suite'),
                'confirmDeleteShortcut' => __('Are you sure you want to delete this shortcut?', 'bd-client-suite'),
                'confirmDeleteCategory' => __('Are you sure you want to delete this category? This will also affect any shortcuts in this category.', 'bd-client-suite'),
                'selectMedia' => __('Select Media', 'bd-client-suite'),
                'useMedia' => __('Use This Media', 'bd-client-suite')
            )
        ));
        
        // Enqueue color picker separately and safely
        if (current_user_can('manage_options')) {
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
        }
    }
    
    /**
     * Initialize settings
     */
    public function init_settings() {
        register_setting('bd_client_suite_settings', 'bd_client_suite_options');
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        // Debug: Check if we reach this method
        error_log('BD Client Suite: render_settings_page called');
        
        // Set a default tab if not provided, and make sure it is available in the template
        $current_tab = isset($_GET['tab']) && $_GET['tab'] !== '' ? sanitize_text_field($_GET['tab']) : 'branding';
        // Make $current_tab available in the template scope
        if (!isset($current_tab)) {
            $current_tab = 'branding';
        }
        
        // Debug: Check if template file exists
        $template_path = BD_CLIENT_SUITE_PATH . 'templates/admin/settings-page.php';
        error_log('BD Client Suite: Template path: ' . $template_path);
        error_log('BD Client Suite: Template exists: ' . (file_exists($template_path) ? 'YES' : 'NO'));
        
        if (!file_exists($template_path)) {
            echo '<div class="wrap"><h1>BD Client Suite</h1><p>Error: Template file not found at: ' . esc_html($template_path) . '</p></div>';
            return;
        }
        
        try {
            // Switch back to main template now that we know JS works
            include $template_path;
            // include BD_CLIENT_SUITE_PATH . 'templates/admin/test-settings-page.php';
        } catch (Exception $e) {
            echo '<div class="wrap"><h1>BD Client Suite</h1><p>Error loading template: ' . esc_html($e->getMessage()) . '</p></div>';
            error_log('BD Client Suite: Template error: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX save settings
     */
    public function ajax_save_settings() {
        check_ajax_referer('bd_client_suite_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions.', 'bd-client-suite'));
        }
        
        $settings = isset($_POST['settings']) ? $_POST['settings'] : array();
        $tab = isset($_POST['tab']) ? sanitize_text_field($_POST['tab']) : '';
        
        // Sanitize settings based on tab
        $sanitized_settings = $this->sanitize_settings($settings, $tab);
        
        // Update options
        $current_options = get_option('bd_client_suite_options', array());
        $current_options[$tab] = $sanitized_settings;
        
        $updated = update_option('bd_client_suite_options', $current_options);
        
        if ($updated) {
            wp_send_json_success(array(
                'message' => __('Settings saved successfully!', 'bd-client-suite')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Error saving settings.', 'bd-client-suite')
            ));
        }
    }
    
    /**
     * Sanitize settings
     */
    private function sanitize_settings($settings, $tab) {
        $sanitized = array();
        
        switch ($tab) {
            case 'branding':
                $sanitized['login_logo'] = isset($settings['login_logo']) ? esc_url_raw($settings['login_logo']) : '';
                $sanitized['admin_logo'] = isset($settings['admin_logo']) ? esc_url_raw($settings['admin_logo']) : '';
                $sanitized['color_scheme'] = isset($settings['color_scheme']) ? sanitize_text_field($settings['color_scheme']) : 'default';
                $sanitized['hide_wp_branding'] = isset($settings['hide_wp_branding']) ? (bool) $settings['hide_wp_branding'] : false;
                $sanitized['custom_colors'] = isset($settings['custom_colors']) ? array_map('sanitize_hex_color', $settings['custom_colors']) : array();
                break;
                
            case 'shortcuts':
                $sanitized['enabled'] = isset($settings['enabled']) ? (bool) $settings['enabled'] : true;
                $sanitized['show_in_dashboard'] = isset($settings['show_in_dashboard']) ? (bool) $settings['show_in_dashboard'] : true;
                $sanitized['categories'] = isset($settings['categories']) ? array_map('sanitize_text_field', $settings['categories']) : array();
                break;
                
            case 'login':
                $sanitized['custom_login_page'] = isset($settings['custom_login_page']) ? (bool) $settings['custom_login_page'] : false;
                $sanitized['background_image'] = isset($settings['background_image']) ? esc_url_raw($settings['background_image']) : '';
                $sanitized['background_color'] = isset($settings['background_color']) ? sanitize_hex_color($settings['background_color']) : '#f1f1f1';
                $sanitized['form_background'] = isset($settings['form_background']) ? sanitize_hex_color($settings['form_background']) : '#ffffff';
                $sanitized['form_border_color'] = isset($settings['form_border_color']) ? sanitize_hex_color($settings['form_border_color']) : '#dddddd';
                $sanitized['button_color'] = isset($settings['button_color']) ? sanitize_hex_color($settings['button_color']) : '#0073aa';
                $sanitized['custom_logo'] = isset($settings['custom_logo']) ? esc_url_raw($settings['custom_logo']) : '';
                $sanitized['logo_width'] = isset($settings['logo_width']) ? absint($settings['logo_width']) : 80;
                $sanitized['logo_height'] = isset($settings['logo_height']) ? absint($settings['logo_height']) : 80;
                break;
                
            case 'redirects':
                $sanitized['enable_role_redirects'] = isset($settings['enable_role_redirects']) ? (bool) $settings['enable_role_redirects'] : false;
                $sanitized['logout_redirect'] = isset($settings['logout_redirect']) ? sanitize_text_field($settings['logout_redirect']) : 'home';
                $sanitized['custom_logout_url'] = isset($settings['custom_logout_url']) ? esc_url_raw($settings['custom_logout_url']) : '';
                
                // Sanitize role redirects
                if (isset($settings['role_redirects']) && is_array($settings['role_redirects'])) {
                    $sanitized['role_redirects'] = array();
                    foreach ($settings['role_redirects'] as $role => $redirect_data) {
                        if (is_array($redirect_data)) {
                            $sanitized['role_redirects'][sanitize_text_field($role)] = array(
                                'type' => sanitize_text_field($redirect_data['type']),
                                'custom_url' => isset($redirect_data['custom_url']) ? esc_url_raw($redirect_data['custom_url']) : ''
                            );
                        }
                    }
                } else {
                    $sanitized['role_redirects'] = array();
                }
                break;
                
            case 'advanced':
                $sanitized['custom_css'] = isset($settings['custom_css']) ? wp_strip_all_tags($settings['custom_css']) : '';
                $sanitized['custom_footer'] = isset($settings['custom_footer']) ? wp_kses_post($settings['custom_footer']) : '';
                $sanitized['hide_admin_bar'] = isset($settings['hide_admin_bar']) ? (bool) $settings['hide_admin_bar'] : false;
                break;
                
            default:
                $sanitized = array_map('sanitize_text_field', $settings);
        }
        
        return $sanitized;
    }
    
    /**
     * Admin notices
     */
    public function admin_notices() {
        // Show activation notice
        if (get_transient('bd_client_suite_activated')) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . sprintf(
                __('BD Client Suite activated! <a href="%s">Configure your settings</a> to get started.', 'bd-client-suite'),
                admin_url('admin.php?page=bd-client-suite')
            ) . '</p>';
            echo '</div>';
            delete_transient('bd_client_suite_activated');
        }
    }
    
    /**
     * Get plugin stats for dashboard
     */
    public function get_plugin_stats() {
        global $wpdb;
        
        $stats = array(
            'shortcuts' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}bd_client_suite_shortcuts WHERE active = 1"),
            'categories' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}bd_client_suite_categories WHERE active = 1"),
            'active_branding' => bd_client_suite()->get_option('branding.login_logo') ? 1 : 0,
            'custom_redirects' => count(bd_client_suite()->get_option('redirects.role_redirects', array()))
        );
        
        return $stats;
    }

    /**
     * AJAX handler for saving categories
     */
    public function ajax_save_category() {
        check_ajax_referer('bd_client_suite_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        global $wpdb;
        
        $category_id = intval($_POST['category_id']);
        $name = sanitize_text_field($_POST['name']);
        $icon = sanitize_text_field($_POST['icon']);
        $color = sanitize_hex_color($_POST['color']);
        $slug = sanitize_title($name);
        
        if (empty($name)) {
            wp_send_json_error(__('Category name is required.', 'bd-client-suite'));
        }
        
        $data = array(
            'name' => $name,
            'slug' => $slug,
            'icon' => $icon,
            'color' => $color,
            'updated_at' => current_time('mysql')
        );
        
        if ($category_id > 0) {
            // Update existing category
            $result = $wpdb->update(
                $wpdb->prefix . 'bd_client_suite_categories',
                $data,
                array('id' => $category_id),
                array('%s', '%s', '%s', '%s', '%s'),
                array('%d')
            );
            $action = 'updated';
        } else {
            // Create new category
            $data['created_at'] = current_time('mysql');
            $data['sort_order'] = $wpdb->get_var("SELECT COALESCE(MAX(sort_order), 0) + 1 FROM {$wpdb->prefix}bd_client_suite_categories");
            
            $result = $wpdb->insert(
                $wpdb->prefix . 'bd_client_suite_categories',
                $data,
                array('%s', '%s', '%s', '%s', '%s', '%s', '%d')
            );
            $category_id = $wpdb->insert_id;
            $action = 'created';
        }
        
        if ($result === false) {
            wp_send_json_error(__('Failed to save category.', 'bd-client-suite'));
        }
        
        wp_send_json_success(array(
            'message' => sprintf(__('Category %s successfully.', 'bd-client-suite'), $action),
            'category_id' => $category_id,
            'category' => array(
                'id' => $category_id,
                'name' => $name,
                'slug' => $slug,
                'icon' => $icon,
                'color' => $color
            )
        ));
    }

    /**
     * AJAX handler for deleting categories
     */
    public function ajax_delete_category() {
        check_ajax_referer('bd_client_suite_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        global $wpdb;
        
        $category_id = intval($_POST['category_id']);
        
        if ($category_id <= 0) {
            wp_send_json_error(__('Invalid category ID.', 'bd-client-suite'));
        }
        
        // Check if category has shortcuts
        $shortcut_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}bd_client_suite_shortcuts WHERE category_id = %d AND active = 1",
            $category_id
        ));
        
        if ($shortcut_count > 0) {
            wp_send_json_error(__('Cannot delete category that contains shortcuts. Please move or delete the shortcuts first.', 'bd-client-suite'));
        }
        
        $result = $wpdb->update(
            $wpdb->prefix . 'bd_client_suite_categories',
            array('active' => 0, 'updated_at' => current_time('mysql')),
            array('id' => $category_id),
            array('%d', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            wp_send_json_error(__('Failed to delete category.', 'bd-client-suite'));
        }
        
        wp_send_json_success(array(
            'message' => __('Category deleted successfully.', 'bd-client-suite')
        ));
    }

    /**
     * AJAX handler for saving shortcuts
     */
    public function ajax_save_shortcut() {
        // Add comprehensive debugging
        error_log('BD Client Suite: ajax_save_shortcut called');
        error_log('BD Client Suite: POST data: ' . print_r($_POST, true));
        
        check_ajax_referer('bd_client_suite_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            error_log('BD Client Suite: User does not have manage_options capability');
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        global $wpdb;
        
        $shortcut_id = isset($_POST['shortcut_id']) ? intval($_POST['shortcut_id']) : 0;
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';
        $icon = isset($_POST['icon']) ? sanitize_text_field($_POST['icon']) : '🔗';
        $category_slug = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : 'general';
        $roles = isset($_POST['roles']) ? array_map('sanitize_text_field', $_POST['roles']) : array('all');
        
        error_log('BD Client Suite: Parsed data - Name: ' . $name . ', URL: ' . $url . ', Category: ' . $category_slug);
        
        if (empty($name) || empty($url)) {
            error_log('BD Client Suite: Name or URL is empty - Name: "' . $name . '", URL: "' . $url . '"');
            wp_send_json_error(__('Shortcut name and URL are required.', 'bd-client-suite'));
            return;
        }
        
        // Get category ID from slug
        $category_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}bd_client_suite_categories WHERE slug = %s AND active = 1",
            $category_slug
        ));
        
        error_log('BD Client Suite: Category lookup result for slug "' . $category_slug . '": ' . var_export($category_id, true));
        
        if (!$category_id) {
            error_log('BD Client Suite: No category found, creating default General category');
            
            // Check if categories table exists first
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}bd_client_suite_categories'");
            error_log('BD Client Suite: Categories table exists: ' . var_export($table_exists, true));
            
            if (!$table_exists) {
                error_log('BD Client Suite: Creating categories table');
                $charset_collate = $wpdb->get_charset_collate();
                $sql = "CREATE TABLE {$wpdb->prefix}bd_client_suite_categories (
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
                
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                dbDelta($sql);
                error_log('BD Client Suite: Categories table created');
            }
            
            // Create a default "General" category
            $default_category = array(
                'name' => 'General',
                'slug' => 'general',
                'icon' => '📁',
                'color' => '#667eea',
                'sort_order' => 0,
                'active' => 1,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            );
            
            $result = $wpdb->insert(
                $wpdb->prefix . 'bd_client_suite_categories',
                $default_category,
                array('%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s')
            );
            
            error_log('BD Client Suite: Category insert result: ' . var_export($result, true));
            
            if ($result === false) {
                error_log('BD Client Suite: Failed to create default category. DB Error: ' . $wpdb->last_error);
            }
            
            $category_id = $wpdb->insert_id;
            error_log('BD Client Suite: New category ID: ' . $category_id);
        }
        
        $data = array(
            'name' => $name,
            'url' => $url,
            'icon' => $icon,
            'category_id' => $category_id,
            'user_roles' => wp_json_encode($roles),
            'updated_at' => current_time('mysql')
        );
        
        if ($shortcut_id > 0) {
            // Update existing shortcut
            $result = $wpdb->update(
                $wpdb->prefix . 'bd_client_suite_shortcuts',
                $data,
                array('id' => $shortcut_id),
                array('%s', '%s', '%s', '%d', '%s', '%s'),
                array('%d')
            );
            $action = 'updated';
        } else {
            // Create new shortcut
            $data['created_at'] = current_time('mysql');
            $data['sort_order'] = $wpdb->get_var("SELECT COALESCE(MAX(sort_order), 0) + 1 FROM {$wpdb->prefix}bd_client_suite_shortcuts");
            $data['active'] = 1;
            
            // Debug the data being inserted
            error_log('BD Client Suite: Inserting shortcut data: ' . var_export($data, true));
            
            // Check if shortcuts table exists
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}bd_client_suite_shortcuts'");
            if (!$table_exists) {
                // Create the shortcuts table if it doesn't exist
                error_log('BD Client Suite: Creating shortcuts table');
                $charset_collate = $wpdb->get_charset_collate();
                $sql = "CREATE TABLE {$wpdb->prefix}bd_client_suite_shortcuts (
                    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    name varchar(255) NOT NULL,
                    url varchar(255) NOT NULL,
                    icon varchar(50) DEFAULT '🔗' NOT NULL,
                    category_id mediumint(9) NOT NULL,
                    user_roles text NOT NULL,
                    sort_order int(11) DEFAULT 0 NOT NULL,
                    active tinyint(1) DEFAULT 1 NOT NULL,
                    created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    updated_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    PRIMARY KEY  (id)
                ) $charset_collate;";
                
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                dbDelta($sql);
                
                error_log('BD Client Suite: Shortcuts table created');
            }
            
            // Try to insert with simplified format specifiers
            $result = $wpdb->insert(
                $wpdb->prefix . 'bd_client_suite_shortcuts',
                $data
            );
            
            if ($result === false) {
                error_log('BD Client Suite: Insert failed. DB Error: ' . $wpdb->last_error);
                
                // Try alternative approach with direct SQL
                $columns = implode(', ', array_keys($data));
                $placeholders = implode(', ', array_fill(0, count($data), '%s'));
                $sql = $wpdb->prepare(
                    "INSERT INTO {$wpdb->prefix}bd_client_suite_shortcuts ($columns) VALUES ($placeholders)",
                    array_values($data)
                );
                
                $result = $wpdb->query($sql);
                if ($result === false) {
                    error_log('BD Client Suite: Direct SQL insert failed. DB Error: ' . $wpdb->last_error);
                }
            }
            
            $shortcut_id = $wpdb->insert_id;
            error_log('BD Client Suite: Shortcut insert result: ' . var_export($result, true) . ', ID: ' . $shortcut_id);
            $action = 'created';
        }
        
        if ($result === false) {
            error_log('BD Client Suite: Failed to save shortcut. DB Error: ' . $wpdb->last_error);
            wp_send_json_error('Failed to save shortcut: ' . $wpdb->last_error);
            return;
        }
        
        wp_send_json_success(array(
            'message' => sprintf(__('Shortcut %s successfully.', 'bd-client-suite'), $action),
            'shortcut_id' => $shortcut_id,
            'shortcut' => array(
                'id' => $shortcut_id,
                'name' => $name,
                'url' => $url,
                'icon' => $icon,
                'category_id' => $category_id,
                'user_roles' => $roles
            )
        ));
    }

    /**
     * AJAX handler for deleting shortcuts
     */
    public function ajax_delete_shortcut() {
        check_ajax_referer('bd_client_suite_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        global $wpdb;
        
        $shortcut_id = intval($_POST['shortcut_id']);
        
        if ($shortcut_id <= 0) {
            wp_send_json_error(__('Invalid shortcut ID.', 'bd-client-suite'));
        }
        
        $result = $wpdb->update(
            $wpdb->prefix . 'bd_client_suite_shortcuts',
            array('active' => 0, 'updated_at' => current_time('mysql')),
            array('id' => $shortcut_id),
            array('%d', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            wp_send_json_error(__('Failed to delete shortcut.', 'bd-client-suite'));
        }
        
        wp_send_json_success(array(
            'message' => __('Shortcut deleted successfully.', 'bd-client-suite')
        ));
    }
}