<?php
/*
Plugin Name: Daily Link Updater
Plugin URI: http://example.com
Description: A plugin to update links daily.
Version: 1.0
Author: Deepak Shitole
Author URI: https://peekdeep.com/author/deepak/
License: GPL2
*/

// Register custom cron schedule
function register_link_updater_cron_interval($schedules) {
    $schedules['thirty_minutes'] = array(
        'interval' => 1800, // 30 minutes in seconds
        'display'  => 'Every 30 Minutes'
    );
    return $schedules;
}
add_filter('cron_schedules', 'register_link_updater_cron_interval');

// Schedule the cron event
function schedule_link_updater() {
    if (!wp_next_scheduled('run_link_updater')) {
        wp_schedule_event(time(), 'thirty_minutes', 'run_link_updater');
    }
}

// Hook for the cron event
function run_link_updater_cron() {
    daily_update_links();
}
add_action('run_link_updater', 'run_link_updater_cron');

// Add main menu page
function daily_link_updater_menu() {
    add_menu_page(
        'Daily Link Updater', // Page title
        'Link Updater', // Menu title
        'manage_options', // Capability required
        'link-updater', // Menu slug
        'daily_link_updater_dashboard', // Function to display the page
        'dashicons-update', // Icon (optional)
        30 // Position in menu (optional)
    );
}
add_action('admin_menu', 'daily_link_updater_menu');

// Add submenu pages
function daily_link_updater_submenus() {
    add_submenu_page(
        'link-updater', // Parent slug
        'Manage Posts', // Page title
        'Manage Posts', // Menu title
        'manage_options', // Capability required
        'link-updater-posts', // Menu slug
        'daily_link_updater_posts_page' // Function to display the page
    );
}
add_action('admin_menu', 'daily_link_updater_submenus');

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
    
    // Get next scheduled update
    $nextUpdate = wp_next_scheduled('run_link_updater');
    
    // Get configured posts count
    global $wpdb;
    $table_name = $wpdb->prefix . 'link_updater_posts';
    $posts_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    
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
                <h3>Next Scheduled Update</h3>
                <p><?php echo $nextUpdate ? date('Y-m-d H:i:s', $nextUpdate) : 'Not scheduled'; ?></p>
            </div>
            <div class="card">
                <h3>Update Frequency</h3>
                <p>Every 30 minutes</p>
            </div>
            <div class="card">
                <h3>Configured Posts</h3>
                <p><?php echo intval($posts_count); ?> posts</p>
                <a href="<?php echo admin_url('admin.php?page=link-updater-posts'); ?>" class="button button-secondary">
                    Manage Posts
                </a>
            </div>
            <div class="card">
                <h3>Source Status</h3>
                <?php
                $sources_status = array();
                $posts = $wpdb->get_results("SELECT source_url FROM $table_name");
                foreach ($posts as $post) {
                    $status = @get_headers($post->source_url) ? 'Online' : 'Offline';
                    $sources_status[$status][] = $post->source_url;
                }
                ?>
                <p>
                    <?php
                    $online_count = isset($sources_status['Online']) ? count($sources_status['Online']) : 0;
                    $offline_count = isset($sources_status['Offline']) ? count($sources_status['Offline']) : 0;
                    echo sprintf(
                        '%d Online, %d Offline',
                        $online_count,
                        $offline_count
                    );
                    ?>
                </p>
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

        .card .button {
            margin-top: 10px;
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
            margin-top: 12px;
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


// Modify your existing activation function to include cron setup
function link_updater_activate() {
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
    
    // Schedule the cron job
    schedule_link_updater();
    
    // Create log directory if it doesn't exist
    $logDir = WP_CONTENT_DIR . '/plugin-logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Log activation
    custom_log('Link Updater plugin activated with 30-minute schedule', 'info');
}


register_activation_hook(__FILE__, 'link_updater_activate');

// Add deactivation hook
function link_updater_deactivate() {
    // Clear the scheduled hook
    wp_clear_scheduled_hook('run_link_updater');
    
    // Log deactivation
    custom_log('Link Updater plugin deactivated', 'info');
}
register_deactivation_hook(__FILE__, 'link_updater_deactivate');

// Posts management page
function daily_link_updater_posts_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'link_updater_posts';
    
    // Handle form submission
    if (isset($_POST['submit_post']) && check_admin_referer('link_updater_posts_action')) {
        $post_id = intval($_POST['post_id']);
        $source_url = esc_url_raw($_POST['source_url']);
        $post_type = sanitize_text_field($_POST['post_type']);
        $link_patterns = sanitize_textarea_field($_POST['link_patterns']);
        $link_text = sanitize_text_field($_POST['link_text']);
        
        $wpdb->insert(
            $table_name,
            array(
                'post_id' => $post_id,
                'source_url' => $source_url,
                'post_type' => $post_type,
                'link_patterns' => $link_patterns,
                'link_text' => $link_text
            ),
            array('%d', '%s', '%s', '%s', '%s')
        );
        
        echo '<div class="notice notice-success"><p>Post configuration added successfully!</p></div>';
    }
    
    // Handle deletion
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $wpdb->delete($table_name, array('id' => $id), array('%d'));
        echo '<div class="notice notice-success"><p>Post configuration deleted successfully!</p></div>';
    }
    
    // Get existing configurations
    $posts = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");
    ?>
    <div class="wrap link-updater-posts">
        <h1>Manage Link Updater Posts</h1>
        
        <!-- Add New Post Form -->
        <div class="card add-new-post">
            <h2>Add New Post Configuration</h2>
            <form method="post" action="">
                <?php wp_nonce_field('link_updater_posts_action'); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="post_id">Post ID</label></th>
                        <td>
                            <input type="number" name="post_id" id="post_id" class="regular-text" required>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="source_url">Source URL</label></th>
                        <td>
                            <input type="url" name="source_url" id="source_url" class="regular-text" required>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="post_type">Post Type</label></th>
                        <td>
                            <input type="text" name="post_type" id="post_type" class="regular-text" required>
                            <p class="description">E.g., match_masters, hit_it_rich, zynga_poker</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="link_patterns">Link Patterns</label></th>
                        <td>
                            <textarea name="link_patterns" id="link_patterns" class="large-text" rows="4" required></textarea>
                            <p class="description">Enter one pattern per line</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="link_text">Link Text</label></th>
                        <td>
                            <input type="text" name="link_text" id="link_text" class="regular-text" required>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="submit_post" class="button button-primary" value="Add Post Configuration">
                </p>
            </form>
        </div>
        
        <!-- Existing Posts Table -->
        <div class="card existing-posts">
            <h2>Existing Post Configurations</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Post ID</th>
                        <th>Source URL</th>
                        <th>Post Type</th>
                        <th>Link Text</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                    <tr>
                        <td><?php echo esc_html($post->id); ?></td>
                        <td><?php echo esc_html($post->post_id); ?></td>
                        <td><?php echo esc_url($post->source_url); ?></td>
                        <td><?php echo esc_html($post->post_type); ?></td>
                        <td><?php echo esc_html($post->link_text); ?></td>
                        <td>
                            <a href="<?php echo wp_nonce_url(add_query_arg(array('action' => 'delete', 'id' => $post->id)), 'delete_post_' . $post->id); ?>" 
                               class="button button-small button-link-delete" 
                               onclick="return confirm('Are you sure you want to delete this configuration?');">
                                Delete
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <style>
        .link-updater-posts .card {
            max-width: none;
            margin-top: 20px;
            padding: 20px;
        }
        
        .link-updater-posts .add-new-post {
            margin-bottom: 40px;
        }
        
        .link-updater-posts .form-table th {
            width: 200px;
        }
        
        .link-updater-posts .existing-posts table {
            margin-top: 20px;
        }
    </style>
    <?php
}



// Add a function to check if cron is running properly
function check_link_updater_cron_status() {
    if (defined('DOING_CRON') && DOING_CRON) {
        custom_log('Cron job is running', 'info');
    }else{
        custom_log('Cron job is NOT running','warning');
    }
}
add_action('run_link_updater', 'check_link_updater_cron_status', 1);



// Modify PostLinkUpdater class to use dynamic configurations
class PostLinkUpdater {
    private $post_configs = [];
    
    public function __construct() {
        $this->load_post_configs();
    }
    
    private function load_post_configs() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'link_updater_posts';
        $posts = $wpdb->get_results("SELECT * FROM $table_name");
        
        foreach ($posts as $post) {
            $this->post_configs[$post->post_id] = array(
                'url' => $post->source_url,
                'type' => $post->post_type,
                'link_patterns' => explode("\n", str_replace("\r", "", $post->link_patterns)),
                'link_text' => $post->link_text
            );
        }
    }

    private $date_formats = [
        'display' => 'F j, Y',          // November 1, 2024
        'dot' => 'j.n.Y',              // 1.11.2024
        'dot_alt' => 'd.n.y',              // 01.11.24
        'dot_alt_2' => 'd.n.Y',              // 01.11.2024
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
            case 'board_kings':
                $today_links = $this->extract_board_kings_links($html);
                break;
            case 'coin_master':
                $today_links = $this->extract_coin_master_links($html);
                break;
            case 'bingo_bash':
                $today_links = $this->extract_bingo_bash_links($html);
                break;
            case 'house_of_fun':
                $today_links = $this->extract_house_of_fun_links($html);
                break;
            case 'pop_slots':
                $today_links = $this->extract_pop_slots_links($html);
                break;
            case 'solitaire_grand_harvest':
                $today_links = $this->extract_solitaire_grand_harvest_links($html);
                break;
            case 'coin_tales':
                $today_links = $this->extract_coin_tales_links($html);
                break;
            case 'jackpot_party':
                $today_links = $this->extract_jackpot_party_links($html);
                break;
            case 'crazy_fox':
                $today_links = $this->extract_crazy_fox_links($html);
                break;
            case 'heart_of_vegas':
                $today_links = $this->extract_heart_of_vegas_links($html);
                break;
            case 'cash_frenzy':
                $today_links = $this->extract_cash_frenzy_links($html);
                break;
            case 'wsop_chips':
                $today_links = $this->extract_wsop_chips_links($html);
                break;
            case 'caesars_casino':
                $today_links = $this->extract_caesars_casino_links($html);
                break;
            case 'doubleu_casino':
                $today_links = $this->extract_doubleu_casino_links($html);
                break;
            case 'doubledown_casino':
                $today_links = $this->extract_doubledown_casino_links($html);
                break;
            case 'huuuge_casino':
                $today_links = $this->extract_huuuge_casino_links($html);
                break;
            case 'quick_hit_slots':
                $today_links = $this->extract_quick_hit_slots_links($html);
                break;
            case 'bingo_blitz':
                $today_links = $this->extract_bingo_blitz_links($html);
                break;
            case 'slotomania':
                $today_links = $this->extract_slotomania_links($html);
                break;
            case 'gold_fish':
                $today_links = $this->extract_gold_fish_links($html);
                break;
            case 'club_vegas':
                $today_links = $this->extract_club_vegas_links($html);
                break;
            case 'bingo_aloha':
                $today_links = $this->extract_bingo_aloha_links($html);
                break;
            case 'animal_kingdom':
                $today_links = $this->extract_animal_kingdom_links($html);
                break;
            case 'backgammon_lord':
                $today_links = $this->extract_backgammon_lord_links($html);
                break;
            case 'my_vegas_slots':
                $today_links = $this->extract_my_vegas_slots_links($html);
                break;
            case 'piggy_go':
                $today_links = $this->extract_piggy_go_links($html);
                break;
            case 'pirate_kings':
                $today_links = $this->extract_pirate_kings_links($html);
                break;
            case 'big_fish':
                $today_links = $this->extract_big_fish_links($html);
                break;
            case 'billionaire_casino':
                $today_links = $this->extract_billionaire_casino_links($html);
                break;
            case 'jackpot_world':
                $today_links = $this->extract_jackpot_world_links($html);
                break;
            case 'game_of_thrones':
                $today_links = $this->extract_game_of_thrones_links($html);
                break;
            case 'wizard_of_oz':
                $today_links = $this->extract_wizard_of_oz_links($html);
                break;
            case 'gaminator':
                $today_links = $this->extract_gaminator_links($html);
                break;
            case 'travel_town':
                $today_links = $this->extract_travel_town_links($html);
                break;
            case 'family_island':
                $today_links = $this->extract_family_island_links($html);
                break;
            case 'spin_a_spell':
                $today_links = $this->extract_spin_a_spell_links($html);
                break;
            case '8_ball_pool':
                $today_links = $this->extract_8_ball_pool_links($html);
                break;
            case 'willy_wonka':
                $today_links = $this->extract_willy_wonka_links($html);
                break;
            case 'scatter_slots':
                $today_links = $this->extract_scatter_slots_links($html);
                break;
            case 'lotsa_slots':
                $today_links = $this->extract_lotsa_slots_links($html);
                break;
            case 'mgm_live_slots':
                $today_links = $this->extract_mgm_live_slots_links($html);
                break;
            case 'slotpark':
                $today_links = $this->extract_slotpark_links($html);
                break;
            case 'bingo_holiday':
                $today_links = $this->extract_bingo_holiday_links($html);
                break;
            case 'dice_dreams':
                $today_links = $this->extract_dice_dreams_links($html);
                break;
            case 'island_king':
                $today_links = $this->extract_island_king_links($html);
                break;
            case 'gametwist_slots':
                $today_links = $this->extract_gametwist_slots_links($html);
                break;
        }
        

        return $today_links;
    }
    
    private function extract_gametwist_slots_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot_alt_2']); // Using dot format: "01.11.2024"
        
        foreach ($this->post_configs[2075]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Gametwist Slots from techyhigher.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_island_king_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1984]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Island King from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    
    private function extract_dice_dreams_links($html) {
    $links = [];
    $date = date('d F Y'); // Format: "02 November 2024"
    
    foreach ($this->post_configs[1922]['link_patterns'] as $pattern) {
        // Match section between heading with today's date and next heading
        $section_pattern = '/<h4[^>]*>.*?Updated On: ' . preg_quote($date, '/') . 
            '.*?<\/h4>(.*?)(?=<h4|$)/si';
        
        if (preg_match($section_pattern, $html, $section)) {
            // Extract href links from the matched section
            $link_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>/i';
            preg_match_all($link_pattern, $section[1], $matches);
            
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
    }
    
    $links = array_unique($links); // Remove duplicates
    custom_log("Fetched " . count($links) . " links for Dice Dreams from coinscrazy.com: " . implode(', ', $links));
    return $links;
}
    
    private function extract_bingo_holiday_links($html) {
    $date_pattern = '<span id="Bingo_Holiday_Free_Credits_Link_-_' . date($this->date_formats['underscore']) . '">';
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
    foreach ($this->post_configs[2052]['link_patterns'] as $pattern) {
        preg_match_all('/<a href="(' . preg_quote($pattern, '/') . '[^"]+)"[^>]*>/i', 
            $section_content, $matches);
        
        if (!empty($matches[1])) {
            $links = array_merge($links, $matches[1]);
        }
    }
    
    $links = array_unique($links);
    $links = array_reverse($links);
    
    custom_log("Fetched " . count($links) . " links for Bingo Holiday from rezortricks.com: " . implode(', ', $links));
    return $links;
}
    
    private function extract_slotpark_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[2034]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Slotpark from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_mgm_live_slots_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[2036]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for MGM Live Slots from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_lotsa_slots_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[2038]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Lotsa Slots from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_scatter_slots_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[2042]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Scatter Slots from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_willy_wonka_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[2048]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Willy Wonka from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_8_ball_pool_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[2050]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for 8 Ball Pool from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_spin_a_spell_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot_alt']); // Using dot format: "01.11.24"
        
        foreach ($this->post_configs[2045]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Spin a Spell from rezortricks.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_family_island_links($html) {
    $links = [];
    $date = date('F j, Y'); // Format: "November 3, 2024"
    
    // Create pattern to match content between today's date heading and next heading
    $pattern = '/<strong>' . preg_quote($date, '/') . ':<\/strong><\/p>\s*<div class="reward-box">(.*?)(?=<p>|$)/s';
    
    if (preg_match($pattern, $html, $match)) {
        // Extract all data-link attributes from the matched section
        foreach ($this->post_configs[1972]['link_patterns'] as $pattern) {
            $link_pattern = '/data-link="(' . preg_quote($pattern, '/') . '[^"]+)"/';
            preg_match_all($link_pattern, $match[1], $link_matches);
            
            if (!empty($link_matches[1])) {
                $links = array_merge($links, $link_matches[1]);
            }
        }
    }
    
    $links = array_unique($links); // Remove duplicates
    custom_log("Fetched " . count($links) . " links for Family Island from simplegamingguide.com: " . implode(', ', $links));
    return $links;
}
    
    private function extract_travel_town_links($html) {
    $links = [];
    $date = date('F j, Y'); // Format: "November 3, 2024"
    
    // Create pattern to match content between today's date heading and next heading
    $pattern = '/<strong>' . preg_quote($date, '/') . ':<\/strong><\/p>\s*<div class="reward-box">(.*?)(?=<p>|$)/s';
    
    if (preg_match($pattern, $html, $match)) {
        // Extract all data-link attributes from the matched section
        foreach ($this->post_configs[1990]['link_patterns'] as $pattern) {
            $link_pattern = '/data-link="(' . preg_quote($pattern, '/') . '[^"]+)"/';
            preg_match_all($link_pattern, $match[1], $link_matches);
            
            if (!empty($link_matches[1])) {
                $links = array_merge($links, $link_matches[1]);
            }
        }
    }
    
    $links = array_unique($links); // Remove duplicates
    custom_log("Fetched " . count($links) . " links for Travel Town from simplegamingguide.com: " . implode(', ', $links));
    return $links;
}
    

    
    private function extract_gaminator_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1963]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Gaminator from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_wizard_of_oz_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1970]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Wizard of Oz from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_game_of_thrones_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1976]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Game of Thrones from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    
    private function extract_jackpot_world_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1980]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Jackpot World from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    
    private function extract_billionaire_casino_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1980]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Billionaire Casino from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_big_fish_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1982]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Big Fish from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_pirate_kings_links($html) {
    $date_pattern = '<span id="Pirate_Kings_Free_Spins_Link_-_' . date($this->date_formats['underscore']) . '">';
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
    foreach ($this->post_configs[1986]['link_patterns'] as $pattern) {
        preg_match_all('/<a href="(' . preg_quote($pattern, '/') . '[^"]+)"[^>]*>/i', 
            $section_content, $matches);
        
        if (!empty($matches[1])) {
            $links = array_merge($links, $matches[1]);
        }
    }
    
    $links = array_unique($links);
    $links = array_reverse($links);
    
    custom_log("Fetched " . count($links) . " links for Pirate Kings from rezortricks.com: " . implode(', ', $links));
    return $links;
}
    
    private function extract_piggy_go_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1988]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Piggy Go from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_my_vegas_slots_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1992]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for My Vegas Slots from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_backgammon_lord_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1994]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Backgammon Lord from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_animal_kingdom_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot_alt_2']); // Using dot format: "01.11.2024"
        
        foreach ($this->post_configs[2010]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Animal Kingdom from techyhigher.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_bingo_aloha_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1996]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Bingo Aloha from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_club_vegas_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1998]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Club Vegas from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_gold_fish_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[2000]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Gold Fish from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_slotomania_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1907]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Slotomania from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_bingo_blitz_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1910]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Bingo Blitz from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_quick_hit_slots_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1912]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Quick Hit Slots from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_huuuge_casino_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1914]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Huuuge Casino from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    
    private function extract_doubledown_casino_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1916]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for DoubleDown Casino from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    
    private function extract_doubleu_casino_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot_alt']); // Using dot format: "01.11.24"
        
        foreach ($this->post_configs[1919]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for DoubleU Casino from crazyashwin.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_caesars_casino_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1904]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Caesars Casino from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_wsop_chips_links($html) {
    $links = [];
    $date_pattern = date($this->date_formats['display']); // Using display format: "November 3, 2024"
    
    // Convert date pattern to the format used in the HTML (d F Y)
    $html_date = date('d F Y'); // "03 November 2024"
    
    // Find section with today's date heading
    $section_pattern = '/<p[^>]*><strong>' . preg_quote($html_date, '/') . 
        '<\/strong><\/p>\s*<ol[^>]*>(.*?)(?=<p[^>]*><strong>|$)/is';
            
    if (preg_match($section_pattern, $html, $section_match)) {
        foreach ($this->post_configs[1902]['link_patterns'] as $pattern) {
            // Match links from the section that match the pattern
            $link_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)"[^>]*>(?:\d+\+\s+)?Free\s+(?:Chips|Cards)<\/a>/i';
            
            preg_match_all($link_pattern, $section_match[1], $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
    }
    
    $links = array_unique($links); // Remove duplicates
    custom_log("Fetched " . count($links) . " links for WSOP Chips from freechipswsop.com: " . implode(', ', $links));
    return $links;
}
    
    private function extract_cash_frenzy_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1899]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Cash Frenzy from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_heart_of_vegas_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1893]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Heart of Vegas from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_crazy_fox_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1891]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Crazy Fox from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_jackpot_party_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1888]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Jackpot Party from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_coin_tales_links($html) {
    $links = [];
    $today = date('d F Y'); // Format: "03 November 2024"
    
    // Pattern to match heading with today's date and get the following link
    $regex_pattern = '/<h4[^>]*>.*?' . preg_quote($today, '/') . 
        '.*?<\/h4>.*?<a\s+href="([^"]+)".*?>/is';
    
    if (preg_match($regex_pattern, $html, $match)) {
        if (!empty($match[1])) {
            $links[] = $match[1];
        }
    }
    
    $links = array_unique($links); // Remove duplicates (though we expect only one)
    custom_log("Fetched " . count($links) . " links for Coin Tales: " . implode(', ', $links));
    return $links;
}
    
    private function extract_solitaire_grand_harvest_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1807]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Solitaire Grand Harvest from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_pop_slots_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1785]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Pop Slots from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
private function extract_house_of_fun_links($html) {
    $date_pattern = '<span id="HOF_todays_free_coins_and_spins_link-_' . date($this->date_formats['underscore']) . '">';
    $pos = strpos($html, $date_pattern);
    $section_start = $pos;
    $next_h4_pos = strpos($html, '<h4', $pos + strlen($date_pattern));
    $section_end = $next_h4_pos !== false ? $next_h4_pos : strlen($html);
    $section_content = substr($html, $section_start, $section_end - $section_start);
    $links = [];
    
    preg_match_all('/<a href="([^"]+)"[^>]*>/i', $section_content, $matches);
    
    if (!empty($matches[1])) {
        $links = $matches[1];
    }
    
    $links = array_unique($links);
    $links = array_reverse($links);
    
    custom_log("Fetched " . count($links) . " links for House of Fun from rezortricks.com: " . implode(', ', $links));
    return $links;
}
    
    private function extract_bingo_bash_links($html) {
    // Create a new DOMDocument
    $dom = new DOMDocument();
    
    // Suppress warnings for malformed HTML
    @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    
    $date_string = 'Free Chips – ' . date($this->date_formats['human']);
    
    // Find all h4 elements with the specific date
    $xpath = new DOMXPath($dom);
    $h4_elements = $xpath->query("//h4[contains(., '{$date_string}')]");
    
    $links = [];
    
    // If no matching h4 found, log and return empty
    if ($h4_elements->length == 0) {
        custom_log("Could not find section with today's date pattern", 'warning');
        return [];
    }
    
    // Get the first matching h4 element
    $target_h4 = $h4_elements->item(0);
    
    // Find all links in the section after this h4
    $current = $target_h4;
    while ($current = $current->nextSibling) {
        // Stop if we hit the next h4 (end of section)
        if ($current->nodeName === 'h4') {
            break;
        }
        
        // Only process element nodes
        if ($current->nodeType === XML_ELEMENT_NODE) {
            $anchors = $current->getElementsByTagName('a');
            foreach ($anchors as $anchor) {
                $href = $anchor->getAttribute('href');
                $links[] = $href;
            }
        }
    }
    
    // Remove duplicates, reverse order
    $links = array_values(array_unique($links));
    $links = array_reverse($links);
    
    custom_log("Fetched " . count($links) . " links Bingo Bash from rezortricks.com: " . implode(', ', $links));
    return $links;
}
        
    private function extract_coin_master_links($html) {
    $links = [];
    
    // Match the section with the "Today CM free spins links" heading
    $section_pattern = '/<h2>Today CM free spins links - <span id="currentDate">(.*?)<\/span><\/h2>(.*?)(?=<h[23]|$)/si';
    if (preg_match($section_pattern, $html, $matches)) {
        $date = $matches[1];
        
        // Check each configured link pattern
        foreach ($this->post_configs[387]['link_patterns'] as $pattern) {
            // Extract href links from the matched section that match the pattern
            $link_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)"[^>]*>/i';
            preg_match_all($link_pattern, $matches[2], $link_matches);
            
            if (!empty($link_matches[1])) {
                $links = array_merge($links, $link_matches[1]);
            }
        }
    }
    
    $links = array_unique($links); // Remove duplicates
    custom_log("Fetched " . count($links) . " links for Coin Master from crazyashwin.com: " . implode(', ', $links));
    return $links;
}
    
    private function extract_board_kings_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[230]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Board Kings from mosttechs.com: " . implode(', ', $links));
        return $links;
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
    custom_log("Fetched " . count($links) . " links for Match Masters from mosttechs.com: " . implode(', ', $links));
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
        
        // Check if today's heading already exists
        if (strpos($content, $today_date) !== false) {
            custom_log("Today's heading already exists.", 'info');
            return ['content' => $content, 'modified' => $content_modified];
        }
        
        // Find all existing date headings with their full sections
        preg_match_all('/<h4[^>]*>.*?(\d{1,2}\s+\w+\s+\d{4}).*?<\/h4>.*?(?=<h4|$)/s', $content, $date_matches, PREG_SET_ORDER);
        
        // If we have more than 5 dates, remove all older dates
        if (count($date_matches) > 5) {
            // Keep only the 5 most recent dates
            $dates_to_keep = array_slice($date_matches, -5);
            
            // Remove all sections before the 5 most recent dates
            foreach ($date_matches as $match) {
                if (!in_array($match, $dates_to_keep)) {
                    $content = str_replace($match[0], '', $content);
                }
            }
            
            custom_log("Removed dates older than the 5 most recent", 'info');
        }
        
        // Find the position after the first h2 header
        if (preg_match('/<h2[^>]*>.*?<\/h2>/s', $content, $matches, PREG_OFFSET_CAPTURE)) {
            $first_h2_end = $matches[0][1] + strlen($matches[0][0]);
            
            // Prepare the new section with today's heading and links
            $new_section = "\n" . $today_heading . "\n";
            
            // Add links if available
            if (!empty($links)) {
                $new_section .= $this->generate_links_html($links, $config['link_text']);
                custom_log("Added " . count($links) . " new links under today's heading");
            }
            
            // Insert the new section after the first h2 header
            $content = substr_replace($content, $new_section, $first_h2_end, 0);
            $content_modified = true;
        } else {
            // Do nothing if no h2 header is found
            custom_log("No h2 header found. Skipping link addition.", 'warning');
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
                '<li><a href="%s" target="_blank" rel="noopener"><strong>%s</strong></a></li>' . "\n",
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
        
        $post = get_post($post_id);
        if (!$post) {
            custom_log("Post with ID $post_id not found.", 'error');
            continue;
        }

        // Generate today's heading regardless of link status
        $today_heading = $this->generate_heading(date($this->date_formats['display']));
        
        // Get links from source
        $links = $this->get_links_from_source($config['url'], $config);
        
        if (empty($links)) {
            custom_log("No links found for Post ID $post_id from source {$config['url']}.", 'warning');
            // Still proceed with empty links array to update heading
        }
        
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
    try {
        // Initialize the PostLinkUpdater class
        $updater = new PostLinkUpdater();
        
        // Run the updates
        $updater->run_updates();
        
    } catch (Exception $e) {
        // Log the error message
        custom_log('Error in daily_update_links: ' . $e->getMessage());
        
    }
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
