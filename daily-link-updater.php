
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


function get_links_from_source($sourceUrl) {
    $html = @file_get_contents($sourceUrl);
    if ($html === false) {
        custom_log("Failed to retrieve content from $sourceUrl", 'error');
        return [];
    }
    
    $today_links = [];
    $today_date = date('j.n.Y');  // Current date in the format 1.11.2024
    $alt_date_format = date('jS F, Y');  // Current date in the format 1st November, 2024
    
    if (strpos($sourceUrl, 'mosttechs.com') !== false) {
        preg_match_all('/<a\s+href="(https:\/\/go\.matchmasters\.io\/[^"]+)".*?>.*?(' . 
            preg_quote($today_date, '/') . '|' . preg_quote($alt_date_format, '/') . ')/i', $html, $matches);
        $today_links = $matches[1];
        custom_log("Fetched " . count($today_links) . " links from mosttechs.com: " . implode(', ', $today_links));
    } elseif (strpos($sourceUrl, 'rezortricks.com') !== false) {
        // Debug the content we're searching through
        custom_log("Searching for links in rezortricks.com content");
        
        // First, find the section with today's date using span ID
        $date_pattern = '<span id="Hit_It_Rich_Todays_Free_Coins_Link-_1st_November_2024">';
        $pos = strpos($html, $date_pattern);
        
        if ($pos !== false) {
            // Find the next h4 tag to determine section end
            $section_start = $pos;
            $next_h4_pos = strpos($html, '<h4', $pos + strlen($date_pattern));
            $section_end = $next_h4_pos !== false ? $next_h4_pos : strlen($html);
            
            // Extract the section content
            $section_content = substr($html, $section_start, $section_end - $section_start);
            custom_log("Found section for 1st November, 2024");
            
            // Extract links from the section
            preg_match_all('/<a href="(https:\/\/hititrich\.onelink\.me\/[^"]+)"[^>]*>/i', 
                $section_content, $matches);
            
            if (!empty($matches[1])) {
                $today_links = array_reverse($matches[1]); // Reverse to get correct order
                custom_log("Fetched " . count($today_links) . " links from rezortricks.com: " . implode(', ', $today_links));
            } else {
                custom_log("No links found in the section", 'warning');
            }
        } else {
            custom_log("Could not find section with ID 'Hit_It_Rich_Todays_Free_Coins_Link-_1st_November_2024'", 'warning');
        }
    }
    
    return $today_links;
}

function daily_update_links() {
    $post_sources = [
        1790 => ['url' => 'https://mosttechs.com/match-masters-free-boosters/'],
        1767 => ['url' => 'https://rezortricks.com/hit-it-rich-free-coins/'],
    ];
    
    foreach ($post_sources as $post_id => $config) {
        $source_url = $config['url'];
        $links = get_links_from_source($source_url);
        
        if (empty($links)) {
            custom_log("No links found for Post ID $post_id from source $source_url.", 'warning');
            continue;
        }
        
        $post = get_post($post_id);
        if (!$post) {
            custom_log("Post with ID $post_id not found.", 'error');
            continue;
        }
        
        $content = $post->post_content;
        custom_log("Processing Post ID $post_id");
        
        if ($post_id == 1790) {
            // Match Masters post processing
            $pattern = '/(<strong>'.date('F j, Y').'<\/strong>.*?)(<a\s+href="https:\/\/go\.matchmasters\.io\/[^"]+">(.*?)<\/a>)/is';
            foreach ($links as $index => $link) {
                $replacement = '$1<a href="' . $link . '" data-type="link" data-id="' . $link . '">$3</a>';
                $content = preg_replace($pattern, $replacement, $content, 1);
                custom_log("Updated Match Masters link #{$index}: $link");
            }
        } elseif ($post_id == 1767) {
            // Find all existing Hit It Rich links
            preg_match_all('/<a href="(https:\/\/hititrich\.onelink\.me\/[^"]+)"[^>]*>.*?<\/a>/', $content, $existing_matches);
            
            foreach ($existing_matches[0] as $index => $full_match) {
                if (isset($links[$index])) {
                    $new_link = $links[$index];
                    $pattern = preg_quote($full_match, '/');
                    $replacement = str_replace($existing_matches[1][$index], $new_link, $full_match);
                    $content = preg_replace('/' . $pattern . '/', $replacement, $content, 1);
                    custom_log("Updated Hit It Rich link #{$index}: replaced with $new_link");
                }
            }
        }
        
        // Update the post
        $update_result = wp_update_post(array(
            'ID' => $post_id,
            'post_content' => $content,
        ));
        
        if (is_wp_error($update_result)) {
            custom_log("Failed to update post $post_id: " . $update_result->get_error_message(), 'error');
        } else {
            custom_log("Successfully updated post $post_id");
        }
    }
    
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
