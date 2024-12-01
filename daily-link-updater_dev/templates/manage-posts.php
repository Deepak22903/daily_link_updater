<?php
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
}
