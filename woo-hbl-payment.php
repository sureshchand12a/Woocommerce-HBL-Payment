<?php
/**
 * Plugin Name: Woocommerce Himalayan Bank Payment v2
 * Description: Adds Himalayan Bank (V2) as payment gateway in WooCommerce plugin.
 * Version: 1.0.0
 * Requires PHP: 7.4
 * Requires at least: 5.0
 * Author: Suresh Chand
 * Author URI: https://sureshchand.com.np
 * Text Domain: woo-hbl-payment
 */

defined( 'ABSPATH' ) || exit;

defined( 'ABSPATH' ) || die();
define( 'WOOHBL_PLUGIN_FILE', __FILE__ );
define( 'WOOHBL_PLUGIN_PATH', __DIR__ );
define( 'WOOHBL_VERSION', '1.0.0' );

if( !class_exists( "WC_Gateway_HBL_Payment" ) ){
	require_once WOOHBL_PLUGIN_PATH . '/src/class-hbl-payment.php';
}

// Initialize the plugin.
add_action( 'plugins_loaded', array( 'HBL_Payment_class', 'get_instance' ) );