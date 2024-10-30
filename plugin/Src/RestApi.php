<?php

namespace Boostsite\AI_Plugin\Src;

use DateTime;
use WP_REST_Controller as WP_REST_Controller;

require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');

if (!defined('ABSPATH')) exit;

/**
 * Extending REST API framework of WordPress
 *
 * @author     Nirjhar Lo
 * @package    wp-plugin-framework
 */
if (!class_exists('RestApi')) {

	class RestApi extends WP_REST_Controller
	{

		private $products = [];

		public function __construct()
		{
			// add_action('init', array($this, 'get_woo_products'));
			header("Access-Control-Allow-Origin: *");
			$this->register_routes();
		}


		/**
		 * REST API routes
		 *
		 * @return Object
		 */
		public function register_routes()
		{

			$version = '1';
			$namespace = 'boostsite/v' . $version;

			//Available options for methods are CREATABLE, READABLE, EDITABLE, DELETABLE

			register_rest_route($namespace, '/plan_post', array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array($this, 'callbackPostOrder'),
				'permission_callback' => array($this, 'authenticate'),
				'args'                => array()
			));

			register_rest_route($namespace, '/add_content', array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array($this, 'callbackPutArticle'),
				'permission_callback' => array($this, 'authenticate'),
				'args'                => array()
			));

			register_rest_route($namespace, '/publish', array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array($this, 'callbackPublishArticle'),
				'permission_callback' => array($this, 'authenticate'),
				'args'                => array()
			));
		}


		public function callbackPublishArticle(\WP_REST_Request $request)
		{
			$params = json_decode($request->get_body(), true);

			if (!isset($params['post_id'])) {
				return new \WP_REST_Response(['error' => 'no_post_id'], 400);
			}
			try {
				//code...
				$id = wp_update_post([
					'ID' 		=> $params['post_id'],
					'post_status'  => 'publish',
					'post_date' => date("Y-m-d H:i:s", time())
				]);
			} catch (\Throwable $th) {
				var_dump($th);
				//throw $th;
			}

			return new \WP_REST_Response(['id' => $id], 200);
		}

		public function callbackPostOrder(\WP_REST_Request $request)
		{
			$params = json_decode($request->get_body(), true);

			if (!isset($params['post_title'])) {
				return new \WP_REST_Response(['error' => 'no_post_title'], 400);
			}

			$author = get_option('boostsite_post_author_selected');

			try {
				$id = wp_insert_post([
					'post_title'    => wp_strip_all_tags($params['post_title']),
					'post_content'  => $params['post_content'],
					'post_status'   => 'future',
					'post_author'   => $author,
					'post_date'		=> $params['post_publish_at']
				], true);

				update_post_meta($id, 'boostsite_planner_post', true);

				return new \WP_REST_Response(['id' => $id], 200);
			} catch (\Throwable $th) {
				var_dump($th);
				return new \WP_REST_Response(['error' => $th->getMessage()], 400);
			}
		}

		public function callbackPutArticle(\WP_REST_Request $request)
		{
			$params = json_decode($request->get_body(), true);
			$data = [];

			if (!isset($params['post_content'])) {
				return new \WP_REST_Response(['error' => 'no_post_content'], 400);
			}
			if (!isset($params['post_id'])) {
				return new \WP_REST_Response(['error' => 'no_post_id'], 400);
			}

			try {
				$ret = media_sideload_image($params['post_image'] . '&ext=.png', $params['post_id'], null, 'id');
				$ret1 = set_post_thumbnail($params['post_id'], $ret);
				update_post_meta($ret, '_wp_attachment_image_alt', $params['post_meta_title']);

				$post_category =  get_terms([
					'fields' => 'ids',
					'taxonomy' => 'category',
					'name' => $params['post_category'],
					'hide_empty' => false,
				]);
				wp_update_post([
					'ID' 		=> $params['post_id'],
					'post_content'  => $params['post_content'],
					'post_category'  => $post_category,
				]);

				if (in_array('wordpress-seo/wp-seo.php', apply_filters('active_plugins', get_option('active_plugins')))) {
					update_post_meta($params['post_id'], '_yoast_wpseo_metadesc', $params['post_meta_desc']);
					update_post_meta($params['post_id'], '_yoast_wpseo_title', $params['post_meta_title']);
				} elseif (in_array('all-in-one-seo-pack/all_in_one_seo_pack.php', apply_filters('active_plugins', get_option('active_plugins')))) {
					update_post_meta($params['post_id'], '_yoast_wpseo_metadesc', $params['post_meta_desc']);
					update_post_meta($params['post_id'], '_yoast_wpseo_title', $params['post_meta_title']);
				} else {
				}

				return new \WP_REST_Response($data, 200);
			} catch (\Throwable $th) {
				var_dump($th);
				return new \WP_REST_Response(['error' => $th->getMessage()], 400);
			}
		}



		public function authenticate($user)
		{
			return true;
			try {
				$this->perform_authentication();
			} catch (\Exception $e) {
				$user = new \WP_REST_Response($e->getMessage(), $e->getCode() ?? 401);
			}

			return $user;
		}

		private function is_consumer_secret_valid($password, $user_password)
		{
			return hash_equals($password, $user_password);
		}

		/**
		 * Prevent unauthorized access here
		 *
		 * @return Bool
		 */
		public function perform_authentication()
		{

			return true;
			if (!empty($_SERVER['PHP_AUTH_USER'])) {
				$auth_user = $_SERVER['PHP_AUTH_USER'];
			} else {
				throw new \Exception(__('Username is missing.', 'woocommerce'), 400);
			}

			if (!empty($_SERVER['PHP_AUTH_PW'])) {
				$user_password = $_SERVER['PHP_AUTH_PW'];
			} else {
				throw new \Exception(__('Password is missing.', 'woocommerce'), 400);
			}

			$username = get_option('boostsite_widget_api_user');
			$password = hash_hmac('sha256', get_option('boostsite_widget_api_pass'), 'boostsite-api');

			if ($username !== $auth_user) {
				throw new \Exception(__('Username is invalid.', 'woocommerce'), 401);
			}
			if (!$this->is_consumer_secret_valid($password, $user_password)) {
				throw new \Exception(__('Password is invalid.', 'woocommerce'), 401);
			}

			return true;
		}

		public function connectPermissionCallback()
		{
			return true;
		}

		public function permissionCallback()
		{
			return true;
		}
	}
}
