<?php
/**
 * Plugin Name
 *
 * @package           MANAGE-PLUGINS
 * @author            InstallActivateGo.com
 * @copyright         Copyright (C) 2013-2024, InstallActivateGo.com
 *
 * @wordpress-plugin
 * Plugin Name:       Reveal Network Activated Plugins
 * Plugin URI:        https://installactivatego.com/manage-plugins
 * Description:       Displays Network-Activated and Must-Use (MU) plugins, and Drop-ins on the Installed Plugins Admin panel for individual sites of a WordPress Network.
 * Version:           3.2
 * Requires at least: 6.3
 * Requires PHP:      7.0
 * Author:            InstallActivateGo.com
 * Author URI:        https://installactivatego.com
 * Text Domain:       jonradio-reveal-network-activated-plugins
 * License:           GPL-3.0+
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'ABSPATH' ) || exit;

if ( is_admin() ) {
	add_action( 'init', 'jr_rnap_init' );
	function jr_rnap_init() {
		if ( is_user_logged_in() ) {
			global $jr_rnap_file;
			$jr_rnap_file = __FILE__;
			
			/*	Catch old unsupported version of WordPress before any damage can be done.
			*/
			if ( version_compare( get_bloginfo( 'version' ), '3.1', '<' ) ) {
				require_once( plugin_dir_path( __FILE__ ) . 'includes/old-wp.php' );
			} else {
				/*	Globals
				*/
				global $jr_rnap_settings_names, $jr_rnap_settings_values, $jr_rnap_show_values;
				$jr_rnap_settings_names = array(
					'netact'  => 'Network-Activated', 
					'mustuse' => 'Must-Use', 
					'dropins' => 'Drop-ins'
				);
				$jr_rnap_settings_values = array(
					'noone' => 'No One', 
					'super'  => 'Super Administrators Only',  
					'siteadmin' => 'Site Administrators**'
				);
				$jr_rnap_show_values = array(
					''       => 'Default', 
					'never'  => 'Never',  
					'always' => 'Always'
				);
				
				global $jr_rnap_path;
				$jr_rnap_path = plugin_dir_path( __FILE__ );
				/**
				* Return Plugin's full directory path with trailing slash
				* 
				* Local XAMPP install might return:
				*	C:\xampp\htdocs\wpbeta\wp-content\plugins\jonradio-reveal-network-activated-plugins/
				*
				*/
				function jr_rnap_path() {
					global $jr_rnap_path;
					return $jr_rnap_path;
				}
				
				global $jr_rnap_plugin_basename;
				$jr_rnap_plugin_basename = plugin_basename( __FILE__ );
				/**
				* Return Plugin's Basename
				* 
				* For this plugin, it would be:
				*	jonradio-multiple-themes/jonradio-multiple-themes.php
				*
				*/
				function jr_rnap_plugin_basename() {
					global $jr_rnap_plugin_basename;
					return $jr_rnap_plugin_basename;
				}
				
				global $jr_rnap_plugin_data;
				if ( !function_exists( 'get_plugin_data' ) ) {
					require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				}
				$jr_rnap_plugin_data = get_plugin_data( __FILE__ );
				$jr_rnap_plugin_data['slug'] = basename( dirname( __FILE__ ) );
				
				/*	Detect initial activation or a change in plugin's Version number
			
					Sometimes special processing is required when the plugin is updated to a new version of the plugin.
					Also used in place of standard activation and new site creation exits provided by WordPress.
					Once that is complete, update the Version number in the plugin's Network-wide settings.
				*/
			
				$internal_settings = get_site_option( 'jr_rnap_internal_settings', array() );
				if ( empty( $internal_settings['version'] ) ) {
					/*	Plugin is either:
						- updated from a version so old that Version was not yet stored in the plugin's settings, or
						- first use after install:
							- first time ever installed, or
							- installed previously and properly uninstalled (data deleted)
					*/
			
					$old_version = '1';
				} else {
					$old_version = $internal_settings['version'];
				}
				
				$settings = get_site_option( 'jr_rnap_network_settings' );
				if ( empty( $settings ) || !isset( $settings['netact'] ) ) {
					$settings = array(
						'show' => array()
					);
					foreach ( $jr_rnap_settings_names as $value => $description ) {
						$settings[$value] = 'siteadmin';
					}
					/*	Add if Settings don't exist, re-initialize if they were empty.
					*/
					update_site_option( 'jr_rnap_network_settings', $settings );
					/*	New install on this site, very old version or corrupt settings
					*/
				}
				
				/*	This Setting was added later, and initial implementation was wrong.
				*/
				if ( ( !isset( $settings['show'] ) ) || ( !is_array( $settings['show'] ) ) ) {
					$settings['show'] = array();
					update_site_option( 'jr_rnap_network_settings', $settings );
				}
				
				if ( version_compare( $old_version, $jr_rnap_plugin_data['Version'], '!=' ) ) {
					/*	Create, if internal settings do not exist; update if they do exist
					*/
					$internal_settings['version'] = $jr_rnap_plugin_data['Version'];
					update_site_option( 'jr_rnap_internal_settings', $internal_settings );
					/*	Handle all Settings changes made in old plugin versions
						(none yet)
					*/
				}
			
				if ( is_multisite() ) {
					require_once( jr_rnap_path() . 'includes/multi-site.php' );
				} else {
					require_once( jr_rnap_path() . 'includes/single-site.php' );
				}
			}
		}
	}
}

?>