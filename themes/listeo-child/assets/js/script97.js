jQuery(document).ready(function() {
	

	jQuery(".listings-filters > a").on("click", function(){

		jQuery(this).siblings().removeClass('active');
		jQuery(this).addClass('active');

		jQuery('#listings-wrap').addClass('loading');
		var tax_id = jQuery(this).attr('id');
		var reg_id = jQuery('.regions-filters > a.active').attr('id');

		var data = {
			'action': 'listng_filters',
			'tax-id': tax_id,
			'reg-id': reg_id
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

	jQuery(".regions-filters > a").on("click", function(){

		jQuery(this).siblings().removeClass('active');
		jQuery(this).addClass('active');
		
		jQuery('.listings-filters > a').removeClass('active');
		jQuery('.listings-filters > a#all_loc').addClass('active');

		jQuery('#listings-wrap').addClass('loading');
		var reg_id = jQuery(this).attr('id');
		// var tax_id = jQuery('.listings-filters > a.active').attr('id');
		var tax_id = 'all_loc';

		if( reg_id == 187 ){
			jQuery('.listings-filters > a[catName="typ216"]').hide();
			jQuery('.listings-filters > a[catName="typ222"]').hide();
			jQuery('.listings-filters > a[catName="typ214"]').hide();
			jQuery('.listings-filters > a[catName="typ215"]').hide();
			jQuery('.listings-filters > a[catName="typ186"]').hide();
			jQuery('.listings-filters > a[catName="typ155"]').hide();
			jQuery('.listings-filters > a[catName="typ156"]').hide();
			jQuery('.listings-filters > a[catName="typ221"]').hide();
			jQuery('.listings-filters > a[catName="typ213"]').hide();
		} else {
			jQuery('.listings-filters > a[catName="typ216"]').show();
			jQuery('.listings-filters > a[catName="typ222"]').show();
			jQuery('.listings-filters > a[catName="typ214"]').show();
			jQuery('.listings-filters > a[catName="typ215"]').show();
			jQuery('.listings-filters > a[catName="typ186"]').show();
			jQuery('.listings-filters > a[catName="typ155"]').show();
			jQuery('.listings-filters > a[catName="typ156"]').show();
			jQuery('.listings-filters > a[catName="typ221"]').show();
			jQuery('.listings-filters > a[catName="typ213"]').show();
		}

		var data = {
			'action': 'listng_filters',
			'tax-id': tax_id,
			'reg-id': reg_id
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






	jQuery('.proForm-cbox input').change(function() {

		jQuery('.region-tents').addClass('loading');
		var cur_trmID = jQuery('#cur_trm_id').val();
	    let arr = [];

	    jQuery(".proForm-cbox input:checkbox:checked").each(function() { 
	        arr.push(jQuery(this).val()); 
	    });
	            
	    var data = {
			'action': 'rgn_tents_filter',
			'features': arr,
			'curID': cur_trmID
		};
		jQuery.ajax({
	        url: ajax_object.ajax_url,
	        data: data, 
	        type: 'POST', 

	        success:function(data){
	            jQuery('.region-tents').html(data);
	            jQuery('.region-tents').removeClass('loading');
	        }
	    });

		    
	}); 
	if ( jQuery(window).width() < 768) {
       jQuery(".ts-dsk-sidebar").appendTo(".ts-mobsidebar");
    }

});

