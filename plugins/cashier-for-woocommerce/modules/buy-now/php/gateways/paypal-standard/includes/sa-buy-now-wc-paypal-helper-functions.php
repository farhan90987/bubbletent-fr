<?php
/**
 * WooCommerce Buy Now Helper Functions
 *
 * @package     WooCommerce Buy Now/Functions
 *  author      StoreApps
 * @version     1.1.0
 *
 * Credit: Prospress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Returns a string with all non-ASCII characters removed. This is useful for any string functions that expect only
 * ASCII chars and can't safely handle UTF-8
 *
 * Based on the SV_WC_Helper::str_to_ascii() method developed by the masterful SkyVerge team
 *
 * Note: We must do a strict false check on the iconv() output due to a bug in PHP/glibc {@link https://bugs.php.net/bug.php?id=63450}
 *
 * @param string $string string to make ASCII.
 * @return string|null ASCII string or null if error occurred
 * @since 2.0
 */
function sa_bn_wc_str_to_ascii( $string = '' ) {

	$ascii = false;

	if ( function_exists( 'iconv' ) ) {
		$ascii = iconv( 'UTF-8', 'ASCII//IGNORE', $string );
	}

	return false === $ascii ? preg_replace( '/[^a-zA-Z0-9_\-]/', '', $string ) : $ascii;
}

/**
 * Convert a date string into a timestamp without ever adding or deducting time.
 *
 * Function strtotime() would be handy for this purpose, but alas, if other code running on the server
 * is calling date_default_timezone_set() to change the timezone, strtotime() will assume the
 * date is in that timezone unless the timezone is specific on the string (which it isn't for
 * any MySQL formatted date) and attempt to convert it to UTC time by adding or deducting the
 * GMT/UTC offset for that timezone, so for example, when 3rd party code has set the servers
 * timezone using date_default_timezone_set( 'America/Los_Angeles' ) doing something like
 * gmdate( "Y-m-d H:i:s", strtotime( gmdate( "Y-m-d H:i:s" ) ) ) will actually add 7 hours to
 * the date even though it is a date in UTC timezone because the timezone wasn't specificed.
 *
 * This makes sure the date is never converted.
 *
 * @param string $date_string A date string formatted in MySQl or similar format that will map correctly when instantiating an instance of DateTime().
 * @return int Unix timestamp representation of the timestamp passed in without any changes for timezones
 */
function sa_bn_wc_date_to_time( $date_string = '' ) {

	if ( 0 === $date_string ) {
		return 0;
	}

	$date_time = new WC_DateTime( $date_string, new DateTimeZone( 'UTC' ) );

	return intval( $date_time->getTimestamp() );
}

/**
 * Function wp_json_encode exists since WP 4.1, but because we can't be sure that stores will actually use at least 4.1, we need
 * to have this wrapper.
 *
 * @param array $data Data to be encoded.
 *
 * @return string
 */
function sa_bn_wc_json_encode( $data = array() ) {
	if ( function_exists( 'wp_json_encode' ) ) {
		return wp_json_encode( $data );
	}
	return json_encode( $data ); // phpcs:ignore
}


/**
 * Returns a PayPal Subscription ID or Billing Agreement ID use to process payment for a given subscription or order.
 *
 * @param int $order The ID of a WC_Order or WC_Subscription object.
 * @since 2.0
 */
function sa_bn_wc_get_paypal_id( $order = object ) {

	if ( ! is_object( $order ) ) {
		$order = wc_get_order( $order );
	}

	return sa_bn_wc_get_objects_property( $order, '_paypal_subscription_id' );
}

/**
 * Stores a PayPal Standard Subscription ID or Billing Agreement ID in the post meta of a given order and the user meta of the order's user.
 *
 * @param int|object $order A WC_Order or WC_Subscription object or the ID of a WC_Order or WC_Subscription object.
 * @param string     $paypal_subscription_id A PayPal Standard Subscription ID or Express Checkout Billing Agreement ID.
 * @since 2.0
 */
function sa_bn_wc_set_paypal_id( $order = object, $paypal_subscription_id = '' ) {

	if ( ! is_object( $order ) ) {
		$order = wc_get_order( $order );
	}

	if ( sa_bn_wc_is_paypal_profile_a( $paypal_subscription_id, 'billing_agreement' ) ) {
		if ( ! in_array( $paypal_subscription_id, get_user_meta( $order->get_user_id(), '_paypal_subscription_id', false ), true ) ) {
			update_user_meta( $order->get_user_id(), '_paypal_subscription_id', $paypal_subscription_id );
		}
	}

	sa_bn_wc_set_objects_property( $order, 'paypal_subscription_id', $paypal_subscription_id );
}

/**
 * Checks an order to see if it contains a subscription.
 *
 * @param mixed        $order A WC_Order object or the ID of the order which the subscription was purchased in.
 * @param array|string $order_type Can include 'parent', 'renewal', 'resubscribe' and/or 'switch'. Defaults to 'parent', 'resubscribe' and 'switch' orders.
 * @return bool True if the order contains a subscription that belongs to any of the given order types, otherwise false.
 * @since 2.0
 */
function sa_bn_wc_order_contains_subscription( $order = object, $order_type = array( 'parent', 'resubscribe', 'switch' ) ) {

	// Accept either an array or string (to make it more convenient for singular types, like 'parent' or 'any').
	if ( ! is_array( $order_type ) ) {
		$order_type = array( $order_type );
	}

	if ( ! is_a( $order, 'WC_Abstract_Order' ) ) {
		$order = wc_get_order( $order );
	}

	$contains_subscription = false;
	$get_all               = in_array( 'any', $order_type, true );

	if ( ( in_array( 'parent', $order_type, true ) || $get_all ) && count( sa_bn_wc_get_subscriptions_for_order( sa_bn_wc_get_objects_property( $order, 'id' ), array( 'order_type' => 'parent' ) ) ) > 0 ) {
		$contains_subscription = true;

	} elseif ( ( in_array( 'renewal', $order_type, true ) || $get_all ) && sa_bn_wc_order_contains_renewal( $order ) ) {
		$contains_subscription = true;

	} elseif ( ( in_array( 'resubscribe', $order_type, true ) || $get_all ) && sa_bn_wc_order_contains_resubscribe( $order ) ) {
		$contains_subscription = true;

	} elseif ( ( in_array( 'switch', $order_type, true ) || $get_all ) && sa_bn_wc_order_contains_switch( $order ) ) {
		$contains_subscription = true;

	}

	return $contains_subscription;
}

/**
 * Check if a given order was created to resubscribe to a cancelled or expired subscription.
 *
 * @param WC_Order|int $order The WC_Order object or ID of a WC_Order order.
 * @since 2.0
 */
function sa_bn_wc_order_contains_resubscribe( $order = object ) {

	if ( ! is_a( $order, 'WC_Abstract_Order' ) ) {
		$order = wc_get_order( $order );
	}

	$related_subscriptions = wcs_get_subscriptions_for_resubscribe_order( $order );

	if ( sa_bn_wc_is_order( $order ) && ! empty( $related_subscriptions ) ) {
		$is_resubscribe_order = true;
	} else {
		$is_resubscribe_order = false;
	}

	return apply_filters( 'woocommerce_subscriptions_is_resubscribe_order', $is_resubscribe_order, $order );
}

/**
 * Check if a given order was to switch a subscription
 *
 * @param WC_Order|int $order The WC_Order object or ID of a WC_Order order.
 * @since 2.0
 */
function sa_bn_wc_order_contains_switch( $order = object ) {

	if ( ! is_a( $order, 'WC_Abstract_Order' ) ) {
		$order = wc_get_order( $order );
	}

	if ( ! sa_bn_wc_is_order( $order ) || sa_bn_wc_order_contains_renewal( $order ) ) {

		$is_switch_order = false;

	} else {

		$switched_subscriptions = wcs_get_subscriptions_for_switch_order( $order );

		if ( ! empty( $switched_subscriptions ) ) {
			$is_switch_order = true;
		} else {
			$is_switch_order = false;
		}
	}

	return apply_filters( 'woocommerce_subscriptions_is_switch_order', $is_switch_order, $order );
}

/**
 * Check whether an order is a standard order (i.e. not a refund or subscription) in version compatible way.
 *
 * WC 3.0 has the $order->get_type() API which returns 'shop_order', while WC < 3.0 provided the $order->order_type
 * property which returned 'simple', so we need to check for both.
 *
 * @param WC_Order $order order object.
 * @since  2.2.0
 * @return bool
 */
function sa_bn_wc_is_order( $order = object ) {

	if ( method_exists( $order, 'get_type' ) ) {
		$is_order = ( 'shop_order' === $order->get_type() );
	} else {
		$is_order = ( isset( $order->order_type ) && 'simple' === $order->order_type );
	}

	return $is_order;
}

/**
 * Get the subscription related to an order, if any.
 *
 * @param WC_Order|int $order An instance of a WC_Order object or the ID of an order.
 * @param array        $args A set of name value pairs to filter the returned value.
 *             'subscriptions_per_page' The number of subscriptions to return. Default set to -1 to return all.
 *             'offset' An optional number of subscription to displace or pass over. Default 0.
 *             'orderby' The field which the subscriptions should be ordered by. Can be 'start_date', 'trial_end_date', 'end_date', 'status' or 'order_id'. Defaults to 'start_date'.
 *             'order' The order of the values returned. Can be 'ASC' or 'DESC'. Defaults to 'DESC'
 *             'customer_id' The user ID of a customer on the site.
 *             'product_id' The post ID of a WC_Product_Subscription, WC_Product_Variable_Subscription or WC_Product_Subscription_Variation object
 *             'order_id' The post ID of a shop_order post/WC_Order object which was used to create the subscription
 *             'subscription_status' Any valid subscription status. Can be 'any', 'active', 'cancelled', 'suspended', 'expired', 'pending' or 'trash'. Defaults to 'any'.
 *             'order_type' Get subscriptions for the any order type in this array. Can include 'any', 'parent', 'renewal' or 'switch', defaults to parent.
 * @return array Subscription details in post_id => WC_Subscription form.
 * @since  2.0
 */
function sa_bn_wc_get_subscriptions_for_order( $order, $args = array() ) {

	$subscriptions = array();

	if ( ! is_a( $order, 'WC_Abstract_Order' ) ) {
		$order = wc_get_order( $order );
	}

	if ( ! is_a( $order, 'WC_Abstract_Order' ) ) {
		return $subscriptions;
	}

	$args = wp_parse_args(
		$args,
		array(
			'subscriptions_per_page' => -1,
			'order_type'             => array( 'parent', 'switch' ),
		)
	);

	// Accept either an array or string (to make it more convenient for singular types, like 'parent' or 'any').
	if ( ! is_array( $args['order_type'] ) ) {
		$args['order_type'] = array( $args['order_type'] );
	}

	$get_all = in_array( 'any', $args['order_type'], true );

	if ( $get_all || in_array( 'parent', $args['order_type'], true ) ) {

		$get_subscriptions_args = array_merge(
			$args,
			array(
				'order_id' => sa_bn_wc_get_objects_property( $order, 'id' ),
			)
		);

		$subscriptions = sa_bn_wc_get_subscriptions( $get_subscriptions_args );
	}

	$all_relation_types = WCS_Related_Order_Store::instance()->get_relation_types();
	$relation_types     = $get_all ? $all_relation_types : array_intersect( $all_relation_types, $args['order_type'] );

	foreach ( $relation_types as $relation_type ) {

		$subscription_ids = WCS_Related_Order_Store::instance()->get_related_subscription_ids( $order, $relation_type );

		foreach ( $subscription_ids as $subscription_id ) {
			if ( sa_bn_wc_is_subscription( $subscription_id ) ) {
				$subscriptions[ $subscription_id ] = sa_bn_wc_get_subscription( $subscription_id );
			}
		}
	}

	return $subscriptions;
}


/**
 * Check if a given order is a subscription renewal order.
 *
 * @param WC_Order|int $order The WC_Order object or ID of a WC_Order order.
 * @since 2.0
 */
function sa_bn_wc_order_contains_renewal( $order ) {

	if ( ! is_a( $order, 'WC_Abstract_Order' ) ) {
		$order = wc_get_order( $order );
	}

	$related_subscriptions = sa_bn_wc_get_subscriptions_for_renewal_order( $order );

	if ( sa_bn_wc_is_order( $order ) && ! empty( $related_subscriptions ) ) {
		$is_renewal = true;
	} else {
		$is_renewal = false;
	}

	return apply_filters( 'woocommerce_subscriptions_is_renewal_order', $is_renewal, $order );
}


/**
 * Get the subscription/s to which a resubscribe order relates.
 *
 * @param WC_Order|int $order The WC_Order object or ID of a WC_Order order.
 * @since 2.0
 */
function sa_bn_wc_get_subscriptions_for_renewal_order( $order ) {
	return sa_bn_wc_get_subscriptions_for_order( $order, array( 'order_type' => 'renewal' ) );
}
/**
 * Get all the orders that relate to a subscription in some form (rather than only the orders associated with
 * a specific subscription).
 *
 * @param string       $return_fields The columns to return, either 'all' or 'ids'.
 * @param array|string $order_type Can include 'any', 'parent', 'renewal', 'resubscribe' and/or 'switch'. Defaults to 'parent'.
 * @return array The orders that relate to a subscription, if any. Will contain either as just IDs or WC_Order objects depending on $return_fields value.
 * @since 2.1
 */
function sa_bn_wc_get_subscription_orders( $return_fields = 'ids', $order_type = 'parent' ) {
	global $wpdb;

	// Accept either an array or string (to make it more convenient for singular types, like 'parent' or 'any').
	if ( ! is_array( $order_type ) ) {
		$order_type = array( $order_type );
	}

	$any_order_type = in_array( 'any', $order_type, true );
	$return_fields  = ( 'ids' === $return_fields ) ? $return_fields : 'all';

	$orders    = array();
	$order_ids = array();

	if ( $any_order_type || in_array( 'parent', $order_type, true ) ) {
		$order_ids = array_merge(
			$order_ids, $wpdb->get_col( // phpcs:ignore
				"SELECT DISTINCT post_parent FROM {$wpdb->posts}
			 WHERE post_type = 'shop_subscription'
			 AND post_parent <> 0"
			)
		);
	}

	if ( $any_order_type || in_array( 'renewal', $order_type, true ) || in_array( 'resubscribe', $order_type, true ) || in_array( 'switch', $order_type, true ) ) {

		$meta_query = array(
			'relation' => 'OR',
		);

		if ( $any_order_type || in_array( 'renewal', $order_type, true ) ) {
			$meta_query[] = array(
				'key'     => '_subscription_renewal',
				'compare' => 'EXISTS',
			);
		}

		if ( $any_order_type || in_array( 'switch', $order_type, true ) ) {
			$meta_query[] = array(
				'key'     => '_subscription_switch',
				'compare' => 'EXISTS',
			);
		}

		// $any_order_type handled by 'parent' query above as all resubscribe orders are all parent orders.
		if ( in_array( 'resubscribe', $order_type, true ) && ! in_array( 'parent', $order_type, true ) ) {
			$meta_query[] = array(
				'key'     => '_subscription_resubscribe',
				'compare' => 'EXISTS',
			);
		}

		if ( count( $meta_query ) > 1 ) {
			$order_ids = array_merge(
				$order_ids,
				get_posts(
					array(
						'posts_per_page' => -1,
						'post_type'      => 'shop_order',
						'post_status'    => 'any',
						'fields'         => 'ids',
						'orderby'        => 'ID',
						'order'          => 'DESC',
						'meta_query'     => $meta_query, // phpcs:ignore
					)
				)
			);
		}
	}

	if ( 'all' === $return_fields ) {
		foreach ( $order_ids as $order_id ) {
			$orders[ $order_id ] = wc_get_order( $order_id );
		}
	} else {
		foreach ( $order_ids as $order_id ) {
			$orders[ $order_id ] = $order_id;
		}
	}

	return apply_filters( 'sa_bn_wc_get_subscription_orders', $orders, $return_fields, $order_type );
}


/**
 * Checks if a given profile ID is of a certain type.
 *
 * PayPal offers many different profile IDs that can be used for recurring payments, including:
 * - Express Checkout Billing Agreement IDs for Reference Transactios
 * - Express Checkout Recurring Payment profile IDs
 * - PayPal Standard Subscription IDs
 * - outdated PayPal Standard Subscription IDs (for accounts prior to 2009 that have not been upgraded).
 *
 * @param string $profile_id A PayPal Standard Subscription ID or Express Checkout Billing Agreement ID.
 * @param string $profile_type A type of profile ID, can be 'billing_agreement' or 'old_id'.
 * @since 2.0
 */
function sa_bn_wc_is_paypal_profile_a( $profile_id, $profile_type ) {

	if ( 'billing_agreement' === $profile_type && 'B-' === substr( $profile_id, 0, 2 ) ) {
		$is_a = true;
	} elseif ( 'out_of_date_id' === $profile_type && 'S-' === substr( $profile_id, 0, 2 ) ) {
		$is_a = true;
	} else {
		$is_a = false;
	}

	return apply_filters( 'woocommerce_subscriptions_is_paypal_profile_a_' . $profile_type, $is_a, $profile_id );
}

/**
 * Limit the length of item names to be within the allowed 127 character range.
 *
 * @param  string $item_name item name.
 * @return string
 * @since 2.0
 */
function sa_bn_wc_get_paypal_item_name( $item_name = '' ) {

	if ( strlen( $item_name ) > 127 ) {
		$item_name = substr( $item_name, 0, 124 ) . '...';
	}
	return html_entity_decode( $item_name, ENT_NOQUOTES, 'UTF-8' );
}

/**
 * Helper method to return the item description, which is composed of item
 * meta flattened into a comma-separated string, if available. Otherwise the
 * product SKU is included.
 *
 * The description is automatically truncated to the 127 char limit.
 *
 * @param array       $item cart or order item.
 * @param \WC_Product $product product data.
 * @return string
 * @since 2.0
 */
function sa_bn_wc_get_item_description( $item = array(), $product = object ) {

	if ( isset( WC()->cart ) && is_array( $item ) && empty( $item['item_meta'] ) ) {
		// cart item.
		$item_desc = WC()->cart->get_item_data( $item, true );

		$item_desc = str_replace( "\n", ', ', rtrim( $item_desc ) );

	} else {
		// order item.
		$item_desc = array();

		if ( is_callable( array( $item, 'get_formatted_meta_data' ) ) ) { // WC 3.0+.

			foreach ( $item->get_formatted_meta_data() as $meta ) {
				$item_desc[] = sprintf( '%s: %s', $meta->display_key, $meta->display_value );
			}
		} else { // WC < 3.0.

			$item_meta = new WC_Order_Item_Meta( $item );

			foreach ( $item_meta->get_formatted() as $meta ) {
				$item_desc[] = sprintf( '%s: %s', $meta['label'], $meta['value'] );
			}
		}

		if ( ! empty( $item_desc ) ) {

			$item_desc = implode( ', ', $item_desc );

		} else {

			// translators: placeholder is product SKU.
			$item_desc = is_callable( array( $product, 'get_sku' ) ) && $product->get_sku() ? sprintf( __( 'SKU: %s', 'cashier' ), $product->get_sku() ) : null;
		}
	}

	return sa_bn_wc_get_paypal_item_name( $item_desc );
}

/**
 * Takes a timestamp for a date in the future and calculates the number of days between now and then
 *
 * @param int $future_timestamp future timestamp.
 * @since 2.0
 */
function sa_bn_wc_calculate_paypal_trial_periods_until( $future_timestamp = 0 ) {

	$seconds_until_next_payment = $future_timestamp - gmdate( 'U' );
	$days_until_next_payment    = ceil( $seconds_until_next_payment / ( 60 * 60 * 24 ) );

	if ( $days_until_next_payment <= 90 ) { // Can't be more than 90 days free trial.

		$first_trial_length = $days_until_next_payment;
		$first_trial_period = 'D';

		$second_trial_length = 0;
		$second_trial_period = 'D';

	} else { // We need to use a second trial period.

		if ( $days_until_next_payment > 365 * 2 ) { // We need to use years because PayPal has a maximum of 24 months.

			$first_trial_length = floor( $days_until_next_payment / 365 );
			$first_trial_period = 'Y';

			$second_trial_length = $days_until_next_payment % 365;
			$second_trial_period = 'D';

		} elseif ( $days_until_next_payment > 365 ) { // Less than two years but more than one, use months.

			$first_trial_length = floor( $days_until_next_payment / 30 );
			$first_trial_period = 'M';

			$days_remaining = $days_until_next_payment % 30;

			if ( $days_remaining <= 90 ) { // We can use days.
				$second_trial_length = $days_remaining;
				$second_trial_period = 'D';
			} else { // We need to use weeks.
				$second_trial_length = floor( $days_remaining / 7 );
				$second_trial_period = 'W';
			}
		} else {  // We need to use weeks.

			$first_trial_length = floor( $days_until_next_payment / 7 );
			$first_trial_period = 'W';

			$second_trial_length = $days_until_next_payment % 7;
			$second_trial_period = 'D';

		}
	}

	return array(
		'first_trial_length'  => $first_trial_length,
		'first_trial_period'  => $first_trial_period,
		'second_trial_length' => $second_trial_length,
		'second_trial_period' => $second_trial_period,
	);
}

/**
 * Check if the $_SERVER global has PayPal WC-API endpoint URL slug in its 'REQUEST_URI' value
 *
 * In some cases, we need tdo be able to check if we're on the PayPal API page before $wp's query vars are setup,
 * like from WC_Subscriptions_Product::is_purchasable() and WC_Product_Subscription_Variation::is_purchasable(),
 * both of which are called within WC_Cart::get_cart_from_session(), which is run before query vars are setup.
 *
 * @since 2.0.13
 * @return bool
 **/
function sa_bn_wc_is_paypal_api_page() {
	$server_request_uri = ( ! empty( $_SERVER['REQUEST_URI'] ) ) ? wc_clean( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : ''; // phpcs:ignore
	return ( false !== strpos( $server_request_uri, 'wc-api/sa_bn_wc_paypal' ) ); //phpcs:ignore
}

/**
 * Access an object's property in a way that is compatible with CRUD and non-CRUD APIs for different versions of WooCommerce.
 *
 * We don't want to force the use of a custom legacy class for orders, similar to WC_Subscription_Legacy, because 3rd party
 * code may expect the object type to be WC_Order with strict type checks.
 *
 * A note on dates: in WC 3.0+, dates are returned a timestamps in the site's timezone :upside_down_face:. In WC < 3.0, they were
 * returned as MySQL strings in the site's timezone. We return them from here as MySQL strings in UTC timezone because that's how
 * dates are used in Subscriptions in almost all cases, for sanity's sake.
 *
 * @param WC_Order|WC_Product|WC_Subscription $object   The object whose property we want to access.
 * @param string                              $property The property name.
 * @param string                              $single   Whether to return just the first piece of meta data with the given property key, or all meta data.
 * @param mixed                               $default  (optional) The value to return if no value is found - defaults to single -> null, multiple -> array().
 *
 * @since  2.2.0
 * @return mixed
 */
function sa_bn_wc_get_objects_property( $object = object, $property = '', $single = 'single', $default = null ) {
	$value = ! is_null( $default ) ? $default : ( ( 'single' === $single ) ? null : array() );

	if ( ! is_object( $object ) ) {
		return $value;
	}

	$prefixed_key          = sa_bn_wc_maybe_prefix_key( $property );
	$property_function_map = array(
		'order_version'  => 'version',
		'order_currency' => 'currency',
		'order_date'     => 'date_created',
		'date'           => 'date_created',
		'cart_discount'  => 'total_discount',
	);

	if ( isset( $property_function_map[ $property ] ) ) {
		$property = $property_function_map[ $property ];
	}

	switch ( $property ) {
		case 'post':
			// In order to keep backwards compatibility it's required to use the parent data for variations.
			if ( method_exists( $object, 'is_type' ) && $object->is_type( 'variation' ) ) {
				$value = get_post( sa_bn_wc_get_objects_property( $object, 'parent_id' ) );
			} else {
				$value = get_post( sa_bn_wc_get_objects_property( $object, 'id' ) );
			}
			break;

		case 'post_status':
			$value = sa_bn_wc_get_objects_property( $object, 'post' )->post_status;
			break;

		case 'variation_data':
			$value = wc_get_product_variation_attributes( sa_bn_wc_get_objects_property( $object, 'id' ) );
			break;

		default:
			$function_name = 'get_' . $property;

			if ( is_callable( array( $object, $function_name ) ) ) {
				$value = $object->$function_name();
			} else {
				// If we don't have a method for this specific property, but we are using WC 3.0, it may be set as meta data on the object so check if we can use that.
				if ( $object->meta_exists( $prefixed_key ) ) {
					if ( 'single' === $single ) {
						$value = $object->get_meta( $prefixed_key, true );
					} else {
						// WC_Data::get_meta() returns an array of stdClass objects with id, key & value properties when meta is available.
						$value = wp_list_pluck( $object->get_meta( $prefixed_key, false ), 'value' );
					}
				} elseif ( 'single' === $single && isset( $object->$property ) ) { // WC < 3.0.
					$value = $object->$property;
				} elseif ( strtolower( $property ) !== 'id' && metadata_exists( 'post', sa_bn_wc_get_objects_property( $object, 'id' ), $prefixed_key ) ) {
					// If we couldn't find a property or function, fallback to using post meta as that's what many __get() methods in WC < 3.0 did.
					if ( 'single' === $single ) {
						$value = get_post_meta( sa_bn_wc_get_objects_property( $object, 'id' ), $prefixed_key, true );
					} else {
						// Get all the meta values.
						$value = get_post_meta( sa_bn_wc_get_objects_property( $object, 'id' ), $prefixed_key, false );
					}
				}
			}
			break;
	}

	return $value;
}

/**
 * Set an object's property in a way that is compatible with CRUD and non-CRUD APIs for different versions of WooCommerce.
 *
 * @param WC_Order|WC_Product|WC_Subscription $object The object whose property we want to access.
 * @param string                              $key The meta key name without '_' prefix.
 * @param mixed                               $value The data to set as the value of the meta.
 * @param string                              $save Whether to write the data to the database or not. Use 'save' to write to the database, anything else to only update it in memory.
 * @param int                                 $meta_id The meta ID of existing meta data if you wish to overwrite an existing piece of meta.
 * @param string                              $prefix_meta_key Whether the key should be prefixed with an '_' when stored in meta. Defaulted to 'prefix_meta_key', pass any other value to bypass automatic prefixing (optional).
 * @since  2.2.0
 * @return mixed
 */
function sa_bn_wc_set_objects_property( &$object, $key = '', $value = '', $save = 'save', $meta_id = '', $prefix_meta_key = 'prefix_meta_key' ) {

	$prefixed_key = sa_bn_wc_maybe_prefix_key( $key );

	// WC will automatically set/update these keys when a shipping/billing address attribute changes so we can ignore these keys.
	if ( in_array( $prefixed_key, array( '_shipping_address_index', '_billing_address_index' ), true ) ) {
		return;
	}

	// Special cases where properties with setters which don't map nicely to their function names.
	$meta_setters_map = array(
		'_cart_discount'         => 'set_discount_total',
		'_cart_discount_tax'     => 'set_discount_tax',
		'_customer_user'         => 'set_customer_id',
		'_order_tax'             => 'set_cart_tax',
		'_order_shipping'        => 'set_shipping_total',
		'_sale_price_dates_from' => 'set_date_on_sale_from',
		'_sale_price_dates_to'   => 'set_date_on_sale_to',
	);

	// If we have a 3.0 object with a predefined setter function, use it.
	if ( isset( $meta_setters_map[ $prefixed_key ] ) && is_callable( array( $object, $meta_setters_map[ $prefixed_key ] ) ) ) {
		$function = $meta_setters_map[ $prefixed_key ];
		$object->$function( $value );

		// If we have a 3.0 object, use the setter if available.
	} elseif ( is_callable( array( $object, 'set' . $prefixed_key ) ) ) {

		// Prices include tax is stored as a boolean in props but saved in the database as a string yes/no, so we need to normalise it here to make sure if we have a string (which can be passed to it by things like sa_bn_wc_copy_order_meta()) that it's converted to a boolean before being set.
		if ( '_prices_include_tax' === $prefixed_key && ! is_bool( $value ) ) {
			$value = 'yes' === $value;
		}

		$object->{ "set$prefixed_key" }( $value );

		// If there is a setter without the order prefix (eg set_order_total -> set_total).
	} elseif ( is_callable( array( $object, 'set' . str_replace( '_order', '', $prefixed_key ) ) ) ) {
		$function_name = 'set' . str_replace( '_order', '', $prefixed_key );
		$object->$function_name( $value );

		// If there is no setter, treat as meta within the 3.0.x object.
	} elseif ( is_callable( array( $object, 'update_meta_data' ) ) ) {
		$meta_key = ( 'prefix_meta_key' === $prefix_meta_key ) ? $prefixed_key : $key;
		$object->update_meta_data( $meta_key, $value, $meta_id );

		// 2.6.x handling for name which is not meta.
	} elseif ( 'name' === $key ) {
		$object->post->post_title = $value;

		// 2.6.x handling for everything else.
	} else {
		$object->$key = $value;
	}

	// Save the data.
	if ( 'save' === $save ) {
		if ( is_callable( array( $object, 'save' ) ) ) { // WC 3.0+.
			$object->save();
		} elseif ( 'date_created' === $key ) { // WC < 3.0+.
			wp_update_post(
				array(
					'ID'            => sa_bn_wc_get_objects_property( $object, 'id' ),
					'post_date'     => get_date_from_gmt( $value ),
					'post_date_gmt' => $value,
				)
			);
		} elseif ( 'name' === $key ) { // the replacement for post_title added in 3.0, need to update post_title not post meta.
			wp_update_post(
				array(
					'ID'         => sa_bn_wc_get_objects_property( $object, 'id' ),
					'post_title' => $value,
				)
			);
		} else {
			$meta_key = ( 'prefix_meta_key' === $prefix_meta_key ) ? $prefixed_key : $key;

			if ( ! empty( $meta_id ) ) {
				update_metadata_by_mid( 'post', $meta_id, $value, $meta_key );
			} else {
				update_post_meta( sa_bn_wc_get_objects_property( $object, 'id' ), $meta_key, $value );
			}
		}
	}
}

/**
 * Add a prefix to a string if it doesn't already have it
 *
 * @param string $key existing key.
 * @param string $prefix prefixed used in key.
 * @since 2.2.0
 * @return string
 */
function sa_bn_wc_maybe_prefix_key( $key = '', $prefix = '_' ) {
	return ( substr( $key, 0, strlen( $prefix ) ) !== $prefix ) ? $prefix . $key : $key;
}

/**
 * Remove a prefix from a string if has it
 *
 * @param string $key existing key.
 * @param string $prefix prefiexed used in key.
 * @since 2.2.0
 * @return string
 */
function sa_bn_wc_maybe_unprefix_key( $key = '', $prefix = '_' ) {
	return ( substr( $key, 0, strlen( $prefix ) ) === $prefix ) ? substr( $key, strlen( $prefix ) ) : $key;
}

/**
 * Return an array statuses used to describe when a subscriptions has been marked as ending or has ended.
 *
 * @return array
 * @since 2.0
 */
function sa_bn_wc_get_subscription_ended_statuses() {
	return apply_filters( 'wcs_subscription_ended_statuses', array( 'cancelled', 'trash', 'expired', 'switched', 'pending-cancel' ) );
}
