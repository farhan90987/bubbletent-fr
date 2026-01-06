<?php

namespace YayMail\MailBuilder;

use YayMail\Helper\Helper;

defined( 'ABSPATH' ) || exit;
/**
 * Settings Page
 */
class YaymailElement {
	protected static $instance = null;
	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
			self::$instance->doHooks();
		}

		return self::$instance;
	}

	private function doHooks() {
		// BASIC
		add_action( 'YaymailLogo', array( $this, 'yaymail_logo' ), 100, 6 );
		add_action( 'YaymailImages', array( $this, 'yaymail_images' ), 100, 6 );
		add_action( 'YaymailElementText', array( $this, 'yaymail_element_text' ), 100, 6 );
		add_action( 'YaymailButton', array( $this, 'yaymail_button' ), 100, 6 );
		add_action( 'YaymailTitle', array( $this, 'yaymail_title' ), 100, 6 );
		add_action( 'YaymailSocialIcon', array( $this, 'yaymail_social_icon' ), 100, 6 );
		add_action( 'YaymailVideo', array( $this, 'yaymail_video' ), 100, 6 );
		add_action( 'YaymailHTMLCode', array( $this, 'yaymail_html_code' ), 100, 6 );
		add_action( 'YaymailImageList', array( $this, 'yaymail_image_list' ), 100, 6 );
		add_action( 'YaymailImageBox', array( $this, 'yaymail_image_box' ), 100, 6 );
		add_action( 'YaymailTextList', array( $this, 'yaymail_text_list' ), 100, 6 );
		// GENERAL
		add_action( 'YaymailSpace', array( $this, 'yaymail_space' ), 100, 6 );
		add_action( 'YaymailDivider', array( $this, 'yaymail_divider' ), 100, 6 );
		add_action( 'YaymailOneColumn', array( $this, 'yaymail_one_column' ), 100, 6 );
		add_action( 'YaymailTwoColumns', array( $this, 'yaymail_two_column' ), 100, 6 );
		add_action( 'YaymailThreeColumns', array( $this, 'yaymail_three_column' ), 100, 6 );
		add_action( 'YaymailFourColumns', array( $this, 'yaymail_four_column' ), 100, 6 );
		// WOOCOMMERCE
		add_action( 'YaymailShippingAddress', array( $this, 'yaymail_shipping_address' ), 100, 6 );
		add_action( 'YaymailBillingAddress', array( $this, 'yaymail_billing_address' ), 100, 6 );
		add_action( 'YaymailOrderItem', array( $this, 'yaymail_order_item' ), 100, 6 );
		add_action( 'YaymailOrderItemDownload', array( $this, 'yaymail_order_item_download' ), 100, 6 );
		add_action( 'YaymailHook', array( $this, 'yaymail_hook' ), 100, 6 );
		// Add Support Plugin in Pro version
		add_action( 'YaymailTrackingItem', array( $this, 'yaymail_tracking_item' ), 100, 6 );
		add_action( 'YaymailItemsShipment', array( $this, 'yaymail_items_shipment' ), 100, 6 );
		add_action( 'YaymailAdditionalOrderDetails', array( $this, 'yaymail_additional_order_details' ), 100, 6 );
		add_action( 'YaymailEventOnList', array( $this, 'yaymail_event_on_list' ), 100, 6 );
		add_action( 'YaymailTrackingDetails', array( $this, 'yaymail_tracking_details' ), 100, 6 );
		add_action( 'YaymailSoftwareAddOn', array( $this, 'yaymail_software_add_on' ), 100, 6 );
		add_action( 'YaymailFooEventsTicket', array( $this, 'yaymail_fooevents_ticket' ), 100, 6 );
		add_action( 'YaymailShippingTaxShipmentTracking', array( $this, 'yaymail_shipping_tax_shipment_tracking' ), 100, 6 );
		add_action( 'YaymailAdvancedLocalPickupInstruction', array( $this, 'yaymail_advanced_local_pickup_instruction' ), 100, 6 );
		// Blocks
		add_action( 'YaymailFeaturedProducts', array( $this, 'yaymail_featured_products' ), 100, 6 );
		add_action( 'YaymailSimpleOffer', array( $this, 'yaymail_simple_offer' ), 100, 6 );
		add_action( 'YaymailSingleBanner', array( $this, 'yaymail_single_banner' ), 100, 6 );
	}

	public function yaymail_logo( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		ob_start();
		include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/Logo.php';
		$html = ob_get_contents();
		ob_end_clean();
		$html = do_shortcode( $html );
		// Replace shortcode cannot do_shortcode
		$reg  = '/\[yaymail.*?\]/m';
		$html = preg_replace( $reg, '', $html );
		echo wp_kses_post( $html );
	}

	public function yaymail_images( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		ob_start();
		include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/Images.php';
		$html = ob_get_contents();
		ob_end_clean();
		$html = do_shortcode( $html );
		// Replace shortcode cannot do_shortcode
		$reg  = '/\[yaymail.*?\]/m';
		$html = preg_replace( $reg, '', $html );
		echo wp_kses_post( $html );
	}

	public function yaymail_element_text( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		ob_start();
		include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/ElementText.php';
		$html = ob_get_contents();
		$html = do_shortcode( $html );
		ob_end_clean();
		// Replace shortcode cannot do_shortcode
		$reg                        = '/\[yaymail.*?\]/m';
		$html                       = preg_replace( $reg, '', $html );
		$allowed_html_tags          = wp_kses_allowed_html( 'post' );
		$allowed_html_tags['style'] = array();
		echo wp_kses( $html, $allowed_html_tags );
	}

	public function yaymail_button( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		ob_start();
		include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/Button.php';
		$html = ob_get_contents();
		ob_end_clean();
		$html = do_shortcode( $html );
		// Replace shortcode cannot do_shortcode
		$reg  = '/\[yaymail.*?\]/m';
		$html = preg_replace( $reg, '', $html );
		echo wp_kses_post( $html );
	}
	public function yaymail_title( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		ob_start();
		include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/Title.php';
		$html = ob_get_contents();
		ob_end_clean();
		$html = do_shortcode( $html );
		// Replace shortcode cannot do_shortcode
		$reg  = '/\[yaymail.*?\]/m';
		$html = preg_replace( $reg, '', $html );
		echo wp_kses_post( $html );
	}

	public function yaymail_social_icon( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		ob_start();
		include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/SocialIcon.php';
		$html = ob_get_contents();
		ob_end_clean();
		$html = do_shortcode( $html );
		// Replace shortcode cannot do_shortcode
		$reg  = '/\[yaymail.*?\]/m';
		$html = preg_replace( $reg, '', $html );
		echo wp_kses_post( $html );
	}

	public function yaymail_video( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		ob_start();
		include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/Video.php';
		$html = ob_get_contents();
		ob_end_clean();
		$html = do_shortcode( $html );
		// Replace shortcode cannot do_shortcode
		$reg  = '/\[yaymail.*?\]/m';
		$html = preg_replace( $reg, '', $html );
		echo wp_kses_post( $html );
	}

	public function yaymail_shipping_address( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		if ( ( isset( $args['order'] ) && 'SampleOrder' == $args['order'] ) || ( isset( $args['order'] ) && ! empty( $args['order'] ) && ! empty( $args['order']->get_formatted_shipping_address() ) ) ) {
			ob_start();
			include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/ShippingAddress.php';
			$html = ob_get_contents();
			ob_end_clean();
			$html = do_shortcode( $html );
			// Replace shortcode cannot do_shortcode
			$reg  = '/\[yaymail.*?\]/m';
			$html = preg_replace( $reg, '', $html );
			echo wp_kses_post( $html );
		}
	}

	public function yaymail_billing_address( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
			ob_start();
			include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/BillingAddress.php';
			$html = ob_get_contents();
			ob_end_clean();
			$html = do_shortcode( $html );
			// Replace shortcode cannot do_shortcode
			$reg  = '/\[yaymail.*?\]/m';
			$html = preg_replace( $reg, '', $html );
			echo wp_kses_post( $html );
	}

	public function yaymail_order_item( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		ob_start();
		include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/OrderItem.php';
		$html = ob_get_contents();
		ob_end_clean();
		$html = do_shortcode( $html );
		// Replace shortcode cannot do_shortcode
		$reg                        = '/\[yaymail.*?\]/m';
		$html                       = preg_replace( $reg, '', $html );
		$allowed_html_tags          = wp_kses_allowed_html( 'post' );
		$allowed_html_tags['style'] = array();
		echo wp_kses( $html, $allowed_html_tags );
	}

	public function yaymail_html_code( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		ob_start();
		include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/HTML.php';
		$html = ob_get_contents();
		ob_end_clean();
		$html = do_shortcode( $html );
		// Replace shortcode cannot do_shortcode
		$reg  = '/\[yaymail.*?\]/m';
		$html = preg_replace( $reg, '', $html );
		echo wp_kses_post( $html );
	}

	public function yaymail_image_list( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		ob_start();
		include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/ImageList.php';
		$html = ob_get_contents();
		ob_end_clean();
		$html = do_shortcode( $html );
		// Replace shortcode cannot do_shortcode
		$reg  = '/\[yaymail.*?\]/m';
		$html = preg_replace( $reg, '', $html );
		echo wp_kses_post( $html );
	}

	public function yaymail_image_box( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		ob_start();
		include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/ImageBox.php';
		$html = ob_get_contents();
		ob_end_clean();
		$html = do_shortcode( $html );
		// Replace shortcode cannot do_shortcode
		$reg  = '/\[yaymail.*?\]/m';
		$html = preg_replace( $reg, '', $html );
		echo wp_kses_post( $html );
	}

	public function yaymail_text_list( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		ob_start();
		include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/TextList.php';
		$html = ob_get_contents();
		ob_end_clean();
		$html = do_shortcode( $html );
		// Replace shortcode cannot do_shortcode
		$reg  = '/\[yaymail.*?\]/m';
		$html = preg_replace( $reg, '', $html );
		echo wp_kses_post( $html );
	}

	public function yaymail_order_item_download( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		ob_start();
		include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/OrderItemDownload.php';
		$html = ob_get_contents();
		ob_end_clean();
		$html = do_shortcode( $html );
		// Replace shortcode cannot do_shortcode
		$reg  = '/\[yaymail.*?\]/m';
		$html = preg_replace( $reg, '', $html );
		echo wp_kses_post( $html );
	}

	public function yaymail_hook( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		ob_start();
		include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/Hook.php';
		$html = ob_get_contents();
		ob_end_clean();
		$html = do_shortcode( $html );
		// Replace shortcode cannot do_shortcode
		$reg                        = '/\[yaymail.*?\]/m';
		$html                       = preg_replace( $reg, '', $html );
		$allowed_html_tags          = wp_kses_allowed_html( 'post' );
		$allowed_html_tags['style'] = array();
		echo wp_kses( $html, $allowed_html_tags );
	}

	public function yaymail_space( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		ob_start();
		include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/Space.php';
		$html = ob_get_contents();
		ob_end_clean();
		$html = do_shortcode( $html );
		// Replace shortcode cannot do_shortcode
		$reg  = '/\[yaymail.*?\]/m';
		$html = preg_replace( $reg, '', $html );
		echo wp_kses_post( $html );
	}

	public function yaymail_divider( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		ob_start();
		include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/Divider.php';
		$html = ob_get_contents();
		ob_end_clean();
		$html = do_shortcode( $html );
		// Replace shortcode cannot do_shortcode
		$reg  = '/\[yaymail.*?\]/m';
		$html = preg_replace( $reg, '', $html );
		echo wp_kses_post( $html );
	}

	public function yaymail_one_column( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		ob_start();
		include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/OneColumn.php';
		$html = ob_get_contents();
		ob_end_clean();
		$html = do_shortcode( $html );
		// Replace shortcode cannot do_shortcode
		$reg                        = '/\[yaymail.*?\]/m';
		$html                       = preg_replace( $reg, '', $html );
		$allowed_html_tags          = wp_kses_allowed_html( 'post' );
		$allowed_html_tags['style'] = array();
		$allowed_html_tags          = Helper::customAllowedHTMLTags( array( 'background' => true ) );
		echo wp_kses( $html, $allowed_html_tags );
	}

	public function yaymail_two_column( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		ob_start();
		include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/TwoColumn.php';
		$html = ob_get_contents();
		ob_end_clean();
		$html = do_shortcode( $html );
		// Replace shortcode cannot do_shortcode
		$reg                        = '/\[yaymail.*?\]/m';
		$html                       = preg_replace( $reg, '', $html );
		$allowed_html_tags          = wp_kses_allowed_html( 'post' );
		$allowed_html_tags['style'] = array();
		$allowed_html_tags          = Helper::customAllowedHTMLTags( array( 'background' => true ) );
		echo wp_kses( $html, $allowed_html_tags );
	}

	public function yaymail_three_column( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		ob_start();
		include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/ThreeColumn.php';
		$html = ob_get_contents();
		ob_end_clean();
		$html = do_shortcode( $html );
		// Replace shortcode cannot do_shortcode
		$reg                        = '/\[yaymail.*?\]/m';
		$html                       = preg_replace( $reg, '', $html );
		$allowed_html_tags          = wp_kses_allowed_html( 'post' );
		$allowed_html_tags['style'] = array();
		$allowed_html_tags          = Helper::customAllowedHTMLTags( array( 'background' => true ) );
		echo wp_kses( $html, $allowed_html_tags );
	}

	public function yaymail_four_column( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		ob_start();
		include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/FourColumn.php';
		$html = ob_get_contents();
		ob_end_clean();
		$html = do_shortcode( $html );
		// Replace shortcode cannot do_shortcode
		$reg                        = '/\[yaymail.*?\]/m';
		$html                       = preg_replace( $reg, '', $html );
		$allowed_html_tags          = wp_kses_allowed_html( 'post' );
		$allowed_html_tags['style'] = array();
		$allowed_html_tags          = Helper::customAllowedHTMLTags( array( 'background' => true ) );
		echo wp_kses( $html, $allowed_html_tags );
	}

	public function yaymail_tracking_item( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		if ( class_exists( 'WC_Shipment_Tracking_Actions' ) || class_exists( 'WC_Advanced_Shipment_Tracking_Actions' ) || class_exists( 'AST_PRO_Install' ) ) {
			$order = isset( $args['order'] ) ? $args['order'] : null;
			if ( isset( $order ) && 'SampleOrder' !== $order ) {
				$order_id = is_callable( array( $order, 'get_id' ) ) ? $order->get_id() : $order->id;
				if ( class_exists( 'WC_Shipment_Tracking_Actions' ) && ! class_exists( 'WC_Advanced_Shipment_Tracking_Actions' ) ) {
					$sta            = \WC_Shipment_Tracking_Actions::get_instance();
					$tracking_items = $sta->get_tracking_items( $order_id, true );
				}
				if ( class_exists( 'WC_Advanced_Shipment_Tracking_Actions' ) ) {
					$ast            = \WC_Advanced_Shipment_Tracking_Actions::get_instance();
					$tracking_items = $ast->get_tracking_items( $order_id );
				}
				if ( class_exists( 'AST_PRO_Install' ) ) {
					$ast            = \AST_Pro_Actions::get_instance();
					$tracking_items = $ast->get_tracking_items( $order_id );
				}
			}
			if ( ( isset( $tracking_items ) && is_array( $tracking_items ) && 0 < count( $tracking_items ) ) || 'SampleOrder' === $order ) {
				ob_start();
				include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/ItemsShipment.php';
				$html = ob_get_contents();
				ob_end_clean();
				$html = do_shortcode( $html );
				// Replace shortcode cannot do_shortcode
				// $reg  = '/\[yaymail.*?\]/m';
				// $html = preg_replace( $reg, '', $html );
				// echo ( $html );

				$reg                        = '/\[yaymail.*?\]/m';
				$html                       = preg_replace( $reg, '', $html );
				$allowed_html_tags          = wp_kses_allowed_html( 'post' );
				$allowed_html_tags['style'] = array();
				echo wp_kses( $html, $allowed_html_tags );
			}
		} elseif ( class_exists( 'PH_Shipment_Tracking_API_Manager' ) ) {
			$order    = $args['order'];
			$order_id = is_callable( array( $order, 'get_id' ) ) ? $order->get_id() : $order->id;
			if ( ! class_exists( 'WF_Tracking_Admin' ) ) {
				include_once plugin_dir_url( __FILE__ ) . '/includes/class-wf-tracking-admin.php';
			}
			$tracking_admin_obj   = new \WF_Tracking_Admin();
			$shipment_source_data = $tracking_admin_obj->get_shipment_source_data( $order_id );
			if ( ! empty( $shipment_source_data['shipment_id_cs'] ) || ! empty( $shipment_source_data['shipping_service'] ) ) {
				ob_start();
				include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/ItemsShipment.php';
				$html = ob_get_contents();
				ob_end_clean();
				$html = do_shortcode( $html );
				// Replace shortcode cannot do_shortcode
				$reg  = '/\[yaymail.*?\]/m';
				$html = preg_replace( $reg, '', $html );
				echo wp_kses_post( $html );
			}
		} elseif ( is_plugin_active( 'wc-chitchats-shipping-pro/wc-chitchats-shipping-pro.php' ) ) {
				$order = $args['order'];
			if ( $order && $order->get_meta( 'wc-chitchats-shipping_shipments' ) ) {
				ob_start();
				include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/ItemsShipment.php';
				$html = ob_get_contents();
				ob_end_clean();
				$html = do_shortcode( $html );
				// Replace shortcode cannot do_shortcode
				$reg  = '/\[yaymail.*?\]/m';
				$html = preg_replace( $reg, '', $html );
				echo wp_kses_post( $html );
			}
		} elseif ( class_exists( 'TrackingMore' ) ) {
			$order    = $args['order'];
			$order_id = is_callable( array( $order, 'get_id' ) ) ? $order->get_id() : $order->id;
			if ( ! empty( $order_id ) ) {
				ob_start();
				include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/ItemsShipment.php';
				$html = ob_get_contents();
				ob_end_clean();
				$html = do_shortcode( $html );
				// Replace shortcode cannot do_shortcode
				$reg  = '/\[yaymail.*?\]/m';
				$html = preg_replace( $reg, '', $html );
				echo wp_kses_post( $html );
			}
		}
	}

	public function yaymail_items_shipment( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		if ( class_exists( 'AST_PRO_Install' ) ) {
			$order = $args['order'];
			if ( isset( $order ) && 'SampleOrder' !== $order ) {
					$order_id = is_callable( array( $order, 'get_id' ) ) ? $order->get_id() : $order->id;
				if ( class_exists( 'AST_PRO_Install' ) ) {
					$ast            = \AST_Pro_Actions::get_instance();
					$tracking_items = $ast->get_tracking_items( $order_id );
				}
			}
			if ( 0 < count( $tracking_items ) || 'SampleOrder' === $order ) {
				ob_start();
				include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/ItemsShipment.php';
				$html = ob_get_contents();
				ob_end_clean();
				$html = do_shortcode( $html );
				// Replace shortcode cannot do_shortcode
				$reg                        = '/\[yaymail.*?\]/m';
				$html                       = preg_replace( $reg, '', $html );
				$allowed_html_tags          = wp_kses_allowed_html( 'post' );
				$allowed_html_tags['style'] = array();
				echo wp_kses( $html, $allowed_html_tags );
			}
		}
	}

	public function yaymail_additional_order_details( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		if ( class_exists( 'WC_Admin_Custom_Order_Fields' ) ) {
			$order               = $args['order'];
			$order_id            = $order->get_id();
			$order_fields        = array();
			$order               = $order_id ? wc_get_order( $order_id ) : null;
			$custom_order_fields = get_option( 'wc_admin_custom_order_fields' );
			if ( ! is_array( $custom_order_fields ) ) {
				$custom_order_fields = array();
			}
			foreach ( $custom_order_fields as $key => $field ) {
				$order_field = new \WC_Custom_Order_Field( $key, $field );
				$have_value  = false;
				if ( $order instanceof \WC_Order ) {
					$set_value = false;
					$value     = '';
					if ( metadata_exists( 'post', $order_id, $order_field->get_meta_key() ) ) {
						$set_value = true;
						$value     = $order->get_meta( $order_field->get_meta_key() );
					}
					if ( $set_value ) {
						$order_field->set_value( $value );
						$have_value = true;
					}
				}
				if ( $have_value ) {
					$order_fields[ $key ] = $order_field;
				}
			}
			if ( ! empty( $order_fields ) ) {
				ob_start();
				include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/AdditionalOrderDetails.php';
				$html = ob_get_contents();
				ob_end_clean();
				$html = do_shortcode( $html );
			} else {
				$html = '';
			}
			// Replace shortcode cannot do_shortcode
			$reg  = '/\[yaymail.*?\]/m';
			$html = preg_replace( $reg, '', $html );
			echo wp_kses_post( $html );
		}
	}

	public function yaymail_event_on_list( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		if ( class_exists( 'EventON' ) ) {
			ob_start();
			include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/EventOnList.php';
			$html = ob_get_contents();
			ob_end_clean();
			$html = do_shortcode( $html );
			// Replace shortcode cannot do_shortcode
			$reg  = '/\[yaymail.*?\]/m';
			$html = preg_replace( $reg, '', $html );
			echo wp_kses_post( $html );
			echo "<script>
			let table = document.getElementById('shrief-table');
			table.addEventListener('click',function(){
				console.log('1234');
			})
			</script>";
		}
	}

	public function yaymail_tracking_details( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		if ( class_exists( 'YITH_WooCommerce_Order_Tracking_Premium' ) && isset( $args['order'] ) ) {
			$data          = \YITH_Tracking_Data::get( $args['order'] );
			$tracking_code = $data->get_tracking_code();
			if ( $tracking_code ) {
				ob_start();
				include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/TrackingDetails.php';
				$html = ob_get_contents();
				ob_end_clean();
				$html = do_shortcode( $html );
				// Replace shortcode cannot do_shortcode
				$reg  = '/\[yaymail.*?\]/m';
				$html = preg_replace( $reg, '', $html );
				echo wp_kses_post( $html );
			}
		}
	}

	public function yaymail_software_add_on( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		if ( class_exists( 'WC_Software' ) ) {
			ob_start();
			include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/SoftwareAddOn.php';
			$html = ob_get_contents();
			ob_end_clean();
			$html = do_shortcode( $html );
			// Replace shortcode cannot do_shortcode
			$reg  = '/\[yaymail.*?\]/m';
			$html = preg_replace( $reg, '', $html );
			echo wp_kses_post( $html );
		}
	}

	public function yaymail_fooevents_ticket( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		if ( class_exists( 'FooEvents' ) ) {
			if ( isset( $args['order'] ) && ! empty( $args['order'] ) ) {
				$order                         = $args['order'];
				$WooCommerceEventsOrderTickets = get_post_meta( $order->get_id(), 'WooCommerceEventsOrderTickets', true );
				if ( ! empty( $WooCommerceEventsOrderTickets ) ) {
					ob_start();
					include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/FooEventsTicket.php';
					$html = ob_get_contents();
					ob_end_clean();
					$html = do_shortcode( $html );
					// Replace shortcode cannot do_shortcode
					$reg  = '/\[yaymail.*?\]/m';
					$html = preg_replace( $reg, '', $html );
					echo wp_kses_post( $html );
				}
			}
		}
	}


	public function yaymail_shipping_tax_shipment_tracking( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		if ( class_exists( 'WC_Connect_Loader' ) ) {
			if ( isset( $args['order'] ) && ! empty( $args['order'] ) && is_object( $args['order'] ) ) {
				$order = $args['order'];
				// $id     = \WC_Connect_Compatibility::instance()->get_order_id( $order );
				// $labels = get_post_meta( (int) $id, 'wc_connect_labels', true );
				$labels = $order->get_meta( 'wc_connect_labels', true );
				if ( ! empty( $labels ) ) {
					ob_start();
					include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/ShippingTaxShipmentTracking.php';
					$html = ob_get_contents();
					ob_end_clean();
					$html = do_shortcode( $html );
					// Replace shortcode cannot do_shortcode
					$reg  = '/\[yaymail.*?\]/m';
					$html = preg_replace( $reg, '', $html );
					echo wp_kses_post( $html );
				}
			}
			if ( isset( $args['order'] ) && ! empty( $args['order'] ) && 'SampleOrder' == $args['order'] ) {
					ob_start();
					include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/ShippingTaxShipmentTracking.php';
					$html = ob_get_contents();
					ob_end_clean();
					$html = do_shortcode( $html );
					// Replace shortcode cannot do_shortcode
					$reg  = '/\[yaymail.*?\]/m';
					$html = preg_replace( $reg, '', $html );
					echo wp_kses_post( $html );
			}
		}
	}

	public function yaymail_advanced_local_pickup_instruction( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		if ( class_exists( 'Woocommerce_Local_Pickup' ) ) {
			ob_start();
			include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/AdvancedLocalPickupInstruction.php';
			$html = ob_get_contents();
			ob_end_clean();
			$html = do_shortcode( $html );
			// Replace shortcode cannot do_shortcode
			$reg  = '/\[yaymail.*?\]/m';
			$html = preg_replace( $reg, '', $html );
			echo wp_kses_post( $html );
		}
	}

	public function yaymail_featured_products( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		ob_start();
		include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/Blocks/FeaturedProducts.php';
		$html = ob_get_contents();
		ob_end_clean();
		$html = do_shortcode( $html );
		// Replace shortcode cannot do_shortcode
		$reg  = '/\[yaymail.*?\]/m';
		$html = preg_replace( $reg, '', $html );
		echo wp_kses_post( $html );
	}

	public function yaymail_simple_offer( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		ob_start();
		include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/Blocks/SimpleOffer.php';
		$html = ob_get_contents();
		ob_end_clean();
		$html = do_shortcode( $html );
		// Replace shortcode cannot do_shortcode
		$reg  = '/\[yaymail.*?\]/m';
		$html = preg_replace( $reg, '', $html );
		echo wp_kses_post( $html );
	}
	public function yaymail_single_banner( $args, $attrs, $general_attrs, $id, $postID = '', $isInColumns = false ) {
		ob_start();
		include YAYMAIL_PLUGIN_PATH . 'includes/Templates/Elements/YayMail/Blocks/SingleBanner.php';
		$html = ob_get_contents();
		ob_end_clean();
		$html = do_shortcode( $html );
		// Replace shortcode cannot do_shortcode
		$reg  = '/\[yaymail.*?\]/m';
		$html = preg_replace( $reg, '', $html );
		echo wp_kses_post( $html );
	}
}
