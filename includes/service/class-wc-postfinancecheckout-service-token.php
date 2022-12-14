<?php
if (!defined('ABSPATH')) {
	exit(); // Exit if accessed directly.
}
/**
 * PostFinance Checkout WooCommerce
 *
 * This WooCommerce plugin enables to process payments with PostFinance Checkout (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html).
 *
 * @author wallee AG (http://www.wallee.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */
/**
 * This service provides functions to deal with PostFinance Checkout tokens.
 */
class WC_PostFinanceCheckout_Service_Token extends WC_PostFinanceCheckout_Service_Abstract {
	
	/**
	 * The token API service.
	 *
	 * @var \PostFinanceCheckout\Sdk\Service\TokenService
	 */
	private $token_service;
	
	/**
	 * The token version API service.
	 *
	 * @var \PostFinanceCheckout\Sdk\Service\TokenVersionService
	 */
	private $token_version_service;

	public function update_token_version($space_id, $token_version_id){
		$token_version = $this->get_token_version_service()->read($space_id, $token_version_id);
		$this->update_info($space_id, $token_version);
	}

	public function update_token($space_id, $token_id){
	    $query = new \PostFinanceCheckout\Sdk\Model\EntityQuery();
	    $filter = new \PostFinanceCheckout\Sdk\Model\EntityQueryFilter();
	    $filter->setType(\PostFinanceCheckout\Sdk\Model\EntityQueryFilterType::_AND);
		$filter->setChildren(
				array(
					$this->create_entity_filter('token.id', $token_id),
				    $this->create_entity_filter('state', \PostFinanceCheckout\Sdk\Model\TokenVersionState::ACTIVE) 
				));
		$query->setFilter($filter);
		$query->setNumberOfEntities(1);
		$token_versions = $this->get_token_version_service()->search($space_id, $query);
		if (!empty($token_versions)) {
			$this->update_info($space_id, current($token_versions));
		}
		else {
		    $info = WC_PostFinanceCheckout_Entity_Token_Info::load_by_token($space_id, $token_id);
			if ($info->get_id()) {
				$info->delete();
			}
		}
	}

	protected function update_info($space_id, \PostFinanceCheckout\Sdk\Model\TokenVersion $token_version){
		/* @var WC_PostFinanceCheckout_Entity_Token_Info $info */
	    $info = WC_PostFinanceCheckout_Entity_Token_Info::load_by_token($space_id, $token_version->getToken()->getId());
		if (!in_array($token_version->getToken()->getState(), 
				array(
				    \PostFinanceCheckout\Sdk\Model\CreationEntityState::ACTIVE,
				    \PostFinanceCheckout\Sdk\Model\CreationEntityState::INACTIVE 
				))) {
			if ($info->get_id()) {
				$info->delete();
			}
			return;
		}
		
		$info->set_customer_id($token_version->getToken()->getCustomerId());
		$info->set_name($token_version->getName());
		
		/* @var WC_PostFinanceCheckout_Entity_Method_Configuration $paymentMethod */
		
		$payment_method = WC_PostFinanceCheckout_Entity_Method_Configuration::load_by_configuration($space_id, 
				$token_version->getPaymentConnectorConfiguration()->getPaymentMethodConfiguration()->getId());
		$info->set_payment_method_id($payment_method->get_id());
		$info->set_connector_id($token_version->getPaymentConnectorConfiguration()->getConnector());
		
		$info->set_space_id($space_id);
		$info->set_state($token_version->getToken()->getState());
		$info->set_token_id($token_version->getToken()->getId());
		$info->save();
	}

	public function delete_token($space_id, $token_id){
		$this->get_token_service()->delete($space_id, $token_id);
	}
	
	/**
	 * Returns the token API service.
	 *
	 * @return \PostFinanceCheckout\Sdk\Service\TokenService
	 */
	protected function get_token_service(){
		if ($this->token_service == null) {
		    $this->token_service = new \PostFinanceCheckout\Sdk\Service\TokenService(WC_PostFinanceCheckout_Helper::instance()->get_api_client());
		}
		
		return $this->token_service;
	}

	/**
	 * Returns the token version API service.
	 *
	 * @return \PostFinanceCheckout\Sdk\Service\TokenVersionService
	 */
	protected function get_token_version_service(){
		if ($this->token_version_service == null) {
		    $this->token_version_service = new \PostFinanceCheckout\Sdk\Service\TokenVersionService(WC_PostFinanceCheckout_Helper::instance()->get_api_client());
		}
		
		return $this->token_version_service;
	}
}