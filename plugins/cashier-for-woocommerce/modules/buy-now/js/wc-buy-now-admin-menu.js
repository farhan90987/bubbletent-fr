jQuery(
    function($){

        let withCart            = 0;
        let pricings            = [];
        jQuery( '#buy_now_generate_link_product, #buy_now_generate_link_coupon, #buy_now_shipping_method, #buy_now_redirect_link' ).on( 'change', function() {

            var generated_url       = `${window.location.origin}/?`;
            var selected_products   = jQuery('#buy_now_generate_link_product').val();
            var selected_coupons    = jQuery('#buy_now_generate_link_coupon').val();
            var shipping_method     = jQuery('#buy_now_shipping_method').find('option:selected').val();
            var redirect_page       = jQuery('#buy_now_redirect_link').find('option:selected').val();

            jQuery('#buy_now_generated_link').text('');

            if( selected_products == null )
                return;

            generated_url += 'buy-now=' + selected_products;

            if ( this.id === 'buy_now_generate_link_product' ) {
                getProductDetails(
                    'get_product_details',
                    ['product_ids', JSON.stringify(selected_products)]
                )
                .then( response => {
                    console.log( response.data.data );
                    pricings = response.data.product_prices;
                    withCart = response.data.with_cart;
                    createUrl(
                        generated_url,
                        selected_products,
                        selected_coupons,
                        shipping_method,
                        redirect_page,
                        withCart,
                        pricings
                    );
                })
                .catch( exception => {
                    console.log( 'Error' );
                    console.log( exception );
                })
            } else {
                createUrl(
                    generated_url,
                    selected_products,
                    selected_coupons,
                    shipping_method,
                    redirect_page,
                    withCart,
                    pricings
                );
            }

        });

        jQuery('#buy_now_shipping_method').css( 'width', '50%' );
        jQuery('#buy_now_redirect_link').css( 'width', '50%' );
    }
);

jQuery(document).on( 'change', function () {
    if ( jQuery('#buy_now_generated_link').text().length > 0 ) {
        jQuery('#bn-click-to-copy-btn').prop( 'disabled', false );
    }
});

function copy_to_clipboard() {
    var copyText = document.getElementById("buy_now_generated_link");
    copyText.select();
    document.execCommand("copy");
    document.getElementById("bn-click-to-copy-btn").innerHTML = 'Copied!';
    setTimeout(function(){
        document.getElementById("bn-click-to-copy-btn").innerHTML = 'Click To Copy';
    }, 1000);
}

async function getProductDetails( method, ...args ) {
    let data = {
        action: method,
        security:  cashier_buy_now_object.ajax_nonce // user_registration_object.ajax_nonce,
    }

    for ( let arg of args ) {
        data[ arg[0] ] = arg[1];
    }

    const result = await jQuery.ajax( {
        url: cashier_buy_now_object.ajax_url,
        method: 'POST',
        dataType: 'json',
        data: data,
        //On success or error display the message sent
        success: function ( response ) {
            //console.log( response.data.data );
            return response.data.data;
        },
        error: function ( response ) {
            //console.log( response.data );
            return false;
        }
    } );

    return result
}

function createUrl(
    generated_url,
    selected_products,
    selected_coupons,
    shipping_method,
    redirect_page,
    withCart,
    pricings
) {
    var quantity = JSON.stringify(selected_products).split(',');
    quantity.map(function(x, i, ar){
        ar[i] = 1;
    });

    generated_url += '&qty=' + quantity.join(',');

    if( selected_coupons != null ) {
        generated_url += '&coupon=' + selected_coupons;
    }

    if ( shipping_method != '' ) {
        generated_url += '&ship-via=' + shipping_method;
    }

    if ( redirect_page != '' ) {
        generated_url += '&page=' + redirect_page;
    }

    generated_url += '&with-cart=' + withCart;
    if ( pricings.length > 0 ) {
        generated_url += '&prices=' + pricings.join(',');
    }
    let buyNowButton = `<p><a href="${generated_url}" class="button st-cashier">Buy Now</a></p>`;

    jQuery('#buy_now_generated_link').text(buyNowButton);
}
