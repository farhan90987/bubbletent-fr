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

		let used_wc_order_id = order_id;
		let error_object = jQuery( this ).closest( 'tr' );
		let count = jQuery( error_object ).children( ':not(.hidden)' ).length;

		// refund?
		if ( jQuery( this ).attr( 'data-refund-id' ) ) {
			var refund_id = jQuery( this ).attr( 'data-refund-id' );
			data.refund_id = refund_id;
			data.action = 'lexoffice_woocommerce_edit_shop_order_refund';
			used_wc_order_id = refund_id;
		}

		// do ajax
		jQuery.post( lexoffice_ajax.url, data, function( response ) {

			var success = false;
			let show_warning = false;
			let show_warning_meta_removed = false;

			if ( response.length >= 7 ) {
				var message = response.substring( 0, 7 );
				
				var document_id = '';

				if (  message == 'SUCCESS' ) {
					success = true;
				} else if ( 'EXISTS_' === message || 'NOTEX_A' === message ) {
					show_warning = true;
					message = response.substring( 7 );
				} else if ( 'NOTEX_B' === message ) {
					show_warning_meta_removed = true;
					message = response.substring( 7 );
				}

				if ( response.length > 7 ) {
					document_id = response.substring( 7 );
				}
			}

			if ( show_warning ) {

				var error_message = '<tr class="error-notice-german-market lexoffice"><td colspan="' + count + '">' + message + '</td></tr>';
				jQuery( error_message ).insertAfter( error_object ).hide().slideDown( 'fast' );
				jQuery( button ).removeClass( 'lexoffice-woocommerce-loader lexoffice-not-completed' ).addClass( 'lexoffice-woocommerce-yes dashicons dashicons-yes' );

			} else if ( show_warning_meta_removed ) {

				var error_message = '<tr class="error-notice-german-market lexoffice"><td colspan="' + count + '">' + message + '</td></tr>';
				jQuery( error_message ).insertAfter( error_object ).hide().slideDown( 'fast' );
				jQuery( button ).removeClass( 'lexoffice-woocommerce-loader lexoffice-not-completed' ).addClass( 'lexoffice-woocomerce-default lexoffice-woocommerce-x' );

				var invoice_download_button = jQuery( button ).parent().find( '.lexoffice-invoice-pdf' );
				jQuery( invoice_download_button ).hide();
				jQuery( '#lexoffice-invoice-number-' + used_wc_order_id ).html( '' );

			// error handling
			} else if ( ! success ) {
				jQuery( button ).html( '' );
				jQuery( button ).removeClass( 'lexoffice-woocommerce-loader lexoffice-not-completed' ).addClass( 'lexoffice-woocommerce-error dashicons dashicons-no' );
				var error_message = '<tr class="error-notice-german-market lexoffice"><td colspan="' + count + '">' + response + '</td></tr>';
				jQuery( error_message ).insertAfter( error_object ).hide().slideDown( 'fast' );

			// success handling
			} else {

				jQuery( button ).removeClass( 'lexoffice-woocommerce-loader' ).addClass( 'lexoffice-woocommerce-yes dashicons dashicons-yes' );
				jQuery( button ).html( '' );
				
				var invoice_download_button = jQuery( button ).parent().find( '.lexoffice-invoice-pdf' );

				if ( document_id != '' ) {

					jQuery( invoice_download_button ).attr( 'data-document-id', document_id );

					var href 		= jQuery( invoice_download_button ).attr( 'href' );
					var pos_sec		= href.search( '&_wpnonce=' );
					var sec 		= href.substring( pos_sec );

					var pos_doc		= href.search( '&document_id' );
					var doc 		= href.substring( 0, pos_doc );

					var new_href	= doc + '&document_id=' + document_id + '&order_id=' + used_wc_order_id + sec;

					jQuery( invoice_download_button ).attr( 'href', new_href );
					jQuery( invoice_download_button ).show();

				} else {
					jQuery( invoice_download_button ).attr( 'data-document-id', '' );
					jQuery( invoice_download_button ).hide();
				}

				// new ajax request to show invoice number
				var refresh_invoice_number = {
					action: 'lexoffice_woocommerce_refresh_invoice_number',
					security: lexoffice_ajax.nonce,
					order_id: used_wc_order_id
				};
					
				jQuery.post( lexoffice_ajax.url, refresh_invoice_number, function( response ) {
					if ( jQuery( '#lexoffice-invoice-number-' + refresh_invoice_number.order_id ).length ) {
						jQuery( '#lexoffice-invoice-number-' + refresh_invoice_number.order_id ).html( response );
					}
				});
			}

		} );

	});

});
