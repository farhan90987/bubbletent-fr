jQuery( document ).ready( function() { 

	jQuery( '.marketpress-atomion-gm-b2b-notice-in-gm button.notice-dismiss' ).ready( function() {

		jQuery( '.marketpress-atomion-gm-b2b-notice-in-gm button.notice-dismiss' ).on( 'click', function() {
			
			var data = {
				'action': 'gm_dismiss_marketpress_notice',
				'nonce' : gm_marketpress_ajax_object.nonce
			};

			jQuery.post( gm_marketpress_ajax_object.ajax_url, data, function( response ) {
			});

		});

	});

	jQuery( '.german-market-update-notice button.notice-dismiss' ).ready( function() {

		jQuery( '.german-market-update-notice button.notice-dismiss' ).on( 'click', function() {
			
			var data = {
				'action': 'gm_dismiss_update_notice',
				'nonce' : gm_marketpress_ajax_object.nonce
			};

			jQuery.post( gm_marketpress_ajax_object.ajax_url, data, function( response ) {
			});

		});

	});

	jQuery( '.german-market-update-notice span.notice-dismiss-button' ).ready( function() {

		jQuery( '.german-market-update-notice span.notice-dismiss-button' ).on( 'click', function() {
			
			var data = {
				'action': 'gm_dismiss_update_notice',
				'nonce' : gm_marketpress_ajax_object.nonce
			};

			jQuery( '.german-market-update-notice' ).hide();

			jQuery.post( gm_marketpress_ajax_object.ajax_url, data, function( response ) {
			});

		});

	});

});
