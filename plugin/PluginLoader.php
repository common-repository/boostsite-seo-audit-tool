<?php

namespace Boostsite\AI_Plugin;

use Boostsite\AI_Plugin\Lib\Boostsite;
use Boostsite\AI_Plugin\Src\Install as Install;
use Boostsite\AI_Plugin\Src\Db as Db;
use Boostsite\AI_Plugin\Src\Settings as Settings;
use Boostsite\AI_Plugin\Src\RestApi as RestApi;

use Boostsite\AI_Plugin\Lib\Script as Script;
use Boostsite\AI_Plugin\Src\Widget;

if (!defined('ABSPATH')) exit;

/**
 * Main plugin object to define the plugin
 * Follow: https://codex.wordpress.org/Plugin_API for details
 *
 * @author     Nirjhar Lo
 * @package    wp-plugin-framework
 */
if (!class_exists('PluginLoader')) {

	final class PluginLoader
	{

		/**
		 * @var String
		 */
		protected $version = '1.4.3';


		/**
		 * Plugin Instance.
		 *
		 * @var AI_PLUGIN_BUILD the PLUGIN Instance
		 */
		protected static $_instance;


		/**
		 * Text domain to be used throughout the plugin
		 *
		 * @var String
		 */
		protected static $text_domain = 'boostsite';


		/**
		 * Minimum PHP version allowed for the plugin
		 *
		 * @var String
		 */
		protected static $php_ver_allowed = '5.3';


		/**
		 * DB tabble used in plugin
		 *
		 * @var String
		 */
		protected static $plugin_table = 'wp_boostsite_plugin';


		/**
		 * Plugin listing page links, along with Deactivate
		 *
		 * @var Array
		 */
		protected static $plugin_page_links = array(
			array(
				'slug' => 'admin.php?page=boostsite-menu',
				'label' => 'Settings'
			)
		);


		/**
		 * Main Plugin Instance.
		 *
		 * @return AI_PLUGIN_BUILD
		 */
		public static function instance()
		{
			if (is_null(self::$_instance)) {
				self::$_instance = new self();
				self::$_instance->init();
			}

			return self::$_instance;
		}

		/**
		 * Install plugin setup
		 *
		 * @return Void
		 */
		public function installation()
		{

			if (class_exists('Boostsite\\AI_Plugin\\Src\\Install')) {

				$install = new Install();
				$install->text_domain = self::$text_domain;
				$install->php_ver_allowed = self::$php_ver_allowed;
				$install->plugin_page_links = self::$plugin_page_links;
				$install->execute();
			}

			flush_rewrite_rules();
		}


		/**
		 * Install plugin data
		 *
		 * @return Void
		 */
		// public function db_install()
		// {

		// 	if (class_exists('Boostsite\\AI_Plugin\\Src\\Db')) {

		// 		$db = new Db();
		// 		$db->table = self::$plugin_table;
		// 		$db->sql = "ID mediumint(9) NOT NULL AUTO_INCREMENT,
		// 					username char(64) NOT NULL,
		// 					password char(43) NOT NULL,
		// 					UNIQUE KEY ID (ID)";
		// 		$db->build();
		// 	}

		// 	if (get_option('_tpay_plugin_db_exist') == '0') {
		// 		add_action('admin_notices', array($this, 'db_error_msg'));
		// 	}

		// 	$options = array(
		// 		array('option_name', '__value__'),
		// 	);
		// 	foreach ($options as $value) {
		// 		update_option($value[0], $value[1]);
		// 	}
		// }


		/**
		 * Notice of DB
		 *
		 * @return Html
		 */
		public function db_error_msg()
		{ ?>

			<div class="notice notice-error is-dismissible">
				<p><?php _e('Database table Not installed correctly.', 'textdomain'); ?></p>
			</div>
<?php
		}


		/**
		 * Uninstall plugin data
		 *
		 * @return Void
		 */
		public function uninstall()
		{
			$options = array(
				'boostsite_widget_settings_field_user_email',
				'boostsite_post_author_selected',
				'boostsite_post_language_selected',
				'boostsite_widget_settings_field_user_id',
				'boostsite_widget_api_user',
				'boostsite_widget_api_pass',
				'boostsite_widget_settings_field_count_items',
				'boostsite_widget_settings_field_date',
				'boostsite_widget_settings_field_time',
				'boostsite_widget_settings_field_topic'
			);

			foreach ($options as $value) {
				delete_option($value);
			}
		}


		/**
		 * Include scripts
		 *
		 * @return Void
		 */
		public function scripts()
		{

			if (class_exists('Boostsite\\AI_Plugin\\Lib\\Script')) new Script();
		}


		/**
		 * Include settings pages
		 *
		 * @return Void
		 */
		public function settings()
		{

			if (class_exists('Boostsite\\AI_Plugin\\Src\\Settings')) new Settings();
		}


		// /**
		//  * Include widget classes
		//  *
		//  * @return Void
		//  */
		// public function widgets()
		// {

		// 	if (class_exists('Boostsite\\AI_Plugin\\Src\\Widget')) new Widget();
		// }


		/**
		 * Instantiate REST API
		 *
		 * @return Void
		 */
		public function rest_api()
		{
			if (class_exists('Boostsite\\AI_Plugin\\Src\\RestApi')) new RestApi();
		}


		/**
		 * Instantiate REST API
		 *
		 * @return Void
		 */
		public function prevent_unauthorized_rest_access($result)
		{
			// If a previous authentication check was applied,
			// pass that result along without modification.
			if (true === $result || is_wp_error($result)) {
				return $result;
			}

			// No authentication has been performed yet.
			// Return an error if user is not logged in.
			if (!is_user_logged_in()) {
				return new \WP_Error(
					'rest_not_logged_in',
					__('You are not currently logged in.'),
					array('status' => 401)
				);
			}

			return $result;
		}

		/**
		 * Instantiate the plugin
		 *
		 * @return Void
		 */
		public function init()
		{
			// register_activation_hook(AI_PLUGIN_FILE, array($this, 'create_user'));
			register_uninstall_hook(AI_PLUGIN_FILE, array('Boostsite\\AI_Plugin\\PluginLoader', 'uninstall'));
			add_action('init', array($this, 'installation'));

			$this->scripts();
			$this->settings();

			add_action('rest_api_init', array($this, 'rest_api'));
		}
	}
} ?>