/**
 * Buy Now JavaScipt
 *
 * @package woocommerce-buy-now/assets/js
 */

jQuery(
	function( $ ) {

		$( 'label[for="wc_buy_now_set_for"]' ).parent().hide();
		$( 'td.forminp-radio' ).attr( 'colspan', 2 );

		$( 'label[for="wc_buy_now_express_checkout_button_text"], label[for="wc_buy_now_product_categories"], label[for="wc_buy_now_storewide_coupons"], label[for="wc_buy_now_storewide_shipping_method"], tr.single_select_page th ' ).closest( 'th' ).css( { 'padding-left' : '5em' } );
		$( '.sa_bn_storewide_action_label label' ).css( 'font-weight', 'bold' ).css( 'color', $( '.form-table th' ).css( 'color' ) );

		function manage_options( set_for ) {

			if ( set_for == 'standard' ) {
				$( '#wc_buy_now_express_checkout_button_text, #wc_buy_now_product_categories, .sa_bn_additional_button_action_label, #wc_buy_now_storewide_coupons, #wc_buy_now_storewide_shipping_method, #wc_buy_now_storewide_redirect_to_page' ).closest( 'tr' ).hide();
			} else if ( set_for == 'express-checkout' ) {
				$( '#wc_buy_now_is_quick_checkout, #wc_buy_now_product_categories, .sa_bn_additional_button_action_label, #wc_buy_now_storewide_coupons, #wc_buy_now_storewide_shipping_method, #wc_buy_now_storewide_redirect_to_page' ).closest( 'tr' ).hide();
				$( '#wc_buy_now_express_checkout_button_text' ).closest( 'tr' ).show();
			} else if ( set_for == 'buy-now' ) {
				$( '#wc_buy_now_express_checkout_button_text, #wc_buy_now_product_categories, .sa_bn_additional_button_action_label, #wc_buy_now_storewide_coupons, #wc_buy_now_storewide_shipping_method, #wc_buy_now_storewide_redirect_to_page' ).closest( 'tr' ).hide();
				$( '#wc_buy_now_is_quick_checkout' ).closest( 'tr' ).show();
			} else {
				$( '#wc_buy_now_express_checkout_button_text' ).closest( 'tr' ).hide();
				$( '#wc_buy_now_is_quick_checkout, #wc_buy_now_product_categories, .sa_bn_additional_button_action_label, #wc_buy_now_storewide_coupons, #wc_buy_now_storewide_shipping_method, #wc_buy_now_storewide_redirect_to_page' ).closest( 'tr' ).show();
			}
		}

		var set_for = $( 'input[name=wc_buy_now_set_for]:checked' ).val();
		manage_options( set_for );

		$( 'input[name=wc_buy_now_set_for]' ).on(
			'change',
			function() {
				set_for = $( 'input[name=wc_buy_now_set_for]:checked' ).val();
				manage_options( set_for );
			}
		);

		$( 'input[value="standard"]' ).parent().parent().append( ' <a class="thickbox" style="vertical-align: text-bottom;" href="https://docs.woocommerce.com/wp-content/uploads/2020/07/cashier-buy-now-simple-product-setting.png?TB_iframe=true"><small>[' + buy_now_translation.preview_text + ']</small></a> <span class="woocommerce-help-tip" data-tip="' + buy_now_translation.standard_tip_text + '"></span>' );
		$( 'input[value="express-checkout"]' ).parent().parent().append( ' <a class="thickbox" style="vertical-align: text-bottom;" href="https://docs.woocommerce.com/wp-content/uploads/2020/07/cashier-buy-now-express-checkout.png?TB_iframe=true"><small>[' + buy_now_translation.preview_text + ']</small></a> <span class="woocommerce-help-tip" data-tip="' + buy_now_translation.express_checkout_tip_text + '"></span>' );
		$( 'input[value="buy-now"]' ).parent().parent().append( ' <a class="thickbox" style="vertical-align: text-bottom;" href="https://docs.woocommerce.com/wp-content/uploads/2020/07/cashier-buy-now-only.png?TB_iframe=true"><small>[' + buy_now_translation.preview_text + ']</small></a> <span class="woocommerce-help-tip" data-tip="' + buy_now_translation.buy_now_tip_text + '"></span>' );
		$( 'input[value="and-buy-now"]' ).parent().parent().append( ' <a class="thickbox" style="vertical-align: text-bottom;" href="https://docs.woocommerce.com/wp-content/uploads/2020/07/cashier-buy-now-also.png?TB_iframe=true"><small>[' + buy_now_translation.preview_text + ']</small></a> <span class="woocommerce-help-tip" data-tip="' + buy_now_translation.and_buy_now_tip_text + '"></span>' );
		$( 'input#wc_buy_now_is_two_click' ).parent().parent().find( 'p.description' ).append( ' <a class="thickbox" style="vertical-align: text-bottom;" href="https://docs.woocommerce.com/wp-content/uploads/2020/07/cashier-buy-now-2-step.png?TB_iframe=true"><small>[' + buy_now_translation.preview_text + ']</small></a>' );
	}
);
