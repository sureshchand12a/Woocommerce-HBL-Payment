<?php 

namespace HBLPayment;

class FinalPayment{

    /**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

    /**
	 * Endpoint for requests from Himalayan Bank.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	protected $req_url;

    public function __construct(){
		$payment_gateways   = \WC_Payment_Gateways::instance();
		$payment_gateway    = $payment_gateways->payment_gateways()['woo-hbl-payment'];
		$this->settings = (object) $payment_gateway->settings;
    }

	function request_api(){
		return filter_var($this->settings->test_mode, FILTER_VALIDATE_BOOLEAN) ? 
			'https://core.demo-paco.2c2p.com/api/1.0/Inquiry/transactionStatus?orderNo=%d' 
		: 
			'https://core.paco.2c2p.com/api/1.0/Inquiry/transactionStatus?orderNo=%d';
	}

	function request( $orderNo ){

		$requesturl = sprintf($this->request_api(), $orderNo);
		\WC_Gateway_HBL_Payment::log( 'Validation: Request URL for order ' . $orderNo . ': ' . $requesturl );
		$options = array(
			'headers'     => array(
				'Content-Type' => 'application/json; charset=utf-8',
				'apiKey'       => $this->settings->merchant_password,
			)
		);
		$response = wp_remote_get( $requesturl, $options );

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			\WC_Gateway_HBL_Payment::log( 'Validation: Response error for order ' . $orderNo . ': ' . wc_print_r( $error_message, true ) );
			return false;
		} else {
			$body = wp_remote_retrieve_body( $response );
			$body = json_decode( $body );
			\WC_Gateway_HBL_Payment::log( 'Validation: Response details for ' . $orderNo . ': ' . wc_print_r( $body, true ) );
			return $body;
		}
	}

    public function process( ){
		global $wp;
		if ( 
			! empty( $wp->query_vars['wc-api'] ) 
			&& $wp->query_vars['wc-api'] == 'WC_Gateway_HBL_Payment'
			&& isset($_GET['orderNo']) && !empty($_GET['orderNo'])
			&& isset($_GET['payment']) && !empty($_GET['payment'])
		) {
			$controllerid = isset($_GET['controllerInternalId']) ? trim($_GET['controllerInternalId']) : "";
			
			$orderNo = isset($_GET['orderNo']) ? trim($_GET['orderNo']) : "";
			$order = wc_get_order( $orderNo );

			$status = isset($_GET['payment']) ? trim($_GET['payment']) : "";

			/** Now Request */
			$pay_now_url = wc_get_cart_url();
			if( $order ){
				if( $status == 'cancel' ){
					$pay_now_url = $order->get_cancel_order_url_raw();
				}else if( $status == 'success' ){
					$request = $this->request( $orderNo );
					if( $request ){
						if (
							isset( $request->apiResponse->responseCode )
							&& 'PC-B050000' === $request->apiResponse->responseCode
						) {
							$order->update_status( 'completed' );
							$pay_now_url = $order->get_checkout_order_received_url();
						}else{
							$pay_now_url = $order->get_cancel_order_url_raw();
						}
					}else{
						$pay_now_url = $order->get_cancel_order_url_raw();
					}
				}else if( $status == 'failed' ){
					$order->update_status( 'failed' );
					$pay_now_url = $order->get_checkout_order_received_url();
				}else{
					$order->update_status( 'processing' );
					$pay_now_url = $order->get_checkout_order_received_url();
				}
			}
			
			wp_redirect( esc_url( $pay_now_url ) );
			exit;
		}
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