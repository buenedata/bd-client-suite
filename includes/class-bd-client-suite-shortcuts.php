<?php
/**
 * BD Client Suite Shortcuts Class
 * 
 * Handles admin shortcuts system for quick access to common tasks
 *
 * @package BD_Client_Suite
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BD_Client_Suite_Shortcuts {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_footer', array($this, 'add_shortcut_modal'));
    }
    
    /**
     * Add dashboard widget
     */
    public function add_dashboard_widget() {
        if (bd_client_suite()->get_option('shortcuts.enabled', true)) {
            wp_add_dashboard_widget(
                'bd-client-suite-shortcuts',
                __('🚀 Quick Shortcuts', 'bd-client-suite') . ' <span style="color: #667eea; font-weight: normal; font-size: 0.8em;">by BD Client Suite</span>',
                array($this, 'render_shortcuts_widget')
            );
        }
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        if (get_current_screen()->id === 'dashboard') {
            wp_enqueue_style(
                'bd-client-suite-shortcuts',
                BD_CLIENT_SUITE_URL . 'assets/css/shortcuts.css',
                array(),
                BD_CLIENT_SUITE_VERSION
            );
            
            wp_enqueue_script(
                'bd-client-suite-shortcuts',
                BD_CLIENT_SUITE_URL . 'assets/js/shortcuts.js',
                array('jquery', 'jquery-ui-sortable'),
                BD_CLIENT_SUITE_VERSION,
                true
            );
        }
    }
    
    /**
     * Render shortcuts widget
     */
    public function render_shortcuts_widget() {
        $shortcuts = $this->get_shortcuts();
        $categories = $this->get_categories();
        
        echo '<div class="bd-shortcuts-container">';
        
        if (empty($shortcuts)) {
            echo '<div class="bd-shortcuts-empty">';
            echo '<p>' . __('No shortcuts created yet.', 'bd-client-suite') . '</p>';
            echo '<button type="button" class="button button-primary bd-add-shortcut-btn">';
            echo __('Add Your First Shortcut', 'bd-client-suite');
            echo '</button>';
            echo '</div>';
        } else {
            // Group shortcuts by category
            $grouped_shortcuts = array();
            foreach ($shortcuts as $shortcut) {
                $category = $shortcut->category ?: 'general';
                if (!isset($grouped_shortcuts[$category])) {
                    $grouped_shortcuts[$category] = array();
                }
                $grouped_shortcuts[$category][] = $shortcut;
            }
            
            foreach ($grouped_shortcuts as $category_slug => $category_shortcuts) {
                $category_info = $this->get_category_info($category_slug);
                
                echo '<div class="bd-shortcuts-category" data-category="' . esc_attr($category_slug) . '">';
                echo '<h4 class="bd-category-title">';
                echo '<span class="bd-category-icon">' . esc_html($category_info['icon']) . '</span>';
                echo esc_html($category_info['name']);
                echo '</h4>';
                
                echo '<div class="bd-shortcuts-grid">';
                foreach ($category_shortcuts as $shortcut) {
                    $this->render_shortcut_card($shortcut);
                }
                echo '</div>';
                echo '</div>';
            }
            
            echo '<div class="bd-shortcuts-actions">';
            echo '<button type="button" class="button bd-add-shortcut-btn">';
            echo __('Add Shortcut', 'bd-client-suite');
            echo '</button>';
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    /**
     * Render individual shortcut card
     */
    private function render_shortcut_card($shortcut) {
        $icon = !empty($shortcut->icon) ? $shortcut->icon : '🔗';
        $roles = json_decode($shortcut->user_roles, true);
        $roles_display = is_array($roles) ? implode(', ', $roles) : 'all';
        
        echo '<div class="bd-shortcut-card" data-id="' . esc_attr($shortcut->id) . '" data-category-slug="' . esc_attr($shortcut->category_slug) . '" data-user-roles="' . esc_attr(json_encode($roles)) . '">';
        echo '<a href="' . esc_url($shortcut->url) . '" class="bd-shortcut-link" target="_blank">';
        echo '<div class="bd-shortcut-icon">' . esc_html($icon) . '</div>';
        echo '<div class="bd-shortcut-name">' . esc_html($shortcut->name) . '</div>';
        echo '</a>';
        echo '<div class="bd-shortcut-actions">';
        echo '<button type="button" class="bd-edit-shortcut" data-id="' . esc_attr($shortcut->id) . '" title="' . esc_attr__('Edit', 'bd-client-suite') . '">✏️</button>';
        echo '<button type="button" class="bd-delete-shortcut" data-id="' . esc_attr($shortcut->id) . '" title="' . esc_attr__('Delete', 'bd-client-suite') . '">🗑️</button>';
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * Add shortcut modal
     */
    public function add_shortcut_modal() {
        if (get_current_screen()->id !== 'dashboard') {
            return;
        }
        
        $categories = $this->get_categories();
        $suggested_shortcuts = $this->get_suggested_shortcuts();
        
        ?>
        <div id="bd-shortcut-modal" class="bd-modal" style="display: none;">
            <div class="bd-modal-content">
                <div class="bd-modal-header">
                    <h3><?php _e('Add Quick Shortcut', 'bd-client-suite'); ?></h3>
                    <button type="button" class="bd-modal-close">&times;</button>
                </div>
                
                <div class="bd-modal-body">
                    <div class="bd-shortcut-tabs">
                        <button type="button" class="bd-tab-button active" data-tab="custom">
                            <?php _e('Custom Shortcut', 'bd-client-suite'); ?>
                        </button>
                        <button type="button" class="bd-tab-button" data-tab="suggested">
                            <?php _e('Suggested', 'bd-client-suite'); ?>
                        </button>
                    </div>
                    
                    <div id="bd-tab-custom" class="bd-tab-content active">
                        <form id="bd-shortcut-form">
                            <div class="bd-form-row">
                                <label for="shortcut-name"><?php _e('Name', 'bd-client-suite'); ?></label>
                                <input type="text" id="shortcut-name" name="name" required>
                            </div>
                            
                            <div class="bd-form-row">
                                <label for="shortcut-url"><?php _e('URL', 'bd-client-suite'); ?></label>
                                <input type="url" id="shortcut-url" name="url" required>
                            </div>
                            
                            <div class="bd-form-row">
                                <label for="shortcut-icon"><?php _e('Icon (Emoji)', 'bd-client-suite'); ?></label>
                                <input type="text" id="shortcut-icon" name="icon" placeholder="🔗" maxlength="2">
                            </div>
                            
                            <div class="bd-form-row">
                                <label for="shortcut-category"><?php _e('Category', 'bd-client-suite'); ?></label>
                                <select id="shortcut-category" name="category">
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo esc_attr($category->slug); ?>">
                                            <?php echo esc_html($category->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </form>
                    </div>
                    
                    <div id="bd-tab-suggested" class="bd-tab-content">
                        <div class="bd-suggested-shortcuts">
                            <?php foreach ($suggested_shortcuts as $shortcut): ?>
                                <div class="bd-suggested-shortcut" 
                                     data-name="<?php echo esc_attr($shortcut['name']); ?>"
                                     data-url="<?php echo esc_attr($shortcut['url']); ?>"
                                     data-icon="<?php echo esc_attr($shortcut['icon']); ?>"
                                     data-category="<?php echo esc_attr($shortcut['category']); ?>">
                                    <div class="bd-suggested-icon"><?php echo esc_html($shortcut['icon']); ?></div>
                                    <div class="bd-suggested-info">
                                        <div class="bd-suggested-name"><?php echo esc_html($shortcut['name']); ?></div>
                                        <div class="bd-suggested-desc"><?php echo esc_html($shortcut['description']); ?></div>
                                    </div>
                                    <button type="button" class="button bd-add-suggested">
                                        <?php _e('Add', 'bd-client-suite'); ?>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="bd-modal-footer">
                    <button type="button" class="button" id="bd-cancel-shortcut">
                        <?php _e('Cancel', 'bd-client-suite'); ?>
                    </button>
                    <button type="button" class="button button-primary" id="bd-save-shortcut">
                        <?php _e('Save Shortcut', 'bd-client-suite'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get shortcuts from database
     */
    public function get_shortcuts($category = '', $user_role = '') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bd_client_suite_shortcuts';
        $where_clauses = array('active = 1');
        $params = array();
        
        if (!empty($category)) {
            $where_clauses[] = 'category = %s';
            $params[] = $category;
        }
        
        if (!empty($user_role)) {
            $where_clauses[] = '(user_role = %s OR user_role = "all")';
            $params[] = $user_role;
        }
        
        $where_sql = implode(' AND ', $where_clauses);
        $sql = "SELECT * FROM {$table_name} WHERE {$where_sql} ORDER BY sort_order ASC, name ASC";
        
        if (!empty($params)) {
            $sql = $wpdb->prepare($sql, $params);
        }
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Get categories from database
     */
    public function get_categories() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bd_client_suite_categories';
        
        return $wpdb->get_results(
            "SELECT * FROM {$table_name} WHERE active = 1 ORDER BY sort_order ASC, name ASC"
        );
    }
    
    /**
     * Get category info
     */
    private function get_category_info($slug) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bd_client_suite_categories';
        $category = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE slug = %s",
            $slug
        ));
        
        if ($category) {
            return array(
                'name' => $category->name,
                'icon' => $category->icon,
                'color' => $category->color
            );
        }
        
        return array(
            'name' => ucfirst($slug),
            'icon' => '📁',
            'color' => '#667eea'
        );
    }
    
    /**
     * Get suggested shortcuts
     */
    private function get_suggested_shortcuts() {
        return array(
            array(
                'name' => __('Add New Post', 'bd-client-suite'),
                'description' => __('Create a new blog post', 'bd-client-suite'),
                'url' => admin_url('post-new.php'),
                'icon' => '📝',
                'category' => 'content'
            ),
            array(
                'name' => __('Add New Page', 'bd-client-suite'),
                'description' => __('Create a new page', 'bd-client-suite'),
                'url' => admin_url('post-new.php?post_type=page'),
                'icon' => '📄',
                'category' => 'content'
            ),
            array(
                'name' => __('Media Library', 'bd-client-suite'),
                'description' => __('Manage uploaded files', 'bd-client-suite'),
                'url' => admin_url('upload.php'),
                'icon' => '🖼️',
                'category' => 'media'
            ),
            array(
                'name' => __('Menus', 'bd-client-suite'),
                'description' => __('Manage navigation menus', 'bd-client-suite'),
                'url' => admin_url('nav-menus.php'),
                'icon' => '🍔',
                'category' => 'general'
            ),
            array(
                'name' => __('Widgets', 'bd-client-suite'),
                'description' => __('Manage sidebar widgets', 'bd-client-suite'),
                'url' => admin_url('widgets.php'),
                'icon' => '🧩',
                'category' => 'general'
            ),
            array(
                'name' => __('Users', 'bd-client-suite'),
                'description' => __('Manage users and roles', 'bd-client-suite'),
                'url' => admin_url('users.php'),
                'icon' => '👥',
                'category' => 'general'
            ),
            array(
                'name' => __('Plugins', 'bd-client-suite'),
                'description' => __('Manage installed plugins', 'bd-client-suite'),
                'url' => admin_url('plugins.php'),
                'icon' => '🔌',
                'category' => 'general'
            ),
            array(
                'name' => __('Theme Customizer', 'bd-client-suite'),
                'description' => __('Customize theme appearance', 'bd-client-suite'),
                'url' => admin_url('customize.php'),
                'icon' => '🎨',
                'category' => 'general'
            )
        );
    }
    
    /**
     * AJAX: Add shortcut
     */
    public function ajax_add_shortcut() {
        check_ajax_referer('bd_client_suite_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions.', 'bd-client-suite'));
        }
        
        $name = sanitize_text_field($_POST['name']);
        $url = esc_url_raw($_POST['url']);
        $icon = sanitize_text_field($_POST['icon']);
        $category = sanitize_text_field($_POST['category']);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'bd_client_suite_shortcuts';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'name' => $name,
                'url' => $url,
                'icon' => $icon,
                'category' => $category,
                'user_role' => 'all',
                'sort_order' => 0,
                'active' => 1
            ),
            array('%s', '%s', '%s', '%s', '%s', '%d', '%d')
        );
        
        if ($result) {
            wp_send_json_success(array(
                'message' => __('Shortcut added successfully!', 'bd-client-suite'),
                'shortcut_id' => $wpdb->insert_id
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Error adding shortcut.', 'bd-client-suite')
            ));
        }
    }
    
    /**
     * AJAX: Delete shortcut
     */
    public function ajax_delete_shortcut() {
        check_ajax_referer('bd_client_suite_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions.', 'bd-client-suite'));
        }
        
        $shortcut_id = absint($_POST['shortcut_id']);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'bd_client_suite_shortcuts';
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $shortcut_id),
            array('%d')
        );
        
        if ($result) {
            wp_send_json_success(array(
                'message' => __('Shortcut deleted successfully!', 'bd-client-suite')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Error deleting shortcut.', 'bd-client-suite')
            ));
        }
    }
    
    /**
     * AJAX: Update shortcut order
     */
    public function ajax_update_shortcut_order() {
        check_ajax_referer('bd_client_suite_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions.', 'bd-client-suite'));
        }
        
        $shortcut_ids = array_map('absint', $_POST['shortcut_ids']);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'bd_client_suite_shortcuts';
        
        foreach ($shortcut_ids as $index => $shortcut_id) {
            $wpdb->update(
                $table_name,
                array('sort_order' => $index),
                array('id' => $shortcut_id),
                array('%d'),
                array('%d')
            );
        }
        
        wp_send_json_success(array(
            'message' => __('Shortcut order updated!', 'bd-client-suite')
        ));
    }
}
