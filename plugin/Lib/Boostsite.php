<?php

namespace Boostsite\AI_Plugin\Lib;

if (!class_exists('Boostsite')) {

	class Boostsite
	{


		/**
		 * @var String
		 */
		public $endpoint;


		/**
		 * @var Array
		 */
		public $header;


		/**
		 * @var String
		 */
		public $data_type;


		/**
		 * @var String
		 */
		public $call_type;

		/**
		 * @var String
		 */
		public $payload;

		/**
		 * @var String
		 */
		public $api_host;


		/**
		 * Define the properties inside a instance
		 *
		 * @return Void
		 */
		public function __construct()
		{
			$this->api_host = BOOSTSITE_API_HOST;
			$this->endpoint = '';
			$this->header = array();
			$this->data_type = 'json';
			$this->call_type = '';
			$this->payload = [];
		}


		/**
		 * Define the variables for db table creation
		 *
		 * @return Array
		 */
		public function call()
		{
			$args = array(
				'headers' => array_merge([
					'Content-Type' => 'application/json',
					// 'Authorization' => $this->header['Authorization'],
				]),
				'body'      => json_encode($this->payload),
				'method'    => $this->call_type
			);

			$result =  wp_remote_request($this->endpoint, $args);
			return json_decode(wp_remote_retrieve_body($result), true);
		}

		// public function getTokenAuth($force = false)
		// {
		// 	$token = get_option('boostsite_widget_api_token');
		// 	if ($token === false || $token === '' || $force === true) {
		// 		$this->endpoint = $this->api_host . '/oauth/token';
		// 		$this->payload = array(
		// 			'grant_type' => 'client_credentials',
		// 			'client_id' => get_option('boostsite_widget_user_client_id'),
		// 			'client_secret' => get_option('boostsite_widget_user_client_secret'),
		// 			'scope' => implode(' ', Boostsite_API_SCOPES)
		// 		);
		// 		$this->call_type = 'POST';
		// 		$data = $this->call();
		// 		if (isset($data['meta']) && isset($data['meta']['status']) && $data['meta']['status'] !== 200) {
		// 			error_log('Boostsite API ERROR (generate token): ' . print_r(json_encode($data), true));
		// 			return ['error' => true, 'response' => $data['response']];
		// 		}
		// 		$token = $data['response']['access_token'];
		// 		update_option('boostsite_widget_api_token', $token);
		// 	};
		// 	return $token;
		// }

		public function getUser()
		{
			$this->endpoint = $this->api_host . '/external-plugin/get_user';

			$this->call_type = 'POST';
			$this->payload = [
				'user_id' => get_option('boostsite_widget_settings_field_user_id'),
			];
			$dataResponse = $this->call();
			if (!isset($dataResponse['account_type'])) {
				return null;
			}
			return $dataResponse['account_type'];
		}

		public function updateSettings($email, $topic, $weekDays, $time, $accountType)
		{
			$this->endpoint = $this->api_host . '/external-plugin/update';
			// $this->header = [
			// 	'Authorization' => 'Bearer ' . Boostsite_API_TOKEN
			// ];
			$posts = get_posts([
				'post_status' => ['future', 'published', 'draft'],
				'numberposts' => 50
			]);

			$categoriesData = get_categories([
				'hide_empty' => 0
			]);

			$data = [];
			$categories = [];
			foreach ($posts as $key => $post) {
				array_push($data, $post->post_title);
			}
			foreach ($categoriesData as $key => $cat) {
				array_push($categories, $cat->name);
			}

			$this->call_type = 'PUT';
			$this->payload = [
				'user_id' => get_option('boostsite_widget_settings_field_user_id'),
				'email' => $email,
				'titles' => $data,
				"topic" => $topic,
				"categories" => $categories,
				"week_days" => $weekDays,
				'time' => $time,
				'lang' => get_option('boostsite_post_language_selected'),
				'count' => get_option('boostsite_widget_settings_field_count_items') == '' ? 1 :  get_option('boostsite_widget_settings_field_count_items'),
				"config" => [
					"shop_url" => get_rest_url(),
					"api_auth" => [
						"username" => get_option('boostsite_widget_api_user'),
						"password" => get_option('boostsite_widget_api_pass')
					]
				],
			];
			$dataResponse = $this->call();
			if ($dataResponse && isset($dataResponse['statusCode']) && $dataResponse['statusCode'] == 400) {
				update_option('error_exist', $dataResponse['message']);
			}
			if ($accountType === 'SEO_PLUGIN_PLAN') {
				update_option('info_exist', 'Below you will see the first part of scheduled posts. All scheduled articles will appear here in the next 5 minutes. Come back in a moment and REFRESH this page to see all articles.');
			}
			if (isset($dataResponse) && isset($dataResponse['counter'])) {
				update_option('post_to_generate_count', $dataResponse['counter']);
			}
			if ($dataResponse == NULL) {
				update_option('post_to_generate_count', 1);
			}
			return $dataResponse;
		}
	}
}
