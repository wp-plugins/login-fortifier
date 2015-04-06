<?php

/*
  Plugin Name: Login Fortifier
  Plugin URI: http://www.wpidiots.com/
  Description: Secure your WordPress login form from bruteforce attacks and bots
  Author: WP Idiots
  Author URI: http://www.wpidiots.com/
  Version: 1.0

  Copyright 2015 WP Idiots (http://www.wpidiots.com/)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
  the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'WP_Login_Fortifier' ) ) {

	class WP_Login_Fortifier {

		var $version		 = '1.0';
		var $title		 = 'Login Fortifier';
		var $name		 = 'login-fortifier';
		var $dir_name	 = 'login-fortifier';
		var $location	 = 'plugins';
		var $plugin_dir	 = '';
		var $plugin_url	 = '';

		function __construct() {
			$this->init_vars();
			add_action( 'login_enqueue_scripts', array( &$this, 'add_fortifier_js' ) );
			add_action( 'admin_enqueue_scripts', array( &$this, 'admin_header' ) );
			//validate login signup
			if ( isset( $_POST[ 'wp-submit' ] ) ) {
				require_once(ABSPATH . 'wp-includes/pluggable.php');
				if ( !isset( $_POST[ 'login-fortifier-key' ] ) || !wp_verify_nonce( $_POST[ 'login-fortifier-key' ], 'login_submit' ) ) {
					add_filter( 'authenticate', array( &$this, 'check_login_submit' ), 40, 3 );
				}
			}

			add_action( 'wp_dashboard_setup', array( &$this, 'add_fortifier_widget' ) );
		}

		/*
		 * setup proper directories
		 */

		function add_fortifier_widget() {
			wp_add_dashboard_widget('wp-login-fortifier-widget', 'Login Fortifier', array( &$this, 'widget' ) );
		}

		function widget() {
			require_once( $this->plugin_dir . 'widget.php' );
		}

		function admin_header(){
			wp_enqueue_style( 'wp-login-fortifier', $this->plugin_url . 'css/admin.css', array(), $this->version );
		}
		
		function init_vars() {

			if ( defined( 'WP_PLUGIN_URL' ) && defined( 'WP_PLUGIN_DIR' ) && file_exists( WP_PLUGIN_DIR . '/' . $this->dir_name . '/' . basename( __FILE__ ) ) ) {
				$this->location		 = 'subfolder-plugins';
				$this->plugin_dir	 = WP_PLUGIN_DIR . '/' . $this->dir_name . '/';
				$this->plugin_url	 = plugins_url( '/', __FILE__ );
			} else if ( defined( 'WP_PLUGIN_URL' ) && defined( 'WP_PLUGIN_DIR' ) && file_exists( WP_PLUGIN_DIR . '/' . basename( __FILE__ ) ) ) {
				$this->location		 = 'plugins';
				$this->plugin_dir	 = WP_PLUGIN_DIR . '/';
				$this->plugin_url	 = plugins_url( '/', __FILE__ );
			} else if ( is_multisite() && defined( 'WPMU_PLUGIN_URL' ) && defined( 'WPMU_PLUGIN_DIR' ) && file_exists( WPMU_PLUGIN_DIR . '/' . basename( __FILE__ ) ) ) {
				$this->location		 = 'mu-plugins';
				$this->plugin_dir	 = WPMU_PLUGIN_DIR;
				$this->plugin_url	 = WPMU_PLUGIN_URL;
			} else {
				wp_die( sprintf( __( 'There was an issue determining where %s is installed. Please reinstall it.', 'tc' ), $this->title ) );
			}
		}

		function check_login_submit( $user, $username, $password ) {
			$fortified_time = get_option( 'fortified_times', 0 );
			update_option( 'fortified_times', (int) $fortified_time + 1 );

			$WP_Error = new WP_Error();
			$WP_Error->add( 'fortifier_error', '<strong>Security Error</strong>: Please turn on the JavaScript in your browser.' );

			return $WP_Error;
		}

		function add_fortifier_js() {
			wp_enqueue_script( 'wp-login-fortifier', $this->plugin_url . 'js/wp-login-fortifier.js', array( 'jquery' ), $this->version );
			wp_localize_script( 'wp-login-fortifier', 'wplf_trans', array(
				'login_fortifier_field' => wp_nonce_field( 'login_submit', 'login-fortifier-key', wp_referer_field( false ), false ),
			) );
		}

	}

}

$wp_login_fortifier = new WP_Login_Fortifier();
