jQuery( document ).ready( function(){

	jQuery( '.lexoffice-woocomerce-default' ).click( function() {

		// only if button has not been clicked before
		if ( ! ( jQuery( this ).hasClass( 'lexoffice-woocommerce-x' ) || jQuery( this ).hasClass( 'lexoffice-woocommerce-error' ) || jQuery( this ).hasClass( 'lexoffice-woocommerce-yes' ) ) ) {
			return;
		}

		// get order id
		var order_id = jQuery( this ).attr( 'data-order-id' );

		// before doing ajax
		jQuery( this ).removeClass( 'lexoffice-woocommerce-x' ).removeClass( 'lexoffice-woocommerce-error dashicons dashicons-no lexoffice-woocommerce-yes' ).addClass( 'lexoffice-woocommerce-loader' );

		// set jQuery( this ) to a variable so we can use it in jQuery.post
		var button = jQuery( this );

		// set args
		var data = {
			action: 'lexoffice_woocommerce_edit_shop_order',
			security: lexoffice_ajax.nonce,
			order_id: order_id
		};

		// refund?
		if ( jQuery( this ).attr( 'data-refund-id' ) ) {
			var refund_id = jQuery( this ).attr( 'data-refund-id' );
			data.refund_id = refund_id;
			data.action = 'lexoffice_woocommerce_edit_shop_order_refund';
		}

		let error_object = jQuery( this ).closest( 'tr' );
		let count = jQuery( error_object ).children( ':not(.hidden)' ).length;

		// do ajax
		jQuery.post( lexoffice_ajax.url, data, function( response ) {
			
			// error handling
			if ( response != 'SUCCESS' ) {
				jQuery( button ).html( '' );
				jQuery( button ).removeClass( 'lexoffice-woocommerce-loader lexoffice-not-completed' ).addClass( 'lexoffice-woocommerce-error dashicons dashicons-no' );
				
				var error_message = '<tr class="error-notice-german-market lexoffice"><td colspan="' + count + '">' + response + '</td></tr>';
				jQuery( error_message ).insertAfter( error_object ).hide().slideDown( 'fast' );

			// success handling
			} else {

				jQuery( button ).removeClass( 'lexoffice-woocommerce-loader' ).addClass( 'lexoffice-woocommerce-yes dashicons dashicons-yes' );
				jQuery( button ).html( '' );

			}

		} );

	});

});
