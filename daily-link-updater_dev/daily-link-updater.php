<?php
/*
Plugin Name: Daily Link Updater Dev
Plugin URI: http://example.com
Description: A plugin to update links daily.
Version: 1.0
Author: Deepak Shitole
Author URI: https://peekdeep.com/author/deepak/
License: GPL2
*/

// Autoload classes
spl_autoload_register(function ($class) {
    $prefix = 'DailyLinkUpdater\\';
    $base_dir = __DIR__ . '/includes/';
    
    // Check if the class uses our namespace
    if (strpos($class, $prefix) !== 0) {
        return;
    }

    // Replace namespace prefix with the base directory and convert namespace separators to directory separators
    $relative_class = substr($class, strlen($prefix));
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Initialize plugin components
function daily_link_updater_init() {
    $adminInterface = new DailyLinkUpdater\AdminInterface();
    $postUpdater = new DailyLinkUpdater\PostUpdater();
    
    // Initialize components
    $adminInterface->init();
    $postUpdater->init();
}
add_action('plugins_loaded', 'daily_link_updater_init');
