<?php
/**
 * Payment gateway - HBL
 *
 * Provides a HBL Payment Gateway.
 *
 * @since   2.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_Gateway_HBL_Payment Class.
 */
class WC_Gateway_HBL_Payment extends WC_Payment_Gateway {

	/**
	 * Whether or not logging is enabled.
	 *
	 * @since 2.0.0
	 *
	 * @var boolean
	 */
	public static $log_enabled = false;

	/**
	 * A log object returned by wc_get_logger().
	 *
	 * @since 1 .0.0
	 *
	 * @var boolean
	 */
	public static $log = false;

	/**
	 * Constructor for the gateway.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->id                 = 'woo-hbl-payment';
		$this->has_fields         = true;
		$this->order_button_text  = __( 'Proceed to Himalayan Bank Payment', 'woo-hbl-payment' );
		$this->method_title       = __( 'Himalayan Bank Payment', 'woo-hbl-payment' );
		$this->method_description = __( 'Adds Himalayan Bank as payment gateway in WooCommerce plugin.', 'woo-hbl-payment' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
		$this->debug       = 'yes' === $this->get_option( 'debug', 'no' );
		$this->merchant_id = $this->get_option( 'merchant_id' );

		// Enable logging for events.
		self::$log_enabled = $this->debug;

		if ( $this->debug ) {
			$this->description .= '<p style="color:#fff;background:red;padding:5px 10px;border-radius:3px;"><strong>TEST MODE ENABLED!!!</strong> In test mode, you can use the card numbers listed in <a style="color:#fff;" target="_blank" href="https://sureshchand.com.np/woo-hbl-payment/index.html">documentation</a>.</p>';
			$this->description  = trim( $this->description );
		}

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		if ( ! $this->is_valid_for_use() ) {
			$this->enabled = 'no';
		}

		$this->notify_url = WC()->api_request_url( 'WC_Gateway_HBL_Payment' );
	}

	/**
	 * Return whether or not this gateway still requires setup to function.
	 *
	 * When this gateway is toggled on via AJAX, if this returns true a
	 * redirect will occur to the settings page instead.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function needs_setup() {
		return empty( $this->merchant_id );
	}

	/**
	 * Logging method.
	 *
	 * @param string $message Log message.
	 * @param string $level Optional, defaults to info, valid levels:
	 *                      emergency|alert|critical|error|warning|notice|info|debug.
	 *
	 * @since 2.0.0
	 */
	public static function log( $message, $level = 'info' ) {
		if ( self::$log_enabled ) {
			if ( empty( self::$log ) ) {
				self::$log = wc_get_logger();
			}
			self::$log->log( $level, $message, array( 'source' => 'woo-hbl-payment' ) );
		}
	}

	/**
	 * Processes and saves options.
	 * If there is an error thrown, will continue to save and validate fields, but will leave the erroring field out.
	 *
	 * @since 2.0.0
	 *
	 * @return bool was anything saved?
	 */
	public function process_admin_options() {
		$saved = parent::process_admin_options();

		// Maybe clear logs.
		if ( 'yes' !== $this->get_option( 'debug', 'no' ) ) {
			if ( empty( self::$log ) ) {
				self::$log = wc_get_logger();
			}
			self::$log->clear( 'woo-hbl-payment' );
		}

		return $saved;
	}

	/**
	 * Check if this gateway is enabled and available in the user's country.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_valid_for_use() {

		// return in_array( get_woocommerce_currency(), apply_filters( 'hbl_payment_for_woocommerce_supported_currencies', array( 'USD', 'NPR', 'THB' ) ), true );
		return true;
	}

	/**
	 * Admin Panel Options.
	 * - Options for bits like 'title' and availability on a country-by-country basis.
	 *
	 * @since 2.0.0
	 */
	public function admin_options() {
		if ( $this->is_valid_for_use() ) {
			parent::admin_options();
		} else {
			?>
			<div class="inline error">
				<p>
					<strong><?php esc_html_e( 'Gateway Disabled', 'woo-hbl-payment' ); ?></strong>: <?php esc_html_e( 'Himalayan Bank does not support your store currency. Go to the general settings and setup Supported currency to enable Himalayan Bank Payment.', 'woo-hbl-payment' ); ?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 *
	 * @since 2.0.0
	 */
	public function init_form_fields() {
		$this->form_fields = include WOOHBL_PLUGIN_PATH . '/template/settings.php';
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param  int $order_id Order ID.
	 *
	 * @since 2.0.0
	 *
	 * @return mixed
	 */
	public function process_payment( $order_id ) {
		include WOOHBL_PLUGIN_PATH . '/src/class-hbl-payment-request.php';
		$order = wc_get_order( $order_id );
		$request = new \HBLPayment\Request( $this );
		$result = $request->result( $order );

		

		if ( 
			isset( $result->apiResponse->responseCode )
			&& 'PC-B050001' === $result->apiResponse->responseCode 
			&& isset( $result->data->paymentPage->paymentPageURL )
		) {
			return array(
				'result'   => 'success',
				'redirect' => $result->data->paymentPage->paymentPageURL
			);
		}

		if ( isset( $result->apiResponse->marketingDescription ) ) {
			wc_add_notice( 'REQ ERROR: ' . esc_html( $result->apiResponse->marketingDescription ) . '. Please follow the <a href="https://sureshchand.com.np/woo-hbl-payment/index.html/#testing" target="_blank">testing & debugging instructions.</a>', 'error' );
			return;
		}

		// Something went wrong.
		wc_add_notice( 'ERROR: Something went wrong. Please follow the <a href="https://sureshchand.com.np/woo-hbl-payment/index.html/#testing" target="_blank">testing & debugging instructions.</a>'  , 'error' );

		// Failed anyway.
		return; //phpcs:ignore Squiz.PHP.NonExecutableCode.ReturnNotRequired.
	}

	/**
	 * Creates a GUID
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	private function Guid() {
		if ( function_exists( 'com_create_guid' ) ) {
			return com_create_guid();
		} else {
			$charId = strtoupper( md5( uniqid( rand(), true ) ) );
			$hyphen = chr( 45 );
			// "-"
			$guid = substr( $charId, 0, 8 ) . $hyphen
				. substr( $charId, 8, 4 ) . $hyphen
				. substr( $charId, 12, 4 ) . $hyphen
				. substr( $charId, 16, 4 ) . $hyphen
				. substr( $charId, 20, 12 );
			return strtolower( $guid );
		}
	}
}