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
        if (is_a($post, 'WP_Post') && (has_shortcode($post->post_content, 'llms_generator') || 
            (function_exists('has_block') && has_block('shortcode', $post->post_content)))) {
            
            // Enqueue CSS
            wp_enqueue_style(
                'llms-generator-styles',
                plugins_url('assets/css/llms-generator.css', dirname(__FILE__)),
                array(),
                LLMS_GENERATOR_VERSION
            );
            
            // Enqueue JavaScript
            wp_enqueue_script(
                'llms-generator-script',
                plugins_url('assets/js/llms-generator.js', dirname(__FILE__)),
                array('jquery'),
                LLMS_GENERATOR_VERSION,
                true
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
                'title' => 'Generate LLMs.txt Files',
                'description' => 'Enter your website URL to generate an AI-friendly LLMs.txt file.'
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