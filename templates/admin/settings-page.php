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

<div class="bd-settings-page wrap">
    <div class="bd-settings-header">
        <h1 class="bd-gradient-text">
            <?php echo esc_html__('BD Client Suite', 'bd-client-suite'); ?>
        </h1>
        <p class="bd-subtitle">
            <?php echo esc_html__('Professional WordPress branding and customization for client-friendly admin experiences.', 'bd-client-suite'); ?>
        </p>
    </div>

    <div class="bd-settings-container">
        <div class="bd-settings-nav">
            <ul class="bd-tabs">
                <?php foreach ($tabs as $tab_key => $tab_data): ?>
                    <li class="bd-tab <?php echo $current_tab === $tab_key ? 'active' : ''; ?>">
                        <button type="button" data-tab="<?php echo esc_attr($tab_key); ?>" 
                                class="bd-tab-button <?php echo $current_tab === $tab_key ? 'active' : ''; ?>">
                            <span class="bd-tab-icon"><?php echo esc_html($tab_data['icon']); ?></span>
                            <span class="bd-tab-name"><?php echo esc_html($tab_data['name']); ?></span>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="bd-settings-content">
            <form id="bd-settings-form" method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('bd_client_suite_settings', 'bd_nonce'); ?>
                <input type="hidden" name="current_tab" value="<?php echo esc_attr($current_tab); ?>">
                
                <?php
                // Include all tab contents
                foreach ($tabs as $tab_key => $tab_data) {
                    $tab_file = BD_CLIENT_SUITE_PATH . 'templates/admin/tabs/' . $tab_key . '-tab.php';
                    echo '<div class="bd-tab-content' . ($current_tab === $tab_key ? ' active' : '') . '" id="bd-tab-' . $tab_key . '">';
                    if (file_exists($tab_file)) {
                        include $tab_file;
                    } else {
                        echo '<div class="bd-error-message">';
                        echo '<h3>' . __('Tab not found', 'bd-client-suite') . '</h3>';
                        echo '<p>' . sprintf(__('The settings tab "%s" could not be loaded.', 'bd-client-suite'), $tab_key) . '</p>';
                        echo '</div>';
                    }
                    echo '</div>';
                }
                ?>
                
                <div class="bd-settings-footer">
                    <div class="bd-settings-actions">
                        <button type="submit" class="button button-primary bd-save-settings">
                            <?php _e('Save Changes', 'bd-client-suite'); ?>
                        </button>
                        <button type="button" class="button button-secondary bd-reset-tab">
                            <?php _e('Reset Tab', 'bd-client-suite'); ?>
                        </button>
                    </div>
                    <div class="bd-settings-status">
                        <span class="bd-status-message"></span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
