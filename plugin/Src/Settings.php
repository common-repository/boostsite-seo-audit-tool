<?php

namespace Boostsite\AI_Plugin\Src;

use Boostsite\AI_Plugin\Lib\Boostsite;
use Boostsite\AI_Plugin\Lib\Table as Table;
use Boostsite\AI_Plugin\Src\Db as Db;
use Boostsite\AI_Plugin\Src\WooCommerceCategories as WooCategories;

if (!defined('ABSPATH')) exit;

/**
 * Backend settings page class, can have settings fields or data table
 *
 * @author     Nirjhar Lo
 * @package    wp-plugin-framework
 */
if (!class_exists('Settings')) {

	class Settings
	{


		/**
		 * @var String
		 */
		public $capability;


		/**
		 * @var Array
		 */
		public $menu_page;


		/**
		 * @var Array
		 */
		public $sub_menu_page;


		/**
		 * @var Array
		 */
		public $help;


		/**
		 * @var String
		 */
		public $screen;


		/**
		 * @var Object
		 */
		public $table;

		/**
		 * @var Object
		 */
		public $categories;

		public $step;

		public $account_type;


		/**
		 * Add basic actions for menu and settings
		 *
		 * @return Void
		 */
		public function __construct()
		{

			$this->capability = 'activate_plugins';
			$this->menu_page = array('name' => 'WP AutoPost AI', 'heading' => 'WP AutoPost AI', 'slug' => 'boostsite-menu',);
			$this->screen = '';

			// var_dump(get_option('boostsite_widget_api_user'));
			// var_dump(get_option('boostsite_widget_api_pass'));
			add_action('init', array($this, 'init_func'));
			add_action('admin_menu', array($this, 'menu_page'));
			add_action('admin_init', array($this, 'add_settings'));
			add_filter('set-screen-option', array($this, 'set_screen'), 10, 3);
			// $boostsiteAPI = new Boostsite();
			// $response = $boostsiteAPI->createUser('email@eamil.com');
			$error = get_option('error_exist');
			if (isset($error) && $error != '') {
				add_action('admin_notices', array($this, 'sample_admin_notice__error'));
			}

			add_action('added_option', function ($option_name, $value) {
				$this->generate_basic_auth($option_name, null, $value);
			}, 10, 3);
			add_action('updated_option', function ($option_name, $old_value, $value) {
				$this->generate_basic_auth($option_name, $old_value, $value);
			}, 10, 3);
		}

		function enqueue_facebook_javascript_sdk(): void
		{
			wp_enqueue_script('facebook-javascript-sdk-initializer', get_template_directory_uri() . '/path/to/your/initializer.js');
			wp_enqueue_script('facebook-javascript-sdk', 'https://connect.facebook.net/en_US/sdk.js', array(), null);
			return;
		}


		public function init_func()
		{
			if (isset($_GET['status']) && isset($_GET['email']) && $_GET['email'] != '') {
				update_option('boostsite_widget_settings_field_user_id', $_GET['user_id']);
				update_option('boostsite_widget_settings_field_user_email', $_GET['email']);
				echo '<script>'
					. 'window.opener.location.href = "' . get_site_url() . '/wp-admin/admin.php?page=boostsite-menu";'
					// . 'window.opener.location.href = "' . get_site_url() . '/wp-admin/admin.php?page=boostsite-menu&status=' . $_GET['status'] . '&email=' . $_GET['email'] . '&user_id=' . $_GET['user_id'] . '";'
					. 'setTimeout(() => {window.close()}, 100);'
					. '</script>';
			}
		}

		public function randomPassword()
		{
			$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' . '0123456789-=~!@#$%^&*()_+,./<>?;:[]{}\|';
			$pass = array();
			$alphaLength = strlen($alphabet) - 1;
			for ($i = 0; $i < 16; $i++) {
				$n = rand(0, $alphaLength);
				$pass[] = $alphabet[$n];
			}
			return implode($pass);
		}


		function sample_admin_notice__info()
		{
			$info = get_option('info_exist');
			$message = __($info, 'boostsite');
			echo '<div class="alert alert-warning" role="alert">
			' . $message . '
		  	</div>';
		}

		function sample_admin_notice__error()
		{
			$info = get_option('error_exist');
			$message = __($info, 'boostsite');
			echo '<div class="alert alert-error" role="alert">
			' . $message . '
		  	</div>';
			delete_option('error_exist');
		}

		function sanitize_option_boostsite_widget_settings_field_fb_page_id($value)
		{
			return intval($value);
		}

		function sanitize_option_boostsite_widget_settings_field_fb_page($value)
		{
			return wp_kses_data($value);
		}

		public function generate_basic_auth($option_name, $old_value, $new_value)
		{
			if (class_exists('Boostsite\\AI_Plugin\\Lib\\Boostsite')) {
				$boostsiteAPI = new Boostsite();
				$userMail = get_option('boostsite_widget_settings_field_user_email');
				$topic = get_option('boostsite_widget_settings_field_topic');
				$weekDays = get_option('boostsite_widget_settings_field_date');
				$time = get_option('boostsite_widget_settings_field_time');

				if ($option_name === 'boostsite_widget_settings_field_date') {
					$response = $boostsiteAPI->updateSettings($userMail, $topic, $weekDays, $time, $this->account_type);

					// $password = $this->randomPassword();
					// update_option('boostsite_widget_api_user', $userMail);
					// update_option('boostsite_widget_api_pass', $password);
				}
			}
		}
		/**
		 * Add a sample main menu page callback
		 *
		 * @return Void
		 */
		public function menu_page()
		{
			if ($this->menu_page) {
				add_menu_page(
					$this->menu_page['name'],
					$this->menu_page['heading'],
					$this->capability,
					$this->menu_page['slug'],
					array($this, 'menu_page_callback'),
					plugin_dir_url(__FILE__) . '../../asset/boostsite-icon.png'
				);
			}
		}


		/**
		 * Add a sample Submenu page callback
		 *
		 * @return Void
		 */
		public function sub_menu_page()
		{

			if ($this->sub_menu_page) {
				foreach ($this->sub_menu_page as $page) {

					$hook = add_submenu_page(
						$page['parent_slug'],
						$page['name'],
						$page['heading'],
						$this->capability,
						$page['slug'],
						array($this, 'menu_page_callback')
					);
					if ($page['help']) {
						add_action('load-' . $hook, array($this, 'help_tabs'));
					}
					if ($page['screen']) {
						add_action('load-' . $hook, array($this, 'screen_option'));
					}
				}
			}
		}


		/**
		 * Set screen
		 *
		 * @param String $status
		 * @param String $option
		 * @param String $value
		 *
		 * @return String
		 */
		public function set_screen($status, $option, $value)
		{

			$user = get_current_user_id();

			switch ($option) {
				case 'option_name_per_page':
					update_user_meta($user, 'option_name_per_page', $value);
					$output = $value;
					break;
			}

			if ($output) return $output; // Related to PLUGIN_TABLE()
		}


		/**
		 * Set screen option for Items table
		 *
		 * @return Void
		 */
		public function screen_option()
		{

			$option = 'per_page';
			$args   = array(
				'label'   => __('Show per page', 'boostsite'),
				'default' => 10,
				'option'  => 'option_name_per_page' // Related to PLUGIN_TABLE()
			);
			add_screen_option($option, $args);
			// $this->table = new Table(); // Source /lib/table.php
		}


		/**
		 * Menu page callback
		 *
		 * @return Html
		 */
		public function menu_page_callback()
		{

			$userID = get_option('boostsite_widget_settings_field_user_id');
			if ($userID && $userID != false && $userID != '') {
				$boostsiteAPI = new Boostsite();
				$this->account_type = $boostsiteAPI->getUser();
			}
?>

			<div class="wrap">
				<h1><?php echo get_admin_page_title(); ?></h1>
				<br class="clear">
				<?php
				settings_errors();
				/**
				 * Following is the settings form
				 */ ?>
				<form method="post" action="options.php">
					<?php
					settings_fields("settings_form");
					do_settings_sections("settings_name");
					if ($this->step['index'] === 4) {
						submit_button(__('Generate and Schedule', 'boostsite'), 'primary', 'id', null, ["class" => 'btn btn-primary']);
					} elseif ($this->step['index'] === 5) {
						submit_button(__('Schedule', 'boostsite'), 'primary', 'id', null, ["class" => 'btn btn-primary']);
					} else {
						submit_button(__('Next', 'boostsite'), 'primary', 'id', null, ["class" => 'btn btn-primary']);
					}
					?>
				</form>


				<?php
				/**
				 * Following is the data table class
				 */ ?>

				<br class="clear">
			</div>
<?php
		}


		/**
		 * Add help tabs using help data
		 *
		 * @return Void
		 */
		public function help_tabs()
		{

			foreach ($this->helpData as $value) {
				if ($_GET['page'] == $value['slug']) {
					$this->screen = get_current_screen();
					foreach ($value['info'] as $key) {
						$this->screen->add_help_tab($key);
					}
					$this->screen->set_help_sidebar($value['link']);
				}
			}
		}

		public function getStep($apiUser, $post_author, $days, $desc)
		{

			if (!$apiUser || $apiUser === '') {
				return [
					'index' => 1,
					'title' => __('Account', 'boostsite'),
					'desc' => __('First step is to create simple account.<br><br>1. Click <b>"Sign up to use plugin"</b><br>2. Login or register to our application.<br>3. After window with login will close, click <b>"Next"</b>', 'boostsite')
				];
			}

			if (($apiUser && $apiUser !== '') && (!$desc || $desc === '')) {
				return [
					'index' => 2,
					'title' => __('About your site', 'boostsite'),
					'desc' => __('Write some information about your site.<br><small><i>It can even be one word that describes the topic of your page, e.g. "psychology"</i></small></li>', 'boostsite')
				];
			}

			if (($apiUser && $apiUser !== '') && (!$post_author || $post_author === '') && ($desc || $desc !== '')) {
				return [
					'index' => 3,
					'title' => __('Language & author', 'boostsite'),
					'desc' => __('Set language of posts that will be published and author that will publish that posts', 'boostsite')
				];
			}

			if (($apiUser && $apiUser !== '') && ($post_author || $post_author !== '') && ($desc || $desc !== '') && (!$days || $days === '')) {
				return [
					'index' => 4,
					'title' => __('Set posts schedule', 'boostsite'),
					'desc' => __('Choose the day and time for the article to show up.<br><small><i> Our system will quickly show you the post\'s title. Next, it will create content for it and make it go live at the time you picked. Before it goes live, you can find the post under the "Posts" tab marked as "Scheduled". So, you can take a look at it or even make changes before it\'s out for everyone to see.</i></small>', 'boostsite')
				];
			}

			return [
				'index' => 5,
				'title' => __('', 'boostsite'),
				'desc' => __('Change options in planned blog posts.<br><br>Once all settings are set up, new posts will be published according schedule. You cannot change schedule of already generated blog posts, to change them just edit post in Wordpress and change schedule there or publish.<br><br><ol>
				<li>Choose the day and time for the article to show up.<br><small><i> Our system will quickly show you the post\'s title. Next, it will create content for it and make it go live at the time you picked. Before it goes live, you can find the post under the "Posts" tab marked as "Scheduled". So, you can take a look at it or even make changes before it\'s out for everyone to see.</i></small></li>
				<li>Set language of posts that will be published</li>
				<li>Set author that will publish that posts</li>
				<li>Write some information about your site.<br><small><i>It can even be one word that describes the topic of your page, e.g. "psychology"</i></small></li>
				</ol>', 'boostsite')
			];
		}


		/**
		 * Add different types of settings and corrosponding sections
		 *
		 * @return Void
		 */
		public function add_settings()
		{
			if (false == get_option('settings_form')) {
				add_option('settings_form');
			}
			if (false == get_option('settings_api')) {
				add_option('settings_api');
			}

			/* 
				Settings INFO
			*/
			$apiUser = get_option('boostsite_widget_settings_field_user_email');
			$post_author = get_option('boostsite_post_author_selected');
			$days = get_option('boostsite_widget_settings_field_date');
			$desc = get_option('boostsite_widget_settings_field_topic');

			$this->step = $this->getStep($apiUser, $post_author, $days, $desc);


			$step = $this->step;

			if ($this->step['index'] !== 5) {
				add_settings_section('steps', '', array($this, 'section_steps_cb'), 'settings_name');
			}

			if ($this->step['index'] === 5) {
				add_settings_section('tabs', '', array($this, 'section_tabs_cb'), 'settings_name');

				add_settings_section('settings_info', __('', 'boostsite'), array($this, 'section_info_cb'), 'settings_name');
			}

			add_settings_section('settings_form', $this->step['title'], function () use ($step) {
				if ($step['index'] === 5) {
					echo '<div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">';
				}
				echo '<div class="alert alert-light" role="alert">' . __($this->step['desc']) . '</div>';
			}, 'settings_name');

			if ($this->step['index'] === 1 || $this->step['index'] === 5) {

				register_setting('settings_form', 'boostsite_widget_settings_field_user_email');

				add_settings_field(
					'boostsite_widget_settings_field_user_email',
					__('Your email address', 'boostsite'),
					array($this, 'settings_input_user_email_field_cb'),
					'settings_name',
					'settings_form'
				);
			}
			if ($this->step['index'] == 2 || $this->step['index'] == 5) {

				register_setting('settings_form', 'boostsite_widget_settings_field_topic');
				add_settings_field(
					'boostsite_widget_settings_field_topic',
					__('Description', 'boostsite'),
					array($this, 'settings_input_field_description_topic'),
					'settings_name',
					'settings_form'
				);
			}

			if ($this->step['index'] == 3 || $this->step['index'] == 5) {
				register_setting('settings_form', 'boostsite_post_author_selected');
				add_settings_field(
					'boostsite_post_author_selected',
					__('Author', 'boostsite'),
					array($this, 'settings_input_author_field_cb'),
					'settings_name',
					'settings_form'
				);

				register_setting('settings_form', 'boostsite_post_language_selected');
				add_settings_field(
					'boostsite_post_language_selected',
					__('Language', 'boostsite'),
					array($this, 'settings_input_lang_field_cb'),
					'settings_name',
					'settings_form'
				);
			}



			if ($this->step['index'] == 4 || $this->step['index'] == 5) {

				register_setting('settings_form', 'boostsite_widget_settings_field_count_items');
				add_settings_field(
					'boostsite_widget_settings_field_count_items',
					__('Amount of publications', 'boostsite'),
					array($this, 'settings_input_count_items_field_cb'),
					'settings_name',
					'settings_form'
				);

				register_setting('settings_form', 'boostsite_widget_settings_field_time');
				add_settings_field(
					'boostsite_widget_settings_field_time',
					__('Time of publication', 'boostsite'),
					array($this, 'settings_input_show_items_field_cb'),
					'settings_name',
					'settings_form'
				);

				register_setting('settings_form', 'boostsite_widget_settings_field_date');
				add_settings_field(
					'boostsite_widget_settings_field_date',
					__('How often', 'boostsite'),
					array($this, 'settings_input_date_field_cb'),
					'settings_name',
					'settings_form'
				);
			}
			// }
		}

		public function section_tabs_cb()
		{
			echo '
			<ul class="nav nav-tabs mb-3" id="pills-tab" role="tablist">
			<li class="nav-item" role="presentation">
				<button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home" aria-selected="true">Planned posts</button>
			</li>
			<li class="nav-item" role="presentation">
				<button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile" aria-selected="false">Settings</button>
			</li>
			</ul>';
		}

		public function section_steps_cb()
		{

			echo '
			<div class="container">
			<h2 class="text-center">Configuration steps</h2>
			<div class="progress-wrap">
			<div class="progress" id="progress"></div>
			<div class="step ' . ($this->step['index'] == 1 ? 'active' : '') . '">1</div>
			<div class="step ' . ($this->step['index'] == 2 ? 'active' : '') . '">2</div>
			<div class="step ' . ($this->step['index'] == 3 ? 'active' : '') . '">3</div>
			<div class="step ' . ($this->step['index'] == 4 ? 'active' : '') . '">4</div>
		  </div></div>';
		}

		public function section_info_cb()
		{
			echo '<div class="tab-content" id="pills-tabContent">';
			echo '<div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">';
			echo '
			<div class="alert alert-light" role="alert">
				<p style="font-size: 16px">
					Here you can see all posts planned to be publish. Posts can have 2 states:
					<br><br>
					<span class="badge bg-success text-white">Ready to published</span> - blog post is generated and waiting to be published by our automatic system.<br>
					<span class="badge bg-warning text-black">Ready by [date]</span> - blog post content is scheduled to generate. Content of blog posts will be generated 2 days before it\'s publication.<br>
					<span class="badge bg-primary text-white">Generating</span> - blog post content is generating. Content will be available in about 1h<br>
				</p>
			</div>
			';

			$info = get_option('info_exist');
			$error = get_option('error_exist');
			$counter = get_option('post_to_generate_count');



			if (isset($error) && $error != '') {
				$this->sample_admin_notice__error();
			}

			echo '<div class="table-container">';
			if (isset($info) && $info != '' && $this->account_type == 'SEO_PLUGIN_PLAN') {
				$this->sample_admin_notice__info();
			}
			$posts = get_posts([
				'post_status' => 'future',
				'numberposts' => 35,
				'orderby' => 'date',
				'order' => 'ASC',
			]);

			if ($posts) {

				echo '<table class="table table_planned_posts table-striped">';
				echo '<thead><tr>
						<th>Title</th>
					<th>Publication date</th>
					<th>Content status</th>
				<tr></thead>';
				echo '<tbody>';
				foreach ($posts as $key => $post) {
					$post_date = new \DateTime($post->post_date, new \DateTimeZone('Europe/Warsaw'));
					$dateToGenerate = strtotime('-2 day', $post_date->getTimestamp());
					$message = '';
					$now = new \DateTime("now", new \DateTimeZone('Europe/Warsaw'));

					$metadata = get_post_meta($post->ID, 'boostsite_planner_post');

					$span = '';
					if ($metadata[0] == '1') {
						$span = '<span style="margin-left: 10px" class="badge bg-warning text-white">AI</span>';
					}
					if (strlen($post->post_content) > 0) {
						$class = 'badge bg-success text-white';
					}
					if (strlen($post->post_content) === 0) {

						if ($now->getTimestamp() > $dateToGenerate) {
							$class = 'badge bg-primary text-white';
						} else {
							$class = 'badge bg-warning text-black';
						}
					}
					echo '<tr class="">';
					if (strlen($post->post_content) > 0) {
						echo '<td><a href="' . get_edit_post_link($post) . '">' . $post->post_title . '</a>' . $span . '</td>';
					} else {
						echo '<td>' . $post->post_title . $span . '</td>';
					}

					if (strlen($post->post_content) > 0) {
						$message = 'Ready to be published';
					} else {
						if ($now->getTimestamp() > $dateToGenerate) {
							$message = 'Generating';
						} else {
							$generateDate = new \DateTime('now', new \DateTimeZone('Europe/Warsaw'));
							$generateDate->setTimestamp($dateToGenerate);
							$message = 'Ready by ' . $generateDate->format('d F Y H:i');
						}
					}
					echo '<td>' . date('d F Y H:i', strtotime($post->post_date)) . '</td>';
					echo '<td><span class="' . $class . '">' . $message . '</span></td>';
					echo '</tr>';
				}

				if (isset($counter) && $counter >= 0 && $counter != false && $this->account_type == 'SEO_PLUGIN_PLAN') {
					echo '<tr>';
					echo '<td colspan=3 style="text-align: center; padding: 30px 0;">';

					echo '<p style="color: #999;">More articles will be loaded in few seconds...</p>';
					echo '<div class="spinner-border text-success" role="status">
						<span class="visually-hidden">Loading...</span>
						</div>';
					echo '</td>';
					echo '</tr>';

					echo '
							<script>
							setTimeout(() => {
								window.ajaxLoad();
							}, 20000)
							</script>
						';
				}
				delete_option('info_exist');
				delete_option('post_to_generate_count');
				echo '</tbody>';
				echo '</table>';
				echo '<br>';
				echo '<br>';
				echo '<br>';
			} else {
				echo '<div class="alert alert-light" role="alert">
					You have no blog posts planned yet. 
				</div>';
				if (isset($counter) && $counter >= 0 && $counter != false && $this->account_type == 'SEO_PLUGIN_PLAN') {
					echo '<div class="text-center">';
					echo '<p style="color: #999;">More articles will be loaded in few seconds...</p>';
					echo '<div class="spinner-border text-success" role="status">
						<span class="visually-hidden">Loading...</span>
						</div>';
					echo '
							<script>
							setTimeout(() => {
								window.ajaxLoad();
							}, 45000)
							</script>
						';
					echo '</div>';
				}
				delete_option('info_exist');
				delete_option('post_to_generate_count');
				echo '<br>';
				echo '<br>';
				echo '<br>';
			}
			echo '</div>';
			echo '</div>';
			echo '</div>';
		}

		public function settings_input_user_email_field_cb()
		{
			echo '<script>window.openWindow = function (url, width, height) {
				var leftPosition, topPosition;
				leftPosition = (window.screen.width / 2) - ((width / 2) + 10);
				topPosition = (window.screen.height / 2) - ((height / 2) + 50);
				window.open(url, "Auth0",
					"status=no,height=" + height + ",width=" + width + ",resizable=no,left=" +
					leftPosition + ",top=" + topPosition + ",screenX=" + leftPosition + ",screenY=" +
					topPosition + ",toolbar=no,menubar=no,scrollbars=no,location=no,directories=no");
			}</script>';
			if ($this->step['index'] === 5) {
				echo '<div class="form-inline">
					<input type="email" readonly class="form-control medium-text" name="boostsite_widget_settings_field_user_email" id="boostsite_widget_settings_field_user_email" value="' . esc_html(get_option('boostsite_widget_settings_field_user_email')) . '" placeholder="' . esc_html__('', 'boostsite') . '"  />
					<a class="link link-primary" role="button" onclick="openWindow(\'https://boostsite.com/wp?url=' . urlencode(get_site_url()) . '&type=logout&screen_hint=signup\',375, 765)">Sign with another account</a>
				</div>';
			} else {

				echo '<a class="btn btn-primary" role="button" onclick="openWindow(\'https://boostsite.com/wp?url=' . urlencode(get_site_url()) . '&screen_hint=signup\',375, 765)">Sign up to use plugin</button></a>';
				echo '<br><br><span><i>Add your email address. We will use it to create account for best plugin results. </i></span>';
			}
		}

		public function settings_input_author_field_cb()
		{
			$options = '';
			foreach (get_users([
				'capability__in' => ['editor', 'author', 'administrator']
			]) as $key => $value) {
				$options = $options . '<option value="' . esc_html($value->ID) . '" ' . selected(esc_html($value->ID), esc_html(get_option('boostsite_post_author_selected')), false) . '>' . esc_html($value->user_login) . '</option>';
			}
			echo '<select class="form-select" name="boostsite_post_author_selected" id="boostsite_post_author_selected">
			' . $options . '	
			</select>';
			echo '<p style="padding-top:2px"><i>Select author that will publish this articles. You will only see user with publish capabilities.</i></p>';
		}

		public function settings_input_lang_field_cb()
		{
			$options = '';

			foreach ([
				'en_US' => 'English',
				'pl_PL' => 'Polish',
			] as $key => $value) {
				$options = $options . '<option value="' . esc_html($key) . '" ' . selected(esc_html($key), get_option('boostsite_post_language_selected') !== false ? esc_html(get_option('boostsite_post_language_selected')) : get_locale(), false) . '>' . esc_html($value) . '</option>';
			}
			echo '<select class="form-select" name="boostsite_post_language_selected" id="boostsite_post_language_selected">
			' . $options . '	
			</select>';
			echo '<p style="padding-top:2px"><i>Select language that posts will be published. We set language from your settings but You can change it to any of available.</i></p>';
		}

		public function settings_input_count_items_field_cb()
		{
			$value = get_option('boostsite_widget_settings_field_count_items') == false ? 1 : esc_html(get_option('boostsite_widget_settings_field_count_items'));
			$disabled = 'disabled';
			if ($this->account_type == 'SEO_PLUGIN_PLAN') {
				$disabled = '';
			}
			echo '<div>
				<input class="form" type="number" ' . $disabled . ' required class="medium-text" max="5" min="1" name="boostsite_widget_settings_field_count_items" id="boostsite_widget_settings_field_count_items" value="' . $value . '" />
			</div>';
			echo '<p style="padding-top:2px"><i>How many articles should be planned daily.</i></p>';
			if ($this->account_type != 'SEO_PLUGIN_PLAN') {
				echo '<br><div class="alert alert-warning" role="alert">
			On <b>FREE</b> version you can only select 1 article that will be publish. <a target="_blank" href="https://app.boostsite.com/plugin/order-checkout/seo_plugin_plan">Upgrade your plan</a> to choose up to 5 articles that will be generated single day.
		  </div>';
			}
		}

		public function settings_input_date_field_cb()
		{
			$days = get_option('boostsite_widget_settings_field_date');
			if ($days === false or $days == '') {
				$days = ['Monday'];
			}
			$week = array('Monday', 'Tuesday', 'Wendnesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');

			echo '<div class="btn-group checkbox-weekday" role="group" aria-label="Basic checkbox toggle button group">';
			foreach ($week as $key => $dayName) {
				$checked = '';
				if (in_array($dayName, $days)) {
					$checked = ' checked="checked" ';
				}
				if ($this->account_type != 'SEO_PLUGIN_PLAN') {
					echo '
						<input type="radio" ' . $checked . ' class="btn-check" name="boostsite_widget_settings_field_date[]" value="' . $dayName . '" id="btncheck-' . $key . '" autocomplete="off">
					';
				} else {
					echo '
						<input type="checkbox" ' . $checked . ' class="btn-check" name="boostsite_widget_settings_field_date[]" value="' . $dayName . '" id="btncheck-' . $key . '" autocomplete="off">
					';
				}
				echo '
					<label class="btn btn-outline-primary" for="btncheck-' . $key . '">' . $dayName . '</label>
				';
			}
			echo '</div>';
			if ($this->account_type != 'SEO_PLUGIN_PLAN') {
				echo '<p style="padding-top:2px"><i>Select day that article will be published.</i></p>';
			} else {
				echo '<p style="padding-top:2px"><i>Select days that article will be published. <br>According how many days will be selected, that many articles will be planned</i></p>';
			}
			if ($this->account_type != 'SEO_PLUGIN_PLAN') {
				echo '<br><div class="alert alert-warning" role="alert">
			On <b>FREE</b> version you can only select 1 day that article will be publish. <a target="_blank" href="https://app.boostsite.com/plugin/order-checkout/seo_plugin_plan">Upgrade your plan</a> to select multiple days in a week. Also You will be able to set up to 5 post for a day.
		  </div>';
			}
		}

		public function settings_input_show_items_field_cb()
		{
			echo '<div>
				<input type="time" class="form-control" name="boostsite_widget_settings_field_time" value="' . get_option('boostsite_widget_settings_field_time') . '" /></div>
			';
			echo '<p style="padding-top:2px"><i>Select time that articles will be published on selected days.</i></p>';
		}

		public function settings_input_field_description_topic()
		{
			echo '
			<div class="form-floating">
				<textarea class="form-control" placeholder="Describe your page in few words" name="boostsite_widget_settings_field_topic" id="boostsite_widget_settings_field_topic" style="height: 100px">' . esc_html(get_option('boostsite_widget_settings_field_topic')) . '</textarea>
  				<label for="boostsite_widget_settings_field_topic">Page description</label>
			</div>';
			echo '<p style="padding-top:2px"><i>Describe your page in few words. Write in keywords what is main topic of your site.</i></p>';
			echo '</div>';
		}
	}
} ?>