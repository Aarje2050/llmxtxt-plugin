<?php
/**
 * Plugin Name: LLMs.txt Generator
 * Plugin URI: https://immortalseo.com/llm-txt-generator
 * Description: Generate optimized LLMs.txt files for AI search engines.
 * Version: 1.0.0
 * Author: ImmortalSEO
 * Author URI: https://immortalseo.com
 * Text Domain: llms-txt-generator
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
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
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'llms_proxy_nonce')) {
        wp_send_json_error('Security check failed');
    }
    
    // Get the target URL
    $target_url = isset($_POST['target_url']) ? sanitize_text_field($_POST['target_url']) : '';
    
    if (empty($target_url)) {
        wp_send_json_error('No URL provided');
    }
    
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
        wp_send_json_success($data[$target_url]);
    } else {
        wp_send_json_error('No data returned for the requested URL');
    }
}