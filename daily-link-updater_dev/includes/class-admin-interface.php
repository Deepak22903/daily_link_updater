<?php

namespace DailyLinkUpdater;

class AdminInterface {
    public function init() {
        add_action('admin_menu', [$this, 'register_menu']);
    }

    public function register_menu() {
        add_menu_page(
            'Daily Link Updater',
            'Link Updater',
            'manage_options',
            'link-updater-dev',
            [$this, 'render_dashboard'],
            'dashicons-update',
            30
        );

        add_submenu_page(
            'link-updater',
            'Manage Posts',
            'Manage Posts',
            'manage_options',
            'link-updater-posts',
            [$this, 'render_manage_posts_page']
        );
    }

    public function render_dashboard() {
        include plugin_dir_path(__FILE__) . 'templates/admin-dashboard.php';
    }

    public function render_manage_posts_page() {
        include plugin_dir_path(__FILE__) . 'templates/manage-posts.php';
    }
}
