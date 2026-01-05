<?php
if (!defined('ABSPATH')) {
	exit;
}

$template_loader = new Listeo_Core_Template_Loader;

get_header(get_option('header_bar_style', 'standard'));

$layout = get_option('listeo_single_layout', 'right-sidebar');
$mobile_layout = get_option('listeo_single_mobile_layout', 'right-sidebar');

$gallery_style = get_post_meta($post->ID, '_gallery_style', true);
$listing_logo = get_post_meta($post->ID, '_listing_logo', true);

if (empty($gallery_style)) {
	$gallery_style = get_option('listeo_gallery_type', 'top');
}

$count_gallery = listeo_count_gallery_items($post->ID);

if ($count_gallery < 4) {
	$gallery_style = 'content';
}
if ($count_gallery == 1) {
	$gallery_style = 'none';
}


$packages_disabled_modules = get_option('listeo_listing_packages_options', array());
if (empty($packages_disabled_modules)) {
	$packages_disabled_modules = array();
}

$user_package = get_post_meta($post->ID, '_user_package_id', true);

if ($user_package) {
	$package = listeo_core_get_user_package($user_package);
} else {
	$package = false;
}


$load_gallery = false;
if (in_array('option_gallery', $packages_disabled_modules)) {
	if ($package && $package->has_listing_gallery() == 1) {
		$load_gallery = true;
	}
} else {
	$load_gallery = true;
}

$load_video = false;
if (in_array('option_video', $packages_disabled_modules)) {
	if ($package && $package->has_listing_video() == 1) {
		$load_video = true;
	}
} else {
	$load_video = true;
}

$load_reviews = false;
if (in_array('option_reviews', $packages_disabled_modules)) {
	if ($package && $package->has_listing_reviews() == 1) {
		$load_reviews = true;
	}
} else {
	$load_reviews = true;
}

?>
<style>
.activeside {
	border-radius: 10px;
	background: rgba(255, 255, 255, 0.70);
	color: #54775E;
	font-family: Montserrat;
	font-size: 14px;
	font-style: normal;
	font-weight: 600;
	position: absolute;
	padding: 4px 20px;
	bottom: 14px;
	right: 21px;
	z-index: 1;
}
.modal-backdrop.show {
	opacity: .8 !important;
}
.carousel-control-next-icon {
	background-image: url(Group67.svg);
	width: 35px !important;
	background-size: cover;
	height: 35px;
}
.carousel-control-prev-icon {
	background-image: url(Group66.svg);
	width: 35px !important;
	background-size: cover;
	height: 35px;
}
.carousel-inner img {
	width: 100%;
	height: 100%;
}
#custCarousel .carousel-indicators {
	position: absolute;
	/* margin-top: 20px; */
	bottom: 44px;
}
#custCarousel .carousel-indicators>li {
	width: 100px;
}
#custCarousel .carousel-indicators li img {
	display: block;
	opacity: 0.5;
}
#custCarousel .carousel-indicators li.active img {
	opacity: 1;
}
#custCarousel .carousel-indicators li:hover img {
	opacity: 0.75;
}
.carousel-item img {
	width: 100%;
}
button.close.closepopp {
	position: absolute;
	right: 15px;
	top: 15px;
	z-index: 999999999999999;
	background: #fff;
	border-radius: 50%;
	padding: 1px 9px 2px 8px;
	opacity: 0.5;
}
button.close span {
	font-size: 30px;
}
.scrollbarnew .modal-body {
	width: 98%;
}
#custCarousel .carousel-indicators>li img {
	border-radius: 19px;
}
#custCarousel .carousel-indicators>li {
	background: transparent;
}
div#carousel {
	/*  position: absolute;
	bottom: 5px;
	border: none !important;
	background: transparent !important;*/
	padding-top: 5px;
}
#carousel .slides .flex-active-slide img {
	border: 3.5px solid #54775E;
}
.flexslider .slides img {
	height: auto;
	border-radius: 20px;
	-moz-user-select: none;
}
.slider {
	position: relative;
}
div#carousel ul.slides li {
	/*width: 148px !important;*/
}
.eachreview .avatar {
	padding-bottom: 10px;
}
div#carousel ul.slides li.adjustslide {
	width: 130px !important;
}
div#carousel ul.flex-direction-nav {
	display: none;
}
div#slider {
	border: none !important;
}
div#slider ul.flex-direction-nav {
	position: absolute;
	top: 50%;
	width: 100%;
}
.flex-direction-nav .flex-next {
	right: 17px;
	text-align: right;
	opacity: 1;
}
.flex-direction-nav .flex-prev {
	left: 17px;
	opacity: 1 !important;
	z-index: 10 !important;
}
a.flex-prev::before {
	content: none;
}
a.flex-next::before {
	content: none !important;
}
a.flex-prev {
	background: url(<?php echo get_stylesheet_directory_uri(); ?>/listeo-core/images/Group66.svg);
	background-size: cover;
	color: transparent;
}
a.flex-next {
	background: url(<?php echo get_stylesheet_directory_uri(); ?>/listeo-core/images/Group67.svg);
	background-size: cover;
	color: transparent;
}
div#carousel li img {
	border-radius: 9px;
}
div#slider ul.slides li img {
	border-radius: 15px;
}
div#myModal {
	top: 30px;
}
div#myModal .modal-content {
	background: transparent;
}
div#slider {
	background: transparent !important;
}
div#slider ul li img {
	height: 500px;
}
div#carousel li img {
	/*  height: 127px;*/
	max-height: 90px;
}


.flexslider {
	margin: 0;
	background: transparent !important;
	border: none !important;
}
div#carousel {
	padding-left: 10px;
	padding-right: 10px;
	margin-top: 12px;
}
.slider {
	background: transparent;
}
.slider:before {
	display: none;
}

body {
	position: relative;
}
.gallery-popup-main.popup-gallery-show~body {
	margin: 0;
	height: 100%;
	overflow: hidden
}
.gallery-popup-main {
	width: 100%;
	height: 0vh;
	overflow: auto;
	position: fixed;
	top: 100%;
	transition: .3s;
	background-color: #fff;
	left: 0;
	opacity: 0;
	z-index: -1;
	scrollbar-width: none;
}
.gallery-popup-main .popup-gallery {
	display: grid;
	grid-template-columns: 1fr 1fr;
	grid-template-rows: repeat(auto-fill, minmax(200px, 700PX));
	grid-gap: 30px;
	overflow: auto;

	-ms-overflow-style: none;
	padding: 20px;
	max-width: 1440px;
	margin: auto;
	margin-bottom: 55px;
	grid-template-areas:
		"item1 item1"
		"item2 item3"
		"item4 item4";
}
.gallery-popup-main .popup-gallery a {
	display: flex;
	overflow: hidden;
	max-height: 700px;
}
.gallery-popup-main .popup-gallery img {
	width: 100%;
	height: 100%;
	object-fit: cover;
}
.lg-backdrop {
	background-color: #000000a3;
}
.gallery-popup-main.popup-gallery-show {
	top: 0%;
	opacity: 1;
	z-index: 1010;
	height: 100vh;
}
.booking-sticky-footer {
	z-index: 999 !important;
}
.gallery-popup-main .head {
	height: 75px;
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 0 10px;
	box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
	position: -webkit-sticky;
	/* For Safari */
	position: sticky;
	/* Standard */
	top: 0;
	z-index: 1000;
	background: #fff;
}
.gallery-popup-main .head a {
	color: #000;
}
.gallery-popup-main .head i {
	font-size: 25px;
	font-weight: 900;
}
.gallery-popup-main .head span {
	display: flex;
	gap: 15px;
}

@media (max-width: 700px) {
	.gallery-popup-main .popup-gallery {
		display: grid;
		grid-template-columns: 1fr 1fr;
		grid-template-rows: repeat(auto-fill, minmax(200px, 1fr));
		grid-gap: 10px;
		padding: 0px;
		max-width: 1440px;
		margin: auto;
		margin-bottom: 35px;
	}
	.gallery-popup-main .head {
		height: 55px;
		box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
	}
}

@media (max-width: 400px) {
	.gallery-popup-main .popup-gallery a {
		max-height: 491px;
	}
}
.mobile-slider2 {
	width: 100%;
	height: 350px;
	border-radius: 15px;
	overflow: hidden;
	position: relative;
}
.mobile-slider2 img {
	display: flex;
	width: 100%;
	height: 100%;
	object-fit: cover;
	position: absolute;
	left: 0;
	top: 0;
}
.mobile-slider2 button {
	position: absolute;
	z-index: 6;
	right: 20px;
	bottom: 20px;
}

body {
	position: relative;
}
.gallery-popup-main .popup-gallery a:nth-child(1) {
	grid-area: item1;
}
.gallery-popup-main .popup-gallery a:nth-child(2) {
	grid-area: item2;
}
.gallery-popup-main .popup-gallery a:nth-child(3) {
	grid-area: item3;
}
.gallery-popup-main .popup-gallery a:nth-child(4) {
	grid-area: item4;
}
body:not(.lg-from-hash) .lg-outer.lg-start-zoom .lg-item.lg-complete .lg-object {
	max-height: 700px;
}
.maintop h1 {
	padding-bottom: 5px !important;
}
.form-row {
	display: block;
}
.row.mqtext .custom-marquee-container img {
	margin-bottom: 0 ;
}
.row.mqtext {
	padding-right: 15px;
	padding-left: 15px;
	margin-top: 20px;
}
/* Ensure marquee spans full width */
.row.mqtext .custom-marquee-container {
    width: 100%;
    overflow: hidden;
    white-space: nowrap;
    position: relative;
    background: #F5F5F5;
    padding: 15px 0;
    border-radius: 10px;
}

/* Marquee animation */
.custom-marquee {
    display: inline-block;
    width: auto;
    animation: custom-marquee 20s linear infinite; /* Adjusted speed */
}

/* Each marquee item */
.custom-marquee-item {
    display: inline-flex;
    align-items: center;
    margin-right: 100px; /* Increased space between items */
    font-size: 18px;
    color: #333;
    vertical-align: middle;
}

/* Custom icons */
.custom-marquee-icon {
    width: 32px;
    height: 32px;
    margin-right: 12px;
    vertical-align: middle;
}

/* Full-width animation */
@keyframes custom-marquee {
    0% { transform: translateX(100%); }
    100% { transform: translateX(-100%); }
}

/* Adjust text alignment with the icon */
.custom-marquee-item span {
    display: inline-block;
    vertical-align: middle;
    margin-left: 8px;
}

/* Adjustments for Mobile Devices */
@media (max-width: 768px) {
    /* Remove extra margin for mobile view */
    .custom-marquee-item {
        margin-right: 50px; /* Adjust for mobile spacing */
    }

    /* Speed up animation for mobile */
    .custom-marquee {
        animation: custom-marquee 15s linear infinite; /* Faster for mobile */
    }

    /* Adjust icon margin for mobile */
   
    /* Ensure marquee items start immediately without delay */
    .custom-marquee-container {
        padding-left: 0; /* Remove unnecessary padding on left */
    }
}

</style>
<!-- Content
================================================== -->






<div class="container <?php //echo esc_attr($listing_type); ?>">
	<div class="row sticky-wrapper">
		<!-- Sidebar
		================================================== -->
		<!-- " -->
		<?php /*?>     <?php     
			  if (have_posts()) :



	  $listing_type = get_post_meta(get_the_ID(), '_listing_type', true);

	  if ($gallery_style == 'top' && $load_gallery == true) :

		  $template_loader->get_template_part('single-partials/single-listing', 'gallery');
	  else : ?>
		  <!-- Gradient-->
		  <div class="single-listing-page-titlebar"></div>
	  <?php endif; ?><?php */ ?>






		<?php if ($layout == "left-sidebar" || ($layout == 'right-sidebar' && $mobile_layout == "left-sidebar")): ?>
			<div class="col-lg-4 newcl col-md-4 listeo-single-listing-sidebar <?php if ($layout == 'right-sidebar' && $mobile_layout == "left-sidebar")
				echo "col-lg-push-8"; ?> margin-top-75 sticky">
				<?php do_action('listeo/single-listing/sidebar-start'); ?>
				<?php if ($listing_type == 'classifieds') {
					$currency_abbr = get_option('listeo_currency');
					$currency_postion = get_option('listeo_currency_postion');
					$currency_symbol = Listeo_Core_Listing::get_currency_symbol($currency_abbr);

					?>
					<span id="classifieds_price"><?php if ($currency_postion == "before") {
						echo $currency_symbol;
					}
					echo get_post_meta($post->ID, '_classifieds_price', true);
					if ($currency_postion == "after") {
						echo $currency_symbol;
					} ?></span>
				<?php } ?>





				<?php if ($listing_type != 'classifieds') { ?>


					<?php if (get_post_meta($post->ID, '_verified', true) == 'on'): ?>
						<!-- Verified Badge -->
						<div class="verified-badge with-tip"
							data-tip-content="<?php esc_html_e('Listing has been verified and belongs to the business owner or manager.', 'listeo_core'); ?>">
							<i class="sl sl-icon-check"></i> <?php esc_html_e('Verified Listing', 'listeo_core') ?>
						</div>
					<?php else:


						if (get_option('listeo_claim_page_button')) {
							$claim_page = get_option('listeo_claim_page'); ?>
							<div class="claim-badge with-tip"
								data-tip-content="<?php esc_html_e('Click to claim this listing.', 'listeo_core'); ?>">
								<?php
								$link = add_query_arg('subject', get_permalink(), get_permalink($claim_page)); ?>

								<a href="<?php echo $link; ?>"><i class="sl sl-icon-question"></i>
									<?php esc_html_e('Not verified. Claim this listing!', 'listeo_core') ?></a>
							</div>
						<?php }

					endif; ?>
				<?php } ?>
				<?php get_sidebar('listing'); ?>
				<?php do_action('listeo/single-listing/sidebar-end'); ?>
			</div>
			<!-- Sidebar / End -->
		<?php endif; ?>
		<!-- single listing slider html code -->

		<?php
		$gallery = get_post_meta($post->ID, '_gallery', true);
		if (!empty($gallery)): ?>
			<div class="gallery-popup-main">
				<div class="head">
					<a href="#" class="show-popup" onclick="toggleClass('.gallery-popup-main', 'popup-gallery-show')"><i
							class="bi bi-chevron-left"></i></a>
				</div>
				<div class="popup-gallery">
					<?php

					$count = 0;
					foreach ((array) $gallery as $attachment_id => $attachment_url) {
						$image = wp_get_attachment_image_src($attachment_id, 'listeo-gallery');
						$thumb = wp_get_attachment_image_src($attachment_id, 'medium');
						?>
						<a href="<?= esc_url($image[0]) ?>">
							<img src="<?= esc_url($image[0]) ?>" alt="">
						</a>
					<?php } ?>

				</div>
			</div>
		<?php endif; ?>

		<!-- single listing slider html code -->

		<?php while (have_posts()):
			the_post(); ?>

			<div class="col-md-12 maintop">

				<h1 class="desktopvi"><?php the_title(); ?></h1>

				<?php
				// location address
				$friendly_address = get_post_meta($post->ID, '_friendly_address', true);
				if ($friendly_address) {
					?>
					<p class="locationmarkanddata desktopvi"><img
							src="<?php echo get_stylesheet_directory_uri(); ?>/listeo-core/images/map.svg" />
						<span><?php echo $friendly_address; ?></span>
					</p>
					<?php
				}
				?>
				<!-- slider top-->

				<?php
				$listing_type = get_post_meta(get_the_ID(), '_listing_type', true);

				if ($gallery_style == 'top' && $load_gallery == true) {

					$template_loader->get_template_part('single-partials/single-listing', 'gallery');
				} else { ?>
					<!-- Gradient-->
					<div class="single-listing-page-titlebar"></div>
				<?php } ?>
			</div>
			<div class="row mobileview maintop d-block d-md-none d-lg-none" style="margin-left: 0;margin-right: 0;">
				<div class="col-md-12">
					<h1 class="" style="text-align:center;  padding-bottom:0px; margin-bottom:0px;"><?php the_title(); ?>
					</h1>
					<?php
					// location address
					$friendly_address = get_post_meta($post->ID, '_friendly_address', true);
					if ($friendly_address) {
						?>
						<p class="locationmarkanddata "><img
								src="<?php echo get_stylesheet_directory_uri(); ?>/listeo-core/images/map.svg" />
							<span><?php echo $friendly_address; ?></span>
						</p>
						<?php
					}
					?>
					<div class="mobile-slider2">
						<button type="button" class="btn btn-primary btntopmain show-popup"
							onclick="toggleClass('.gallery-popup-main', 'popup-gallery-show')">
							<?php esc_html_e(' Alle Bilder', 'listeo_core') ?>
						</button>
						<img src="<?= get_field('main_listing_image_1') ?>" alt="" class="show-popup"
							onclick="toggleClass('.gallery-popup-main', 'popup-gallery-show')">
					</div>

				</div>
			</div>

			<!--<div class="col-md-4">
			</div>-->

			<!--  -->
			<!-- Ts Dev Code Started -->

			  <!-- Marquee start -->

					<?php 
					if (get_field('marquee_text') == 'yes') {
						// Retrieve the fields inside the marquee_section group
						$marquee_text_1 = get_field('marquee_section')['marquee_text_1'];
						$marquee_text_2 = get_field('marquee_section')['marquee_text_2'];
						$marquee_text_3 = get_field('marquee_section')['marquee_text_3'];
						
						// Check if at least one text field has a value
						if ($marquee_text_1 || $marquee_text_2 || $marquee_text_3) { 
					?>
							<div class="row mqtext" style="margin-left: 0; margin-right: 0; width:100%;">
								<div class="custom-marquee-container">
									<div class="custom-marquee">
										<!-- Check and display marquee items -->
										<?php if (!empty($marquee_text_1)) { ?>
											<span class="custom-marquee-item">
												<img src="/wp-content/uploads/2024/11/snow.svg" alt="Icon 1" class="custom-marquee-icon" />
												<span><?php echo esc_html($marquee_text_1); ?></span>
											</span>
										<?php } ?>
										<?php if (!empty($marquee_text_2)) { ?>
											<span class="custom-marquee-item">
												<img src="/wp-content/uploads/2024/11/gift.svg" alt="Icon 2" class="custom-marquee-icon" />
												<span><?php echo esc_html($marquee_text_2); ?></span>
											</span>
										<?php } ?>
										<?php if (!empty($marquee_text_3)) { ?>
											<span class="custom-marquee-item">
												<img src="/wp-content/uploads/2024/11/tag.svg" alt="Icon 3" class="custom-marquee-icon" />
												<span><?php echo esc_html($marquee_text_3); ?></span>
											</span>
										<?php } ?>
									</div>
								</div>
							</div>
					<?php 
						} 
					} 
					?>


			<div class="ts-row">
				<div class="col-lg-8 col-md-8 listeo-single-listing-content <?php if ($layout == 'right-sidebar' && $mobile_layout == "left-sidebar") {
					echo "col-lg-pull-4";
				} ?> padding-right-30">
					<?php if ( get_field('small_banner_image') && get_field('small_banner') && get_field('small_banner') == 'yes' ) { ?>
						<img src="<?php echo get_field('small_banner_image'); ?>" class="listing-sm-banner" />
					<?php } ?>
					<div class="ts-mobsidebar"></div>
					<?php if (get_field('highlight_heading')) { ?>
						<h2 class="subheading"><?php echo get_field('highlight_heading'); ?></h2>
					<?php } ?>
					<div class=" icontops topiconcenter " style="padding-bottom:0px">

						<?php
						$highlightfeatures = get_field('highlight_features');
						if ($highlightfeatures) {

							$i = 0;
							foreach ($highlightfeatures as $feature) {

								?>

								<div class="iconstopaaa <?php if ($i == 1) {
									echo "centericonss";
								} ?><?php if ($i == 0) {
									 echo "leftalignnn";
								 } ?><?php if ($i == 2) {
									  echo "rightalignnn  desktopviewww ";
								  } ?>"">
					<?php if (has_post_thumbnail($feature->ID)) { ?>
					<?php
					$image = wp_get_attachment_url(get_post_thumbnail_id($feature->ID, 'full'));
					?>
					<img src=" <?php echo $image; ?>" />
								<?php } ?>
								<span class="icontext">
									<?php echo $feature->post_title;
									?>
								</span>
							</div>

							<?php
							$i++;

							}
						}
						?>


				</div>


				<hr class="mainhr" style="margin-bottom: 22px; margin-top:10px;" />
				</hr>
				<!-- Titlebar -->
<!-- 				<div class="ts-mobsidebar"></div> -->
				<div class="row">
					<div class="col-md-12">
						<?php if (get_field('offer_heading')) { ?>
							<h3 class="heading33"><?php echo get_field('offer_heading'); ?></h3>
						<?php } ?>

						<div class="description-long toplongtext">
							<p class="shorttextmain">
								<?php if (get_field('offer_description_top')) {
									$shortertext = strip_tags(get_field('offer_description_top'));
									?>
									<?php echo substr($shortertext, 0, 300) . " ......"; ?>
								<?php } ?>

							</p>
							<div class="longtextmain" style="display:none;">
								<?php if (get_field('offer_description_top')) { ?>
									<?php echo get_field('offer_description_top'); ?>
								<?php } ?>

							</div>
							<div class="linkright"> <a href="javascript:void(0)" class="readmore remoretop">
									<?php esc_html_e('mehr erfahren', 'listeo_core'); ?></a>
								<a href="javascript:void(0)" class=" readmore readless readlesstop">
									<?php esc_html_e('Text einklappen', 'listeo_core'); ?>
								</a>

							</div>
						</div>
						<!--  <hr class="mainhr1" />-->

					</div>
				</div>




				<!-- new section sidebar-->

				<div class="row newsectionsidebara" style="background:#F5F5F5">
					<div class="col-md-12">
						<p>
							<?php
							// check pdf coupon product 	
							$woocommerce_prd = get_post_meta($post->ID, '_wooproduct_id', true);
							if (!empty($woocommerce_prd)) {
								if (!str_contains($woocommerce_prd, ',')) {
									$updatedproductid = apply_filters('wpml_object_id', $woocommerce_prd, 'product', TRUE);
									$pdf_vouch = get_post_meta($updatedproductid, '_wpdesk_pdf_coupons', true);
									if (isset($pdf_vouch) && $pdf_vouch == 'yes') {
										$product = wc_get_product($updatedproductid);
										$product_slug = $product->get_slug();
									}

								}
							}
							// check pdf coupon product
						

							$current_language = apply_filters('wpml_current_language', NULL);
							/*	if($current_language=='de')
																 {
																	 $pdf_url=get_site_url()."/produit/bon-dachat/";
																	 if(isset($product_slug) && $product_slug!="")
																	 {
																		 $siteurls=get_site_url()."/produkt/".$product_slug;
																	 }
																	 else
																	 {
																		 $siteurls=get_site_url()."/products/?listing_id=".$post->ID;
																	 }
																 }
																 else if($current_language=='en')
																 {
																	 $pdf_url=get_site_url()."/produit/bon-dachat/";
																	 if(isset($product_slug) && $product_slug!="")
																	 {
																		 $siteurls=get_site_url()."/product/".$product_slug;
																	 }
																	 else
																	 {
																		 $siteurls=get_site_url()."/products/?listing_id=".$post->ID;
																	 }
																	 
																 }*/
							if ($current_language == 'fr') {
								$pdf_url = get_site_url() . "/produit/bon-dachat/";
								if (isset($product_slug) && $product_slug != "") {
									$siteurls = get_site_url() . "/produit/" . $product_slug;
								} else {
									$siteurls = get_site_url() . "/produits/?listing_id=" . $post->ID;
								}

							}



							?>

						</p>



						<h3 class="heading33">
						Offre une nuit magique dans une tente bulles.

						</h3>


						<div class="description-long">
							<?php
							if (isset($siteurls) && $siteurls != "") { ?>
								<p>
								Offre des moments inoubliables avec un bon cadeau pour cette bulle!
								</p>
								<ul>
									<li>
										Dates flexibles

									</li>
									<li>

										Jusqu'à 3 ans de validité
									</li>
								</ul>
								<div class="col-md-12 textalignrigh firstbuttonrightsec"
									style="padding-top:5px; padding-bottom:15px;">

									<a href="<?php echo $siteurls; ?>" class="sidbuttonhavestyle"><button type="button"
											class="btn btn-primary buttonpop">

											Acheter un bon exclusif bulle</button><img
											src="<?php echo get_stylesheet_directory_uri(); ?>/listeo-core/images/gift.svg" /></a>
								</div>
								<?php
							}

							?>
							<p>
							Tu peux  aussi choisir un bon cadeau. Tu peux  l'utiliser plus tard pour n'importe laquelle de nos tentes bulles.</p>

							<div class="col-md-12 textalignrigh" style="padding-top:15px;">

								<a href="<?php echo $pdf_url; ?>" class="sidbuttonhavestyle1"><button type="button"
										class="btn btn-primary buttonpop">

										Acheter un bon Réserve ta Bulle</button><img
										src="<?php echo get_stylesheet_directory_uri(); ?>/listeo-core/images/gift.svg" /></a>
							</div>


						</div>


					</div>
				</div>

				<!-- <hr class="mainhr1" />-->

				<!--  new section of sidebar-->
				<h3 class="heading33">

					<?php if (get_field('features_heading')) { ?>
						<?php echo get_field('features_heading'); ?>
					<?php } ?>

				</h3>



				<div class="row topiconcenter middlesections">

					<?php
					$listing_features = get_field('listing_features');
					if ($listing_features) {
						$i = 1;
						foreach ($listing_features as $listfeature) {

							?>
							<div class="col-md-4 col-sm-4 col-xs-6  <?php //if($i==2 || $i==4 || $i==6){ echo "centericonss"; }  ?> <?php if ($i > 4) {
								  echo "desktopviewww";
							  } ?>">
								<?php if (has_post_thumbnail($listfeature->ID)) { ?>
									<?php
									$image1 = wp_get_attachment_url(get_post_thumbnail_id($listfeature->ID, 'full'));
									?>
									<img src="<?php echo $image1; ?>" />
								<?php } ?>
								<span class="icontext">
									<?php echo $listfeature->post_title;
									?>
								</span>
							</div>

							<?php

							$i++;
						}
					}
					?>




				</div>
				<div class="row">
					<div class="col-md-12 textalignrigh" style="padding-top:15px;">

						<button type="button" class="btn btn-primary buttonpop" data-toggle="modal" data-target="#myModal3">
							<?php esc_html_e('Alle anzeigen', 'listeo_core'); ?>
						</button>
					</div>
				</div>




				<?php $template_loader->get_template_part('single-partials/single-listing', 'google-reviews'); ?>
				<?php if ($load_reviews && !get_option('listeo_disable_reviews')) {

					$template_loader->get_template_part('single-partials/single-listing', 'reviews');
				} ?>
				<?php do_action('listeo/single-listing/end-content'); ?>





				<hr class="mainhr1" />

				<div class="row">
					<div class="col-md-12">
						<h3 class="heading33">


							<?php if (get_field('activities_heading')) { ?>
								<?php echo get_field('activities_heading'); ?>
							<?php } ?>

						</h3>
					</div>
				</div>
				<div class=" icontops topiconcenter">

					<?php
					$activities_icons = get_field('activities_icons');
					if ($activities_icons) {
						$i = 1;
						foreach ($activities_icons as $activefeature) {

							?>

							<div class="  <?php //if($i==2 || $i==4 || $i==6){ echo "centericonss"; }  ?>  <?php if ($i > 2) {
								   echo "desktopviewww";
							   } ?> <?php if ($i == 2) {
									 echo "centericonss";
								 } ?><?php if ($i == 1) {
									  echo "leftalignnn";
								  } ?><?php if ($i == 3) {
									   echo "rightalignnn";
								   } ?>">
								<?php if (has_post_thumbnail($activefeature->ID)) { ?>
									<?php
									$image1 = wp_get_attachment_url(get_post_thumbnail_id($activefeature->ID, 'full'));
									?>
									<img src="<?php echo $image1; ?>" />
								<?php } ?>
								<span class="icontext">
									<?php echo $activefeature->post_title;
									?>
								</span>
							</div>

							<?php
							$i++;
						}
					}
					?>


				</div>

				<div class="row">
					<div class="col-md-12">


						<div class="description-long descripmiddle">

							<?php if (get_field('activity_description')) { ?>
								<p class="middleshorttext">
									<?php
									$shortertext1 = strip_tags(get_field('activity_description'));

									echo substr($shortertext1, 0, 300) . " ......"; ?>
								</p>
								<div class="middlelongtext" style="display:none">
									<?php echo get_field('activity_description'); ?>
								</div>
							<?php } ?>

							<div class="linkright"> <a href="javascript:void(0)" class="readmore readmoremiddle">
									<?php esc_html_e('mehr erfahren', 'listeo_core'); ?>
								</a>
								<a href="javascript:void(0)" class=" readmore readless readlessmiddle">
									<?php esc_html_e('Text einklappen', 'listeo_core'); ?>
								</a>
							</div>


						</div>
					</div>

				</div>
				<hr class="mainhr1">

          

          <div class="row">
            <div class="col-md-4">
              <div class="why-item">
                <img src="https://reserve-ta-bulle.fr/wp-content/uploads/2024/12/experience.svg">
                <h4><?php esc_html_e('Expérience', 'listing_point'); ?></h4>
                <ul>
                  <li><?php esc_html_e('Confortable et moderne.', 'listing_point'); ?></li>
                  <li><?php esc_html_e('Privacité maximale.', 'listing_point'); ?></li>
                  <li><?php esc_html_e('Cadres spacieux.', 'listing_point'); ?></li>
                  <li><?php esc_html_e('Situé dans des paysages tranquilles.', 'listing_point'); ?></li>
                </ul>
              </div>
            </div>
                  <div class="col-md-4">
              <div class="why-item">
                <img src="https://reserve-ta-bulle.fr/wp-content/uploads/2024/12/sustain.svg">
                <h4><?php esc_html_e('Durabilité', 'listing_point'); ?></h4>
                <ul>
                  <li><?php esc_html_e('Tentes neutres en CO2 avec systèmes photovoltaïques.', 'listing_point'); ?></li>
                  <li><?php esc_html_e('100% recyclable, réintroduit dans le cycle de la matière.', 'listing_point'); ?></li>
                </ul>
              </div>
            </div>
                  <div class="col-md-4">
              <div class="why-item">
                <img src="https://reserve-ta-bulle.fr/wp-content/uploads/2024/12/safety.svg">
                <h4><?php esc_html_e('Sécurité et qualité', 'listing_point'); ?></h4>
                <ul>
                  <li><?php esc_html_e('Matériaux TPU exempts de produits chimiques nocifs.', 'listing_point'); ?></li>
                  <li><?php esc_html_e('Certifié REACH, testé par DEKRA.', 'listing_point'); ?></li>
                </ul>
              </div>
            </div>
          </div>

				<!-- <div class="row">
					<div class="col-md-12">
						<h3 class="heading33">


							<?php //if (get_field('why_title')) { ?>
								<?php //echo get_field('why_title'); ?>
							<?php //} ?>

						</h3>
					</div>
				</div> -->

				<!-- <div class="row">
					<div class="col-md-12">
						<div class="description-long">

							<?php if (get_field('why_description')) { ?>
								<p class="whyshorttext">
									<?php
									$shortertext2 = strip_tags(get_field('why_description'));

									echo substr($shortertext2, 0, 300) . " ......"; ?>
								</p>
								<div class="whylongtext" style="display:none">
									<?php echo get_field('why_description'); ?>
								</div>
							<?php } ?>

							<div class="linkright">

								<a href="javascript:void(0)" class="readmore readmorewhy">
									<?php esc_html_e('mehr erfahren', 'listeo_core'); ?>
								</a>
								<a href="javascript:void(0)" class=" readmore readless readlesswhy">
									<?php esc_html_e('Text einklappen', 'listeo_core'); ?>
								</a>


							</div>
						</div>
					</div>

				</div> -->
				<hr class="mainhr1">


				<div class="row">
					<div class="col-md-12">
						<h3 class="heading33">

							<?php esc_html_e('Was du noch wissen solltest', 'listeo_core'); ?>
						</h3>
					</div>
				</div>


				<div class="row middlesection">
					<div class="col-md-4 col-sm-4 col-xs-12 mobileviewcenter">
						<h4 class="desktopviewww">
							<?php if (get_field('arrival_and_departure_heading_text')) { ?>
								<?php echo get_field('arrival_and_departure_heading_text'); ?>
							<?php } ?>
						</h4>
						<?php if (get_field('arrival_and_departure')) {
							$arivalsarray = get_field('arrival_and_departure');
							?>
							<ul class="middlelist desktopviewww">
								<?php
								foreach ($arivalsarray as $arrival) {
									if ($arrival['arrival_and_departure_list_items'] != "") {
										?>
										<li> <?php echo $arrival['arrival_and_departure_list_items']; ?> </li>

										<?php
									}
								}
								?>

							</ul>



							<button type="button" class="readmorebtn desktopviewww" data-toggle="modal" data-target="#myModal6">


								<?php esc_html_e('mehr erfahren', 'listeo_core'); ?>
							</button>

							<button type="button" class="btn btn-primary  buttonpop mobilepop" data-toggle="modal"
								data-target="#myModal6">
								<?php if (get_field('arrival_and_departure_heading_text')) { ?>
									<?php echo get_field('arrival_and_departure_heading_text'); ?>
								<?php } ?></button>


							<?php
						}
						?>
					</div>
					<div class="col-md-4  col-sm-4 col-xs-12 centercolumc mobileviewcenter">
						<h4 class="desktopviewww">
							<?php if (get_field('booking_&_cancellation_main_heading')) { ?>
								<?php echo get_field('booking_&_cancellation_main_heading'); ?>
							<?php } ?>
						</h4>
						<?php if (get_field('_booking_&_cancellation')) {
							$bookingarray = get_field('_booking_&_cancellation');
							?>
							<ul class="middlelist desktopviewww">

								<?php
								foreach ($bookingarray as $booking) {
									?>
									<li> <?php echo $booking['_booking_&_cancellation_list_item']; ?> </li>

									<?php
								}
								?>


							</ul>
							<button type="button" class="readmorebtn desktopviewww" data-toggle="modal" data-target="#myModal5">
								<?php esc_html_e('mehr erfahren', 'listeo_core'); ?></button>

							<button type="button" class="btn btn-primary mobilepop buttonpop" data-toggle="modal"
								data-target="#myModal5">
								<?php if (get_field('booking_&_cancellation_main_heading')) { ?>
									<?php echo get_field('booking_&_cancellation_main_heading'); ?>
								<?php } ?>

							</button>
							<?php
						}
						?>
					</div>
					<div class="col-md-4  col-sm-4 col-xs-12 mobileviewcenter">
						<h4 class="desktopviewww">
							<?php if (get_field('house_rules_&_information_main_heading')) { ?>
								<?php echo get_field('house_rules_&_information_main_heading'); ?>
							<?php } ?>

						</h4>
						<?php if (get_field('house_rules_&_information_list')) {
							$rulesarray = get_field('house_rules_&_information_list');
							?>
							<ul class="middlelist desktopviewww">

								<?php
								foreach ($rulesarray as $rules) {
									?>
									<li> <?php echo $rules['house_rules_&_information_list_item']; ?> </li>

									<?php
								}
								?>

							</ul>
							<button type="button" class="readmorebtn desktopviewww" data-toggle="modal" data-target="#myModal4">
								<?php esc_html_e('mehr erfahren', 'listeo_core'); ?></button>

							<button type="button" class="btn btn-primary mobilepop buttonpop" data-toggle="modal"
								data-target="#myModal4">
								<?php if (get_field('house_rules_&_information_main_heading')) { ?>
									<?php echo get_field('house_rules_&_information_main_heading'); ?>
								<?php } ?>

							</button>
							<?php
						}
						?>
					</div>
				</div>

				<hr class="mainhr1">


				<div class="row">
					<div class="col-md-12">
						<h3 class="heading33">

							<?php the_title(); ?> 	<?php esc_html_e(' FAQs ', 'listeo_core'); ?>
						</h3>
					</div>
				</div>

				<!--  <div class="row">-->



				<div class=" mainfaq row">

					<?php if (get_field('location_faqs')) {
						$i = 1;
						$location_faqs = get_field('location_faqs');

						$totalcount = count($location_faqs);
						?>


						<?php

						//echo "<pre>";
				
						//print_r($location_faqs);
						foreach ($location_faqs as $faq) {
							//if($i==1 || $i==4)
							//{
							?>
							<div class="col-md-6 col-xs-12  mobilefaq">
								<?php
								//}
								?>


								<div class="faq mainfaqcard">
									<div class=" mainfaqheader">
										<button class="btn btn-link">
											<i class="fa fa-plus"></i><span><?php echo $faq["faq_title"]; ?></span>
										</button>
									</div>
									<div class="description" style="display:none">
										<div class="mainfaqbody">
											<?php echo $faq["faq_description"]; ?>
										</div>
									</div>
								</div>

								<?php
								///if($i==3 || $i==6 || $i==$totalcount)
								//{ ?>
							</div>
							<?php
							//}
				
							$i++;
						}
					}
					?>




				</div>




				<!-- </div>-->


				<div class="row">
					<div class="col-md-12 faqmainbtn mobilefaqalign" style="text-align:right">
						<button type="button" class="btn btn-primary buttonpop " data-toggle="modal"
							data-target="#myModal8">

							<?php esc_html_e('Allgemeine FAQs', 'listeo_core'); ?>
						</button>
					</div>
				</div>

				<?php /*?><div class="row">
									 <div class="col-md-12">
										 <?php $template_loader->get_template_part('single-partials/single-listing', 'location');  ?>
									 </div>
									 </div><?php */ ?>

			</div>


			<?php
			//sidebar that is used
			if ($layout == "right-sidebar" && $mobile_layout != "left-sidebar"): ?>
				<div class="col-lg-4 newside col-md-4 sidebarclick  listeo-single-listing-sidebar margin-top-75 sticky ts-dsk-sidebar"
					id="custom-booking">
					<div class="nwesidedetail" style="margin-top:20px;">
						<div class="pricelisting">
							<?php $template_loader->get_template_part('single-partials/single-listing', 'pricing'); ?>
						</div>

						<?php do_action('listeo/single-listing/sidebar-start'); ?>
						<?php
						$classifieds_price = get_post_meta($post->ID, '_classifieds_price', true);

						if ($listing_type == 'classifieds' && !empty($classifieds_price)) {

							$currency_abbr = get_option('listeo_currency');
							$currency_postion = get_option('listeo_currency_postion');
							$currency_symbol = Listeo_Core_Listing::get_currency_symbol($currency_abbr);

							?>
							<span id="classifieds_price">

								<?php echo get_the_listing_price_range(); ?>
								<?php


								if ($currency_postion == "before") {
									echo $currency_symbol;
								}
								$decimals = get_option('listeo_number_decimals', 2);
								echo number_format_i18n(get_post_meta($post->ID, '_classifieds_price', true), $decimals);
								if ($currency_postion == "after") {
									echo $currency_symbol;
								} ?>
							</span>
						<?php } ?>
						<?php if ($listing_type != 'classifieds') { ?>

							<?php if (get_post_meta($post->ID, '_verified', true) == 'on'): ?>
								<!-- Verified Badge -->
								<div class="verified-badge with-tip"
									data-tip-content="<?php esc_html_e('Listing has been verified and belongs to the business owner or manager.', 'listeo_core'); ?>">
									<i class="sl sl-icon-check"></i> <?php esc_html_e('Verified Listing', 'listeo_core') ?>
								</div>
							<?php else:
								if (get_option('listeo_claim_page_button')) {
									$claim_page = get_option('listeo_claim_page'); ?>
									<div class="claim-badge with-tip"
										data-tip-content="<?php esc_html_e('Click to claim this listing.', 'listeo_core'); ?>">
										<?php
										$link = add_query_arg('subject', get_permalink(), get_permalink($claim_page)); ?>

										<a href="<?php echo $link; ?>"><i class="sl sl-icon-question"></i>
											<?php esc_html_e('Not verified. Claim this listing!', 'listeo_core') ?></a>
									</div>
								<?php }

							endif; ?>
						<?php } ?>
						<?php get_sidebar('listing'); ?>
						<?php do_action('listeo/single-listing/sidebar-end'); ?>

						<?php
						global $post;
						$woo_product_id = get_post_meta($post->ID, 'product_id', true);
						$listing_product = wc_get_product($woo_product_id);
						if (!empty($listing_product)) {
							$property_id = wc_get_product($woo_product_id)->get_meta('custom_property_id_field');
							$base_price = get_post_meta($woo_product_id, 'sa_cfw_cog_amount', true);
						} else {
							$property_id = 0;
							$base_price = 0;
						}

						// Get the current language code using WPML (if available):
						if (function_exists('icl_get_language_code')) {
							$current_language = icl_get_language_code();
						} else {
							// Fallback if WPML is not active:
							$current_language = ''; // Or use a default language code if applicable
						}

						// Construct the base URL considering language:
						$base_url = get_home_url(null); // No language code for German
				
						// Conditional logic for appending language code:
						if ($current_language !== 'de') { // de is the standard code for German
							$base_url .= '/' . $current_language;
						}

						// Disable booking for some locations
						//$disableLocIDs = [149074, 149392, 149393, 149391, 149070, 149390, 149076, 149443, 149442, 149078, 149448, 149447, 145731, 146191, 146190];	
						$disableLocIDs = [];

						$without_checkout = get_field('listing_without_checkout');

						?>
						<div class="sidebarnewmodeule">
							<?php if ( !in_array($post->ID, $disableLocIDs) ) :  ?>
							<?php if ($property_id): ?>
								<?php
								// Build the Smoobu calendar shortcode with dynamic base URL:
								$checkout_page_id = get_option('woocommerce_checkout_page_id');
								$shortcode = do_shortcode("[smoobu_calendar property_id='$property_id' layout='1x3' link='" . $base_url . "?buy-now=$woo_product_id&qty=1&coupon=&ship-via=free_shipping&page=$checkout_page_id&with-cart=0&prices=$base_price']");
								// Display the shortcode:
								echo $shortcode;
								?>
							<?php else: ?>
								<p>
									<a href="javascript:void(0)" data-toggle="modal" data-target="#myModa22"
										class="button book-now fullwidth margin-top-5  hash-custom-book-id">
										<span class="book-now-text"> <?php esc_html_e('Jetzt buchen', 'listeo_core') ?></span>
									</a>
								</p>
								<!-- <button type="button" class="btn btn-primary buttonpop " data-toggle="modal" data-target="#myModal8">-->
							<?php endif; ?>
							<?php else : ?>	
							<?php
							$currency_symbol = '';
							if ( function_exists( 'get_woocommerce_currency_symbol' ) ) {
								$currency_symbol = get_woocommerce_currency_symbol();
							}
							$formatted_price = sprintf( __( 'From %1$s%2$s / Night', 'smoobu-calendar' ), $base_price, $currency_symbol );
							?>
							<div class="smoobu-price-display-container"><?php echo $formatted_price; ?></div>
							<h4 class="text-center">
								<?php
								$msg_text = __( 'Pour les réservations, veuillez nous contacter à l\'adresse suivante : <a href="mailto:contact@reserve-ta-bulle.fr">contact@reserve-ta-bulle.fr</a>', 'listeo_core' );
								echo wp_kses_post( $msg_text );
								?>
							</h4>
							<style>.container .nwesidedetail .pricelisting {display: none;}</style>
							<?php endif; ?>

						</div>
					</div>
					<!-- Sidebar / End -->
				<?php endif; ?>
			</div>
			<!-- Ts Dev Code End -->
		</div>
	</div>
	</div>
	<div class="row mapdivc">
		<div class="col-md-12">
			<?php $template_loader->get_template_part('single-partials/single-listing', 'location'); ?>
		</div>
	</div>
	</div>



	<!--   POPup list-->




	<div class="modal fade modelpopdesgn scrollbarnew" id="myModal3" tabindex="-1" role="dialog"
		aria-labelledby="myModalLabel">
		<div class="modal-dialog modal-dialog-centered " role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">
						<?php if (get_field('features_heading')) { ?>
							<?php echo get_field('features_heading'); ?>
						<?php } ?>

					</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">


					<?php
					$listing_features_popup = get_field('listing_features_popup');
					if ($listing_features_popup) {
						//echo "<pre>";
						//print_r($listing_features_popup); 
				
						foreach ($listing_features_popup as $listing_features) {

							?>


							<h5 class="headingfeaturespop" style="margin-left:15px;">
								<?php echo $listing_features['listing_features_popup_heading']; ?>
							</h5>
							<ul class="popuiconsss">


								<?php if ($listing_features['listing_features_popup_icons']) {

									foreach ($listing_features['listing_features_popup_icons'] as $popup_icons) {

										?>
										<li>
											<?php if (has_post_thumbnail($popup_icons->ID)) { ?>
												<?php
												$image1 = wp_get_attachment_url(get_post_thumbnail_id($popup_icons->ID, 'full'));
												?>
												<img src="<?php echo $image1; ?>" />

												<?php
											}
											?>
											<span class="icontext">
												<?php
												echo $popup_icons->post_title;
												?>
											</span>
										</li>
										<?php
									}
								}
								?>
							</ul>
							<hr />

							<?php

							//$i++;
						}
					}
					?>

				</div>

			</div>
		</div>
	</div>




	<!--     popup list-->


	<!-- three popups-->

	<div class="modal fade modelpopdesgn imgiconpop " id="myModal4" tabindex="-1" role="dialog"
		aria-labelledby="myModalLabel">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">
						<?php if (get_field('house_rules_&_information_main_heading')) { ?>
							<?php echo get_field('house_rules_&_information_main_heading'); ?>
						<?php } ?>

					</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">




					<?php if (get_field('house_rules_&_information_popup_icons')) {
						$iconsarray = get_field('house_rules_&_information_popup_icons');
						?>
						<ul class="iconspoplist">

							<?php
							foreach ($iconsarray as $icons) {
								?>

								<li>
									<?php if (has_post_thumbnail($icons->ID)) { ?>
										<?php
										$image = wp_get_attachment_url(get_post_thumbnail_id($icons->ID, 'full'));
										?>
										<img class="iconpopssss" src="<?php echo $image; ?>" />
									<?php } ?>
									<span class="icontext">
										<?php echo $icons->post_title;
										?>
									</span>
								</li>


								<?php
							}
					}
					?>
					</ul>




				</div>

			</div>
		</div>
	</div>








	<div class="modal fade modelpopdesgn textalignother  " id="myModal5" tabindex="-1" role="dialog"
		aria-labelledby="myModalLabel">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">

						<?php if (get_field('booking_&_cancellation_main_heading')) { ?>
							<?php echo get_field('booking_&_cancellation_main_heading'); ?>
						<?php } ?>
					</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">


					<?php if (get_field('_booking_&_cancellation')) {
						$bookingarray = get_field('_booking_&_cancellation');
						?>


						<?php
						foreach ($bookingarray as $booking) {
							?>


							<h5><?php echo $booking['_booking_&_cancellation_popup_heading']; ?></h5>

							<p class="textpopupparagraph"><?php echo $booking['_booking_&_cancellation_popup_description']; ?></p>


							<hr />

							<?php
						}
					}
					?>

				</div>

			</div>
		</div>
	</div>






	<div class="modal fade modelpopdesgn textalignother " id="myModal6" tabindex="-1" role="dialog"
		aria-labelledby="myModalLabel">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">
						<?php if (get_field('arrival_and_departure_heading_text')) { ?>
							<?php echo get_field('arrival_and_departure_heading_text'); ?>
						<?php } ?>

					</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">

					<?php if (get_field('arrival_and_departure')) {
						$arivalsarray = get_field('arrival_and_departure');
						?>

						<?php
						foreach ($arivalsarray as $arrival) {
							?>


							<h5><?php echo $arrival['arrival_and_departure_popup_heading']; ?></h5>
							<p class="textpopupparagraph">
								<?php echo $arrival['arrival_and_departure_popup_description']; ?>
							</p>

							<hr />

							<?php
						}
					}
					?>

				</div>

			</div>
		</div>
	</div>




	<!-- three popups-->



	<!--   FAQ popup-->

	<div class="modal fade modelpopdesgn " id="myModal8" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
		<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" style="width:100%; text-align:center">
						<?php the_title(); ?> 	<?php esc_html_e(' FAQs', 'listeo_core'); ?>
					</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">

					<div class="row">
						<div class="col-md-12">

							<div class="input-group">
								<div class="form-outline">
									<input type="search" id="searchInput" class="form-control"
										placeholder="<?php esc_html_e(' Suche', 'listeo_core'); ?>..."
										style="font-size:17px;" />
								</div>
								<button type="button" class="btn seacjbtn btn-primary">
									<i class="fas fa-search"></i>
								</button>
							</div>
						</div>

					</div>
					<div class="row">
						<div class="col-md-12">
							<h6 class="faqheadingn">

								<?php esc_html_e('Allgemeine FAQs', 'listeo_core'); ?>
							</h6>
						</div>
					</div>

					<div class=" row mainfaq popupsearch">
						<?php if (get_field('general_faq')) {
							$i = 1;
							$general_faq = get_field('general_faq');

							$totalcount = count($general_faq);
							$totalcountfirst = ceil($totalcount / 2);
							$totalcountsecond = $totalcount - $totalcountfirst;

							?>


							<?php

							//echo "<pre>";
					
							//print_r($location_faqs);
							foreach ($general_faq as $faq) {
								if ($i == 1 || $i == $totalcountfirst + 1) {
									?>
									<div class="col-md-6 col-sm-12 col-xs-12">
										<?php
								}
								?>










									<div class="faq mainfaqcard popupfaqss">
										<div class=" mainfaqheader">
											<button class="btn btn-link">
												<i class="fa fa-plus"></i><?php echo $faq->post_title; ?>
											</button>
										</div>
										<div class="description" style="display:none">
											<div class="mainfaqbody">
												<?php echo $faq->post_content; ?>
											</div>
										</div>
									</div>



									<?php
									if ($i == $totalcountfirst || $i == $totalcount) { ?>
									</div>
									<?php
									}

									$i++;
							}
						}
						?>











					</div>






				</div>

			</div>
		</div>
	</div>

	<!--   FAQ popup-->


	<!-- booking popup-->

	<div class="modal fade modelpopdesgn " id="myModa22" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
		<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" style="width:100%; text-align:center">
						<?php esc_html_e('Booking', 'listeo_core'); ?>
					</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">


					<div class="row">
						<div class="col-md-12">

							<?php if (get_field('smoobu_iframe')) { ?>
								<?php echo get_field('smoobu_iframe'); ?>
							<?php } ?>

						</div>
					</div>








				</div>

			</div>
		</div>
	</div>

	<!--  booking popup-->






	<div id="titlebar" class="listing-titlebar" style="display:none">


		<?php
		if ($listing_logo) { ?>
			<div class="listing-logo"> <img src="<?php echo $listing_logo; ?>" alt=""></div>
		<?php } ?>
		<div class="listing-titlebar-title">
			<div class="listing-titlebar-tags">
				<?php
				$terms = get_the_terms(get_the_ID(), 'listing_category');
				if ($terms && !is_wp_error($terms)):
					$categories = array();
					foreach ($terms as $term) {

						$categories[] = sprintf(
							'<a href="%1$s">%2$s</a>',
							esc_url(get_term_link($term->slug, 'listing_category')),
							esc_html($term->name)
						);
					}

					$categories_list = join(", ", $categories);
					?>
					<span class="listing-tag">
						<?php echo ($categories_list) ?>
					</span>
				<?php endif; ?>
				<?php
				switch ($listing_type) {
					case 'service':
						$type_terms = get_the_terms(get_the_ID(), 'service_category');
						$taxonomy_name = 'service_category';
						break;
					case 'rental':
						$type_terms = get_the_terms(get_the_ID(), 'rental_category');
						$taxonomy_name = 'rental_category';
						break;
					case 'event':
						$type_terms = get_the_terms(get_the_ID(), 'event_category');
						$taxonomy_name = 'event_category';
						break;
					case 'classifieds':
						$type_terms = get_the_terms(get_the_ID(), 'classifieds_category');
						$taxonomy_name = 'classifieds_category';
						break;
					case 'region':
						$type_terms = get_the_terms(get_the_ID(), 'region');
						$taxonomy_name = 'region';
						break;

					default:
						# code...
						break;
				}
				if (isset($type_terms)) {
					if ($type_terms && !is_wp_error($type_terms)):
						$categories = array();
						foreach ($type_terms as $term) {
							$categories[] = sprintf(
								'<a href="%1$s">%2$s</a>',
								esc_url(get_term_link($term->slug, $taxonomy_name)),
								esc_html($term->name)
							);
						}

						$categories_list = join(", ", $categories);
						?>
						<span class="listing-tag">
							<?php echo ($categories_list) ?>
						</span>
					<?php endif;
				}
				?>
				<?php if (get_the_listing_price_range()): ?>
					<span class="listing-pricing-tag"><i
							class="fa fa-<?php echo esc_attr(get_option('listeo_price_filter_icon', 'tag')); ?>"></i><?php echo get_the_listing_price_range(); ?></span>
				<?php endif;

				do_action('listeo/single-listing/tags');

				?>

			</div>




			<?php if (get_the_listing_address()): ?>
				<span>
					<a href="#listing-location" class="listing-address">
						<i class="fa fa-map-marker"></i>
						<?php the_listing_address(); ?>
					</a>
				</span> <br>
			<?php endif;


			if (!get_option('listeo_disable_reviews')) {
				$rating = get_post_meta($post->ID, 'listeo-avg-rating', true);
				if (!$rating && get_option('listeo_google_reviews_instead')) {
					$reviews = listeo_get_google_reviews($post);
					if (!empty($reviews['result']['reviews'])) {
						$rating = number_format_i18n($reviews['result']['rating'], 1);
						$rating = str_replace(',', '.', $rating);
					}
				}
				if (isset($rating) && $rating > 0):
					$rating_type = get_option('listeo_rating_type', 'star');
					if ($rating_type == 'numerical') { ?>
						<div class="numerical-rating" data-rating="<?php $rating_value = esc_attr(round($rating, 1));
						printf("%0.1f", $rating_value); ?>">
						<?php } else { ?>
							<div class="star-rating" data-rating="<?php echo $rating; ?>">
							<?php } ?>
							<?php $number = listeo_get_reviews_number($post->ID);
							if (!get_post_meta($post->ID, 'listeo-avg-rating', true) && get_option('listeo_google_reviews_instead')) {
								$number = $reviews['result']['user_ratings_total'];
							} ?>

							<div class="rating-counter"><a href="#listing-reviews"><strong><?php esc_attr(round($rating, 1));
							printf("%0.1f", $rating); ?></strong>
									(<?php printf(_n('%s review', '%s reviews', $number, 'listeo_core'), number_format_i18n($number)); ?>)</a>
							</div>
						</div>
					<?php endif;
			} ?>

			</div>

		</div>
		<?php
		if ($listing_type == 'classifieds') {
			$load_reviews = false;
		}
		?>

		<!-- Content
			================================================== -->
		<?php
		if ($gallery_style == 'none' && $load_gallery == true):
			$gallery = get_post_meta($post->ID, '_gallery', true);
			if (!empty($gallery)):

				foreach ((array) $gallery as $attachment_id => $attachment_url) {
					$image = wp_get_attachment_image_src($attachment_id, 'listeo-gallery');
					echo '<img src="' . esc_url($image[0]) . '" class="single-gallery margin-bottom-40" style="margin-top:-30px;"></a>';
				}

			endif;
		endif; ?>

		<!-- Listing Nav -->
		<?php /*?><div id="listing-nav" class="listing-nav-container">
								<ul class="listing-nav">
									<?php do_action('listeo/single-listing/navigation-start'); ?>
									<li><a href="#listing-overview" class="active"><?php esc_html_e('Overview', 'listeo_core'); ?></a></li>
									<?php if ($count_gallery > 0 && $gallery_style == 'content'  && $load_gallery == true) : ?><li><a href="#listing-gallery"><?php esc_html_e('Gallery', 'listeo_core'); ?></a></li>
										<?php endif;
									$_menu = get_post_meta(get_the_ID(), '_menu_status', 1);

									if (!empty($_menu)) {
										$_bookable_show_menu =  get_post_meta(get_the_ID(), '_hide_pricing_if_bookable', true);
										if (!$_bookable_show_menu) { ?>
											<li><a href="#listing-pricing-list"><?php esc_html_e('Pricing', 'listeo_core'); ?></a></li>
										<?php } ?>

									<?php } ?>
									<?php if (class_exists('WeDevs_Dokan') && get_post_meta(get_the_ID(), '_store_section_status', 1)) : ?><li><a href="#listing-store"><?php esc_html_e('Store', 'listeo_core'); ?></a></li><?php endif; ?>
									<?php $video = get_post_meta($post->ID, '_video', true);
									if ($load_video && !empty($video)) :  ?>
										<li><a href="#listing-video"><?php esc_html_e('Video', 'listeo_core'); ?></a></li>
									<?php endif;
									$latitude = get_post_meta($post->ID, '_geolocation_lat', true);
									if (!empty($latitude)) :  ?>
										<li><a href="#listing-location"><?php esc_html_e('Location', 'listeo_core'); ?></a></li>
										<?php
									endif;
									if ($listing_type != 'classifieds') {
										if ($load_reviews && !get_option('listeo_disable_reviews')) {
											$reviews = get_comments(array(
												'post_id' => $post->ID,
												'status' => 'approve' //Change this to the type of comments to be displayed
											));
											if ($reviews) : ?>
												<li><a href="#listing-reviews"><?php esc_html_e('Reviews', 'listeo_core'); ?></a></li>
											<?php endif; ?>
											<?php
											$usercomment = false;
											if (is_user_logged_in()) {
												$usercomment = get_comments(array(
													'user_id' => get_current_user_id(),
													'post_id' => $post->ID,
												));
											}
											//TODO if open comments
											if (!$usercomment) { ?>
												<li><a href="#add-review"><?php esc_html_e('Add Review', 'listeo_core'); ?></a></li>
											<?php } ?>
									<?php }
									}
									do_action('listeo/single-listing/navigation-end');
									?>


								</ul>
							</div><?php */ ?>
		<?php


		// 		$d = DateTime::createFromFormat('d-m-Y', $expires);
		// 		echo $d->getTimestamp(); 
		?>
		<!-- Overview -->
		<?php /*?><div id="listing-overview" class="listing-section">
								<?php $template_loader->get_template_part('single-partials/single-listing', 'main-details');  ?>

								<!-- Description -->
								<?php do_action('listeo/single-listing/before-content'); ?>
								<?php the_content(); ?>
								<?php do_action('listeo/single-listing/after-content'); ?>
								<?php
								if (in_array('option_social_links', $packages_disabled_modules)) {
									if ($package && $package->has_listing_social_links() == 1) {
										$template_loader->get_template_part('single-partials/single-listing', 'socials');
									}
								} else {
									$template_loader->get_template_part('single-partials/single-listing', 'socials');
								}
								?>
								<?php $template_loader->get_template_part('single-partials/single-listing', 'features');  ?>
							</div><?php */ ?>

		<?php


		if ($count_gallery > 0 && $gallery_style == 'content' && $load_gallery == true):
			$template_loader->get_template_part('single-partials/single-listing', 'gallery-content');
		endif; ?>
		<?php //$template_loader->get_template_part('single-partials/single-listing', 'pricing');  ?>
		<?php if (class_exists('WeDevs_Dokan') && get_post_meta(get_the_ID(), '_store_section_status', 1)):
			$template_loader->get_template_part('single-partials/single-listing', 'store');
		endif; ?>
		<?php if ($load_video) {
			$template_loader->get_template_part('single-partials/single-listing', 'video');
		} ?>
		<?php //$template_loader->get_template_part('single-partials/single-listing', 'location');  ?>

		<?php
		if (in_array($listing_type, array('rental', 'service'))) {
			if (get_option('listeo_show_calendar_single')) {
				$template_loader->get_template_part('single-partials/single-listing', 'calendar');
			}
		} ?>
		<?php
		if (get_option('listeo_related_listings_status')) {
			$template_loader->get_template_part('single-partials/single-listing', 'related');
		}
		?>
		<?php //$template_loader->get_template_part('single-partials/single-listing', 'google-reviews'); ?>
		<?php if ($load_reviews && !get_option('listeo_disable_reviews')) {

			$template_loader->get_template_part('single-partials/single-listing', 'reviews');
		} ?>
		<?php do_action('listeo/single-listing/end-content'); ?>
	</div>
<?php endwhile; // End of the loop. 
		//old sidebar place
		?>





<?php /*?><?php else : ?>

<?php get_template_part('content', 'none'); ?>

<?php endif; ?><?php */ ?>




<?php get_footer(); ?>

<!-- single listing slider jquery code -->
<?php global $post;

if ($post->post_type == 'listing') {
	?>

	<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script> -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery-js/1.4.0/js/lightgallery.min.js"></script>

	<script>



		jQuery(document).ready(function () {
			//alert("sss");



			jQuery(".smoobu-calendar-button-container").click(function () {

				if (jQuery(".smoobu-calendar").val() == "") {

					const shadowHost = document.querySelector('.easepick-wrapper');
					const shadowRoot = shadowHost.shadowRoot;
					const myDiv = shadowRoot.querySelector('div');
					//z-index: 100; color: red; top: -231.333px; left: -184.778px;
					jQuery(myDiv).css({
						'z-index': '100',
						'top': '40px',
						'left': '-184.778px'
					});
					jQuery(myDiv).addClass('show');
				}

			});
		});
	</script>

	<script>

		function toggleClass(el, className) {
			var el = document.querySelectorAll(el);

			for (i = 0; i < el.length; i++) {

				if (el[i].classList) {
					el[i].classList.toggle(className);
				} else {
					var classes = el[i].className.split(' ');
					var existingIndex = -1;
					for (var j = classes.length; j--;) {
						if (classes[j] === className)
							existingIndex = j;
					}

					if (existingIndex >= 0)
						classes.splice(existingIndex, 1);
					else
						classes.push(className);

					el[i].className = classes.join(' ');
				}
			}
		}

	</script>

	<script>
		lightGallery(document.querySelector('.popup-gallery'));

		document.getElementById('submitAnchor').addEventListener('click', function(e) {
			e.preventDefault();
			document.querySelector('.dateForm').submit();
		});
	</script>

<?php } ?>

<!-- end of single listing slider jquery code -->