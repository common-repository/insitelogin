<?php
/*
Plugin Name: InsiteLogin
Plugin URI: http://www.eurotraining.it/insitelogin
Description: A plugin that insert the standard login procedure into a page
Version: 0.7
Author: Franco Traversaro
Author URI: mailto:franco.traversaro@eurotraining.it
*/

class InSiteLogin_plugin {
	var $login_page;
	var $logged_in;
	var $logged_in_title;
	var $logged_out_title;
	var $text_login;
	var $text_lostpassword;
	var $text_retrievepassword;
	var $text_resetpass;
	var $text_rp;
	var $text_register;
	var $notice;
	var $separator;
	var $activated_actions = array('logout', 'lostpassword', 'retrievepassword', 'resetpass', 'rp', 'register');

	function __construct() {
		load_plugin_textdomain('insitelogin', PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)).'/i18n');

		define('INSITELOGIN_LOGIN_PAGE', 0);
		define('INSITELOGIN_LOGGED_IN', __('You are logged in. <a href="%%logout%%">Log out</a>.', 'insitelogin'));
		define('INSITELOGIN_LOGGED_IN_TITLE', __('Logout', 'insitelogin'));
		define('INSITELOGIN_LOGGED_OUT_TITLE', __('Login', 'insitelogin'));

		$this->login_page = INSITELOGIN_LOGIN_PAGE;
		$this->logged_in = INSITELOGIN_LOGGED_IN;
		$this->logged_in_title = INSITELOGIN_LOGGED_IN_TITLE;
		$this->logged_out_title = INSITELOGIN_LOGGED_OUT_TITLE;
		$this->notice = '';
		$this->separator = (get_option('permalink_structure')) ? '?' : '&';

		$this->manage_options();

		add_action('admin_menu', array($this, 'admin_menu'));
		add_action('admin_head', array($this, 'admin_head'));

		if ($this->login_page) {
			add_action('init', array($this, 'a_init'));
			add_filter('posts_results', array($this, 'f_posts_results'));
			add_filter('get_pages', array($this, 'f_get_pages'), 10, 2);
			add_filter('wp_redirect', array($this, 'f_wp_redirect'), 10, 2);
			add_filter('login_redirect', array($this, 'f_login_redirect'), 10, 2);
		}


		register_activation_hook(__FILE__, array($this, 'activate_plugin'));
		register_deactivation_hook(__FILE__, array($this, 'deactivate_plugin'));
	}

	function activate_plugin() {
		add_option('insitelogin_login_page', INSITELOGIN_LOGIN_PAGE);
		add_option('insitelogin_logged_in', INSITELOGIN_LOGGED_IN);
		add_option('insitelogin_logged_in_title', INSITELOGIN_LOGGED_IN_TITLE);
		add_option('insitelogin_logged_out_title', INSITELOGIN_LOGGED_OUT_TITLE);
	}

	function deactivate_plugin() {
		delete_option('insitelogin_login_page');
		delete_option('insitelogin_logged_in');
		delete_option('insitelogin_logged_in_title');
		delete_option('insitelogin_logged_out_title');
	}

	function a_init() {
		if (empty($_POST) and false!==strpos($_SERVER['PHP_SELF'], 'wp-login.php')) {
			$proto = ( is_ssl() ) ? 'https://' : 'http://';
			$url = parse_url($proto . $_SERVER['SERVER_NAME'] . '/' . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
			if (isset($url['query'])) {
				wp_redirect(get_permalink($this->login_page) . $this->separator . $url['query']);
				die();
			} else {
				wp_redirect(get_permalink($this->login_page));
				die();
			}
		}
		register_sidebar( array(	'name' => 'insitelogin_sidebar',
									'before_widget' => '<li id="%1$s" class="widget %2$s">',
									'after_widget' => '</li>',
									'before_title' => '<h2>',
									'after_title' => '</h2>'	) );
	}

	function f_wp_redirect($location, $status) {
		if (preg_match('/^wp-login.php(?:\?(.*))?/', $location, $matches)) {
			return get_permalink($this->login_page) . $this->separator . $matches[1];
		} else {
			return $location;
		}
	}

	function f_login_redirect($default, $requested) {
		if (isset($_REQUEST['redirect_to']) and $_REQUEST['redirect_to']) {
			return $_REQUEST['redirect_to'];
		} else {
			if ($default==admin_url()) {
				return get_permalink($this->login_page);
			} else {
				return $default;
			}
		}
	}

	function admin_head() {
		echo '<link rel="stylesheet" type="text/css" href="'.WP_PLUGIN_URL. '/insitelogin/style.css">';
	}


	function manage_options() {
		if (isset($_POST['insitelogin_changeoptions']) and $_POST['insitelogin_changeoptions']==md5(__FILE__)) {
// 	$activated_actions = array('logout', 'lostpassword', 'retrievepassword', 'resetpass', 'rp', 'register');
			update_option('insitelogin_login_page', stripslashes($_POST['insitelogin']['login_page']));
			update_option('insitelogin_logged_in', stripslashes($_POST['insitelogin']['logged_in']));
			update_option('insitelogin_logged_in_title', stripslashes($_POST['insitelogin']['logged_in_title']));
			update_option('insitelogin_logged_out_title', stripslashes($_POST['insitelogin']['logged_out_title']));

			update_option('insitelogin_text_login', stripslashes($_POST['insitelogin']['text_login']));
			update_option('insitelogin_text_lostpassword', stripslashes($_POST['insitelogin']['text_lostpassword']));
			update_option('insitelogin_text_retrievepassword', stripslashes($_POST['insitelogin']['text_retrievepassword']));
			update_option('insitelogin_text_resetpass', stripslashes($_POST['insitelogin']['text_resetpass']));
			update_option('insitelogin_text_register', stripslashes($_POST['insitelogin']['text_register']));
			$this->notice = '<br /><div class="updated fade" id="message" style="background-color: rgb(255, 251, 204);"><p>' . __('Options correctly changed!', 'insitelogin') . '</p></div>';
		}
		$this->login_page = get_option('insitelogin_login_page');
		$this->logged_in = get_option('insitelogin_logged_in');
		$this->logged_in_title = get_option('insitelogin_logged_in_title');
		$this->logged_out_title = get_option('insitelogin_logged_out_title');

		$this->text_login = get_option('insitelogin_text_login');
		$this->text_lostpassword = get_option('insitelogin_text_lostpassword');
		$this->text_retrievepassword = get_option('insitelogin_text_retrievepassword');
		$this->text_resetpass = get_option('insitelogin_text_resetpass');
		$this->text_rp = $this->text_resetpass;
		$this->text_register = get_option('insitelogin_text_register');

		$this->text_rp = $this->text_resetpass;

		if (!$this->logged_out_title) $this->logged_out_title=INSITELOGIN_LOGGED_OUT_TITLE;
		if (!$this->logged_in_title) $this->logged_in_title=INSITELOGIN_LOGGED_IN_TITLE;
// 		if (!$this->logged_in) $this->logged_in=INSITELOGIN_LOGGED_IN;
	}

	function f_posts_results($posts) {
		if (is_admin() or empty($posts)) {
			return $posts;
		} else {
			foreach ($posts as $key=>$val) {
				if ($val->ID==$this->login_page) {
					$val->post_content = $this->the_plugin();
					$val->post_title = (is_user_logged_in()) ? $this->logged_in_title : $this->logged_out_title;
				}
				$return[ $key ] = $val;
			}
			return $return;
		}
	}

	function f_get_pages($posts, $r) {
		if (is_admin() or empty($posts)) {
			return $posts;
		} else {
			foreach ($posts as $key=>$val) {
				if ($val->ID==$this->login_page) {
					$val->post_title = (is_user_logged_in()) ? $this->logged_in_title : $this->logged_out_title;
				}
				$return[ $key ] = $val;
			}
			return $return;
		}
	}

	function the_plugin() {
		global $post;
		$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
		if ( in_array($action, $this->activated_actions) or !is_user_logged_in() ) {
			ob_start();
			include(ABSPATH.'/wp-login.php');
			preg_match('/\<(body)\b(?:.*?)(?:(?:\/))?\>(?:(.+?)\<\/\1\>)?/si', ob_get_clean(), $matches);
			$matches[2] = preg_replace('/\<h1\b(?:.*?)(?:(?:\/))?\>(?:(?:.+?)\<\/h1\>)?/si', '', $matches[2]);
			$chroot = '<div id="insitelogin_container">'.$matches[2].'</div>';
			preg_match_all('/(?:href|action)=("|\')(?:(.*?)\1)/i', $chroot, $matches);
			if (empty($matches)) {
				return $chroot;
			} else {
				$search = $replace = array();
				foreach ($matches[2] as $victim) {
// 					maialata perchè non sono capace a mettere la regola nel preg_match_all
					if (false!==strpos($victim, 'wp-login.php')) {
						$url = parse_url($victim);
						$search[] = '|[\'"]'.preg_quote($victim).'[\'"]|';
						if (isset($url['query'])) {
							$replace[] = '"' . get_permalink($this->login_page) . $this->separator . $url['query'] . '"';
						} else {
							$replace[] = '"' . get_permalink($this->login_page) . '"';
						}
					}
				}

				if ( !$action ) $action='login';
				return '<div class="insitelogin_text">' . $this->{'text_' . $action} . '</div>' . preg_replace($search, $replace, $chroot);
			}
		} else {
			ob_start();
			echo '<ul id="insitelogin_sidebar_container">';
			dynamic_sidebar('insitelogin_sidebar');
			echo '</ul>';
			$sidebar = ob_get_clean();
			if (is_callable('wp_nonce_url')) {
// 				new way, Mike Malone's way
				$logout = wp_nonce_url( get_permalink( $this->login_page ) . $this->separator . 'action=logout', 'log-out' );
			} else {
// 				old way, Belinde's way
				$logout = get_permalink($this->login_page) . $this->separator . 'action=logout';
			}

			return str_ireplace('%%logout%%', $logout, $this->logged_in . $sidebar);
		}
	}

	function admin_menu() {
		add_options_page('InsiteLogin', 'InsiteLogin', 10, __FILE__, array($this, 'opzioni'));
	}

	function opzioni() {
		global $wpdb;

		echo '<div class="wrap">';
		echo '<h2>' . __('InsiteLogin options', 'insitelogin') . '</h2>';
		echo $this->notice;
		echo '<form method="post">';
		echo '<table class="form-table" id="insitelogin_options">';
		echo '<tbody>';

		echo "\n<tr>";
		echo "\n\t".'<td><label for="insitelogin_login_page"><strong>' . __('Login page', 'insitelogin') . '</strong><br />' . __('Original content page will be ignored.', 'insitelogin') . '</label></td>';
		echo "\n\t".'<td><select id="insitelogin_login_page" name="insitelogin[login_page]" class="insitelogin_width">';
		$pagine = $wpdb->get_results("SELECT `ID`, `post_title`, `menu_order` FROM `{$wpdb->posts}` WHERE `post_status`='publish' AND `post_type`='page' UNION SELECT 0, '', -1 AS menu_order ORDER BY `menu_order`", ARRAY_A);
		if (!empty($pagine)) foreach ($pagine as $row) {
			$check = ($row['ID']==$this->login_page) ? ' selected="selected"' : '';
			echo '<option value="'.$row['ID'].'"'.$check.'>'.$row['post_title'].'</option>';
		}
		echo '</select></td>';
		echo "\n".'</tr>';


		echo "\n<tr>";
		echo "\n\t".'<td><label for="insitelogin_logged_in"><strong>' . __('Logged in page', 'insitelogin') . '</strong><br />' . __('%%logout%% will be replaced with logout URL.', 'insitelogin') . '</label></td>';
		echo "\n\t".'<td><textarea id="insitelogin_logged_in" name="insitelogin[logged_in]" class="insitelogin_width">' . $this->logged_in . '</textarea></td>';
		echo "\n".'</tr>';

		echo "\n<tr>";
		echo "\n\t".'<td><label for="insitelogin_logged_in_title"><strong>' . __('Logged in page title', 'insitelogin') . '</strong></label></td>';
		echo "\n\t".'<td><input type="text" id="insitelogin_logged_in_title" name="insitelogin[logged_in_title]" value="' . $this->logged_in_title . '" class="insitelogin_width" /></td>';
		echo "\n".'</tr>';

		echo "\n<tr>";
		echo "\n\t".'<td><label for="insitelogin_logged_out_title"><strong>' . __('Logged out page title', 'insitelogin') . '</strong></label></td>';
		echo "\n\t".'<td><input type="text" id="insitelogin_logged_out_title" name="insitelogin[logged_out_title]" value="' . $this->logged_out_title . '" class="insitelogin_width" /></td>';
		echo "\n".'</tr>';




		echo "\n<tr>";
		echo "\n\t".'<td><label for="insitelogin_text_login"><strong>' . __('Login text', 'insitelogin') . '</strong></label></td>';
		echo "\n\t".'<td><textarea id="insitelogin_text_login" name="insitelogin[text_login]" class="insitelogin_width">' . $this->text_login . '</textarea></td>';
		echo "\n".'</tr>';

		echo "\n<tr>";
		echo "\n\t".'<td><label for="insitelogin_text_lostpassword"><strong>' . __('Lost password text', 'insitelogin') . '</strong></label></td>';
		echo "\n\t".'<td><textarea id="insitelogin_text_lostpassword" name="insitelogin[text_lostpassword]" class="insitelogin_width" >' . $this->text_lostpassword . '</textarea></td>';
		echo "\n".'</tr>';

		echo "\n<tr>";
		echo "\n\t".'<td><label for="insitelogin_text_retrievepassword"><strong>' . __('Retrieve password text', 'insitelogin') . '</strong></label></td>';
		echo "\n\t".'<td><textarea id="insitelogin_text_retrievepassword" name="insitelogin[text_retrievepassword]" class="insitelogin_width" >' . $this->text_retrievepassword . '</textarea></td>';
		echo "\n".'</tr>';

		echo "\n<tr>";
		echo "\n\t".'<td><label for="insitelogin_text_resetpass"><strong>' . __('Reset password text', 'insitelogin') . '</strong></label></td>';
		echo "\n\t".'<td><textarea id="insitelogin_text_resetpass" name="insitelogin[text_resetpass]" class="insitelogin_width" >' . $this->text_resetpass . '</textarea></td>';
		echo "\n".'</tr>';

		echo "\n<tr>";
		echo "\n\t".'<td><label for="insitelogin_text_register"><strong>' . __('Register text', 'insitelogin') . '</strong></label></td>';
		echo "\n\t".'<td><textarea id="insitelogin_text_register" name="insitelogin[text_register]" class="insitelogin_width" >' . $this->text_register . '</textarea></td>';
		echo "\n".'</tr>';

		echo '</tbody>';
		echo '</table>';
		echo '<p class="submit"><input type="submit" value="' . __('Save Changes', 'insitelogin') . '" class="button" /></p>';
		echo '<input type="hidden" value="'.md5(__FILE__).'" name="insitelogin_changeoptions"/>';
		echo '</form>';
		echo '</div>';
	}

}

if (!isset($InSiteLogin_plugin_singleton)) $InSiteLogin_plugin_singleton = new InSiteLogin_plugin();
?>