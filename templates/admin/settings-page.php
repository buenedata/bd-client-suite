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

<!-- TEMPORARY DEBUG: Inline styles to test if CSS is loading -->
<style>
.bd-settings-page { 
    background: #f0f0f1 !important; 
    min-height: 100vh !important; 
    padding: 20px !important;
}
.bd-gradient-text { 
    color: #667eea !important; 
    font-size: 2em !important; 
    font-weight: bold !important;
    margin-bottom: 10px !important;
}
.bd-settings-header { 
    background: white !important; 
    padding: 20px !important; 
    margin: 20px 0 !important; 
    border-radius: 8px !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
}
.bd-card { 
    background: white !important; 
    border-radius: 8px !important; 
    padding: 20px !important; 
    margin: 10px 0 !important; 
    box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
}
.bd-tabs { 
    display: flex !important; 
    list-style: none !important; 
    margin: 0 !important; 
    padding: 0 !important; 
    background: white !important; 
    border-radius: 8px !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
}
.bd-tab-button { 
    background: #f9f9f9 !important; 
    border: none !important; 
    padding: 15px 20px !important; 
    cursor: pointer !important;
    border-radius: 8px !important;
    transition: all 0.3s ease !important;
}
.bd-tab-button.active { 
    background: #667eea !important; 
    color: white !important;
}
.bd-tab-button:hover {
    background: #e2e8f0 !important;
}
.bd-tab-button.active:hover {
    background: #5a67d8 !important;
}
.bd-settings-container {
    display: flex !important;
    gap: 20px !important;
    margin-top: 20px !important;
}
.bd-settings-nav {
    flex: 0 0 250px !important;
}
.bd-settings-content {
    flex: 1 !important;
}
</style>

<div class="wrap bd-settings-page">
    <div class="bd-settings-header">
        <h1 class="bd-gradient-text">
            <?php _e('BD Client Suite Settings', 'bd-client-suite'); ?>
        </h1>
        <p class="bd-subtitle">
            <?php _e('Customize your WordPress admin experience', 'bd-client-suite'); ?>
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
