jQuery(function ($) {
    "use strict";

    var FCMetaboxes = {

        field: $('#wpdesk_pdf_coupons'),

        requiredFields: function () {
            let left_checkboxes = $('.checkbox-wrapper-left input');
            if (left_checkboxes.length) {
                left_checkboxes.each(function () {
                    let checked = $(this).prop('checked');
                    let required_fields = $(this).closest('.checkbox-wrapper').find('.checkbox-wrapper-right');
                    if (checked) {
                        required_fields.show();
                    } else {
                        required_fields.hide();
                    }
                });

                left_checkboxes.click(function () {
                    let checked = $(this).prop('checked');
                    let required_field = $(this).closest('.checkbox-wrapper').find('.checkbox-wrapper-right');
                    if (checked) {
                        required_field.show();
                    } else {
                        required_field.hide();
                        required_field.find('input').prop('checked', false);
                    }
                })

            }
        },

        productPage: function () {
            let field = $('#wpdesk_pdf_coupons');
            let general_tab = $('.general_options a');
            let coupon_tab = $('.pdfcoupons_tab');
            let coupon_panel = $('.pdfcoupon_product_data');
            let virtual_field = $('#_virtual');
            let attribute_tab = $('li.attribute_tab');
            let tab_if = $('.hide_if_coupon_disabled');

            $('input#_virtual').on('change', function () {
                let is_pdf_coupon = field.is(':checked');
                if (!is_pdf_coupon) {
                    coupon_tab.hide();
                    coupon_panel.hide();
                }
            });

            if (field.length) {
                field.change(function () {
                    if ($(this).is(':checked')) {
                        coupon_tab.show();
                        coupon_panel.show();
                        $('.show_if_pdf_coupon').show().find('.fc_variation_base_on').trigger('change');
                        tab_if.removeClass('hide');
                    } else {
                        general_tab.click();
                        coupon_tab.hide();
                        coupon_panel.hide();
                        $('.show_if_pdf_coupon').hide();
                        $('.show_if_variation_manage_coupons').hide();
                        tab_if.addClass('hide');
                    }
                });

                if (field.is(':checked')) {
                    coupon_tab.show();
                    coupon_panel.show();
                    $('.show_if_pdf_coupon').show().find('.fc_variation_base_on').trigger('change');
                    tab_if.removeClass('hide');
                }
            }

            var product_type = $('select#product-type');
            if (product_type.val() === 'variable') {
                field.parent().show();
            }

            $(document.body).on('woocommerce-product-type-change', function (e, val) {
                if (val === 'variable') {
                    field.parent().show();
                }
                if (field.is(':checked')) {
                    coupon_tab.show();
                    coupon_panel.show();
                    tab_if.removeClass('hide');
                } else {
                    coupon_tab.hide();
                    coupon_panel.hide();
                    tab_if.hide();
                    tab_if.addClass('hide');
                }
            });

            let _this = this;

            $('#variable_product_options').on('reload', function (e) {
                if (product_type.val() === 'variable') {
                    field.parent().show();
                }
            });

            let container = $('#woocommerce-product-data');

        },

        expiringOwnField: function () {
            let parent_field = $('select.expiring-date-select');
            if (parent_field.length && parent_field.val() === 'own') {
                $('.expiring-date-own').show();
            } else {
                $('.expiring-date-own').hide();
            }

            parent_field.change(function () {
                if ($(this).val() === 'own') {
                    $('.expiring-date-own').show();
                } else {
                    $('.expiring-date-own').hide();
                }
            })
        },

        /** Settings for simple product */
        manageSimpleCouponCodePrefix: function () {
            $(this).closest('.woocommerce_options_panel').find('.show_if_variation_manage_prefix').hide();
            if ($(this).is(':checked')) {
                $(this).closest('.woocommerce_options_panel').find('.show_if_variation_manage_prefix').show();
            }
        },

        initSimpleCouponCodeCheckbox: function () {
            let wrapper = $('.woocommerce_options_panel');
            let input = $('.woocommerce_options_panel input.fc_coupon_own_code');
            wrapper.on('change', 'input.fc_coupon_own_code', this.manageSimpleCouponCodePrefix);
            if (input.is(':checked')) {
                input.closest('.woocommerce_options_panel').find('.show_if_variation_manage_prefix').show();
            }
        },


        /** Settings for variable product */
        manageVariableCouponsCodePrefix: function () {
            $(this).closest('.woocommerce_variation').find('.show_if_variation_manage_coupons').hide();
            if ($(this).is(':checked')) {
                $(this).closest('.woocommerce_variation').find('.show_if_variation_manage_coupons').show();
            }
        },

        manageVariablePrefix: function () {
            $(this).closest('.woocommerce_variation').find('.show_if_variation_manage_prefix').hide();
            if ($(this).is(':checked')) {
                $(this).closest('.woocommerce_variation').find('.show_if_variation_manage_prefix').show();
            }
        },

        manageDisablePDF: function () {
            let is_checked = $(this).is(':checked');
            $(this).closest('.woocommerce_variation')
                .find('.show_if_variation_manage_coupons')
                .find('input, select')
                .not('.fc_disable_pdf_coupon')
                .prop('disabled', is_checked);
        },

        initVariableCheckbox: function () {
            let _this = this;
            let wrapper = $('#variable_product_options');
            wrapper.on('change', 'input.fc_variation_base_on', this.manageVariableCouponsCodePrefix);
            wrapper.on('change', 'input.fc_coupon_own_code', this.manageVariablePrefix);
            wrapper.on('change', 'input.fc_disable_pdf_coupon', this.manageDisablePDF);

            $('body').on('woocommerce_variations_loaded woocommerce_variations_added', function () {
                let is_checked = $(this).find('#wpdesk_pdf_coupons').prop('checked');
                if (is_checked) {
                    let variation_box = $(this).find('#variable_product_options');
                    variation_box.find('.show_if_pdf_coupon').show();
                    $('input.fc_disable_pdf_coupon').trigger('change');
                }

                _this.expiringOwnField();
                _this.requiredFields();

                let parent_fields = $('.woocommerce_variation').find('select.expiring-date-select');
                parent_fields.each(function () {
                    let _select = $(this);
                    if (_select.val() === 'own') {
                        _select.parent().next().show();
                    } else {
                        _select.parent().next().hide();
                    }
                });

            });
        },

        handleDelaySendingFields: function (select) {
            let wrapper = $('.woocommerce_options_panel');
            wrapper.find('.show_if_simple_delay, .show_if_fixed_date_delay').hide();

            if (!select.length) {
                return;
            }
            if (select.val() === 'simple_delay') {
                wrapper.find('.show_if_simple_delay').show();
            } else if (select.val() === 'fixed_date_delay') {
                wrapper.find('.show_if_fixed_date_delay').show();
            }
        },

        initDelaySendingFields: function () {
            const simple_product_select = $('.woocommerce_options_panel select.fcs-delay-type');
            simple_product_select.on('change', () => this.handleDelaySendingFields(simple_product_select));
            simple_product_select.trigger('change');

            $('body').on('woocommerce_variations_loaded woocommerce_variations_added', function () {
                const variations_select_fields = $('.woocommerce_variation').find('select.fcs-delay-type');
                variations_select_fields.each(function () {
                    let variable_product_select = $(this);
                    variable_product_select.on('change', () => FCMetaboxes.handleDelaySendingFields(variable_product_select));
                    variable_product_select.trigger('change');
                });
            });
        },

        /**
         * Removes the "variation-needs-update" class / marker from variations after loading.
         *
         * The "variation-needs-update" class triggers a save variations AJAX request just before a bulk update variations AJAX request.
         * Both requests operate asynchronously on the same data (variations), which can cause issues and unexpected results.
         * (WooCommerce should ideally control this, but it does not.)
         *
         * This fix is not perfect. We should probably change how we add fields to variations to prevent the "variation-needs-update" class from being added in the first place.
         * However, WooCommerce is planning to introduce new product page settings soon, so this is likely a temporary solution.
         *
         * @since 2.2.2
         */
        fixNotNeedForUpdateOnVariationsLoad: function() {
            $( '#woocommerce-product-data' ).on( 'woocommerce_variations_loaded', function() {
                $( '#variable_product_options' )
                    .find( '.woocommerce_variations .variation-needs-update' )
                    .removeClass( 'variation-needs-update' );
            } );
        }
    };

    FCMetaboxes.productPage();
    FCMetaboxes.requiredFields();
    FCMetaboxes.expiringOwnField();
    FCMetaboxes.initVariableCheckbox();
    FCMetaboxes.initSimpleCouponCodeCheckbox();
    FCMetaboxes.initDelaySendingFields();
    FCMetaboxes.fixNotNeedForUpdateOnVariationsLoad()

    function toggleFCMultiplePDFOptions( $checkboxElement ) {
        let $closestWrapper = $checkboxElement.closest('.fc-multiple-pdfs-options-wrapper');
        $closestWrapper.each(function () {
            let $closestCheckbox = $(this).find('.fc_multiple_pdf_enable');
            let isChecked = $closestCheckbox.is(':checked');
            if(isChecked) {
                $(this).find('.fc-multiple-pdfs-advanced-options').show();
            } else {
                $(this).find('.fc-multiple-pdfs-advanced-options').hide();
            }
        });

    }

    jQuery(document).on('change', '.fc_multiple_pdf_enable', function () {
        toggleFCMultiplePDFOptions( jQuery(this) );
    });

    jQuery('body').on('woocommerce_variations_loaded woocommerce_variations_added', function () {
        toggleFCMultiplePDFOptions( jQuery('.fc_multiple_pdf_enable') );
    });

    toggleFCMultiplePDFOptions( jQuery('.fc_multiple_pdf_enable') );
});
