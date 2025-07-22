<?php
// Get current options
$shortcuts_options = bd_client_suite()->get_option('shortcuts', array());
$enabled = isset($shortcuts_options['enabled']) ? $shortcuts_options['enabled'] : true;
$show_in_dashboard = isset($shortcuts_options['show_in_dashboard']) ? $shortcuts_options['show_in_dashboard'] : true;
$show_in_admin_bar = isset($shortcuts_options['show_in_admin_bar']) ? $shortcuts_options['show_in_admin_bar'] : false;

// Get categories from database
global $wpdb;
$categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}bd_client_suite_categories WHERE active = 1 ORDER BY sort_order ASC");
$shortcuts = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}bd_client_suite_shortcuts WHERE active = 1 ORDER BY sort_order ASC");
?>

<h2><?php _e('Shortcuts Settings', 'bd-client-suite'); ?></h2>
<p><?php _e('Configure admin shortcuts and quick actions for faster navigation.', 'bd-client-suite'); ?></p>

<table class="form-table">
    <tr>
        <th scope="row"><?php _e('Enable Shortcuts', 'bd-client-suite'); ?></th>
        <td>
            <label>
                <input type="checkbox" name="shortcuts[enabled]" value="1" <?php checked($enabled); ?> />
                <?php _e('Enable admin shortcuts system', 'bd-client-suite'); ?>
            </label>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php _e('Display Options', 'bd-client-suite'); ?></th>
        <td>
            <p>
                <label>
                    <input type="checkbox" name="shortcuts[show_in_dashboard]" value="1" <?php checked($show_in_dashboard); ?> />
                    <?php _e('Show shortcuts widget in dashboard', 'bd-client-suite'); ?>
                </label>
            </p>
            <p>
                <label>
                    <input type="checkbox" name="shortcuts[show_in_admin_bar]" value="1" <?php checked($show_in_admin_bar); ?> />
                    <?php _e('Show shortcuts in admin bar', 'bd-client-suite'); ?>
                </label>
            </p>
        </td>
    </tr>
</table>

<h3><?php _e('Shortcut Categories', 'bd-client-suite'); ?></h3>
<p><?php _e('Organize your shortcuts into categories for better navigation.', 'bd-client-suite'); ?></p>

<div id="bd-categories-list">
    <?php if (!empty($categories)): ?>
        <?php foreach ($categories as $category): ?>
            <div class="bd-category-item" data-id="<?php echo esc_attr($category->id); ?>">
                <div class="bd-category-header">
                    <span class="bd-category-icon"><?php echo esc_html($category->icon); ?></span>
                    <span class="bd-category-name"><?php echo esc_html($category->name); ?></span>
                    <div class="bd-category-actions">
                        <button type="button" class="button button-small bd-edit-category"><?php _e('Edit', 'bd-client-suite'); ?></button>
                        <button type="button" class="button button-small bd-delete-category"><?php _e('Delete', 'bd-client-suite'); ?></button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p><?php _e('No categories found. Add your first category below.', 'bd-client-suite'); ?></p>
    <?php endif; ?>
</div>

<button type="button" class="button button-secondary" id="bd-add-category"><?php _e('Add New Category', 'bd-client-suite'); ?></button>

<h3><?php _e('Custom Shortcuts', 'bd-client-suite'); ?></h3>
<p><?php _e('Add custom shortcuts to frequently used admin pages.', 'bd-client-suite'); ?></p>

<div id="bd-shortcuts-list">
    <?php if (!empty($shortcuts)): ?>
        <?php foreach ($shortcuts as $shortcut): ?>
            <div class="bd-shortcut-item" data-id="<?php echo esc_attr($shortcut->id); ?>">
                <div class="bd-shortcut-header">
                    <span class="bd-shortcut-icon"><?php echo esc_html($shortcut->icon); ?></span>
                    <span class="bd-shortcut-name"><?php echo esc_html($shortcut->name); ?></span>
                    <span class="bd-shortcut-url"><?php echo esc_html($shortcut->url); ?></span>
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
