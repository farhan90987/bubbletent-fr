(function($) {	

    $.fn.inputsToArray = function () {		
			
			const firstArr = this.map(function () {

				return this.elements ? jQuery.makeArray( this.elements ) : this;

			});

			return firstArr.filter( (index, elem ) => elem.value != null )
							.map(  (index, elem ) => {

								if ( elem.type == "checkbox" && elem.checked == false) 
								{
										return { name: elem.name, value: elem.checked ? elem.value : '' }						
								}

								if( jQuery.isArray( elem.value ) )
								{
									return jQuery.map( $(this).val(), function (val, i) {
										return {name: elem.name, value: val.replace( /\r?\n/g, "\r\n")};
									}) 
								}

								return  { name: elem.name, value: elem.value.replace( /\r?\n/g, "\r\n") };					
					
			}).get();
	  };

	  $(document).on('click touchend', '.single_add_to_cart_button:not(.disabled)', function (e) {

			const button 	 = $(this)
			const form 		 = button.closest('form.cart')	
			/*
			/* YITH Gift card compatibilty
			*/		
			if( form.hasClass('gift-cards_form') ) return; 

			const data 		 = form.find('input:not([name="product_id"]), select, button, textarea').inputsToArray() || 0;
			const replicated = [...data]
			
			$.each(data, function (i, item) {

				if (item.name == 'add-to-cart') 
				{
					item.name = 'product_id';
					item.value = form.find('input[name=variation_id]').val() || button.val() || item.value ;
				}

			});
			
			e.preventDefault();
		
			$(document.body).trigger('adding_to_cart', [ button, data ]);

			button.removeClass('added').addClass('loading')

			const added = ajaxAddToCart( replicated )
			
			added.done( response => {

					button.addClass('added').removeClass('loading') 
					
					if (response.error & response.product_url) return;
					$(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, button]);

			})
		
			return false;
	
	  });

	  function ajaxAddToCart( data )
	  {					
			return jQuery.ajax({								
				type: 'POST',
				url: woocommerce_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart'),
				data: data,						
			});
	  }

})( jQuery );	