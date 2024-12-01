<?php
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
