<?php
/**
 * Template for the LLMs.txt Generator
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div id="llms-generator-app" class="llms-generator-container">
    <div class="generator-form">
        <?php if (!empty($atts['title'])) : ?>
            <h2><?php echo esc_html($atts['title']); ?></h2>
        <?php endif; ?>
        
        <?php if (!empty($atts['description'])) : ?>
            <p><?php echo esc_html($atts['description']); ?></p>
        <?php endif; ?>
        
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
</div>