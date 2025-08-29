<?php
namespace WhatsPro\Premium\Data;

class Migrations {
    public static function install() {
        self::create_leads_table();
        self::create_messages_table();
        self::create_smart_lists_table();
        self::create_list_leads_table();
        self::create_segments_table();
        self::create_campaigns_table();
        self::create_message_opens_table();
        self::create_message_clicks_table();

        // Set default options
        add_option('wpp_db_version', '2.0.0');
    }

    public static function maybe() {
        $current_version = get_option('wpp_db_version', '1.0.0');

        if (version_compare($current_version, '2.0.0', '<')) {
            self::install();
        }
    }

    public static function upgrade() {
        $current_version = get_option('wpp_db_version', '1.0.0');

        if (version_compare($current_version, '2.0.0', '<')) {
            self::install();
        }
    }

    private static function create_leads_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wpp_leads';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            phone varchar(20) NOT NULL,
            email varchar(255) DEFAULT '' NOT NULL,
            city varchar(255) DEFAULT '' NOT NULL,
            company varchar(255) DEFAULT '' NOT NULL,
            ip varchar(45) DEFAULT '' NOT NULL,
            referrer text DEFAULT '' NOT NULL,
            tags text DEFAULT '' NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY phone (phone),
            KEY name (name),
            KEY email (email),
            KEY city (city),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private static function create_messages_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wpp_messages';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            phone_number varchar(20) NOT NULL,
            content text NOT NULL,
            message_id varchar(255) DEFAULT '' NOT NULL,
            status varchar(20) DEFAULT 'sent' NOT NULL,
            direction varchar(10) DEFAULT 'outbound' NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id),
            KEY phone_number (phone_number),
            KEY message_id (message_id),
            KEY status (status),
            KEY direction (direction),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private static function create_smart_lists_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wpp_smart_lists';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text DEFAULT '' NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id),
            KEY name (name),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private static function create_list_leads_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wpp_list_leads';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            list_id mediumint(9) NOT NULL,
            lead_id mediumint(9) NOT NULL,
            added_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY list_lead (list_id, lead_id),
            KEY list_id (list_id),
            KEY lead_id (lead_id),
            KEY added_at (added_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private static function create_segments_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wpp_segments';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text DEFAULT '' NOT NULL,
            filters longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id),
            KEY name (name),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private static function create_campaigns_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wpp_campaigns';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            template_name varchar(255) NOT NULL,
            recipients longtext NOT NULL,
            parameters longtext DEFAULT '' NOT NULL,
            schedule_time datetime NOT NULL,
            status varchar(20) DEFAULT 'scheduled' NOT NULL,
            executed_at datetime NULL,
            results longtext DEFAULT '' NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id),
            KEY name (name),
            KEY template_name (template_name),
            KEY schedule_time (schedule_time),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private static function create_message_opens_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wpp_message_opens';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            lead_id mediumint(9) NOT NULL,
            message_id varchar(255) NOT NULL,
            opened_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            ip_address varchar(45) DEFAULT '' NOT NULL,
            user_agent text DEFAULT '' NOT NULL,
            PRIMARY KEY (id),
            KEY lead_id (lead_id),
            KEY message_id (message_id),
            KEY opened_at (opened_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private static function create_message_clicks_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wpp_message_clicks';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            lead_id mediumint(9) NOT NULL,
            message_id varchar(255) NOT NULL,
            link_url text NOT NULL,
            clicked_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            ip_address varchar(45) DEFAULT '' NOT NULL,
            user_agent text DEFAULT '' NOT NULL,
            PRIMARY KEY (id),
            KEY lead_id (lead_id),
            KEY message_id (message_id),
            KEY clicked_at (clicked_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public static function uninstall() {
        // Optionally remove tables on uninstall
        // Uncomment the following lines if you want to remove tables on uninstall

        /*
        global $wpdb;

        $tables = [
            $wpdb->prefix . 'wpp_leads',
            $wpdb->prefix . 'wpp_messages',
            $wpdb->prefix . 'wpp_smart_lists',
            $wpdb->prefix . 'wpp_list_leads',
            $wpdb->prefix . 'wpp_segments',
            $wpdb->prefix . 'wpp_campaigns',
            $wpdb->prefix . 'wpp_message_opens',
            $wpdb->prefix . 'wpp_message_clicks'
        ];

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }

        // Remove options
        delete_option('wpp_db_version');
        delete_option('wpp_business_name');
        delete_option('wpp_timezone');
        delete_option('wpp_language');
        delete_option('wpp_enable_logging');
        delete_option('wpp_default_sender');
        delete_option('wpp_message_signature');
        delete_option('wpp_quiet_hours_start');
        delete_option('wpp_quiet_hours_end');
        delete_option('wpp_respect_quiet_hours');
        delete_option('wpp_frequency_cap');
        delete_option('wpp_enable_meta_extraction');
        delete_option('wpp_enable_excel_upload');
        delete_option('wpp_enable_advanced_segmentation');
        delete_option('wpp_enable_analytics');
        delete_option('wpp_enable_waba_integration');
        delete_option('wpp_analytics_retention');
        delete_option('wpp_export_format');
        delete_option('wpp_enable_auto_backup');
        delete_option('wpp_waba_access_token');
        delete_option('wpp_waba_phone_number_id');
        delete_option('wpp_waba_business_account_id');
        delete_option('wpp_waba_verify_token');
        */
    }
}