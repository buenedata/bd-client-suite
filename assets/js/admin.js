/**
 * BD Client Suite Admin JavaScript
 * 
 * @package BD_Client_Suite
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // Initialize when DOM is ready
    $(document).ready(function() {
        console.log('BD Client Suite: DOM ready, jQuery available');
        BDClientSuite.init();
    });

    // Main BD Client Suite object
    window.BDClientSuite = {
        
        /**
         * Initialize all components
         */
        init: function() {
            this.initColorPickers();
            this.initMediaUploaders();
            this.initToggles();
            this.initTabs();
            this.initFormValidation();
            this.initAjaxForms();
            this.initNotifications();
            this.initShortcutManagement();
            this.initCategoryManagement();
        },

        /**
         * Initialize color pickers
         */
        initColorPickers: function() {
            $('.bd-color-picker input[type="text"]').wpColorPicker({
                change: function(event, ui) {
                    const color = ui.color.toString();
                    $(this).trigger('bd:color-changed', [color]);
                },
                clear: function() {
                    $(this).trigger('bd:color-cleared');
                }
            });
        },

        /**
         * Initialize media uploaders
         */
        initMediaUploaders: function() {
            $('.bd-media-uploader').each(function() {
                const $uploader = $(this);
                const $input = $uploader.find('input[type="hidden"]');
                const $preview = $uploader.find('.bd-media-preview');
                const $info = $uploader.find('.bd-media-info');

                // Upload button click
                $uploader.on('click', '.bd-upload-button', function(e) {
                    e.preventDefault();
                    BDClientSuite.openMediaLibrary($uploader, $input, $preview, $info);
                });

                // Remove button click
                $uploader.on('click', '.bd-remove-media', function(e) {
                    e.preventDefault();
                    BDClientSuite.removeMedia($uploader, $input, $preview, $info);
                });

                // Drag and drop
                $uploader.on('dragover', function(e) {
                    e.preventDefault();
                    $(this).addClass('bd-dragover');
                });

                $uploader.on('dragleave', function(e) {
                    e.preventDefault();
                    $(this).removeClass('bd-dragover');
                });

                $uploader.on('drop', function(e) {
                    e.preventDefault();
                    $(this).removeClass('bd-dragover');
                    // Handle file drop (would need additional implementation)
                });
            });
        },

        /**
         * Open WordPress media library
         */
        openMediaLibrary: function($uploader, $input, $preview, $info) {
            const mediaType = $uploader.data('media-type') || 'image';
            
            const mediaUploader = wp.media({
                title: bdClientSuite.strings.selectMedia || 'Select Media',
                button: {
                    text: bdClientSuite.strings.useMedia || 'Use This Media'
                },
                multiple: false,
                library: {
                    type: mediaType
                }
            });

            mediaUploader.on('select', function() {
                const attachment = mediaUploader.state().get('selection').first().toJSON();
                
                $input.val(attachment.url);
                
                if (mediaType === 'image') {
                    $preview.attr('src', attachment.url).show();
                    $info.text(attachment.filename);
                } else {
                    $info.text(attachment.filename + ' (' + BDClientSuite.formatFileSize(attachment.filesizeInBytes) + ')');
                }
                
                $uploader.addClass('has-media');
                $uploader.trigger('bd:media-selected', [attachment]);
            });

            mediaUploader.open();
        },

        /**
         * Remove selected media
         */
        removeMedia: function($uploader, $input, $preview, $info) {
            $input.val('');
            $preview.hide();
            $info.text($uploader.data('placeholder') || 'No media selected');
            $uploader.removeClass('has-media');
            $uploader.trigger('bd:media-removed');
        },

        /**
         * Initialize toggle switches
         */
        initToggles: function() {
            $('.bd-toggle input[type="checkbox"]').on('change', function() {
                const $toggle = $(this).closest('.bd-toggle');
                const isChecked = $(this).is(':checked');
                
                $toggle.toggleClass('checked', isChecked);
                $(this).trigger('bd:toggle-changed', [isChecked]);
            });
        },

        /**
         * Initialize tabs
         */
        initTabs: function() {
            $('.bd-tabs').each(function() {
                const $tabs = $(this);
                const $tabButtons = $tabs.find('.bd-tab-button');
                const $tabContents = $('.bd-tab-content');

                $tabButtons.on('click', function(e) {
                    e.preventDefault();
                    
                    const targetTab = $(this).data('tab');
                    
                    // Update active states
                    $tabButtons.removeClass('active');
                    $(this).addClass('active');
                    
                    $tabContents.removeClass('active');
                    $('#bd-tab-' + targetTab).addClass('active');
                    
                    // Trigger event
                    $tabs.trigger('bd:tab-changed', [targetTab]);
                });
            });
        },

        /**
         * Initialize form validation
         */
        initFormValidation: function() {
            $('form[data-bd-validate]').each(function() {
                const $form = $(this);
                
                $form.on('submit', function(e) {
                    if (!BDClientSuite.validateForm($form)) {
                        e.preventDefault();
                        return false;
                    }
                });

                // Real-time validation
                $form.find('input, select, textarea').on('blur', function() {
                    BDClientSuite.validateField($(this));
                });
            });
        },

        /**
         * Validate form
         */
        validateForm: function($form) {
            console.log('Validating form:', $form[0]);
            let isValid = true;
            
            $form.find('[required]').each(function() {
                const fieldValid = BDClientSuite.validateField($(this));
                console.log('Field', $(this).attr('name'), 'valid:', fieldValid, 'value:', $(this).val());
                if (!fieldValid) {
                    isValid = false;
                }
            });

            console.log('Overall form valid:', isValid);
            return isValid;
        },

        /**
         * Validate individual field
         */
        validateField: function($field) {
            const value = $field.val().trim();
            const fieldType = $field.attr('type');
            let isValid = true;
            let errorMessage = '';

            // Required validation
            if ($field.attr('required') && !value) {
                isValid = false;
                errorMessage = 'This field is required.';
            }

            // Email validation
            if (fieldType === 'email' && value && !BDClientSuite.isValidEmail(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid email address.';
            }

            // URL validation
            if (fieldType === 'url' && value && !BDClientSuite.isValidUrl(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid URL.';
            }

            // Update field appearance
            $field.toggleClass('bd-field-error', !isValid);
            
            // Show/hide error message
            let $errorMsg = $field.siblings('.bd-field-error-message');
            if (!isValid) {
                if (!$errorMsg.length) {
                    $errorMsg = $('<div class="bd-field-error-message"></div>');
                    $field.after($errorMsg);
                }
                $errorMsg.text(errorMessage);
            } else {
                $errorMsg.remove();
            }

            return isValid;
        },

        /**
         * Initialize AJAX forms
         */
        initAjaxForms: function() {
            // Settings form
            $('#bd-settings-form').on('submit', function(e) {
                e.preventDefault();
                BDClientSuite.saveSettings($(this));
            });

            // Quick save buttons
            $(document).on('click', '.bd-quick-save', function(e) {
                e.preventDefault();
                const $form = $(this).closest('form');
                BDClientSuite.saveSettings($form, true);
            });
        },

        /**
         * Save settings via AJAX
         */
        saveSettings: function($form, isQuickSave) {
            const $button = $form.find('[type="submit"]');
            const originalText = $button.text();
            
            // Show loading state
            $button.text(bdClientSuite.strings.saving).prop('disabled', true);
            $form.addClass('bd-loading');

            const formData = new FormData($form[0]);
            formData.append('action', 'bd_client_suite_save_settings');
            formData.append('nonce', bdClientSuite.nonce);
            formData.append('tab', $form.find('[name="current_tab"]').val());

            $.ajax({
                url: bdClientSuite.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        BDClientSuite.showNotification('success', response.data.message || bdClientSuite.strings.saved);
                        
                        if (!isQuickSave) {
                            // Trigger saved event
                            $form.trigger('bd:settings-saved', [response.data]);
                        }
                    } else {
                        BDClientSuite.showNotification('error', response.data.message || bdClientSuite.strings.error);
                    }
                },
                error: function(xhr, status, error) {
                    BDClientSuite.showNotification('error', bdClientSuite.strings.error);
                    console.error('AJAX Error:', error);
                },
                complete: function() {
                    $button.text(originalText).prop('disabled', false);
                    $form.removeClass('bd-loading');
                }
            });
        },

        /**
         * Initialize notifications
         */
        initNotifications: function() {
            // Auto-hide notifications after 5 seconds
            $('.bd-notification').each(function() {
                const $notification = $(this);
                setTimeout(function() {
                    $notification.fadeOut();
                }, 5000);
            });

            // Close button for notifications
            $(document).on('click', '.bd-notification .bd-close', function() {
                $(this).closest('.bd-notification').fadeOut();
            });
        },

        /**
         * Show notification
         */
        showNotification: function(type, message, duration) {
            duration = duration || 5000;
            
            const $notification = $('<div class="bd-notification ' + type + '">' +
                '<span class="bd-notification-message">' + message + '</span>' +
                '<button type="button" class="bd-close">&times;</button>' +
                '</div>');

            // Find or create notification container
            let $container = $('.bd-notifications');
            if (!$container.length) {
                $container = $('<div class="bd-notifications"></div>');
                $('body').append($container);
            }

            $container.append($notification);
            
            // Auto-hide
            setTimeout(function() {
                $notification.fadeOut(function() {
                    $(this).remove();
                });
            }, duration);
        },

        /**
         * Utility functions
         */
        isValidEmail: function(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        },

        isValidUrl: function(url) {
            try {
                new URL(url);
                return true;
            } catch (e) {
                return false;
            }
        },

        formatFileSize: function(bytes) {
            if (bytes === 0) return '0 Bytes';
            
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },

        /**
         * Debounce function
         */
        debounce: function(func, wait, immediate) {
            let timeout;
            return function() {
                const context = this;
                const args = arguments;
                const later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                const callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        },

        /**
         * Initialize shortcut management
         */
        initShortcutManagement: function() {
            console.log('Initializing shortcut management');
            
            // Add new shortcut button (multiple possible IDs)
            $(document).on('click', '#bd-add-shortcut, #bd-add-first-shortcut', function(e) {
                e.preventDefault();
                console.log('Add shortcut button clicked');
                BDClientSuite.openShortcutModal();
            });

            // Add new category button (multiple possible IDs)
            $(document).on('click', '#bd-add-category, #bd-add-first-category', function(e) {
                e.preventDefault();
                console.log('Add category button clicked');
                BDClientSuite.openCategoryModal();
            });

            // Edit shortcut button
            $(document).on('click', '.bd-edit-shortcut', function(e) {
                e.preventDefault();
                const shortcutId = $(this).closest('.bd-shortcut-item').data('id');
                console.log('Edit shortcut button clicked, ID:', shortcutId);
                BDClientSuite.editShortcut(shortcutId);
            });

            // Delete shortcut button
            $(document).on('click', '.bd-delete-shortcut', function(e) {
                e.preventDefault();
                const shortcutId = $(this).closest('.bd-shortcut-item').data('id');
                console.log('Delete shortcut button clicked, ID:', shortcutId);
                BDClientSuite.deleteShortcut(shortcutId);
            });

            // Shortcut form submission - simplified
            $(document).on('submit', '#bd-shortcut-form', function(e) {
                e.preventDefault();
                console.log('Shortcut form submitted');
                BDClientSuite.saveShortcut();
                return false;
            });

            // Direct button click handler
            $(document).on('click', '#bd-shortcut-form button[type="submit"]', function(e) {
                e.preventDefault();
                console.log('Save shortcut button clicked');
                BDClientSuite.saveShortcut();
            });

            // Modal close
            $(document).on('click', '.bd-modal-close', function(e) {
                e.preventDefault();
                console.log('Modal close button clicked');
                $(this).closest('.bd-modal').hide();
            });

            // Click outside modal to close
            $(document).on('click', '.bd-modal', function(e) {
                if (e.target === this) {
                    console.log('Clicked outside modal, closing');
                    $(this).hide();
                }
            });
        },

        /**
         * Initialize category management
         */
        initCategoryManagement: function() {
            // Add new category button
            $(document).on('click', '#bd-add-category', function(e) {
                e.preventDefault();
                BDClientSuite.openCategoryModal();
            });

            // Edit category button
            $(document).on('click', '.bd-edit-category', function(e) {
                e.preventDefault();
                const categoryId = $(this).closest('.bd-category-item').data('id');
                BDClientSuite.editCategory(categoryId);
            });

            // Delete category button
            $(document).on('click', '.bd-delete-category', function(e) {
                e.preventDefault();
                const categoryId = $(this).closest('.bd-category-item').data('id');
                BDClientSuite.deleteCategory(categoryId);
            });

            // Category form submission
            $(document).on('submit', '#bd-category-form', function(e) {
                e.preventDefault();
                console.log('Category form submitted via AJAX');
                BDClientSuite.saveCategory();
                return false;
            });
        },

        /**
         * Open shortcut modal
         */
        openShortcutModal: function(shortcutData = null) {
            console.log('Opening shortcut modal with data:', shortcutData);
            const $modal = $('#bd-shortcut-modal');
            const $form = $('#bd-shortcut-form');
            const isEdit = shortcutData !== null;

            console.log('Modal found:', $modal.length, 'Form found:', $form.length);

            // Reset form
            $form[0].reset();
            $form.find('input[name="shortcut_id"]').remove();

            if (isEdit) {
                $('#bd-shortcut-modal-title').text(bdClientSuite.strings.editShortcut || 'Edit Shortcut');
                $('#bd-shortcut-name').val(shortcutData.name);
                $('#bd-shortcut-url').val(shortcutData.url);
                $('#bd-shortcut-icon').val(shortcutData.icon);
                $('#bd-shortcut-category').val(shortcutData.category_slug);
                
                // Handle roles
                $('#bd-shortcut-roles option').prop('selected', false);
                if (shortcutData.user_roles) {
                    shortcutData.user_roles.forEach(function(role) {
                        $('#bd-shortcut-roles option[value="' + role + '"]').prop('selected', true);
                    });
                }

                $form.append('<input type="hidden" name="shortcut_id" value="' + shortcutData.id + '">');
            } else {
                $('#bd-shortcut-modal-title').text(bdClientSuite.strings.addShortcut || 'Add Shortcut');
                $('#bd-shortcut-roles option[value="all"]').prop('selected', true);
            }

            $modal.show();
            console.log('Modal should now be visible');
            console.log('Modal display style:', $modal.css('display'));
            console.log('Modal visibility:', $modal.is(':visible'));
            
            // Focus the first input to help with debugging
            setTimeout(function() {
                $form.find('input:first').focus();
            }, 100);
        },

        /**
         * Open category modal
         */
        openCategoryModal: function(categoryData = null) {
            const $modal = $('#bd-category-modal');
            const $form = $('#bd-category-form');
            const isEdit = categoryData !== null;

            // Reset form
            $form[0].reset();
            $form.find('input[name="category_id"]').remove();

            if (isEdit) {
                $('#bd-category-modal-title').text(bdClientSuite.strings.editCategory || 'Edit Category');
                $('#bd-category-name').val(categoryData.name);
                $('#bd-category-icon').val(categoryData.icon);
                $('#bd-category-color').val(categoryData.color);
                
                $form.append('<input type="hidden" name="category_id" value="' + categoryData.id + '">');
            } else {
                $('#bd-category-modal-title').text(bdClientSuite.strings.addCategory || 'Add Category');
                $('#bd-category-color').val('#667eea');
            }

            // Initialize color picker if not already done
            $('#bd-category-color').wpColorPicker();

            $modal.show();
        },

        /**
         * Save shortcut
         */
        saveShortcut: function() {
            console.log('saveShortcut function called');
            
            // Prevent multiple simultaneous saves
            if (this._savingShortcut) {
                console.log('Already saving shortcut, ignoring duplicate call');
                return;
            }
            this._savingShortcut = true;
            
            const $form = $('#bd-shortcut-form');
            const $button = $form.find('button[type="submit"]');
            
            // Disable button and show saving state
            $button.prop('disabled', true).text('Saving...');
            
            // Create a simple object with the form data
            const formData = new FormData();
            
            // Manually collect form data
            const name = $('#bd-shortcut-name').val();
            const url = $('#bd-shortcut-url').val();
            const icon = $('#bd-shortcut-icon').val();
            let category = $('#bd-shortcut-category').val();
            const shortcutId = $form.find('input[name="shortcut_id"]').val();
            
            // Validate required fields
            if (!name || !url) {
                console.log('Required fields missing');
                BDClientSuite.showNotification('error', 'Name and URL are required');
                $button.prop('disabled', false).text('Save Shortcut');
                this._savingShortcut = false;
                return;
            }
            
            // Check if category is selected, if not, use 'general' as default
            if (!category || $('#bd-shortcut-category option').length === 0) {
                console.log('No category selected or no categories available, using "general" as default');
                category = 'general';
            }
            
            // Add form data
            formData.append('name', name);
            formData.append('url', url);
            formData.append('icon', icon);
            formData.append('category', category);
            
            // Add shortcut ID if editing
            if (shortcutId) {
                formData.append('shortcut_id', shortcutId);
            }
            
            // Add roles - always include 'all' as a fallback
            formData.append('roles[]', 'all');
            
            // Add AJAX action and nonce
            formData.append('action', 'bd_client_suite_save_shortcut');
            formData.append('nonce', bdClientSuite.nonce);
            
            console.log('Form data created with these values:');
            console.log('- Name:', name);
            console.log('- URL:', url);
            console.log('- Icon:', icon);
            console.log('- Category:', category);
            console.log('- Shortcut ID:', shortcutId || 'New shortcut');

            const self = this;
            this.makeAjaxRequest(formData, function(response) {
                self._savingShortcut = false;
                $button.prop('disabled', false).text('Save Shortcut');
                
                BDClientSuite.showNotification('success', response.data.message);
                $('#bd-shortcut-modal').hide();
                // Reload with proper URL parameters
                window.location.href = window.location.pathname + '?page=bd-client-suite-settings&tab=shortcuts';
            }, function(error) {
                // Error callback
                self._savingShortcut = false;
                $button.prop('disabled', false).text('Save Shortcut');
                console.log('Save shortcut failed:', error);
            });
        },

        /**
         * Save category
         */
        saveCategory: function() {
            const $form = $('#bd-category-form');
            const formData = new FormData($form[0]);
            
            formData.append('action', 'bd_client_suite_save_category');
            formData.append('nonce', bdClientSuite.nonce);

            this.makeAjaxRequest(formData, function(response) {
                BDClientSuite.showNotification('success', response.data.message);
                $('#bd-category-modal').hide();
                // Reload with proper URL parameters
                window.location.href = window.location.pathname + '?page=bd-client-suite-settings&tab=shortcuts';
            });
        },

        /**
         * Edit shortcut
         */
        editShortcut: function(shortcutId) {
            // Get shortcut data from the DOM or make AJAX request
            const $item = $('[data-id="' + shortcutId + '"]');
            const shortcutData = {
                id: shortcutId,
                name: $item.find('.bd-shortcut-name').text(),
                url: $item.find('.bd-shortcut-url').text(),
                icon: $item.find('.bd-shortcut-icon').text(),
                category_slug: $item.data('category-slug') || '',
                user_roles: $item.data('user-roles') || ['all']
            };

            this.openShortcutModal(shortcutData);
        },

        /**
         * Edit category
         */
        editCategory: function(categoryId) {
            // Get category data from the DOM
            const $item = $('[data-id="' + categoryId + '"]');
            const categoryData = {
                id: categoryId,
                name: $item.find('.bd-category-name').text(),
                icon: $item.find('.bd-category-icon').text(),
                color: $item.data('color') || '#667eea'
            };

            this.openCategoryModal(categoryData);
        },

        /**
         * Delete shortcut
         */
        deleteShortcut: function(shortcutId) {
            if (!confirm(bdClientSuite.strings.confirmDeleteShortcut || 'Are you sure you want to delete this shortcut?')) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'bd_client_suite_delete_shortcut');
            formData.append('shortcut_id', shortcutId);
            formData.append('nonce', bdClientSuite.nonce);

            this.makeAjaxRequest(formData, function(response) {
                BDClientSuite.showNotification('success', response.data.message);
                $('[data-id="' + shortcutId + '"]').fadeOut(function() {
                    $(this).remove();
                });
            });
        },

        /**
         * Delete category
         */
        deleteCategory: function(categoryId) {
            if (!confirm(bdClientSuite.strings.confirmDeleteCategory || 'Are you sure you want to delete this category?')) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'bd_client_suite_delete_category');
            formData.append('category_id', categoryId);
            formData.append('nonce', bdClientSuite.nonce);

            this.makeAjaxRequest(formData, function(response) {
                BDClientSuite.showNotification('success', response.data.message);
                $('[data-id="' + categoryId + '"]').fadeOut(function() {
                    $(this).remove();
                });
            });
        },

        /**
         * Make AJAX request
         */
        makeAjaxRequest: function(formData, successCallback, errorCallback) {
            console.log('Making AJAX request to:', bdClientSuite.ajaxUrl);
            console.log('Form data entries:');
            for (let [key, value] of formData.entries()) {
                console.log('  ', key, ':', value);
            }
            
            // Show a notification that we're processing the request
            BDClientSuite.showNotification('info', 'Processing request...');
            
            $.ajax({
                url: bdClientSuite.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log('AJAX Success response:', response);
                    
                    // Check if the response is valid JSON
                    if (typeof response !== 'object') {
                        console.log('Response is not a valid JSON object:', response);
                        BDClientSuite.showNotification('error', 'Invalid response from server');
                        if (typeof errorCallback === 'function') {
                            errorCallback('Invalid response');
                        }
                        return;
                    }
                    
                    if (response.success) {
                        console.log('Request successful:', response.data);
                        if (typeof successCallback === 'function') {
                            successCallback(response);
                        }
                    } else {
                        console.log('AJAX returned failure:', response.data);
                        let errorMsg = 'An error occurred';
                        
                        if (response.data && typeof response.data === 'string') {
                            errorMsg = response.data;
                        } else if (response.data && response.data.message) {
                            errorMsg = response.data.message;
                        }
                        
                        BDClientSuite.showNotification('error', errorMsg);
                        if (typeof errorCallback === 'function') {
                            errorCallback(response.data);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error details:');
                    console.log('Status:', status);
                    console.log('Error:', error);
                    console.log('Response text:', xhr.responseText);
                    console.log('Status code:', xhr.status);
                    
                    let errorMsg = 'Server error: ' + error;
                    
                    // Try to parse the response text as JSON
                    try {
                        const jsonResponse = JSON.parse(xhr.responseText);
                        if (jsonResponse && jsonResponse.message) {
                            errorMsg = jsonResponse.message;
                        }
                    } catch (e) {
                        // If parsing fails, use the raw response text if it's not too long
                        if (xhr.responseText && xhr.responseText.length < 100) {
                            errorMsg = xhr.responseText;
                        }
                    }
                    
                    BDClientSuite.showNotification('error', errorMsg);
                    if (typeof errorCallback === 'function') {
                        errorCallback(error);
                    }
                }
            });
        }
    };

})(jQuery);
