<?php
// Get current options
$shortcuts_options = bd_client_suite()->get_option('shortcuts', array());
$enabled = isset($shortcuts_options['enabled']) ? $shortcuts_options['enabled'] : true;
$show_in_dashboard = isset($shortcuts_options['show_in_dashboard']) ? $shortcuts_options['show_in_dashboard'] : true;
$show_in_admin_bar = isset($shortcuts_options['show_in_admin_bar']) ? $shortcuts_options['show_in_admin_bar'] : false;

// Get categories from database
global $wpdb;
$categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}bd_client_suite_categories WHERE active = 1 ORDER BY sort_order ASC");
$shortcuts = $wpdb->get_results("SELECT s.*, c.name as category_name, c.slug as category_slug FROM {$wpdb->prefix}bd_client_suite_shortcuts s LEFT JOIN {$wpdb->prefix}bd_client_suite_categories c ON s.category_id = c.id WHERE s.active = 1 ORDER BY s.sort_order ASC");

$stats = array(
    'shortcuts' => count($shortcuts),
    'categories' => count($categories)
);
?>

<div class="bd-shortcuts-header">
    <div>
        <h1 class="bd-shortcuts-title"><?php _e('🚀 Admin Shortcuts', 'bd-client-suite'); ?></h1>
        <p><?php _e('Configure admin shortcuts and quick actions for faster navigation.', 'bd-client-suite'); ?></p>
    </div>
    <div class="bd-shortcuts-stats">
        <div class="bd-stat-item">
            <span><?php _e('Shortcuts:', 'bd-client-suite'); ?></span>
            <span class="bd-stat-number"><?php echo $stats['shortcuts']; ?></span>
        </div>
        <div class="bd-stat-item">
            <span><?php _e('Categories:', 'bd-client-suite'); ?></span>
            <span class="bd-stat-number"><?php echo $stats['categories']; ?></span>
        </div>
    </div>
</div>

<div class="bd-card">
    <h3><?php _e('⚙️ Shortcut Settings', 'bd-client-suite'); ?></h3>
    
    <table class="form-table">
        <tr>
            <th scope="row"><?php _e('Enable Shortcuts', 'bd-client-suite'); ?></th>
            <td>
                <label class="bd-toggle">
                    <input type="checkbox" name="shortcuts[enabled]" value="1" <?php checked($enabled); ?> />
                    <span class="bd-toggle-slider"></span>
                    <span class="bd-toggle-label"><?php _e('Enable admin shortcuts system', 'bd-client-suite'); ?></span>
                </label>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php _e('Display Options', 'bd-client-suite'); ?></th>
            <td>
                <div class="bd-form-group">
                    <label class="bd-toggle">
                        <input type="checkbox" name="shortcuts[show_in_dashboard]" value="1" <?php checked($show_in_dashboard); ?> />
                        <span class="bd-toggle-slider"></span>
                        <span class="bd-toggle-label"><?php _e('Show shortcuts widget in dashboard', 'bd-client-suite'); ?></span>
                    </label>
                </div>
                <div class="bd-form-group">
                    <label class="bd-toggle">
                        <input type="checkbox" name="shortcuts[show_in_admin_bar]" value="1" <?php checked($show_in_admin_bar); ?> />
                        <span class="bd-toggle-slider"></span>
                        <span class="bd-toggle-label"><?php _e('Show shortcuts in admin bar', 'bd-client-suite'); ?></span>
                    </label>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="bd-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h3><?php _e('📁 Shortcut Categories', 'bd-client-suite'); ?></h3>
            <p><?php _e('Organize your shortcuts into categories for better navigation.', 'bd-client-suite'); ?></p>
        </div>
        <button type="button" class="button button-primary" id="bd-add-category">
            <?php _e('Add Category', 'bd-client-suite'); ?>
        </button>
    </div>

    <div id="bd-categories-list">
        <?php if (!empty($categories)): ?>
            <?php foreach ($categories as $category): ?>
                <div class="bd-category-item" data-id="<?php echo esc_attr($category->id); ?>" data-color="<?php echo esc_attr($category->color); ?>">
                    <div class="bd-category-header">
                        <span class="bd-category-icon"><?php echo esc_html($category->icon); ?></span>
                        <div class="bd-category-details">
                            <div class="bd-category-name"><?php echo esc_html($category->name); ?></div>
                            <span class="bd-category-slug"><?php echo esc_html($category->slug); ?></span>
                        </div>
                        <div class="bd-category-color" style="background-color: <?php echo esc_attr($category->color); ?>"></div>
                        <div class="bd-category-actions">
                            <button type="button" class="button bd-edit-category"><?php _e('Edit', 'bd-client-suite'); ?></button>
                            <button type="button" class="button bd-delete-category"><?php _e('Delete', 'bd-client-suite'); ?></button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="bd-shortcuts-empty-state">
                <div class="bd-empty-icon">📁</div>
                <h3><?php _e('No Categories Yet', 'bd-client-suite'); ?></h3>
                <p><?php _e('Create your first category to organize your shortcuts effectively.', 'bd-client-suite'); ?></p>
                <button type="button" class="button button-primary" id="bd-add-first-category">
                    <?php _e('Create First Category', 'bd-client-suite'); ?>
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="bd-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h3><?php _e('🔗 Custom Shortcuts', 'bd-client-suite'); ?></h3>
            <p><?php _e('Add custom shortcuts to frequently used admin pages.', 'bd-client-suite'); ?></p>
        </div>
        <button type="button" class="button button-primary" id="bd-add-shortcut">
            <?php _e('Add Shortcut', 'bd-client-suite'); ?>
        </button>
    </div>

    <div id="bd-shortcuts-list">
        <?php if (!empty($shortcuts)): ?>
            <?php foreach ($shortcuts as $shortcut): ?>
                <?php 
                $roles = json_decode($shortcut->user_roles, true);
                $roles_display = is_array($roles) ? implode(', ', $roles) : 'all';
                ?>
                <div class="bd-shortcut-item" data-id="<?php echo esc_attr($shortcut->id); ?>" data-category-slug="<?php echo esc_attr($shortcut->category_slug); ?>" data-user-roles="<?php echo esc_attr(json_encode($roles)); ?>">
                    <div class="bd-shortcut-header">
                        <div class="bd-shortcut-icon"><?php echo esc_html($shortcut->icon ?: '🔗'); ?></div>
                        <div class="bd-shortcut-details">
                            <div class="bd-shortcut-name"><?php echo esc_html($shortcut->name); ?></div>
                            <a href="<?php echo esc_url($shortcut->url); ?>" class="bd-shortcut-url" target="_blank"><?php echo esc_html($shortcut->url); ?></a>
                            <div class="bd-shortcut-meta">
                                <span class="bd-shortcut-category"><?php echo esc_html($shortcut->category_name ?: 'General'); ?></span>
                                <span class="bd-shortcut-roles"><?php echo esc_html($roles_display); ?></span>
                            </div>
                        </div>
                        <div class="bd-shortcut-actions">
                            <button type="button" class="button bd-edit-shortcut"><?php _e('Edit', 'bd-client-suite'); ?></button>
                            <button type="button" class="button bd-delete-shortcut"><?php _e('Delete', 'bd-client-suite'); ?></button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="bd-shortcuts-empty-state">
                <div class="bd-empty-icon">🚀</div>
                <h3><?php _e('No Shortcuts Yet', 'bd-client-suite'); ?></h3>
                <p><?php _e('Create your first shortcut to quickly access your most-used admin pages.', 'bd-client-suite'); ?></p>
                <button type="button" class="button button-primary" id="bd-add-first-shortcut">
                    <?php _e('Create First Shortcut', 'bd-client-suite'); ?>
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>
                    <div class="bd-shortcut-actions">
                        <button type="button" class="button button-small bd-edit-shortcut"><?php _e('Edit', 'bd-client-suite'); ?></button>
                        <button type="button" class="button button-small bd-delete-shortcut"><?php _e('Delete', 'bd-client-suite'); ?></button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p><?php _e('No shortcuts found. Add your first shortcut below.', 'bd-client-suite'); ?></p>
    <?php endif; ?>
</div>

<button type="button" class="button button-secondary" id="bd-add-shortcut"><?php _e('Add New Shortcut', 'bd-client-suite'); ?></button>

<!-- Category Modal -->
<div id="bd-category-modal" class="bd-modal" style="display: none;">
    <div class="bd-modal-content">
        <h3 id="bd-category-modal-title"><?php _e('Add Category', 'bd-client-suite'); ?></h3>
        <form id="bd-category-form" method="post" action="javascript:void(0);">
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Category Name', 'bd-client-suite'); ?></th>
                    <td><input type="text" id="bd-category-name" name="name" class="regular-text" required /></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Icon', 'bd-client-suite'); ?></th>
                    <td><input type="text" id="bd-category-icon" name="icon" class="regular-text" placeholder="🏠" /></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Color', 'bd-client-suite'); ?></th>
                    <td><input type="text" id="bd-category-color" name="color" class="bd-color-field" value="#667eea" /></td>
                </tr>
            </table>
            <div class="bd-modal-actions">
                <button type="submit" class="button button-primary"><?php _e('Save Category', 'bd-client-suite'); ?></button>
                <button type="button" class="button bd-modal-close"><?php _e('Cancel', 'bd-client-suite'); ?></button>
            </div>
        </form>
    </div>
</div>

<!-- Shortcut Modal -->
<div id="bd-shortcut-modal" class="bd-modal" style="display: none;">
    <div class="bd-modal-content">
        <h3 id="bd-shortcut-modal-title"><?php _e('Add Shortcut', 'bd-client-suite'); ?></h3>
        <form id="bd-shortcut-form" method="post" action="javascript:void(0);">
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Shortcut Name', 'bd-client-suite'); ?></th>
                    <td><input type="text" id="bd-shortcut-name" name="name" class="regular-text" required /></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('URL', 'bd-client-suite'); ?></th>
                    <td><input type="url" id="bd-shortcut-url" name="url" class="regular-text" required /></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Icon', 'bd-client-suite'); ?></th>
                    <td><input type="text" id="bd-shortcut-icon" name="icon" class="regular-text" placeholder="⚙️" /></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Category', 'bd-client-suite'); ?></th>
                    <td>
                        <select id="bd-shortcut-category" name="category">
                            <?php if (!empty($categories)): ?>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo esc_attr($category->slug); ?>"><?php echo esc_html($category->name); ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="general"><?php _e('General', 'bd-client-suite'); ?></option>
                            <?php endif; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('User Roles', 'bd-client-suite'); ?></th>
                    <td>
                        <select id="bd-shortcut-roles" name="roles[]" multiple>
                            <option value="all"><?php _e('All Users', 'bd-client-suite'); ?></option>
                            <?php
                            $roles = wp_roles()->get_names();
                            foreach ($roles as $role_key => $role_name) {
                                echo '<option value="' . esc_attr($role_key) . '">' . esc_html($role_name) . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
            </table>
            <div class="bd-modal-actions">
                <button type="submit" class="button button-primary"><?php _e('Save Shortcut', 'bd-client-suite'); ?></button>
                <button type="button" class="button bd-modal-close"><?php _e('Cancel', 'bd-client-suite'); ?></button>
            </div>
        </form>
    </div>
</div>

<style>
.bd-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 100000;
}
.bd-modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 20px;
    border-radius: 8px;
    min-width: 500px;
    max-width: 90%;
}
.bd-modal-actions {
    margin-top: 20px;
    text-align: right;
}
.bd-category-item, .bd-shortcut-item {
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 10px;
}
.bd-category-header, .bd-shortcut-header {
    display: flex;
    align-items: center;
    gap: 15px;
}
.bd-category-actions, .bd-shortcut-actions {
    margin-left: auto;
}
</style>
