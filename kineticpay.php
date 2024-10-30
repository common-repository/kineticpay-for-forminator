<?php
/**
Plugin Name: Kineticpay for Forminator
Plugin URI: https://wordpress.org/plugins/kineticpay-for-forminator/
Description: Kineticpay. Fair payment platform.
Version: 1.0.2
Author: Kinetic Innovative Technologies Sdn Bhd
Author URI: https://www.kitsb.com.my/
License: GPL-2.0+
Text Domain: kineticpay-forminator
Domain Path: /languages
*/

defined( 'ABSPATH' ) || die();

define('FMNTR_KINETICPAY_VERSION', '1.0.2');
define('FMNTR_KINETICPAY_URL', plugin_dir_url(__FILE__));
define('FMNTR_KINETICPAY_PATH', dirname(__FILE__));
define('FMNTR_PRODUCT_CODE', '1786');
add_action( 'forminator_loaded', 'kineticpay_for_forminator' );
function kineticpay_for_forminator() {
	if (class_exists('Forminator_API') && class_exists('Forminator_Front_Action')) {
		include_once FMNTR_KINETICPAY_PATH . '/classes/kineticpay-gateway.php';
		include_once FMNTR_KINETICPAY_PATH . '/classes/admin.php';
		include_once FMNTR_KINETICPAY_PATH . '/classes/public.php';
		load_plugin_textdomain( 'kineticpay-forminator', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
	}
}