<?php
/**
* Plugin Name: FS WooCommerce Wallet
* Plugin URI: http://codecanyon.net/user/firassaidi
* Description: سیستم کیف پول ووکامرس
* Version: 4.3.0
* Author: نوین وردپرس 
* Author URI: wpnovin.com
* Text Domain: fsww
* Domain Path: /languages/
 */
 
   require 'novin-update/wpnovin-atuo-update.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'http://dl3.wpnovin.com/Source/Plugins/FS_WooCommerce_Wallet/bhjkjhgvfkjhdglth5225fd.json',
	__FILE__,
	'FSWallet'
);



if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

if (!defined('FSWW_FILE')) {
    define('FSWW_FILE', __FILE__);
}

if (!defined('FSWW_BASE')) {
    define('FSWW_BASE', dirname(__FILE__));
}


require_once(dirname(__FILE__) . '/includes/classes/FS_WC_Wallet.php');

register_activation_hook(__FILE__, array('FS_WC_Wallet', 'activation'));

require_once(dirname(__FILE__) . '/includes/functions.php');

$GLOBAL['FSWW'] = FS_WC_Wallet::instance();

