<?php

defined( 'ABSPATH' ) || exit;

/**
 * Settings for Himalayan Bank Gateway.
 */
return array(
	'enabled'           => array(
		'title'   => __( 'Enable/Disable', 'woo-hbl-payment' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable Himalayan Bank Payment', 'woo-hbl-payment' ),
		'default' => 'yes',
	),
	'title'             => array(
		'title'       => __( 'Title', 'woo-hbl-payment' ),
		'type'        => 'text',
		'desc_tip'    => true,
		'description' => __( 'This controls the title which the user sees during checkout.', 'woo-hbl-payment' ),
		'default'     => __( 'Himalayan Bank', 'woo-hbl-payment' ),
	),
	'description'       => array(
		'title'       => __( 'Description', 'woo-hbl-payment' ),
		'type'        => 'text',
		'desc_tip'    => true,
		'description' => __( 'This controls the description which the user sees during checkout.', 'woo-hbl-payment' ),
		'default'     => __( 'Pay via Himalayan Bank Credit Card in real-time.', 'woo-hbl-payment' ),
	),
	'merchant_id'       => array(
		'title'       => __( 'Merchant ID (Office ID)', 'woo-hbl-payment' ),
		'type'        => 'text',
		'desc_tip'    => true,
		'description' => __( 'Please enter your Himalayan Bank Merchant ID (Office ID).', 'woo-hbl-payment' ),
		'default'     => '',
	),
	'merchant_password' => array(
		'title'       => __( 'Secret (API) Key', 'woo-hbl-payment' ),
		'type'        => 'text',
		'desc_tip'    => true,
		'description' => __( 'Please enter your Himalayan Bank API/Secret Key. This is needed in order to take payment.', 'woo-hbl-payment' ),
		'default'     => '',
	),
	'advanced'          => array(
		'title'       => __( 'Advanced options', 'woo-hbl-payment' ),
		'type'        => 'title',
		'description' => '',
	),
	'test_mode'         => array(
		'title'       => __( 'Sandbox mode', 'woo-hbl-payment' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable Sandbox Mode', 'woo-hbl-payment' ),
		'default'     => 'no',
		'description' => __( 'If enabled, Sandbox/Test mode Merchant ID and API Key should be used.' ),
	),
	'invoice_prefix'    => array(
		'title'       => __( 'Invoice prefix', 'woo-hbl-payment' ),
		'type'        => 'text',
		'desc_tip'    => true,
		'description' => __( 'Please enter a prefix for your invoice numbers. If you use your Himalayan Bank account for multiple stores ensure this prefix is unique as Himalayan Bank will not allow orders with the same invoice number.', 'woo-hbl-payment' ),
		'default'     => 'WC-',
	),
	'debug'             => array(
		'title'       => __( 'Debug log', 'woo-hbl-payment' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable logging', 'woo-hbl-payment' ),
		'default'     => 'no',
		'description' => sprintf( __( 'Log Himalayan Bank events, such as IPN requests, inside <code>%s</code>', 'woo-hbl-payment' ), wc_get_log_file_path( 'Himalayan Bank' ) ),
	),
);