<?php
if (!defined('ABSPATH')) {
	exit();
}
/**
 * PostFinance Checkout WooCommerce
 *
 * This WooCommerce plugin enables to process payments with PostFinance Checkout (https://www.postfinance.ch).
 *
 * @author customweb GmbH (http://www.customweb.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */
/**
 * This class handles the customer returns
 */
class WC_PostFinanceCheckout_Return_Handler {

	public static function init(){
		add_action('woocommerce_api_postfinancecheckout_return', array(
			__CLASS__,
			'process' 
		));
	}

	public static function process(){
		if (isset($_GET['action']) && isset($_GET['order_key']) && isset($_GET['order_id'])) {
			$order_key = $_GET['order_key'];
			$order_id = absint($_GET['order_id']);
			$order = WC_Order_Factory::get_order($order_id);
			$action = $_GET['action'];
			if ($order->get_id() === $order_id && $order->get_order_key() === $order_key) {
				switch ($action) {
					case 'success':
						self::process_success($order);
						break;
					case 'failure':
						self::process_failure($order);
						break;
					default:
				}
			}
		}
		wp_redirect(home_url('/'));
		exit();
	}

	protected static function process_success(WC_Order $order){
	    $transaction_service = WC_PostFinanceCheckout_Service_Transaction::instance();
		
		$transaction_service->wait_for_transaction_state($order, 
				array(
				    \PostFinanceCheckout\Sdk\Model\TransactionState::CONFIRMED,
				    \PostFinanceCheckout\Sdk\Model\TransactionState::PENDING,
				    \PostFinanceCheckout\Sdk\Model\TransactionState::PROCESSING 
				), 5);
		$gateway = wc_get_payment_gateway_by_order($order);
		wp_redirect($gateway->get_return_url($order));
		exit();
	}

	protected static function process_failure(WC_Order $order){
	    $transaction_service = WC_PostFinanceCheckout_Service_Transaction::instance();
		$transaction_service->wait_for_transaction_state($order, array(
		    \PostFinanceCheckout\Sdk\Model\TransactionState::FAILED 
		), 5);
		$transaction = WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_order_id($order->get_id());
		
		$failure_reason = $transaction->get_failure_reason();
		if ($failure_reason !== null) {
		    WooCommerce_PostFinanceCheckout::instance()->add_notice($failure_reason, 'error');
		}
		wp_redirect(wc_get_checkout_url());
		exit();
	}
}
WC_PostFinanceCheckout_Return_Handler::init();
