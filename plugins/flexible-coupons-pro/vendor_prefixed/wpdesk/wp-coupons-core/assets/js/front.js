jQuery( function ( $ ) {
    "use strict";

    var VariationFields = {

        get: function () {
            jQuery( 'body' ).on( 'found_variation', function ( e, variation ) {
                if ( variation.variation_id ) {
                    var data = {
                        'action': 'get_variation_fields',
                        'security': fc_front.security,
                        'variation_id': variation.variation_id,
                    };

                    jQuery.post( fc_front.ajax_url, data, function ( response ) {
                        if ( response.success ) {
                            jQuery( 'div.pdf-coupon-fields' ).html( response.data );
                        } else {
                            console.log( response.data );
                        }
                    } );
                }
            } );
        },

    };

    VariationFields.get();

} );
