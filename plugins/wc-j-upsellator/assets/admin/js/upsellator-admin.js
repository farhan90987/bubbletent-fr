(function($) {	

   "use strict";  

    const { __, _x, _n, sprintf } = wp.i18n;

    let found_products = [];

    function updatePreviewPrice( sel )
    {
        let newPrice;

        const selector  = $( sel );
        const amount    = selector.find('.discount-amount-input').val();
        const type      = selector.find('.upsell-discount-type').val();
        const basePrice = selector.find('.preview-base-price').val();     
        const qty       = selector.find('.upsell-qty-input').val() || 1;    
      
        switch( type ) 
        {
                case '2': default:                               
                        selector.find('.wc-timeline-product-price.striked').addClass('hidden');
                        selector.find('.preview-price').text( qty * basePrice );
                        break;
                case '1':
                        selector.find('.wc-timeline-product-price.striked').removeClass('hidden');
                        selector.find('.preview-striked-price').text( qty * basePrice );

                        newPrice = qty * ( basePrice - ( ( basePrice / 100 ) * amount ));
                        selector.find('.preview-price').text( parseFloat( newPrice.toFixed( 2 ) ) );
                        break;

                case '0':
                        selector.find('.wc-timeline-product-price.striked').removeClass('hidden');
                        selector.find('.preview-striked-price').text( qty * basePrice );
                        newPrice = qty * ( basePrice - amount );
                        selector.find('.preview-price').text( parseFloat( newPrice.toFixed( 2 ) ) );
                        break;

        }      

    }

    $(document).ready(function($) {   
           
            $('.color-picker').wpColorPicker({

                change: function (event, ui) {

                        const element   = event.target;
                        const color     = ui.color.toString();
                        const variab    = $(element).attr('data-variable-change');
                        
                        if( variab )
                        {       
                                $(':root').css('--'+variab, color );
                               
                        }                       
        
                }

            });   

            $(".upsells-container").sortable({
                axis: 'y',
                handle:'.sorting-wrapper'
            });            
            
            $('.new-goal-button').on('click', function(){
                
                $('.new-goal-template .row').clone().appendTo(".dynamic-bar-goals")
                $('.no-goals.wcj-banner').hide()

            })

            $('.woo-upsellator-admin-content .upsell-discount-type').on('change',function(e){

                    const item = $(this).closest('.row-wrapper');
                    item.find('span.discount-type').fadeOut(1);

                    switch( $(this).val() ) 
                    {
                        case '1':                                
                                item.find('.discount-amount-row ').removeClass('hidden');
                                item.find('span.discount-type.percentage-value').fadeIn(1);
                                 break;
                        case '0':
                                item.find('.discount-amount-row ').removeClass('hidden');
                                item.find('span.discount-type.currency-value').fadeIn(1);
                                break;
                        case '2':
                                item.find('.discount-amount-row ').addClass('hidden');
                                break;
                        default:                                
                                break;
                      }
                                        
            });

            $('.woo-upsellator-admin-content .has-popup').on('click',function(e){
                            
                if( $(this).is(':checked') )
                {
                        const text  = $(this).data('text');

                        Swal.fire({
                                icon: 'info',                                
                                html: text,
                                showCloseButton: true,
                                showCancelButton: false,   
                                showConfirmButton: false,                    
                                footer: '<b>J Cart Upsell and cross-sell</b>'
                        })
                }              

            });

            $('.woo-upsellator-admin-content .needs-pro input:not(.free), .woo-upsellator-admin-nav .nav-tab.needs-pro').on('click',function(e){

                e.preventDefault();               
                
                Swal.fire({
                        icon: 'warning',
                        title: __( 'Premium required', 'woo_j_cart' ),
                        html: __( 'purchase our <b><a href="/wp-admin/admin.php?page=wc-j-upsellator-pricing">premium version</a></b> to unlock these features', 'woo_j_cart' ),
                        showCloseButton: true,
                        showCancelButton: false,   
                        showConfirmButton: false,                    
                        footer: '<b>J Cart Upsell and cross-sell</b>'
                })

            });

            $('.woo-upsellator-admin-content .needs-pro select').on('change',function(e){

                e.preventDefault();      
                   
                $(this).find("option").attr('selected',false);
                $(this).find("option:first").attr('selected','selected');

                Swal.fire({
                        icon: 'warning',
                        title: __( 'Premium required', 'woo_j_cart' ),
                        html: __( 'purchase our <b><a href="/wp-admin/admin.php?page=wc-j-upsellator-pricing">premium version</a></b> to unlock these features', 'woo_j_cart' ),
                        showCloseButton: true,
                        showCancelButton: false,   
                        showConfirmButton: false,                    
                        footer: '<b>J Cart Upsell and cross-sell</b>'
                })

            });    
            
            $('.upsell-admin-text').on('input',function(e){
                $(this).closest('.row-wrapper').find('.preview').find('.upsell-text').html( $(this).val() );
            });

            $('.upsell-admin-heading').on('input',function(e){
                $(this).closest('.row-wrapper').find('.preview').find('.upsell-heading').html( $(this).val() );
            }); 
            
            $('.upsell-admin-heading').on('input',function(e){
                $(this).closest('.row-wrapper').find('.preview').find('.upsell-heading').html( $(this).val() );
            });  
            
            $('.gift-qty-input').on('change',function(e){
                $(this).closest('.row-wrapper').find('.preview').find('.qty').html( $(this).val() );  
            }); 
            
            $('.upsell-qty-input').on('change',function(e){

                const qty               = $(this).val()
                const product_wrapper   = $(this).closest('.row-wrapper').find('.preview')                

                if( qty > 1 ) product_wrapper.find('.wc-timeline-product-qty').html( qty +'* ')
                else product_wrapper.find('.wc-timeline-product-qty').html('')   
                
                if( $(this).hasClass('main') )  updatePreviewPrice('.new-upsell-wrapper'); 
                else                            updatePreviewPrice( $(this).closest('.product-wrapper') ); 
                
            }); 

            $('.shipping-after-price').on('input',function(e){                 
                $('.shipping-post-price').html( $(this).val() );
            });

            $('.shipping-before-price').on('input',function(e){                 
                $('.shipping-pre-price').html( $(this).val() );
            });

            $('.select-shipping-icon').on('change',function(e){

                const newIcon = $(this).val();
                $('.shipping-icon').removeClass().addClass('flex-row-center shipping-icon '+ newIcon );

            });

            $('input[name=banner]').on('change',function(e){

                        if( $(this).is(':checked') )
                        {
                                $(this).closest('.row-wrapper').find('.banner-text').removeClass('hidden')
                        }else{

                                $(this).closest('.row-wrapper').find('.banner-text').addClass('hidden')
                        }
            });

            $('input[name=only_background]').on('change',function(e){

                if( $(this).is(':checked') )
                {
                       $('.needs-modal').addClass('transparent');

                       Swal.fire({
                                icon: 'warning',
                                title: __( 'Background mode', 'woo_j_cart' ),
                                html: __( 'we have hidden some options because they are not needed in this mode', 'woo_j_cart' ),
                                showCloseButton: true,
                                showCancelButton: false,   
                                showConfirmButton: false,                    
                                footer: '<b>J Cart Upsell and cross-sell</b>'
                        })

                } else {
                       $('.needs-modal').removeClass('transparent');
                }
            });

            $('.select-shipping-bar-type').on('change',function(e){
                
                $('.shipping-bar-preview-container').removeClass().addClass('shipping-bar-preview-container flex-column-center '+ $(this).val() );

            })

            $('.woo-upsellator-admin-content .upsell-discount-type').on('change',function(e){ 

                if( $(this).hasClass('main') )  updatePreviewPrice('.new-upsell-wrapper'); 
                else                            updatePreviewPrice( $(this).closest('.product-wrapper') ); 

            });

            $('.woo-upsellator-admin-content .discount-amount-input').on('input',function(e){  
                    
                if( $(this).hasClass('main') )  updatePreviewPrice('.new-upsell-wrapper'); 
                else                            updatePreviewPrice( $(this).closest('.product-wrapper') ); 

            });  
            
            $('.woo-upsellator-admin-content .reorder-upsells-form').on('click',function(e){

                e.preventDefault();

                const upsellIDS = [] 

                $('.upsells-container .upsell-wrapper').each( (index, element) => {
                        upsellIDS.push( parseInt( $(element).attr('data-upsell')))
                });

                $(this).find('input[name=order]').val( upsellIDS )

                $(this).submit();

            });

            $('.woo-upsellator-admin-content .delete-upsell').on('click',function(e){

                e.preventDefault();

                const title = $(this).data('title');
                const text  = $(this).data('text');
                const div   = $(this).closest('.product-wrapper');                
                
                Swal.fire({

                        icon: 'error',
                        title: title,
                        text: text,
                        showCloseButton: true,
                        showCancelButton: true,
                        confirmButtonColor: '#c84444',
                        confirmButtonText: __( 'Yes, remove', 'woo_j_cart' ),
                        footer: '<b>J Cart Upsell and cross-sell</b>'

                }).then((result) => {

                        if (result.isConfirmed) { 

                                div.find('.delete-upsell-form').submit();                               
                        }
                })

            });

            $('.woo-upsellator-admin-content .delete-stats-data').on('click',function(e){

                e.preventDefault();

                const title = $(this).data('title');
                const text  = $(this).data('text');                          
                
                Swal.fire({

                        icon: 'error',
                        title: title,
                        text: text,
                        showCloseButton: true,
                        showCancelButton: true,
                        confirmButtonColor: '#c84444',
                        confirmButtonText: __( 'Yes, clear', 'woo_j_cart' ),
                        footer: '<b>J Cart Upsell and cross-sell</b>'

                }).then((result) => {

                        if (result.isConfirmed) { 
                                
                                $('.clear-data-form input[name="period"]').val( $('#clear_period').val() )
                                $('.clear-data-form').submit()
                                                             
                        }
                })

            });

            $('#search_pages').on('input', function(){


                const searched = $(this).val()

                if( searched.length <= 2 )
                {
                        $('.row.page').removeClass('hidden') 
                        return
                }
               
                $('.row.page').each( (index, element) => {
                       
                        const page = $(element).attr('data-page')

                        if( !page.includes( searched ) ) $(element).addClass('hidden')
                        else                             $(element).removeClass('hidden')       
                })

            })

            $(document).on('click', '.woo-upsellator-admin-content .delete-goal',function(e){

                e.preventDefault();

                const title = $(this).data('title');
                const text  = $(this).data('text');
                const div   = $(this).closest('.row');                
                
                Swal.fire({

                        icon: 'error',
                        title: title,
                        text: text,
                        showCloseButton: true,
                        showCancelButton: true,
                        confirmButtonColor: '#c84444',
                        confirmButtonText: __( 'Yes, remove', 'woo_j_cart' ),
                        footer: '<b>J Cart Upsell and cross-sell</b>'

                }).then((result) => {

                        if (result.isConfirmed) { 

                                div.remove();                               
                        }
                })

            });

            $('.woo-upsellator-admin-content .product-condition').on('change', function(e){
                       
                        const item      = $(this).closest('.row-wrapper');                                                
                        
                        item.find('[class^="product-by-"]').addClass('hidden');
                        item.find('.product-by-' + $(this).val() ).removeClass('hidden');                

            });   
           
           
            $('.woo-upsellator-admin-content .upsell-condition').on('change', function(e){
                       
                        const item      = $(this).closest('.row');       
                        
                        if( $(this).val() == 3 )
                        {
                                item.find('.upsell-displayed-number').removeClass('hidden');
                        }else{

                                item.find('.upsell-displayed-number').addClass('hidden');
                        }                        
            }); 
            
            $('.woo-upsellator-admin-content input[name="hide_if_gifted"]').on('change', function(e){
                       
                        const item      = $(this).closest('.row-wrapper');
                        
                        if( $(this).is(':checked') ) item.find('.hide-if-gifted').removeClass('hidden');
                        else                         item.find('.hide-if-gifted').addClass('hidden');   
            });

            $('.woo-upsellator-admin-content .product-toggle').on('click', function(e){
                $(this).closest('.product-wrapper').toggleClass('open closed');               
            }); 

            $('.woo-upsellator-admin-content .new-gift-button').on('click', function(e){
                $('.new-gift-wrapper').toggleClass('hidden');
            });

            $('.upsellator-checkbox.off').click(function(e){
                e.preventDefault();
            });
            
            $('.woo-upsellator-admin-content .new-upsell-button').on('click', function(e){
                $('.new-upsell-wrapper').toggleClass('hidden');
            });

            $('#modal_theme').on('change',function(e){

                        $('.theme-option').fadeOut()
                        $('.theme-' + $(this).val() +'-option').fadeIn();                       

            });  

            $(document).on('change', '.woo-upsellator-admin-content .modal_icon_select', function(e){

                    const item    = $(this).closest('.option').find('span.attribute:not(.font-size)');            
                
                    item.removeClass();
                    item.addClass('attribute '+$(this).val() );
            });    
            
            $('.wc-upsellator-attributes-search').select2({
                minimumInputLength: 3, 
            });

            $('.wc-upsellator-product-search').each( function() {

                let $this = $(this);

                $this.select2({
                        language: {
                                searching: () => __( 'Searching...', 'woo_j_cart' ),
                                noResults: () => __( 'No results found, sorry', 'woo_j_cart' ),
                                inputTooShort: () =>  __( 'We need at least 3 characters', 'woo_j_cart' )
                        },
                        ajax: {
                        url: ajaxurl, 
                        dataType: 'json',
                        delay: 250, 
                        data: function (params) {
                                
                                return {
                                        term:           params.term, 
                                        exclude:        $this.data( 'exclude' ),
                                        security:       $this.attr('data-security'),
                                        action:         'wjufc_search_products',
                                        parent_vis:     $this.attr('data-parentvisible')
                                };
                        },
                        processResults: function( data ) 
                        {        
                                let options = [];
                                if ( data ) 
                                {   
                                        $.each( data, function( index, value ) {

                                                const fulltext = value.name.split(")");
                                                options.push({ 
                                                        id: index, 
                                                        text: fulltext[0] + ")" 
                                                });

                                        
                                        });  
                                        
                                        found_products = data;
                                }                           

                                return { results: options };
                        },                   
                        cache: true
                        },

                        minimumInputLength: 3, 
                        allowClear: true

                }).on('change', function (e) {
                                
                                $this.closest('.row').find('.alert').fadeOut( 300 );
                                $( document ).trigger( "wooj:product:changed", [ $this.val(), $this.attr('id')  ] );                        
                });
             });

             $('.wc-upsellator-category-search').each( function() {

                let $this = $(this);

                $this.select2({
                        language: {
                                searching: () => __( 'Searching...', 'woo_j_cart' ),
                                noResults: () => __( 'No results found, sorry', 'woo_j_cart' ),
                                inputTooShort: () =>  __( 'We need at least 3 characters', 'woo_j_cart' )
                        },
                        ajax: {
                        url: ajaxurl, 
                        dataType: 'json',
                        delay: 250, 
                        data: function (params) {
                                
                                return {
                                        term:           params.term, 
                                        exclude:        $this.data( 'exclude' ),
                                        security:       $this.attr('data-security'),
                                        action:         'wjufc_search_categories',
                                        parent_vis:     $this.attr('data-parentvisible')
                                };
                        },
                        processResults: function( data ) 
                        {        
                                let options = [];
                                if ( data ) 
                                {   
                                        $.each( data, function( index, value ) {
                                                                                        
                                                options.push({ 
                                                        id: index, 
                                                        text: value.formatted_name 
                                                });

                                        
                                        });                               
                                }                           

                                return { results: options };
                        },                   
                        cache: true
                        },

                        minimumInputLength: 3, 
                        allowClear: true

                });
             });
             /*
             /* Attributes Search
             */
             $('.wc-upsellator-attributes-search').each( function() {

                let $this = $(this);

                $this.select2({
                        language: {
                                searching: () => __( 'Searching...', 'woo_j_cart' ),
                                noResults: () => __( 'No results found, sorry', 'woo_j_cart' ),
                                inputTooShort: () =>  __( 'We need at least 3 characters', 'woo_j_cart' )
                        },
                        ajax: {
                        url: ajaxurl, 
                        dataType: 'json',
                        delay: 250, 
                        data: function (params) {
                                
                                return {
                                        term:           params.term, 
                                        exclude:        $this.data( 'exclude' ),
                                        security:       $this.attr('data-security'),
                                        action:         'wjufc_search_attributes',
                                        parent_vis:     $this.attr('data-parentvisible')
                                };
                        },
                        processResults: function( data ) 
                        {        
                                let options = [];
                                if ( data ) 
                                {   
                                        $.each( data, function( index, value ) {
                                                                                        
                                                options.push({ 
                                                        id: index, 
                                                        text: `${value.attribute} ${value.slug} (${value.count})`,   
                                                });

                                        
                                        });                               
                                }                           

                                return { results: options };
                        },                   
                        cache: true
                        },

                        minimumInputLength: 3, 
                        allowClear: true

                });      
             });   
             /*
             /* Switch Upsell/Gift Status
             */
             $(document).on("click", ".switch-product-status", function(e) {

                                const status    = $(this).hasClass('pause') ? 1 : 0;
                                const id        = $(this).attr('data-id');
                                const security  = $(this).attr('data-security');
                                const type      = $(this).attr('data-type');
                                const action    = ( type == 'gift' ) ? 'wjufc_switch_gift_status' : 'wjufc_switch_upsell_status' ;

                                $(this).prop('disabled', true ) 
                                $(this).addClass('loading') 
                                $(this).html('<img style="height:20px" src="' + wc_j.img_path +'loader.svg">')

                                jQuery.ajax({								
                                        url: ajaxurl,
                                        method:'post',
                                        dataType:"json",
                                        context:this,		
                                        data: { action: action, id:id, status:status, security:security },
                                        success: function( data ) {

                                                $(this).removeClass('loading') 
                                                
                                                if( status == 0 )
                                                {
                                                        $(this).addClass('pause')
                                                        $(this).html('<i class="wooj-icon-play">')
                                                      
                                                }else{

                                                        $(this).removeClass('pause')
                                                        $(this).html('<i class="wooj-icon-pause">')                                                       
                                                }

                                                $(this).prop('disabled', false ) 
                                        }				
                                });

                });

             $(document).on("change", "form.wjufc-auto-send :input", function() {
                       
                        $(this).closest('form').submit();
              });

              $(document).on("submit.form-ajax", "form.wjufc-ajax", function( e ) {                       

                        const trigger 	         = $(this).attr('data-event') || 'ajax-event';
                        const button             = $(this).find('button[type="submit"], div[type="async"]')
                        let buttonContent;

                        if( button )
                        {     
                                buttonContent      = button.html()

                                button.prop('disabled', true )        
                                button.addClass('loading')
                                button.html('<img style="height:20px" src="' + wc_j.img_path +'loader.svg">')
                        }

                        const submitted = ajax_submit_form( e, $(this) );                        
                        
                        submitted.done( response  => {	
                                
                                if( response )
                                {                                        
                                        $(document).trigger( trigger, response.body || '' );

                                        if( response.modal )
                                        {
                                                Swal.fire({
                                                        icon: response.icon || 'warning',
                                                        title: response.heading,
                                                        html: response.text,
                                                        showCloseButton: true,
                                                        showCancelButton: false,   
                                                        showConfirmButton: false,                    
                                                        footer: '<b>J Cart Upsell and cross-sell</b>'
                                                })
                                        }                                       
                                } 
                        });

                        submitted.fail( ( response ) => {
                               
                                const err = JSON.parse( response.responseText )

                                Swal.fire({
                                        icon: response.icon || 'warning',
                                        title: response.heading,
                                        html: err.message,
                                        showCloseButton: true,
                                        showCancelButton: false,   
                                        showConfirmButton: false,                    
                                        footer: '<b>J Cart Upsell and cross-sell</b>'
                                })

                                
                        });

                        submitted.always( () => {

                                if( button )
                                { 
                                        button.removeClass('loading')
                                        button.prop('disabled', false ) 
                                        button.html( buttonContent ) 
                                        
                                }
                        })
              });                
        
    });

    $( document ).on( "wooj:upsell:deleted", function( e, data ) {

        if( data ) $('.upsell-wrapper[data-upsell='+ data.id +']').fadeOut( 300 );
    });

    $( document ).on( "wooj:gift:deleted", function( e, data ) {

        if( data ) $('.gift-wrapper[data-gift='+ data.id +']').fadeOut( 300 );
    });
    
    /*
    /* We need to check if this product is already in the upsell list
    */
    $( document ).on( "wooj:product:changed", function( e, product_id, item_id ) {
        
                
                if( product_id && item_id == 'main-product-search' )
                {      
                      
                        const already_exist = $('.selected_product').filter( ( index, element ) => $(element).attr('data-id') == product_id );
                        
                        if( already_exist.length ){

                                $('#main-product-search').val( [] ).trigger('change');

                                Swal.fire({
                                        icon: 'warning',
                                        title: __( 'Product already added', 'woo_j_cart' ),
                                        html: __( 'This product is already added. Change product or remove this from the active upsells/gifts', 'woo_j_cart' ),
                                        showCloseButton: true,
                                        showCancelButton: false,   
                                        showConfirmButton: false,                    
                                        footer: '<b>J Cart Upsell and cross-sell</b>'
                                })           

                        }else{
                                
                                const selected_item = found_products[ product_id ];
                                
                                if( selected_item )
                                {      
                                        $('#main-preview .wc-timeline-product-name').text( selected_item.base_name );
                                        $('#main-preview .preview-img').attr('src', selected_item.img );
                                        $('#main-preview .preview-price').text( selected_item.price );
                                        $('#main-preview .preview-base-price').val( selected_item.price );

                                        updatePreviewPrice('.new-upsell-wrapper');
                                }

                        } 
                }
     });    
     
     function ajax_submit_form( e, form, customData = {} )
     {
		
		e.preventDefault();		
                
                let data;
		const action  	= form.attr('action');
		const method  	= form.attr('method') || 'POST';                
                const security  = form.attr('data-security') || ''; 
                const api       = form.attr('data-api') || false; 

                if( method.toUpperCase() == 'GET' )  data = $.param( form.serializeArray().filter( el => $.trim( el.value ) ) );
                if( method.toUpperCase() == 'POST' ){

                        data = new FormData( form[0] ?? '' );   
                        
                        for ( let names in customData) {
                                data.append( names, customData[names] );
                        }

                        data.append('action', action );
                        data.append('security', security );
                }               

		return jQuery.ajax({
                                url: api ? action : ajaxurl, 
                                dataType:'json',
                                processData: false,
                                contentType: false,
                                context: this,
                                method: method.toUpperCase() ,
                                data: data,   
                                beforeSend: function (xhr) {
                                        xhr.setRequestHeader('X-WP-Nonce', security );
                                }                             
		});		
	}
    

})( jQuery );