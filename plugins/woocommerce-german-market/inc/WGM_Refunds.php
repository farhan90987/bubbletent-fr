<?php

/**
 * Class WGM_Refunds
 *
 * Refunds: Backend Menue, List, Downloads
 *
 * @author MarketPress
 */
class WGM_Refunds {

	/**
	 * @var WGM_Refunds
	 */
	private static $instance = null;

	/**
	 * @var Integer
	 */
	private $count_all_refunds = null;

	/**
	 * @var String
	 */
	private $current_screen_id = null;
	
	/**
	* Singletone get_instance
	*
	* @static
	* @return WGM_Refunds
	*/
	public static function get_instance() {
		if ( self::$instance == NULL) {
			self::$instance = new WGM_Refunds();	
		}
		return self::$instance;
	}

	/**
	* Singletone constructor
	*
	* @access private
	*/
	private function __construct() {
		
		// only if we need the submenu
		$activate = false;
		$activated_add_ons = WGM_Add_Ons::get_activated_add_ons();

		$need_to_be_activated_for_submenu = array( 'sevdesk', 'lexoffice', 'online-buchhaltung', 'woocommerce-running-invoice-number', 'woocommerce-invoice-pdf' );
		foreach ( $activated_add_ons as $key => $add_on ) {
			if ( in_array( $key, $need_to_be_activated_for_submenu ) ) {
				$activate = true;
				break;
			}
		}

		if ( ! $activate ) {
			return;
		}
		
		// add submenu
		add_action( 'admin_menu', array( $this, 'add_refund_submenu' ), 10 );

		// add screen options
		add_filter( 'screen_options_show_screen', array( $this, 'screen_options' ), 10, 2 );

		// save screen options
		add_filter( 'set-screen-option', array( $this, 'save_screen_options' ), 10, 3 );

		// select all
		add_action( 'wgm_refunds_render_refund_id',      array( $this, 'refund_checkboxes' ), 10, 2 );
		add_action( 'wgm_refunds_render_refund_id_head', array( $this, 'refund_checkboxes_select_all' ) );

		// let other add actions or remove our actions
		do_action( 'wgm_refunds_after_actions', $this );
	}

	/**
	* Checkbox for select all refunds
	*
	* @since 3.1
	* @access public
	* @static 
	* @hook wgm_refunds_render_refund_id_head
	* @param String $string
	* @return String
	*/
	public function refund_checkboxes_select_all( $string ) {
		return '<input type="checkbox" class="gm-select-all-refunds" /> ' . $string;
	}

	/**
	* Checkboxen for refunds
	*
	* @since 3.1
	* @access public
	* @static 
	* @hook refund_checkboxes
	* @param String $string
	* @param String raw_content
	* @return String
	*/
	public static function refund_checkboxes( $string, $raw_content ) {

		return '<input class="gm-select-refund" type="checkbox" name="refunds[]" value="' . $raw_content . '" /> ' . $string; 

	}

	/**
	* Add submenu
	* 
	* @wp-hook admin_menu
	* @access public
	*/
	public function add_refund_submenu() {

		$submenu_page = add_submenu_page( 
			'woocommerce', 
			__( 'All Refunds', 'woocommerce-german-market' ), 
			__( 'All Refunds', 'woocommerce-german-market' ),
			apply_filters( 'wgm_refunds_capability', 'view_woocommerce_reports' ), // manage_woocommerce
			'wgm-refunds', 
			array( $this, 'render_refund_menu' )
		);

		$this->current_screen_id = $submenu_page;

	}

	/**
	* Add submenu
	* 
	* @add_submenu_page
	* @access public
	*/
	public function render_refund_menu() {

		// init list
		require_once( 'WGM_Refunds_List_Table.php' );
		$refund_list = new WGM_Refunds_List_Table();

		// German Market styles
		$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : 'min.';
		wp_enqueue_style( 'woocommerce_de_admin', plugins_url( '/css/backend.' . $min . 'css', Woocommerce_German_Market::$plugin_base_name ), array( 'wp-components', 'wc-experimental' ), Woocommerce_German_Market::$version );
	
		// get post per page
		$user = get_current_user_id();
		$screen = get_current_screen();
		$screen_option = $screen->get_option( 'per_page', 'option' );
		$per_page = get_user_meta( $user, $screen_option, true );
		if ( empty ( $per_page) || $per_page < 1 ) {
			$per_page = $screen->get_option( 'per_page', 'default' );
		}

		$refund_list->args[ 'posts_per_page' ] = $per_page;
		$refund_list->args[ 'total_posts' ] = $this->count_all_refunds();
		
		$refund_list->prepare_items();
		?>

		<div class="wrap german-market-refund-list">
			<h1><?php echo esc_attr( __( 'Refunds', 'woocommerce-german-market' ) ); ?></h1>

			<?php
				if ( class_exists( 'Woocommerce_Running_Invoice_Number' ) ) {
					?>
					<p>
						<?php echo esc_attr( __( 'You can edit refund numbers afterwards as explained in this video:', 'woocommerce-german-market' ) ) . ' ' . wp_kses_post( WGM_Ui::get_video_layer( 'https://s3.eu-central-1.amazonaws.com/marketpress-videos/german-market/stornonummer-bearbeiten.mp4' ) ); ?></p>
					</p>
					<?php

				}

			?>

			<form method="post" action="<?php echo get_admin_url(); ?>admin.php?page=wgm-refunds">
				
				<div class="german-market-refund-actions before-list">
					<?php do_action( 'woocommerc_de_refund_before_list' ); ?>
				</div>

				<?php wp_nonce_field( 'wgm_refund_list', 'wgm_refund_list_nonce' ); ?>

				<?php 
				if ( isset( $_REQUEST[ 'notice' ] ) ) {
					?>
						<div id="message" class="updated inline">
							<p>
								<strong><?php echo urldecode( $_REQUEST[ 'notice' ] ); ?></strong>
							</p>
						</div>
					<?php
				}
				?>

				<?php $refund_list->display(); ?>

				<div class="german-market-refund-actions german-market-refund-actions-after-list">
					<?php do_action( 'woocommerc_de_refund_after_list' ); ?>
				</div>

			</form>

		</div>
		<?php

	}

	/**
	* Count all refunds
	* 
	* @access private
	* @return Integer
	*/
	private function count_all_refunds() {

		if ( is_null( $this->count_all_refunds ) ) {
			$refunds = wc_get_orders( 
					array(
        				'type'   => 'shop_order_refund',
       					'limit'  => -1,
       					'return' => 'ids',
       				)
       		);
       		$this->count_all_refunds = count( $refunds );
		}
		
		return $this->count_all_refunds;

	}

	/**
	* Add Screen Options
	* 
	* @access public
	* @return Boolean
	*/
	public function screen_options( $bool, $screen ){
		
		if ( ! is_object( $screen ) || $screen->id != $this->current_screen_id ) {
			return $bool;
		}
		
		add_screen_option( 
	    	'per_page',
			array( 
				'label' 	=> __( 'Number of items per page:', 'woocommerce-german-market' ),
				'default'	=> 10,
				'option'	=> 'wgm_all_refunds_per_page'
			) 
		);
		
		return true;
	}

	/**
	* Save Screen Options
	* 
	* @access public
	* @param $status
	* @param String $option
	* @param String $value
	* @return Integer
	*/
	public function save_screen_options( $status, $option, $value ) {
		if ( 'wgm_all_refunds_per_page' == $option ) {
			return $value;
		} 
	}

}

