
---

# Daily Link Updater Plugin

The **Daily Link Updater** is a WordPress plugin designed to automate the process of updating one-time-use in-game reward links on your site. These links are sourced from an external website and are updated daily to ensure that users always have access to the latest working links.

## Features

- **Manual Trigger**: Start the update process manually from the WordPress dashboard.
- **Web Scraping**: Extracts links directly from a specified source web page that lacks an API.
- **Selective Replacement**: Updates only the link buttons within specific sections of each post, leaving other content unchanged.
- **Link Validation**: Validates each link before updating to ensure it’s active and working.
- **Error Handling**: Skips updates for any missing or inaccessible links and logs errors for easy review.

## Requirements

- WordPress 5.0 or later
- PHP 7.0 or later
- cURL extension enabled in PHP

## Installation

1. Download or clone this repository into your `wp-content/plugins` directory.
2. Ensure the plugin folder is named `daily-link-updater`.
3. Activate the **Daily Link Updater** plugin from the **Plugins** menu in WordPress.
4. Go to **Settings > Link Updater** to start the update process manually.

## Usage

1. Navigate to **Settings > Link Updater** in your WordPress admin dashboard.
2. Click **Start Update** to manually trigger the link update process.
3. The plugin will:
   - Fetch the latest links from the external source website.
   - Validate each link to ensure it’s functional.
   - Update the button links in the designated section of each post.
   - Log any errors encountered during the process.

## Configuration

- **Source URL**: The URL of the page from which links are scraped is defined within the plugin code. Modify this URL in the `get_links_from_source()` function in `daily-link-updater.php` if needed.
- **Error Logging**: Errors and link validation failures are logged in `wp-content/plugin-logs/link-updater.log`.

## Code Structure

- **daily-link-updater.php**: Core plugin file containing all main functions.
  - **Admin Page Setup**: Adds the plugin page under **Settings** in the WordPress dashboard.
  - **Link Scraping**: Extracts links from the specified source using cURL or a scraping library.
  - **Link Validation**: Checks that each link is active before updating.
  - **Content Update**: Replaces old links in the posts' button sections with new, validated links.

## Troubleshooting

- **Link Validation Fails**: Ensure that the links are live and accessible. The plugin skips any failed link updates and logs the issue.
- **No Links Found**: If no links are detected on the source page, check that the URL and regex patterns in the `get_links_from_source()` function are correct.
- **Log Not Generated**: Ensure that the `wp-content/plugin-logs` directory is writable by the web server.

## License

This plugin is open-source and available under the MIT License.

## Acknowledgments

- [Goutte](https://github.com/FriendsOfPHP/Goutte) - PHP Web Scraping Library for advanced scraping capabilities.
  
---

