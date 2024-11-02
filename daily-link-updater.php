
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
                <p><?php echo get_option('link_updater_post_id', '419'); ?></p>
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


class PostLinkUpdater {
    private $post_configs = [
        297 => [
            'url' => 'https://mosttechs.com/match-masters-free-boosters/',
            'type' => 'match_masters',
            'link_patterns' => [
                'https://go.matchmasters.io/',
                // Add any additional Match Masters patterns here
            ],
            'link_text' => 'Collect Free Boosters and Gifts'
        ],
        419 => [
            'url' => 'https://rezortricks.com/hit-it-rich-free-coins/',
            'type' => 'hit_it_rich',
            'link_patterns' => [
                'https://hititrich.onelink.me/',
                'https://web.hititrich.zynga.com/client/mobile_landing.php'
            ],
            'link_text' => 'Collect 2500+ Free Coins'
        ],
        271 => [
            'url' => 'https://mosttechs.com/zynga-poker-free-chips-link/',
            'type' => 'zynga_poker',
            'link_patterns' => [
                'http://zynga.live/'
            ],
            'link_text' => 'Collect 3X Free Chips'
        ]
    ];

    private $date_formats = [
        'display' => 'F j, Y',          // November 1, 2024
        'dot' => 'j.n.Y',              // 1.11.2024
        'ordinal' => 'jS F, Y',        // 1st November, 2024
        'underscore' => 'jS_F_Y',      // 1st_November_2024
        'id' => 'F_j_Y'                // November_1_2024
    ];

    public function get_links_from_source($source_url, $config) {
        $html = @file_get_contents($source_url);
        if ($html === false) {
            custom_log("Failed to retrieve content from $source_url", 'error');
            return [];
        }

        $today_links = [];
        
        switch ($config['type']) {
            case 'match_masters':
                $today_links = $this->extract_match_masters_links($html);
                break;
            case 'hit_it_rich':
                $today_links = $this->extract_hit_it_rich_links($html);
                break;
            case 'zynga_poker':
                $today_links = $this->extract_zynga_poker_links($html);
                break;
        }

        return $today_links;
    }
    
    private function extract_zynga_poker_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[271]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Zynga Poker from mosttechs.com: " . implode(', ', $links));
        return $links;
    }

    private function extract_match_masters_links($html) {
    $links = [];
    foreach ($this->post_configs[297]['link_patterns'] as $pattern) {
        $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?(' . 
            preg_quote(date($this->date_formats['dot']), '/') . '|' . 
            preg_quote(date($this->date_formats['ordinal']), '/') . ')/i';
        
        preg_match_all($regex_pattern, $html, $matches);
        if (!empty($matches[1])) {
            $links = array_merge($links, $matches[1]);
        }
    }
    
    $links = array_unique($links); // Remove duplicates
    custom_log("Fetched " . count($links) . " links from mosttechs.com: " . implode(', ', $links));
    return $links;
}

private function extract_hit_it_rich_links($html) {
    $date_pattern = '<span id="Hit_It_Rich_Todays_Free_Coins_Link-_' . date($this->date_formats['underscore']) . '">';
    $pos = strpos($html, $date_pattern);
    
    if ($pos === false) {
        custom_log("Could not find section with today's date pattern", 'warning');
        return [];
    }

    $section_start = $pos;
    $next_h4_pos = strpos($html, '<h4', $pos + strlen($date_pattern));
    $section_end = $next_h4_pos !== false ? $next_h4_pos : strlen($html);
    $section_content = substr($html, $section_start, $section_end - $section_start);

    $links = [];
    foreach ($this->post_configs[419]['link_patterns'] as $pattern) {
        preg_match_all('/<a href="(' . preg_quote($pattern, '/') . '[^"]+)"[^>]*>/i', 
            $section_content, $matches);
        
        if (!empty($matches[1])) {
            $links = array_merge($links, $matches[1]);
        }
    }
    
    $links = array_unique($links);
    $links = array_reverse($links);
    
    custom_log("Fetched " . count($links) . " links from rezortricks.com: " . implode(', ', $links));
    return $links;
}

    private function update_post_content($content, $today_heading, $links, $config) {
    $today_date = date($this->date_formats['display']);
    $content_modified = false;
    
    // Check if today's heading exists
    if (preg_match('/<h4[^>]*>.*?' . preg_quote($today_date, '/') . '.*?<\/h4>/s', $content, $heading_matches, PREG_OFFSET_CAPTURE)) {
        $heading_pos = $heading_matches[0][1];
        $heading_end = $heading_pos + strlen($heading_matches[0][0]);
        
        $next_heading_pos = stripos($content, '<h4', $heading_end);
        $section_end = $next_heading_pos !== false ? $next_heading_pos : strlen($content);
        
        $current_section = substr($content, $heading_pos, $section_end - $heading_pos);
        
        // Count existing links for all patterns
        $existing_links_count = 0;
        foreach ($config['link_patterns'] as $pattern) {
            preg_match_all('/<a href="' . preg_quote($pattern, '/') . '[^"]+"/i', $current_section, $existing_matches);
            $existing_links_count += count($existing_matches[0]);
        }
        
        if (count($links) > $existing_links_count) {
            $new_links = array_slice($links, $existing_links_count);
            
            if (preg_match('/<ul>(.*?)<\/ul>/s', $current_section)) {
                $ul_end_pos = strrpos($current_section, '</ul>');
                $additional_links_html = '';
                foreach ($new_links as $link) {
                    $additional_links_html .= sprintf(
                        '<li><a href="%s" target="_blank" rel="noopener">%s</a></li>' . "\n",
                        esc_url($link),
                        $config['link_text']
                    );
                }
                $updated_section = substr_replace($current_section, $additional_links_html, $ul_end_pos, 0);
            } else {
                $updated_section = $current_section . $this->generate_links_html($new_links, $config['link_text']);
            }
            
            $content = substr_replace($content, $updated_section, $heading_pos, $section_end - $heading_pos);
            custom_log("Added " . count($new_links) . " new links to existing section for $today_date");
            $content_modified = true;
        } else {
            custom_log("Section for $today_date is already up to date. No new links to add.", 'info');
        }
    } else {
        if (preg_match('/<h4[^>]*>/', $content, $matches, PREG_OFFSET_CAPTURE)) {
            $first_h4_pos = $matches[0][1];
            $new_section = $today_heading . "\n" . $this->generate_links_html($links, $config['link_text']);
            $content = substr_replace($content, $new_section, $first_h4_pos, 0);
            custom_log("Created new section for $today_date at the top");
            $content_modified = true;
        } else {
            $content = $today_heading . "\n" . $this->generate_links_html($links, $config['link_text']) . $content;
            custom_log("No existing headings found, added new section at the beginning");
            $content_modified = true;
        }
    }
    
    return ['content' => $content, 'modified' => $content_modified];
}

    private function generate_heading($date) {
        return sprintf(
            '<h4 class="wp-block-heading has-text-color has-link-color wp-elements-f2ac3daac33216e856b046520ec53ee3" style="color:#008effe6">' .
            '<span class="ez-toc-section" id="%s" ez-toc-data-id="#%s"></span>' .
            '<strong>%s</strong>' .
            '<span class="ez-toc-section-end"></span>' .
            '</h4>',
            str_replace(' ', '_', $date),
            str_replace(' ', '_', $date),
            $date
        );
    }

    private function generate_links_html($links, $link_text) {
        if (empty($links)) {
            return '';
        }
        
        $html = "<ol>\n";
        foreach ($links as $link) {
            $html .= sprintf(
                '    <li><a href="%s" target="_blank" rel="noopener">%s</a></li>' . "\n",
                esc_url($link),
                $link_text
            );
        }
        return $html . "</ol>\n";
    }
    
    // Helper method to validate configuration
private function validate_config($config) {
    $required_fields = ['url', 'type', 'link_patterns', 'link_text'];
    foreach ($required_fields as $field) {
        if (!isset($config[$field])) {
            custom_log("Missing required configuration field: $field", 'error');
            return false;
        }
    }
    
    if (!is_array($config['link_patterns']) || empty($config['link_patterns'])) {
        custom_log("link_patterns must be a non-empty array", 'error');
        return false;
    }
    
    return true;
}

    public function run_updates() {
    $updates_made = false;
    
    foreach ($this->post_configs as $post_id => $config) {
        // Validate configuration
        if (!$this->validate_config($config)) {
            custom_log("Invalid configuration for post ID $post_id. Skipping.", 'error');
            continue;
        }
        
        $links = $this->get_links_from_source($config['url'], $config);
        
        if (empty($links)) {
            custom_log("No links found for Post ID $post_id from source {$config['url']}.", 'warning');
            continue;
        }

        $post = get_post($post_id);
        if (!$post) {
            custom_log("Post with ID $post_id not found.", 'error');
            continue;
        }

        $today_heading = $this->generate_heading(date($this->date_formats['display']));
        $update_result = $this->update_post_content($post->post_content, $today_heading, $links, $config);
        
        if ($update_result['modified']) {
            $post_update = wp_update_post([
                'ID' => $post_id,
                'post_content' => $update_result['content'],
            ]);

            if (is_wp_error($post_update)) {
                custom_log("Failed to update post $post_id: " . $post_update->get_error_message(), 'error');
            } else {
                custom_log("Successfully updated post $post_id");
                $updates_made = true;
            }
        }
    }
    
    if ($updates_made) {
        update_option('link_updater_last_run', current_time('mysql'));
    } else {
        custom_log("No updates needed. All posts are up to date.", 'info');
    }
    }
}

// Usage
function daily_update_links() {
    $updater = new PostLinkUpdater();
    $updater->run_updates();
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
