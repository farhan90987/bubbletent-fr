/**
 * Side Cart JavaScipt
 */

(function ($, ajaxVar) {
	"use strict";

	var cfw_sideCart = {
		init: function () {
			$( ".cfw-sc-modal" )
				.on( "click", ".cfw-sc-opac", this.openBox )
				.on( "click", ".close-panel", this.closeBox )
				.on( "click", ".cfw-sc-remove", this.removeItem )
				.on( "change", ".cfw-sc-qty", this.addQuantity )
				.on( "click", ".cfw-sc-toggle-slider", this.showCouponInput )
				.on( "submit", "form.cfw-sc-sl-apply-coupon", this.applyCoupon )
				.on( "click", ".cfw-sc-icon-cross", this.removeCoupon )
				.on( "click", ".cfw-blocker", this.closeBox );

			// Single Page added to cart
			$( ".single_add_to_cart_button" ).on( "click", this.spAddedtoCart );

			// Woocommerce triggers
			$( document.body ).on( "added_to_cart", this.cartRefreshed );
			$( document.body ).on( "updated_cart_totals", this.updatedCart );
		},
		openBox: function () {
			$( ".cfw-sc-modal" ).find( ".cfw-sc-container" ).addClass( "open" );
			$( ".cfw-sc-modal" )
				.find( ".cfw-sc-opac .cfw-sc-total" )
				.removeClass( "open" );
		},
		closeBox: function () {
			$( ".cfw-sc-modal" ).find( ".cfw-sc-container" ).removeClass( "open" );
			$( ".cfw-sc-modal" )
				.find( ".cfw-sc-opac .cfw-sc-total" )
				.addClass( "open" );
		},
		addCartContent: function (html) {
			$( "body" ).trigger( "update_checkout" );
			$( ".cfw-sc-modal" )
				.find(
					".cfw-cart-notice, .cart-items, .cart-meta, .cfw-sc-empty-cart"
				)
				.remove();
			$( ".cfw-sc-modal" ).find( "header" ).after( html );
		},
		refreshTotal: function (amount) {
			$( ".cfw-sc-total" ).find( ".cfw-total-qty" ).html( amount );
		},
		showCouponInput: function () {
			$( ".cfw-sc-modal" ).find( ".cfw-coupon-input" ).toggle( "slow" );
		},
		sectionLoader: function (enable) {
			var $loaderSec = $( ".cfw-sc-modal" ).find(
				".cfw-sc-container .cfw-sc-base > section"
			);
			if (enable) {
				$loaderSec.addClass( "loading" );
			} else {
				$loaderSec.removeClass( "loading" );
			}
		},
		spAddedtoCart: function (e) {
			e.preventDefault();
			var $thisbutton  = $( this ),
				$form        = $thisbutton.closest( "form.cart" ),
				id           = $thisbutton.val(),
				product_qty  = $form.find( "input[name=quantity]" ).val() || 1,
				product_id   = $form.find( "input[name=product_id]" ).val() || id,
				variation_id =
					$form.find( "input[name=variation_id]" ).val() || 0;
			var data         = {
				action: "sc_woo_ajax_add_to_cart",
				product_id: product_id,
				product_sku: "",
				quantity: product_qty,
				variation_id: variation_id,
				security: ajaxVar.nonce.add_to_cart,
			};
			$.ajax(
				{
					type: "post",
					url: ajaxVar.ajax_url,
					data: data,
					beforeSend: function () {
						$thisbutton.removeClass( "added" ).addClass( "loading" );
					},
					complete: function () {
						$thisbutton.addClass( "added" ).removeClass( "loading" );
					},
					success: function (response) {
						if (response.error & response.product_url) {
							window.location = response.product_url;
							return;
						} else {
							$( document.body ).trigger(
								"added_to_cart",
								[
								response.fragments,
								response.cart_hash,
								$thisbutton,
								]
							);
						}
					},
					error( err ) {
						console.log( '[Cashier] AJAX add to cart error: ', err );
					},
				}
			);
		},
		applyCoupon: function (e) {
			e.preventDefault();
			const self = cfw_sideCart,
				coupon = $( this ).find( "input" ).val();
			self.__updateCoupon( coupon, "add" );
		},
		removeCoupon: function () {
			const self = cfw_sideCart,
				coupon = $( this ).attr( "data-coupon" );
			self.__updateCoupon( coupon, "remove" );
		},
		__updateCoupon: function (coupon, act) {
			const self     = cfw_sideCart;
			const postData = {
				action: "sc_add_coupon",
				act,
				coupon,
			};
			self.__ajax( postData, "coupon" );
		},
		updatedCart: function () {
			const self = cfw_sideCart;

			self.cartRefreshed( false );
		},
		cartRefreshed: function (isOpen = true) {
			const self = cfw_sideCart;

			self.__cartRefreshed();
			if (isOpen) {
				self.openBox();
			}
		},
		__cartRefreshed: function () {
			const self     = cfw_sideCart;
			const postData = {
				action: "sc_cart_refreshed",
			};
			self.__ajax( postData, "cart_refresh" );
		},

		addQuantity: function () {
			const self  = cfw_sideCart,
				itemKey = $( this ).closest( ".cfw-sc-product" ).attr( "data-key" );
			self.__addQuantity( itemKey, $( this ).val() );
		},
		__addQuantity: function (item_key, quantity) {
			const self     = cfw_sideCart;
			const postData = {
				action: "sc_product_update",
				item_key,
				quantity,
			};
			self.__ajax( postData, "add_quantity" );
		},

		removeItem: function (e) {
			e.preventDefault();
			const self  = cfw_sideCart,
				itemKey = $( this ).closest( ".cfw-sc-product" ).attr( "data-key" );
			self.__removeItem( itemKey );
		},
		__removeItem: function (item_key) {
			const self     = cfw_sideCart;
			const postData = {
				action: "sc_product_remove",
				item_key,
			};
			self.__ajax( postData, "remove_cart" );
		},
		__ajax( data, security ) {
			const self = cfw_sideCart;

			self.sectionLoader( true );
			$.ajax(
				{
					url: ajaxVar.ajax_url,
					type: "POST",
					data: { ...data, security: ajaxVar.nonce[security] },
					success( res ) {
						if (res.success) {
							self.addCartContent( res.data.content );
							self.refreshTotal( res.data.total );
						}
						self.sectionLoader( false );
					},
				}
			);
		},
	};

	cfw_sideCart.init();
})( jQuery, cfw_ajax_vars );
