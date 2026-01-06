<?php

namespace MarketPress\GermanMarket\Shipping;

// Exit on direct access
use WC_AJAX;
use WC_Order;

defined( 'ABSPATH' ) || exit;

class Frontend {

	/**
	 * @acces public
	 *
	 * @var string
	 */
	public string $id;

	/**
	 * Class constructor.
	 *
	 * @acces protected
	 *
	 * @param string $id
	 */
	protected function __construct( string $id ) {

		$this->id = $id;
	}

	/**
	 * Returns if installed WooCommerce is greater than needed.
	 *
	 * @param string $version
	 *
	 * @return bool
	 */
	protected function version_check( string $version = '3.2' ) : bool {

		if ( class_exists( 'WooCommerce' ) ) {
			global $woocommerce;
			if ( version_compare( $woocommerce->version, $version, ">=" ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @Wp-hook wp_enqueue_scripts
	 *
	 * @return void
	 */
	public function global_enqueue_scripts() {

		$provider     = Woocommerce_Shipping::get_instance()->get_provider_by_id( $this->id );
		$dependencies = array();

		if ( is_cart() || is_checkout() ) {

			$select_woo_active = $this->version_check( '3.2' );
			$dependencies      = array( 'jquery' );
			if ( $select_woo_active ) {
				$dependencies[] = 'selectWoo';
			}
		}

		$google_map_enabled = $provider::$options->get_option( 'google_map_enabled', 'off' );
		$google_map_key     = $provider::$options->get_option( 'google_map_key', '' );

		if ( 'on' === $google_map_enabled && '' !== $google_map_key ) {
			wp_enqueue_script( 'gmaps-markerclusterer', WGM_SHIPPING_URL . '/assets/js/gmaps-markerclusterer' . WGM_SHIPPING_MINIFY . '.js', $dependencies, WGM_SHIPPING_VERSION, true );
			wp_enqueue_script( 'google-maps-api', 'https://maps.googleapis.com/maps/api/js?key=' . $google_map_key, $dependencies, WGM_SHIPPING_VERSION, true );
		} else {
			wp_enqueue_script( 'parcel-select', WGM_SHIPPING_URL . '/assets/js/parcel-select' . WGM_SHIPPING_MINIFY . '.js', $dependencies, WGM_SHIPPING_VERSION, true );
			wp_localize_script( 'parcel-select', 'wgm_woocommerce_shipping', array(
				'ajax_url'        => WC()->ajax_url(),
				'wc_ajax_url'     => WC_AJAX::get_endpoint( '%%endpoint%%' ),
				'ajax_nonce'      => wp_create_nonce( 'save-terminal' ),
			) );
		}
	}

	/**
	 * Returns path to template.
	 *
	 * @param string $template
	 * @param string $template_name
	 * @param string $template_path
	 *
	 * @return string
	 */
	public function locate_template( string $template, string $template_name, string $template_path ) : string {

		// Tmp holder
		$_template = $template;

		if ( ! $template_path ) {
			$template_path = WC()->template_path();
		}

		// Set our base path
		$plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/provider/' . $this->id . '/templates/woocommerce/';

		// Look within passed path within the theme - this is priority
		$template = locate_template(
			array(
				trailingslashit( $template_path ) . $template_name,
				$template_name,
			)
		);

		// Get the template from this plugin, if it exists
		if ( ! $template && file_exists( $plugin_path . $template_name ) ) {
			$template = $plugin_path . $template_name;
		}

		// Use default template
		if ( ! $template ) {
			$template = $_template;
		}

		// Return what we found
		return $template;
	}

	/**
	 * Adding a retoure shipping label download link in customer account section.
	 *
	 * @Hook woocommerce_order_details_after_order_table
	 *
	 * @param WC_Order|array $order
	 *
	 * @return void
	 */
	public function add_retoure_label_download_link( $order ) {

		if ( ! is_user_logged_in() ) {
			return;
		}

		if ( ! is_object( $order ) ) {
			return;
		}

		// Check if order has a valid shipping method.
		if ( ! Helper::check_order_for_shipping_provider_methods( $order, $this->id ) ) {
			return;
		}

		// check if order has status 'completed'.
		$status = $order->get_status();
		if ( 'completed' !== $status ) {
			return;
		}

		$provider = Woocommerce_Shipping::get_instance()->get_provider_by_id( $this->id );

		// Check if download label in "my account" section is enabled.
		if ( ( 'on' !== $provider::$options->get_option( 'label_retoure_enabled', 'off' ) ) || ( 'off' === $provider::$options->get_option( 'retoure_label_download', 'off' ) ) ) {
			return;
		}

		// check if order has a shipping label.
		$has_shipping_label = ! empty( Woocommerce_Shipping::$order_meta->get_shipment_numbers( $order->get_id() ) );
		if ( ! $has_shipping_label ) {
			return;
		}

		$has_retoure_label = ! empty( Woocommerce_Shipping::$order_meta->get_shipment_retoure_label( $order->get_id() ) );
		if ( ! $has_retoure_label ) {
			return;
		}

		if ( Helper::is_international_shipment( $provider::$options->get_option( 'shipping_shop_address_country' ), $order->get_shipping_country(), $order->get_shipping_postcode() ) ) {
			return;
		}

		// if you don't set html5 attribut download and open link in current tab you get in chrome: Resource interpreted as Document but transferred with MIME type application
		$a_href       = esc_url( wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_' . $this->id . '_retoure_shipping_label_download&order_id=' . $order->get_id() ), 'wc-' . $this->id . '-retoure-shipping-label-download' ) );
		$a_target     = '';
		$a_download   = ' download';
		$a_attributes = trim( $a_target . $a_download );
		$button_text  = sprintf( __( 'Download %s Retoure Label', 'woocommerce-german-market' ), $provider->name );

		?>
		<p class="download-invoice-pdf">
			<a href="<?php echo $a_href; ?>" class="button"<?php echo ( $a_attributes != '' ) ? ' ' . $a_attributes : ''; ?> style="<?php echo apply_filters( 'wp_wc_invoice_pdf_download_buttons_inline_style', 'margin: 0.15em 0;' ); ?>"><?php echo $button_text; ?></a>
		</p>
		<?php

	}

}
