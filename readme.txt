# BD Client Suite

Professional WordPress branding and customization plugin that transforms the admin experience for a polished, client-friendly environment.

## Features

### 🎨 Professional Branding
- **Custom Login Page**: Fully customizable login page with logo, colors, and styling
- **Admin Bar Branding**: Replace WordPress logo with custom branding
- **Custom Logos**: Upload and manage multiple logo variations
- **Color Schemes**: Create and apply custom admin color schemes
- **White-Label Options**: Remove/replace WordPress branding throughout admin

### 🚀 Smart Shortcuts System
- **Custom Admin Shortcuts**: Create quick-access buttons for any admin page
- **Categorized Organization**: Group shortcuts into logical categories
- **Auto-Detection**: Automatically detect and suggest shortcuts for common tasks
- **Visual Shortcuts**: Icon-based shortcuts with custom icons
- **Role-Based Shortcuts**: Different shortcuts for different user roles

### 🔐 Login & Redirection Management
- **Smart Login Redirection**: Redirect users to appropriate pages after login
- **Role-Based Redirection**: Different redirect rules for different user roles
- **Custom Welcome Pages**: Create personalized welcome/dashboard pages
- **Login Customization**: Custom login forms, messages, and styling
- **Security Enhancements**: Login attempt limiting, custom error messages

### 🎯 Client Experience Enhancement
- **Simplified Admin Menu**: Hide/reorganize menu items for cleaner interface
- **Custom Dashboard Widgets**: Create branded dashboard widgets
- **Welcome Messages**: Personalized welcome messages and instructions
- **Help Documentation**: Custom help sections and documentation
- **Client Portal**: Dedicated client area with relevant tools only

### ⚙️ Advanced Customization
- **CSS Injection**: Custom CSS for advanced styling
- **Footer Customization**: Custom admin footer text and links
- **Favicon Management**: Custom favicons for admin and frontend
- **Email Branding**: Custom email templates and branding
- **Export/Import**: Full configuration backup and restore

## Installation

1. Upload the plugin files to `/wp-content/plugins/bd-client-suite/`
2. Activate the plugin through the WordPress admin
3. Go to **Buene Data > Client Suite** to configure your settings

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## Quick Start

1. **Configure Branding**: Upload your logo and set custom colors
2. **Create Shortcuts**: Add quick-access buttons for common tasks
3. **Customize Login**: Design a professional login page
4. **Set Redirects**: Define where users go after login
5. **Apply Advanced Settings**: Add custom CSS and footer content

## BD Plugin Integration

BD Client Suite automatically integrates with other Buene Data plugins:

- **BD CleanDash**: Main dashboard and overview
- **BD Security Suite**: Advanced security features *(Coming Soon)*
- **BD Analytics Pro**: Advanced analytics and reporting *(Coming Soon)*
- **BD Performance**: Site optimization tools *(Coming Soon)*

## Configuration

### Branding Settings
```php
// Programmatically set branding options
bd_client_suite()->update_option('branding.login_logo', 'https://example.com/logo.png');
bd_client_suite()->update_option('branding.custom_colors.primary', '#667eea');
```

### Shortcuts Management
```php
// Add custom shortcuts programmatically
global $wpdb;
$wpdb->insert(
    $wpdb->prefix . 'bd_client_suite_shortcuts',
    array(
        'name' => 'Custom Shortcut',
        'url' => admin_url('admin.php?page=custom-page'),
        'icon' => '⚡',
        'category' => 'custom',
        'active' => 1
    )
);
```

### Login Redirects
```php
// Custom redirect filter
add_filter('bd_client_suite_login_redirect', function($redirect_url, $user) {
    if (in_array('custom_role', $user->roles)) {
        return admin_url('admin.php?page=custom-dashboard');
    }
    return $redirect_url;
}, 10, 2);
```

## Hooks & Filters

### Actions
- `bd_client_suite_init` - Plugin initialization
- `bd_client_suite_activated` - Plugin activation
- `bd_client_suite_after_login` - After user login
- `bd_client_suite_settings_saved` - After settings save

### Filters
- `bd_client_suite_login_redirect` - Modify login redirect URL
- `bd_client_suite_shortcut_categories` - Modify shortcut categories
- `bd_client_suite_color_schemes` - Add custom color schemes
- `bd_client_suite_admin_footer` - Customize admin footer

## Database Tables

### Shortcuts (`wp_bd_client_suite_shortcuts`)
- `id` - Unique identifier
- `name` - Shortcut display name
- `url` - Target URL
- `icon` - Display icon (emoji)
- `category` - Shortcut category
- `user_role` - Target user role
- `sort_order` - Display order
- `active` - Status flag

### Categories (`wp_bd_client_suite_categories`)
- `id` - Unique identifier
- `name` - Category display name
- `slug` - Category slug
- `icon` - Category icon
- `color` - Category color
- `sort_order` - Display order
- `active` - Status flag

## Troubleshooting

### Common Issues

**Plugin conflicts with other branding plugins:**
- Deactivate conflicting plugins
- Check for CSS conflicts in browser console

**Login customizations not appearing:**
- Clear browser cache
- Check if login customization is enabled in settings
- Verify image URLs are accessible

**Shortcuts not saving:**
- Check database permissions
- Verify AJAX requests in browser network tab
- Check PHP error logs

### Debug Mode
Enable WordPress debug mode to troubleshoot issues:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Support

- **Documentation**: [https://buenedata.no/docs/bd-client-suite/](https://buenedata.no/docs/bd-client-suite/)
- **Support Forum**: [https://buenedata.no/support/](https://buenedata.no/support/)
- **Email**: support@buenedata.no

## Changelog

### 1.0.0
- Initial release
- Complete branding system
- Smart shortcuts functionality
- Login customization
- Redirect management
- BD plugin integration

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Developed by [Buene Data](https://buenedata.no/) - Professional WordPress solutions for modern websites.

---

*Transform your WordPress admin into a professional, client-friendly environment with BD Client Suite.*
