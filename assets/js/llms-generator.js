/**
 * LLMs.txt Generator
 * Simple implementation that works with your Next.js API
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        const generateBtn = $('#generate-button');
        const urlInput = $('#website-url');
        const errorMsg = $('#error-message');
        const loadingIndicator = $('#loading-indicator');
        const resultsContainer = $('#results-container');
        const llmsTextCode = $('#llms-txt-code');
        
        // Handle generate button click
        generateBtn.on('click', function(e) {
            e.preventDefault();
            
            const url = urlInput.val().trim();
            
            // Basic validation
            if (!url) {
                showError('Please enter a URL');
                return;
            }
            
            // Format URL
            let processedUrl = url;
            if (!url.startsWith('http://') && !url.startsWith('https://')) {
                processedUrl = 'https://' + url;
            }
            
            // Clear previous errors
            errorMsg.hide();
            
            // Show loading
            loadingIndicator.show();
            resultsContainer.hide();
            
            // Make API request to your Next.js app
            $.ajax({
                url: 'https://llmstxt-next.vercel.app/api/scrape',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    urls: [processedUrl],
                    bulkMode: false
                }),
                success: function(response) {
                    // Hide loading
                    loadingIndicator.hide();
                    
                    // Process response
                    if (response && response[processedUrl]) {
                        const result = response[processedUrl];
                        
                        if (result.status === 'error') {
                            showError(result.error || 'An error occurred');
                            return;
                        }
                        
                        // Set LLMs.txt content
                        llmsTextCode.text(result.llms_txt);
                        
                        // Display URLs
                        displayUrls(result.discovered_urls || []);
                        
                        // Show results
                        resultsContainer.show();
                        
                        // Scroll to results
                        $('html, body').animate({
                            scrollTop: resultsContainer.offset().top - 50
                        }, 500);
                    } else {
                        showError('Invalid response from server');
                    }
                },
                error: function(xhr, status, error) {
                    loadingIndicator.hide();
                    showError('Failed to generate LLMs.txt: ' + (error || 'Unknown error'));
                }
            });
        });
        
        // Handle tab switching
        $('.tab-button').on('click', function() {
            const tabId = $(this).data('tab');
            
            // Update active tab
            $('.tab-button').removeClass('active');
            $(this).addClass('active');
            
            // Show selected content
            $('.tab-content').removeClass('active');
            $('#' + tabId + '-content').addClass('active');
        });
        
        // Handle copy button
        $('#copy-button').on('click', function() {
            const content = llmsTextCode.text();
            if (!content) return;
            
            navigator.clipboard.writeText(content)
                .then(function() {
                    showNotice('Copied to clipboard!');
                })
                .catch(function() {
                    showError('Failed to copy. Please try selecting and copying manually.');
                });
        });
        
        // Handle download button
        $('#download-button').on('click', function() {
            const content = llmsTextCode.text();
            if (!content) return;
            
            // Create blob and download
            const blob = new Blob([content], {type: 'text/plain'});
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'LLMs.txt';
            link.click();
            
            showNotice('LLMs.txt downloaded');
        });
        
        // Helper function to display URLs
        function displayUrls(urls) {
            const container = $('#discovered-urls-content');
            
            if (!urls.length) {
                container.html('<p>No URLs discovered.</p>');
                return;
            }
            
            let html = '<ul class="url-list">';
            urls.forEach(function(url) {
                html += '<li>' + url + '</li>';
            });
            html += '</ul>';
            
            container.html(html);
        }
        
        // Helper function to show errors
        function showError(message) {
            errorMsg.text(message).show();
        }
        
        // Helper function to show notices
        function showNotice(message) {
            const notice = $('<div class="llms-notice"></div>')
                .text(message)
                .appendTo('body');
                
            setTimeout(function() {
                notice.fadeOut(300, function() {
                    notice.remove();
                });
            }, 3000);
        }
    });
})(jQuery);