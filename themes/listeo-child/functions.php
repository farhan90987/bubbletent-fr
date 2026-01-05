<?php

function enqueue_parent_styles() {
	wp_enqueue_style( 'owl-slider', get_stylesheet_directory_uri().'/assets/css/owl.carousel.min.css' );
	wp_enqueue_style( 'owl-slider-default', get_stylesheet_directory_uri().'/assets/css/owl.theme.default.min.css' );
	wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' );

    wp_enqueue_script( 'owl-slider',  get_stylesheet_directory_uri().'/assets/js/owl.carousel.min.js', array('jquery'), '1.0', true );
    wp_enqueue_script( 'custom-scripts',  get_stylesheet_directory_uri().'/assets/js/script97.js',  array('jquery'), '1.0.1', true );
    wp_localize_script( 'custom-scripts', 'ajax_object', array( 'ajax_url' => admin_url('admin-ajax.php')));
    
}
add_action( 'wp_enqueue_scripts', 'enqueue_parent_styles' );

/* woocommerce product detail in popup*/

add_action('wp_ajax_nopriv_getproductdetails', 'wc_getproductdetails');
add_action('wp_ajax_getproductdetails', 'wc_getproductdetails');
function wc_getproductdetails(){
	$productId = $_POST['productId'];
	$product = wc_get_product($productId);
	$productname = $product->get_title();
	$productprice = $product->get_price_html();
	$product_description = get_post($productId)->post_content;
	$image = wp_get_attachment_image_src( get_post_thumbnail_id( $productId ), 'single-post-thumbnail' );
	$product_faq = get_field( "faq_field" , $productId );
	// echo "<pre>";print_R($$productprice);
	$prdct = [];
	$prdct['productname'] = $productname;
	$prdct['productprice'] = $productprice;
	$prdct['product_description'] = $product_description;
	$prdct['product_faq'] = $product_faq;
	$prdct['imageurl'] = $image[0];
	$prdct['imageurl'] = $image[0];
	
	
	echo json_encode($prdct);

	wp_die();

}
add_filter( 'woocommerce_get_image_size_thumbnail', function( $size ) {
	return array(
		'width'  => 'auto',
		'height' => 'auto',
		'crop'   => 1,
	);
} );

add_filter( 'dokan_ensure_vendor_coupon', '__return_false' );


function custom_ajax_listings(){
	ob_start();
	global $sitepress;

	if( $sitepress->get_current_language() == 'fr' ){
		$default_reg = 187;
	} else {
		$default_reg = 74;
	}

	

	$args = array(
        'post_type'         => 'listing',
        'posts_per_page'    => -1, 
        'post_status'       => 'publish',
        // 'orderby'           => 'publish_date',
        // 'order'             => 'DESC',
        'tax_query' => array( array(
            'taxonomy' => 'region',
            'field'    => 'id',
            'terms'    => $default_reg,
        ) ),
    );
    $the_query = new WP_Query($args);
    

    if($the_query->have_posts()){

	$locations = get_terms([
	    'taxonomy' => 'listing_location',
	    'hide_empty' => true,
	]);
	$regions = get_terms([
	    'taxonomy' => 'region',
	    'hide_empty' => true,
	]);
	if($regions){ $count = 1; ?>
		<div class="regions-filters">
			<?php foreach ($regions as $region){
				$image_id = get_term_meta( $region->term_id, '_cover', true ); ?>
				<a href="javascript:void(0)" class="<?php if( $region->term_id == 74 && $sitepress->get_current_language() != 'fr' ){echo 'active';} elseif( $region->term_id != 74 && $sitepress->get_current_language() == 'fr' ){echo 'active';} ?>" id="<?php echo $region->term_id; ?>">
					<?php if($region->term_id == 74){ ?>
						<h4 class="filter-title"><?php esc_html_e('Deutsche Standorte', 'listeo_core'); ?></h4>
					<?php } else { ?>
						<h4 class="filter-title"><?php esc_html_e('Französische Standorte', 'listeo_core'); ?></h4>
					<?php } ?>
						<?php echo wp_get_attachment_image( $image_id, array('30', '30'), "" ); ?>
				</a>
			<?php $count++; } ?>
		</div>

	<?php }
	if($locations){ ?>

		<?php if($sitepress->get_current_language() == 'fr'){ ?>
		
		<?php } ?>

		<div class="listings-filters">
			<a href="javascript:void(0)" class="active" id="all_loc">
				<div class="listings-filter">
					<img src="https://book-a-bubble.de/wp-content/uploads/2023/10/icons8-igloo-64.png">
					<h4 class="filter-title"><?php esc_html_e('Alle Standorte', 'listeo_core'); ?></h4>
				</div>
			</a>

			<?php foreach ($locations as $location){
				$image_object = get_field('icon', 'term_' . $location->term_id);
				$image_size = 'listeo_core-avatar';
				$thumb = $image_object['sizes'][$image_size]; ?>

				
				<a href="javascript:void(0)" class="" id="<?php echo $location->term_id; ?>" catName="typ<?php echo $location->term_id; ?>">
					<div class="listings-filter">
						<img src="<?php echo esc_url($thumb); ?>">
						<h4 class="filter-title"><?php echo $location->name; ?></h4>
					</div>
				</a>
			<?php } ?>
		</div>
	<?php } ?>
	
	<div class="listings-wrap" id="listings-wrap">

        <?php
        global $post;
        while($the_query->have_posts()){ $the_query->the_post(); ?>

            <?php $featured_img_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
            $lis_address = get_post_meta( get_the_ID(), '_friendly_address', true );

            $place_id = get_post_meta(get_the_ID(),'_place_id', true);
            if(!empty($place_id)){
            	$place_data = listeo_get_google_reviews($post);
            }

            $_menu = get_post_meta(get_the_ID(), '_menu', 1);

            if (isset($_menu[0]['menu_elements'][0]['name']) && !empty($_menu[0]['menu_elements'][0]['name'])) {

				foreach ($_menu as $menu) {

					if (isset($menu['menu_elements']) && !empty($menu['menu_elements'])){

						$list_price = '';
						if (isset($menu['menu_elements'][0]['price']) && !empty($menu['menu_elements'][0]['price'])) {

							$currency_abbr = get_option('listeo_currency');
							$currency_postion = get_option('listeo_currency_postion');
							$currency_symbol = Listeo_Core_Listing::get_currency_symbol($currency_abbr);

							if ($menu['menu_elements'][0]['price'] == 0) {
								$list_price = esc_html_e('Free', 'listeo_core');
							} else {
								if ($currency_postion == 'before') {
									$list_price .= $currency_symbol . ' ';
								}
								$price = $menu['menu_elements'][0]['price'];
								if (is_numeric($price)) {
									$decimals = get_option('listeo_number_decimals', 2);
									$list_price .= number_format_i18n($price, $decimals);
								} else {
									$list_price .= esc_html($price);
								}

								if ($currency_postion == 'after') {
									$list_price .= ' ' . $currency_symbol;
								}
							}
						} else {
							$list_price = esc_html_e('Free', 'listeo_core');
						}
					}
				}
            } ?>
            
            <div class="listings-item">
            	<a href="<?php the_permalink(); ?>">
            		<img src="<?php echo $featured_img_url; ?>">
            		<h2 class="listings-title"><?php the_title(); ?></h2>
            	</a>
            </div>

        <?php } wp_reset_postdata(); ?>

	</div>

<?php }
	return ob_get_clean();
}
add_shortcode('new_listings', 'custom_ajax_listings');



function listng_filters(){

    $tax_id = $_REQUEST['tax-id'];
    $reg_id = $_REQUEST['reg-id'];

    if( $tax_id == 'all_loc' ){
    	$args = array(
	        'post_type'         => 'listing',
	        'posts_per_page'    => -1, 
	        'post_status'       => 'publish',
	        'tax_query' => array( array(
	            'taxonomy' => 'region',
	            'field'    => 'id',
	            'terms'    => $reg_id,
	        ) ),
	    );
    } else {
    	$args = array(
	        'post_type'         => 'listing',
	        'posts_per_page'    => -1, 
	        'post_status'       => 'publish',
	        'tax_query' => array(
			    'relation' => 'AND',
			    array(
			        'taxonomy' => 'listing_location',
			        'field'    => 'id',
			        'terms'    => $tax_id,
			    ),
			    array(
			        'taxonomy' => 'region',
			        'field'    => 'id',
			        'terms'    => $reg_id,
			    ),
			),
	    );
    }

    $the_query = new WP_Query($args);

    if($the_query->have_posts()){

		global $post;
	    while($the_query->have_posts()){ $the_query->the_post(); ?>

	        <?php $featured_img_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
	        $lis_address = get_post_meta( get_the_ID(), '_friendly_address', true );

	        $place_id = get_post_meta(get_the_ID(),'_place_id', true);
	        if(!empty($place_id)){
	        	$place_data = listeo_get_google_reviews($post);
	        }



	        $_menu = get_post_meta(get_the_ID(), '_menu', 1);

	        if (isset($_menu[0]['menu_elements'][0]['name']) && !empty($_menu[0]['menu_elements'][0]['name'])) {

				foreach ($_menu as $menu) {

					if (isset($menu['menu_elements']) && !empty($menu['menu_elements'])){

						$list_price = '';
						if (isset($menu['menu_elements'][0]['price']) && !empty($menu['menu_elements'][0]['price'])) {

							$currency_abbr = get_option('listeo_currency');
							$currency_postion = get_option('listeo_currency_postion');
							$currency_symbol = Listeo_Core_Listing::get_currency_symbol($currency_abbr);

							if ($menu['menu_elements'][0]['price'] == 0) {
								$list_price = esc_html_e('Free', 'listeo_core');
							} else {
								if ($currency_postion == 'before') {
									$list_price .= $currency_symbol . ' ';
								}
								$price = $menu['menu_elements'][0]['price'];
								if (is_numeric($price)) {
									$decimals = get_option('listeo_number_decimals', 2);
									$list_price .= number_format_i18n($price, $decimals);
								} else {
									$list_price .= esc_html($price);
								}

								if ($currency_postion == 'after') {
									$list_price .= ' ' . $currency_symbol;
								}
							}
						} else {
							$list_price = esc_html_e('Free', 'listeo_core');
						}
					}
				}
	        } ?>
	        
	        <div class="listings-item">
	        	<a href="<?php the_permalink(); ?>">
	        		<img src="<?php echo $featured_img_url; ?>">
	        		<h2 class="listings-title"><?php the_title(); ?></h2>
	        	</a>
            	<!-- <div class="listings-rating-wrap">
            		<?php //if( isset($place_data['result']['rating']) ){ ?>
            			<span class="dashicons dashicons-star-filled"></span><strong><?php //echo number_format_i18n($place_data['result']['rating'],1); ?></strong> -->
            		<?php //} else {
            			//echo '<span class="dashicons dashicons-star-filled"></span> 0';
            		//} ?>
            		<!-- <span class="listing_price"><?php //echo $list_price; ?></span>
            	</div> -->
	        </div>

	    <?php } wp_reset_postdata();
    } else { ?>
	    <p><?php esc_html_e('Keine Ergebnisse gefunden..!', 'listeo_core'); ?></p>
    <?php }

    die();
}
add_action('wp_ajax_listng_filters', 'listng_filters');
add_action('wp_ajax_nopriv_listng_filters', 'listng_filters');




/**
 * Remove the More Products and Product Reviews tabs from WooCommerce
 */
add_filter( 'woocommerce_product_tabs', 'remove_product_tabs', 98 );

function remove_product_tabs( $tabs ) {
    unset( $tabs['more_seller_product'] ); // Remove the More Products tab
    unset( $tabs['reviews'] ); // Remove the Product Reviews tab

    return $tabs;
}

/**
 * Custom Blog Start
 */


function new_blogs(){
	ob_start();

	$args = array(
	    'post_type' => 'post',
	    'post_status' => 'publish',
	    'order' => 'DESC',
	    'posts_per_page' => -1,
	);
	$loop = new WP_Query( $args );

	if( $loop->have_posts() ){ $count = 1;?>
		<div class="blog-wrap">
			<?php while ( $loop->have_posts() ) { $loop->the_post();
			$featured_img = get_the_post_thumbnail_url( get_the_ID(), 'large'); ?>

				<?php if( $count % 6 == 1 || $count % 6 == 0 ){ ?>
					<div class="blog-box vertical-box blog-box-<?php echo $count; ?>">
						<div class="blog-item">
							<?php if(get_field('schedule_date')): ?>
								<style type="text/css">.blog-box.blog-box-<?php echo $count; ?>::after{content: 'Coming Soon' !important;}</style>
								<div class="blog-item-overlay"><?php the_field('schedule_date'); ?></div>
							<?php endif; ?>
							<div class="blog-item-img">
								<?php if($featured_img && $featured_img != ''){ ?>
								<img src="<?php echo $featured_img; ?>" style="max-width: 100%;">
								<?php } else { ?>
								<img src="https://reserve-ta-bulle.fr/wp-content/uploads/2023/08/blog-1.png" style="max-width: 100%;">
								<?php } ?>
							</div>
							<div class="blog-item-content">
								<h2><?php the_title(); ?></h2>
								<div class="blog-content"><?php the_excerpt(); ?></div>
								<!-- <a href="<?php //the_permalink(); ?>" class="blog-lnk">mehr Erfahren</a> -->
							</div>
						</div>

						<a href="<?php the_permalink(); ?>" class="blog-lnk"><?php esc_html_e('mehr Erfahren', 'listeo_core'); ?></a>

					</div>
				<?php } ?>

				<?php if( !($count % 6 == 1 || $count % 6 == 0) ){ ?>
					<div class="blog-box horizontal-box blog-box-<?php echo $count; ?>">
						<div class="blog-item">
							<?php if(get_field('schedule_date')): ?>
								<style type="text/css">.blog-box.blog-box-<?php echo $count; ?>::after{content: 'Coming Soon' !important;}</style>
								<div class="blog-item-overlay"><?php the_field('schedule_date'); ?></div>
							<?php endif; ?>
							<div class="blog-hrzntl">
								<div class="blog-item-img">
									<?php if($featured_img && $featured_img != ''){ ?>
									<img src="<?php echo $featured_img; ?>" style="max-width: 100%;">
									<?php } else { ?>
									<img src="https://reserve-ta-bulle.fr/wp-content/uploads/2023/08/blog-2.png" style="max-width: 100%;">
									<?php } ?>
								</div>
								<div class="blog-item-content">
								<h2><?php the_title(); ?></h2>
									<div class="blog-content"><?php the_excerpt(); ?></div>
									<!-- <a href="<?php //the_permalink(); ?>" class="blog-lnk">mehr Erfahren</a> -->
								</div>
							</div>
						</div>

						<a href="<?php the_permalink(); ?>" class="blog-lnk"><?php esc_html_e('mehr Erfahren', 'listeo_core'); ?></a>

					</div>
				<?php } ?>

			<?php $count++; } wp_reset_postdata(); ?>

<!-- 			<div class="blog-box">
				<div class="blog-item">
					<h2><?php //esc_html_e('Du wünschst dir einen bestimmten Blogartikel?', 'listeo_core'); ?></h2>
					<p><?php //esc_html_e('Schreibe uns gerne, welches Thema dich nocht interessiert.', 'listeo_core'); ?></p>
					<p><?php //printf(
					    //esc_html__( 'Hier gehts zum %sKontaktformular%s', 'listeo_core' ),
    					//'<a id="blog-popup" href="javascript:void(0);">',
    					//'</a>'
					// ); ?></p>
				</div>
			</div> -->

		</div>

	<script>
		// Define the repeating pattern based on your original CSS
        const gridPatterns = [
            { row: 1, col: 1, rowSpan: 2, colSpan: 1, class: 'pattern-1' }, // Items 1, 7, 13...
            { row: 1, col: 2, rowSpan: 1, colSpan: 2, class: 'pattern-2' }, // Items 2, 8, 14...
            { row: 2, col: 2, rowSpan: 1, colSpan: 2, class: 'pattern-3' }, // Items 3, 9, 15...
            { row: 3, col: 1, rowSpan: 1, colSpan: 2, class: 'pattern-4' }, // Items 4, 10, 16...
            { row: 4, col: 1, rowSpan: 1, colSpan: 2, class: 'pattern-5' }, // Items 5, 11, 17...
            { row: 3, col: 3, rowSpan: 2, colSpan: 1, class: 'pattern-6' }  // Items 6, 12, 18...
        ];
		
		function applyGridPositions() {
            const blogBoxes = document.querySelectorAll('.blog-box');
            
            blogBoxes.forEach((box, index) => {
                const patternIndex = index % 6; // Every 6 items repeat the pattern
                const cycleNumber = Math.floor(index / 6); // Which cycle we're in
                const pattern = gridPatterns[patternIndex];
                
                // Calculate actual grid position accounting for previous cycles
                // Each cycle adds 4 rows (based on your original pattern)
                const startRow = pattern.row + (cycleNumber * 4);
                const endRow = startRow + pattern.rowSpan;
                const startCol = pattern.col;
                const endCol = startCol + pattern.colSpan;
                
                // Apply grid-area and styling
                box.style.gridArea = `${startRow} / ${startCol} / ${endRow} / ${endCol}`;
//                 box.className = `blog-box ${pattern.class}`;
				
				box.classList.add(pattern.class);
                
                // Update the text to reflect current position
//                 box.textContent = `Blog Post ${index + 1}`;
            });
            
            // Update counter after positioning
            updateCounter();
        }
		
		// Apply positions when page loads
        document.addEventListener('DOMContentLoaded', function() {
            applyGridPositions();
        });


	</script>


	<?php } 
	
	return ob_get_clean();
}
add_shortcode('new_blog', 'new_blogs');
/**
 * Custom Blog End
 */



function add_multiple_to_cart_action() {
    if ( ! isset( $_REQUEST['multiple-item-to-cart'] ) || false === strpos( wp_unslash( $_REQUEST['multiple-item-to-cart'] ), '|' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            return;
        }

    wc_nocache_headers();

    $product_ids        = apply_filters( 'woocommerce_add_to_cart_product_id', wp_unslash( $_REQUEST['multiple-item-to-cart'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
    $product_ids = explode( '|', $product_ids );
    if( ! is_array( $product_ids ) ) return;

    $product_ids = array_map( 'absint', $product_ids );
    $was_added_to_cart = false;
    $last_product_id = end($product_ids);
    //stop re-direction
    add_filter( 'woocommerce_add_to_cart_redirect', '__return_false' );
    foreach ($product_ids as $index => $product_id ) {
        $product_id = absint(  $product_id  );
        if( empty( $product_id ) ) continue;
        $_REQUEST['add-to-cart'] = $product_id;
        if( $product_id === $last_product_id ) {

            add_filter( 'option_woocommerce_cart_redirect_after_add', function() { 
                return 'yes'; 
            } );
        } else {
            add_filter( 'option_woocommerce_cart_redirect_after_add', function() { 
                return 'no'; 
            } );
        }

        WC_Form_Handler::add_to_cart_action();
    }
}
add_action( 'wp_loaded', 'add_multiple_to_cart_action', 20 );





function christmas_box_ss(){
	ob_start(); ?>

	<form id="gift-box-form">

		<h4><?php esc_html_e('Bitte wähle eine Option', 'chirstmas_page'); ?></h4>
        <div class="voucher-radio-wrap">
			<label class="msform-card">
                <input type="radio" class="fs_radio" name="firstOpt" value="Voucher" checked>
                <span class="radio-details">
                    <span class="radio-title"><?php esc_html_e('Standortgutschein', 'chirstmas_page'); ?></span>
                </span>
            </label>

            <label class="msform-card">
                <input type="radio" class="fs_radio" name="firstOpt" value="Wertgutschein">
                <span class="radio-details">
                    <span class="radio-title"><?php esc_html_e('Wertgutschein', 'chirstmas_page'); ?></span>
                </span>
            </label>
        </div>

		<div class="chtistmas-opt" style="display:none;">
		
			<h4><?php esc_html_e('Wähle deinen Standort:', 'chirstmas_page'); ?></h4>
			<div class="locations-radio-wrap">
				<label class="msform-card">
	                <input type="radio" class="fs_radio" name="tentLoc" value="Hesel">
	                <span class="radio-details">
	                    <img src="https://book-a-bubble.de/wp-content/uploads/2023/10/icons8-igloo-64.png">
	                    <span class="radio-title"><?php esc_html_e('Hesel', 'listeo_core'); ?></span>
	                </span>
	            </label>

	            <label class="msform-card">
	                <input type="radio" class="fs_radio" name="tentLoc" value="Elzach">
	                <span class="radio-details">
	                    <img src="https://book-a-bubble.de/wp-content/uploads/2023/10/icons8-igloo-64.png">
	                    <span class="radio-title"><?php esc_html_e('Elzach', 'listeo_core'); ?></span>
	                </span>
	            </label>

	            <label class="msform-card">
	                <input type="radio" class="fs_radio" name="tentLoc" value="Gerbstedt">
	                <span class="radio-details">
	                    <img src="https://book-a-bubble.de/wp-content/uploads/2023/10/icons8-igloo-64.png">
	                    <span class="radio-title"><?php esc_html_e('Gerbstedt', 'listeo_core'); ?></span>
	                </span>
	            </label>

	            <label class="msform-card">
	                <input type="radio" class="fs_radio" name="tentLoc" value="Gutach">
	                <span class="radio-details">
	                    <img src="https://book-a-bubble.de/wp-content/uploads/2023/10/icons8-igloo-64.png">
	                    <span class="radio-title"><?php esc_html_e('Gutach', 'listeo_core'); ?></span>
	                </span>
	            </label>

	            <label class="msform-card">
	                <input type="radio" class="fs_radio" name="tentLoc" value="Ellenberg">
	                <span class="radio-details">
	                    <img src="https://book-a-bubble.de/wp-content/uploads/2023/10/icons8-igloo-64.png">
	                    <span class="radio-title"><?php esc_html_e('Ellenberg', 'listeo_core'); ?></span>
	                </span>
	            </label>

	            <label class="msform-card">
	                <input type="radio" class="fs_radio" name="tentLoc" value="Ingolstadt">
	                <span class="radio-details">
	                    <img src="https://book-a-bubble.de/wp-content/uploads/2023/10/icons8-igloo-64.png">
	                    <span class="radio-title"><?php esc_html_e('Ingolstadt', 'listeo_core'); ?></span>
	                </span>
	            </label>

	            <label class="msform-card">
	                <input type="radio" class="fs_radio" name="tentLoc" value="Furtwangen">
	                <span class="radio-details">
	                    <img src="https://book-a-bubble.de/wp-content/uploads/2023/10/icons8-igloo-64.png">
	                    <span class="radio-title"><?php esc_html_e('Furtwangen', 'listeo_core'); ?></span>
	                </span>
	            </label>

	            <label class="msform-card">
	                <input type="radio" class="fs_radio" name="tentLoc" value="Füssen im Allgäu">
	                <span class="radio-details">
	                    <img src="https://book-a-bubble.de/wp-content/uploads/2023/10/icons8-igloo-64.png">
	                    <span class="radio-title"><?php esc_html_e('Füssen im Allgäu', 'listeo_core'); ?></span>
	                </span>
	            </label>

	            <label class="msform-card">
	                <input type="radio" class="fs_radio" name="tentLoc" value="Berlin">
	                <span class="radio-details">
	                    <img src="https://book-a-bubble.de/wp-content/uploads/2023/10/icons8-igloo-64.png">
	                    <span class="radio-title"><?php esc_html_e('Berlin', 'listeo_core'); ?></span>
	                </span>
	            </label>

	            <label class="msform-card">
	                <input type="radio" class="fs_radio" name="tentLoc" value="Eichstetten">
	                <span class="radio-details">
	                    <img src="https://book-a-bubble.de/wp-content/uploads/2023/10/icons8-igloo-64.png">
	                    <span class="radio-title"><?php esc_html_e('Eichstetten', 'listeo_core'); ?></span>
	                </span>
	            </label>

	            <label class="msform-card">
	                <input type="radio" class="fs_radio" name="tentLoc" value="Düsseldorf">
	                <span class="radio-details">
	                    <img src="https://book-a-bubble.de/wp-content/uploads/2023/10/icons8-igloo-64.png">
	                    <span class="radio-title"><?php esc_html_e('Düsseldorf', 'listeo_core'); ?></span>
	                </span>
	            </label>

	            <label class="msform-card">
	                <input type="radio" class="fs_radio" name="tentLoc" value="Tauberfeld">
	                <span class="radio-details">
	                    <img src="https://book-a-bubble.de/wp-content/uploads/2023/10/icons8-igloo-64.png">
	                    <span class="radio-title"><?php esc_html_e('Tauberfeld', 'listeo_core'); ?></span>
	                </span>
	            </label>

	            <label class="msform-card">
	                <input type="radio" class="fs_radio" name="tentLoc" value="Peine">
	                <span class="radio-details">
	                    <img src="https://book-a-bubble.de/wp-content/uploads/2023/10/icons8-igloo-64.png">
	                    <span class="radio-title"><?php esc_html_e('Peine', 'listeo_core'); ?></span>
	                </span>
	            </label>

	            <label class="msform-card">
	                <input type="radio" class="fs_radio" name="tentLoc" value="Aurich">
	                <span class="radio-details">
	                    <img src="https://book-a-bubble.de/wp-content/uploads/2023/10/icons8-igloo-64.png">
	                    <span class="radio-title"><?php esc_html_e('Aurich', 'listeo_core'); ?></span>
	                </span>
	            </label>

	            <label class="msform-card">
	                <input type="radio" class="fs_radio" name="tentLoc" value="KrugsdorfEx">
	                <span class="radio-details">
	                    <img src="https://book-a-bubble.de/wp-content/uploads/2023/10/icons8-igloo-64.png">
	                    <span class="radio-title"><?php esc_html_e('Krugsdorf Exklusiv', 'listeo_core'); ?></span>
	                </span>
	            </label>

	            <label class="msform-card">
	                <input type="radio" class="fs_radio" name="tentLoc" value="KrugsdorfWel">
	                <span class="radio-details">
	                    <img src="https://book-a-bubble.de/wp-content/uploads/2023/10/icons8-igloo-64.png">
	                    <span class="radio-title"><?php esc_html_e('Krugsdorf Wellness', 'listeo_core'); ?></span>
	                </span>
	            </label>

	            <label class="msform-card">
	                <input type="radio" class="fs_radio" name="tentLoc" value="KrugsdorfSta">
	                <span class="radio-details">
	                    <img src="https://book-a-bubble.de/wp-content/uploads/2023/10/icons8-igloo-64.png">
	                    <span class="radio-title"><?php esc_html_e('Krugsdorf Standard', 'listeo_core'); ?></span>
	                </span>
	            </label>
	        </div>

	        <h4><?php esc_html_e('Wähle deinen Gutschein:', 'chirstmas_page'); ?></h4>
	        <div class="voucher-radio-wrap">
				<label class="msform-card" id="voucherRomantik">
	                <input type="radio" class="fs_radio" name="voucherType" value="Romantik">
	                <span class="radio-details">
	                    <span class="radio-title"><?php esc_html_e('Gutschein Romantik', 'chirstmas_page'); ?></span>
	                </span>
	            </label>

	            <label class="msform-card" id="voucherStandard">
	                <input type="radio" class="fs_radio" name="voucherType" value="Standard">
	                <span class="radio-details">
	                    <span class="radio-title"><?php esc_html_e('Gutschein Standard', 'chirstmas_page'); ?></span>
	                </span>
	            </label>
	        </div>

	    </div>

	    <div class="christmasOutput">
	    	<div class="cOutput-wrap">
	    		<div class="cOutput-left">
	    			<div class="voucher-price"></div>
	    			<p class="voucher-desc"></p>
	    		</div>
	    		<div class="cOutput-right" style="display:none;">
	    			<div class="voucher-price"><?php esc_html_e('Gratis', 'chirstmas_page'); ?></div>
	    			<p class="voucher-desc"><img src="https://book-a-bubble.de/wp-content/uploads/2023/11/gift-box1.jpg"></p>
	    		</div>
	    	</div>
	    </div>

        <div class="gift_box_btn-wrap">
	        <a href="javascript:void(0)" id="gift_box_btn"><?php esc_html_e('Zur Kasse', 'chirstmas_page'); ?></a>
	    </div>

	</form>

	<?php return ob_get_clean();
}
add_shortcode('christmas_box_ss', 'christmas_box_ss');

// Disable free shipping for a specific product
add_filter( 'woocommerce_package_rates', 'custom_hide_free_shipping_for_shipping_class', 9999, 2 );
function custom_hide_free_shipping_for_shipping_class( $rates, $package ) {
    $shipping_class_target = 188; // Replace this with the correct shipping class ID
    $in_cart = false;

    foreach ( WC()->cart->get_cart_contents() as $cart_item ) {
        if ( $cart_item['data']->get_shipping_class_id() == $shipping_class_target ) {
            $in_cart = true;
            break;
        }
    }

    if ( $in_cart ) {
        foreach ( $rates as $rate_id => $rate ) {
            if ( 'free_shipping' === $rate->method_id ) {
                unset( $rates[ $rate_id ] );
                break;
            }
        }
    }

    return $rates;
}



// Change Listing Taxonomy Name 'listing_location'
function overwrite_listing_location( $taxonomy, $object_type, $args ){
    if( 'listing_location' == $taxonomy ){
        remove_action( current_action(), __FUNCTION__ );
        $args['rewrite'] = array('slug' => 'listings_features');

        $args['labels']->singular_name = 'Main Features';
        $args['labels']->menu_name = 'Main Features';
        $args['labels']->name = 'Main Features';
        $args['label'] = 'Main Features';
        register_taxonomy( $taxonomy, $object_type, $args );
    }
}
add_action( 'registered_taxonomy', 'overwrite_listing_location', 10, 3 );

// Change Listing Taxonomy Name 'region'
function overwrite_region( $taxonomy, $object_type, $args ){
    if( 'region' == $taxonomy ){
        remove_action( current_action(), __FUNCTION__ );
        $args['rewrite'] = array('slug' => 'listings_countries');

        $args['labels']->singular_name = 'Countries';
        $args['labels']->menu_name = 'Countries';
        $args['labels']->name = 'Countries';
        $args['label'] = 'Countries';
        register_taxonomy( $taxonomy, $object_type, $args );
    }
}
add_action( 'registered_taxonomy', 'overwrite_region', 10, 3 );

// Change Listing Taxonomy Name 'listing_feature'
function overwrite_listing_feature( $taxonomy, $object_type, $args ){
    if( 'listing_feature' == $taxonomy ){
        remove_action( current_action(), __FUNCTION__ );
        $args['rewrite'] = array('slug' => 'listings_regions');

        $args['labels']->singular_name = 'Regions';
        $args['labels']->menu_name = 'Regions';
        $args['labels']->name = 'Regions';
        $args['label'] = 'Regions';
        register_taxonomy( $taxonomy, $object_type, $args );
    }
}
add_action( 'registered_taxonomy', 'overwrite_listing_feature', 10, 3 );



function featured_listing_highlight_features(){

	$listing_features = get_field( 'highlight_features', get_the_ID() );
	if( $listing_features ){
		echo '<ul class="listing-feats">';
		foreach( $listing_features as $list ){
			echo '<li>' . esc_html( get_the_title( $list->ID ) ) . '</li>';
		}
		echo '</ul>';
	}

}
add_shortcode('featured_listing_highlight_features', 'featured_listing_highlight_features');


function featured_list_query( $query ) {
	$meta_query = $query->get( 'meta_query' );

	if ( ! $meta_query ) {
		$meta_query = [];
	}

	$meta_query[] = [
		'key' => '_featured',
		'value' => [ 'on', ],
		'compare' => '='
	];
	
// 	$meta_query[] = [
// 		'key' => 'featured_tent',
// 		'value' => [ 'yes', ],
// 		'compare' => '='
// 	];

	$query->set( 'meta_query', $meta_query );
}
add_action( 'elementor/query/featured_list_query', 'featured_list_query' );


function get_listing_regions($atts){
	ob_start();

	if( $atts['country'] && 'gr' === $atts['country'] ){
		$parent_region = 284; //germany
	} else if( $atts['country'] && 'fr' === $atts['country'] ){
// 		$parent_region = 274; //france
		$parent_region = 294; //france
	} else {
		$parent_region = 0;
	}

	if( isset($atts['bg']) && $atts['bg'] && '1' === $atts['bg'] ){
		$dark_bg = 'dark-bg';
	} else {
		$dark_bg = ' ';
	}
	$cur_trm_id = get_queried_object()->term_id;

	$region_terms = get_terms([ 'taxonomy' => 'listing_feature', 'hide_empty' => false, 'child_of' => $parent_region ]);

	if($region_terms){

		echo '<div class="region-slider owl-carousel owl-theme '.$dark_bg.'">';
	    foreach ($region_terms as $r_term) {

	    	$region_id = $r_term->term_id;
			$region_name = $r_term->name;
			$region_url	= get_term_link($r_term);
			$region_acf_helper 	= 'term_' . $region_id;

			if( get_field('region_image', $region_acf_helper) ){
				$region_img = get_field('region_image', $region_acf_helper);
			} else {
				$region_img = wc_placeholder_img_src();
			}

			$datas = array(
				'id' 	=> $region_id,
				'name'	=>	$region_name,
				'url'	=>	$region_url,
				'image'	=>	$region_img
			);

			if(!$cur_trm_id){
				get_template_part('region', 'item', $datas);
			} else if($cur_trm_id && $cur_trm_id != $region_id){
				get_template_part('region', 'item', $datas);
			} else {
				continue;
			}

	    }
	    echo '</div>';
	    ?>
	    <script type="text/javascript">
	    	jQuery(document).ready(function(){
	            jQuery(".region-slider").owlCarousel({
	                loop: true,
	                loop: true,
	                margin: 25,
	                autoplay: false,
	                nav: true,
	                navText: ['<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22.6066 11.9998L1.3934 11.9998M1.3934 11.9998L12 22.6064M1.3934 11.9998L12 1.39324" stroke="#54775E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>','<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.3934 12.0002H22.6066M22.6066 12.0002L12 1.39355M22.6066 12.0002L12 22.6068" stroke="#54775E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>'],
	                autoplayTimeout: 8000,
	                autoplayHoverPause: true,
	                smartSpeed: 800,
	                dots: false,
	                responsive:{
	                    0:{
	                        items: 1,
	                    },
	                    768:{
	                        items: 2,
	                    },
	                    1024:{
	                        items: 4,
	                    },
	                    1401:{
	                        items: 5,
	                    }
	                }
	            });
	        });
        </script>
	    <?php
	}

	return ob_get_clean();
}
add_shortcode('get_regions', 'get_listing_regions');



function regions_by_country_grid($atts){
	ob_start();
	
	$parent_region = 294;

	$region_terms = get_terms([ 'taxonomy' => 'listing_feature', 'hide_empty' => false, 'child_of' => $parent_region ]);

	if($region_terms){

		echo '<div class="ctry-regions">';
	    foreach ($region_terms as $r_term) {

	    	$region_id = $r_term->term_id;
			$region_name = $r_term->name;
			$region_url	= get_term_link($r_term);
			$region_acf_helper 	= 'term_' . $region_id;

			if( get_field('region_image', $region_acf_helper) ){
				$region_img = get_field('region_image', $region_acf_helper);
			} else {
				$region_img = wc_placeholder_img_src();
			}

			$datas = array(
				'id' 	=> $region_id,
				'name'	=>	$region_name,
				'url'	=>	$region_url,
				'image'	=>	$region_img
			);

	    	get_template_part('region', 'item', $datas);

	    }
	    echo '</div>';
	}

	return ob_get_clean();
}
add_shortcode('regions_by_country', 'regions_by_country_grid');




function region_tents_and_filters(){
	ob_start();

	$cur_trm_id = get_queried_object()->term_id;

	$args = array(
        'post_type' => 'listing',
        'tax_query' => array(             
             array(
                'taxonomy' => 'listing_feature',
                'field' => 'id',
                'terms' => $cur_trm_id,
            ),
		)
	);
	$query = new WP_Query($args);

	if ($query->have_posts()) { ?>

	<div class="region-tents-filter">
		<?php $main_feats = get_terms([
		    'taxonomy' => 'listing_location',
		    'hide_empty' => true,
		]);
		if(!$main_feats){
			echo '<input type="hidden" id="cur_trm_id" name="cur_trm_id" value="'.$cur_trm_id.'">';
			echo '<div id="feat_cb_wrap">';
				foreach($main_feats as $main_feat){ ?>
					<label class="proForm-cbox" for="single_sheet"><?php esc_html_e($main_feat->name, 'listeo_core'); ?>
	                    <input type="checkbox" value="<?php echo $main_feat->term_id ?>" name="<?php echo $main_feat->slug ?>" id="fil-<?php echo $main_feat->slug ?>">
	                    <span class="checkmark"></span>
	                </label>
	            <?php }
			echo '</div>';
		} ?>
	</div>

	<div class="region-tents-wrap">
		<div class="region-tents">
			<?php while ($query->have_posts()) { $query->the_post();
				$feat_img_url = get_the_post_thumbnail_url( get_the_ID(), 'full'); ?>
				<div class="region-tnt">
					<a href="<?php echo esc_url(get_the_permalink()); ?>"><img src="<?php echo esc_url($feat_img_url); ?>"></a>
					<div class="bubble-cntnt">
						<div class="buble-tnt"><?php esc_html_e('Bubble Tent', 'listeo_core'); ?></div>
						<h3 class="bubble-name"><a href="<?php echo esc_url(get_the_permalink()); ?>"><?php esc_html_e(get_the_title(), 'listeo_core'); ?></a></h3>
						<?php
						$listing_features = get_field( 'highlight_features', get_the_ID() );
						if( $listing_features ){
							echo '<ul class="listing-feats">';
							foreach( $listing_features as $list ){
								echo '<li>' . esc_html( get_the_title( $list->ID ) ) . '</li>';
							}
							echo '</ul>';
						}
						?>
					</div>
				</div>
			<?php } wp_reset_postdata(); ?>
		</div>
	</div>

	<?php
	}
	return ob_get_clean();
}
add_shortcode('region_tents_filter', 'region_tents_and_filters');

function rgn_tents_filter(){

	$m_features = array();
	$filters_data = $_POST['features'];
	$curID = $_POST['curID'];

	if($filters_data){
		foreach($filters_data as $filter_data){
			$m_features[] = $filter_data;
		}
		$args = array(
	        'post_type' => 'listing',
	        'tax_query' => array(
	        	'relation' => 'AND',         
	            array(
	                'taxonomy' => 'listing_feature',
	                'field' => 'id',
	                'terms' => $curID,
	            ),
	            array(
	                'taxonomy' => 'listing_location',
	                'field' => 'id',
	                'terms' => $m_features,
	            ),
			)
		);

	} else {
		$args = array(
	        'post_type' => 'listing',
	        'tax_query' => array(
	            array(
	                'taxonomy' => 'listing_feature',
	                'field' => 'id',
	                'terms' => $curID,
	            ),
			)
		);
	}
	$query1 = new WP_Query($args);

	if ($query1->have_posts()) {
		while ($query1->have_posts()) { $query1->the_post();
			$feat_img_url = get_the_post_thumbnail_url( get_the_ID(), 'full'); ?>
			<div class="region-tnt">
				<a href="<?php echo esc_url(get_the_permalink()); ?>"><img src="<?php echo esc_url($feat_img_url); ?>"></a>
				<div class="bubble-cntnt">
					<div class="buble-tnt"><?php esc_html_e('Bubble Tent', 'listeo_core'); ?></div>
					<h3 class="bubble-name"><a href="<?php echo esc_url(get_the_permalink()); ?>"><?php esc_html_e(get_the_title(), 'listeo_core'); ?></a></h3>
					<?php
					$listing_features = get_field( 'highlight_features', get_the_ID() );
					if( $listing_features ){
						echo '<ul class="listing-feats">';
						foreach( $listing_features as $list ){
							echo '<li>' . esc_html( get_the_title( $list->ID ) ) . '</li>';
						}
						echo '</ul>';
					}
					?>
				</div>
			</div>
		<?php }
		wp_reset_postdata();
	} else {
		esc_html_e('Nothing found!', 'listeo_core');
	}
	
	die();
}
add_action('wp_ajax_rgn_tents_filter', 'rgn_tents_filter');
add_action('wp_ajax_nopriv_rgn_tents_filter', 'listng_filters');

function get_all_listings($atts){
	ob_start();
	
	if( $atts['region'] ){

		$args = array(
	        'post_type' => 'listing',
	        'post_status' => 'publish',
			'posts_per_page' => -1,
			'tax_query'      => array(
				array (
					'taxonomy' => 'listing_feature',
					'field' => 'slug',
					'terms' => $atts['region'],
				)
			)
		);
		
	} else {
		$args = array(
	        'post_type' => 'listing',
	        'post_status' => 'publish',
			'posts_per_page' => -1,
		);
	}
	$query = new WP_Query($args);

	if($query->have_posts()){

		echo '<div class="ctry-regions">';
	    while ($query->have_posts()) { $query->the_post();
			$feat_img_url = get_the_post_thumbnail_url( get_the_ID(), 'full');
			$woocommerce_prd = get_post_meta(get_the_ID(), '_wooproduct_id', true);
// 			$product_slug = '';
			
			if(!empty($woocommerce_prd)){
				if (!str_contains($woocommerce_prd, ',')) {
					$updatedproductid= apply_filters( 'wpml_object_id', $woocommerce_prd, 'product', TRUE  );	
					$pdf_vouch = get_post_meta($updatedproductid , '_wpdesk_pdf_coupons' , true);
					if(isset($pdf_vouch) && $pdf_vouch=='yes'){
						$product = wc_get_product($updatedproductid);
						$product_slug = $product->get_slug();
					}
				}
				if( isset($product_slug) ){
					$siteurls = get_site_url()."/produit/".$product_slug."/";
				} else {
					$siteurls = get_the_permalink();
				}
			} else {
				$siteurls = get_the_permalink();
			}
			?>

	    	<div class="region-item" style="background-image:url(<?php echo $feat_img_url; ?>);">
				<a href="<?php echo esc_url($siteurls); ?>">
					<div class="region-item-icon"><svg width="35" height="35" viewBox="0 0 35 35" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="0.5" y="0.5" width="34" height="34" rx="17" stroke="white"/><path d="M13.125 21.875L21.875 13.125M21.875 13.125H13.125M21.875 13.125V21.875" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
					<div><?php esc_html_e('Bubble Tent', 'listeo_core'); ?></div>
					<h4><?php esc_html_e(get_the_title(), 'listeo_core'); ?></h4>
				</a>
			</div>
		<?php
	    } wp_reset_postdata();
	    echo '</div>';
	}

	return ob_get_clean();
}
add_shortcode('get_all_listings', 'get_all_listings');

function get_all_the_listings($atts){
	ob_start();

	if( isset($atts['country']) && 'fr' === $atts['country'] ){

		$args = array(
	        'post_type' => 'listing',
	        'post_status' => 'publish',
			'posts_per_page' => -1,
			'tax_query'      => array(
				array (
					'taxonomy' => 'region',
					'field' => 'slug',
					'terms' => 'frankreich',
				)
			)
		);
		
	} else if( isset($atts['region']) ){

		$args = array(
	        'post_type' => 'listing',
	        'post_status' => 'publish',
			'posts_per_page' => -1,
			'tax_query'      => array(
				array (
					'taxonomy' => 'listing_feature',
					'field' => 'slug',
					'terms' => $atts['region'],
				)
			)
		);
		
	} else {
		$args = array(
	        'post_type' => 'listing',
	        'post_status' => 'publish',
			'posts_per_page' => -1,
		);
	}


	$wpQuery = new WP_Query($args);

	if($wpQuery->have_posts()){

		echo '<div class="ctry-regions">';
	    while ($wpQuery->have_posts()) { $wpQuery->the_post();
			$feat_img_url = get_the_post_thumbnail_url( get_the_ID(), 'large');
			
			?>
	    	<div class="region-item" style="background-image:url(<?php echo $feat_img_url; ?>);">
				<a href="<?php echo esc_url(get_the_permalink()); ?>">
					<div class="region-item-icon"><svg width="35" height="35" viewBox="0 0 35 35" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="0.5" y="0.5" width="34" height="34" rx="17" stroke="white"/><path d="M13.125 21.875L21.875 13.125M21.875 13.125H13.125M21.875 13.125V21.875" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
					<div><?php esc_html_e('Bubble Tent', 'listeo_core'); ?></div>
					<h4><?php esc_html_e(get_the_title(), 'listeo_core'); ?></h4>
				</a>
			</div>
		<?php
	    } wp_reset_postdata();
	    echo '</div>';
	}

	return ob_get_clean();
}
add_shortcode('all_listings', 'get_all_the_listings');

/* 
Disable 'bacs' payment option if the start date is less than next 21 days 
*/

add_action('woocommerce_checkout_update_order_review', 'update_payment_method_option');

function update_payment_method_option( $posted_data) {

	//$wc_gateways      = new WC_Payment_Gateways();
	$payment_gateways = WC()->payment_gateways->get_available_payment_gateways();
	
	parse_str( $posted_data, $post_data_array );

	if ( isset($post_data_array['smoobu_calendar_start']) && WC()->payment_gateways->payment_gateways()['bacs']->enabled ) {
		$smoobu_start_date = $post_data_array['smoobu_calendar_start']; 
		$current_date = date( 'Y-m-d' );
		$smoobu_start_date = date( 'Y-m-d', strtotime( $smoobu_start_date ) );
		$current_date_plus_seven_days = date( 'Y-m-d', strtotime( $current_date . ' + 21 days' ) );

		if ( $smoobu_start_date <= $current_date_plus_seven_days ) {
			WC()->payment_gateways->payment_gateways()['bacs']->enabled = false;
		} else {
			//WC()->payment_gateways->payment_gateways()['bacs']->enabled = true;
		}
	}
}


/*
 * Disable the ajax of 'single_add_to_cart_button' of 'Cashier for WooCommerce' plugin
 * */

function disable_ajax_single_add_to_cart_button() {
	
	if (is_product()) {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {

            $('.single_add_to_cart_button').off('click');

            $(document).on('click', '.single_add_to_cart_button', function(e) {
								
                e.preventDefault();
                e.stopPropagation();
				
                var $thisbutton = $(this);
                var $form = $thisbutton.closest("form.cart");
                if ($form.length) {
                    $form.submit();
                } else {
                    console.log('No form found');
                }
            });
        });
    </script>
    <?php
	}
	?>
	 <script type="text/javascript">
		jQuery(window).on("load", function(){
			if( jQuery('#_number_of_kids').length ){
				jQuery(document).on('change', '#_number_of_kids, #_number_of_adults', function() {
					jQuery(document.body).trigger("update_checkout");
				});   
			}
		});
	</script>
	<?php
}
add_action('wp_footer', 'disable_ajax_single_add_to_cart_button', 200);


// Remove the default Dokan Vendor name from cart or checkout page
remove_filter( 'woocommerce_get_item_data', 'dokan_product_seller_info', 10 );



// Stop Listeo Core from changing product vendor to admin after Listing Update *** START//

// Step 1: Store listing author and product ID temporarily.
add_action('save_post_listing', 'store_listing_data_temporarily', 10, 3);
function store_listing_data_temporarily($post_id, $post, $update) {
    if (!$update) return; // Only act on updates, not new listings

    // Check if the post type is 'listing'
    if ($post->post_type !== 'listing') return; // Only proceed for 'listing' post type

    // Get the associated product ID from the listing.
    $product_id = get_post_meta($post_id, 'product_id', true);
    if (!$product_id) return; // Exit if no product ID is found

    // Store the listing author and product ID in a transient.
    $listing_author_id = $post->post_author;
    set_transient('listing_author_product_' . $post_id, [
        'author' => $listing_author_id,
        'product_id' => $product_id
    ], 60); // Store it for 60 seconds
}

// Step 2: Update the product author after Listeo core finishes.
add_action('wp_insert_post', 'correct_product_author_after_listeo', 20, 3);
function correct_product_author_after_listeo($post_id, $post, $update) {
    // Only proceed if this is an update
    if (!$update) return;

    // Check if we have stored listing data for this post
    $transient_key = 'listing_author_product_' . $post_id;
    $stored_data = get_transient($transient_key);

    // Only proceed if we have stored data and if it's a 'listing'
    if ($stored_data && $post->post_type === 'listing') {
        $product_id = $stored_data['product_id'];
        $listing_author_id = $stored_data['author'];

        // Check if the current author of the product is the admin (ID 1)
        $current_product_author_id = get_post_field('post_author', $product_id);
        if ($current_product_author_id !== $listing_author_id) {
            // Update the product author to match the listing author
            wp_update_post(array(
                'ID' => $product_id,
                'post_author' => $listing_author_id
            ));

            // Log the change
            error_log('Product Author Updated After Listeo: Listing ID ' . $post_id . ' sets Product ID ' . $product_id . ' author to ' . $listing_author_id);
        }

        // Clean up the transient
        delete_transient($transient_key); // Clean up the transient
    }
}

// Stop Listeo Core from changing product vendor to admin after Listing Update *** END//


//Set busy days for the Smoobu calendar based on WooCommerce listing products.
function set_product_listing_busy_days($days) {

    if (!is_plugin_active('woocommerce/woocommerce.php')) {
        return $days;
    }

    $debug_info = array();

    global $post;
    if ($post->post_type === 'listing') {

        $woo_product_id = get_post_meta($post->ID, 'product_id', true);
        $listing_product = wc_get_product($woo_product_id);

        if (!empty($listing_product)) {
            $property_id = $listing_product->get_meta('custom_property_id_field');

            if ($listing_product->is_type('listing_booking')) {
                $lead_time = intval($listing_product->get_meta('min_lead_time'));
                $debug_info['lead_time'] = $lead_time;

                if ($lead_time > 0) {
                    $lead_time_dates = array_map(
                        function ($x) {
                            return gmdate('Y-m-d', strtotime("+$x days"));
                        },
                        range(0, $lead_time)
                    );

                    $days = array_unique(array_merge($lead_time_dates, $days));
                    sort($days);
                }
            }
        } 
    }

    return $days;
}

add_filter('smoobu_set_calendar_busy_days', 'set_product_listing_busy_days');

//21 nov 2024 Updates
add_action( 'woocommerce_before_calculate_totals', 'remove_items_from_cart' );
 //remove the product listing if there is a another item in the cart
function remove_items_from_cart( $cart ) {
    $product_category_to_remove = 'listing_booking';

    $items = $cart->get_cart();

    foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
        $product = $cart_item['data'];
        if ( $product->is_type( $product_category_to_remove ) ) {
            $cart->remove_cart_item( $key );
            //break;
        }
    }
    $cart->set_cart_contents( $items ); // Update the cart with removed items.
}

add_filter('woocommerce_calculated_total', 'apply_discount_to_cart_total', 10, 2);

function apply_discount_to_cart_total($total, $cart) {
    // Get applied coupons
    $applied_coupons = $cart->get_applied_coupons();
	//echo "<pre>"; print_r([$cart, $total, $applied_coupons]); echo "</pre>";    
	// Check if there are any applied coupons
    if (!empty($applied_coupons)) {
        // Loop through applied coupons
        foreach ($applied_coupons as $coupon_code) {
            // Get the coupon object
            $coupon = new WC_Coupon($coupon_code);

            // Get the discount type and amount
            $discount_type = $coupon->get_discount_type();
            $discount_amount = $coupon->get_amount();

            // Check if the discount is a fixed amount or percentage
            if ($discount_type === 'percent') {
                // Calculate percentage discount on total
                //$discount = ($total * $discount_amount) / 100;
            } elseif ($discount_type === 'fixed_cart') {
                // Fixed discount amount
                $discount = $discount_amount;
				 
				$total_fee = $cart->get_fee_total() + $cart->get_subtotal();
				// Set the discount total in the cart object for display purposes
				if($discount_amount >= $total_fee){
					// Deduct the discount from the total
				    $total -= $discount;
					$cart->set_discount_total($total_fee);
				}
				
            } else {
                // Skip unsupported discount types
                continue;
            }

        }
    }
	
	
	// Ensure total doesn't go below zero
    return max(0, $total);
}

function filter_woocommerce_cart_totals_coupon_html( $coupon_html, $coupon, $discount_amount_html ) {
	if($coupon->get_discount_type() === 'fixed_cart' ) {
		$cartDiscount = WC()->cart->get_discount_total();
		preg_match_all ( '#<a(.+?)</a>#', $coupon_html, $parts );
    	$coupon_html = wc_price('-'.$cartDiscount).$parts[0][0];
	}
		
    return $coupon_html;
}
add_filter( 'woocommerce_cart_totals_coupon_html', 'filter_woocommerce_cart_totals_coupon_html', 10, 3 );



function custom_reservation(){
	ob_start();
	include locate_template('custom-booking.php');
	return ob_get_clean();
}
add_shortcode('custom_res', 'custom_reservation');


// Paypal Temp 0 sum fix
// Enable "Place order" button for PayPal gateway
//add_filter('woocommerce_paypal_payments_use_place_order_button', '__return_true');

// Change the button label – defaults to "Proceed to PayPal"
//add_filter('woocommerce_paypal_payments_place_order_button_text', function() {
   // return __( 'Place order', 'woocommerce' );
//});

// Allow only one voucher purchase at a time
add_filter('woocommerce_add_to_cart_validation', 'restrict_voucher_and_pdf_coupon_cart_contents', 10, 3);

function restrict_voucher_and_pdf_coupon_cart_contents($passed, $product_id, $quantity) {
    // Add all allowed exception product IDs (gift box in all languages)
    $allowed_product_ids = [222556, 222557, 222558]; // Gift box IDs

    // Check if the product is a voucher (downloadable) or a PDF coupon
    $is_voucher = get_post_meta($product_id, '_downloadable', true) === 'yes';
    $is_pdf_coupon = get_post_meta($product_id, '_wpdesk_pdf_coupons', true) === 'yes';

    // Only proceed if it's a restricted product type
    if ($is_voucher || $is_pdf_coupon) {
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $cart_product_id = $cart_item['product_id'];

            // Remove everything except the gift box
            if (!in_array($cart_product_id, $allowed_product_ids)) {
                WC()->cart->remove_cart_item($cart_item_key);
            }
        }
    }

    return $passed;
}





// 1. Add custom column to "listing" CPT
add_filter('manage_listing_posts_columns', 'add_custom_booking_column');
function add_custom_booking_column($columns) {
    $columns['custom_booking'] = 'Custom Booking';
    return $columns;
}

// 2. Display data from ACF field in the custom column
add_action('manage_listing_posts_custom_column', 'show_custom_booking_column_data', 10, 2);
function show_custom_booking_column_data($column, $post_id) {
    if ($column === 'custom_booking') {
        $value = get_field('listing_without_checkout', $post_id); // ACF field
        echo $value ? esc_html($value) : '<em>–</em>';
    }
}

add_filter('woocommerce_get_return_url', 'custom_add_product_type_param_to_return_url', 20, 2);

function custom_add_product_type_param_to_return_url($return_url, $order) {
    if (!$order || !is_a($order, 'WC_Order')) {
        return $return_url;
    }

    // Loop through order items
    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        
        if ($product && $product instanceof WC_Product_Listing_Booking) {
            $parsed_url = parse_url($return_url);
            $separator = isset($parsed_url['query']) ? '&' : '?';
            $return_url .= $separator . 'productType=product';
            break;
        } elseif ($product instanceof WC_Product_Simple || $product instanceof WC_Product_Variable) {
			$parsed_url = parse_url($return_url);
            $separator = isset($parsed_url['query']) ? '&' : '?';
            $return_url .= $separator . 'productType=voucher';
            break;
		}
    }

    return $return_url;
}

add_filter('woocommerce_order_is_vat_exempt', 'handle_vat_exempt', 10, 2);
function handle_vat_exempt($is_exempt, $order){
	return true;
}