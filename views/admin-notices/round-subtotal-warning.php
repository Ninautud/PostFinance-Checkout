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
?>
<div class="error notice notice-error">
	<p><?php _e( 'The PostFinance Checkout payment methods are not available, if the taxes are rounded at subtotal level. Please disable the \'Round tax at subtotal level, instead of rounding per line\' in the tax settings to enable the PostFinance Checkout payment methods.', 'woo-postfinancecheckout' ); ?></p>
</div>