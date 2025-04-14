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
define('LLMS_GENERATOR_API_URL', 'https://llmstxt-next.vercel.app/api/generate');

// Include required files
require_once LLMS_GENERATOR_PLUGIN_DIR . 'includes/class-llms-txt-generator.php';

// Initialize the plugin
function llms_txt_generator_init() {
    $plugin = new LLMS_Txt_Generator();
    $plugin->init();
}
add_action('plugins_loaded', 'llms_txt_generator_init');