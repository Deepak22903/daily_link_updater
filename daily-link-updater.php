<?php
/*
Plugin Name: Daily Link Updater
Description: Automates the update of one-time-use reward links in posts.
Version: 1.0
*/

// Trigger function on manual execution
function daily_link_updater_menu() {
    add_menu_page('Link Updater', 'Link Updater', 'manage_options', 'link-updater', 'daily_link_updater');
}
add_action('admin_menu', 'daily_link_updater_menu');

function daily_link_updater() {
    if (isset($_POST['start_update'])) {
        daily_update_links();
    }
    echo '<h1>Daily Link Updater</h1>';
    echo '<form method="POST"><input type="submit" name="start_update" value="Start Update"></form>';
}

function get_links_from_source() {
    $sourceUrl = 'https://mosttechs.com/monopoly-go-free-dice/';
    $html = @file_get_contents($sourceUrl);

    if ($html === false) {
        custom_log("Failed to retrieve content from $sourceUrl");
        return [];
    }

    preg_match_all('/<a href="(https:\/\/example\.com\/reward-link[^"]+)"/', $html, $matches);
    return $matches[1]; // Returns an array of links
}

function validate_link($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $httpCode === 200;
}

function daily_update_links() {
    $links = get_links_from_source();
    if (empty($links)) {
        error_log('No links found.');
        return;
    }

    // Get posts with game links (assuming these are stored with specific IDs or categories)
    $query = new WP_Query(array('category_name' => 'game-rewards', 'posts_per_page' => -1));
    while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();
        $content = get_the_content();

        // Find and replace only button links in a specific section
        foreach ($links as $link) {
            if (validate_link($link)) {
                $content = preg_replace('/<a href="old_link">/', '<a href="' . $link . '">', $content, 1);
            } else {
                error_log("Link validation failed for: $link");
            }
        }

        wp_update_post(array(
            'ID' => $post_id,
            'post_content' => $content,
        ));
    }
    wp_reset_postdata();
}

function custom_log($message) {
    $logDir = WP_CONTENT_DIR . '/plugin-logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $logfile = $logDir . '/link-updater.log';
    file_put_contents($logfile, date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
}
>
