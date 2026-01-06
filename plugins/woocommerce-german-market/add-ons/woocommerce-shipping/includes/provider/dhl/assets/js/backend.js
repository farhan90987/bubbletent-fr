(function( $, window, document ) {
    'use strict';

    function handle_product_select_in_order() {

        if ( $( '#_wgm_dhl_paket_product' ).length ) {
            if ( ! $( '#_wgm_dhl_paket_product').prop( 'disabled')) {
                let option = $( '#_wgm_dhl_paket_product' ).find( ':selected');
                if ( $( option).attr( 'data-service-shippingconditions' ) ) {
                    if ( 'on' === $( option ).attr( 'data-service-shippingconditions' ) ) {
                        $( '#_wgm_dhl_service_terms_of_trade').removeAttr( 'disabled');
                    } else {
                        $( '#_wgm_dhl_service_terms_of_trade').attr( 'disabled', 'disabled');
                    }
                }
                if ( $( option ).attr( 'data-service-endorsement' ) ) {
                    if ( 'on' === $( option ).attr( 'data-service-endorsement' ) ) {
                        $( '#_wgm_dhl_service_endorsement').removeAttr( 'disabled');
                    } else {
                        $( '#_wgm_dhl_service_endorsement').attr( 'disabled', 'disabled');
                    }
                }
                if ( $( option ).attr( 'data-service-ddp' ) ) {
                    if ( 'on' === $( option ).attr( 'data-service-ddp' )  ) {
                        if ( 'on' === $( option).attr( 'data-service-shippingconditions' ) ) {
                            $( '#_wgm_dhl_service_ddp' ).removeAttr( 'disabled' );
                            if ( $( '#_wgm_dhl_service_ddp' ).is( ':checked' ) ) {
                                $( '#_wgm_dhl_service_terms_of_trade' ).attr( 'disabled', 'disabled' );
                            } else {
                                $( '#_wgm_dhl_service_terms_of_trade' ).removeAttr( 'disabled' );
                            }
                        }
                    } else {
                        $( '#_wgm_dhl_service_ddp')                     .attr( 'disabled', 'disabled');
                        $( '#_wgm_dhl_service_terms_of_trade').removeAttr( 'disabled');
                    }
                }
            }
            $( '#_wgm_dhl_paket_product' ).on( 'change', function( event ) {
                let option = $( '#_wgm_dhl_paket_product' ).find( ':selected');
                if ( $( option ).attr( 'data-service-ddp' ) ) {
                    if ( 'on' === $( option ).attr( 'data-service-ddp' ) ) {
                        $( '#_wgm_dhl_service_ddp').removeAttr( 'disabled');
                        if ( $( '#_wgm_dhl_service_ddp').is( ':checked' ) ) {
                            $( '#_wgm_dhl_service_terms_of_trade').attr( 'disabled', 'disabled');
                        } else {
                            $( '#_wgm_dhl_service_terms_of_trade').removeAttr( 'disabled');
                        }
                    } else {
                        $( '#_wgm_dhl_service_ddp').attr( 'disabled', 'disabled').prop( 'checked', false );
                        $( '#_wgm_dhl_service_terms_of_trade').removeAttr( 'disabled');
                    }
                }
                if ( $( option).attr( 'data-service-shippingconditions' ) ) {
                    if ( 'on' === $( option ).attr( 'data-service-shippingconditions' ) ) {
                        $( '#_wgm_dhl_service_terms_of_trade').removeAttr( 'disabled');
                    } else {
                        $( '#_wgm_dhl_service_terms_of_trade').attr( 'disabled', 'disabled');
                    }
                }
                if ( $( option ).attr( 'data-service-endorsement' ) ) {
                    if ( 'on' === $( option ).attr( 'data-service-endorsement' ) ) {
                        $( '#_wgm_dhl_service_endorsement').removeAttr( 'disabled');
                    } else {
                        $( '#_wgm_dhl_service_endorsement').attr( 'disabled', 'disabled');
                    }
                }
            });
            $( '#_wgm_dhl_paket_product' ).change();
            $( '#_wgm_dhl_service_ddp').on( 'change', function( event ) {
                if ( 'on' === $( option).attr( 'data-service-shippingconditions' ) ) {
                    if ( $( this ).is( ':checked' ) ) {
                        $( '#_wgm_dhl_service_terms_of_trade').attr( 'disabled', 'disabled');
                    } else {
                        $( '#_wgm_dhl_service_terms_of_trade').removeAttr( 'disabled');
                    }
                }
            });
        }
    }

    function handle_ident_check_select() {
        if ( $( '#_wgm_dhl_service_ident_check' ).length && $( '#_wgm_dhl_service_ident_check' ).attr( 'disabled' ) !== false ) {
            $( '#_wgm_dhl_service_ident_check' ).on( 'change', function( event ) {
                let value = $( '#_wgm_dhl_service_ident_check' ).val();
                if ( 0 == value ) {
                    $( '.billing-dob-wrapper' ).hide();
                } else {
                    $( '.billing-dob-wrapper' ).show();
                }
            });
        }
    }

    /**
     * Try to catch order id in admin order screen.
     *
     * @returns {string}
     */
    function get_order_id() {

        // Grab the order id.

        let order_id = undefined;

        // For WooCommerce <= 7.8 without HPOS feature
        if ( $( 'input#post_ID' ).length ) {
            order_id = $( 'input#post_ID' ).val();
        } else {
            if ( undefined !== typeof woocommerce_admin_meta_boxes ) {
                // Try to grab from woocommerce admin meta boxes array
                order_id = woocommerce_admin_meta_boxes.post_id;
            } else {
                // Try to grab the order ID from url params
                let query_string = window.location.search;
                let params = new URLSearchParams( query_string );
                order_id = params.get( 'id' );
            }
        }

        return order_id;
    }

    /**
     * Trigger Ajax request when changing parcel weight input field.
     */
    function handle_parcel_weight_input_change() {

        if ( ! $( '#parcel_total_weight' ).length || $( '#_wgm_dhl_paket_weight' ).attr( 'disabled' ) == 'disabled' ) {
            return;
        }

        $( '#_wgm_dhl_paket_weight' ).on( 'change', function( event ) {

            var data = {
                action: 'woocommerce_dhl_admin_order_calculate_parcel_total_weight',
                nonce: wgm_shipping_dhl.ajax_nonce,
                order_id: get_order_id(),
                weight: $( this ).val(),
            };

            $.post(
                wgm_shipping_dhl.ajax_internetmarke_url,
                data,
                function( response ) {
                    if ( true === response.data.success ) {
                        let input_weight = response.data.input_weight;
                        let total_weight = response.data.total_weight;
                        $( '#parcel_total_weight' ).text( total_weight );
                        $( '#_wgm_dhl_paket_weight' ).val( input_weight );
                    }
                }
            );
        });
    }

    /*
     * Click on the DHL label action button in admin order.
     */
    function create_shipping_label_from_order() {

        // Handle Ajax Shipment Creation.
        if ( $( 'input#_wgm_dhl_action_button' ).length ) {
            $(  'input#_wgm_dhl_action_button').on( 'click', function( event ) {

                // Hide error Div if exists.

                if ( $( '#wgm_dhl_error').length ) {
                    $( '#wgm_dhl_error').removeClass('open');
                }

                // Disable Meta Box and show the spinner while request.

                $( '#wgm_dhl_package_services' ).block( {
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    }
                } );

                // Loop through inputs within id meta box.

                var data = {
                    action: 'woocommerce_dhl_admin_order_create_label',
                    nonce: wgm_shipping_dhl.ajax_nonce,
                    order_id: get_order_id(),
                };

                // In case an error has occured.
                var abort = false;
                var $form = $('#wgm_dhl_package_services');

                $form.each(function(i, div) {

                    $(div).find('input').each(function(j, element){
                        if( $(element).attr('type') == 'checkbox' ) {
                            if ( $(element).prop('checked') ) {
                                data[ $(element).attr('name') ] = 'on';
                            } else {
                                data[ $(element).attr('name') ] = 'off';
                            }
                        } else {
                            var eName = $(element).attr('name');
                            // Do NOT add array inputs here!
                            if (eName.indexOf("[]") == -1) {
                                data[ $(element).attr('name') ] = $(element).val();
                            }
                        }
                    });

                    $(div).find('select').each(function(j, element){
                        data[ $(element).attr('name') ] = $(element).val();
                    });

                    $(div).find('textarea').each(function(j, element){
                        data[ $(element).attr('name') ] = $(element).val();
                    });
                });

                $.post(
                    wgm_shipping_dhl.ajax_internetmarke_url,
                    data,
                    function( response ) {
                        if ( false === response.data.success  ) {
                            $(  '#_wgm_dhl_paket_product' ).removeAttr( 'disabled' );
                            $( '#create_label_wrapper').show();
                            // Show Error.
                            $( '#wgm_dhl_error .inner').empty().html( response.data.error );
                            $( '#wgm_dhl_error').addClass('open');
                            // Removing buttons.
                            if ( $( '#label_download_wrapper' ).length ) {
                                $( '#label_download_wrapper').remove();
                            }
                            if ( $( '#export_documents_wrapper' ).length ) {
                                $( '#export_documents_wrapper').remove();
                            }
                            if ( $( '#cancel_shipment_wrapper' ).length ) {
                                $( '#cancel_shipment_wrapper').remove();
                            }
                        } else {
                            $(  '#_wgm_dhl_paket_product' ).attr( 'disabled', 'disabled' );
                            $( '#create_label_wrapper').hide();
                            $( '#wgm_dhl_error .inner').removeClass( 'open');
                            // Disable input and select fields.
                            $.each(
                                $( '#wgm_dhl_package_services input[name^=_wgm], #wgm_dhl_package_services select'),
                                function( index, element ) {
                                    $( this ).attr( 'disabled', 'disabled' );
                                }
                            );
                            if ( response.data.buttons !== null ) {
                                let wrapper = $(  '#create_label_wrapper');
                                if ( response.data.buttons.markup_cancel_shipment != '' && ! $( '#cancel_shipment_wrapper').length )  {
                                    $( wrapper).after( response.data.buttons.markup_cancel_shipment );
                                    cancel_shipment_from_admin_order();
                                }
                                if ( response.data.buttons.markup_download_export_documents != '' && ! $( '#export_documents_wrapper').length) {
                                    $( wrapper).after( response.data.buttons.markup_download_export_documents );
                                }
                                if ( response.data.buttons.markup_download_label != '' && ! $( '#label_download_wrapper').length) {
                                    $(  wrapper).after( response.data.buttons.markup_download_label );
                                }
                            }
                            // Order notes.
                            if ( $( '#woocommerce-order-notes' ).length ) {
                                if ( $( '#woocommerce-order-notes li:first-child' ).length ) {
                                    $.each( response.data.note, function( index, note ) {
                                            $( '#woocommerce-order-notes li:first-child' ).before( note )
                                        }
                                    )
                                } else {
                                    $.each( response.data.note, function( index, note ) {
                                            $( '#woocommerce-order-notes' ).append( response.data.note) ;
                                        }
                                    )
                                }
                            }
                        }
                        $( '#wgm_dhl_package_services' ).unblock();
                    }
                );

                $( this ).trigger( 'blur' );

                return false;
                });
        }
    }

    function cancel_shipment_from_admin_order() {
        // Handle Ajax Cancel Shipment.
        if ( $( '#_wgm_dhl_cancel_button' ).length ) {
            $( '#_wgm_dhl_cancel_button' ).on( 'click', function( event ) {

                // Disable Meta Box and show the spinner while request.

                $( '#wgm_dhl_package_services' ).block( {
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    }
                } );

                // Loop through inputs within id meta box.

                var data = {
                    action: 'woocommerce_dhl_admin_order_cancel_shipment',
                    nonce: wgm_shipping_dhl.ajax_nonce,
                    order_id: get_order_id(),
                };

                $.post(    wgm_shipping_dhl.ajax_internetmarke_url, data, function( response ) {
                        if ( response.data.success === true ) {
                            $( '#_wgm_dhl_paket_product').removeAttr( 'disabled');
                            $( '#create_label_wrapper').show();
                            // Enable editing again.
                            $.each(
                                $( '#wgm_dhl_package_services input[name^=_wgm], #wgm_dhl_package_services select'),
                                function( index, element ) {
                                $( this ).removeAttr( 'disabled' );
                                $( '#_wgm_dhl_paket_product' ).trigger( 'change');
                            }
                            );
                            // Removing buttons.
                            if ( $( '#label_download_wrapper' ).length ) {
                                $( '#label_download_wrapper').remove();
                            }
                            if ( $( '#export_documents_wrapper' ).length ) {
                                $( '#export_documents_wrapper').remove();
                            }
                            if ( $( '#cancel_shipment_wrapper' ).length ) {
                                $( '#cancel_shipment_wrapper').remove();
                            }
                            // Order notes.
                            if ( $( '#woocommerce-order-notes' ).length ) {
                    if ( $( '#woocommerce-order-notes li:first-child' ).length ) {
                        $.each( response.data.note, function( index, note ) {
                            $( '#woocommerce-order-notes li:first-child' ).before( note )
                        }
                        )
                    } else {
                        $.each( response.data.note, function( index, note ) {
                            $( '#woocommerce-order-notes' ).append( response.data.note) ;
                            }
                        )
                    }
                            }
                        }
                        $( '#wgm_dhl_package_services' ).unblock();
                    }
                );

                $( this ).trigger( 'blur' );

                return false;
            } );
        }
    }

    /*
     * Click on the DHL label action button in overview.
     */
    function create_shipping_label_from_order_overview() {

        $( 'a.dhl_shipping_label_create' ).on( 'click', function( e ) {

            var _this = $( this );
            var ajax_url = $( this ).attr( 'href' );

            if ( ! $( _this ).hasClass( 'dhl_shipping_label_download' ) ) {

                $( _this ).removeClass( 'dhl_shipping_label_create' );
                $( _this ).addClass( 'dhl_shipping_label_loading' );

                $( this ).trigger( 'blur');

                // create a pdf first
                $.getJSON( ajax_url, function( response ) {

                    if ( response.data.barcode.length > 0 ) {
                        $( _this ).removeClass( 'dhl_shipping_label_loading' );
                        $( _this ).addClass( 'dhl_shipping_label_download' );

                        $( _this ).removeClass( 'wc-action-button-dhl_shipping_label_create' );
                        $( _this ).addClass( 'wc-action-button-dhl_shipping_label_download' );

                        $( _this ).on( 'click', function( e ) {} );

                        var tracking_labels = response.data.barcode[ 0 ];
                        var tracking_link   = '';

                        $.each( tracking_labels, function( index, barcode ) {
                            tracking_link += '<a href="' + wgm_shipping_dhl.tracking_url.replace( '{tracking_number}', barcode ) + '" target="_blank" style="font-weight: 600; background: url(' + wgm_shipping_dhl.theme_url + 'assets/images/icon.png) left center no-repeat; background-size: auto 20px; padding-left: 52px;" class="icon icon-dhl">' + this + '</a><br/>';
                        });

                        $( _this )
                        .closest( 'tr' )
                        .find( 'td.order_tracking' )
                        .html( tracking_link );

                        var a = document.createElement( 'a' );
                        a.href = ajax_url;
                        a.click();
                    } else {
                        $( _this ).removeClass( 'dhl_shipping_label_loading' );
                        $( _this ).addClass( 'dhl_shipping_label_create' );

                        $.toast({
                            heading: wgm_shipping_dhl.error_creating_label_heading,
                            text: response.data.error,
                            showHideTransition: 'fade',
                            position: 'bottom-right',
                            icon: 'error',
                            hideAfter: 5000
                        })
                    }
                } )
                .done( function() {} )
                .fail( function( jqXHR, textStatus, errorThrown ) {} )
                .always( function() {} );

                e.preventDefault();
            }
        } );
    }

    /**
     * Playing with the Google Maps Switch.
     */
    function handle_google_maps_switch() {
        if ( $( '#wgm_dhl_google_map_enabled' ).length ) {
            var $row = $( '#wgm_dhl_google_map_enabled' ).closest( 'tr' ).next( 'tr' );
            if ( true == $( '#wgm_dhl_google_map_enabled' ).prop( 'checked' ) ) {
                $row.show();
            } else {
                $row.hide();
            }
            var $switch = $( '#wgm_dhl_google_map_enabled' ).on( 'change', function() {
                if ( true === this.checked ) {
                    $row.show();
                } else {
                    $row.hide();
                }
            });
        }
    }

    /**
     * Playing with the test mode switch.
     */
    function handle_test_mode_switch() {
        if ( $( '#wgm_dhl_test_mode' ).length ) {
            let dhl_username = $( '#wgm_dhl_global_username' );
            let dhl_pass = $( '#wgm_dhl_global_signature' );
            let dhl_ekp = $( '#wgm_dhl_global_ekp' );
            $( dhl_username ).attr( 'disabled', ( true === $( '#wgm_dhl_test_mode' ).prop( 'checked' ) ) ? true : false );
            $( dhl_pass ).attr( 'disabled', ( true === $( '#wgm_dhl_test_mode' ).prop( 'checked' ) ) ? true : false );
            $( dhl_ekp ).attr( 'disabled', ( true === $( '#wgm_dhl_test_mode' ).prop( 'checked' ) ) ? true : false );
            $( '#wgm_dhl_test_mode' ).on(
                'change',
                function( event ) {
                    $( dhl_username ).attr( 'disabled', ( true === $( this ).prop( 'checked' ) ) ? true : false );
                    $( dhl_pass ).attr( 'disabled', ( true === $( this ).prop( 'checked' ) ) ? true : false );
                    $( dhl_ekp ).attr( 'disabled', ( true === $( this ).prop( 'checked' ) ) ? true : false );
                }
            )
        }
    }

    function handle_print_retoure_label_switch() {
        if ( $( '#wgm_dhl_label_retoure_enabled' ).length ) {
            var $row1_status = $( '#wgm_dhl_label_retoure_enabled' ).closest( 'tr' ).next( 'tr' );
            if ( true === $( '#wgm_dhl_label_retoure_enabled' ).prop( 'checked' ) ) {
                $row1_status.show();
            } else {
                $row1_status.hide();
            }
            var $switch = $( '#wgm_dhl_label_retoure_enabled' ).on( 'change', function() {
                if ( true === this.checked ) {
                    $row1_status.show()
                } else {
                    $row1_status.hide()
                }
            })
        }
    }

    /**
     * Playing with the Auto Label Creation switch.
     */
    function handle_auto_label_creation_switch() {
        if ( $( '#wgm_dhl_label_auto_creation' ).length ) {
            var $row_status = $( '#wgm_dhl_label_auto_creation' ).closest( 'tr' ).next( 'tr' );
            if ( true === $( '#wgm_dhl_label_auto_creation' ).prop( 'checked' ) ) {
                $row_status.show();
            } else {
                $row_status.hide();
            }
            var $switch = $( '#wgm_dhl_label_auto_creation' ).on( 'change', function() {
                if ( true === this.checked ) {
                    $row_status.show()
                } else {
                    $row_status.hide()
                }
            })
        }
    }

    /**
     * Playing with the Email switch.
     */
    function handle_email_switch() {
        if ( $( '#wgm_dhl_label_email_enabled' ).length ) {
            var $row_email = $( '#wgm_dhl_label_email_enabled' ).closest( 'tr' ).next( 'tr' );
            if ( true === $( '#wgm_dhl_label_email_enabled' ).prop( 'checked' ) ) {
                $row_email.show();
            } else {
                $row_email.hide();
            }
            var $switch = $( '#wgm_dhl_label_email_enabled' ).on( 'change', function() {
                if ( true === this.checked ) {
                    $row_email.show();
                } else {
                    $row_email.hide();
                }
            });
        }
    }

    /**
     * Open Google Maps from order.
     */
    function open_google_maps_from_order() {
        if ( $( '#dhl-show-parcel-modal' ).length ) {
            $( '#dhl-show-parcel-modal' ).click( function( e ) {
                e.preventDefault();
            } );
        }
    }

    function handle_shipper_reference_field() {
        if ( $( '#wgm_dhl_shipping_gkp_shipper_reference' ).length ) {
            if ( '' != $( '#wgm_dhl_shipping_gkp_shipper_reference').val()) {
                $.each( $( 'input[name^=wgm_dhl_shipping_shop_address], select[name^=wgm_dhl_shipping_shop_address]' ), function( index, field ) {
                        $( this ).prop( 'disabled', true);
                    }
                );
            }
            $( '#wgm_dhl_shipping_gkp_shipper_reference').on(
                'blur',
                function( event ) {
if ( '' == $( this ).val() ) {
                    $.each( $( 'input[name^=wgm_dhl_shipping_shop_address], select[name^=wgm_dhl_shipping_shop_address]' ), function( index, field ) {
$( this ).prop( 'disabled', false);
                    }
                    );
} else {
    $.each( $( 'input[name^=wgm_dhl_shipping_shop_address], select[name^=wgm_dhl_shipping_shop_address]' ), function( index, field ) {
            $( this ).prop( 'disabled', true);
        }
    );
}
            }
            )
        }
    }

    /**
     * Initialization when DOM ready.
     */
    $( function() {
        // Handle some backend options.
        handle_test_mode_switch();
        handle_print_retoure_label_switch();
        handle_auto_label_creation_switch();
        handle_google_maps_switch();
        handle_email_switch();

        // Handle Sender Reference field.
        handle_shipper_reference_field();

        // Create shipping label from order overview.
        create_shipping_label_from_order_overview();

        // Handle DHL product select.
        handle_product_select_in_order();

        // Handle Ident select in order.
        handle_ident_check_select();

        // Handle parcel weight input change.
        handle_parcel_weight_input_change();

        // Create shipping label from order screen.
        create_shipping_label_from_order();

        // Cancel shipment from order screen.
        cancel_shipment_from_admin_order();

        // Open Google Maps from order.
        open_google_maps_from_order();

        /**
         * Internetmarke Wizard.
         */
        if ( $( '#internetmarke_wizard' ).length ) {

            let cart           = [];
            let products       = [];
            let count          = 0;
            let container      = $( '#internetmarke_wizard #internetmarke_checkout_wrapper_inner' );
            let wizard         = $( '#internetmarke_wizard' );
            let wrapper        = $( '#wizard-wrapper' );
            let wp_navigation  = $( '#adminmenuback' );
            let labels_created = false;

            $( window ).on( 'resize', adjustWrapperPosition );

            function adjustWrapperPosition() {
                let win = $( this );
                let wrapper_width = $( wrapper ).width();
                $( wrapper ).css({ 'margin-left': 0 - ( wrapper_width / 2 ) + ( $( wp_navigation ).width() / 2 ) });
            }

            // Click on the Internetmarke Wizard button
            $( 'a.button-primary.internetmarke' ).click( function( e ) {
                e.preventDefault();
                $( '#internetmarke_wizard .categories .national ul li' )[0].click();
                $( '#internetmarke_wizard' )
                    .css( { 'display': 'block' } )
                    .animate({ 'opacity': 1 }, 750 );
                $( '#internetmarke_wizard #wizard-wrapper' )
                    .animate( { 'margin-top': '-400px' }, 500 );
                $( window ).trigger( 'resize' );
            } );

            // Click on the Wizard Close button
            $( '#internetmarke_wizard a.close' ).click( function( e ) {
                e.preventDefault();
                $( '#internetmarke_wizard' ).css({
                    'display': 'none',
                    'opacity': 0
                });
                $( '#internetmarke_wizard #wizard-wrapper' ).css({
                    'margin-top': '-370px'
                });
                $( '#internetmarke_wizard #internetmarke_checkout_wrapper_inner' ).css({
                    'margin-left': '0'
                });
                if ( true === labels_created ) {
                    location.reload();
                }
            } );

            // Click on Internetmarke category
            $( '#internetmarke_wizard .header li' ).click( function( e ) {
                e.preventDefault();
                let category_slug = $( this ).attr( 'id' );
                if ( ! $( this ).hasClass( 'current' ) ) {
                    $( '#internetmarke_wizard .header li' ).removeClass( 'current' );
                    $( '#internetmarke_wizard .label img' ).remove();
                    $( this ).addClass( 'current' );
                    load_products_and_services_by_category( category_slug );
                }
            } );

            // Click on Internetmarke products
            function make_products_clickable() {
                $( '#internetmarke_wizard .products ul.product-list li' ).click( function( e ) {
                    e.preventDefault();
                    let product_id = $( this ).attr( 'id' );
                    product_id = product_id.substring( 8 );
                    let product_im_id = $( this ).attr( 'data-product-im-id' );
                    if ( undefined != product_im_id ) {
                        load_internetmarke_preview( product_im_id );
                    }
                    if ( ! $( this ).hasClass( 'current' ) ) {
                        $( '#internetmarke_wizard .products ul.product-list li' ).removeClass( 'current' );
                        $( '#internetmarke_wizard .label img' ).remove();
                        $( this ).addClass( 'current' );
                    } else {
                        $( '#internetmarke_wizard .content .services input:checked' )
                        .removeClass( 'current' )
                        .prop( 'checked', false );
                    }
                    $( '#internetmarke_wizard .description .information-text span.product-title' ).html('');
                    show_product_service_details( product_id );
                } );
            }

            // Click on the Page Layout
            function make_pagelayout_clickable() {
                $( '#internetmarke_wizard .voucher-layout input[name=voucherLayout]' ).on( 'change', function(){
                    let product_im_id = ( $( '#internetmarke_wizard .services input[name=service]:checked' ).length ? $( '#internetmarke_wizard .services input[name=service]:checked' ).attr( 'id').substr( 8 ) : $( '#internetmarke_wizard .products ul.product-list li.current' ).attr( 'data-product-im-id' ) );
                    if ( undefined != product_im_id ) {
                        load_internetmarke_preview( product_im_id );
                    }
                });
            }

            make_products_clickable();
            make_pagelayout_clickable();

            // Click on Internetmarke products
            function make_services_clickable() {
                $( '#internetmarke_wizard .services li input' ).click( function( e ) {
                    let product_id    = $( this ).val();
                    let product_im_id = $( this ).attr( 'id' );
                    product_im_id     = product_im_id.substr( 8 );
                    if ( ! $( this ).hasClass( 'current' ) ) {
                        load_internetmarke_preview( product_im_id );
                        update_service_details( product_im_id );
                        $( '#internetmarke_wizard .services li input' ).removeClass( 'current' );
                        $( this ).addClass( 'current' );
                    }
                });
            }

            /**
             * Removing product from cart if 'X' button clicked.
             */
            function make_remove_from_cart_buttons_clickable() {
                $( '#internetmarke_wizard form span.remove' ).click( function( e ) {
                    e.preventDefault();

                    let product_im_id = $( this ).closest( 'li' ).attr( 'data-product-id' );
                    $.each( cart, function( i, item ){
                        if ( item.product_im_id == product_im_id ) {
                            cart.splice( i, 1 );
                        }
                    });
                    update_cart_html();
                    update_cart_totals();

                    // Add disabled class on 'next' button if cart is emty.
                    if ( 0 <= cart.length ) {
                        $( '#internetmarke_wizard form button.next' ).addClass( 'disabled' );
                    }
                });
            }

            /**
             * Updates the cart.
             */
            function update_cart() {
                if ( $( '#internetmarke_wizard .product-summary input[name=product_quantity]' ).length ) {
                    $.each( $( '#internetmarke_wizard .product-summary input[name=product_quantity]' ), function( i, item ){
                        let quantity      = $( this ).val();
                        let product_im_id = $( this ).closest( 'li' ).attr( 'data-product-id' );
                        $.each( cart, function( i, product ){
                            if ( product.product_im_id == product_im_id ) {
                                if ( 0 == quantity ) {
                                    cart.splice( i, 1 );
                                } else {
                                    cart[ i ][ 'quantity' ] = quantity;
                                }
                            }
                        });
                    });
                    update_cart_html();
                    update_cart_totals();
                }
            }

            /**
             * Updates the cart html in step 2.
             */
            function update_cart_html() {
                $( '#internetmarke_wizard .product-summary ul li:not(.empty-cart)' ).remove();
                let button = $( '#internetmarke_wizard button.order' );
                // checks if cart is empty.
                if ( 0 < cart.length ) {
                    $( '#internetmarke_wizard .product-summary .empty-cart' ).hide();
                    $.each( cart, function( i, product ){
                        let cart_html = '<li data-product-id="' + product[ 'product_im_id' ] + '">\n' +
                            '    <div class="product-title">' + product[ 'product_title' ] + ' <span class="dashicons dashicons-dismiss remove" title="' + wgm_shipping_dhl.internetmarke_remove_from_cart + '"></span> <input type="number" name="product_quantity" min="0" value="' + product[ 'quantity' ] + '" /></div>\n' +
                            '    <small class="dimensions">' + product[ 'product_dimensions' ] + '</small>\n' +
                            '</li>\n'
                        $( '#internetmarke_wizard .product-summary ul' ).append( cart_html );
                    });
                    if ( button.hasClass( 'disabled' ) ) button.removeClass( 'disabled' );
                    make_remove_from_cart_buttons_clickable();
                } else {
                    $( '#internetmarke_wizard .product-summary .empty-cart' ).show();
                    if ( ! button.hasClass( 'disabled' ) ) button.addClass( 'disabled' );
                }
            }

            /**
             * Updates the cart summary / totals.
             */
            function update_cart_totals() {
                $( '#internetmarke_wizard .product-totals li:not(.empty-cart):not(.totals)' ).remove();
                if ( 0 < cart.length ) {
                    let summary = 0;
                    $( '#internetmarke_wizard .product-totals li.empty-cart' ).hide();
                    $( '#internetmarke_wizard .product-totals li.totals' ).show();
                    $.each( cart, function( i, product ){
                        let quantity     = product[ 'quantity' ];
                        let title        = product[ 'product_title' ];
                        let price        = product[ 'product_price' ];
                        let product_html = '<li class="' + ( i % 2 === 0 ? 'even' : 'odd' ) + '">\n' +
                            '    <span class="quantity">' + quantity + '</span> x <span class="product-title">' + title + '</span> <span class="product-total">' + price + '</span>\n' +
                            '</li>\n';
                        $( '#internetmarke_wizard .product-totals .totals' ).before( product_html );
                        summary += parseFloat( price.replace(/[^0-9]/g, '' ) * product[ 'quantity' ] );
                    });
                    let euros = summary / 100;
                    euros = euros.toLocaleString("de-DE", { style:"currency", currency:"EUR" } );
                    $( '#internetmarke_wizard .product-totals li.totals .price' ).html( euros );
                } else {
                    $( '#internetmarke_wizard .product-totals .empty-cart' ).show();
                    $( '#internetmarke_wizard .product-totals li.totals' ).hide();
                }
            }

            $( '#internetmarke_wizard form button' ).click( function( e ) {

                e.preventDefault();

                // move to step 2
                if ( $( this ).hasClass( 'next' ) ) {
                    update_cart_html();
                    update_cart_totals();
                    move_to_checkout_step( 2 );
                    // container.css( 'margin-left', '-100%' );
                } else
                // back to previous step
                if ( $( this ).hasClass( 'prev' ) ) {
                    move_to_checkout_step( 1 );
                } else
                // add product to cart
                if ( $( this ).hasClass( 'add-to-cart' ) ) {

                    let product            = {};
                    let product_im_id      = 0;
                    let quantity           = 1;
                    let category_id        = $( '#internetmarke_wizard form .categories li.current' ).attr( 'id' );
                    let voucher_layout     = $( '#internetmarke_wizard form input[name=voucherLayout]:checked' ).val();
                    let product_title      = $( '#internetmarke_wizard form .product-list li.current' ).html();
                    let product_dimensions = $( '#internetmarke_wizard form .description .dimensions div' ).html();
                    let product_price      = $( '#internetmarke_wizard form .total p' ).html();
                    let found              = false;

                    if ( $( '#internetmarke_wizard form input[name=service]:checked' ).length ) {
                        product_im_id = $( '#internetmarke_wizard form input[name=service]:checked' ).val();
                    } else {
                        product_im_id = $( '#internetmarke_wizard .products .product-list li.current' ).attr( 'id' );
                        product_im_id = product_im_id.substring( 8 ); // cutting 'product_' from id
                    }

                    product[ 'product_im_id' ]      = product_im_id;
                    product[ 'product_title' ]      = product_title;
                    product[ 'product_dimensions' ] = product_dimensions;
                    product[ 'product_price' ]      = product_price;
                    product[ 'quantity' ]           = quantity;
                    product[ 'category_id' ]        = category_id;
                    product[ 'voucher_layout' ]     = voucher_layout;

                    if ( 0 < cart.length ) {
                        $.each( cart, function( i, item ){
                            if ( item.product_im_id == product_im_id ) found = true;
                        });
                    }

                    if ( false === found ) {
                        cart.push( product );
                    }

                    // Remove disabled class from 'next' button.
                    $( '#internetmarke_wizard form button.next' ).removeClass( 'disabled' );

                    // triggering click on 'next step' to update the cart in the step 2.
                    $( '#internetmarke_wizard form button.next' ).click();
                } else
                // add new internetmarke product
                if ( $( this ).hasClass( 'add-product' ) ) {
                    move_to_checkout_step( 1 );
                } else
                // update cart
                if ( $( this ).hasClass( 'update-cart' ) ) {
                    update_cart();
                } else
                // placing order.
                if ( $( this ).hasClass( 'order' ) ) {
                    if ( 0 < cart.length ) {
                        // Disabling button to prevent double action.
                        $( this ).addClass( 'disabled' );

                        var data = {
                            'action':   'woocommerce_internetmarke_process_checkout',
                            'nonce':    wgm_shipping_dhl.ajax_internetmarke_nonce,
                            'order_id': $( '#internetmarke_wizard input[name=wc_order_id]' ).val(),
                            'order':    cart,
                        };

                        $.post( wgm_shipping_dhl.ajax_internetmarke_url, data, function( response ){
                            let ajax_response = $.parseJSON( response );
                            move_to_checkout_step( 3 );
                            if ( 'success' == ajax_response[ 'result_code' ] ) {

                                // Updating Portkasse Balance
                                let euros = ajax_response.balance / 100;
                                euros = euros.toLocaleString("de-DE", { style:"currency", currency:"EUR" } );
                                $( '#internetmarke_wizard #internetmarke_checkout_step_2 .portokasse-balance .value' ).html( euros );

                                $( '#internetmarke_wizard #internetmarke_checkout_step_3 .wrapper-success .info-download-button a' ).attr( 'href', ajax_response.link );
                                $( '#internetmarke_wizard #internetmarke_checkout_step_3 .wrapper-success' ).show();

                                // Emptying cart.
                                cart.length = 0;

                                labels_created = true;

                            } else
                            if ( 'error' == ajax_response[ 'result_code' ] ) {

                                $( '#internetmarke_wizard #internetmarke_checkout_step_3 .wrapper-error .info-additional' ).html( ajax_response.api_error_msg );
                                $( '#internetmarke_wizard #internetmarke_checkout_step_3 .wrapper-error' ).show();

                                labels_created = false;
                            }
                        });
                    } else {
                        //console.log( 'do nothing. cart is empty.' );
                    }
                }
            } );

            /**
             * Moving container to a specific step.
             *
             * @param int step
             *
             * @return void
             */
            function move_to_checkout_step( step ) {
                switch ( step ) {
                    default:
                    case 1: // Products
                        container.css( 'margin-left', '0' );
                        break;
                    case 2: // Cart, Checkout
                        // Grab wallet balance via Ajax Request once.
                        if ( $( '#portokasse_balance' ).attr( 'data-balance-loaded' ) == 'no' )  {
                            var data = {
                                'action':        'woocommerce_internetmarke_load_wallet_balance',
                                'nonce':         wgm_shipping_dhl.ajax_internetmarke_nonce,
                            };
                            $.post( wgm_shipping_dhl.ajax_internetmarke_url, data, function( response ) {
                                let ajax_response = $.parseJSON( response );
                                $( '#portokasse_balance .value' ).html( ajax_response[ 'wallat_balance' ] );
                                $( '#portokasse_balance' ).attr( 'data-balance-loaded', 'yes' );
                            } );
                        }
                        container.css( 'margin-left', '-100%' );
                        break;
                    case 3: // Download
                        container.css( 'margin-left', '-200%' );
                        break;
                }
            }

            /**
             * Loading product catalog
             *
             * @return array
             */
            function load_products_and_services_by_category( category_slug ) {

                var data = {
                    'action':        'woocommerce_internetmarke_load_products_and_services_by_category',
                    'nonce':         wgm_shipping_dhl.ajax_internetmarke_nonce,
                    'category_slug': category_slug,
                };

                $.post( wgm_shipping_dhl.ajax_internetmarke_url, data, function( response ) {
                    products = $.parseJSON( response );
                    count    = products.length;
                    $( '#internetmarke_checkout_step_1 .products ul.product-list li' ).remove();
                    $.each( products, function( index, product ) {
                        $( '#internetmarke_checkout_step_1 .products ul.product-list' ).append( '<li id="product_' + product.product_id + '" data-product-im-id="' + product.product_im_id + '">' + product.product_name + '</li>' );
                        if ( index + 1 === count ) {
                            if ( $( '#internetmarke_checkout_step_1 .products ul.product-list li' ).length ) {
                                make_products_clickable();
                                $( '#internetmarke_checkout_step_1 .products ul.product-list li' )[0].click(); // trigger click event on first product
                            }
                        }
                    });
                });
            }

            /**
             * Show product details and service data.
             *
             * @param int product_id
             *
             * @return array
             */
            function show_product_service_details( product_id ) {
                if ( product_id ) {
                    $.each( products, function( index, product ){
                        if ( product_id === product.product_id ) {
                            $( '#internetmarke_checkout_step_1 .description .dimensions div' ).html( product.product_dimensions_formatted );
                            $( '#internetmarke_checkout_step_1 .description .information-text div' ).html( product.product_information_text_formatted );
                            $( '#internetmarke_checkout_step_1 .total p' ).html( product.product_price_formatted );
                            $( '#internetmarke_checkout_step_1 .services ul li' ).remove();
                            $.each( product.services, function( key, service_data ){
                                if ( '1' == service_data.no_service_available ) {
                                    $( '#internetmarke_checkout_step_1 .services ul' ).append( '<li>' + service_data.information_text + '</li>' );
                                } else {
                                    $( '#internetmarke_checkout_step_1 .services ul' ).append( '<li class="service-' + service_data.service.product_service_slug + '" data-service-weight="' + service_data.service_weight + '"><input type="radio" name="service" id="product_' + service_data.service_product.product_im_id + '" value="' + service_data.service_product.product_id + '" /> ' + service_data.service_title + '</li>' );
                                }
                            });
                            make_services_clickable();
                        }
                    });
                }
            }

            /**
             * Update details when a service is selected.
             *
             * @param int product_im_id
             *
             * @return array
             */
            function update_service_details( product_im_id ) {
                if ( product_im_id ) {
                    $.each( products, function( index, product ){
                        $.each( product.services, function( key, service_data ) {
                            if ( product_im_id == service_data.service_product.product_im_id ) {
                                $( '#internetmarke_checkout_step_1 .description .dimensions div' ).html( service_data.service_product.product_dimensions_formatted );
                                $( '#internetmarke_checkout_step_1 .description .information-text h3 span.product-title' ).html( '(' + service_data.service_product.product_title_formatted + ')' );
                                $( '#internetmarke_checkout_step_1 .description .information-text div' ).html( service_data.service_product.product_information_text_formatted );
                                $( '#internetmarke_checkout_step_1 .total p' ).html( service_data.service_product.product_price_formatted );
                            }
                        })
                    });
                }
            }

            /**
             * Loading Internetmarke image preview.
             *
             * @param int product_im_id
             */
            function load_internetmarke_preview( product_im_id ) {

                let order_id       = $( '#internetmarke_wizard input#wc_order_id' ).val();
                var voucher_layout = $( '#internetmarke_wizard input[name=voucherLayout]:checked' ).val();

                var data = {
                    'action':         'woocommerce_internetmarke_load_image_preview',
                    'nonce':          wgm_shipping_dhl.ajax_internetmarke_nonce,
                    'order_id':       order_id,
                    'voucher_layout': ( '' != voucher_layout ? voucher_layout : 'AddressZone' ),
                    'product_im_id':  product_im_id,
                };

                $.post( wgm_shipping_dhl.ajax_internetmarke_url, data, function( response ) {
                    response = $.parseJSON(response );
                    if ( response.link ) {
                        if ( $( '#internetmarke_wizard .label img' ).length ) {
                            $( '#internetmarke_wizard .label img' ).attr( 'src', response.link );
                        } else {
                            $( '#internetmarke_wizard .label' ).append( '<img src="' + response.link + '" />' );
                        }
                        $( '#internetmarke_wizard .label' )
                        .removeClass( 'addresszone' )
                        .removeClass( 'frankingzone' )
                        .addClass( voucher_layout.toLowerCase() );
                    }
                });
            }

        }

    })

})( jQuery, window, document );
