<?php

namespace YayMail\MailBuilder;

use YayMail\Page\Source\CustomPostType;
use YayMail\Ajax;
use YayMail\MailBuilder\PIPTemplate;

defined( 'ABSPATH' ) || exit;
/**
 * Settings Page
 */
class WooTemplate {

	protected static $instance    = null;
	private $templateAccount      = null;
	private $templateSubscription = null;
	private $automatewoo_info     = null;

	private $automatewoo_referrals_email_content = null;
	private $trackShipArgs                       = null;
	private $templateGermanizedForWC             = null;
	private $FUE_Sending_Email_Variables         = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
			self::$instance->doHooks();
		}

		return self::$instance;
	}

	private function doHooks() {
		$this->templateAccount         = array( 'customer_new_account', 'customer_new_account_activation', 'customer_reset_password' );
		$this->templateGermanizedForWC = array( 'sab_simple_invoice', 'sab_cancellation_invoice', 'sab_packing_slip', 'sab_document_admin', 'sab_document' );
		add_filter( 'storeabill_get_template', array( $this, 'storeabill_get_template' ), 100, 5 );
		add_filter( 'wc_get_template', array( $this, 'getTemplateMail' ), 99999, 5 );

		if ( has_filter( 'YaymailCreateFollowUpTemplates' ) ) {
			add_action( 'fue_before_variable_replacements', array( $this, 'register_variable_replacements' ), 100, 4 );
			add_filter( 'fue_before_sending_email', array( $this, 'getFollowUpTemplates' ), 100, 3 );
		}

		if ( class_exists( 'CWG_Instock_Notifier' ) ) {
			$postID_notifier_instock_mail = CustomPostType::postIDByTemplate( 'notifier_instock_mail' );
			if ( get_post_meta( $postID_notifier_instock_mail, '_yaymail_status', true ) ) {
				add_filter( 'cwginstock_message', array( $this, 'cwginstock_message' ), 100, 2 );
				add_action( 'admin_action_cwginstock-sendmail', array( $this, 'action_remove_header_footer' ), 9 );
				add_action( 'cwginstock_notify_process', array( $this, 'action_remove_header_footer' ), 9 );
				add_action( 'cwginstocknotifier_handle_action_send_mail', array( $this, 'action_remove_header_footer' ), 9 );
			}

			$postID_notifier_subscribe_mail = CustomPostType::postIDByTemplate( 'notifier_subscribe_mail' );
			if ( get_post_meta( $postID_notifier_subscribe_mail, '_yaymail_status', true ) ) {
				add_filter( 'cwgsubscribe_message', array( $this, 'cwgsubscribe_message' ), 100, 2 );
				add_action( 'cwginstock_after_insert_subscriber', array( $this, 'action_remove_header_footer' ), 9 );
			}
		}

		// change german market template dir
		$this->yaymail_get_german_market_templates();

		// Support custom order status
		if ( class_exists( 'Alg_WC_Custom_Order_Statuses' ) ) {
			add_action( 'woocommerce_order_status_changed', array( $this, 'send_email_on_order_status' ), 10, 4 );
		}

		if ( class_exists( 'WC_PIP_Loader' ) && class_exists( 'YayMailWooPrintInvoices\\templateDefault\\DefaultInvoice' ) && class_exists( 'YayMailWooPrintInvoices\\templateDefault\\DefaultPickList' ) ) {
			PIPTemplate::handle_trigger();
		}
		add_filter( 'retrieve_password_message', array( $this, 'admin_reset_password' ), 100, 4 );
		add_action( 'automatewoo_before_action_run', array( $this, 'automatewoo_before_action_run' ), 10 );
		add_filter( 'automatewoo/referrals/invite_email/mailer', array( $this, 'automatewoo_invite_email' ), 100, 2 );

		if ( class_exists( 'WCFM' ) ) {
			$WCFMWooFM_Template = CustomPostType::postIDByTemplate( 'WCFMWooFM_Template' );
			if ( get_post_meta( $WCFMWooFM_Template, '_yaymail_status', true ) ) {
				global $WCFM;
				remove_action( 'wcfm_email_content_wrapper', array( $WCFM, 'wcfm_email_content_wrapper' ), 10 );
				add_filter( 'wcfm_email_content_wrapper', array( &$this, 'wcfm_email_content_wrapper' ), 1, 2 );
			}
		}

		add_filter( 'wpml_translate_single_string', array( $this, 'yaymail_ywces_new_template' ), 1, 3 );

		if ( class_exists( 'WPDesk\\ShopMagic\\Plugin' ) && function_exists( 'YaymailAddonShopMagicForWoocommerce\\init' ) ) {
			add_filter( 'shopmagic/core/action/send_mail/sending', array( $this, 'yaymail_addon_shopmagic_send_mail' ) );
			//add_action( 'shopmagic/core/initialized/v2', array( $this, 'shopmagic_do_action_external_plugins_access' ), 10, 1 );
			add_action( 'shopmagic/core/action/before_execution', array( $this, 'shopmagic_before_send_mail' ), 10, 3 );
		}

		if ( class_exists( 'CARTFLOWS_CA_Loader' ) && function_exists( 'YayMailAddonWcCartAbandonmentRecovery\\init' ) ) {
			add_filter( 'woo_ca_recovery_email_data', array( $this, 'yaymail_addon_ca_recovery_send_mail' ), 10, 1 );
		}

		if ( 'yes' === get_option( 'woocommerce_gzd_display_emails_product_item_desc' ) && class_exists( 'WooCommerce_Germanized' ) ) {
			add_filter( 'woocommerce_order_item_name', array( $this, 'wc_gzd_cart_product_item_desc' ), 10, 2 );
		}

	}

	public function wc_gzd_cart_product_item_desc( $title, $cart_item, $cart_item_key = '' ) {
		$product_desc = '';
		$echo         = false;

		if ( is_array( $title ) && isset( $title['data'] ) ) {
			$cart_item     = $title;
			$cart_item_key = $cart_item;
			$title         = '';
			$echo          = true;
		} elseif ( is_numeric( $title ) && wc_gzd_is_checkout_action() && is_a( $cart_item, 'WC_Order_Item_Product' ) ) {
			$echo          = true;
			$cart_item_key = $title;
			$title         = '';
		}

		if ( is_a( $cart_item, 'WC_Order_Item_Product' ) ) {
			$gzd_item = wc_gzd_get_order_item( $cart_item );
			$product  = $cart_item->get_product();

			if ( ! empty( $gzd_item ) ) {
				$product_desc = $gzd_item->get_cart_description();
			} elseif ( ! empty( $product ) && wc_gzd_get_gzd_product( $product )->get_mini_desc() ) {
				$product_desc = wc_gzd_get_gzd_product( $product )->get_formatted_cart_description();
			}
		} elseif ( isset( $cart_item['data'] ) ) {
			$product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

			if ( is_a( $product, 'WC_Product' ) && wc_gzd_get_product( $product )->get_cart_description() ) {
				$product_desc = wc_gzd_get_product( $product )->get_formatted_cart_description();
			}
		} elseif ( isset( $cart_item['item_desc'] ) ) {
			$product_desc = $cart_item['item_desc'];
		}

		if ( ! empty( $product_desc ) ) {
			$title .= '<div class="wc-gzd-cart-info wc-gzd-item-desc item-desc">' . do_shortcode( $product_desc ) . '</div>';
		}

		if ( $echo ) {
			echo wp_kses_post( $title );
		}

		return wp_kses_post( $title );
	}


	public function yaymail_addon_ca_recovery_send_mail( $email_data ) {
		$ca_recovery_template_id = $email_data->email_template_id;

		if ( empty( $ca_recovery_template_id ) ) {
			return $email_data;
		}
		$yaymail_template_id = 'YaymailWcCaRecovery_' . $ca_recovery_template_id;

		if ( ! empty( $yaymail_template_id ) ) {
			$postID          = null;
			$postID          = CustomPostType::postIDByTemplate( $yaymail_template_id );
			$template_status = get_post_meta( $postID, '_yaymail_status', true );
		}

		if ( $template_status && ! empty( $postID ) ) {
			$active_template = file_exists( YAYMAIL_PLUGIN_PATH . 'views/templates/single-follow-up-mail-template.php' ) ? YAYMAIL_PLUGIN_PATH . 'views/templates/single-follow-up-mail-template.php' : false;

			$args['email'] = $email_data;
			$template      = $yaymail_template_id;
			if ( $active_template ) {
				ob_start();
				include $active_template;
				$template_body = ob_get_contents();
				ob_end_clean();
			}
		}

		if ( ! empty( $template_body ) ) {
			$email_data->email_body = $template_body;
		}

		return $email_data;

	}

	public function shopmagic_before_send_mail( $action, $automation, $event ) {
		$automation_id = $automation->get_id();

		if ( empty( $automation_id ) ) {
			return;
		}

		$shopmagic_data = array(
			'automation' => $automation,
			'action'     => $action,
		);

		session_start();

		$_SESSION['shopmagic_data'] = $shopmagic_data;

	}

	public function shopmagic_do_action_external_plugins_access( $external_plugins_access ) {
		$external_plugins_access->add_template_resolver( new \ShopMagicVendor\WPDesk\View\Resolver\DirResolver( YAYMAIL_ADDON_SHOP_MAGIC_FOR_WC_PLUGIN_PATH . 'views/Template' ) );
	}

	public function yaymail_addon_shopmagic_send_mail( $sending_email ) {
		if ( ! $sending_email->get_email()->is_html() ) {
			return $sending_email;
		}

		$shopmagic_automation = $sending_email->get_automation();
		if ( method_exists( $shopmagic_automation, 'get_id' ) ) {
			$shopmagic_template_id = $shopmagic_automation->get_id();
		}

		if ( isset( $shopmagic_template_id ) ) {
			$shopmagic_template_name = 'ShopMagic_' . $shopmagic_template_id;
		}

		if ( ! empty( $shopmagic_template_name ) ) {
			$postID          = null;
			$postID          = CustomPostType::postIDByTemplate( $shopmagic_template_name );
			$template_status = get_post_meta( $postID, '_yaymail_status', true );
		}

		if ( $template_status && ! empty( $postID ) ) {
			$active_template = file_exists( YAYMAIL_PLUGIN_PATH . 'views/templates/single-follow-up-mail-template.php' ) ? YAYMAIL_PLUGIN_PATH . 'views/templates/single-follow-up-mail-template.php' : false;

			$args['email']      = $sending_email->get_email();
			$args['automation'] = $sending_email->get_automation();
			$template           = $shopmagic_template_name;
			ob_start();
			include $active_template;
			$template_body = ob_get_contents();
			ob_end_clean();
		}

		if ( ! empty( $template_body ) ) {
			$sending_email->email->message = $template_body;
		}

		return $sending_email;
	}
	public function yaymail_ywces_new_template( $mail_body_content, $admin_mail_body_text, $mail_body_text ) {
		if ( class_exists( 'YayMailYITHWooCouponEmailSystem\templateDefault\DefaultCouponEmailSystem' ) && class_exists( 'YITH_WC_Coupon_Email_System' ) && false !== strpos( $mail_body_text, 'ywces_mailbody_' ) ) {
			$mail_body_text_explode = explode( 'ywces_mailbody_', $mail_body_text );
			$email_template_type    = $mail_body_text_explode[1];
			$template               = 'YWCES_' . $email_template_type;
			$postID                 = CustomPostType::postIDByTemplate( $template );
			if ( $postID ) {
				if ( get_post_meta( $postID, '_yaymail_status', true ) && ! empty( get_post_meta( $postID, '_yaymail_elements', true ) ) ) {
					$check_YWCES    = true;
					$templateActive = file_exists( YAYMAIL_PLUGIN_PATH . 'views/templates/single-mail-template.php' ) ? YAYMAIL_PLUGIN_PATH . 'views/templates/single-mail-template.php' : false;
					ob_start();
					include $templateActive;
					$template_body = ob_get_contents();
					// Replace newline (\n) in a string to YAYMAIL_YWCES_YAY
					$template_body = str_replace( "\n", 'YAYMAIL_YWCES_YAY', $template_body );
					ob_end_clean();
					return $template_body;
				}
			}
		}

		return $mail_body_content;
	}

	public function wcfm_email_content_wrapper( $content_body, $email_heading ) {
		$template       = 'WCFMWooFM_Template';
		$postID         = CustomPostType::postIDByTemplate( 'WCFMWooFM_Template' );
		$templateActive = file_exists( YAYMAIL_PLUGIN_PATH . 'views/templates/single-follow-up-mail-template.php' ) ? YAYMAIL_PLUGIN_PATH . 'views/templates/single-follow-up-mail-template.php' : false;
		$args           = array(
			'order'         => null,
			'content_body'  => $content_body,
			'email_heading' => $email_heading,
		);
		ob_start();
		include $templateActive;
		$template_body = ob_get_contents();
		ob_end_clean();
		return $template_body;
	}
	public function automatewoo_invite_email( $mailer, $invite_email ) {
		if ( defined( 'YAYMAIL_ADDON_AUTOMATEWOO' ) && ! empty( YAYMAIL_ADDON_AUTOMATEWOO ) ) {
			$template        = 'AutomateWoo_Referrals_Email';
			$postID          = CustomPostType::postIDByTemplate( $template );
			$template_status = get_post_meta( $postID, '_yaymail_status', true );
			if ( $template_status ) {
				$templateActive = file_exists( YAYMAIL_PLUGIN_PATH . 'views/templates/single-follow-up-mail-template.php' ) ? YAYMAIL_PLUGIN_PATH . 'views/templates/single-follow-up-mail-template.php' : false;
				ob_start();
				include $templateActive;
				$template_body = ob_get_contents();
				ob_end_clean();
				if ( '' !== $template_body ) {
					$template_body                             = $invite_email->replace_variables( $template_body );
					$this->automatewoo_referrals_email_content = $template_body;
				}
				add_filter(
					'woocommerce_mail_content',
					function( $email_content ) {
						return $this->automatewoo_referrals_email_content;
					},
					100,
					1
				);
			}
		}
		return $mailer;
	}


	public function automatewoo_before_action_run( $action ) {
		if ( defined( 'YAYMAIL_ADDON_AUTOMATEWOO' ) && ! empty( YAYMAIL_ADDON_AUTOMATEWOO ) ) {
			$action_content         = $action->get_option_raw( 'email_content' );
			$this->automatewoo_info = $action;
			$template               = 'AutomateWoo_' . $action->workflow->get_id();
			$postID                 = CustomPostType::postIDByTemplate( $template );
			$template_status        = get_post_meta( $postID, '_yaymail_status', true );
			if ( $template_status ) {
				add_filter(
					'woocommerce_mail_content',
					function( $html ) {
						$workflow       = $this->automatewoo_info->workflow;
						$this->workflow = $workflow;
						$template       = 'AutomateWoo_' . $workflow->get_id();
						$postID         = CustomPostType::postIDByTemplate( $template );
						$templateActive = file_exists( YAYMAIL_PLUGIN_PATH . 'views/templates/single-follow-up-mail-template.php' ) ? YAYMAIL_PLUGIN_PATH . 'views/templates/single-follow-up-mail-template.php' : false;
						$raw_data       = $workflow->data_layer()->get_raw_data();
						$args           = array(
							'order'        => isset( $raw_data['order'] ) ? $raw_data['order'] : null,
							'subscription' => isset( $raw_data['subscription'] ) ? $raw_data['subscription'] : null,
							'email'        => '',
							'workflow'     => $workflow,
						);
						ob_start();
						include $templateActive;
						$template_body = ob_get_contents();
						ob_end_clean();
						if ( '' !== $template_body ) {
							$template_body = $workflow->variable_processor()->process_field( $template_body, true );
							$email         = new \AutomateWoo\Workflow_Email( $workflow, '', '', $template_body );
							$get_mailer    = $email->get_mailer();
							if ( $workflow->is_tracking_enabled() ) {
								$template_body = $get_mailer->inject_tracking_pixel( $template_body );
								$template_body = $get_mailer->replace_urls_in_content( $template_body );
							}
							$template_body = $get_mailer->style_inline( $template_body );
							return $template_body;
						}
						return $html;
					},
					10
				);
			}
		}
	}

	public function send_email_on_order_status( $order_id, $status_from, $status_to, $order ) {
		$postID          = CustomPostType::postIDByTemplate( 'wc-' . $status_to, $order );
		$template_status = get_post_meta( $postID, '_yaymail_status', true );
		if ( $template_status ) {
			global $wp_filter;
			$action = isset( $wp_filter['woocommerce_order_status_changed'] ) ? $wp_filter['woocommerce_order_status_changed']->callbacks : array();
			if ( ! empty( $action ) ) {

				foreach ( $action as $key => $value ) {
					foreach ( $value as $key1 => $value1 ) {
						if ( is_array( $value1['function'] ) && isset( $value1['function']['1'] ) ) {
							if ( 'send_email_on_order_status_changed' === $value1['function']['1'] ) {
								remove_action( 'woocommerce_order_status_changed', $key1, PHP_INT_MAX, 4 );
							}
						}
					}
				}
			}

			$alg_orders_custom_statuses_array          = alg_get_custom_order_statuses_from_cpt();
			$alg_orders_custom_statuses_with_id_array  = alg_get_custom_order_statuses_from_cpt( true, true );
			$email_address                             = '';
			$bcc_email_address                         = '';
			$email_subject                             = '';
			$email_heading                             = '';
			$emails_statuses                           = get_option( 'alg_orders_custom_statuses_emails_statuses', array() );
			$is_global_emails_enabled                  = get_option( 'alg_orders_custom_statuses_emails_enabled', 'no' );
			$alg_send_emails                           = false;
			$alg_orders_custom_statuses_emails_enabled = '';

			if ( 'yes' === $is_global_emails_enabled ) {
				if ( in_array( 'wc-' . $status_to, $emails_statuses, true ) || ( in_array( 'wc-' . $status_to, array_keys( $alg_orders_custom_statuses_array ), true ) ) ) {
					$alg_send_emails = true;
					// Options.
					$email_address     = get_option( 'alg_orders_custom_statuses_emails_address', '' );
					$bcc_email_address = get_option( 'alg_orders_custom_statuses_bcc_emails_address', '' );
					$email_subject     = get_option(
						'alg_orders_custom_statuses_emails_subject',
						// translators: WC Order Number, New Status & Date on which the order was placed.
						sprintf( __( '[%1$s] Order #%2$s status changed to %3$s - %4$s', 'custom-order-statuses-woocommerce' ), '{site_title}', '{order_number}', '{status_to}', '{order_date}' )
					);
					$email_heading = get_option(
						'alg_orders_custom_statuses_emails_heading',
						/* translators: $s: status to */
						sprintf( __( 'Order status changed to %s', 'custom-order-statuses-woocommerce' ), '{status_to}' )
					);
				}
			}
			// For the emails set at custom status level(Individual level).
			if ( ! empty( $alg_orders_custom_statuses_with_id_array ) ) {
				// Get custom meta box values of custom post status.
				if ( isset( $alg_orders_custom_statuses_with_id_array[ $status_to ] ) ) {
					$status_post_id                            = $alg_orders_custom_statuses_with_id_array[ $status_to ];
					$alg_orders_custom_statuses_emails_enabled = get_post_meta( $status_post_id, 'alg_orders_custom_statuses_emails_enabled', true );
					if ( $status_post_id > 0 && 'yes' === $alg_orders_custom_statuses_emails_enabled ) {
						$alg_send_emails = true;
						if ( ! empty( get_post_meta( $status_post_id, 'alg_orders_custom_statuses_emails_address', true ) ) ) {
							$email_address = get_post_meta( $status_post_id, 'alg_orders_custom_statuses_emails_address', true );
						}
						if ( ! empty( get_post_meta( $status_post_id, 'alg_orders_custom_statuses_bcc_emails_address', true ) ) ) {
							$bcc_email_address = get_post_meta( $status_post_id, 'alg_orders_custom_statuses_bcc_emails_address', true );
						}
						if ( ! empty( get_post_meta( $status_post_id, 'alg_orders_custom_statuses_emails_subject', true ) ) ) {
							$email_subject = get_post_meta( $status_post_id, 'alg_orders_custom_statuses_emails_subject', true );
						}
						if ( ! empty( get_post_meta( $status_post_id, 'alg_orders_custom_statuses_emails_heading', true ) ) ) {
							$email_heading = get_post_meta( $status_post_id, 'alg_orders_custom_statuses_emails_heading', true );
						}
					}
				}
			}

			if ( 'yes' !== $is_global_emails_enabled && 'yes' !== $alg_orders_custom_statuses_emails_enabled ) {
				return;
			}

			$woo_statuses        = wc_get_order_statuses();
			$replace_status_from = isset( $alg_orders_custom_statuses_array[ 'wc-' . $status_from ] ) ? $alg_orders_custom_statuses_array[ 'wc-' . $status_from ] : $woo_statuses[ 'wc-' . $status_from ];
			$replace_status_to   = isset( $alg_orders_custom_statuses_array[ 'wc-' . $status_to ] ) ? $alg_orders_custom_statuses_array[ 'wc-' . $status_to ] : $woo_statuses[ 'wc-' . $status_to ];

			// Replaced values.
			$replaced_values = array(
				'{order_id}'         => $order_id,
				'{order_number}'     => $order->get_order_number(),
				'{order_date}'       => gmdate( get_option( 'date_format' ), strtotime( $order->get_date_created() ) ),
				'{site_title}'       => wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ),
				'{status_from}'      => $replace_status_from,
				'{status_to}'        => $replace_status_to,
				'{first_name}'       => $order->get_billing_first_name(),
				'{last_name}'        => $order->get_billing_last_name(),
				'{billing_address}'  => $order->get_formatted_billing_address(),
				'{shipping_address}' => $order->get_formatted_shipping_address(),
			);

			$email_replaced_values = array(
				'{customer_email}' => $order->get_billing_email(),
				'{admin_email}'    => get_option( 'admin_email' ),
			);

			// Final processing.
			$email_address = ( '' === $email_address ? get_option( 'admin_email' ) : str_replace( array_keys( $email_replaced_values ), $email_replaced_values, $email_address ) );
			$email_subject = do_shortcode( str_replace( array_keys( $replaced_values ), $replaced_values, $email_subject ) );
			$email_heading = do_shortcode( str_replace( array_keys( $replaced_values ), $replaced_values, $email_heading ) );
			$headers       = array();
			$headers[]     = 'Content-Type: text/html; charset=UTF-8';
			$headers[]     = 'From: Owner <owner@owner.com>';
			$bcc_to        = array();
			if ( ! empty( $bcc_email_address ) ) {
				$bcc_to = explode( ', ', $bcc_email_address );
				if ( ! empty( $bcc_to ) ) {
					foreach ( $bcc_to as $email ) {
						$headers[] = 'Bcc: ' . $email;
					}
				}
			}
			// Content
			$args = array(
				'email_heading'       => $email_heading,
				'custom_order_status' => true,
				'order'               => $order,
				'sent_to_admin'       => false,
				'status_from'         => $status_from,
			);
			ob_start();
			include YAYMAIL_PLUGIN_PATH . 'views/templates/single-mail-template.php';
			$html = ob_get_contents();
			ob_end_clean();

			// Send mail.
			if ( $alg_send_emails ) {
				wc_mail( $email_address, strval( $email_subject ), $html, $headers );
			}
		}

	}

	public function yaymail_get_german_market_templates() {
		if ( class_exists( 'Woocommerce_German_Market' ) ) {
			add_filter(
				'wgm_locate_template',
				function( $template, $template_name, $template_path ) {
					if ( 'emails/customer-confirm-order.php' === $template_name ) {
						$postID          = CustomPostType::postIDByTemplate( 'wgm_confirm_order_email' );
						$template_status = get_post_meta( $postID, '_yaymail_status', true );
						if ( $template_status ) {
							return YAYMAIL_PLUGIN_PATH . 'views/templates/emails/customer-confirm-order.php';
						}
					} elseif ( 'emails/double-opt-in-customer-registration.php' === $template_name ) {
						$postID          = CustomPostType::postIDByTemplate( 'wgm_double_opt_in_customer_registration' );
						$template_status = get_post_meta( $postID, '_yaymail_status', true );
						if ( $template_status ) {
							return YAYMAIL_PLUGIN_PATH . 'views/templates/emails/double-opt-in-customer-registration.php';
						}
					} elseif ( 'emails/sepa-mandate.php' === $template_name ) {
						$postID          = CustomPostType::postIDByTemplate( 'wgm_sepa' );
						$template_status = get_post_meta( $postID, '_yaymail_status', true );
						if ( $template_status ) {
							return YAYMAIL_PLUGIN_PATH . 'views/templates/emails/sepa.php';
						}
					}
					return $template;
				},
				100,
				3
			);
		}
	}

	public function cwgsubscribe_message( $message, $subscriber_id ) {
		$custom_shortcode = new Shortcodes( 'notifier_subscribe_mail', '', false );
		$custom_shortcode->setOrderId( 0, true );
		$custom_shortcode->shortCodesOrderDefined( false, array( 'subscriber_id' => $subscriber_id ) );
		$Ajax   = Ajax::getInstance();
		$postID = CustomPostType::postIDByTemplate( 'notifier_subscribe_mail' );
		$html   = $Ajax->getHtmlByElements( $postID, array( 'order' => 'SampleOrder' ) );
		// Replace shortcode cannot do_shortcode
		$reg  = '/\[yaymail.*?\]/m';
		$html = preg_replace( $reg, '', $html );
		$html = html_entity_decode( $html, ENT_QUOTES, 'UTF-8' );
		return $html;
	}

	public function cwginstock_message( $message, $subscriber_id ) {
		$custom_shortcode = new Shortcodes( 'notifier_instock_mail', '', false );
		$custom_shortcode->setOrderId( 0, true );
		$custom_shortcode->shortCodesOrderDefined( false, array( 'subscriber_id' => $subscriber_id ) );
		$Ajax   = Ajax::getInstance();
		$postID = CustomPostType::postIDByTemplate( 'notifier_instock_mail' );
		$html   = $Ajax->getHtmlByElements( $postID, array( 'order' => 'SampleOrder' ) );
		// Replace shortcode cannot do_shortcode
		$reg  = '/\[yaymail.*?\]/m';
		$html = preg_replace( $reg, '', $html );
		$html = html_entity_decode( $html, ENT_QUOTES, 'UTF-8' );
		return $html;
	}
	public function action_remove_header_footer() {
		$emails = \WC_Emails::instance();
		remove_action( 'woocommerce_email_header', array( $emails, 'email_header' ) );
		remove_action( 'woocommerce_email_footer', array( $emails, 'email_footer' ) );
	}

	public function storeabill_get_template( $located, $template_name, $args, $template_path, $default_path ) {
		if ( is_plugin_active( 'yaymail-addon-for-germanized/yaymail-premium-addon-germanized.php' ) || is_plugin_active( 'YayMail-Addons/yaymail-premium-addon-germanized.php' ) ) {
			$this_template  = false;
			$templateActive = file_exists( YAYMAIL_PLUGIN_PATH . 'views/templates/single-mail-template.php' ) ? YAYMAIL_PLUGIN_PATH . 'views/templates/single-mail-template.php' : false;
			$template       = isset( $args['email'] ) && isset( $args['email']->id ) && ! empty( $args['email']->id ) ? $args['email']->id : false;
			$order          = isset( $args['order'] ) ? $args['order'] : null;
			$order          = isset( $args['document'] ) && ! empty( $args['document']->get_order() ) ? $args['document']->get_order() : $order;
			if ( $template ) {
				$GLOBALS['yaymail_set_order'] = $order;
				$postID                       = CustomPostType::postIDByTemplate( $template, $order );
				if ( $postID ) {
					if ( get_post_meta( $postID, '_yaymail_status', true ) && ! empty( get_post_meta( $postID, '_yaymail_elements', true ) ) ) {
						if ( in_array( $template, $this->templateGermanizedForWC ) ) { // template mail with account
							$this_template = $templateActive;
						}
					}
				}
			}
			$this_template = $this_template ? $this_template : $located;
			return $this_template;
		}
		return $located;
	}

	private function __construct() {}
	// define the woocommerce_new_order callback

	public function admin_reset_password( $message, $key, $user_login, $user_data ) {
		$this_template  = false;
		$templateActive = file_exists( YAYMAIL_PLUGIN_PATH . 'views/templates/single-mail-template.php' ) ? YAYMAIL_PLUGIN_PATH . 'views/templates/single-mail-template.php' : false;
		$template       = 'customer_reset_password';
		$email          = array(
			'id'         => 'customer_reset_password',
			'user_login' => $user_login,
			'user_id'    => $user_data->ID,
			'user_email' => $user_data->data->user_email,
			'user_data'  => $user_data,
			'key'        => $key,
		);
		$args           = array(
			'email'         => (object) $email,
			'sent_to_admin' => false,
			'reset_key'     => $key,
		);
		$postID         = CustomPostType::postIDByTemplate( $template );
		if ( $postID ) {
			if ( get_post_meta( $postID, '_yaymail_status', true ) && ! empty( get_post_meta( $postID, '_yaymail_elements', true ) ) ) {
				if ( isset( $args['order'] ) || in_array( $template, $this->templateAccount ) ) { // template mail with order
					$this_template = $templateActive;
				}
			}
		}
		$template_path = '';
		$template_name = 'emails/customer-reset-password.php';
		if ( false !== $this_template ) {
			ob_start();
			include $this_template;
			$message = ob_get_contents();
			ob_end_clean();
			$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
			// translators: none.
			$title = sprintf( __( '[%s] Password Reset', 'yaymail' ), $site_name );
			wc_mail( $email['user_email'], $title, $message );
			$message = false;
		}
		return $message;
	}

	// support addon TrackShip for WooCommerce
	public function woocommerce_mail_content( $html ) {
		$trackshipArgs  = $this->trackShipArgs;
		$template       = 'trackship_' . $trackshipArgs['new_status'];
		$postID         = CustomPostType::postIDByTemplate( $template );
		$templateActive = file_exists( YAYMAIL_PLUGIN_PATH . 'views/templates/single-follow-up-mail-template.php' ) ? YAYMAIL_PLUGIN_PATH . 'views/templates/single-follow-up-mail-template.php' : false;
		$args           = array(
			'order_id'             => isset( $trackshipArgs['order_id'] ) ? $trackshipArgs['order_id'] : '0',
			'show_shipment_status' => isset( $trackshipArgs['show_shipment_status'] ) ? $trackshipArgs['show_shipment_status'] : false,
			'new_status'           => isset( $trackshipArgs['new_status'] ) ? $trackshipArgs['new_status'] : '',
			'tracking_items'       => isset( $trackshipArgs['tracking_items'] ) ? $trackshipArgs['tracking_items'] : array(),
			'shipment_status'      => isset( $trackshipArgs['shipment_status'] ) ? $trackshipArgs['shipment_status'] : array(),
		);

		ob_start();
		include $templateActive;
		$template_body = ob_get_contents();
		ob_end_clean();
		if ( '' !== $template_body ) {
			return $template_body;
		}
		return $html;

	}

	public function getTemplateMail( $located, $template_name, $args, $template_path, $default_path ) {
		// support addon TrackShip for WooCommerce
		if ( 'emails/tracking-info.php' == $template_name ) {
			$this->trackShipArgs = $args;
			if ( isset( $args['new_status'] ) ) {
				$template        = 'trackship_' . $args['new_status'];
				$postID          = CustomPostType::postIDByTemplate( $template );
				$template_status = get_post_meta( $postID, '_yaymail_status', true );
				if ( $template_status ) {
					add_filter( 'woocommerce_mail_content', array( $this, 'woocommerce_mail_content' ), 100 );
				}
			}
		}
		$this_template  = false;
		$templateActive = file_exists( YAYMAIL_PLUGIN_PATH . 'views/templates/single-mail-template.php' ) ? YAYMAIL_PLUGIN_PATH . 'views/templates/single-mail-template.php' : false;
		if ( isset( $args['yith_wc_email'] ) && isset( $args['yith_wc_email']->id ) && ! empty( $args['yith_wc_email']->id ) ) {
			// Get Email ID in yith-woocommerce-multi-vendor-premium
			$template = $args['yith_wc_email']->id;
		} else {
			$template = isset( $args['email'] ) && isset( $args['email']->id ) && ! empty( $args['email']->id ) ? $args['email']->id : false;
			if ( 'emails/customer-wholesale-register.php' == $template_name ) {
				$template = 'Dokan_Email_Wholesale_Register';
			}
			if ( 'new-user-registration.php' == $template_name ) {
				$template = 'wp_crowdfunding_new_user';
			}
			if ( 'campaign-accepted.php' == $template_name ) {
				$template = 'wp_crowdfunding_campaign_accept';
			}
			if ( 'submit-campaign.php' == $template_name ) {
				$template = 'wp_crowdfunding_submit_campaign';
			}
			if ( 'campaign-updated.php' == $template_name ) {
				$template = 'wp_crowdfunding_campaign_update_email';
			}
			if ( 'new-backed.php' == $template_name ) {
				$template = 'wp_crowdfunding_new_backed';
			}
			if ( 'campaign-target-reached.php' == $template_name ) {
				$template = 'wp_crowdfunding_target_reached_email';
			}
			if ( 'withdraw-request.php' == $template_name ) {
				$template = 'wp_crowdfunding_withdraw_request';
			}
			if ( class_exists( 'WC_Smart_Coupons' ) ) {
				if ( isset( $args['email'] ) && strpos( $located, plugin_dir_path( WC_SC_PLUGIN_FILE ) ) !== false ) {
					$templateName = str_replace( plugin_dir_path( WC_SC_PLUGIN_FILE ) . 'templates/', '', $located );
					if ( 'email.php' == $templateName ) {
						$template   = 'wc_sc_email_coupon';
						$args['id'] = 'wc_sc_email_coupon';
					}
					if ( 'combined-email.php' == $templateName ) {
						$template   = 'wc_sc_combined_email_coupon';
						$args['id'] = 'wc_sc_combined_email_coupon';
					}
					if ( 'acknowledgement-email.php' == $templateName ) {
						$template   = 'wc_sc_acknowledgement_email';
						$args['id'] = 'wc_sc_acknowledgement_email';
					}
				}
			}
			if ( 'emails/waitlist-mailout.php' == $template_name ) {
				$template = 'woocommerce_waitlist_mailout';
			}
			if ( 'emails/waitlist-left.php' == $template_name ) {
				$template = 'woocommerce_waitlist_left_email';
			}
			if ( 'emails/waitlist-joined.php' == $template_name ) {
				$template = 'woocommerce_waitlist_joined_email';
			}
			if ( 'emails/waitlist-new-signup.php' == $template_name ) {
				$template = 'woocommerce_waitlist_signup_email';
			}
			if ( 'emails/dokan-admin-new-booking.php' == $template_name ) {
				$template = 'Dokan_Email_Booking_New';
			}
			if ( 'emails/dokan-customer-booking-cancelled.php' == $template_name ) {
				$template = 'Dokan_Email_Booking_Cancelled_NEW';
			}
		}

		if ( isset( $args['email'] ) && isset( $args['email']->id ) && false !== strpos( get_class( $args['email'] ), 'ORDDD_Email_Delivery_Reminder' ) ) {
			$template .= '_customer';
		}

		if ( isset( $args['email'] ) && is_object( $args['email'] ) && 'WC_GZD_Email_Customer_Cancelled_Order' === get_class( $args['email'] ) && 'customer_failed_order' === $template ) {
			$template = 'customer_cancelled_order';
		}

		// support Custom Order Statuses for WooCommerce by nuggethon
		if ( class_exists( 'WOOCOS_Email_Manager' ) ) {
			if ( isset( $args['order'] ) && null != $args['order']->data['status'] && str_contains( $default_path, 'custom-order-statuses-for-woocommerce' ) ) {
				$order_status = $args['order']->data['status'];
				$template     = 'woocos-' . $order_status;
			}
		}

		// can't load tempalte email-delivery-date.php because it will has error when check out, with plugin WooCommerce Order Delivery
		if ( $template && 'emails/email-delivery-date.php' != $template_name && 'emails/email-order-details.php' != $template_name ) {
			// Yith Stripe

			if ( 'emails/expiring-card-email.php' === $template_name ) {
				$template = 'expiring_card';
			}
			if ( 'emails/renew-needs-action-email.php' === $template_name ) {
				$template = 'renew_needs_action';
			}

			if ( 'emails/admin-notify-approved.php' === $template_name ) {
				$template = 'admin_notify_approved';
			}
			if ( 'customer_partially_refunded_order' == $template ) {
				$template = 'customer_refunded_order';
			}
			$holder_order                 = isset( $args['order'] ) ? $args['order'] : null;
			$holder_order                 = isset( $args['subscription'] ) ? $args['subscription'] : $holder_order;
			$GLOBALS['yaymail_set_order'] = $holder_order;
			$postID                       = CustomPostType::postIDByTemplate( $template, $holder_order );
			if ( $postID ) {
				if ( get_post_meta( $postID, '_yaymail_status', true ) && ! empty( get_post_meta( $postID, '_yaymail_elements', true ) ) ) {
					if ( isset( $args['order'] ) || in_array( $template, $this->templateAccount ) ) { // template mail with order
						$this_template = $templateActive;
					} else {
						$checkHasTempalte = apply_filters( 'yaymail_addon_defined_template', false, $template );
						if ( $checkHasTempalte ) { // template mail with account
							$this_template = $templateActive;
						}
					}
				}
			}
		}
		$this_template = $this_template ? $this_template : $located;
		return $this_template;
	}

	public function register_variable_replacements( $var, $email_data, $email, $queue_item ) {
		$this->FUE_Sending_Email_Variables = $var;
	}

	public function getFollowUpTemplates( $email_data, $email, $queue_item ) {
		if ( has_filter( 'yaymail_follow_up_shortcode' ) ) {
			$templateActive  = file_exists( YAYMAIL_PLUGIN_PATH . 'views/templates/single-follow-up-mail-template.php' ) ? YAYMAIL_PLUGIN_PATH . 'views/templates/single-follow-up-mail-template.php' : false;
			$template        = 'follow_up_email_' . $email->id;
			$postID          = CustomPostType::postIDByTemplate( $template );
			$template_status = get_post_meta( $postID, '_yaymail_status', true );
			$args            = array(
				'email_data' => $email_data,
				'email'      => $email,
				'queue_item' => $queue_item,
			);
			if ( $template_status ) {
				ob_start();
				include $templateActive;
				$template_body               = ob_get_contents();
				$FUE_Sending_Email_Variables = $this->FUE_Sending_Email_Variables;
				$template_body               = $FUE_Sending_Email_Variables->apply_replacements( $template_body );
				ob_end_clean();
				$email_data['message'] = $template_body;
			}
		}
		return $email_data;
	}
}
