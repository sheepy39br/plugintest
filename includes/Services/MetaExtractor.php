<?php
namespace WhatsPro\Premium\Services;

class MetaExtractor {
    public static function extract_and_store($message_data) {
        global $wpdb;

        // Ensure license is valid
        if (!LicenseManager::is_valid()) {
            return;
        }

        $table_name = $wpdb->prefix . 'wpp_leads';

        // Extract meta information
        $meta = self::parse_message_meta($message_data);

        if (empty($meta['phone'])) {
            return; // No phone number, skip
        }

        // Check if lead exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_name WHERE phone = %s",
            $meta['phone']
        ));

        if ($existing) {
            // Update existing lead
            $wpdb->update(
                $table_name,
                [
                    'name' => $meta['name'],
                    'city' => $meta['city'],
                    'ip' => $meta['ip'],
                    'referrer' => $meta['referrer'],
                    'source' => $meta['source'],
                    'user_agent' => $meta['user_agent'],
                    'updated_at' => current_time('mysql')
                ],
                ['id' => $existing->id]
            );
        } else {
            // Insert new lead
            $wpdb->insert(
                $table_name,
                [
                    'name' => $meta['name'],
                    'phone' => $meta['phone'],
                    'city' => $meta['city'],
                    'ip' => $meta['ip'],
                    'referrer' => $meta['referrer'],
                    'source' => $meta['source'],
                    'user_agent' => $meta['user_agent'],
                    'tags' => 'New Lead',
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ]
            );
        }

        // Trigger meta display if in admin
        if (is_admin()) {
            self::display_meta_sidebar($meta);
        }
    }

    private static function parse_message_meta($message_data) {
        $meta = [
            'name' => '',
            'phone' => '',
            'city' => '',
            'ip' => '',
            'referrer' => '',
            'source' => 'whatsapp',
            'user_agent' => ''
        ];

        // Extract from WhatsApp webhook data
        if (isset($message_data['contacts'])) {
            $contact = $message_data['contacts'][0];
            $meta['name'] = $contact['profile']['name'] ?? '';
            $meta['phone'] = $contact['wa_id'] ?? '';
        }

        // Extract additional meta from message context
        if (isset($message_data['context'])) {
            $meta['source'] = $message_data['context']['source'] ?? 'whatsapp';
        }

        // Extract IP and location data (would need geolocation service)
        $meta['ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
        $meta['city'] = self::get_city_from_ip($meta['ip']);
        $meta['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // Extract referrer
        $meta['referrer'] = wp_get_referer() ?? '';

        return $meta;
    }

    private static function get_city_from_ip($ip) {
        // Simple geolocation - in production, use a proper service
        // For demo purposes, return mock data based on IP patterns
        if (strpos($ip, '192.168') === 0 || strpos($ip, '10.') === 0) {
            return 'Local Network';
        }

        $cities = ['New York', 'London', 'Tokyo', 'Paris', 'Sydney', 'Berlin', 'Toronto', 'Singapore', 'Mumbai', 'São Paulo'];
        return $cities[array_rand($cities)];
    }

    public static function display_meta_sidebar($meta) {
        if (!is_admin()) return;

        ob_start();
        ?>
        <div class="meta-info-sidebar" id="wpp-meta-sidebar">
            <h3>Latest Lead Meta Info</h3>
            <div class="meta-info-item">
                <div class="meta-info-label">Full Name</div>
                <div class="meta-info-value"><?php echo esc_html($meta['name']); ?></div>
            </div>
            <div class="meta-info-item">
                <div class="meta-info-label">Phone Number</div>
                <div class="meta-info-value"><?php echo esc_html($meta['phone']); ?></div>
            </div>
            <div class="meta-info-item">
                <div class="meta-info-label">City</div>
                <div class="meta-info-value"><?php echo esc_html($meta['city']); ?></div>
            </div>
            <div class="meta-info-item">
                <div class="meta-info-label">IP Address</div>
                <div class="meta-info-value"><?php echo esc_html($meta['ip']); ?></div>
            </div>
            <div class="meta-info-item">
                <div class="meta-info-label">Referrer</div>
                <div class="meta-info-value"><?php echo esc_html($meta['referrer']); ?></div>
            </div>
            <div class="meta-info-item">
                <div class="meta-info-label">Source</div>
                <div class="meta-info-value"><?php echo esc_html($meta['source']); ?></div>
            </div>
            <button class="wpp-btn" onclick="document.getElementById('wpp-meta-sidebar').remove()">Close</button>
        </div>
        <script>
        // Auto-remove after 10 seconds
        setTimeout(() => {
            const sidebar = document.getElementById('wpp-meta-sidebar');
            if (sidebar) sidebar.remove();
        }, 10000);
        </script>
        <?php
        echo ob_get_clean();
    }

    public static function get_recent_meta() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpp_leads';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d",
            10
        ));
    }

    public static function get_meta_analytics($period = 30) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpp_leads';

        $date_limit = date('Y-m-d H:i:s', strtotime("-{$period} days"));

        // Geographic distribution
        $geographic = $wpdb->get_results($wpdb->prepare(
            "SELECT city, COUNT(*) as count FROM $table_name
             WHERE created_at >= %s AND city != ''
             GROUP BY city ORDER BY count DESC LIMIT 10",
            $date_limit
        ));

        // Source distribution
        $sources = $wpdb->get_results($wpdb->prepare(
            "SELECT source, COUNT(*) as count FROM $table_name
             WHERE created_at >= %s AND source != ''
             GROUP BY source ORDER BY count DESC",
            $date_limit
        ));

        // Referrer analysis
        $referrers = $wpdb->get_results($wpdb->prepare(
            "SELECT referrer, COUNT(*) as count FROM $table_name
             WHERE created_at >= %s AND referrer != ''
             GROUP BY referrer ORDER BY count DESC LIMIT 10",
            $date_limit
        ));

        // Name patterns (first letter analysis)
        $name_patterns = $wpdb->get_results($wpdb->prepare(
            "SELECT UPPER(SUBSTRING(name, 1, 1)) as initial, COUNT(*) as count
             FROM $table_name WHERE created_at >= %s AND name != ''
             GROUP BY initial ORDER BY initial",
            $date_limit
        ));

        return [
            'geographic' => $geographic,
            'sources' => $sources,
            'referrers' => $referrers,
            'name_patterns' => $name_patterns,
            'total_leads' => $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE created_at >= %s", $date_limit
            )),
            'unique_cities' => count($geographic),
            'unique_sources' => count($sources)
        ];
    }
}