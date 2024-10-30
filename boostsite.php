<?php
/*
	Plugin Name: 	WP AutoPost AI
	Plugin URI: 	https://boostsite.org/
	Description: 	Quick and fast setup for automated, SEO optimized blog posts
	Version: 		2.3
	Author: 		Boostsite
	Author URI: 	https://Boostsitebots.com/
	License: 		GPLv2 or later
	License URI: 	https://www.gnu.org/licenses/gpl-2.0.html
	Text Domain: 	boostsite
	Domain Path: 	/languages
*/


if (!defined('ABSPATH')) exit;

//Define basic names
//Edit the "_PLUGIN" in following namespaces for compatibility with your desired name.
defined('AI_PLUGIN_DEBUG') or define('AI_PLUGIN_DEBUG', true);

defined('AI_PLUGIN_PATH') or define('AI_PLUGIN_PATH', plugin_dir_path(__FILE__));
defined('AI_PLUGIN_FILE') or define('AI_PLUGIN_FILE', plugin_basename(__FILE__));

defined('AI_PLUGIN_TRANSLATE') or define('AI_PLUGIN_TRANSLATE', dirname(plugin_basename(__FILE__)) . '/languages/');

defined('AI_PLUGIN_JS') or define('AI_PLUGIN_JS', plugins_url('/asset/js/', __FILE__));
defined('AI_PLUGIN_CSS') or define('AI_PLUGIN_CSS', plugins_url('/asset/css/', __FILE__));
defined('AI_PLUGIN_IMAGE') or define('AI_PLUGIN_IMAGE', plugins_url('/asset/img/', __FILE__));
// 
// define('BOOSTSITE_API_HOST', 'https://42a2-93-175-96-175.ngrok-free.app');
// define('BOOSTSITE_API_HOST', 'https://api.develop-h2so4.boostsite.com');
define('BOOSTSITE_API_HOST', 'https://api.boostsite.com');

//The Plugin
require_once('vendor/autoload.php');
function boostsite_plugin()
{
	if (class_exists('Boostsite\\AI_Plugin\\PluginLoader')) {
		// delete_option('boostsite_widget_settings_field_user_email');
		// delete_option('boostsite_post_author_selected');
		// delete_option('boostsite_post_language_selected');
		// delete_option('boostsite_widget_settings_field_user_id');
		// delete_option('boostsite_widget_api_user');
		// delete_option('boostsite_widget_api_pass');
		// delete_option('boostsite_widget_settings_field_count_items');
		// delete_option('boostsite_widget_settings_field_date');
		// delete_option('boostsite_widget_settings_field_time');
		// delete_option('boostsite_widget_settings_field_topic');
		return Boostsite\AI_Plugin\PluginLoader::instance();
	}
}

global $plugin;
$plugin = boostsite_plugin();
