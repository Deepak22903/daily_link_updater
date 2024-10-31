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
        custom_log("daily-link-updater: Failed to retrieve content from $sourceUrl");
        return [];
    }

    preg_match_all('/<a href="(https:\/\/mply\.io\/[^"]+)"/', $html, $matches);
    return array_slice($matches[1], 0, 4); // Returns the first 4 links
}


function daily_update_links() {
    // Specify the post ID of the post you want to update
    $post_id_to_update = 1767; // Replace with your actual post ID

    // Fetch the new links from the external source
    $links = get_links_from_source();
    if (empty($links)) {
        custom_log('No links found.');
        return;
    }

    // Get the specific post to update
    $post = get_post($post_id_to_update);
    
    // Log the post object and content for debugging
    if (!$post) {
        custom_log("Post with ID $post_id_to_update not found.");
        return;
    }

    $content = $post->post_content;

    // Log the original post content
    custom_log("Original Post Content: " . $content);

    // Loop through the fetched links and update only bit.ly links
    foreach ($links as $link) {
        // Log the link being processed
        custom_log("Processing link: $link");

        // Update links that are inside the button structure
        // Use a pattern that captures the caption
        $pattern = '/<a\s+href="https:\/\/bit\.ly\/[^"]+">([^<]*)<\/a>/';
        $replacement = '<a href="' . $link . '">$1</a>'; // Preserve the caption
        
        // Replace only the first match
        $content = preg_replace($pattern, $replacement, $content, 1);
        
        custom_log("Updated link in content: $link");
    }

    custom_log("Updated Post Content: " . $content);

    // Update the post with the new content
    wp_update_post(array(
        'ID' => $post_id_to_update,
        'post_content' => $content,
    ));
}



function custom_log($message) {
    $logDir = WP_CONTENT_DIR . '/plugin-logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $logfile = $logDir . '/link-updater.log';
    file_put_contents($logfile, date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
}

