<?php
/**
 * HBL Payment setup
 *
 * @package woo_hbl_payment
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main HBL Payment Class.
 *
 * @class HBL Payment
 */
final class HBL_Payment_class {

    /**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '1.0.0';

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;


    /**
	 * Initialize the plugin.
	 */
	private function __construct() {
		// Checks with WooCommerce is installed.
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.0', '>=' ) ) {
			$this->includes();

			// Hooks.
			add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( WOOHBL_PLUGIN_FILE ), array( $this, 'plugin_action_links' ) );

			add_action("parse_request", function( $wp ){
				if ( 
					! empty( $wp->query_vars['wc-api'] ) 
					&& $wp->query_vars['wc-api'] == 'WC_Gateway_HBL_Payment'
					&& isset($_GET['orderNo']) && !empty($_GET['orderNo'])
					&& isset($_GET['payment']) && !empty($_GET['payment'])
				) {
					require_once WOOHBL_PLUGIN_PATH . '/src/class-hbl-final-payment.php';
					$finalprocess = \HBLPayment\FinalPayment::get_instance();
					$finalprocess->process();
				}
			}, -1);
		} else {
			add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
		}
	}

    /**
	 * Includes.
	 */
	private function includes() {
		include_once WOOHBL_PLUGIN_PATH . '/src/class-wc-gateway-hbl.php';
	}

    /**
	 * Add the gateway to WooCommerce.
	 *
	 * @param  array $methods WooCommerce payment methods.
	 * @return array          Payment methods with eSewa.
	 */
	public function add_gateway( $methods ) {
		$methods[] = 'WC_Gateway_HBL_Payment';
		return $methods;
	}
    
    /**
	 * Display action links in the Plugins list table.
	 *
	 * @param  array $actions Plugin Action links.
	 * @return array
	 */
	public function plugin_action_links( $actions ) {
		$new_actions = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=woo-hbl-payment' ) . '" aria-label="' . esc_attr( __( 'View HBL Payment settings', 'woo-hbl-payment' ) ) . '">' . __( 'Settings', 'woo-hbl-payment' ) . '</a>',
		);

		return array_merge( $new_actions, $actions );
	}


    /**
	 * WooCommerce fallback notice.
	 */
	public function woocommerce_missing_notice() {
		/* translators: %s: woocommerce version */
        echo '<div class="error notice is-dismissible"><p>'
        . sprintf( esc_html__( 'Woocommerce HBL Payment depends on the last version of %s or later to work.', 'woo-hbl-payment' ) )
        . sprintf('<a href="%s" target="_blank">', admin_url("update.php?action=install-plugin&plugin=woocommerce"))
        . esc_html__( 'Activate Plugin', 'woo-hbl-payment' )
        . '</a></p></div>';
	}

    /**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}