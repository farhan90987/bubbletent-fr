<?php

/**
 * Give a user a package
 *
 * @param  int $user_id
 * @param  int $product_id
 * @param  int $order_id
 * @return int|bool false
 */
function listeo_core_give_user_package( $user_id, $product_id, $order_id = 0 ) {
	global $wpdb;

	$package = wc_get_product( $product_id );
	if ( ! $package->is_type( 'listing_package' ) && ! $package->is_type( 'listing_package_subscription' ) ) {
		return false;
	}

	$is_featured = false;
	$is_featured = $package->is_listing_featured();
	$has_booking = $package->has_listing_booking();
	$has_reviews = $package->has_listing_reviews();
	$has_gallery = $package->has_listing_gallery();
	$has_social_links = $package->has_listing_social_links();
	$has_opening_hours = $package->has_listing_opening_hours();
	$has_video = $package->has_listing_video();
	$has_coupons = $package->has_listing_coupons();
	

	$id = $wpdb->get_var( 
		$wpdb->prepare( "SELECT id FROM {$wpdb->prefix}listeo_core_user_packages WHERE
			user_id = %d
			AND product_id = %d
			AND order_id = %d
			AND package_duration = %d
			AND package_limit = %d
			AND package_featured = %d
			AND package_option_booking = %d
			AND	package_option_reviews = %d
			AND	package_option_gallery  = %d
			AND	package_option_gallery_limit  = %d
			AND	package_option_social_links  = %d
			AND	package_option_opening_hours  = %d
			AND	package_option_video   = %d
			AND	package_option_coupons = %d",
			$user_id,
			$product_id,
			$order_id,
			$package->get_duration(),
			$package->get_limit(),
			$is_featured ? 1 : 0,
			$has_booking ? 1 : 0,
			$has_reviews ? 1 : 0,
			$has_gallery  ? 1 : 0,
			$package->get_option_gallery_limit(),
			$has_social_links ? 1 : 0,
			$has_opening_hours ? 1 : 0,
			$has_video ? 1 : 0,
			$has_coupons? 1 : 0
		));
		
	if ( $id ) {
		return $id;
	}

	$wpdb->insert(
		"{$wpdb->prefix}listeo_core_user_packages",
		array(
			'user_id'          				=> $user_id,
			'product_id'       				=> $product_id,
			'order_id'         				=> $order_id,
			'package_count'    				=> 0,
			'package_duration' 				=> $package->get_duration(),
			'package_limit'    				=> $package->get_limit(),
			'package_featured' 				=> $is_featured ? 1 : 0,
			'package_option_booking' 		=> $has_booking ? 1 : 0,
			'package_option_reviews' 		=> $has_reviews ? 1 : 0,
			'package_option_gallery' 		=> $has_gallery ? 1 : 0,
			'package_option_gallery_limit' 	=> $package->get_option_gallery_limit(),
			'package_option_social_links' 	=> $has_social_links ? 1 : 0,
			'package_option_opening_hours'  =>  $has_opening_hours ? 1 : 0,
			'package_option_video'   		=> $has_video ? 1 : 0,
			'package_option_coupons' 		=> $has_coupons? 1 : 0
		)
	);

	return $wpdb->insert_id;
}




/**
 * See if a package is valid for use
 *
 * @param int $user_id
 * @param int $package_id
 * @return bool
 */
function listeo_core_package_is_valid( $user_id, $package_id ) {
	global $wpdb;

	$package = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}listeo_core_user_packages WHERE user_id = %d AND id = %d;", $user_id, $package_id ) );

	if ( ! $package ) {
		return false;
	}

	if ( $package->package_count >= $package->package_limit && $package->package_limit != 0 ) {
		return false;
	}

	return true;
}



/**
 * Increase job count for package
 *
 * @param  int $user_id
 * @param  int $package_id
 * @return int affected rows
 */
function listeo_core_increase_package_count( $user_id, $package_id ) {
	global $wpdb;

	$packages = listeo_core_user_packages( $user_id );

	if ( isset( $packages[ $package_id ] ) ) {
		$new_count = $packages[ $package_id ]->package_count + 1;
	} else {
		$new_count = 1;
	}

	return $wpdb->update(
		"{$wpdb->prefix}listeo_core_user_packages",
		array(
			'package_count' => $new_count,
		),
		array(
			'user_id' => $user_id,
			'id'      => $package_id,
		),
		array( '%d' ),
		array( '%d', '%d' )
	);
}

/**
 * decrease job count for package
 *
 * @param  int $user_id
 * @param  int $package_id
 * @return int affected rows
 */
function listeo_core_decrease_package_count( $user_id, $package_id ) {
	global $wpdb;

	$packages = listeo_core_user_packages( $user_id );

	if ( isset( $packages[ $package_id ] ) ) {
		$new_count = $packages[ $package_id ]->package_count - 1;
	} 
	if($new_count < 0){
		$new_count = 0;
	}

	if(isset($new_count)) {

		return $wpdb->update(
			"{$wpdb->prefix}listeo_core_user_packages",
			array(
				'package_count' => $new_count,
			),
			array(
				'user_id' => $user_id,
				'id'      => $package_id,
			),
			array( '%d' ),
			array( '%d', '%d' )
		);
	}
}




/**
 * Get a users packages from the DB
 *
 * @param  int          $user_id
 * @param string|array $package_type
 * @return array of objects
 */
function listeo_core_user_packages( $user_id ) {
	global $wpdb;

	
	$package_type = array( 'listing_package' );


	$packages = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}listeo_core_user_packages WHERE user_id = %d AND ( package_count < package_limit OR package_limit = 0 );", $user_id ), OBJECT_K );

	return $packages;
}

function listeo_core_get_package_by_id($id){
	global $wpdb;

	$packages = 
	$wpdb->get_row( 
		$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}listeo_core_user_packages WHERE id = %d ;", $id )
	);

	return $packages;
}

/**
 * Get a package
 *
 * @param  stdClass $package
 * @return listeo_core__Package
 */
function listeo_core_get_package( $package ) {
	return new Listeo_Core_Paid_Listings_Package( $package );
}



function listeo_core_available_packages( $user_id, $selected ) {
	global $wpdb;

	
	$package_type = array( 'listing_package' );


	$packages = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}listeo_core_user_packages WHERE user_id = %d AND ( package_count < package_limit OR package_limit = 0 );", $user_id ), ARRAY_A );
	
	$options = '<option  '.selected('',$selected,false).' value="">No package assigned</option>';
	if($packages){
		foreach ($packages as $row) {
			
			$options .= '<option '.selected($row['id'],$selected,false).' value="'.$row['id'].'">'.get_the_title( $row['product_id']).' (orderID:'.$row['order_id'].')</option>';
			# code...
		}
	}
	
	return $options;
}



/**
 * Approve a listing
 *
 * @param  int $listing_id
 * @param  int $user_id
 * @param  int $user_package_id
 * @return void
 */
function listeo_core_approve_listing_with_package( $listing_id, $user_id, $user_package_id ) {
	if ( listeo_core_package_is_valid( $user_id, $user_package_id ) ) {
		$resumed_post_status = get_post_meta( $listing_id, '_post_status_before_package_pause', true );
		if ( ! empty( $resumed_post_status ) ) {
			$listing = array(
				'ID'            => $listing_id,
				'post_status'   => $resumed_post_status,
			);
			delete_post_meta( $listing_id, '_post_status_before_package_pause' );
		} else {
			$listing = array(
				'ID'            => $listing_id,
				'post_date'     => current_time( 'mysql' ),
				'post_date_gmt' => current_time( 'mysql', 1 ),
			);

			switch ( get_post_type( $listing_id ) ) {
				case 'listing' :
					delete_post_meta( $listing_id, '_listing_expires' );
					$listing[ 'post_status' ] = get_option( 'listeo_new_listing_requires_approval' ) ? 'pending' : 'publish';
					break;
				
			}
		}

		// Do update
		wp_update_post( $listing );
		update_post_meta( $listing_id, '_user_package_id', $user_package_id );
		$expire_obj = new Listeo_Core_Post_Types;
		$expire_obj->set_expiry(get_post($listing_id));
		listeo_core_increase_package_count( $user_id, $user_package_id );
		
	}
}

/**
 * Get a package
 *
 * @param  int $package_id
 * @return listeo_core_Package
 */
function listeo_core_get_user_package( $package_id ) {
	global $wpdb;

	$package = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}listeo_core_user_packages WHERE id = %d;", $package_id ) );
	return listeo_core_get_package( $package );
}
/**
 * Get listing IDs for a user package
 *
 * @return array
 */
function listeo_core_get_listings_for_package( $user_package_id ) {
	global $wpdb;

	return $wpdb->get_col( $wpdb->prepare(
		"SELECT post_id FROM {$wpdb->postmeta} " .
		"LEFT JOIN {$wpdb->posts} ON {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID " .
		"WHERE meta_key = '_user_package_id' " .
		'AND meta_value = %s;'
	, $user_package_id ) );
}

function listeo_core_get_dashboard_pages_list(){
	return
	array(
		'listeo_dashboard_page' => array('title' => 'Dashboard', 'content' => '[listeo_dashboard]', 'option' => 'listeo_dashboard_page',),
		'listeo_messages_page' => array('title' => 'Messages', 'content' => '[listeo_messages]', 'option' => 'listeo_messages_page',),
		'listeo_bookings_page' => array('title' => 'Bookings', 'content' => '[listeo_bookings]', 'option' => 'listeo_bookings_page',),
		'listeo_bookings_calendar_page' => array('title' => 'Calendar View', 'content' => '[listeo_calendar_view]', 'option' => 'listeo_bookings_calendar_page',),
		'listeo_user_bookings_page' => array('title' => 'My Bookings', 'content' => '[listeo_my_bookings]', 'option' => 'listeo_user_bookings_page',),
		'listeo_booking_confirmation_page' => array('title' => 'Booking Confirmation', 'content' => '[listeo_booking_confirmation]', 'option' => 'listeo_booking_confirmation_page',),
		'listeo_listings_page' => array('title' => 'My Listings', 'content' => '[listeo_my_listings]', 'option' => 'listeo_listings_page',),
		'listeo_wallet_page' => array('title' => 'Wallet', 'content' => '[listeo_wallet]', 'option' => 'listeo_wallet_page',),
		'listeo_reviews_page' => array('title' => 'Reviews', 'content' => '[listeo_reviews]', 'option' => 'listeo_reviews_page',),
		'listeo_bookmarks_page' => array('title' => 'Bookmarks', 'content' => '[listeo_bookmarks]', 'option' => 'listeo_bookmarks_page',),
		'listeo_submit_page' => array('title' => 'Add Listing', 'content' => '[listeo_submit_listing]', 'option' => 'listeo_submit_page',),
		'listeo_stats_page' => array('title' => 'Statistics', 'content' => '[listeo_stats_full]', 'option' => 'listeo_stats_page',),
		'listeo_profile_page' => array('title' => 'My profile', 'content' => '[listeo_my_account]', 'option' => 'listeo_profile_page',),
		'listeo_lost_password_page' => array('title' => 'Lost Password', 'content' => '[listeo_lost_password]', 'option' => 'listeo_lost_password_page',),
		'listeo_reset_password_page' => array('title' => 'Reset Password', 'content' => '[listeo_reset_password]', 'option' => 'listeo_reset_password_page',),
		'listeo_dashboard_page' => array('title' => 'Coupons', 'content' => '[listeo_coupons_page]', 'option' => 'listeo_dashboard_page',),
	);
}



add_action('template_redirect', 'custom_clear_cart_if_specific_product_type_and_leave_checkout');

function custom_clear_cart_if_specific_product_type_and_leave_checkout() {
	// Define the specific product type to check against
	// $specific_product_type = 'listing_booking'; // Replace 'YOUR_PRODUCT_TYPE' with the desired product type slug
	$specific_product_type = 'xyz';


	// Check if the cart contains the specific product type
	$contains_specific_product_type = false;
	foreach (WC()->cart->get_cart() as $cart_item) {

		if ($cart_item['data']->is_type($specific_product_type)) {
			$contains_specific_product_type = true;
			break;
		}
	}

	// Clear the cart if it contains the specific product type
	if ($contains_specific_product_type) {
		WC()->cart->empty_cart();
	}
}