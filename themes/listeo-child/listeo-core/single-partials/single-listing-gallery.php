<!-- Content
================================================== -->

<?php $gallery = get_post_meta( $post->ID, '_gallery', true ); 

if(!empty($gallery)) {?>

<?php if(count($gallery)==4){ ?>

<style>
@media(min-width:1024px)
{
div#carousel ul.slides {
    padding-left: 9%;
}
}
</style>
              <?php } ?>
  <?php if(count($gallery)==3){ ?>            
<style>
@media(min-width:1024px)
{
div#carousel ul.slides {
padding-left: 18%;
}
}
</style>

<?php } ?>

  <?php if(count($gallery)==2){ ?>            
<style>
@media(min-width:1024px)
{
div#carousel ul.slides {
padding-left: 30%;
}
}
</style>

<?php } ?>
<?php /*?>	<!-- Slider -->
	<?php 
	echo '<div class="listing-slider mfp-gallery-container1 margin-bottom-0 testclass">';
	$count = 0;
	foreach ( (array) $gallery as $attachment_id => $attachment_url ) {
		$image = wp_get_attachment_image_src( $attachment_id, 'listeo-gallery' );
		$thumb = wp_get_attachment_image_src( $attachment_id, 'medium' );
		echo '<a href="'.esc_url($image[0]).'" data-background-image="'.esc_attr($image[0]).'" class="item mfp-gallery"></a>';
	}
	echo '</div>';
 endif; ?><?php */?>
 
 <div class="col-md-6 col-sm-6 col-xs-12 mainimagetop desktopvi"> 
 
  <?php 
	$main_listing_image_1 = get_field('main_listing_image_1'); 
	 if($main_listing_image_1){ ?>
                
                    <img src="<?php echo $main_listing_image_1; ?>"  class="show-popup"  onclick="toggleClass('.gallery-popup-main', 'popup-gallery-show')" />
                    <?php } ?>
 		<!--<img src="https://devwebgency1.de/wp-content/themes/listeo-child/listeo-core/images/FRCH9181 (1) 1.png" />-->
<!--          <button type="button" class="btn btn-primary btntopmain" data-toggle="modal" data-target="#myModal1">
 <?php esc_html_e(' Alle Bilder', 'listeo_core') ?>
</button> -->
						          <button type="button" class="btn btn-primary btntopmain show-popup" onclick="toggleClass('.gallery-popup-main', 'popup-gallery-show')">
 <?php esc_html_e(' Alle Bilder', 'listeo_core') ?>
</button>
 </div>
  <div class="col-md-6 col-sm-6 col-xs-12 otherimagesmobile desktopvi">
  	<div class="topimage">
     <?php 
	$main_listing_image_2 = get_field('main_listing_image_2'); 
	 if($main_listing_image_2){ ?>
                <a href="#" onclick="toggleClass('.gallery-popup-main', 'popup-gallery-show')" >
                    <img src="<?php echo $main_listing_image_2; ?>" /></a>
                    <?php } ?>
    		<!--<img src="https://devwebgency1.de/wp-content/themes/listeo-child/listeo-core/images/Aussenansicht 1.png" />-->
    </div>
    	<div class="row obileimage" style="padding-top:15px;">
    		<div class="col-md-6 col-sm-6 col-xs-12">
            
     <?php 
	$main_listing_image_3 = get_field('main_listing_image_3'); 
	 if($main_listing_image_3){ ?>
                <a href="#" onclick="toggleClass('.gallery-popup-main', 'popup-gallery-show')" >
                    <img src="<?php echo $main_listing_image_3; ?>" /></a>
                    <?php } ?>
            		<!--<img src="https://devwebgency1.de/wp-content/themes/listeo-child/listeo-core/images/JTGZE4795 1.png" />-->
   			 </div>
             <div class="col-md-6 col-sm-6 col-xs-12">
               <?php 
	$main_listing_image_4 = get_field('main_listing_image_4'); 
	 if($main_listing_image_4){ ?>
                <a href="#" onclick="toggleClass('.gallery-popup-main', 'popup-gallery-show')" >
                    <img src="<?php echo $main_listing_image_4; ?>" /></a>
                    <?php } ?>
             		<!--<img src="https://devwebgency1.de/wp-content/themes/listeo-child/listeo-core/images/JTGZE4795 2.png" />-->
   			 </div>
         </div>
 
 </div>
 

<!-- Modal -->
<?php /*?><div class="modal fade " id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-lg" style="margin:0 auto; height:100%" role="document">
    <div class="modal-content" >
    
        <button type="button" class="close closepopp" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
     
      
        <div id="custCarousel" class="carousel slide" data-ride="carousel" align="center">
        <!-- slides -->
        <div class="carousel-inner">
        
         <?php if(!empty($gallery)) : ?>
	<!-- Slider -->
	<?php 

	$count = 0;
	foreach ( (array) $gallery as $attachment_id => $attachment_url ) {
		$image = wp_get_attachment_image_src( $attachment_id, 'listeo-gallery' );
		$thumb = wp_get_attachment_image_src( $attachment_id, 'medium' );
		if($count==0)
		{
			$ac='active';
		}
		else
		{
			$ac="";
		}
		echo '<div class="carousel-item '.$ac.'"><img src="'.esc_url($image[0]).'"></div>';
		$count++;
	}

 endif; ?>
        
       
        </div>

        <!-- Left right -->
        <a class="carousel-control-prev" href="#custCarousel" data-slide="prev">
          <span class="carousel-control-prev-icon"></span>
        </a>
        <a class="carousel-control-next" href="#custCarousel" data-slide="next">
          <span class="carousel-control-next-icon"></span>
        </a>

        <!-- Thumbnails -->
        <ol class="carousel-indicators list-inline">
        
         <?php if(!empty($gallery)) : ?>
	<!-- Slider -->
	<?php 

	$count = 0;
	foreach ( (array) $gallery as $attachment_id => $attachment_url ) {
		$image = wp_get_attachment_image_src( $attachment_id, 'listeo-gallery' );
		$thumb = wp_get_attachment_image_src( $attachment_id, 'medium' );
		if($count==0)
		{
			$ap='active';
		}
		else
		{
			$ap="";
		}
		echo '<li class="list-inline-item '.$ap.'"> <a id="carousel-selector-'.$count.'" class="selected" data-slide-to="'.$count.'" data-target="#custCarousel"><img src="'.esc_url($image[0]).'" class="img-fluid"></a></li>';
		$count++;
	}

 endif; ?>
        
      
         </ol>
      </div>
            
    </div>
  </div>
</div><?php */?>
 

   
        
        <?php
}
		?>
 
 
 
 
