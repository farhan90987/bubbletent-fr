<?php
/* Helper functions */
function wpwoof_get_all_attributes() {
	global $woocommerce_wpwoof_common;
	$attributes       = array();
	$taxonomy_objects = get_object_taxonomies( 'product', 'objects' );
	foreach ( $taxonomy_objects as $taxonomy_key => $taxonomy_object ) {
		$cat = substr( $taxonomy_key, 0, 3 ) === "pa_" ? 'pa' : 'global';
		if ( $taxonomy_key == 'product_type' ) {
			$attributes[ $cat ][ $taxonomy_key ] = 'Product Type (' . $taxonomy_key . ')';
		} else {
			$attributes[ $cat ][ $taxonomy_key ] = $taxonomy_object->label . ' (' . $taxonomy_key . ')';
		}
	}
	$attributes['meta']   = array_merge( wpwoof_get_custom_fields(), get_products_meta_keys() );
	$integratedMetaFields = $woocommerce_wpwoof_common->getIntegratedMetaFields();
	if ( ! empty( $integratedMetaFields ) ) {
		$attributes['integrated'] = $integratedMetaFields;
	}

	return $attributes;
}

function wpwoof_get_custom_fields() {
	global $wpdb, $woocommerce_wpwoof_common;
	$cache = get_transient( 'wpwoof_custom_fields' );
	if ( $cache ) {
		return $cache;
	}

	$fields = array();
	// get Global Custom value fields
	$gd = $woocommerce_wpwoof_common->getGlobalData();
	if ( isset( $gd['extra'] ) && count( $gd['extra'] ) ) {
		foreach ( $gd['extra'] as $extraValue ) {
			if ( isset( $extraValue['feed_type'] ) && isset( $extraValue['feed_type']['mapping'] ) && isset( $extraValue['custom_tag_name'] ) ) {
				$fields[] = $extraValue['custom_tag_name'];
			}
		}
	}
	// get product Custom value fields
	$query  = "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key='wpwoofextra' and meta_value LIKE '%\"mapping\";s:2:\"on\";%'";
	$result = $wpdb->get_results( $query, 'ARRAY_A' );
	if ( count( $result ) ) {
		foreach ( $result as $value ) {
			if ( isset( $value['meta_value'] ) && ! empty( $value['meta_value'] ) ) {
				$meta_value = unserialize( $value['meta_value'] );
				if ( is_array( $meta_value ) && count( $meta_value ) ) {
					foreach ( $meta_value as $extraValue ) {
						if ( isset( $extraValue['feed_type'] ) && isset( $extraValue['feed_type']['mapping'] ) && isset( $extraValue['custom_tag_name'] ) ) {
							$fields[] = $extraValue['custom_tag_name'];
						}
					}
				}
			}

		}
	}
	set_transient( 'wpwoof_custom_fields', $fields );

	return $fields;

}

function generate_products_meta_keys() {
	global $wpdb;
	$post_type = 'product';
	$query     = "
        SELECT DISTINCT($wpdb->postmeta.meta_key) 
        FROM $wpdb->posts 
        LEFT JOIN $wpdb->postmeta 
        ON $wpdb->posts.ID = $wpdb->postmeta.post_id 
        WHERE $wpdb->posts.post_type = '%s' 
        AND $wpdb->postmeta.meta_key != '' 
        AND $wpdb->postmeta.meta_key NOT RegExp '(^[_0-9].+$)' 
        AND $wpdb->postmeta.meta_key NOT RegExp '(^[0-9]+$)'
    ";
	$meta_keys = $wpdb->get_col( $wpdb->prepare( $query, $post_type ) );
	set_transient( 'products_meta_keys', $meta_keys, 60 * 60 * 24 ); # create 1 Day Expiration

	return $meta_keys;
}

function get_products_meta_keys() {
	$cache     = get_transient( 'products_meta_keys' );
	$meta_keys = $cache ? $cache : generate_products_meta_keys();

	return $meta_keys;
}

function wpwoof_get_product_fields_sort() {
	$sort = array(
		'ids' => array(
			'id',
			'_sku'
		),
	);
	$name = array(
		'ids' => 'ID\'s',
	);

	return array( 'sort' => $sort, 'name' => $name );
}

function wpwoof_get_product_fields() {

	$all_fields         = array();
	$all_fields['id']   = array(
		'label'        => __( 'ID', 'woocommerce_wpwoof' ),
		'desc'         => '',
		'value'        => false,
		'required'     => false,
		'feed_type'    => array( 'facebook', 'all', 'google', 'pinterest', 'adsensecustom', 'tiktok' ),
		'facebook_len' => false,
		'text'         => true,
	);
	$all_fields['_sku'] = array(
		'label'        => __( 'SKU', 'woocommerce_wpwoof' ),
		'desc'         => '',
		'value'        => false,
		'required'     => false,
		'feed_type'    => array( 'facebook', 'all', 'google', 'pinterest', 'adsensecustom', 'tiktok' ),
		'facebook_len' => false,
		'text'         => true,
	);

	return $all_fields;
}

function wpwoof_get_all_fields( $product = false ) {
	global $woocommerce_wpwoof_common;

	$all_fields  = $woocommerce_wpwoof_common->product_fields;
	$field_group = array();
	$post_type   = false;
	if ( $product ) {
		$post_type = $product->get_type();
	}

	foreach ( $all_fields as $key => $value ) {

		if ( ! empty( $value['product_type_exclude'] ) && in_array( $post_type, $value['product_type_exclude'] ) ) {
			continue;
		}
		if ( isset( $value['type'] ) && is_array( $value['type'] ) ) {
			foreach ( $value['type'] as $valueType ) {
				$tpKey = ! empty( $valueType ) ? $valueType : 'extra';
				if ( ! isset( $field_group[ $tpKey ] ) ) {
					$field_group[ $tpKey ] = array();
				}
				$field_group[ $tpKey ][ $key ] = $value;
			}
		} else {
			$tpKey = ! empty( $value['type'] ) ? $value['type'] : 'extra';
			if ( ! isset( $field_group[ $tpKey ] ) ) {
				$field_group[ $tpKey ] = array();
			}
			$field_group[ $tpKey ][ $key ] = $value;
		}
	}

	return $field_group;
}

/**
 * Cleans up debug log files in the wpwoof-feed directory.
 *
 * This function deletes all .log files located in the wpwoof-feed directory
 * within the WordPress uploads directory.
 *
 * @return void
 */
function wpwoof_cleanup_debug_files() {
	$upload_dir = wp_upload_dir();
	$path       = $upload_dir['basedir'] . "/wpwoof-feed/";
	$files      = glob( $path . "*.log" );
	foreach ( $files as $file ) {
		unlink( $file );
	}

}

