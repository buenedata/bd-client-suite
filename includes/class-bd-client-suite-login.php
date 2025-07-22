<?php
/**
 * BD Client Suite Login Class
 * 
 * Handles login page customization and branding
 *
 * @package BD_Client_Suite
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BD_Client_Suite_Login {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('login_enqueue_scripts', array($this, 'enqueue_login_styles'));
        add_action('login_head', array($this, 'add_login_head_content'));
        add_filter('login_headerurl', array($this, 'custom_login_logo_url'));
        add_filter('login_headertext', array($this, 'custom_login_logo_title'));
        add_filter('login_message', array($this, 'custom_login_message'));
        add_action('login_footer', array($this, 'add_login_footer_content'));
        add_filter('login_errors', array($this, 'custom_login_errors'));
        add_action('wp_login_failed', array($this, 'login_failed_redirect'));
    }
    
    /**
     * Enqueue login styles
     */
    public function enqueue_login_styles() {
        if (!bd_client_suite()->get_option('login.custom_login_page', false)) {
            return;
        }
        
        wp_enqueue_style(
            'bd-client-suite-login',
            BD_CLIENT_SUITE_URL . 'assets/css/login.css',
            array(),
            BD_CLIENT_SUITE_VERSION
        );
        
        // Add custom CSS
        $custom_css = $this->generate_login_css();
        if (!empty($custom_css)) {
            wp_add_inline_style('bd-client-suite-login', $custom_css);
        }
    }
    
    /**
     * Generate custom login CSS
     */
    private function generate_login_css() {
        $css = '';
        
        // Get login options
        $login_options = bd_client_suite()->get_option('login', array());
        $background_image = isset($login_options['background_image']) ? $login_options['background_image'] : '';
        $background_color = isset($login_options['background_color']) ? $login_options['background_color'] : '#f1f1f1';
        $form_background = isset($login_options['form_background']) ? $login_options['form_background'] : '#ffffff';
        $form_border_color = isset($login_options['form_border_color']) ? $login_options['form_border_color'] : '#dddddd';
        $button_color = isset($login_options['button_color']) ? $login_options['button_color'] : '#0073aa';
        $custom_logo = isset($login_options['custom_logo']) ? $login_options['custom_logo'] : '';
        $logo_width = isset($login_options['logo_width']) ? $login_options['logo_width'] : 80;
        $logo_height = isset($login_options['logo_height']) ? $login_options['logo_height'] : 80;
        
        // Fallback to branding logo if no custom login logo
        if (empty($custom_logo)) {
            $custom_logo = bd_client_suite()->get_option('branding.login_logo', '');
        }
        
        // Base styling
        $css .= "
        body.login {
            background-color: {$background_color} !important;";
            
        if (!empty($background_image)) {
            $css .= "
            background-image: url('{$background_image}') !important;
            background-size: cover !important;
            background-position: center !important;
            background-repeat: no-repeat !important;";
        }
        
        $css .= "
            min-height: 100vh !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        
        .login {
            width: 100% !important;
            max-width: 400px !important;
            margin: 0 auto !important;
            padding: 20px !important;
        }
        
        .login form {
            background: {$form_background} !important;
            border: 2px solid {$form_border_color} !important;
            border-radius: 12px !important;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1) !important;
            padding: 30px !important;
            margin-top: 20px !important;
        }
        
        .login .message,
        .login .success {
            background: {$form_background} !important;
            border: 2px solid {$form_border_color} !important;
            border-left: 4px solid {$button_color} !important;
            border-radius: 8px !important;
            padding: 15px !important;
            margin-bottom: 20px !important;
        }
        
        .login .error {
            background: {$form_background} !important;
            border: 2px solid #dc3232 !important;
            border-left: 4px solid #dc3232 !important;
            border-radius: 8px !important;
            padding: 15px !important;
            margin-bottom: 20px !important;
        }";
        
        // Logo styling
        if (!empty($custom_logo)) {
            $css .= "
            .login h1 a {
                background-image: url('{$custom_logo}') !important;
                background-size: contain !important;
                background-repeat: no-repeat !important;
                background-position: center !important;
                width: {$logo_width}px !important;
                height: {$logo_height}px !important;
                margin: 0 auto 25px !important;
                display: block !important;
            }
            
            .login h1 {
                text-align: center !important;
            }";
        }
        
        // Button and input styling
        $button_hover = $this->adjust_brightness($button_color, -20);
        $button_rgb = $this->hex_to_rgb($button_color);
        
        $css .= "
        .wp-core-ui .button-primary {
            background: {$button_color} !important;
            border-color: {$button_color} !important;
            color: white !important;
            text-shadow: none !important;
            box-shadow: none !important;
            border-radius: 6px !important;
            padding: 8px 16px !important;
            font-weight: 600 !important;
            transition: all 0.3s ease !important;
        }
        
        .wp-core-ui .button-primary:hover,
        .wp-core-ui .button-primary:focus {
            background: {$button_hover} !important;
            border-color: {$button_hover} !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 8px rgba({$button_rgb}, 0.3) !important;
        }
        
        input[type=text],
        input[type=password],
        input[type=email] {
            border: 2px solid {$form_border_color} !important;
            border-radius: 6px !important;
            padding: 12px 16px !important;
            font-size: 16px !important;
            transition: all 0.3s ease !important;
        }
        
        input[type=text]:focus,
        input[type=password]:focus,
        input[type=email]:focus {
            border-color: {$button_color} !important;
            box-shadow: 0 0 0 3px rgba({$button_rgb}, 0.2) !important;
            outline: none !important;
        }
        
        .login label {
            color: #333 !important;
            font-weight: 600 !important;
            font-size: 14px !important;
        }
        
        .login #nav a,
        .login #backtoblog a {
            color: {$button_color} !important;
            text-decoration: none !important;
            transition: all 0.3s ease !important;
        }
        
        .login #nav a:hover,
        .login #backtoblog a:hover {
            color: {$button_hover} !important;
        }";
        
        // Add any custom CSS
        $custom_css = bd_client_suite()->get_option('login.custom_css', '');
        
        return $css . "\n" . $custom_css;
    }
    
    /**
     * Add content to login head
     */
    public function add_login_head_content() {
        if (!bd_client_suite()->get_option('login.custom_login_page', false)) {
            return;
        }
        
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '<meta name="robots" content="noindex, nofollow">';
        
        // Add favicon if set
        $favicon = bd_client_suite()->get_option('branding.favicon', '');
        if (!empty($favicon)) {
            echo '<link rel="icon" type="image/x-icon" href="' . esc_url($favicon) . '">';
        }
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
        return get_bloginfo('name') . ' - ' . __('Powered by BD Client Suite', 'bd-client-suite');
    }
    
    /**
     * Custom login message
     */
    public function custom_login_message($message) {
        $custom_message = bd_client_suite()->get_option('login.welcome_message', '');
        
        if (!empty($custom_message)) {
            $message = '<div class="bd-login-message">' . wp_kses_post($custom_message) . '</div>' . $message;
        }
        
        return $message;
    }
    
    /**
     * Add login footer content
     */
    public function add_login_footer_content() {
        if (!bd_client_suite()->get_option('login.custom_login_page', false)) {
            return;
        }
        
        $footer_text = bd_client_suite()->get_option('login.footer_text', '');
        
        if (!empty($footer_text)) {
            echo '<div class="bd-login-footer">';
            echo wp_kses_post($footer_text);
            echo '</div>';
        }
        
        // Add some JavaScript for enhanced UX
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add loading state to submit button
            const form = document.getElementById('loginform');
            const submitBtn = document.getElementById('wp-submit');
            
            if (form && submitBtn) {
                form.addEventListener('submit', function() {
                    submitBtn.value = '<?php echo esc_js(__('Signing in...', 'bd-client-suite')); ?>';
                    submitBtn.disabled = true;
                });
            }
            
            // Auto-focus first empty input
            const userInput = document.getElementById('user_login');
            const passInput = document.getElementById('user_pass');
            
            if (userInput && userInput.value === '') {
                userInput.focus();
            } else if (passInput && passInput.value === '') {
                passInput.focus();
            }
            
            // Add shake animation for errors
            const errors = document.getElementById('login_error');
            if (errors) {
                errors.style.animation = 'shake 0.5s ease-in-out';
            }
        });
        </script>
        
        <style>
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .bd-login-message {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .bd-login-footer {
            text-align: center;
            margin-top: 30px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
        }
        
        .bd-login-footer a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
        }
        
        .bd-login-footer a:hover {
            color: #ffffff;
        }
        </style>
        <?php
    }
    
    /**
     * Custom login errors
     */
    public function custom_login_errors($errors) {
        // Customize error messages for better UX
        $custom_errors = array(
            'Invalid username or password.' => __('The username or password you entered is incorrect.', 'bd-client-suite'),
            'Invalid username.' => __('Please enter a valid username.', 'bd-client-suite'),
            'The password field is empty.' => __('Please enter your password.', 'bd-client-suite'),
            'The username field is empty.' => __('Please enter your username.', 'bd-client-suite')
        );
        
        foreach ($custom_errors as $original => $custom) {
            $errors = str_replace($original, $custom, $errors);
        }
        
        return $errors;
    }
    
    /**
     * Handle login failures
     */
    public function login_failed_redirect($username) {
        $redirect_to = bd_client_suite()->get_option('login.failed_redirect_url', '');
        
        if (!empty($redirect_to)) {
            wp_redirect(add_query_arg(array(
                'login' => 'failed',
                'username' => urlencode($username)
            ), $redirect_to));
            exit;
        }
    }
    
    /**
     * Adjust color brightness
     */
    private function adjust_brightness($hex, $percent) {
        $hex = str_replace('#', '', $hex);
        
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        $r = max(0, min(255, $r + ($r * $percent / 100)));
        $g = max(0, min(255, $g + ($g * $percent / 100)));
        $b = max(0, min(255, $b + ($b * $percent / 100)));
        
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
    
    /**
     * Convert hex to RGB
     */
    private function hex_to_rgb($hex) {
        $hex = str_replace('#', '', $hex);
        
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        return "{$r}, {$g}, {$b}";
    }
}
