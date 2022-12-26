<?php

namespace HBLPayment;

/**
 * Request to Himalayan Bank.
 */
class Request {

	/**
	 * Pointer to gateway making the request.
	 *
	 * @since 2.0.0
	 *
	 * @var WC_Gateway_HBL_Payment
	 */
	protected $gateway;

	/**
	 * Endpoint for requests from Himalayan Bank.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	protected $notify_url;

	/**
	 * Endpoint for requests to Himalayan Bank.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	protected $endpoint;

	/**
	 * Constructor.
	 *
	 * @since 2.0.0
	 *
	 * @param WC_Gateway_HBL_Payment $gateway Gateway class.
	 */
	public function __construct( $gateway ) {
		$this->gateway    = $gateway;
		$this->notify_url = WC()->api_request_url( 'WC_Gateway_HBL_Payment' ) . "?payment=%s";
	}

	/**
	 * Get the Himalayan Bank request URL for an order or receive a response.
	 *
	 * @param  WC_Order $order   Order object.
	 *
	 * @since 2.0.0
	 *
	 * @return object The result of the request.
	 */
	public function result( $order ) {

		$test_mode = $this->gateway->get_option( 'test_mode' );
		
		$this->endpoint = 'yes' === $test_mode ? 
			'https://core.demo-paco.2c2p.com/api/1.0/Payment/prePaymentUi' 
		: 
			'https://core.paco.2c2p.com/api/1.0/Payment/prePaymentUi';

		$hbl_payment_args = $this->get_hbl_payment_args( $order );

		\WC_Gateway_HBL_Payment::log( 'Himalayan Bank Payment Request Args for order ' . $order->get_order_number() . ': ' . wc_print_r( $hbl_payment_args, true ) );

		$body = wp_json_encode( $hbl_payment_args );
		$options = array(
			'body'        => $body,
			'headers'     => array(
				'Content-Type' => 'application/json; charset=utf-8',
				'apiKey'       => $this->gateway->get_option( 'merchant_password' ),
			)
		);
		$response = wp_remote_post( $this->endpoint, $options );

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			\WC_Gateway_HBL_Payment::log( 'Response error for order ' . $order->get_order_number() . ': ' . wc_print_r( $error_message, true ) );
		} else {
			$body = wp_remote_retrieve_body( $response );
			$body = json_decode( $body );
			\WC_Gateway_HBL_Payment::log( 'Response details for ' . $order->get_order_number() . ': ' . wc_print_r( $body, true ) );
			return $body;
		}
	}

	/**
	 * Get Himalayan Bank Pay Args for passing to Himalayan Bank Pay.
	 *
	 * @param  WC_Order $order Order object.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function get_hbl_payment_args( $order ) {

		\WC_Gateway_HBL_Payment::log( 'Generating payment form for order ' . $order->get_order_number() . '. Notify URL: ' . $this->notify_url );

		return array(
			'apiRequest'                => array(
				'requestMessageID' => $this->Guid(),
				'requestDateTime'  => date( 'Y-m-d\TH:i:s.v\Z' ),
				'language'         => 'en-US',
			),
			'officeId'                  => $this->gateway->get_option( 'merchant_id' ),
			'orderNo'                   => $order->get_order_number(),
			'productDescription'        => 'product desc.',
			'paymentType'               => 'CC',
			'paymentCategory'           => 'ECOM',
			'storeCardDetails'          => array(
				'storeCardFlag'      => 'N',
				'storedCardUniqueID' => '{{guid}}',
			),
			'installmentPaymentDetails' => array(
				'ippFlag'           => 'N',
				'installmentPeriod' => 0,
				'interestType'      => null,
			),
			'mcpFlag'                   => 'N',
			'request3dsFlag'            => 'N',
			'transactionAmount'         => array(
				'amountText'    => sprintf( '%012d', $order->get_total() * 100 ),
				'currencyCode'  => $order->get_currency(),
				'decimalPlaces' => 2,
				'amount'        => $order->get_total(),
			),
			'notificationURLs'          => array(
				'confirmationURL' => sprintf($this->notify_url, "success"),
				'failedURL'       => sprintf($this->notify_url, "failed"),
				'cancellationURL' => sprintf($this->notify_url, "cancel"),
				'backendURL'      => sprintf($this->notify_url, "backend"),
			),
			'purchaseItems'             => array(
				array(
					'purchaseItemType'        => 'ticket',
					'referenceNo'             => $order->get_order_number(),
					'purchaseItemDescription' => 'Product Description',
					'purchaseItemPrice'       => array(
						'amountText'    => sprintf( '%012d', $order->get_total() * 100 ),
						'currencyCode'  => $order->get_currency(),
						'decimalPlaces' => 2,
						'amount'        => $order->get_total(),
					),
					'subMerchantID'           => 'string',
					'passengerSeqNo'          => 1,
				),
			),
		);
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