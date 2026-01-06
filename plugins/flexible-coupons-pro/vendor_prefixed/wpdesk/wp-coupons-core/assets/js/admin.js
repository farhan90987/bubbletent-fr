( function ( $ ) {
    "use strict";

    var FCAdmin = {

        generateCoupon: function () {

            var _this = this;
            let submit_button = jQuery( '.generate_coupon' );
            if ( submit_button.length ) {
                submit_button.click( function () {
                    let _this = jQuery( this );
                    let nonce = jQuery( '#generate_coupon_nonce' ).val();
                    let post_ID = woocommerce_admin_meta_boxes['post_id'];
                    let item_id = jQuery( this ).attr( 'data-item-id' );
                    var data = {
                        action: 'generate_coupon',
                        security: nonce,
                        order_id: post_ID,
                        item_id: item_id,
                    };

                    _this.after( '<span class="spinner" style="visibility: visible;"></span>' );
                    _this.closest( '.postbox-container' ).find( '.metabox_coupon .generate_coupon' ).attr( 'disabled', 'disabled' );

                    jQuery.post( ajaxurl, data, function ( response ) {
                        if ( response.success && response.data.html !== 'undefined' ) {
                            _this.closest( '.postbox-container' ).find( '.metabox_coupon .generate_coupon' ).removeAttr( 'disabled' );
                            _this.next().remove();
                            _this.parent().children( '.generated-coupons' ).html( response.data.html );
                            _this.parent().children( '.generate_coupon' ).hide();
                        } else {
                            alert( response.data );
                        }

                    } );

                    return false;
                } );
                return false;
            }
        }

    };

    FCAdmin.generateCoupon();

} )( jQuery );
