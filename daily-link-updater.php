
<?php
/*
Plugin Name: Daily Link Updater
Description: Automates the update of one-time-use reward links in posts.
Version: 1.0
*/

// Enqueue admin styles
function link_updater_admin_styles() {
    wp_enqueue_style('wp-admin');
    wp_enqueue_style('link-updater-admin', plugins_url('css/admin-style.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'link_updater_admin_styles');

// Create admin menu
function daily_link_updater_menu() {
    add_menu_page(
        'Link Updater',
        'Link Updater',
        'manage_options',
        'link-updater',
        'daily_link_updater_dashboard',
        'dashicons-update'
    );
}
add_action('admin_menu', 'daily_link_updater_menu');

// Dashboard main function
function daily_link_updater_dashboard() {
    // Process form submission
    if (isset($_POST['start_update'])) {
        daily_update_links();
    }
    
    // Get log contents
    $logFile = WP_CONTENT_DIR . '/plugin-logs/link-updater.log';
    $recentLogs = file_exists($logFile) ? array_slice(array_filter(file($logFile)), -10) : [];
    
    // Get last update time
    $lastUpdate = get_option('link_updater_last_run', 'Never');
    
    // Dashboard HTML
    ?>
    <div class="wrap link-updater-dashboard">
        <h1><span class="dashicons dashicons-update"></span> Daily Link Updater</h1>
        
        <!-- Status Cards -->
        <div class="status-cards">
            <div class="card">
                <h3>Last Update</h3>
                <p><?php echo esc_html($lastUpdate); ?></p>
            </div>
            <div class="card">
                <h3>Target Post ID</h3>
                <p><?php echo get_option('link_updater_post_id', '1767'); ?></p>
            </div>
            <div class="card">
                <h3>Source Status</h3>
                <p><?php 
                    $source = 'https://mosttechs.com/monopoly-go-free-dice/';
                    $status = @get_headers($source) ? 'Online' : 'Offline';
                    echo $status;
                ?></p>
            </div>
        </div>

        <!-- Update Button -->
        <div class="update-section">
            <form method="POST" class="update-form">
                <?php wp_nonce_field('link_updater_action', 'link_updater_nonce'); ?>
                <button type="submit" name="start_update" class="button button-primary button-hero">
                    <span class="dashicons dashicons-update"></span> Update Links Now
                </button>
            </form>
        </div>

        <!-- Recent Activity Log -->
        <div class="activity-log">
            <h2>Recent Activity</h2>
            <div class="log-entries">
                <?php if (empty($recentLogs)): ?>
                    <p class="no-logs">No recent activity logged.</p>
                <?php else: ?>
                    <?php foreach ($recentLogs as $log): ?>
                        <div class="log-entry">
                            <?php echo esc_html($log); ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <style>
        .link-updater-dashboard {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }

        .status-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }

        .card {
            background: white;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            box-shadow: 0 1px 1px rgba(0,0,0,0.04);
        }

        .card h3 {
            margin: 0 0 10px 0;
            color: #1d2327;
        }

        .update-section {
            text-align: center;
            margin: 40px 0;
        }

        .update-form button {
            padding: 15px 30px;
            height: auto;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .update-form button .dashicons {
            line-height: 1;
            font-size: 20px;
            margin-top: 12px; /* Adjust this to align the icon vertically if needed */
            margin-right: 5px;
        }

        .activity-log {
            background: white;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            margin-top: 40px;
        }

        .log-entries {
            max-height: 400px;
            overflow-y: auto;
            background: #f6f7f7;
            padding: 15px;
            border-radius: 4px;
        }

        .log-entry {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            font-family: monospace;
        }

        .log-entry:last-child {
            border-bottom: none;
        }

        .no-logs {
            color: #666;
            text-align: center;
            padding: 20px;
        }
    </style>
    <?php
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


// Modified update function to track last run time
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

    
    // Update last run time
    update_option('link_updater_last_run', current_time('mysql'));
}

// Modified logging function to handle different message types
function custom_log($message, $type = 'info') {
    $logDir = WP_CONTENT_DIR . '/plugin-logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $logfile = $logDir . '/link-updater.log';
    $timestamp = date('[Y-m-d H:i:s]');
    $formattedMessage = sprintf("%s [%s] %s", $timestamp, strtoupper($type), $message);
    file_put_contents($logfile, $formattedMessage . PHP_EOL, FILE_APPEND);
}
