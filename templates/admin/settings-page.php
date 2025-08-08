<?php
/**
 * Main Settings Page Template
 * 
 * @package BD_Client_Suite
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$tabs = array(
    'branding' => array(
        'name' => __('Branding', 'bd-client-suite'),
        'icon' => '🎨'
    ),
    'shortcuts' => array(
        'name' => __('Shortcuts', 'bd-client-suite'),
        'icon' => '🚀'
    ),
    'login' => array(
        'name' => __('Login Page', 'bd-client-suite'),
        'icon' => '🔐'
    ),
    'redirects' => array(
        'name' => __('Redirects', 'bd-client-suite'),
        'icon' => '🔄'
    ),
    'advanced' => array(
        'name' => __('Advanced', 'bd-client-suite'),
        'icon' => '⚙️'
    )
);
?>

<div class="wrap bd-client-suite-admin">
    <div class="bd-admin-header">
        <div class="bd-branding">
            <h2>🎨 BD Client Suite</h2>
            <p><?php echo esc_html__('Professional WordPress branding and customization for client-friendly admin experiences.', 'bd-client-suite'); ?></p>
        </div>
        <div class="bd-actions">
            <button class="button button-primary">🚀 Quick Setup</button>
        </div>
    </div>

    <!-- Modern Navigation Tabs -->
    <nav class="nav-tab-wrapper">
        <?php foreach ($tabs as $tab_key => $tab_data): ?>
            <a href="#<?php echo esc_attr($tab_key); ?>"
               class="nav-tab <?php echo $current_tab === $tab_key ? 'nav-tab-active' : ''; ?>"
               data-tab="<?php echo esc_attr($tab_key); ?>">
                <?php echo esc_html($tab_data['icon']); ?> <?php echo esc_html($tab_data['name']); ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- Tab Content -->
    <?php foreach ($tabs as $tab_key => $tab_data): ?>
        <div class="tab-content <?php echo $current_tab === $tab_key ? 'active' : ''; ?>" id="<?php echo esc_attr($tab_key); ?>">
            <div class="bd-settings-section">
                <?php
                $tab_file = BD_CLIENT_SUITE_PATH . 'templates/admin/tabs/' . $tab_key . '-tab.php';
                if (file_exists($tab_file)) {
                    include $tab_file;
                } else {
                    echo '<div class="bd-error-message">';
                    echo '<h3>' . __('Tab not found', 'bd-client-suite') . '</h3>';
                    echo '<p>' . sprintf(__('The settings tab "%s" could not be loaded.', 'bd-client-suite'), $tab_key) . '</p>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Settings Footer -->
    <div class="bd-settings-footer">
        <div class="bd-settings-actions">
            <button type="submit" class="button button-primary">💾 Save Changes</button>
            <button type="button" class="button button-secondary">Reset Tab</button>
        </div>
        <div class="bd-status-message"></div>
    </div>
</div>
