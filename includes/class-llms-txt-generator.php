<?php
/**
 * Main plugin class
 */
class LLMS_Txt_Generator {

    /**
     * Initialize the plugin
     */
    public function init() {
        // Register shortcode
        add_shortcode('llms_generator', array($this, 'render_generator_shortcode'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_assets() {
        // Only load on pages/posts that have our shortcode
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'llms_generator')) {
            // Enqueue CSS
            wp_enqueue_style(
                'llms-generator-styles',
                LLMS_GENERATOR_PLUGIN_URL . 'assets/css/llms-generator.css',
                array(),
                LLMS_GENERATOR_VERSION
            );
            
            // Enqueue JavaScript
            wp_enqueue_script(
                'llms-generator-script',
                LLMS_GENERATOR_PLUGIN_URL . 'assets/js/llms-generator.js',
                array('jquery'),
                LLMS_GENERATOR_VERSION,
                true
            );
            
            // Pass variables to JavaScript
            wp_localize_script(
                'llms-generator-script',
                'llmsGeneratorVars',
                array(
                    'ajaxUrl' => admin_url('admin-ajax.php'),
                    'apiUrl' => LLMS_GENERATOR_API_URL,
                    'nonce' => wp_create_nonce('llms_generator_nonce')
                )
            );
        }
    }

    /**
     * Render the generator shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string HTML content
     */
    public function render_generator_shortcode($atts) {
        // Process shortcode attributes
        $atts = shortcode_atts(
            array(
                'title' => 'Generate Optimized LLMs.txt & Markdown Files for AI Search Engines
',
                'description' => 'Input a Website URL to Automatically Generate AI-Ready LLMs.txt and Markdown (.md) Files with Relevant Site Content for Enhanced SEO Performance.

'
            ),
            $atts,
            'llms_generator'
        );
        
        // Start output buffering
        ob_start();
        
        // Include template
        include_once LLMS_GENERATOR_PLUGIN_DIR . 'templates/generator-template.php';
        
        // Return the buffered content
        return ob_get_clean();
    }
}