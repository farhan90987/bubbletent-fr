<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package listeo
 */

?>
<?php if (!function_exists('elementor_theme_do_location') || !elementor_theme_do_location('footer')) { ?>
<!-- Footer
================================================== -->
<?php 
$sticky = get_option('listeo_sticky_footer') ;
$style = get_option('listeo_footer_style') ;

if(is_singular()){

	$sticky_singular = get_post_meta($post->ID, 'listeo_sticky_footer', TRUE); 
	
	switch ($sticky_singular) {
		case 'on':
		case 'enable':
			$sticky = true;
			break;

		case 'disable':
			$sticky = false;
			break;	

		case 'use_global':
			$sticky = get_option('listeo_sticky_footer'); 
			break;
		
		default:
			$sticky = get_option('listeo_sticky_footer'); 
			break;
	}

	$style_singular = get_post_meta($post->ID, 'listeo_footer_style', TRUE); 
	switch ($style_singular) {
		case 'light':
			$style = 'light';
			break;

		case 'dark':
			$style = 'dark';
			break;

		case 'use_global':
			$style = get_option('listeo_footer_style'); 
			break;
		
		default:
			$sticky = get_option('listeo_footer_style'); 
			break;
	}
}

$sticky = apply_filters('listeo_sticky_footer_filter',$sticky);
?>
<div id="footer" class="<?php echo esc_attr($style); echo esc_attr(($sticky == 'on' || $sticky == 1 || $sticky == true) ? " sticky-footer" : ''); ?> ">
	<!-- Main -->
	<div class="container">
		<div class="row">
			<?php
			$footer_layout = get_option( 'pp_footer_widgets','6,3,3' ); 
			
	        $footer_layout_array = explode(',', $footer_layout); 
	        $x = 0;
	        foreach ($footer_layout_array as $value) {
	            $x++;
	             ?>
	             <div class="col-md-<?php echo esc_attr($value); ?> col-sm-6 col-xs-12">
	                <?php
					if( is_active_sidebar( 'footer'.$x ) ) {
						dynamic_sidebar( 'footer'.$x );
					}
	                ?>
	            </div>
	        <?php } ?>

		</div>
		<!-- Copyright -->
		<div class="row">
			<div class="col-md-12">
				<div class="copyrights"> <?php $copyrights = get_option( 'pp_copyrights' , '&copy; Theme by Purethemes.net. All Rights Reserved.' ); 
		
		            echo wp_kses($copyrights,array( 'a' => array('href' => array(),'target' => array(), 'title' => array()),'br' => array(),'em' => array(),'strong' => array(),));
		         ?></div>
			</div>
		</div>
	</div>
</div>

<!-- Back To Top Button -->
<div id="backtotop"><a href="#"></a></div>

<?php if(is_singular('listing')) : 
	$_booking_status = get_post_meta($post->ID, '_booking_status',true);
	if($_booking_status) : ?>
		<!-- Booking Sticky Footer -->
		<div class="booking-sticky-footer">
			<div class="container">
				<div class="bsf-left">
					<?php 
					$price_min = get_post_meta( $post->ID, '_price_min', true );
					if (is_numeric($price_min)) {
						$decimals = get_option('listeo_number_decimals',2);
					    $price_min_raw = number_format_i18n($price_min,$decimals);
					} 
					$currency_abbr = get_option( 'listeo_currency' );
					$currency_postion = get_option( 'listeo_currency_postion' );
					$currency_symbol = Listeo_Core_Listing::get_currency_symbol($currency_abbr);
					
					if($price_min) : ?>
					<h4><?php esc_html_e('Starting from','listeo') ?> <?php if($currency_postion == 'after') { echo $price_min_raw . $currency_symbol; } else { echo $currency_symbol . $price_min_raw; } ?></h4>
					<?php else : ?>
                    <?php // mohsin work start ?>
						<h4 class="pricemobiless"><?php //esc_html_e('Select dates to see prices','listeo') ?>
                        <?php
						//$pernight= esc_html_e(' Nacht','listeo');
						$currency_symbol = Listeo_Core_Listing::get_currency_symbol($currency_abbr);
						foreach ($pricessss as $pricefoo) {
							
							if (isset($pricefoo['menu_elements']) && !empty($pricefoo['menu_elements'])) :
								$i=0;
								
								foreach ($pricefoo['menu_elements'] as $item) {
									
									if($i==0)
									{
									echo $item["price"]." ".$currency_symbol;
									esc_html_e(' / Nacht','listeo');
									}
									$i++;
								}
							endif;
						}
						// mohsin work end
						?>
                        
                        </h4>
					<?php endif; ?>

						<?php 
						if(!get_option('listeo_disable_reviews')){
							$rating = get_post_meta($post->ID, 'listeo-avg-rating', true); 
							if(isset($rating) && $rating > 0 ) : 
								$rating_type = get_option('listeo_rating_type','star');
								if($rating_type == 'numerical') { ?>
									<div class="numerical-rating" data-rating="<?php $rating_value = esc_attr(round($rating,1)); printf("%0.1f",$rating_value); ?>">
								<?php } else { ?>
									<div class="star-rating" data-rating="<?php echo $rating; ?>">
								<?php } ?>
									
								</div>
						<?php endif; 
						}?>
					
				</div>
				<div class="bsf-right">
					<?php $book_btn = get_post_meta($post->ID, '_booking_link',true); 
					if($book_btn){ ?>
					<a href="<?php echo $book_btn; ?>" class="button"><?php esc_html_e('Book Now','listeo') ?></a>
					<?php } else { ?>
					<a href="#custom-booking" class="button"><?php esc_html_e('Book Now','listeo') ?></a>
					<?php } ?>
				</div>
			</div>
		</div>
	<?php endif;
	endif; ?>

</div> <!-- weof wrapper -->
<?php } ?>
<?php if(( is_page_template('template-home-search.php') || is_page_template('template-home-search-slider.php') || is_page_template('template-home-search-video.php') || is_page_template('template-home-search-splash.php')) && get_option('listeo_home_typed_status','enable') == 'enable') { 
	$typed = get_option('listeo_home_typed_text'); 
	$typed_array = explode(',',$typed);
	?>
						<script src="https://cdn.jsdelivr.net/npm/typed.js@2.0.9"></script>
						<script>
						var typed = new Typed('.typed-words', {
						strings: <?php echo json_encode($typed_array); ?>,
						typeSpeed: 80,
						backSpeed: 80,
						backDelay: 4000,
						startDelay: 1000,
						loop: true,
						showCursor: true
						});
						</script>
					<?php } ?>
<?php wp_footer(); ?>
<script type="text/javascript">
	<?php /*?>
	jQuery(document).ready(function(){
		jQuery("#billing_country").attr('required',true);
		

		

		 jQuery('.regular-product, ').click(function(event){
		 	event.preventDefault();	
		 	var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";	
		 	var classList = jQuery(this).attr('class').split(/\s+/);			
		 	var productclass = classList[1].replace ( /[^\d.]/g, '' ); 
		 	var productId =  parseInt(productclass);

		 	jQuery.ajax({
		 		type: "POST",
		 		url: ajaxurl,
		 		dataType: 'JSON',
		 		data: {
		 			action: 'getproductdetails',
		 			productId: productId
		 		},
		 		success: function(res) {
		 			jQuery("#userTable tbody").empty();	

		 			jQuery(".coupons_desc").html(res.product_description);
		 			jQuery(".coupons_faq").html(res.product_faq);
		 			jQuery(".product_title h2").html(res.productname);
		 			jQuery(".product_price p").html(res.productprice);
		 			jQuery(".add_cart").html('<a href="?add-to-cart='+productId+'" data-quantity="1" class="button product_type_simple add_to_cart_button ajax_add_to_cart" data-product_id="'+productId+'" data-product_sku="" rel="nofollow"><i class="fas fa-circle-notch fa-spin"></i> In den Warenkorb</a>'); 	
		 			jQuery(".coupons_image").html('<img src="'+res.imageurl+'">	');


		 			jQuery('#myModal').modal('show');

		 		},
			        error:function(request, status, error) {
				    	console.log("ajax call went wrong:" + request.responseText);
			        }

                        });
		 });
	});<?php */?>

</script>
<!--<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet" />
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>-->

<!-- FlexSlider JS -->
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js?ver=1"></script>
  <?php
  if(get_post_type()=='listing')
  {
	  ?>
<script src="<?php echo get_stylesheet_directory_uri(); ?>/listeo-core/js/jquery.flexslider.js?ver=3"></script>

	<!-- Demo CSS -->
  
     
    <script>
jQuery(document).ready(function(){
	 
	  
	  var myModal = document.getElementById('myModal1');
	   jQuery('#myModal1').on('shown.bs.modal', function() {
   // call your slider function
   
   		     jQuery('#carousel').flexslider({
        animation: "slide",
        controlNav: true,
        animationLoop: false,
        slideshow: false,
        itemWidth: 144, 
        itemMargin: 12,
        asNavFor: '#slider',
		cursor: 'move'
      });
      jQuery('#slider').flexslider({
        animation: "slide",
        controlNav: false,
        animationLoop: false,
        slideshow: false,
        sync: "#carousel",
        start: function(slider){
          jQuery('body').removeClass('loading');
        }
      });
	  

})


// mobile slider



			jQuery('#slidermobile').flexslider({
				animation: "slide",
				controlNav: false,
				animationLoop: false,
				count: true,
				slideshow: false,
				  keyboard: true,
        			touch: true, 
			//sync: "#carousel",
			start: function(slider){
					jQuery('body').removeClass('loading');
					var index = jQuery('#slidermobile li.flex-active-slide').index()+1;
					var total = jQuery('#slidermobile ul.slides li').length;
					jQuery(".activeside").html(total+'/'+index);
			
			},
			after: function(slider) {
			
					var index = jQuery('#slidermobile li.flex-active-slide').index()+1;
					var total = jQuery('#slidermobile ul.slides li').length;  
					jQuery(".activeside").html(total+'/'+index);
			}
			});



});
</script>

<script>
jQuery(document).ready(function(){
  //Add a minus icon to the collapse element that is open by default
  /*	jQuery('.collapse.show').each(function(){
		jQuery(this).parent().find(".fa").removeClass("fa-plus").addClass("fa-minus");
    });*/
	
	jQuery(".faq .mainfaqheader button").on('click', function(){
		
		//jQuery(".faq .card-header button").find('i').removeClass('fa-minus');
		//jQuery(".faq .card-header button").find('i').addClass('fa-plus');
		
		if(jQuery(this).find('i').hasClass('fa-plus'))
		{
		
			jQuery(".faq .description").hide();
			jQuery(".faq .mainfaqheader button").find('i').removeClass('fa-minus');
			jQuery(".faq .mainfaqheader button").find('i').addClass('fa-plus');
			
		
			jQuery(this).parent().next().show();
			jQuery(this).find('i').removeClass('fa-plus');
			jQuery(this).find('i').addClass('fa-minus');
		}
		else if(jQuery(this).find('i').hasClass('fa-minus'))
		{
			//alert("aa");
			jQuery(this).parent().next().hide();
			jQuery(this).find('i').addClass('fa-plus');
			jQuery(this).find('i').removeClass('fa-minus');
		}
		
		})
      
  //Toggle plus/minus icon on show/hide of collapse element
/*	jQuery('.collapse').on('shown.bs.collapse', function(){
		jQuery(this).parent().find(".fa").removeClass("fa-plus").addClass("fa-minus");
	}).on('hidden.bs.collapse', function(){
		jQuery(this).parent().find(".fa").removeClass("fa-minus").addClass("fa-plus");
	}); */      
});
</script>

<script>
/*jQuery(document).ready(function() {
  jQuery('#searchInput').on('keyup', function() {
    var value = jQuery(this).val().toLowerCase();
    jQuery('.popupfaqss  .mainfaqheader button').filter(function() {
      jQuery(this).toggle(jQuery(this).text().toLowerCase().indexOf(value) > -1)
    });
  });
});*/


jQuery(document).ready(function(){
      jQuery(".seacjbtn").on("click", function() {
        var value = jQuery("#searchInput").val().toLowerCase();
        jQuery(".popupsearch .mainfaqcard").each(function() {
          var text = jQuery(this).text().toLowerCase();
          if (text.indexOf(value) > -1) {
            jQuery(this).show();
          } else {
            jQuery(this).hide();
          }
        });
      });
    });


</script>

<script type="text/javascript">
	jQuery(document).ready(function(){
		//console.log("initial"+jQuery(".nwesidedetail").offset().top);
		var toppostion= jQuery(".nwesidedetail").offset().top;
		var divBottom = jQuery('.sidebarclick').outerHeight();
		divBottom=divBottom-90;
		// divBottom= divBottom+toppostion-260;
		divBottom= divBottom+toppostion - document.getElementsByClassName('nwesidedetail')[0].offsetHeight;
		console.log("outerdiv"+divBottom);

		
		jQuery(window).scroll(function(){
			var scrolltops = jQuery(this).scrollTop();
			var finalscroll= scrolltops-toppostion+70;
			console.log(scrolltops);
			if(jQuery(window).width()>=768)
			{
			if(scrolltops>toppostion-40 && scrolltops<divBottom-20)
			{
				console.log("scrolltops"+scrolltops+" toppostion"+toppostion);
			//jQuery(".gform_legacy_markup_wrapper").scrollTop();
			//jQuery(".nwesidedetail").css({ top: finalscroll+'px' });
			
			jQuery('.nwesidedetail').animate({
    top: finalscroll+'px' // Set the desired top position after the delay
  }, 0); 
			
			
			//jQuery(".nwesidedetail").addClass('fixedtops');
			//
			}
			else
			{
				//jQuery(".nwesidedetail").removeClass('fixedtops');
			}
			}
				/*jQuery('html, body').animate({
					
					scrollTop: jQuery(".gform_legacy_markup_wrapper").offset().top
				
				});*/
			
			
			})
		
		
		})
	</script>
    
    <script>
	jQuery(document).ready(function() {
/*  var showChar = 300; // number of characters to show before truncating
  var ellipsestext = "...";
  //jQuery('.truncate').each(function() {
    var content = jQuery(".toplongtext p").html();
    
    if (content.length > showChar) {
      var c = content.substr(0, showChar);
      var h = content.substr(showChar, content.length - showChar);
      var html = c + '<span class="moreellipses">' + ellipsestext + '</span><span class="morecontent"><span>' + h + '</span></span>';
	  //alert(html);
      
      jQuery(".toplongtext p").html(html);
    }*/
  //});
  
  jQuery(".remoretop").click(function(){
	  
			//var longtext= jQuery(".morecontent").text();
			//jQuery(".toplongtext p").html(longtext);
			jQuery(".longtextmain").show();
			jQuery(".shorttextmain").hide();
			
			jQuery(".readlesstop").show();
			jQuery(".remoretop").hide();
			
	  
	  })
	  
	   jQuery(".readlesstop").click(function(){
	  
				//var longtext= jQuery(".morecontent").text();
				//jQuery(".toplongtext p").html( c + '<span class="moreellipses">' + ellipsestext + '</span><span class="morecontent"><span>' + h + '</span></span>');
			jQuery(".longtextmain").hide();
			jQuery(".shorttextmain").show();
			
			jQuery(".readlesstop").hide();
			jQuery(".remoretop").show();
	  
	  })
	  
	  
	  // second block functionality
	  
	  jQuery(".readmoremiddle").click(function(){
	  
			jQuery(".middlelongtext").show();
			jQuery(".middleshorttext").hide();
			
			jQuery(".readlessmiddle").show();
			jQuery(".readmoremiddle").hide();
			
	  
	  })
	  
	   jQuery(".readlessmiddle").click(function(){
	  
			jQuery(".middlelongtext").hide();
			jQuery(".middleshorttext").show();
			
			jQuery(".readlessmiddle").hide();
			jQuery(".readmoremiddle").show();
	  
	  })
	  
	  // why block show hide
	  
	  // second block functionality
	  
	  jQuery(".readmorewhy").click(function(){
	  
			jQuery(".whylongtext").show();
			jQuery(".whyshorttext").hide();
			
			jQuery(".readlesswhy").show();
			jQuery(".readmorewhy").hide();
			
	  
	  })
	  
	   jQuery(".readlesswhy").click(function(){
	  
			jQuery(".whylongtext").hide();
			jQuery(".whyshorttext").show();
			
			jQuery(".readlesswhy").hide();
			jQuery(".readmorewhy").show();
	  
	  })
	  
	  
	  // eviews section show hide
	  
	  jQuery(".readmorereview").click(function(){
	  
			//var longtext= jQuery(".morecontent").text();
			//jQuery(".toplongtext p").html(longtext);
			jQuery(this).parent().prev().show();
			jQuery(this).parent().prev().prev().hide();
			jQuery(this).hide();
			jQuery(this).next().show();
			
	  
	  })
	  
	    jQuery(".readlessreview").click(function(){
	  
			//var longtext= jQuery(".morecontent").text();
			//jQuery(".toplongtext p").html(longtext);
			jQuery(this).parent().prev().hide();
			jQuery(this).parent().prev().prev().show();
			jQuery(this).hide();
			jQuery(this).prev().show();
			
	  
	  })

});

	</script>
    
    <?php
  }
	?>

<!--<div id="myModal" class="modal fade coupon_modal" tabindex="-1" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			</div>
			<div class="modal-body">                	
                	<div class="coupons" id="userTable">
                		<div class="coupons_image"></div>
                		<div class="coupons_info">
                			<div class="product_title"><h2></h2></div>
                			<div class="product_price"><p></p></div>	
                			<div class="add_cart"></div>
                		</div>
                	</div>
                	<h3>Beschreibung</h3>
                	<div class="coupons_desc"></div>
                	<h3>FAQ</h3>
                	<div class="coupons_faq"></div>
                </div>
        </div>
</div>
</div>-->



<!-- -------------->






</body>
</html>