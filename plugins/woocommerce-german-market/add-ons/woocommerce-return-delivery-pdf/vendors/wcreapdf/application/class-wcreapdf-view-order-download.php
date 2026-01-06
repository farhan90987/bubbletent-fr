<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WCREAPDF_View_Order_Download' ) ) {
	
	/**
	* frontend download on customer account, view-order
	*
	* @class WCREAPDF_View_Order_Download
	* @version	1.0.7
	* @category	Class
	*/
	class WCREAPDF_View_Order_Download {
		
		/**
		* download button on view-order page
		*
		* @since 0.0.1
		* @access public
		* @hook woocommerce_order_details_after_order_table
		* @return void
		*/
		public static function make_download_button ( $order ){
			
			if ( ! is_user_logged_in() ) {
				return;	
			}

			// manual order confirmation
			if ( is_object( $order ) && method_exists( $order, 'get_meta' ) ) {
				if ( $order->get_meta( '_gm_needs_conirmation' ) === 'yes' ) {
					return;
				}
			}

			// double-opt-in check
			if ( get_option( 'wgm_double_opt_in_customer_registration' ) == 'on' ) {
				
				$user = wp_get_current_user();
				$activation_status = get_user_meta( $user->ID, '_wgm_double_opt_in_activation_status', true );

				if ( $activation_status == 'waiting' ) {
					return;
				}

			}
			
			if ( WCREAPDF_Helper::check_if_needs_attachement( $order ) ) {	// if there's nothing to ship, ther's no retour pdf, so don't output the button

				// if you don't set html5 attribut download and open link in current tab you get in chrome: Resource interpreted as Document but transferred with MIME type application
				$a_href			= esc_url( wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_wcreapdf_view_order_download&order_id=' . $order->get_id() ), 'woocommerce-wcreapdf-retour-download' ) );
				$a_target       = ( get_option( WCREAPDF_Helper::get_wcreapdf_optionname( 'view-order-link-behaviour' ), 'new' ) == 'new' ) ? 'target="_blank"' : '' ;
				$a_download     = ( get_option( WCREAPDF_Helper::get_wcreapdf_optionname( 'view-order-download-behaviour' ), 'inline' ) == 'inline' ) ? '' : ' download' ;
				$a_attributes	= trim( $a_target . $a_download ); 
				$button_text    = get_option( WCREAPDF_Helper::get_wcreapdf_optionname( 'view-order-button-text' ), __( 'Download Return Delivery Pdf', 'woocommerce-german-market' ) );
				if( has_action( 'wcreapdf_view_order_button' ) ) {
					do_action( 'wcreapdf_view_order_button', $a_href, $a_target, $a_attributes, $button_text );
				} else {
					?>
					<p class="download-invoice-pdf"><a href="<?php echo $a_href; ?>" class="button"<?php echo ( $a_attributes != '' ) ? ' ' . $a_attributes : ''; ?>><?php echo $button_text; ?></a>
					</p>
					<?php
				}
			}
		}
		
		/**
		* download pdf frontend
		*
		* @since 0.0.1
		* @access public
		* @hook wp_ajax_woocommerce_wcreapdf_view_order_download
		* @return void
		*/
		public static function download_pdf(){
			check_ajax_referer( 'woocommerce-wcreapdf-retour-download', 'security' );
			// init
			$order_id    = $_REQUEST[ 'order_id' ];
			$order       = wc_get_order( $order_id );
			$status      = $order->get_status();
			// creata pdf only if user is allowed to
			if ( $status !== false && current_user_can( 'view_order', $order_id ) && WCREAPDF_Helper::check_if_needs_attachement( $order ) ) {
				// download behaviour, if download_string is 'I' pdf is send inline to browser, if it is 'D' download will be forced. you shold not use other options here.
				$download_string = ( get_option( WCREAPDF_Helper::get_wcreapdf_optionname( 'view-order-download-behaviour' ), 'inline' ) == 'inline' ) ? 'I' : 'D' ;
				WCREAPDF_Pdf::create_pdf( $order, false, $download_string );
			} else {
				$redirect = apply_filters( 'wcreapdf_view_order_redirect_link', get_permalink( get_option('woocommerce_myaccount_page_id' ) ) );
				wp_safe_redirect( $redirect );	
			}		
			exit();
		}
		
	} // end class
	
} // end if
?>