/**
 * Buy Now Admin JavaScipt
 *
 * @package woocommerce-buy-now/assets/js
 */

jQuery(
	function( $ ) {

		var bn_product_actions = {

			init: function() {
				$( '#woocommerce-product-data' )
					.on( 'change', 'select#product-type', this.trigger_product_type_change )
					.on( 'change', 'input[name=sa_wc_bn_action]', this.get_seleted_action )
					.on( 'bn_action_updated', this.enable_disable_bn_fields )
					.on( 'update_bn_link', this.set_bn_link )
					.on( 'product_type_change', this.hide_show_bn_link_field )
					.on( 'change', '#sa_wc_buy_now_coupon, #sa_wc_buy_now_shipping_method, #sa_wc_buy_now_redirect_page', this.trigger_update )
					.on( 'woocommerce_variations_loaded', this.set_bn_link )
					.on( 'click', '.product_data .variations_options', this.may_be_trigger_variations_loaded );

				$( document.body ).on( 'woocommerce_variations_added', this.set_bn_link );

				this.set_product_type();
				this.enable_disable_bn_fields();
				this.set_bn_link();
				this.hide_show_bn_link_field();

			},

			set_product_type: function() {
				product_type = $( '#woocommerce-product-data select#product-type' ).find( 'option:selected' ).val();
			},

			set_bn_link: function() {
				let selected_coupons = bn_product_actions.get_selected_coupons(),
					shipping_method  = bn_product_actions.get_selected_shipping_method(),
					redirect_page    = bn_product_actions.get_selected_redirect_page();

				let product_id  = bn_admin_params.product_id,
					is_variable = bn_admin_params.is_variable,
					variations  = bn_admin_params.product_variations;

				let with_cart = bn_admin_params.with_cart;

				let product_types_to_exclude = bn_admin_params.exluded_product_types;

				if ( $.inArray( product_type, product_types_to_exclude ) < 0 ) {
					if ( 'no' === is_variable ) {
						let bn_link = bn_product_actions.get_bn_link( product_id, selected_coupons, shipping_method, redirect_page, with_cart );

						$( '#sa_wc_buy_now_link_' + product_id ).text( bn_link );
					} else if ( 'yes' === is_variable && variations.length > 0 ) {
						for ( index in variations ) {
							let variation_id = variations[index];
							let bn_link      = bn_product_actions.get_bn_link( variation_id, selected_coupons, shipping_method, redirect_page, with_cart );

							$( '#sa_wc_buy_now_link_' + variation_id ).text( bn_link );
						}
					}
				}

			},

			get_selected_coupons: function() {
				return $( '#sa_wc_buy_now_coupon' ).val();
			},

			get_selected_shipping_method: function() {
				return $( '#sa_wc_buy_now_shipping_method' ).find( 'option:selected' ).val();
			},

			get_selected_redirect_page: function() {
				return $( '#sa_wc_buy_now_redirect_page' ).find( 'option:selected' ).val();
			},

			trigger_product_type_change: function() {
				bn_product_actions.set_product_type()
				$( '#woocommerce-product-data' ).trigger( 'product_type_change' );
				$( '#woocommerce-product-data' ).trigger( 'update_bn_link' );
			},

			hide_show_bn_link_field: function() {
				var product_type = $( '#woocommerce-product-data select#product-type' ).find( 'option:selected' ).val(),
					product_id   = bn_admin_params.product_id;

				if ( 'variable' === product_type || 'variable-subscription' === product_type ) {
					$( 'p.sa_wc_buy_now_link_' + product_id + '_field' ).hide();
					$( 'p.bn-note-varition' ).removeClass( 'hidden' );
				}

				if ( 'simple' === product_type || 'subscription' === product_type || 'bundle' === product_type ) {
					$( 'p.sa_wc_buy_now_link_' + product_id + '_field' ).show();
					$( 'p.bn-note-varition' ).addClass( 'hidden' );
				}
			},

			get_bn_link: function( product_id, selected_coupons, shipping_method, redirect_page, with_cart  ) {
				generated_url = '';

				if ( null !== product_id ) {
					var generated_url = bn_admin_params.site_url + '/?';

					generated_url += 'buy-now=' + product_id;

					generated_url += '&qty=1';

					if (  'undefined' !== typeof selected_coupons && '' !== selected_coupons && null !== selected_coupons ) {
						generated_url += '&coupon=' + selected_coupons;
					}

					if ( 'undefined' !== typeof shipping_method && '' !== shipping_method && null !== shipping_method ) {
						generated_url += '&ship-via=' + shipping_method;
					}

					if ( 'undefined' !== typeof redirect_page && '' !== redirect_page && null !== redirect_page ) {
						generated_url += '&page=' + redirect_page;
					}

					generated_url += '&with-cart=' + with_cart;
				}

				return generated_url;
			},

			enable_disable_bn_fields: function() {
				var bn_action = $( '.sa_wc_bn_action_field .wc-radios input[name=sa_wc_bn_action]:checked' ).val();
				is_disabled   = ( 'bn_only' === bn_action || 'bn_both' === bn_action ) ? false : true;

				$( '#sa_wc_buy_now_coupon, #sa_wc_buy_now_shipping_method, #sa_wc_buy_now_redirect_page' ).prop( 'disabled', is_disabled );
			},

			get_seleted_action: function() {
				$( '#woocommerce-product-data' ).trigger( 'bn_action_updated' );
			},

			may_be_trigger_variations_loaded: function() {
				if ( 'undefined' !== typeof bn_fields_updated && null !== bn_fields_updated && true === bn_fields_updated ) {
					$( '#woocommerce-product-data' ).trigger( 'woocommerce_variations_loaded' );
					bn_fields_updated = false;
				}
			},

			trigger_update: function() {
				$( '#woocommerce-product-data' ).trigger( 'update_bn_link' );
				bn_fields_updated = true;
			},
		}

		bn_product_actions.init();
	}
);
