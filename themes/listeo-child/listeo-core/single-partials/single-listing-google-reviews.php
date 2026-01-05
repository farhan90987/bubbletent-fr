<?php

global $post;
global $wpdb; 	


$place_id = get_post_meta($post->ID,'_place_id', true);
//echo "$place_id". "test".$post->ID;

if(!empty($place_id)){
	$place_data = listeo_get_google_reviews($post);

	if(empty($place_data['result']['reviews'])){
		return;
	} else {
		$reviews = $place_data['result']['reviews'];	
	}
	
//echo "testing";
?>

  <hr class="mainhr1" />
                        
                        <div class="row">
                        <div class="col-md-12">
<div id="listing-google-reviews" class="listing-section">
	<div class="row">
    <div class="col-md-8">
    
    <h3 class="heading33">
    <?php esc_html_e('Standort bewertungen','listeo_core'); ?>
                            
                            </h3>
     </div>
	
    <?php
	if(isset($reviews) && !empty($reviews)){?>
    <div class="col-md-4">
	
	<div class="google-reviews-new">
<!--	    <div class="google-reviews-summary-logo" style="display:none !important"></div>
-->	    <div class="google-reviews-summary-avg">
	        <strong class="reviewcount"><?php echo number_format_i18n($place_data['result']['rating'],1); ?></strong>
	        <div class="star-rating" data-rating="<?php echo $place_data['result']['rating']; ?>"></div>
	     <?php /*?>   <span>
<?php
		$google_reviews_count = $place_data['result']['user_ratings_total'];
		printf( // WPCS: XSS OK.
			esc_html( _nx(  'Review %1$s','%1$s reviews', $google_reviews_count, 'comments title', 'listeo_core' ) ),
			 number_format_i18n(  $google_reviews_count )
		);
	?>
	        </span><?php */?>
	    </div>
	 
	    
	</div>
    
    </div>
    </div>
	
    <section class="comments listing-reviews">
    	<div class="comment-list row">
    		<?php  
			$i=0;
			foreach ($reviews as $key => $review) {
				if($i<=2){
				 ?>
    			<div class="col-md-4 col-sm-4 col-xs-6 colreviews <?php if($i<=1){ echo "mobileshowsss"; } else { echo "desktopreview reviewdesktop"; }?>" >
                <div class="eachreview">
    
                   	<div class="avatar desktopreview"><img src="<?php echo esc_attr($review['profile_photo_url']); ?>" alt="<?php echo $review['author_name'];  ?>">
                    <h5 class="desktopreview"><a href="<?php echo esc_url($review['author_url']); ?>" target="_blank"> <?php echo $review['author_name'];  ?></a></h5> 
                    </div>
            		<div class="comment-content"><div class="arrow-comment"></div>
            		
            			 <h5 class="mobileshowsss" style="text-align:center;"><a href="<?php echo esc_url($review['author_url']); ?>" target="_blank"> <?php echo $review['author_name'];  ?></a></h5> 
						<?php /*?><div class="comment-by">
            				
            				
            		        <span class="date"><?php echo esc_attr($review['relative_time_description']); ?></span>
    			        	<div class="star-rating" data-rating="<?php echo esc_attr($review['rating']); ?>"></div>
            			</div><?php */
						
						
						if(strlen($review['text'])>=150)
						{
						
						?>
            			<p class="shortreview">
                        <?php echo substr($review['text'],0,150)." ......";?>
                        </p>
                        <p class="fullreview" style="display:none">	<?php echo $review['text']; ?></p>
                        
                        <div class="linkright"> <a href="javascript:void(0)" class="readmore readmorereview ">
                      <?php esc_html_e('mehr Erfahren', 'listeo_core'); ?></a>
                      <a href="javascript:void(0)" class=" readmore readless readlessreview ">
                      <?php esc_html_e('Text Einklappen', 'listeo_core'); ?>
                      </a>
                      
                      </div>
                      
                      <?php
						}
						else
						{
					  ?>
                       <p>	<?php echo $review['text']; ?></p>
                      <?php
						}
					  ?>
                        
                        
                    </div>
    </div>
    			</div>
     		<?php }
			$i++;
			} ?>
    	</div>
    <?php }
      ?>
      
    </section>
	    <div class="google-reviews-read-more bottom">
	        <a href="https://search.google.com/local/reviews?placeid=<?php echo $place_id ?>" target="_blank"><img src="<?php echo get_template_directory_uri(); ?>/images/google-reviews-logo.svg" alt=""><?php esc_html_e('Read More Reviews','listeo_core'); ?></a>
            
            
	        <a href="https://search.google.com/local/writereview?placeid=<?php echo $place_id; ?>" target="_blank"><img src="<?php echo get_template_directory_uri(); ?>/images/google-reviews-button-icon.svg" alt=""><?php esc_html_e('Add Review','listeo_core'); ?></a>
	   
	    </div>
	  </div>
      </div>
      </div>
<?php } ?>
