<?php defined('ABSPATH') or die('Bye');
/**
 * Plugin Name:     Ib Firebase Enabled
 * Plugin URI:      https://github.com/internetbacked/ib-firebase-enabled
 * Description:     connect firebase to wordpress
 * Author:          Internetbacked
 * Author URI:      https://internetbacked.com
 * Text Domain:     ib-firebase-enabled
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Ib_Firebase_Enabled
 */

global $ib_firebase_enabled;

define('IBFE_PLUGIN', __FILE__);
define('IBFE_PLUGIN_DIR', __DIR__);
define('IBFE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('IBFE_INDEX', 999);

require_once IBFE_PLUGIN_DIR.'/vendor/autoload.php';

/**
 * Check if Ib Laravel Helper is active
 **/
if ( in_array( 'ib-laravel-helper/ib-laravel-helper.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	add_action('init', array(Ibfe\Ib_Firebase_Enabled::class, 'init'), IBFE_INDEX);
} else {
	function ibfe_admin_notice()
	{
		?>
		<div class="notice notice-error is-dismissible">
			<p>Firebase not enabled</p>
		</div>
		<?php
	}

	add_action('admin_notices', 'ibfe_admin_notice');
}
