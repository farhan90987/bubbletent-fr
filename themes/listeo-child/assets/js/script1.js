jQuery(document).ready(function() {


	jQuery(".listings-filters > a").on("click", function(){

		jQuery(this).siblings().removeClass('active');
		jQuery(this).addClass('active');

		jQuery('#listings-wrap').addClass('loading');
		var tax_id = jQuery(this).attr('id');


		var data = {
			'action': 'listng_filters',
			'tax-id': tax_id
		};
		jQuery.ajax({
            url: ajax_object.ajax_url,
            data: data, 
            type: 'POST', 

            success:function(data){
                jQuery('#listings-wrap').html(data);
                jQuery('#listings-wrap').removeClass('loading');
            }
        });

	});



});

