/**
 * LLMs.txt Generator
 * Frontend JavaScript
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // API URL from WordPress
        const API_URL = llmsGeneratorVars.apiUrl;
        
        // Elements
        const generateButton = document.getElementById('generate-button');
        const urlInput = document.getElementById('website-url');
        const errorMessage = document.getElementById('error-message');
        const loadingIndicator = document.getElementById('loading-indicator');
        const resultsContainer = document.getElementById('results-container');
        const llmsTextCode = document.getElementById('llms-txt-code');
        const copyButton = document.getElementById('copy-button');
        const downloadButton = document.getElementById('download-button');
        const editButton = document.getElementById('edit-button');
        const editorContainer = document.getElementById('editor-container');
        const editor = document.getElementById('editor');
        const saveButton = document.getElementById('save-button');
        const cancelButton = document.getElementById('cancel-button');
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');
        
        // Current state
        let currentResults = null;
        let activeUrl = null;
        
        // Initialize event listeners
        if (generateButton) {
            generateButton.addEventListener('click', handleGenerate);
        }
        
        if (copyButton) {
            copyButton.addEventListener('click', handleCopy);
        }
        
        if (downloadButton) {
            downloadButton.addEventListener('click', handleDownload);
        }
        
        if (editButton) {
            editButton.addEventListener('click', handleEdit);
        }
        
        if (saveButton) {
            saveButton.addEventListener('click', handleSave);
        }
        
        if (cancelButton) {
            cancelButton.addEventListener('click', handleCancel);
        }
        
        // Initialize tab functionality
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                handleTabChange(this);
            });
        });
        
        /**
         * Handle generate button click
         */
        function handleGenerate() {
            const url = urlInput.value.trim();
            
            // Validate URL
            if (!url) {
                showError('Please enter a URL');
                return;
            }
            
            // Format URL
            let processedUrl = url;
            if (!url.startsWith('http://') && !url.startsWith('https://')) {
                processedUrl = 'https://' + url;
            }
            
            // Validate URL format
            try {
                new URL(processedUrl);
            } catch (e) {
                showError('Please enter a valid URL (e.g., example.com)');
                return;
            }
            
            // Clear previous results and errors
            errorMessage.textContent = '';
            errorMessage.style.display = 'none';
            
            // Show loading indicator
            loadingIndicator.style.display = 'block';
            resultsContainer.style.display = 'none';
            
            // Call API
            fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    urls: [processedUrl],
                    bulkMode: false
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Server responded with ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                // Store results
                currentResults = data;
                activeUrl = processedUrl;
                
                // Display results
                displayResults();
                
                // Hide loading, show results
                loadingIndicator.style.display = 'none';
                resultsContainer.style.display = 'block';
                
                // Scroll to results
                resultsContainer.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            })
            .catch(error => {
                loadingIndicator.style.display = 'none';
                showError(`Error: ${error.message}`);
            });
        }
        
        /**
         * Display the results
         */
        function displayResults() {
            if (!currentResults || !activeUrl) return;
            
            const result = currentResults[activeUrl];
            
            if (result.status === 'error') {
                showError(result.error);
                return;
            }
            
            // Display LLMs.txt content
            llmsTextCode.textContent = result.llms_txt;
            
            // Display token analytics
            displayTokenAnalytics(result.llms_txt);
            
            // Display discovered URLs
            displayDiscoveredUrls(result.discovered_urls);
        }
        
        /**
         * Display token analytics
         */
        function displayTokenAnalytics(content) {
            const analyticsContainer = document.getElementById('token-analytics-content');
            // Implement token counting logic or make another API call
            // This is simplified - you might want to use a proper token counter
            const tokenCount = countTokens(content);
            
            analyticsContainer.innerHTML = `
                <div class="analytics-summary">
                    <div class="summary-item">
                        <div class="summary-label">Total Tokens</div>
                        <div class="summary-value">${tokenCount}</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Characters</div>
                        <div class="summary-value">${content.length}</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Words</div>
                        <div class="summary-value">${countWords(content)}</div>
                    </div>
                </div>
                <div class="token-explanation">
                    <p>Tokens are the basic units processed by AI models like GPT-3.5 and Claude.</p>
                    <p>A token can be as short as one character or as long as one word.</p>
                </div>
            `;
        }
        
        /**
         * Display discovered URLs
         */
        function displayDiscoveredUrls(urls) {
            const urlsContainer = document.getElementById('discovered-urls-content');
            
            if (!urls || urls.length === 0) {
                urlsContainer.innerHTML = '<p>No URLs discovered.</p>';
                return;
            }
            
            const urlList = urls.map(url => `<li>${url}</li>`).join('');
            urlsContainer.innerHTML = `<ul class="url-list">${urlList}</ul>`;
        }
        
        /**
         * Handle copy button click
         */
        function handleCopy() {
            if (!currentResults || !activeUrl) return;
            
            const content = currentResults[activeUrl].llms_txt;
            navigator.clipboard.writeText(content)
                .then(() => {
                    showNotice('Content copied to clipboard!');
                })
                .catch(err => {
                    showError('Failed to copy content. Please try again.');
                });
        }
        
        /**
         * Handle download button click
         */
        function handleDownload() {
            if (!currentResults || !activeUrl) return;
            
            const content = currentResults[activeUrl].llms_txt;
            const blob = new Blob([content], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            
            const a = document.createElement('a');
            a.href = url;
            a.download = 'LLMs.txt';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            
            showNotice('LLMs.txt downloaded successfully!');
        }
        
        /**
         * Handle edit button click
         */
        function handleEdit() {
            if (!currentResults || !activeUrl) return;
            
            editor.value = currentResults[activeUrl].llms_txt;
            document.getElementById('llms-txt-code').parentElement.style.display = 'none';
            editorContainer.style.display = 'block';
        }
        
        /**
         * Handle save button click
         */
        function handleSave() {
            if (!currentResults || !activeUrl) return;
            
            currentResults[activeUrl].llms_txt = editor.value;
            llmsTextCode.textContent = editor.value;
            
            editorContainer.style.display = 'none';
            document.getElementById('llms-txt-code').parentElement.style.display = 'block';
            
            showNotice('Changes saved successfully!');
        }
        
        /**
         * Handle cancel button click
         */
        function handleCancel() {
            editorContainer.style.display = 'none';
            document.getElementById('llms-txt-code').parentElement.style.display = 'block';
        }
        
        /**
         * Handle tab change
         */
        function handleTabChange(clickedTab) {
            const tab = clickedTab.getAttribute('data-tab');
            
            // Update active tab button
            tabButtons.forEach(btn => btn.classList.remove('active'));
            clickedTab.classList.add('active');
            
            // Update active tab content
            tabContents.forEach(content => content.classList.remove('active'));
            document.getElementById(`${tab}-content`).classList.add('active');
        }
        
        /**
         * Show error message
         */
        function showError(message) {
            errorMessage.textContent = message;
            errorMessage.style.display = 'block';
        }
        
        /**
         * Show notice
         */
        function showNotice(message) {
            // Create notice element
            const notice = document.createElement('div');
            notice.className = 'llms-notice';
            notice.textContent = message;
            
            // Add to document
            document.body.appendChild(notice);
            
            // Remove after delay
            setTimeout(() => {
                notice.classList.add('fadeout');
                setTimeout(() => {
                    document.body.removeChild(notice);
                }, 300);
            }, 3000);
        }
        
        /**
         * Count tokens (simplified approximation)
         */
        function countTokens(text) {
            // This is a very rough approximation
            // For a more accurate count, consider using a proper tokenizer
            return Math.ceil(text.length / 4);
        }
        
        /**
         * Count words
         */
        function countWords(text) {
            return text.split(/\s+/).filter(Boolean).length;
        }
    });
})(jQuery);