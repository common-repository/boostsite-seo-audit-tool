<?php

namespace Boostsite\AI_Plugin\Lib;

/**
 * Add scripts to the plugin. CSS and JS.
 *
 * @author     Nirjhar Lo
 * @package    wp-plugin-framework
 */
if (!defined('ABSPATH')) exit;

if (!class_exists('Script')) {

	final class Script
	{


		/**
		 * Add scripts to front/back end heads
		 *
		 * @return Void
		 */
		public function __construct()
		{

			add_action('admin_head', array($this, 'data_table_css'));
			add_action('admin_enqueue_scripts', array($this, 'backend_scripts'));
			add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));

			add_action("wp_ajax_my_user_vote", array($this, "my_user_vote"));
		}


		/**
		 * Table css for settings page data tables
		 *
		 * @return String
		 */
		public function data_table_css()
		{

			// Set condition to add script
			// if ( ! isset( $_GET['page'] ) || $_GET['page'] != 'pageName' ) return;

			$table_css = '<style type="text/css">
							.wp-list-table .column-ColumnName { width: 100%; }
						</style>';

			return $table_css;
		}


		/**
		 * Enter scripts into pages
		 *
		 * @return String
		 */
		public function backend_scripts()
		{

			// Set condition to add script
			if (!isset($_GET['page']) || $_GET['page'] != 'boostsite-menu') return;

			wp_enqueue_script('jsPopper', 'https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js', array());
			wp_enqueue_script('jsBooststrap', AI_PLUGIN_JS . '../bootstrap/js/bootstrap.min.js', array());
			wp_enqueue_script('jsName', AI_PLUGIN_JS . '../js/ui.js', array());

			wp_enqueue_script('jsAjax', AI_PLUGIN_JS . '../js/ajax.js', array());
			wp_localize_script('jsAjax', 'ajax', array('ajax_url' => admin_url('admin-ajax.php')));

			wp_enqueue_style('cssBoostrap', AI_PLUGIN_CSS . '../bootstrap/css/bootstrap.min.css');
			wp_enqueue_style('cssName', AI_PLUGIN_CSS . 'css.css');
		}


		/**
		 * Enter scripts into pages
		 *
		 * @return String
		 */
		public function frontend_scripts()
		{

			// wp_enqueue_script( 'jsName', AI_PLUGIN_JS . 'ui.js', array() );

			wp_localize_script('jsName', 'ajax', array('ajax_url' => admin_url('admin-ajax.php')));
			wp_enqueue_style('cssName', AI_PLUGIN_CSS . 'css.css');
		}



		function my_user_vote()
		{
			// if ($accountType === 'SEO_PLUGIN_PLAN') {
			// 	update_option('info_exist', 'Below you will see the first part of scheduled posts. All scheduled articles will appear here in the next 5 minutes. Come back in a moment and REFRESH this page to see all articles.');
			// }
			$html = '';
			$html .= '<div class="table-container">';
			$posts = get_posts([
				'post_status' => 'future',
				'numberposts' => 35,
				'orderby' => 'date',
				'order' => 'ASC',
			]);
			if ($posts) {

				$html .= '<table class="table table_planned_posts table-striped">';
				$html .= '<thead><tr>
						<th>Title</th>
					<th>Publication date</th>
					<th>Status</th>
				<tr></thead>';
				$html .= '<tbody>';
				//foreach ($posts as $key => $post) {
				// 	if (strlen($post->post_content) > 0) {
				// 		$class = 'badge bg-success text-white';
				// 	}
				// 	if (strlen($post->post_content) === 0) {
				// 		$class = 'badge bg-warning text-black';
				// 	}
				// 	$html .= '<tr class="">';
				// 	if (strlen($post->post_content) > 0) {
				// 		$html .= '<td><a href="' . get_edit_post_link($post) . '">' . $post->post_title . '</a></td>';
				// 	} else {
				// 		$html .= '<td>' . $post->post_title . '</td>';
				// 	}
				// 	$html .= '<td>' . date('j-m-Y H:i', strtotime($post->post_date)) . '</td>';
				// 	$html .= '<td><span class="' . $class . '">' . (strlen($post->post_content) > 0 ? 'Ready to be published' : 'Generating post content') . '</span></td>';
				// 	$html .= '</tr>';
				// }

				foreach ($posts as $key => $post) {
					$post_date = new \DateTime($post->post_date, new \DateTimeZone('Europe/Warsaw'));
					$dateToGenerate = strtotime('-2 day', $post_date->getTimestamp());
					$message = '';
					$now = new \DateTime("now", new \DateTimeZone('Europe/Warsaw'));


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
					$html .= '<tr class="">';
					if (strlen($post->post_content) > 0) {
						$html .= '<td><a href="' . get_edit_post_link($post) . '">' . $post->post_title . '</a></td>';
					} else {
						$html .= '<td>' . $post->post_title . '</td>';
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
					$html .= '<td>' . date('d F Y H:i', strtotime($post->post_date)) . '</td>';
					$html .= '<td><span class="' . $class . '">' . $message . '</span></td>';
					$html .= '</tr>';
				}

				$html .= '</tbody>';
				$html .= '</table>';
				$html .= '<br>';
				$html .= '<br>';
				$html .= '<br>';
			} else {
				$html .= '<div class="alert alert-light" role="alert">
					You have no blog posts planned yet. 
				</div>';
				$html .= '<br>';
				$html .= '<br>';
				$html .= '<br>';
			}

			$html .= '</div>';

			if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
				$result = $html;
				echo $result;
			} else {
				header("Location: " . $_SERVER["HTTP_REFERER"]);
			}


			die();
		}
	}
}
