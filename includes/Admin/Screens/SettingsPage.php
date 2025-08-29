<?php
namespace WhatsPro\Premium\Admin\Screens;

use WhatsPro\Premium\Services\LicenseManager;

class SettingsPage {
    public static function render() {
        // Handle form submissions
        self::handle_form_submission();

        $license_info = LicenseManager::get_license_info();

        ?>
        <div class="wrap">
            <h1>WhatsPro Settings</h1>

            <!-- Icon Shortcuts -->
            <div class="icon-shortcuts">
                <div class="icon-shortcut" onclick="showSection('license')">
                    <svg viewBox="0 0 24 24"><path d="M12,1L3,5V11C3,16.55 6.84,21.74 12,23C17.16,21.74 21,16.55 21,11V5L12,1M10,17L6,13L7.41,11.59L10,14.17L16.59,7.58L18,9L10,17Z"/></svg>
                    <span>License</span>
                </div>
                <div class="icon-shortcut" onclick="showSection('general')">
                    <svg viewBox="0 0 24 24"><path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4M12,6A6,6 0 0,0 6,12A6,6 0 0,0 12,18A6,6 0 0,0 18,12A6,6 0 0,0 12,6M12,8A4,4 0 0,1 16,12A4,4 0 0,1 12,16A4,4 0 0,1 8,12A4,4 0 0,1 12,8Z"/></svg>
                    <span>General</span>
                </div>
                <div class="icon-shortcut" onclick="showSection('messaging')">
                    <svg viewBox="0 0 24 24"><path d="M20 2H4C2.9 2 2 2.9 2 4V22L6 18H20C21.1 18 22 17.1 22 16V4C22 2.9 21.1 2 20 2M20 16H5.2L4 17.2V4H20V16Z"/></svg>
                    <span>Messaging</span>
                </div>
                <div class="icon-shortcut" onclick="showSection('premium')">
                    <svg viewBox="0 0 24 24"><path d="M12,2L15.09,8.26L22,9.27L17,14.14L18.18,21.02L12,17.77L5.82,21.02L7,14.14L2,9.27L8.91,8.26L12,2Z"/></svg>
                    <span>Premium</span>
                </div>
            </div>

            <!-- License Status Banner -->
            <?php if (!$license_info['valid']): ?>
                <div class="card" style="border-left: 4px solid #dc3545; background: #f8d7da;">
                    <div class="premium-badge" style="background: linear-gradient(135deg, #dc3545, #c82333);">
                        ⚠️ License Required
                    </div>
                    <p>Your WhatsPro Premium license is not active. Please activate your license to access premium features.</p>
                    <button class="wpp-btn" onclick="showSection('license')">Activate License</button>
                </div>
            <?php else: ?>
                <div class="card" style="border-left: 4px solid #28a745; background: #d4edda;">
                    <div class="premium-badge">
                        ✅ Premium Active
                    </div>
                    <p>License valid until <?php echo esc_html($license_info['expires']); ?> for domain <?php echo esc_html($license_info['domain']); ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <?php wp_nonce_field('wpp_settings_nonce'); ?>

                <!-- License Settings -->
                <div class="wpp-collapsible" id="license-section">
                    <div class="wpp-collapsible-header">
                        <h3>🔑 License Management</h3>
                        <span class="toggle-icon">▼</span>
                    </div>
                    <div class="wpp-collapsible-content">
                        <?php echo LicenseManager::render_license_settings(); ?>
                    </div>
                </div>

                <!-- General Settings -->
                <div class="wpp-collapsible" id="general-section">
                    <div class="wpp-collapsible-header">
                        <h3>⚙️ General Settings</h3>
                        <span class="toggle-icon">▼</span>
                    </div>
                    <div class="wpp-collapsible-content">
                        <div class="wpp-form-group">
                            <label for="business_name">Business Name:</label>
                            <input type="text" id="business_name" name="business_name"
                                   value="<?php echo esc_attr(get_option('wpp_business_name', '')); ?>">
                            <small>The name of your business as it will appear in messages.</small>
                        </div>

                        <div class="wpp-form-group">
                            <label for="timezone">Timezone:</label>
                            <select id="timezone" name="timezone">
                                <?php
                                $current_tz = get_option('wpp_timezone', 'UTC');
                                $timezones = timezone_identifiers_list();
                                foreach ($timezones as $tz) {
                                    echo '<option value="' . esc_attr($tz) . '" ' . selected($current_tz, $tz, false) . '>' . esc_html($tz) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="wpp-form-group">
                            <label for="language">Default Language:</label>
                            <select id="language" name="language">
                                <?php
                                $current_lang = get_option('wpp_language', 'en');
                                $languages = [
                                    'en' => 'English',
                                    'es' => 'Spanish',
                                    'fr' => 'French',
                                    'de' => 'German',
                                    'pt' => 'Portuguese',
                                    'ar' => 'Arabic'
                                ];
                                foreach ($languages as $code => $name) {
                                    echo '<option value="' . esc_attr($code) . '" ' . selected($current_lang, $code, false) . '>' . esc_html($name) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="wpp-form-group">
                            <label>
                                <input type="checkbox" name="enable_logging" value="1" <?php checked(get_option('wpp_enable_logging', '0'), '1'); ?>>
                                Enable Activity Logging
                            </label>
                            <small>Log all plugin activities for debugging and analytics.</small>
                        </div>
                    </div>
                </div>

                <!-- Messaging Settings -->
                <div class="wpp-collapsible" id="messaging-section">
                    <div class="wpp-collapsible-header">
                        <h3>💬 Messaging Settings</h3>
                        <span class="toggle-icon">▼</span>
                    </div>
                    <div class="wpp-collapsible-content">
                        <div class="wpp-form-group">
                            <label for="default_sender">Default Sender Name:</label>
                            <input type="text" id="default_sender" name="default_sender"
                                   value="<?php echo esc_attr(get_option('wpp_default_sender', '')); ?>">
                            <small>The name that will appear as the sender of messages.</small>
                        </div>

                        <div class="wpp-form-group">
                            <label for="message_signature">Message Signature:</label>
                            <textarea id="message_signature" name="message_signature" rows="3"><?php echo esc_textarea(get_option('wpp_message_signature', '')); ?></textarea>
                            <small>Text that will be appended to all outgoing messages.</small>
                        </div>

                        <div class="wpp-form-group">
                            <label for="quiet_hours_start">Quiet Hours Start:</label>
                            <input type="time" id="quiet_hours_start" name="quiet_hours_start"
                                   value="<?php echo esc_attr(get_option('wpp_quiet_hours_start', '22:00')); ?>">
                        </div>

                        <div class="wpp-form-group">
                            <label for="quiet_hours_end">Quiet Hours End:</label>
                            <input type="time" id="quiet_hours_end" name="quiet_hours_end"
                                   value="<?php echo esc_attr(get_option('wpp_quiet_hours_end', '08:00')); ?>">
                        </div>

                        <div class="wpp-form-group">
                            <label>
                                <input type="checkbox" name="respect_quiet_hours" value="1" <?php checked(get_option('wpp_respect_quiet_hours', '1'), '1'); ?>>
                                Respect Quiet Hours
                            </label>
                            <small>Don't send messages during quiet hours unless urgent.</small>
                        </div>

                        <div class="wpp-form-group">
                            <label for="frequency_cap">Frequency Cap (messages per day):</label>
                            <input type="number" id="frequency_cap" name="frequency_cap" min="1" max="1000"
                                   value="<?php echo esc_attr(get_option('wpp_frequency_cap', '50')); ?>">
                            <small>Maximum number of messages that can be sent to a single contact per day.</small>
                        </div>
                    </div>
                </div>

                <!-- Premium Settings -->
                <div class="wpp-collapsible" id="premium-section">
                    <div class="wpp-collapsible-header">
                        <h3>⭐ Premium Settings</h3>
                        <span class="toggle-icon">▼</span>
                    </div>
                    <div class="wpp-collapsible-content">
                        <?php if ($license_info['valid']): ?>
                            <div class="premium-settings">
                                <div class="wpp-form-group">
                                    <label>
                                        <input type="checkbox" name="enable_meta_extraction" value="1" <?php checked(get_option('wpp_enable_meta_extraction', '0'), '1'); ?>>
                                        Enable WhatsApp Meta Info Extraction
                                    </label>
                                    <small>Automatically extract and store contact information from incoming messages.</small>
                                </div>

                                <div class="wpp-form-group">
                                    <label>
                                        <input type="checkbox" name="enable_excel_upload" value="1" <?php checked(get_option('wpp_enable_excel_upload', '0'), '1'); ?>>
                                        Enable Excel Upload & Smart Lists
                                    </label>
                                    <small>Allow uploading Excel files to import and manage contact lists.</small>
                                </div>

                                <div class="wpp-form-group">
                                    <label>
                                        <input type="checkbox" name="enable_advanced_segmentation" value="1" <?php checked(get_option('wpp_enable_advanced_segmentation', '0'), '1'); ?>>
                                        Enable Advanced Segmentation
                                    </label>
                                    <small>Use advanced filters to create targeted contact segments.</small>
                                </div>

                                <div class="wpp-form-group">
                                    <label>
                                        <input type="checkbox" name="enable_analytics" value="1" <?php checked(get_option('wpp_enable_analytics', '1'), '1'); ?>>
                                        Enable Analytics Dashboard
                                    </label>
                                    <small>Access detailed analytics and reporting features.</small>
                                </div>

                                <div class="wpp-form-group">
                                    <label>
                                        <input type="checkbox" name="enable_waba_integration" value="1" <?php checked(get_option('wpp_enable_waba_integration', '0'), '1'); ?>>
                                        Enable WhatsApp Business API
                                    </label>
                                    <small>Connect and manage WhatsApp Business API integration.</small>
                                </div>

                                <div class="wpp-form-group">
                                    <label for="analytics_retention">Analytics Data Retention (days):</label>
                                    <input type="number" id="analytics_retention" name="analytics_retention" min="30" max="365"
                                           value="<?php echo esc_attr(get_option('wpp_analytics_retention', '90')); ?>">
                                    <small>How long to keep analytics data before automatic cleanup.</small>
                                </div>

                                <div class="wpp-form-group">
                                    <label for="export_format">Default Export Format:</label>
                                    <select id="export_format" name="export_format">
                                        <?php
                                        $current_format = get_option('wpp_export_format', 'csv');
                                        $formats = ['csv' => 'CSV', 'xlsx' => 'Excel'];
                                        foreach ($formats as $value => $label) {
                                            echo '<option value="' . esc_attr($value) . '" ' . selected($current_format, $value, false) . '>' . esc_html($label) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="wpp-form-group">
                                    <label>
                                        <input type="checkbox" name="enable_auto_backup" value="1" <?php checked(get_option('wpp_enable_auto_backup', '0'), '1'); ?>>
                                        Enable Automatic Data Backup
                                    </label>
                                    <small>Automatically backup contact data and settings daily.</small>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="premium-locked">
                                <div class="premium-badge" style="background: linear-gradient(135deg, #6c757d, #495057);">
                                    🔒 Premium Required
                                </div>
                                <p>These advanced settings require an active Premium license.</p>
                                <button class="wpp-btn" onclick="showSection('license')">Activate License</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="card">
                    <button type="submit" name="save_settings" class="wpp-btn">Save All Settings</button>
                    <button type="submit" name="reset_settings" class="wpp-btn wpp-btn-secondary" onclick="return confirm('Are you sure you want to reset all settings?')">Reset to Defaults</button>
                </div>
            </form>
        </div>

        <script>
        function showSection(sectionId) {
            // Hide all sections
            const sections = document.querySelectorAll('.wpp-collapsible-content');
            sections.forEach(section => {
                section.style.display = 'none';
            });

            // Show selected section
            const targetSection = document.getElementById(sectionId + '-section');
            if (targetSection) {
                targetSection.querySelector('.wpp-collapsible-content').style.display = 'block';
            }
        }

        // Toggle functionality for collapsible sections
        document.querySelectorAll('.wpp-collapsible-header').forEach(header => {
            header.addEventListener('click', function() {
                const content = this.nextElementSibling;
                const icon = this.querySelector('.toggle-icon');

                if (content.style.display === 'none' || content.style.display === '') {
                    content.style.display = 'block';
                    icon.textContent = '▼';
                } else {
                    content.style.display = 'none';
                    icon.textContent = '▶';
                }
            });
        });

        // Show license section by default if not activated
        <?php if (!$license_info['valid']): ?>
            document.addEventListener('DOMContentLoaded', function() {
                showSection('license');
            });
        <?php endif; ?>

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const quietStart = document.getElementById('quiet_hours_start').value;
            const quietEnd = document.getElementById('quiet_hours_end').value;

            if (quietStart && quietEnd && quietStart === quietEnd) {
                alert('Quiet hours start and end times cannot be the same.');
                e.preventDefault();
                return false;
            }

            return true;
        });
        </script>

        <style>
        .premium-locked {
            text-align: center;
            padding: 40px 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 2px dashed #dee2e6;
        }

        .premium-settings {
            display: grid;
            gap: 20px;
        }

        .wpp-collapsible {
            margin-bottom: 20px;
        }

        .wpp-collapsible-header {
            cursor: pointer;
            user-select: none;
        }

        .wpp-collapsible-content {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 0 0 8px 8px;
        }

        .toggle-icon {
            float: right;
            transition: transform 0.3s ease;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .stat-item {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }

        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #FFD700;
            display: block;
        }

        .stat-label {
            color: #666;
            font-size: 0.9em;
        }
        </style>
        <?php
    }

    private static function handle_form_submission() {
        if (!isset($_POST['save_settings']) && !isset($_POST['reset_settings'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['_wpnonce'], 'wpp_settings_nonce')) {
            wp_die('Security check failed');
        }

        if (isset($_POST['reset_settings'])) {
            // Reset all settings to defaults
            self::reset_settings();
            add_settings_error('wpp_settings', 'settings_reset', 'All settings have been reset to defaults.', 'success');
            return;
        }

        // Save general settings
        $settings = [
            'wpp_business_name',
            'wpp_timezone',
            'wpp_language',
            'wpp_enable_logging',
            'wpp_default_sender',
            'wpp_message_signature',
            'wpp_quiet_hours_start',
            'wpp_quiet_hours_end',
            'wpp_respect_quiet_hours',
            'wpp_frequency_cap'
        ];

        foreach ($settings as $setting) {
            $value = isset($_POST[str_replace('wpp_', '', $setting)]) ? $_POST[str_replace('wpp_', '', $setting)] : '';
            update_option($setting, sanitize_text_field($value));
        }

        // Save premium settings (only if license is valid)
        if (LicenseManager::is_valid()) {
            $premium_settings = [
                'wpp_enable_meta_extraction',
                'wpp_enable_excel_upload',
                'wpp_enable_advanced_segmentation',
                'wpp_enable_analytics',
                'wpp_enable_waba_integration',
                'wpp_analytics_retention',
                'wpp_export_format',
                'wpp_enable_auto_backup'
            ];

            foreach ($premium_settings as $setting) {
                $value = isset($_POST[str_replace('wpp_', '', $setting)]) ? $_POST[str_replace('wpp_', '', $setting)] : '0';
                update_option($setting, $value);
            }
        }

        add_settings_error('wpp_settings', 'settings_saved', 'Settings saved successfully!', 'success');
    }

    private static function reset_settings() {
        // Delete all settings
        $all_settings = [
            'wpp_business_name',
            'wpp_timezone',
            'wpp_language',
            'wpp_enable_logging',
            'wpp_default_sender',
            'wpp_message_signature',
            'wpp_quiet_hours_start',
            'wpp_quiet_hours_end',
            'wpp_respect_quiet_hours',
            'wpp_frequency_cap',
            'wpp_enable_meta_extraction',
            'wpp_enable_excel_upload',
            'wpp_enable_advanced_segmentation',
            'wpp_enable_analytics',
            'wpp_enable_waba_integration',
            'wpp_analytics_retention',
            'wpp_export_format',
            'wpp_enable_auto_backup'
        ];

        foreach ($all_settings as $setting) {
            delete_option($setting);
        }
    }
}