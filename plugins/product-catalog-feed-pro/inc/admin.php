<?php
require_once( "common.php" );
global $woocommerce_wpwoof_common;


function wpwoof_delete_feed( $id ) {
	global $wpdb, $woocommerce_wpwoof_common;
	$id          = (int) $id;
	$feed_config = wpwoof_get_feed( $id );
	if ( is_wp_error( $feed_config ) ) {
		return $feed_config;
	}
	$woocommerce_wpwoof_common->remove_feed_gen_schedule( $id );
	wpwoof_delete_feed_file( $id );

	if ( ! empty( $feed_config['main_feed_id'] ) ) {
		wpwoof_change_list_localized_feeds( 'delete', $feed_config );
	} elseif ( ! empty( $feed_config['localized_feeds_ids'] ) ) {
		foreach ( $feed_config['localized_feeds_ids'] as $feed_id ) {
			wpwoof_delete_feed( $feed_id );
		}
	}

	return $wpdb->query( "DELETE FROM " . $wpdb->prefix . "options WHERE option_id='" . (int) $id . "' AND option_name LIKE 'wpwoof_feedlist_%'	" );
}

function wpwoof_update_feed( $option_value, $option_id, $flag = false, $feed_name = '' ) {
	global $wpdb;
	//wpwoof_delete_feed_file($id);
	if ( ! $flag ) {
		if ( empty( $option_value['status_feed'] ) ) {
			$option_value['status_feed'] = "";
		}
		$tmpdata = wpwoof_get_feed( $option_id );
		if ( is_wp_error( $tmpdata ) ) {
			return false;
		}

		if ( ! empty( $tmpdata['status_feed'] ) ) {
			$option_value['status_feed'] = $tmpdata['status_feed'];
		}
	}

	$option_value = serialize( $option_value );
	$table        = "{$wpdb->prefix}options";
	$data         = array( 'option_value' => $option_value );
	if ( $feed_name ) {
		$data['option_name'] = 'wpwoof_feedlist_' . $feed_name;
	}

	$sSet  = " option_value=%s" . ( isset( $data['option_name'] ) ? ", option_name=%s" : "" );
	$aData = array();
	array_push( $aData, $option_value );
	if ( isset( $data['option_name'] ) ) {
		array_push( $aData, $data['option_name'] );
	}
	array_push( $aData, $option_id );
	array_push( $aData, 'wpwoof_feedlist_%' );

	return $wpdb->query( $wpdb->prepare( " update " . $table . " SET  " . $sSet . " WHERE option_id=%d AND option_name LIKE %s ", $aData ) );

}

function wpwoof_update_feed_partially( array $values, int $option_id ) {

	$feed_old_data = wpwoof_get_feed( $option_id );
	if ( is_wp_error( $feed_old_data ) ) {
		return false;
	}

	$protected_keys = array( 'feed_name', 'edit_feed' );
	foreach ( $protected_keys as $key ) {
		if ( array_key_exists( $key, $values ) ) {
			unset( $values[ $key ] );
		}
	}

	if ( empty( $values ) ) {
		return false;
	}

	$feed_data = array_merge( $feed_old_data, $values );

	return wpwoof_update_feed( $feed_data, $option_id );
}


function wpwoof_get_feeds( $search = "" ) {

	global $wpdb;
	$option_name = "wpwoof_feedlist_";
	if ( $search != '' ) {
		$option_name = $search;
	}

	$query  = $wpdb->prepare( "SELECT * FROM $wpdb->options WHERE option_name LIKE %s;", "%" . $option_name . "%" );
	$result = $wpdb->get_results( $query, 'ARRAY_A' );

	return $result;

}

function wpwoof_get_feed( $option_id ) {
	global $wpdb;

	$query  = $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_id='%s' AND option_name LIKE 'wpwoof_feedlist_%%'", $option_id );
	$result = $wpdb->get_var( $query );
	if ( empty( $result ) ) {
		return new WP_Error( 'wpwoof_feed_not_found', 'Feed not found. ID: ' . $option_id );
	}
	$result              = unserialize( $result );
	$result['edit_feed'] = $option_id;
	if ( ! isset( $result['feed_name'] ) ) {
		return new WP_Error( 'wpwoof_feed_dont_have_name', 'Feed don\'t have name. ID: ' . $option_id );
	}
	if ( empty( $result['feed_type'] ) ) {
		$result['feed_type'] = 'facebook';
	}

	if ( $result ) {
		return $result;
	}
}

function wpwoof_feed_dir( $file_name, $file_type = 'xml' ) {
	$global_settings = wpwoof_product_catalog::$WWC->getGlobalData();
	$upload_dir      = wp_upload_dir();
	$base            = $upload_dir['basedir'];
	$baseurl         = $upload_dir['baseurl'];

	$path    = $base . "/wpwoof-feed/" . $file_type;
	$baseurl = $baseurl . "/wpwoof-feed/" . $file_type;
	$file    = $path . "/" . $file_name . "." . $file_type;
	$fileurl = $baseurl . "/" . $file_name . "." . $file_type;

	return array(
		'path'       => $file,
		'url'        => ! empty( $global_settings['add_no_cache_to_url'] ) ? add_query_arg( array( 'cf-no-cache' => 1 ), $fileurl ) : $fileurl,
		'file'       => $file_name . '.' . $file_type,
		'pathtofile' => $path
	);
}

/**
 * Creates a new feed based on the provided data and updates necessary options in the database.
 *
 * @param array $data An associative array containing the feed data.
 *
 * @return void
 */
function wpwoof_create_feed( $data ) {
	global $wpdb;
	//trace($data,1);
	if ( ! isset( $data['feed_type'] ) ) {
		exit();
	}
	$feedname   = sanitize_text_field( $data['feed_name'] );
	$file_name  = isset( $data['feed_file_name'] ) ? sanitize_text_field( $data['feed_file_name'] ) : strtolower( str_replace( ' ', '-', trim( $feedname ) ) );
	$upload_dir = wpwoof_feed_dir( $file_name, $data['feed_type'] == "adsensecustom" ? "csv" : "xml" );
	$file       = $upload_dir['path'];
	$file_name  = $upload_dir['file'];

	if ( update_option( 'wpwoof_feedlist_' . $feedname, $data ) ) {
		$row = $wpdb->get_row( "SELECT * FROM " . $wpdb->options . " WHERE option_name = 'wpwoof_feedlist_" . $feedname . "'", ARRAY_A );
		if ( empty( $row['option_id'] ) ) {
			$r = $wpdb->get_row( "SELECT MAX(option_id)+1 as id from " . $wpdb->options, ARRAY_A );
			$wpdb->query( "update " . $wpdb->options . " SET option_id='" . $r['id'] . "' where option_name = 'wpwoof_feedlist_" . $feedname . "'" );
			if ( ! isset( $data['edit_feed'] ) ) {
				$data['edit_feed'] = $r['id'];
			}
		} elseif ( ! isset( $data['edit_feed'] ) ) {
			$data['edit_feed'] = $row['option_id'];
		}
		wpwoof_change_list_localized_feeds( 'add', $data );
	}

	$dir_path = str_replace( $file_name, '', $file );
	wpwoof_checkDir( $dir_path );

	$global_settings = wpwoof_product_catalog::$WWC->getGlobalData();
	if ( $global_settings['on_save_feed_action'] == 'save' ) {
		wpwoof_product_catalog::schedule_feed( $data );
		if ( ! empty( $data['localized_feeds_ids'] ) ) {
			foreach ( $data['localized_feeds_ids'] as $feed_id ) {
				$localized_feed = wpwoof_get_feed( $feed_id );
				if ( is_wp_error( $localized_feed ) ) {
					continue;
				}
				wpwoof_product_catalog::schedule_feed( $localized_feed );
			}
		}
	} elseif ( $global_settings['on_save_feed_action'] == 'save_and_regenerate_main' ) {
		wpwoof_product_catalog::schedule_feed( $data, time() );
		if ( ! empty( $data['localized_feeds_ids'] ) ) {
			foreach ( $data['localized_feeds_ids'] as $feed_id ) {
				$localized_feed = wpwoof_get_feed( $feed_id );
				if ( is_wp_error( $localized_feed ) ) {
					continue;
				}
				wpwoof_product_catalog::schedule_feed( $localized_feed );
			}
		}
	} else {
		wpwoof_product_catalog::schedule_feed( $data, time() );
		if ( ! empty( $data['localized_feeds_ids'] ) ) {
			foreach ( $data['localized_feeds_ids'] as $feed_id ) {
				$localized_feed = wpwoof_get_feed( $feed_id );
				if ( is_wp_error( $localized_feed ) ) {
					continue;
				}
				wpwoof_product_catalog::schedule_feed( $localized_feed, time() );
			}
		}
	}

}

/**
 * Manages the list of localized feeds for a given main feed based on the specified action.
 *
 * @param string $action The action to perform. Either 'add' or 'delete'.
 * @param mixed $feed_config The configuration of the feed or the ID of the feed.
 *
 * @return bool True on success, false on failure.
 */
function wpwoof_change_list_localized_feeds( $action, $feed_config ) {
	if ( is_int( $feed_config ) ) {
		$feed_config = wpwoof_get_feed( $feed_config );
		if ( is_wp_error( $feed_config ) ) {
			return false;
		}
	}
	if ( ! empty( $feed_config['main_feed_id'] ) ) {
		$main_feed = wpwoof_get_feed( $feed_config['main_feed_id'] );
		if ( is_wp_error( $main_feed ) ) {
			return false;
		}
		if ( $action == 'add' ) {
			if ( empty( $main_feed['localized_feeds_ids'] ) ) {
				$main_feed['localized_feeds_ids'] = array( $feed_config['edit_feed'] );
			} elseif ( ! in_array( $feed_config['edit_feed'], $main_feed['localized_feeds_ids'] ) ) {
				$main_feed['localized_feeds_ids'][] = $feed_config['edit_feed'];
			} else {
				return false;
			}

		} elseif ( $action == 'delete' ) {
			if ( is_array( $main_feed['localized_feeds_ids'] ) && in_array( $feed_config['edit_feed'], $main_feed['localized_feeds_ids'] ) ) {
				$main_feed['localized_feeds_ids'] = array_diff( $main_feed['localized_feeds_ids'], array( $feed_config['edit_feed'] ) );
			} else {
				return false;
			}

		} else {
			return false;
		}
		wpwoof_update_feed( $main_feed, $main_feed['edit_feed'] );

		return true;
	}

	return false;
}

function wpwoof_checkDir( $path ) {
	if ( ! file_exists( $path ) ) {
		return wp_mkdir_p( $path );
	}

	return true;
}

function wpwoof_delete_feed_file( $id ) {
	$option_id     = $id;
	$wpwoof_values = wpwoof_get_feed( $option_id );
	if ( is_wp_error( $wpwoof_values ) ) {
		return false;
	}
	$file_name  = isset( $wpwoof_values['feed_file_name'] ) ? sanitize_text_field( $wpwoof_values['feed_file_name'] ) : strtolower( str_replace( ' ', '-', trim( $wpwoof_values['feed_name'] ) ) );
	$upload_dir = wpwoof_feed_dir( $file_name );
	$file       = $upload_dir['path'];

	if ( file_exists( $file ) ) {
		unlink( $file );
	}
}

function wpwoof_refresh( $message = '' ) {
	$settings_page = $_SERVER['REQUEST_URI'];
	if ( strpos( $settings_page, '&' ) !== false ) {
		$settings_page = substr( $settings_page, 0, strpos( $settings_page, '&' ) );
	}
	if ( ! empty( $message ) ) {
		$settings_page .= '&show_msg=true&wpwoof_message=' . $message;
	}
	if ( ! WPWOOF_DEBUG ) {
		header( "Location:" . $settings_page );
	}
}


add_action( 'wp_ajax_wpwoofgtaxonmy', 'ajax_wpwoofgtaxonmy' );
function ajax_wpwoofgtaxonmy() {
	$taxonomyPath = ( isset( $_POST['taxonomy'] ) ? $_POST['taxonomy'] : null );
	wp_send_json( wpwoof_getTaxonmyByPath( $taxonomyPath ) );
}

function wpwoof_getTaxonmyByPath( $taxonomyPath ) {
	$categories = LazyTaxonomyReader();
	$lvl        = 1;
	$result[0]  = $categories['values'];
	$tmpCatLvl  = $categories;
	foreach ( explode( ' > ', $taxonomyPath ) as $value ) {
		if ( isset( $tmpCatLvl[ $value ] ) ) {
			$result[ $lvl ++ ] = $tmpCatLvl[ $value ]["values"];
			$tmpCatLvl         = $tmpCatLvl[ $value ];
		} else {
			break;
		}
	}

	return $result;
}


function LazyTaxonomyReader() {
	$categories = array();
	$upload_dir = wp_upload_dir();
	$upload_dir['basedir'];

	if ( file_exists( $upload_dir['basedir'] . "/wpwoof-feed/google-taxonomy.en.txt" ) ) {
		$file = $upload_dir['basedir'] . "/wpwoof-feed/google-taxonomy.en.txt";
	} else {
		$file = plugin_dir_path( __FILE__ ) . 'google-taxonomy.en.txt';
	}

	$lines = file( $file, FILE_IGNORE_NEW_LINES );
	// remove first line that has version number
	if ( substr( $lines[0], 0, 1 ) == '#' ) {
		unset( $lines[0] );
	}
	$categories['values'] = array();
	$tcat[0]              = $categories;
	foreach ( $lines as $line ) {
		$tarr = explode( ' > ', $line );
		if ( count( $tarr ) > 1 ) {
			$val = end( $tarr );
			unset( $tarr[ count( $tarr ) - 1 ] );
			$arpath = '["' . implode( '"]["', $tarr ) . '"]';
			eval( "\$categories" . $arpath . '["values"][]="' . $val . '";' );
		} else {
			$categories["values"][] = $tarr[0];
		}
	}

	return $categories;
}

add_action( 'wp_ajax_wpwoofcategories', 'ajax_wpwoofcategories' );
function ajax_wpwoofcategories() {
	wpwoofcategories( $_POST );
	die();
}


function wpwoofcategories_wmpl( $options ) {
	global $sitepress, $wp_version;
	$general_lang = ICL_LANGUAGE_CODE;
	$options      = array_merge( array(), $options );
	$aLanguages   = icl_get_languages( 'skip_missing=N&orderby=KEY&order=DIR&link_empty_to=str' );
	$sell_all     = ! isset( $options['feed_name'] ) && ! isset( $options['feed_category_all'] ) || isset( $options['feed_category_all'] ) && $options['feed_category_all'] == "-1";
	// trace("options['feed_category_all']:".$options['feed_category_all']);
	// trace("sell_all:".$sell_all);

	?><p><b>Please select categories</b></p>
    <p class="description">You can also select multiple categories</p>
    <ul id="lang_wpwoof_categories">
        <li>Include Exclude</li>
        <li><input type="checkbox" value="-1" name="feed_category_all" id="feed_category_all"
                   class="feed_category" <?php checked( $sell_all )
			?>><input type="checkbox" disabled="disabled">
            <label for="feed_category_all">All Categories</label>
        </li>
		<?php
		$array_terms = array();
		foreach ( $aLanguages as $lang ) {
			//   $lang['language_code']; $lang['translated_name'];
			$terms = null;
			$sitepress->switch_lang( $lang['language_code'] );
			if ( version_compare( floatval( $wp_version ), '4.5', '>=' ) ) {
				$args  = array(
					'taxonomy'   => array( 'product_cat' ),
					'hide_empty' => false,
					'orderby'    => 'name',
					'order'      => 'ASC'
				);
				$terms = get_terms( $args );
			} else {
				$terms = get_terms( 'product_cat', 'orderby=name&order=ASC&hide_empty=0' );
			}

			if ( empty( $options['feed_category'] ) ) {
				$options['feed_category']     = array();
				$options['feed_category_all'] = '-1';
				foreach ( $terms as $key => $term ) {
					if ( get_term_meta( $term->term_id, 'wpfoof-exclude-category', true ) != "on" ) {
						$options['feed_category'][] = $term->term_id;
					} else {
						unset( $terms[ $key ] );
					}
				}

			}

			echo "<li class='language_" . $lang['language_code'] . " language_all'><b><i>" . $lang['translated_name'] . "</i></b></li>";
			$categories_excluded = $options['feed_category_excluded'] ?? array();
			$categories_included = $options['feed_category'] ?? array();
			foreach ( $terms as $term ) {
				?>
                <li class="language_<?php echo $lang['language_code'] ?> language_all">
                    <input type="checkbox" value="<?php echo $term->term_id; ?>" name="feed_category[]"
                           id="feed_category_<?php echo $term->term_id; ?>"
                           class="feed_category"
						<?php checked( $sell_all || in_array( $term->term_id, $categories_included ) ); ?>>
                    <input type="checkbox" value="<?php echo $term->term_id; ?>" name="feed_category_excluded[]"
                           id="feed_category_excluded_<?php echo $term->term_id; ?>"
                           class="feed_category_excluded"
						<?php checked( in_array( $term->term_id, $categories_excluded ) ); ?>>
                    <label for="<?php echo 'feed_category_' . $term->term_id; ?>"><?php echo $term->name; ?> &nbsp;
                        &nbsp;
                        (<?php echo $term->count; ?>)</label>
                </li>
				<?php
			} ?>
			<?php
		}//foreach($aLanguages as $lang) {

		?>
    </ul>
    <br>
    <div id="wpwoof-popup-bottom"><a href="#done" class="button button-secondary wpwoof-popup-done">Done</a></div>
	<?php
	$sitepress->switch_lang( $general_lang );
}

function wpwoofcategories( $options = array() ) {
	if ( WoocommerceWpwoofCommon::isActivatedWPML() ) {
		wpwoofcategories_wmpl( $options );

		return;
	}
	global $wp_version;
	$options = array_merge( array(), $options );
	?>
    <p><b>Please select categories</b></p>
	<?php
	$terms = null;
	if ( version_compare( floatval( $wp_version ), '4.5', '>=' ) ) {
		$args = array(
			'taxonomy'   => array( 'product_cat' ),
			'hide_empty' => false,
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key'     => 'wpfoof-exclude-category',
					'value'   => 'on',
					'compare' => 'NOT LIKE'
				),
				array(
					'key'     => 'wpfoof-exclude-category',
					'compare' => 'NOT EXISTS' // doesn't work
				)
			),
			'orderby'    => 'name',
			'order'      => 'ASC'
		);


		$terms = get_terms( $args );
	} else {
		$terms = get_terms( 'product_cat', 'orderby=name&order=ASC&hide_empty=0' );
	}


	if ( empty( $options['feed_category'] ) ) {
		$options['feed_category']     = array();
		$options['feed_category_all'] = '-1';
		$options['feed_category'][]   = '0';
		foreach ( $terms as $key => $term ) {
			if ( get_term_meta( $term->term_id, 'wpfoof-exclude-category', true ) != "on" ) {
				$options['feed_category'][] = $term->term_id;
			} else {
				unset( $terms[ $key ] );
			}
		}

	}
	$sell_all = ! isset( $options['feed_name'] ) && ! isset( $options['feed_category_all'] ) || isset( $options['feed_category_all'] ) && $options['feed_category_all'] == "-1";
	?>
    <p class="description">You can also select multiple categories</p>
    <ul>
        <li>Include Exclude</li>
        <li><input type="checkbox" value="-1" name="feed_category_all" id="feed_category_all"
                   class="feed_category" <?php checked( $sell_all ); ?>>
            <input type="checkbox" disabled="disabled">
            <label for="feed_category_all">All Categories</label></li>
		<?php
		$categories_excluded = $options['feed_category_excluded'] ?? array();
		$categories_included = $options['feed_category'] ?? array();
		foreach ( $terms as $term ) { ?>
            <li><input type="checkbox" value="<?php echo $term->term_id; ?>" name="feed_category[]"
                       id="feed_category_<?php echo $term->term_id; ?>"
                       class="feed_category" <?php checked( $sell_all || in_array( $term->term_id, $categories_included ) ); ?>>
                <input type="checkbox" value="<?php echo $term->term_id; ?>" name="feed_category_excluded[]"
                       id="feed_category_excluded_<?php echo $term->term_id; ?>"
                       class="feed_category_excluded" <?php checked( in_array( $term->term_id, $categories_excluded ) ); ?>>
                <label
                        for="<?php echo 'feed_category_' . $term->term_id; ?>"><?php echo $term->name; ?> &nbsp; &nbsp;
                    (<?php echo $term->count; ?>)</label></li>
		<?php } ?>
    </ul>
    <br>
    <div id="wpwoof-popup-bottom"><a href="#done" class="button button-secondary wpwoof-popup-done">Done</a></div>

	<?php
}

function wpwoof_create_csv( $path, $file, $content, $columns, $info = array() ) {
	$info = array_merge( array( 'delimiter' => 'tab', 'enclosure' => 'double' ), $info );
	if ( wpwoof_checkDir( $path ) ) {
		$fp        = fopen( $file, "w" );
		$delimiter = $info['delimiter'];
		if ( $delimiter == 'tab' ) {
			$delimiter = "\t";
		}
		$enclosure = $info['enclosure'];
		if ( $enclosure == "double" ) {
			$enclosure = chr( 34 );
		} else if ( $enclosure == "single" ) {
			$enclosure = chr( 39 );
		} else {
			$enclosure = '"';
		}
		if ( ! empty( $columns ) ) {
			$header = array();
			foreach ( $columns as $column_name => $value ) {
				$header[] = $column_name;
			}
			fputcsv( $fp, $header, $delimiter, $enclosure );
		}
		if ( ! empty( $content ) ) {
			foreach ( $content as $fields ) {
				if ( count( $fields ) != count( $columns ) ) {
					continue;
				}
				fputcsv( $fp, $fields, $delimiter, $enclosure );
			}
		}
		fclose( $fp );

		return true;
	} else {
		return false;
	}
}

if ( strpos( $_SERVER['REQUEST_URI'], '/edit.php' ) !== false && isset( $_GET['post_type'] ) && $_GET['post_type'] == 'product' ) {
	add_filter( 'manage_edit-product_columns', 'wpwoof_products_columns', 20 );
	function wpwoof_products_columns( $columns_array ) {

		$id = array_search( 'product_tag', array_keys( $columns_array ) ) ?: 5;
		$id ++;

		return array_slice( $columns_array, 0, $id, true )
		       + array( 'feed' => 'Feed' )
		       + array_slice( $columns_array, $id, null, true );


	}

	add_action( 'manage_posts_custom_column', 'wpwoof_products_populate_columns' );
	function wpwoof_products_populate_columns( $column_name ) {
		global $wpdb, $product;
		if ( $column_name == 'feed' ) {
			$product_type = version_compare( WC_VERSION, '3.0', '>=' ) ? $product->get_type() : $product->product_type;
			$_var         = get_post_meta( get_the_ID(), 'wpfoof-exclude-product', true );
			if ( ! in_array( $product_type, array( 'variable', 'variable-subscription' ) ) || $_var ) {
				echo $_var ? 'No' : 'Yes';
			} else {
				$variations = $product->get_children();
				if ( ! empty( $variations ) ) {
					$query = "SELECT DISTINCT post_id 
                                FROM $wpdb->postmeta
                                WHERE `meta_key` LIKE 'wpfoof-exclude-product' 
                                AND `meta_value` != '0' 
                                AND post_id IN (" . implode( ", ", $variations ) . ")";

					$product_qty = $wpdb->query( $query );
					if ( $product_qty == count( $variations ) ) {
						echo 'No';
					} else {
						echo 'Yes';
					}

					return true;
				}
				echo 'No';

			}

		}

	}
}

if ( strpos( $_SERVER['REQUEST_URI'], '/edit-tags.php' ) !== false && isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] == 'product_cat' ) {
	add_filter( 'manage_edit-product_cat_columns', 'wpwoof_product_cats_columns', 20 );
	function wpwoof_product_cats_columns( $columns_array ) {

		$id = count( $columns_array ) - 1;

		return array_slice( $columns_array, 0, $id, true )
		       + array( 'feed' => 'Feed' )
		       + array_slice( $columns_array, $id, null, true );


	}

	add_action( 'manage_product_cat_custom_column', 'wpwoof_cat_populate_columns', 10, 3 );
	function wpwoof_cat_populate_columns( $columns, $column, $term_id ) {
		if ( $column == 'feed' ) {
			echo ( get_term_meta( $term_id, 'wpfoof-exclude-category', true ) != "on" ) ? "Yes" : "No";
		}

	}
}

if ( strpos( $_SERVER['REQUEST_URI'], '/edit-tags.php' ) !== false && isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] == 'product_tag' ) {
	add_filter( 'manage_edit-product_tag_columns', 'wpwoof_product_tags_columns', 20 );
	function wpwoof_product_tags_columns( $columns_array ) {

		$id = count( $columns_array );

		return array_slice( $columns_array, 0, $id, true )
		       + array( 'feed' => 'Feed' )
		       + array_slice( $columns_array, $id, null, true );


	}

	add_action( 'manage_product_tag_custom_column', 'wpwoof_tag_populate_columns', 10, 3 );
	function wpwoof_tag_populate_columns( $columns, $column, $term_id ) {
		if ( $column == 'feed' ) {
			echo ( get_term_meta( $term_id, 'wpfoof-exclude-category', true ) != "on" ) ? "Yes" : "No";
		}

	}
}
 