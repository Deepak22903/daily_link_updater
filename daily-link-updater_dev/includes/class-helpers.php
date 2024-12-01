<?php
namespace DailyLinkUpdater;

class Helpers {

    public static function activate() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'link_updater_posts';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id mediumint(9) NOT NULL,
            source_url varchar(255) NOT NULL,
            post_type varchar(50) NOT NULL,
            link_patterns text NOT NULL,
            link_text varchar(255) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Create log directory if it doesn't exist
        $logDir = WP_CONTENT_DIR . '/plugin-logs';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    public static function deactivate() {
        // Clear the scheduled hook
        wp_clear_scheduled_hook('run_link_updater');
        
        // Log deactivation
        custom_log('Link Updater plugin deactivated', 'info');
    }

    // Modified logging function to handle different message types
    public static function custom_log($message, $type = 'info') {
    $logDir = WP_CONTENT_DIR . '/plugin-logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $logfile = $logDir . '/link-updater.log';
    $timestamp = date('[Y-m-d H:i:s]');
    $formattedMessage = sprintf("%s [%s] %s", $timestamp, strtoupper($type), $message);
    file_put_contents($logfile, $formattedMessage . PHP_EOL, FILE_APPEND);

}
?>

