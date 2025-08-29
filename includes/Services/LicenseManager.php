<?php
namespace WhatsPro\Premium\Services;

class LicenseManager {
    private static $license_option = 'wpp_premium_license';
    private static $domain_option = 'wpp_premium_domain';
    private static $activation_option = 'wpp_premium_activated';

    public static function activate_license($license_key) {
        // Validate license format (basic validation)
        if (!self::validate_license_format($license_key)) {
            return ['error' => 'Invalid license format'];
        }

        // For demo keys, skip domain validation
        if (strpos($license_key, 'DEMO') === 0) {
            // This is a demo key - allow any domain
            $current_domain = self::get_current_domain();
        } else {
            // Check domain for production keys
            $current_domain = self::get_current_domain();
            $allowed_domains = self::decode_license_domains($license_key);

            if (!in_array($current_domain, $allowed_domains)) {
                return ['error' => 'License not valid for this domain'];
            }

            // Optional: Ping remote server for validation
            $remote_valid = self::validate_remote($license_key, $current_domain);
            if (!$remote_valid) {
                return ['error' => 'License validation failed'];
            }
        }

        // Store license
        update_option(self::$license_option, self::obfuscate($license_key));
        update_option(self::$domain_option, $current_domain);
        update_option(self::$activation_option, time());

        return ['success' => 'License activated successfully'];
    }

    public static function deactivate_license() {
        delete_option(self::$license_option);
        delete_option(self::$domain_option);
        delete_option(self::$activation_option);

        return ['success' => 'License deactivated'];
    }

    public static function is_valid() {
        $license = get_option(self::$license_option);
        $domain = get_option(self::$domain_option);
        $activated = get_option(self::$activation_option);

        // For demo purposes, if no license is set, try to auto-activate demo
        if (!$license || !$domain || !$activated) {
            // Try to auto-activate demo license
            self::auto_activate_demo();
            $license = get_option(self::$license_option);
            $domain = get_option(self::$domain_option);
            $activated = get_option(self::$activation_option);

            if (!$license || !$domain || !$activated) {
                return false;
            }
        }

        // Check if still within valid domain (skip for demo keys)
        $current_domain = self::get_current_domain();
        $deobfuscated_license = self::deobfuscate($license);

        if (strpos($deobfuscated_license, 'DEMO') === 0) {
            // Demo license - allow any domain
            return true;
        }

        if ($domain !== $current_domain) {
            return false;
        }

        // Check if license hasn't expired (optional: 1 year validity)
        $activation_time = intval($activated);
        $expiration_time = $activation_time + (365 * 24 * 60 * 60); // 1 year

        if (time() > $expiration_time) {
            return false;
        }

        return true;
    }

    private static function auto_activate_demo() {
        // Auto-activate demo license if no license exists
        $demo_key = 'DEMO2024000000000000000000001';
        update_option(self::$license_option, self::obfuscate($demo_key));
        update_option(self::$domain_option, self::get_current_domain());
        update_option(self::$activation_option, time());
    }

    public static function get_license_info() {
        return [
            'valid' => self::is_valid(),
            'domain' => get_option(self::$domain_option),
            'activated' => get_option(self::$activation_option) ? date('Y-m-d H:i:s', get_option(self::$activation_option)) : null,
            'expires' => get_option(self::$activation_option) ? date('Y-m-d H:i:s', get_option(self::$activation_option) + (365 * 24 * 60 * 60)) : null
        ];
    }

    private static function validate_license_format($license_key) {
        // Basic format validation - should be 32 characters, alphanumeric
        return preg_match('/^[A-Z0-9]{32}$/', $license_key);
    }

    private static function get_current_domain() {
        $domain = parse_url(get_site_url(), PHP_URL_HOST);
        return $domain;
    }

    private static function decode_license_domains($license_key) {
        // Simple decoding - in production, use proper encryption
        $decoded = base64_decode(substr($license_key, 8, -8));
        $domains = explode(',', $decoded);

        return array_map('trim', $domains);
    }

    private static function validate_remote($license_key, $domain) {
        // Optional remote validation
        // In production, this would make an API call to your license server
        // For demo purposes, always return true
        return true;
    }

    private static function obfuscate($string) {
        // Simple obfuscation - reverse and base64 encode
        return base64_encode(strrev($string));
    }

    private static function deobfuscate($string) {
        // Reverse obfuscation
        return strrev(base64_decode($string));
    }

    public static function check_license() {
        if (!self::is_valid()) {
            // Obfuscate plugin behavior
            add_action('admin_notices', [__CLASS__, 'show_license_notice']);
            add_filter('wpp_premium_features_enabled', '__return_false');
        }
    }

    public static function show_license_notice() {
        if (!current_user_can('manage_options')) return;

        echo '<div class="notice notice-error is-dismissible">';
        echo '<p><strong>WhatsPro Premium:</strong> Please activate your license to access premium features.</p>';
        echo '<p><a href="' . admin_url('admin.php?page=wpp-settings') . '" class="button button-primary">Activate License</a></p>';
        echo '</div>';
    }

    public static function get_hidden_verification() {
        // Hidden verification code that only appears when license is valid
        if (self::is_valid()) {
            $domain = self::get_current_domain();
            $hash = wp_hash($domain . 'wpp_premium_salt');
            return substr($hash, 0, 16);
        }
        return '';
    }

    public static function render_license_settings() {
        $license_info = self::get_license_info();

        ob_start();
        ?>
        <div class="license-management-card" style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #e9ecef;">
            <h3 style="margin-top: 0; color: #333;">🔑 License Management</h3>

            <?php if ($license_info['valid']): ?>
                <div class="license-status-active" style="background: linear-gradient(135deg, #d4edda, #c3e6cb); border: 1px solid #c3e6cb; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <div style="background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 8px 16px; border-radius: 20px; font-weight: bold; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; display: inline-block; margin-bottom: 10px;">
                        ✅ Premium License Active
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div>
                            <label style="display: block; font-weight: 600; color: #333; margin-bottom: 5px;">Domain:</label>
                            <input type="text" value="<?php echo esc_attr($license_info['domain']); ?>" readonly style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; background: #f8f9fa;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; color: #333; margin-bottom: 5px;">Activated:</label>
                            <input type="text" value="<?php echo esc_attr($license_info['activated']); ?>" readonly style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; background: #f8f9fa;">
                        </div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; color: #333; margin-bottom: 5px;">Expires:</label>
                        <input type="text" value="<?php echo esc_attr($license_info['expires']); ?>" readonly style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; background: #f8f9fa;">
                    </div>
                    <button class="wpp-btn wpp-btn-secondary" onclick="deactivateLicense()" style="background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer;">Deactivate License</button>
                </div>
            <?php else: ?>
                <div class="license-activation-form" style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 2px dashed #dee2e6;">
                    <div style="margin-bottom: 20px;">
                        <label for="license_key" style="display: block; font-weight: 600; color: #333; margin-bottom: 8px;">License Key:</label>
                        <input type="text" id="license_key" name="license_key" placeholder="Enter your 32-character license key (e.g., DEMO2024000000000000000000001)" style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 6px; font-size: 14px; font-family: monospace;">
                        <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">For testing: Use any 32-character alphanumeric key like <code style="background: #e9ecef; padding: 2px 4px; border-radius: 3px;">DEMO2024000000000000000000001</code></small>
                    </div>
                    <button class="wpp-btn" onclick="activateLicense()" style="background: linear-gradient(135deg, #FFD700, #FFA500); color: #333; border: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 14px;">🚀 Activate License</button>
                </div>
            <?php endif; ?>

            <?php if (!empty(self::get_hidden_verification())): ?>
                <div class="license-verification" style="margin-top: 20px; padding: 15px; background: #e8f5e8; border: 1px solid #c3e6cb; border-radius: 6px;">
                    <label style="display: block; font-weight: 600; color: #333; margin-bottom: 5px;">✅ Verification Code:</label>
                    <input type="text" value="<?php echo esc_attr(self::get_hidden_verification()); ?>" readonly style="width: 100%; padding: 8px; border: 1px solid #c3e6cb; border-radius: 4px; background: white; font-family: monospace;">
                    <small style="color: #666; font-size: 12px;">This code confirms your premium license is valid.</small>
                </div>
            <?php endif; ?>
        </div>

        <script>
        function activateLicense() {
            const licenseKey = document.getElementById('license_key').value;

            if (!licenseKey) {
                alert('Please enter a license key');
                return;
            }

            if (licenseKey.length !== 32) {
                alert('License key must be exactly 32 characters long');
                return;
            }

            // Show loading state
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '🔄 Activating...';
            button.disabled = true;

            fetch(ajaxurl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'wpp_activate_license',
                    license_key: licenseKey,
                    nonce: '<?php echo wp_create_nonce('wpp_license_nonce'); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.data);
                    location.reload();
                } else {
                    alert('❌ ' + (data.data || 'Activation failed'));
                }
            })
            .catch(error => {
                alert('❌ Network error. Please try again.');
                console.error('License activation error:', error);
            })
            .finally(() => {
                // Reset button state
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }

        function deactivateLicense() {
            if (!confirm('Are you sure you want to deactivate the license? This will disable all premium features.')) {
                return;
            }

            // Show loading state
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '🔄 Deactivating...';
            button.disabled = true;

            fetch(ajaxurl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'wpp_deactivate_license',
                    nonce: '<?php echo wp_create_nonce('wpp_license_nonce'); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.data);
                    location.reload();
                } else {
                    alert('❌ ' + (data.data || 'Deactivation failed'));
                }
            })
            .catch(error => {
                alert('❌ Network error. Please try again.');
                console.error('License deactivation error:', error);
            })
            .finally(() => {
                // Reset button state
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }

        // Auto-focus license input when page loads (if not activated)
        document.addEventListener('DOMContentLoaded', function() {
            const licenseInput = document.getElementById('license_key');
            if (licenseInput) {
                licenseInput.focus();
                // Pre-fill with demo key for testing
                if (!licenseInput.value) {
                    licenseInput.value = 'DEMO2024000000000000000000001';
                }
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }
}