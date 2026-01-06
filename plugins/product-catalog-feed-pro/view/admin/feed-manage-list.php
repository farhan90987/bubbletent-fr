<?php

//ob_start();
class WpWoof_Feed_Manage_list extends Wpwoof_Feed_List_Table {

	protected $_wpnotice;
	protected $total_items;
	/** ************************************************************************
	 * Normally we would be querying data from a database and manipulating that
	 * for use in your list table. For this example, we're going to simplify it
	 * slightly and create a pre-built array. Think of this as the data that might
	 * be returned by $wpdb->query()
	 *
	 * In a real-world scenario, you would make your own custom query inside
	 * this class' prepare_items() method.
	 *
	 * @var array
	 **************************************************************************/


	/** ************************************************************************
	 * REQUIRED. Set up a constructor that references the parent constructor. We
	 * use the parent reference to set some default configs.
	 ***************************************************************************/
	function __construct() {

		//Set parent defaults
		parent::__construct( array(
			'singular' => __( 'feed' ),     //singular name of the listed records
			'plural'   => __( 'feeds' ),    //plural name of the listed records
			'ajax'     => false        //does this table support ajax?
		) );

		$this->_wpnotice = wp_create_nonce( 'wooffeed-nonce' );

	}


	/** ************************************************************************
	 * Recommended. This method is called when the parent class can't find a method
	 * specifically build for a given column. Generally, it's recommended to include
	 * one method for each column you want to render, keeping your package class
	 * neat and organized. For example, if the class needs to process a column
	 * named 'title', it would first see if a method named $this->column_title()
	 * exists - if it does, that method will be used. If it doesn't, this one will
	 * be used. Generally, you should try to use custom column methods as much as
	 * possible.
	 *
	 * Since we have defined a column_title() method later on, this method doesn't
	 * need to concern itself with any column with a name of 'title'. Instead, it
	 * needs to handle everything else.
	 *
	 * For more detailed insight into how columns are handled, take a look at
	 * WP_List_Table::single_row_columns()
	 *
	 * @param array $item A singular item (one full row's worth of data)
	 * @param array $column_name The name/slug of the column to be processed
	 *
	 * @return string Text or HTML to be placed inside the column <td>
	 **************************************************************************/
	function column_default( $item, $column_name ) {

		global $woocommerce_wpwoof_common;

		$option_id = (int) $item['option_id'];
		$itemInfo  = unserialize( $item['option_value'] );
		if ( ! is_array( $itemInfo ) ) {
			return false;
		}

		$file_name       = isset( $itemInfo['feed_file_name'] ) ? sanitize_text_field( $itemInfo['feed_file_name'] ) : strtolower( str_replace( ' ', '-', trim( $itemInfo['feed_name'] ) ) );
		$upload_dir      = wpwoof_feed_dir( $file_name, 'xml' );
		$upload_dir_csv  = wpwoof_feed_dir( $file_name, 'csv' );
		$feedFileExist   = file_exists( $itemInfo['feed_type'] == "adsensecustom" ? $upload_dir_csv['path'] : $upload_dir['path'] );
		$hideFeedButtons = $feedFileExist ? '' : ' style="display: none;" ';
		$menu_url        = menu_page_url( 'wpwoof-settings', false );

		switch ( $column_name ) {

			case 'feednumber':
				$status        = $woocommerce_wpwoof_common->get_feed_status( $option_id );
				$loaders_style = ( $status['show_loader'] ) ? '' : ' style="display: none;"';
				$feedStatus    = "";
				if ( ! empty( $itemInfo['status_feed'] ) && $itemInfo['status_feed'] != 'finished' && $itemInfo['status_feed'] != 'starting' ) {
					$feedStatus = '<div class="wpwoof-feed-status"><a class="wpwoof_alarm" title="' . htmlspecialchars( $itemInfo['status_feed'], ENT_QUOTES ) . '"><span class="dashicons dashicons-warning" style="color:#dd4e4e;"></span></a>&nbsp;</div>';
				}
				if ( ! in_array( $itemInfo['feed_type'], array( "fb_localize", 'fb_country', 'google_local_inventory' ) ) ) {
					$switcher = '<div class="wpwoof-switch"><input ' . ( empty( $itemInfo['noGenAuto'] ) ? 'checked="true"' : '' ) . '" onchange="jQuery.fn.wpwoofSwicher(' . $option_id . ',jQuery(this).prop(\'checked\'));" type="checkbox" class="ios-switch toggleFeed" /></div>';
				} else {
					$switcher = '——';
				}

				$html = '<div class="wpwoof-feednumber-content">
							<div class="wpwoof-loader"' . $loaders_style . '></div>
							<div class="wpwoof-feedname">' . $itemInfo['feed_name'] . '</div>' . $switcher . $feedStatus . '
						</div>';

				if ( ! empty( $item['has_localized_feeds'] ) ) {
					$html .= '<div><span
                            class="wpwoof-localized-switcher wpwoof-localized-switcher-shown"
                            data-main-feed-id="' . $option_id . '"
                        >Hide localized feeds</span></div>';
				}

				return $html;

			case 'feedname':
				//trace($itemInfo,1);
				$status        = $woocommerce_wpwoof_common->get_feed_status( $option_id );
				$loaders_style = ( $status['show_loader'] ) ? '' : ' style="display: none;"';
				$addStr        = "";
				if ( ! empty( $itemInfo['status_feed'] ) && $itemInfo['status_feed'] != 'finished' && $itemInfo['status_feed'] != 'starting' ) {
					$addStr = '<a class="wpwoof_alarm" title="' . htmlspecialchars( $itemInfo['status_feed'], ENT_QUOTES ) . '"><span class="dashicons dashicons-warning" style="color:#dd4e4e;"></span></a>&nbsp;';
				}

				$view = $itemInfo['feed_type'] == "adsensecustom" ? $upload_dir_csv['url'] : $upload_dir['url'];

				$feed_inactive_notification = ! empty( $status['is_inactive'] ) ? "<p class='inactive-feed-notification'>This feed uses some features of WPML plugin which seems to be deactivated.<br>Please re-activate your WPML plugin for feeds localization.</p>" : "";
				$copy_link                  = add_query_arg( array(
					'copy'     => $option_id,
					'_wpnonce' => $this->_wpnotice
				), $menu_url );
				$return                     = "<span class='copy'><a disabled='disabled'  class='wpwoof-button-forlist wpwoof-hide-feed-button ' " . $hideFeedButtons . " href='" . $view . "' onclick='return copyWoofLink(this.href);'>" . __( 'Copy feed URL', 'woocommerce_wpwoof' ) . "</a></span> | ";
				$return                     .= "<span class='open'><a disabled='disabled'  class='wpwoof-button-forlist wpwoof-hide-feed-button ' " . $hideFeedButtons . " target='_blank' class='button' href='" . $view . "'>" . ( $itemInfo['feed_type'] == "adsensecustom" ? __( 'CSV Link', 'woocommerce_wpwoof' ) : __( 'Open' ) ) . "</a></span> ";
				if ( ! in_array( $itemInfo['feed_type'], array( 'fb_localize', 'fb_country', 'google_local_inventory' ) ) ) {
					$return .= " | <span class='duplicate'><a disabled='disabled'  class='wpwoof-button-forlist ' class='button' href='" . $copy_link . "'>" . __( 'Duplicate' ) . "</a></span>";
				}
				if ( $itemInfo['feed_type'] == "facebook" ) {
					if ( $woocommerce_wpwoof_common->can_create_facebook_country_feed() ) {
						$return .= " | <span class='create_localized'><a disabled='disabled'  class='wpwoof-button-forlist ' class='button' href='" . add_query_arg( array(
								'feed_type' => 'fb_country',
								'main-feed' => $option_id,
								'_wpnonce'  => $this->_wpnotice
							), $menu_url ) . "'>" . __( 'Create country feed' ) . "</a></span> ";
					}
					if ( $woocommerce_wpwoof_common->can_create_facebook_language_feed( $itemInfo ) ) {
						$return .= " | <span class='create_localized'><a disabled='disabled'  class='wpwoof-button-forlist ' class='button' href='" . add_query_arg( array(
								'feed_type' => 'fb_localize',
								'main-feed' => $option_id,
								'_wpnonce'  => $this->_wpnotice
							), $menu_url ) . "'>" . __( 'Create language feed' ) . "</a></span> ";
					}

				} elseif ( $itemInfo['feed_type'] == "google" ) {
					if ( $woocommerce_wpwoof_common->can_create_google_local_inventory_feed() ) {
						$return .= " | <span class='create_localized'><a disabled='disabled'  class='wpwoof-button-forlist ' class='button' href='" . add_query_arg( array(
								'feed_type' => 'google_local_inventory',
								'main-feed' => $option_id,
								'_wpnonce'  => $this->_wpnotice
							), $menu_url ) . "'>" . __( 'Create local inventory feed' ) . "</a></span> ";
					}

				}

//                return $return;


				return '<div class="wpwoof-feedname-content"><div class="wpwoof-loader" ' . $loaders_style . '></div>&nbsp;' . $addStr . $itemInfo['feed_name'] . '</div>' . $feed_inactive_notification . '<div class="row-actions">' . $return . '</div>';


			case 'feed_type':
				return isset( $woocommerce_wpwoof_common->feed_type_name[ $itemInfo['feed_type'] ] ) ? $woocommerce_wpwoof_common->feed_type_name[ $itemInfo['feed_type'] ] : "Facebook Product Catalog";

			case 'feedtaxcountry':
				$answ = "";
				if ( isset( $itemInfo['field_mapping']['tax']['value'] ) && $itemInfo['field_mapping']['tax']['value'] == "true" ) {
					if ( get_option( "woocommerce_tax_based_on" )/*!='base'*/ ) {
						if ( isset( $itemInfo['field_mapping']['tax_countries']['value'] )
						     && ! empty( $itemInfo['field_mapping']['tax_countries']['value'] )
						) {
							$sValTMP = $itemInfo['field_mapping']['tax_countries']['value'];
							$taxRate = "";
							if ( strpos( $sValTMP, "-" ) ) {
								$sValTMP = explode( "-", $itemInfo['field_mapping']['tax_countries']['value'] );
								$id      = $sValTMP[1];
								$taxRate = $woocommerce_wpwoof_common->getTaxRateCountries( $id );
								if ( count( $taxRate ) == 1 ) {
									$taxRate = "&nbsp;(" . $taxRate[0]['rate'] . ")";
								} else {
									$taxRate = "&nbsp;(n/a)";
								}
								$sValTMP = $sValTMP[0];
							}
							$answ .= $woocommerce_wpwoof_common->getCountryByCode( $sValTMP ) . $taxRate;
						}
					}

				} else {
					$answ = "--";
				}

				return $answ;

			case 'feedcategory':
				if ( in_array( $itemInfo['feed_type'], array( "fb_localize", 'fb_country', 'google_local_inventory' ) ) ) {
					return '';
				}

				if ( ! empty( $itemInfo['feed_category_all'] ) ) {
					return 'All';
				}
				if ( empty( $itemInfo['feed_category'] ) ) {
					return '';
				}

				// Get only 2 categories
				$category_id = array_slice( $itemInfo['feed_category'], 0, 2 );

				$categories_name = $this->get_categories_name( $category_id );

				$additional_info = '';
				if ( count( $itemInfo['feed_category'] ) > 2 || ! empty( $itemInfo['feed_category_excluded'] ) ) {
					$included_categories_name = $this->get_categories_name( $itemInfo['feed_category'] );
					$excluded_categories_name = ! empty( $itemInfo['feed_category_excluded'] ) ? $this->get_categories_name( $itemInfo['feed_category_excluded'] ) : '';
					$excluded_string          = ! empty( $itemInfo['feed_category_excluded'] ) ? ' excluded: ' . count( $itemInfo['feed_category_excluded'] ) : '';
					$additional_info          = "<br><a class='show-feed-all-categories' href='#' data-categories='" . esc_attr( $included_categories_name ) . "' data-categories_excluded='" . esc_attr( $excluded_categories_name ) . "'>Total included: " . count( $itemInfo['feed_category'] ) . $excluded_string . '</a>';
				}

				return $categories_name . $additional_info;

			case 'feedproducts':
				return isset( $itemInfo['total_products'] ) ? $itemInfo['total_products'] : '';

			case 'feeddate':
				$out  = "";
				$date = new DateTime();
				$date->setTimestamp( isset( $itemInfo['generated_time'] ) ? $itemInfo['generated_time'] : $itemInfo['added_time'] );
				$date->setTimezone( new DateTimeZone( $woocommerce_wpwoof_common->getWpTimezone() ) );
				$out     .= $date->format( 'd/m/Y H:i:s' );
				$nextRun = $woocommerce_wpwoof_common->get_feed_gen_schedule( $option_id );
				if ( ! empty( $nextRun ) ) {
					$date->setTimestamp( $nextRun );
					$out .= '<br>Next update:<br>' . $date->format( 'd/m/Y H:i:s' );
				}

				return $out;

//			case 'feedaction':
//				$view = $itemInfo['feed_type'] == "adsensecustom" ? $upload_dir_csv['url'] : $upload_dir['url'];
//
//				$copy_link = add_query_arg( array( 'copy' => $option_id, '_wpnonce' => $this->_wpnotice ), $menu_url );
//				$return    = "<a disabled='disabled'  class='wpwoof-button-forlist wpwoof-hide-feed-button green' " . $hideFeedButtons . " href='" . $view . "' onclick='return copyWoofLink(this.href);'>" . __( 'Copy feed URL', 'woocommerce_wpwoof' ) . "</a>";
//				$return    .= "<a disabled='disabled'  class='wpwoof-button-forlist wpwoof-hide-feed-button gray' " . $hideFeedButtons . " target='_blank' class='button' href='" . $view . "'>" . ( $itemInfo['feed_type'] == "adsensecustom" ? __( 'CSV Link', 'woocommerce_wpwoof' ) : __( 'Open' ) ) . "</a>";
//				if ($itemInfo['feed_type'] != "fb_localize") {
//					$return    .= "<a disabled='disabled'  class='wpwoof-button-forlist gray' class='button' href='" . $copy_link . "'>" . __( 'Duplicate' ) . "</a>";
//				}
//
//
//
//				return $return;

			case 'feedupdate':
				$status          = $woocommerce_wpwoof_common->get_feed_status( $option_id );
				$loaders_style   = ( $status['show_loader'] ) ? 'style="display: block;"' : '';
				$img_margin_left = - 2;
				if ( $status['parsed_products'] != - 1 ) {
					$total           = $status['total_products'] ? 100.0 / $status['total_products'] * $status['parsed_products'] : 100;
					$img_margin_left += - 29 + round( 3 * $total / 10 );
				} else {
					$img_margin_left = - 29;
				}

				$edit   = add_query_arg( array(
					'tab'      => 1,
					'edit'     => $option_id,
					'_wpnonce' => $this->_wpnotice
				), $menu_url );
				$delete = add_query_arg( array(
					'tab'      => 0,
					'delete'   => $option_id,
					'_wpnonce' => $this->_wpnotice
				), $menu_url );
				$update = add_query_arg( array(
					'tab'      => 0,
					'update'   => $option_id,
					'_wpnonce' => $this->_wpnotice
				), $menu_url );

				$return = "<div class='column-feedupdate-wrapper'>";
				if ( empty( $status['is_inactive'] ) ) {
					$return .= "<a disabled='disabled' class='wpwoof-button-forlist support-hover' href='" . $edit . "'> <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 576 512'><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d='M402.6 83.2l90.2 90.2c3.8 3.8 3.8 10 0 13.8L274.4 405.6l-92.8 10.3c-12.4 1.4-22.9-9.1-21.5-21.5l10.3-92.8L388.8 83.2c3.8-3.8 10-3.8 13.8 0zm162-22.9l-48.8-48.8c-15.2-15.2-39.9-15.2-55.2 0l-35.4 35.4c-3.8 3.8-3.8 10 0 13.8l90.2 90.2c3.8 3.8 10 3.8 13.8 0l35.4-35.4c15.2-15.3 15.2-40 0-55.2zM384 346.2V448H64V128h229.8c3.2 0 6.2-1.3 8.5-3.5l40-40c7.6-7.6 2.2-20.5-8.5-20.5H48C21.5 64 0 85.5 0 112v352c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V306.2c0-10.7-12.9-16-20.5-8.5l-40 40c-2.2 2.3-3.5 5.3-3.5 8.5z'/></svg> " . '<span class="tip">Edit</span></a>';
					$return .= "<a disabled='disabled' id='wpwoof_status_" . $option_id . "a' class='wpwoof-button-forlist regenerate support-hover' href='$update'> <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d='M212.3 224.3H12c-6.6 0-12-5.4-12-12V12C0 5.4 5.4 0 12 0h48c6.6 0 12 5.4 12 12v78.1C117.8 39.3 184.3 7.5 258.2 8c136.9 1 246.4 111.6 246.2 248.5C504 393.3 393.1 504 256.3 504c-64.1 0-122.5-24.3-166.5-64.2-5.1-4.6-5.3-12.6-.5-17.4l34-34c4.5-4.5 11.7-4.7 16.4-.5C170.8 415.3 211.6 432 256.3 432c97.3 0 176-78.7 176-176 0-97.3-78.7-176-176-176-58.5 0-110.3 28.5-142.3 72.3h98.3c6.6 0 12 5.4 12 12v48c0 6.6-5.4 12-12 12z'/></svg> <span class='tip'>Regenerate</span>" . "<div class='wpwoof_statusbar' id='wpwoof_status_" . $option_id . "' data-feedid='" . $option_id . "' " . $loaders_style . "><img id='wpwoof_img_" . $option_id . "' style='margin-left: " . $img_margin_left . "px;' src='" . WPWOOF_URL . "/assets/img/bar.png' /></div></a>";
				}
				$return .= "<a disabled='disabled' class='wpwoof-button-forlist support-hover' href='" . $delete . "'> <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d='M32 464a48 48 0 0 0 48 48h288a48 48 0 0 0 48-48V128H32zm272-256a16 16 0 0 1 32 0v224a16 16 0 0 1 -32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1 -32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1 -32 0zM432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.7 23.7 0 0 0 -21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0 -16-16z'/></svg>" . '<span class="tip">Delete</span></a>';
				$return .= "</div>";

				return $return;

			case 'feeddownload':
				$return = "<div class='column-feeddownload-wrapper'>";
				$return .= ( $itemInfo['feed_type'] == "adsensecustom" ) ? '' : '<a disabled="disabled" id="wpwoof_status_' . $option_id . 'x" class="wpwoof-button-forlist wpwoof-hide-feed-button support-hover" ' . $hideFeedButtons . ' href="' . $upload_dir['url'] . '" download="' . $upload_dir['file'] . '"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M224 136V0H24C10.7 0 0 10.7 0 24v464c0 13.3 10.7 24 24 24h336c13.3 0 24-10.7 24-24V160H248c-13.2 0-24-10.8-24-24zm60.1 106.5L224 336l60.1 93.5c5.1 8-.6 18.5-10.1 18.5h-34.9c-4.4 0-8.5-2.4-10.6-6.3C208.9 405.5 192 373 192 373c-6.4 14.8-10 20-36.6 68.8-2.1 3.9-6.1 6.3-10.5 6.3H110c-9.5 0-15.2-10.5-10.1-18.5l60.3-93.5-60.3-93.5c-5.2-8 .6-18.5 10.1-18.5h34.8c4.4 0 8.5 2.4 10.6 6.3 26.1 48.8 20 33.6 36.6 68.5 0 0 6.1-11.7 36.6-68.5 2.1-3.9 6.2-6.3 10.6-6.3H274c9.5-.1 15.2 10.4 10.1 18.4zM384 121.9v6.1H256V0h6.1c6.4 0 12.5 2.5 17 7l97.9 98c4.5 4.5 7 10.6 7 16.9z"/></svg><span class="tip tip-left">Download XML</span></a>';
				if ( $itemInfo['feed_type'] != "googleReviews" ) {
					$return .= '<a disabled="disabled" id="wpwoof_status_' . $option_id . 'c" class="wpwoof-button-forlist wpwoof-hide-feed-button support-hover" ' . $hideFeedButtons . ' href="' . $upload_dir_csv['url'] . '" download="' . $upload_dir_csv['file'] . '"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M384 121.9V128H256V0h6.1c6.4 0 12.5 2.5 17 7l97.9 97.9A24 24 0 0 1 384 121.9zM248 160c-13.2 0-24-10.8-24-24V0H24C10.7 0 0 10.7 0 24v464c0 13.3 10.7 24 24 24h336c13.3 0 24-10.7 24-24V160H248zM123.2 400.5a5.4 5.4 0 0 1 -7.6 .2l-64.9-60.8a5.4 5.4 0 0 1 0-7.9l64.9-60.8a5.4 5.4 0 0 1 7.6 .2l19.6 20.9a5.4 5.4 0 0 1 -.4 7.7L101.7 336l40.8 35.9a5.4 5.4 0 0 1 .4 7.7l-19.6 20.9zm51.3 50.5l-27.5-8a5.4 5.4 0 0 1 -3.7-6.7l61.4-211.6a5.4 5.4 0 0 1 6.7-3.7l27.5 8a5.4 5.4 0 0 1 3.7 6.7l-61.4 211.6a5.4 5.4 0 0 1 -6.7 3.7zm160.8-111l-64.9 60.8a5.4 5.4 0 0 1 -7.6-.2l-19.6-20.9a5.4 5.4 0 0 1 .4-7.7L284.4 336l-40.8-35.9a5.4 5.4 0 0 1 -.4-7.7l19.6-20.9a5.4 5.4 0 0 1 7.6-.2l64.9 60.8a5.4 5.4 0 0 1 0 7.9z"/></svg><span class="tip tip-left">Download CSV</span></a>';
				}
				$return .= "</div>";

				return $return;
			default:
				do_action('wpwoof_custom_column_' . $column_name, $item, $column_name);

				return apply_filters('wpwoof_custom_column_content', '', $item, $column_name);

		}
	}


	/** ************************************************************************
	 * Recommended. This is a custom column method and is responsible for what
	 * is rendered in any column with a name/slug of 'title'. Every time the class
	 * needs to render a column, it first looks for a method named
	 * column_{$column_title} - if it exists, that method is run. If it doesn't
	 * exist, column_default() is called instead.
	 *
	 * This example also illustrates how to implement rollover actions. Actions
	 * should be an associative array formatted as 'slug'=>'link html' - and you
	 * will need to generate the URLs yourself. You could even ensure the links
	 *
	 *
	 * @param array $item A singular item (one full row's worth of data)
	 *
	 * @return string Text to be placed inside the column <td> (movie title only)
	 **************************************************************************@see WP_List_Table::::single_row_columns()
	 */
	function column_option_name( $item ) {
		//Build row actions
		$edit_nonce   = wp_create_nonce( 'wpwoof_edit_nonce' );
		$delete_nonce = wp_create_nonce( 'wpwoof_delete_nonce' );
		//$title = '<strong>' . $item['option_name'] . '</strong>';


		$actions = array(
			'edit'   => sprintf( '<a disabled=\'disabled\' href="?page=%s&action=%s&feed=%s&_wpnonce=%s">' . __( 'Edit', 'woo-feed' ) . '</a>', esc_attr( $_REQUEST['page'] ), 'edit-feed', $item['option_name'], $edit_nonce ),
			'delete' => sprintf( '<a disabled=\'disabled\' val="?page=%s&action=%s&feed=%s&_wpnonce=%s" class="single-feed-delete" style="cursor: pointer;">' . __( 'Delete', 'woo-feed' ) . '</a>', esc_attr( $_REQUEST['page'] ), 'delete-feed', absint( $item['option_id'] ), $delete_nonce )
		);

		//Return the title contents
		$name = str_replace( "wpwoof_feedlist_", "", $item['option_name'] );

		return sprintf( '%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
			/*$1%s*/
			$name,
			/*$2%s*/
			$item['option_id'],
			/*$3%s*/
			$this->row_actions( $actions )
		);
	}

	public function get_feeds( $search = "" ) {
		global $wpdb;
		$var = "wpwoof_feedlist_";

		$query  = $wpdb->prepare( "SELECT * FROM $wpdb->options WHERE option_name LIKE %s AND option_name <> 'wpwoof_feedlist_' Order By option_id DESC;", $var . "%" );
		$result = $wpdb->get_results( $query, 'ARRAY_A' );
		//trace($result);
		if ( $result ) {
			foreach ( $result as $id => $row ) {
				if ( empty( $row['option_id'] ) ) {
					$r = $wpdb->get_row( "SELECT MAX(option_id)+1 as id from " . $wpdb->options, ARRAY_A );
					$wpdb->query( "update " . $wpdb->options . " SET option_id='" . $r['id'] . "' where option_name = '" . $row['option_name'] . "'" );
					$result[ $id ]['option_id'] = $r['id'];
				}
			}
		}

		// Sort the arrays by descending `option_id` and filter those without `main_feed_id`
		$sorted_data = array_filter( $result, function ( $item ) {
			$option_value = unserialize( $item['option_value'] );

			return ! isset( $option_value['main_feed_id'] );
		} );
		usort( $sorted_data, function ( $a, $b ) {
			return $b['option_id'] <=> $a['option_id'];
		} );

		$this->total_items = count( $sorted_data );

		// Sort arrays with `main_feed_id`
		$main_feed_data = array_filter( $result, function ( $item ) {
			$option_value = unserialize( $item['option_value'] );

			return isset( $option_value['main_feed_id'] );
		} );
		usort( $main_feed_data, function ( $a, $b ) {
			return $a['option_id'] <=> $b['option_id'];
		} );

		// Add sorted arrays with `main_feed_id` after corresponding `option_id`
		foreach ( $main_feed_data as $item ) {
			$option_value   = unserialize( $item['option_value'] );
			$main_feed_id   = $option_value['main_feed_id'];
			$total_elements = count( $sorted_data );
			$current_index  = 0;
			foreach ( $sorted_data as $index => $sorted_item ) {
				$current_index ++;
				if ( $sorted_item['option_id'] == $main_feed_id ) {
					array_splice( $sorted_data, $index + 1, 0, [ $item ] );
					$sorted_data[ $index ]['has_localized_feeds'] = true;
					break;
				} elseif ( $current_index === $total_elements ) {
					// if main_feed_id not foud add feed to beginning of the list
					array_splice( $sorted_data, 0, 0, [ $item ] );
				}
			}
		}

		return $sorted_data;
	}

	/**
	 * Delete a Feed.
	 *
	 * @param int $id Feed ID
	 *
	 * @return false|int
	 */
	public static function delete_feed( $id ) {
		global $wpdb;
		self::delete_feed_file( $id );

		return $wpdb->delete(
			"{$wpdb->prefix}options",
			array( 'option_id' => $id ),
			array( '%d' )
		);
	}

	/**
	 * Delete a Feed File.
	 *
	 * @param int $id customer ID
	 *
	 * @return false|int
	 */
	public static function delete_feed_file( $id ) {
		global $wpdb;
		$mylink      = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}options WHERE option_id = $id" );
		$option_name = $mylink->option_name;
		if ( ! is_array( get_option( $option_name ) ) ) {
			$feedInfo = unserialize( get_option( $option_name ) );
		}

		$upload_dir = wp_upload_dir();
		$base       = $upload_dir['basedir'];
		if ( isset( $feedInfo['feedrules']['provider'] ) && isset( $feedInfo['feedrules']['feedType'] ) ) {
			$path = $base . "/woo-feed/" . $feedInfo['feedrules']['provider'] . "/" . $feedInfo['feedrules']['feedType'];
			$file = $path . "/" . $feedInfo['feedrules']['filename'] . "." . $feedInfo['feedrules']['feedType'];
			unlink( $file );
		}
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;
		$sql = "SELECT * FROM {$wpdb->prefix}options WHERE option_name like 'wpwoof_feedlist_%'";

		return $wpdb->get_var( $sql );
	}

	/** Text displayed when no data is available */
	public function no_items() {
		_e( 'No feed available.', 'woo-feed' );
	}


	/** ************************************************************************
	 * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
	 * is given special treatment when columns are processed. It ALWAYS needs to
	 * have it's own method.
	 *
	 * @param array $item A singular item (one full row's worth of data)
	 *
	 * @return string Text to be placed inside the column <td> (movie title only)
	 **************************************************************************@see WP_List_Table::::single_row_columns()
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/
			$this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
			/*$2%s*/
			$item['option_id']                //The value of the checkbox should be the record's id
		);
	}


	function column_name( $item ) {
		$edit_nonce   = wp_create_nonce( 'wpwoof_edit_nonce' );
		$delete_nonce = wp_create_nonce( 'wpwoof_delete_nonce' );
		$title        = '<strong>' . $item['option_name'] . '</strong>';

		$actions = array(
			'edit'   => sprintf( '<a disabled=\'disabled\' href="?page=%s&action=%s&feed=%s&_wpnonce=%s">' . __( 'Edit', 'woo-feed' ) . '</a>', esc_attr( $_REQUEST['page'] ), 'edit-feed', absint( $item['option_id'] ), $edit_nonce ),
			'delete' => sprintf( '<a disabled=\'disabled\' val="?page=%s&action=%s&feed=%s&_wpnonce=%s" class="single-feed-delete" style="cursor: pointer;">' . __( 'Delete', 'woo-feed' ) . '</a>', esc_attr( $_REQUEST['page'] ), 'delete-feed', absint( $item['option_id'] ), $delete_nonce )
		);

		return $title . $this->row_actions( $actions );
	}

	/** ************************************************************************
	 * REQUIRED! This method dictates the table's columns and titles. This should
	 * return an array where the key is the column slug (and class) and the value
	 * is the column's title text. If you need a checkbox for bulk actions, refer
	 * to the $columns array below.
	 *
	 * The 'cb' column is treated differently than the rest. If including a checkbox
	 * column in your table you must create a column_cb() method. If you don't need
	 * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
	 *
	 * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
	 **************************************************************************@see WP_List_Table::::single_row_columns()
	 */
	function get_columns() {
		$columns = array(
			'cb'             => '<input type="checkbox" />', //Render a checkbox instead of text
			'feednumber'     => __( '#' ),
			'feedname'       => __( 'Feed Name' ),
			'feed_type'      => __( 'Feed Type' ),
			'feedtaxcountry' => __( 'Tax Сountry' ),
			'feedcategory'   => __( 'Category' ),
			'feedproducts'   => __( 'Products' ),
			'feeddate'       => __( 'Last updated' ),
//			'feedaction'     => __( "Action" ),
			'feedupdate'     => __( "Regenerate" ),
			'feeddownload'   => __( "Download" ),
		);

		return apply_filters('wpwoof_feed_table_columns', $columns);
	}


	/** ************************************************************************
	 * Optional. If you want one or more columns to be sortable (ASC/DESC toggle),
	 * you will need to register it here. This should return an array where the
	 * key is the column that needs to be sortable, and the value is db column to
	 * sort by. Often, the key and value will be the same, but this is not always
	 * the case (as the value is a column name from the database, not the list table).
	 *
	 * This method merely defines which columns should be sortable and makes them
	 * clickable - it does not handle the actual sorting. You still need to detect
	 * the ORDERBY and ORDER querystring variables within prepare_items() and sort
	 * your data accordingly (usually by modifying your query).
	 *
	 * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
	 **************************************************************************/
	function get_sortable_columns() {
		$sortable_columns = array(
			// 'feednumber'=>array('feednumber'),
			// 'feedname' => array('feedname')
		);

		return $sortable_columns;
	}


	/** ************************************************************************
	 * Optional. If you need to include bulk actions in your list table, this is
	 * the place to define them. Bulk actions are an associative array in the format
	 * 'slug'=>'Visible Title'
	 *
	 * If this method returns an empty value, no bulk action will be rendered. If
	 * you specify any bulk actions, the bulk actions box will be rendered with
	 * the table automatically on display().
	 *
	 * Also note that list tables are not automatically wrapped in <form> elements,
	 * so you will need to create those manually in order for bulk actions to function.
	 *
	 * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
	 **************************************************************************/
	function get_bulk_actions() {
		$actions = array(
			'bulk-delete'   => __( 'Delete' ),
			'bulk-turn-on'  => __( 'Turn ON' ),
			'bulk-turn-off' => __( 'Turn OFF' ),
		);

		return $actions;
	}


	/** ************************************************************************
	 * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
	 * For this example package, we will handle it in the class to keep things
	 * clean and organized.
	 *
	 * @see $this->prepare_items()
	 **************************************************************************/
	public function process_bulk_action() {
		//Detect when a bulk action is being triggered...
		if ( 'delete-feed' === $this->current_action() ) {
			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );
			if ( ! wp_verify_nonce( $nonce, 'wpwoof_delete_nonce' ) ) {
				update_option( 'wpwoof_message', 'Failed To Delete Feed. You do not have sufficient permission to delete.' );
				wp_redirect( admin_url( "admin.php?page=category_mapping&wpwoof_message=error" ) );
			} else {
				if ( self::delete_feed( absint( $_GET['feed'] ) ) ) {

					update_option( 'wpwoof_message', 'Feed Deleted Successfully' );
					wp_redirect( admin_url( "admin.php?page=wpwoof-settings&wpwoof_message=success" ) );
				} else {
					update_option( 'wpwoof_message', 'Failed To Delete Feed' );
					wp_redirect( admin_url( "admin.php?page=wpwoof-settings&wpwoof_message=error" ) );
				}

			}
		}
		//Detect when a bulk action is being triggered...
		if ( 'edit-feed' === $this->current_action() ) {
			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );
			if ( ! wp_verify_nonce( $nonce, 'wpwoof_edit_nonce' ) ) {
				die( 'Cheating huh!' );
			} else {

			}
		}


		// If the delete bulk action is triggered
		if ( isset( $_POST['feed'] ) && ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
		                                  || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' ) )
		) {
			if ( 'bulk-delete' === $this->current_action() ) {
				$nonce = esc_attr( $_REQUEST['_wpnonce'] );
				if ( ! wp_verify_nonce( $nonce, "bulk-" . $this->_args['plural'] ) ) {
					die( 'cheating huh!' );
				} else {
					$delete_ids = esc_sql( $_POST['feed'] );
					// loop over the array of record IDs and delete them
					if ( count( $delete_ids ) ) {
						foreach ( $delete_ids as $id ) {
							self::delete_feed( $id );

						}
						update_option( 'wpwoof_message', 'Feed Deleted Successfully' );
						wp_redirect( admin_url( "admin.php?page=wpwoof-settings&wpwoof_message=success" ) );
					}
				}
			}
		}

		// If the Turn ON or Turn OFF bulk action is triggered
		if ( isset( $_POST['feed'] ) && ( ( isset( $_POST['action'] ) && in_array( $_POST['action'], array(
						'bulk-turn-on',
						'bulk-turn-off'
					) ) )
		                                  || ( isset( $_POST['action2'] ) && in_array( $_POST['action2'], array(
						'bulk-turn-on',
						'bulk-turn-off'
					) ) ) )
		) {
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );
			if ( ! wp_verify_nonce( $nonce, 'bulk-' . $this->_args['plural'] ) ) {
				die( 'cheating huh!' );
			} else {
				$changeStatusAction = ( $this->current_action() == 'bulk-turn-on' ) ? 0 : 1;
				$feed_ids           = esc_sql( $_POST['feed'] );
				if ( count( $feed_ids ) ) {
					foreach ( $feed_ids as $id ) {
						if ( ! $id || ! is_numeric( $id ) ) {
							continue;
						}

						$value = wpwoof_get_feed( $id );
						if ( ! empty( $value['feed_name'] ) ) {
							$value['noGenAuto'] = $changeStatusAction;
							wpwoof_update_feed( $value, $id, true );
							wpwoof_product_catalog::schedule_feed( $value );
						}
					}
					$wpwoof_message = ( $changeStatusAction === 0 ) ? 'Feed Activated Successfully' : 'Feed Deactivated Successfully';
					update_option( 'wpwoof_message', $wpwoof_message );
					wp_redirect( admin_url( 'admin.php?page=wpwoof-settings&wpwoof_message=success' ) );
				}
			}
		}
	}


	/** ************************************************************************
	 * REQUIRED! This is where you prepare your data for display. This method will
	 * usually be used to query the database, sort and filter the data, and generally
	 * get it ready to be displayed. At a minimum, we should set $this->items and
	 * $this->set_pagination_args(), although the following properties and methods
	 * are frequently interacted with here...
	 *
	 * @global WPDB $wpdb
	 * @uses $this->_column_headers
	 * @uses $this->items
	 * @uses $this->get_columns()
	 * @uses $this->get_sortable_columns()
	 * @uses $this->get_pagenum()
	 * @uses $this->set_pagination_args()
	 **************************************************************************/
	function prepare_items() {
		global $wpdb; //This is used only if making any database queries

		/**
		 * First, lets decide how many records per page to show
		 */
		$per_page = 10;


		/**
		 * REQUIRED. Now we need to define our column headers. This includes a complete
		 * array of columns to be displayed (slugs & titles), a list of columns
		 * to keep hidden, and a list of columns that are sortable. Each of these
		 * can be defined in another method (as we've done here) before being
		 * used to build the value for our _column_headers property.
		 */
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();


		/**
		 * REQUIRED. Finally, we build an array to be used by the class for column
		 * headers. The $this->_column_headers property takes an array which contains
		 * 3 other arrays. One for all columns, one for hidden columns, and one
		 * for sortable columns.
		 */
		$this->_column_headers = array( $columns, $hidden, $sortable );


		/**
		 * Optional. You can handle your bulk actions however you see fit. In this
		 * case, we'll handle them within our package just to keep things clean.
		 */
		$this->process_bulk_action();


		/**
		 * Instead of querying a database, we're going to fetch the example data
		 * property we created for use in this plugin. This makes this example
		 * package slightly different than one you might build on your own. In
		 * this example, we'll be using array manipulation to sort and paginate
		 * our data. In a real-world implementation, you will probably want to
		 * use sort and pagination data to build a custom query instead, as you'll
		 * be able to use your precisely-queried data immediately.
		 */
		if ( isset( $_POST['s'] ) ) {
			$data = $this->get_feeds( $_POST['s'] );
		} else {

			$data = $this->get_feeds();
		}


		/***********************************************************************
		 * ---------------------------------------------------------------------
		 * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
		 *
		 * In a real-world situation, this is where you would place your query.
		 *
		 * For information on making queries in WordPress, see this Codex entry:
		 * http://codex.wordpress.org/Class_Reference/wpdb
		 *
		 * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
		 * ---------------------------------------------------------------------
		 **********************************************************************/


		/**
		 * REQUIRED for pagination. Let's figure out what page the user is currently
		 * looking at. We'll need this later, so you should always include it in
		 * your own package classes.
		 */
		$current_page = $this->get_pagenum();

		/**
		 * The WP_List_Table class does not handle pagination for us, so we need
		 * to ensure that the data is trimmed to only the current page. We can use
		 * array_slice() to
		 */
//		$data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

		$feeds_for_show        = array();
		$main_feed_counter     = 0;
		$show_feed_from_number = ( $current_page - 1 ) * $per_page;
		foreach ( $data as $feed ) {
			$option_value = unserialize( $feed['option_value'] );
			if ( empty( $option_value['main_feed_id'] ) ) {
				$main_feed_counter ++;
				if ( $main_feed_counter > $show_feed_from_number + $per_page ) {
					break;
				}
				if ( $main_feed_counter > $show_feed_from_number ) {
					$feeds_for_show[] = $feed;
				}
			} elseif ( ( $main_feed_counter > $show_feed_from_number && $main_feed_counter <= $show_feed_from_number + $per_page )
			           || $main_feed_counter == 0 ) {
				$feeds_for_show[] = $feed;
			}
		}

		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args( array(
			'total_items' => $this->total_items,                  //WE have to calculate the total number of items
			'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
			'total_pages' => ceil( $this->total_items / $per_page )   //WE have to calculate the total number of pages
		) );

		$this->items = $feeds_for_show;
	}


	public function get_categories_name( $ids ): string {

		$category_str = '';
		if ( ! empty( $ids ) && is_array( $ids ) ) {

			foreach ( $ids as $key => $term_id ) {
				$term = get_term( $term_id, 'product_cat' );
				if ( ! is_wp_error( $term ) ) {
					$category_str .= $term->name . ', ';
				}

			}
		}

		$category_str = rtrim( $category_str, ', ' );


		return $category_str;
	}

}
