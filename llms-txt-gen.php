<?php
/**
 * Plugin Name: llms.txt Gen
 * Plugin URI: https://immortalseo.com
 * Description: Generate optimized LLMs.txt files for AI search engines.
 * Version: 2.0.0
 * Author: ImmortalSEO
 * Author URI: https://immortalseo.com
 * Text Domain: llms-txt-gen
 */

// Exit if accessed directly


if (!defined('ABSPATH')) {
    exit;
}
// Register activation hook
register_activation_hook(__FILE__, 'llms_generator_activate');

/**
 * Plugin activation function
 */
function llms_generator_activate() {
    // Create database table for URL history
    llms_generator_create_history_table();
    
    // Set version in options
    update_option('llms_generator_version', LLMS_GENERATOR_VERSION);
}

/**
 * Create the URL history table
 */
function llms_generator_create_history_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'llms_generator_history';
    $charset_collate = $wpdb->get_charset_collate();
    
    // SQL to create the history table
    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        url varchar(255) NOT NULL,
        generated_date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        user_id bigint(20) DEFAULT NULL,
        llms_txt longtext DEFAULT NULL,
        urls_found longtext DEFAULT NULL,
        PRIMARY KEY  (id),
        KEY url (url),
        KEY user_id (user_id),
        KEY generated_date (generated_date)
    ) $charset_collate;";
    
    // Include WordPress database upgrade functions
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    
    // Create or update the table
    dbDelta($sql);
}

// Define plugin constants
define('LLMS_GENERATOR_VERSION', '1.0.0');
define('LLMS_GENERATOR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LLMS_GENERATOR_PLUGIN_URL', plugin_dir_url(__FILE__));

// Create the shortcode
add_shortcode('llms_generator', 'llms_generator_shortcode');

// Register test shortcode for debugging
add_shortcode('llms_test', 'llms_test_shortcode');

/**
 * Render the LLMs.txt generator shortcode
 */
function llms_generator_shortcode($atts) {
    // Process shortcode attributes
    $atts = shortcode_atts(
        array(
            'title' => 'Generate LLMs.txt Files',
            'description' => 'Enter your website URL to generate an AI-friendly LLMs.txt file.'
        ),
        $atts,
        'llms_generator'
    );
    
    // Include CSS inline
    $output = '<style>
    .history-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.history-table th,
.history-table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
}

.history-table th {
    background-color: #f0f0f0;
    font-weight: 600;
}

.load-history {
    padding: 5px 10px;
    font-size: 14px;
}
    
    .llms-generator-container {
        max-width: 900px;
        margin: 0 auto;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }
    .generator-form {
        background-color: #f8f9fa;
        padding: 25px;
        border-radius: 8px;
        margin-bottom: 30px;
        border: 1px solid #e0e0e0;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
    }
    .form-control {
        width: 100%;
        padding: 12px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 16px;
    }
    .llms-btn {
        padding: 12px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 500;
        font-size: 16px;
    }
    .llms-btn-primary {
        background-color: #0171ce;
        color: white;
    }
    .llms-btn-secondary {
        background-color: #f0f0f0;
        color: #333;
    }
    .error-message {
        color: #cf0000;
        background-color: #ffe6e6;
        padding: 10px;
        border-radius: 4px;
        margin-bottom: 15px;
    }
    #loading-indicator {
        text-align: center;
        padding: 30px;
    }
    .spinner {
        border: 4px solid rgba(0, 0, 0, 0.1);
        border-radius: 50%;
        border-top: 4px solid #0171ce;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin: 0 auto 15px auto;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .tabs {
        display: flex;
        border-bottom: 1px solid #e0e0e0;
        margin-bottom: 20px;
    }
    .tab-button {
        padding: 10px 20px;
        background: none;
        border: none;
        cursor: pointer;
        font-size: 16px;
        color: #666;
        border-bottom: 3px solid transparent;
    }
    .tab-button.active {
        color: #0171ce;
        border-bottom-color: #0171ce;
    }
    .tab-content {
        display: none;
    }
    .tab-content.active {
        display: block;
    }
    .code-container {
        background-color: #f8f9fa;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
    }
    pre {
        padding: 15px;
        overflow: auto;
        max-height: 500px;
        margin: 0;
        white-space: pre-wrap;
        font-family: monospace;
    }
    .actions {
        display: flex;
        gap: 10px;
        padding: 15px;
        border-top: 1px solid #e0e0e0;
        background-color: #fff;
    }
    .url-list {
        list-style-type: none;
        padding: 0;
        margin: 0;
    }
    .url-list li {
        padding: 8px 12px;
        border-bottom: 1px solid #eee;
        word-break: break-all;
    }
    .llms-notice {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background-color: #10b981;
        color: white;
        padding: 10px 20px;
        border-radius: 4px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        z-index: 9999;
    }
    </style>';
    
    // Start HTML output
    $output .= '<div id="llms-generator-app" class="llms-generator-container">
        <div class="generator-form">
            <h2>' . esc_html($atts['title']) . '</h2>
            <p>' . esc_html($atts['description']) . '</p>
            
            <div class="form-group">
                <label for="website-url">Website URL:</label>
                <input type="text" id="website-url" placeholder="example.com" class="form-control">
            </div>
            
            <div id="error-message" class="error-message" style="display:none;"></div>
            
            <button id="generate-button" class="llms-btn llms-btn-primary">Generate LLMs.txt</button>
        </div>
        
        <div id="loading-indicator" style="display: none;">
            <div class="spinner"></div>
            <p>Generating your LLMs.txt file. This may take a moment...</p>
        </div>
        
        <div id="results-container" style="display: none;">
            <h3>Generated LLMs.txt</h3>
            <div class="tabs">
                <button class="tab-button active" data-tab="llms-txt">LLMs.txt</button>
                <button class="tab-button" data-tab="discovered-urls">Discovered URLs</button>
            </div>
            
            <div id="llms-txt-content" class="tab-content active">
                <div class="code-container">
                    <pre id="llms-txt-code"></pre>
                    <div class="actions">
                        <button id="copy-button" class="llms-btn llms-btn-secondary">Copy to Clipboard</button>
                        <button id="download-button" class="llms-btn llms-btn-secondary">Download File</button>
                    </div>
                </div>
            </div>
            
            <div id="discovered-urls-content" class="tab-content">
                <!-- Discovered URLs will be loaded here -->
            </div>
        </div>
    </div>';
    
    // Include JavaScript with debugging
    $output .= '<script>
    jQuery(document).ready(function($) {
        const generateBtn = $("#generate-button");
        const urlInput = $("#website-url");
        const errorMsg = $("#error-message");
        const loadingIndicator = $("#loading-indicator");
        const resultsContainer = $("#results-container");
        const llmsTextCode = $("#llms-txt-code");
        
        // Handle generate button click
        generateBtn.on("click", function(e) {
            e.preventDefault();
            console.log("Generate button clicked");
            
            const url = urlInput.val().trim();
            
            // Basic validation
            if (!url) {
                errorMsg.text("Please enter a URL").show();
                return;
            }
            
            // Format URL
            let processedUrl = url;
            if (!url.startsWith("http://") && !url.startsWith("https://")) {
                processedUrl = "https://" + url;
            }
            
            console.log("Processing URL:", processedUrl);
            
            // Clear previous errors
            errorMsg.hide();
            
            // Show loading
            loadingIndicator.show();
            resultsContainer.hide();
            
            // Use the WordPress admin-ajax.php as a proxy
            $.ajax({
                url: "' . admin_url('admin-ajax.php') . '",
                type: "POST",
                data: {
                    action: "llms_proxy_request",
                    nonce: "' . wp_create_nonce('llms_proxy_nonce') . '",
                    target_url: processedUrl
                },
                beforeSend: function() {
                    console.log("Sending request to WP proxy");
                },
                success: function(response) {
                    console.log("Response received:", response);
                    loadingIndicator.hide();
                    
                    if (response.success && response.data) {
                        const result = response.data;
                        
                        if (result.status === "error") {
                            errorMsg.text(result.error || "An error occurred").show();
                            return;
                        }
                        
                        // Set LLMs.txt content
                        llmsTextCode.text(result.llms_txt);
                        
                        // Display URLs
                        displayUrls(result.discovered_urls || []);
                        
                        // Show results
                        resultsContainer.show();
                        
                        // Scroll to results
                        $("html, body").animate({
                            scrollTop: resultsContainer.offset().top - 50
                        }, 500);
                    } else {
                        errorMsg.text(response.data || "Invalid response from server").show();
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX error:", status, error);
                    loadingIndicator.hide();
                    errorMsg.text("Failed to generate LLMs.txt: " + (error || "Unknown error")).show();
                }
            });
        });
        
        // Handle tab switching
        $(".tab-button").on("click", function() {
            const tabId = $(this).data("tab");
            
            // Update active tab
            $(".tab-button").removeClass("active");
            $(this).addClass("active");
            
            // Show selected content
            $(".tab-content").removeClass("active");
            $("#" + tabId + "-content").addClass("active");
        });
        
        // Handle copy button
        $("#copy-button").on("click", function() {
            const content = llmsTextCode.text();
            if (!content) return;
            
            navigator.clipboard.writeText(content)
                .then(function() {
                    showNotice("Copied to clipboard!");
                })
                .catch(function() {
                    errorMsg.text("Failed to copy. Please try selecting and copying manually.").show();
                });
        });
        
        // Handle download button
        $("#download-button").on("click", function() {
            const content = llmsTextCode.text();
            if (!content) return;
            
            // Create blob and download
            const blob = new Blob([content], {type: "text/plain"});
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.download = "LLMs.txt";
            link.click();
            
            showNotice("LLMs.txt downloaded");
        });
        
        // Helper function to display URLs
        function displayUrls(urls) {
            const container = $("#discovered-urls-content");
            
            if (!urls.length) {
                container.html("<p>No URLs discovered.</p>");
                return;
            }
            
            let html = "<ul class=\"url-list\">";
            urls.forEach(function(url) {
                html += "<li>" + url + "</li>";
            });
            html += "</ul>";
            
            container.html(html);
        }
        
        // Helper function to show notices
        function showNotice(message) {
            const notice = $("<div class=\"llms-notice\"></div>")
                .text(message)
                .appendTo("body");
                
            setTimeout(function() {
                notice.fadeOut(300, function() {
                    notice.remove();
                });
            }, 3000);
        }
    });
    </script>';
    
    return $output;
}

/**
 * Test shortcode for debugging
 */
function llms_test_shortcode() {
    return '
    <h3>Test LLMs.txt API Directly</h3>
    <div style="max-width:600px; margin:20px 0; padding:15px; background:#f5f5f5; border-radius:4px;">
        <p>This form will open a new window to test the API endpoint directly.</p>
        <form id="test-form" style="display:flex; gap:10px; margin-top:15px;">
            <input type="text" name="test_url" placeholder="URL to test" value="example.com" style="flex:1; padding:8px;">
            <button type="submit" style="padding:8px 16px; background:#0171ce; color:white; border:none; border-radius:4px; cursor:pointer;">Test API Directly</button>
        </form>
    </div>
    <script>
    document.getElementById("test-form").addEventListener("submit", function(e) {
        e.preventDefault();
        const url = this.elements.test_url.value;
        
        // Send a direct fetch request in a new window
        window.open("https://llmstxt-next.vercel.app/api/scrape?test=" + encodeURIComponent(url), "_blank");
    });
    </script>';
}

// Add the AJAX handler for proxying requests
add_action('wp_ajax_llms_proxy_request', 'llms_proxy_request');
add_action('wp_ajax_nopriv_llms_proxy_request', 'llms_proxy_request');

/**
 * Handle proxying requests to the Next.js API
 */
function llms_proxy_request() {
    // Existing code...
    
    // Make the request to Next.js API
    $response = wp_remote_post('https://llmstxt-next.vercel.app/api/scrape', [
        'timeout' => 60,
        'headers' => [
            'Content-Type' => 'application/json',
        ],
        'body' => json_encode([
            'urls' => [$target_url],
            'bulkMode' => false
        ])
    ]);
    
    // Check for errors
    if (is_wp_error($response)) {
        wp_send_json_error('API request failed: ' . $response->get_error_message());
    }
    
    // Get the response body
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    // Check if we got valid JSON
    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error('Invalid JSON response: ' . substr($body, 0, 100) . '...');
    }
    
    // Check if we have data for the requested URL
    if (isset($data[$target_url])) {
        // Save the result to history
        llms_generator_save_history($target_url, $data[$target_url]);
        
        wp_send_json_success($data[$target_url]);
    } else {
        wp_send_json_error('No data returned for the requested URL');
    }
}

/**
 * Save URL check history to database
 *
 * @param string $url The URL that was checked
 * @param array $data The response data
 */
function llms_generator_save_history($url, $data) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'llms_generator_history';
    
    // Get current user ID (0 if not logged in)
    $user_id = get_current_user_id();
    
    // Prepare data for database
    $db_data = [
        'url' => $url,
        'user_id' => $user_id,
        'llms_txt' => isset($data['llms_txt']) ? $data['llms_txt'] : '',
        'urls_found' => isset($data['discovered_urls']) ? maybe_serialize($data['discovered_urls']) : ''
    ];
    
    // Insert into database
    $wpdb->insert($table_name, $db_data);
}

// Add admin menu
add_action('admin_menu', 'llms_generator_admin_menu');

/**
 * Add admin menu items
 */
function llms_generator_admin_menu() {
    add_menu_page(
        'LLMs.txt Generator',
        'LLMs.txt Generator',
        'manage_options',
        'llms-generator',
        'llms_generator_history_page',
        'dashicons-media-text',
        30
    );
}

/**
 * Render the history page
 */
function llms_generator_history_page() {
    global $wpdb;
    
    // Handle deletion if requested
    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id']) && is_numeric($_GET['id'])) {
        check_admin_referer('llms_delete_history');
        
        $id = intval($_GET['id']);
        $table_name = $wpdb->prefix . 'llms_generator_history';
        
        $wpdb->delete($table_name, ['id' => $id], ['%d']);
        
        echo '<div class="notice notice-success"><p>History entry deleted successfully.</p></div>';
    }
    
    // Get history items with pagination
    $table_name = $wpdb->prefix . 'llms_generator_history';
    $per_page = 20;
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($current_page - 1) * $per_page;
    
    // Count total items
    $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    $total_pages = ceil($total_items / $per_page);
    
    // Get paginated results
    $items = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_name ORDER BY generated_date DESC LIMIT %d OFFSET %d", 
            $per_page, 
            $offset
        )
    );
    
    // Render the page
    ?>
    <div class="wrap">
        <h1>LLMs.txt Generator History</h1>
        
        <p>This page shows a history of URLs that have been processed by the LLMs.txt generator.</p>
        
        <?php if (empty($items)) : ?>
            <div class="notice notice-info">
                <p>No history items found. Generate some LLMs.txt files first!</p>
            </div>
        <?php else : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>URL</th>
                        <th>Date Generated</th>
                        <th>User</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item) : ?>
                        <tr>
                            <td><?php echo esc_html($item->url); ?></td>
                            <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item->generated_date))); ?></td>
                            <td>
                                <?php 
                                if ($item->user_id) {
                                    $user = get_userdata($item->user_id);
                                    echo $user ? esc_html($user->display_name) : 'Unknown';
                                } else {
                                    echo 'Guest';
                                }
                                ?>
                            </td>
                            <td>
                                <a href="#" class="button view-content" data-id="<?php echo esc_attr($item->id); ?>">View Content</a>
                                <a href="<?php echo wp_nonce_url(add_query_arg(['action' => 'delete', 'id' => $item->id]), 'llms_delete_history'); ?>" class="button" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if ($total_pages > 1) : ?>
                <div class="tablenav">
                    <div class="tablenav-pages">
                        <span class="displaying-num"><?php echo esc_html($total_items); ?> items</span>
                        <span class="pagination-links">
                            <?php
                            echo paginate_links([
                                'base' => add_query_arg('paged', '%#%'),
                                'format' => '',
                                'prev_text' => '&laquo;',
                                'next_text' => '&raquo;',
                                'total' => $total_pages,
                                'current' => $current_page
                            ]);
                            ?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Modal for content view -->
            <div id="content-modal" style="display:none; position:fixed; z-index:999; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.4);">
                <div style="background-color:#fefefe; margin:10% auto; padding:20px; border:1px solid #888; width:80%; max-width:800px;">
                    <span style="color:#aaa; float:right; font-size:28px; font-weight:bold; cursor:pointer;" onclick="document.getElementById('content-modal').style.display='none'">&times;</span>
                    <h2>LLMs.txt Content</h2>
                    <pre id="modal-content" style="background:#f8f9fa; padding:15px; overflow:auto; max-height:400px;"></pre>
                    <h3>Discovered URLs</h3>
                    <div id="modal-urls" style="background:#f8f9fa; padding:15px; overflow:auto; max-height:200px;"></div>
                </div>
            </div>
            
            <script>
            jQuery(document).ready(function($) {
                // Handle view content button clicks
                $('.view-content').on('click', function(e) {
                    e.preventDefault();
                    const id = $(this).data('id');
                    
                    // Get content via AJAX
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'llms_get_history_content',
                            id: id,
                            nonce: '<?php echo wp_create_nonce('llms_history_content_nonce'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#modal-content').text(response.data.llms_txt);
                                
                                // Display URLs
                                let urlsHtml = '';
                                if (response.data.urls_found && response.data.urls_found.length > 0) {
                                    urlsHtml = '<ul>';
                                    response.data.urls_found.forEach(function(url) {
                                        urlsHtml += '<li>' + url + '</li>';
                                    });
                                    urlsHtml += '</ul>';
                                } else {
                                    urlsHtml = '<p>No URLs found</p>';
                                }
                                $('#modal-urls').html(urlsHtml);
                                
                                // Show modal
                                $('#content-modal').show();
                            } else {
                                alert('Error: ' + (response.data || 'Could not retrieve content'));
                            }
                        },
                        error: function() {
                            alert('Error: Could not connect to server');
                        }
                    });
                });
            });
            </script>
        <?php endif; ?>
    </div>
    <?php
}

// Add AJAX handler for getting history content
add_action('wp_ajax_llms_get_history_content', 'llms_get_history_content');

/**
 * AJAX handler for getting history content
 */
function llms_get_history_content() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'llms_history_content_nonce')) {
        wp_send_json_error('Security check failed');
    }
    
    // Get the history ID
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    if ($id <= 0) {
        wp_send_json_error('Invalid ID');
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'llms_generator_history';
    
    // Get the history item
    $item = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id)
    );
    
    if (!$item) {
        wp_send_json_error('Item not found');
    }
    
    // Prepare response data
    $response = [
        'llms_txt' => $item->llms_txt,
        'urls_found' => maybe_unserialize($item->urls_found)
    ];
    
    wp_send_json_success($response);
}

// Add this to your shortcode HTML, inside the tabs section
$output .= '<button class="tab-button" data-tab="history">History</button>';

// Add this to your shortcode HTML, after other tab content divs
$output .= '<div id="history-content" class="tab-content">
    <div id="history-list">Loading history...</div>
</div>';

// Add this to your shortcode JavaScript
$output .= '
// Load history when history tab is clicked
$(".tab-button[data-tab=\'history\']").on("click", function() {
    loadHistory();
});

// Function to load history
function loadHistory() {
    $.ajax({
        url: "' . admin_url('admin-ajax.php') . '",
        type: "POST",
        data: {
            action: "llms_get_public_history",
            nonce: "' . wp_create_nonce('llms_public_history_nonce') . '"
        },
        success: function(response) {
            if (response.success) {
                displayHistory(response.data);
            } else {
                $("#history-list").html("<p>Error loading history: " + (response.data || "Unknown error") + "</p>");
            }
        },
        error: function() {
            $("#history-list").html("<p>Error: Could not connect to server</p>");
        }
    });
}

// Function to display history
function displayHistory(items) {
    if (!items || items.length === 0) {
        $("#history-list").html("<p>No history found. Generate some LLMs.txt files first!</p>");
        return;
    }
    
    let html = "<table class=\"history-table\">";
    html += "<thead><tr><th>URL</th><th>Date</th><th>Action</th></tr></thead><tbody>";
    
    items.forEach(function(item) {
        html += "<tr>";
        html += "<td>" + item.url + "</td>";
        html += "<td>" + item.date + "</td>";
        html += "<td><button class=\"llms-btn llms-btn-secondary load-history\" data-id=\"" + item.id + "\">Load</button></td>";
        html += "</tr>";
    });
    
    html += "</tbody></table>";
    
    $("#history-list").html(html);
    
    // Add click handler for load buttons
    $(".load-history").on("click", function() {
        const id = $(this).data("id");
        
        $.ajax({
            url: "' . admin_url('admin-ajax.php') . '",
            type: "POST",
            data: {
                action: "llms_get_history_content",
                id: id,
                nonce: "' . wp_create_nonce('llms_history_content_nonce') . '"
            },
            success: function(response) {
                if (response.success) {
                    // Load the content into the main tab
                    llmsTextCode.text(response.data.llms_txt);
                    displayUrls(response.data.urls_found || []);
                    
                    // Switch to main tab
                    $(".tab-button[data-tab=\'llms-txt\']").click();
                    
                    showNotice("Historical content loaded");
                } else {
                    errorMsg.text("Error: " + (response.data || "Could not load content")).show();
                }
            },
            error: function() {
                errorMsg.text("Error: Could not connect to server").show();
            }
        });
    });
}';

// Add AJAX handler for getting public history
add_action('wp_ajax_llms_get_public_history', 'llms_get_public_history');
add_action('wp_ajax_nopriv_llms_get_public_history', 'llms_get_public_history');

/**
 * AJAX handler for getting public history
 */
function llms_get_public_history() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'llms_public_history_nonce')) {
        wp_send_json_error('Security check failed');
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'llms_generator_history';
    
    // Get current user ID (0 if not logged in)
    $user_id = get_current_user_id();
    
    // Get history items (for current user if logged in, otherwise most recent)
    if ($user_id > 0) {
        $items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, url, generated_date FROM $table_name WHERE user_id = %d ORDER BY generated_date DESC LIMIT 20",
                $user_id
            )
        );
    } else {
        // For non-logged in users, use session cookie or IP to filter if possible
        // Here we just show recent items for simplicity
        $items = $wpdb->get_results(
            "SELECT id, url, generated_date FROM $table_name ORDER BY generated_date DESC LIMIT 10"
        );
    }
    
    // Format the response data
    $response = [];
    foreach ($items as $item) {
        $response[] = [
            'id' => $item->id,
            'url' => $item->url,
            'date' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item->generated_date))
        ];
    }
    
    wp_send_json_success($response);
}