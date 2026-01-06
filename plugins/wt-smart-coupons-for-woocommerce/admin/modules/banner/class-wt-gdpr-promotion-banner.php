<?php
if( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Wt_Gdpr_Promotion_banner' ) ) {
    class Wt_Gdpr_Promotion_banner {
        
        private $banner_id = 'wt-gdpr-promotion-banner';
        private static $banner_state_option_name = "wt_gdpr_promotion_banner_state";
        private $banner_state = 1;
        private static $show_banner = null;
        private static $ajax_action_name = "wt_gdpr_promotion_banner_state";
        private static $promotion_link = "https://www.webtoffee.com/product/gdpr-cookie-consent/?utm_source=free_plugin_smart_coupons&utm_medium=smart_coupons_free&utm_campaign=GDPR"; 
        private static $banner_version = '';
        public function __construct() {

            $this->banner_state = get_option( self::$banner_state_option_name ); // Get the banner's current state from database.
            $this->banner_state = absint( false === $this->banner_state ? 1 : $this->banner_state );
            self::$banner_version = WEBTOFFEE_SMARTCOUPON_VERSION; // Plugin version.

            // Enqueue styles.
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
            // Add banner.
            add_action( 'admin_notices', array( $this, 'show_banner' ) );
            //Add banner scripts.
			add_action( 'admin_print_footer_scripts', array( $this, 'add_banner_scripts' ) );
            // Ajax hook to save banner state.
			add_action( 'wp_ajax_' . self::$ajax_action_name, array( $this, 'update_banner_state' ) ); 
        }

        /**
         * To add the banner styles
         *
         * @return void
         */
        public function enqueue_styles() {
            wp_enqueue_style( $this->banner_id, plugin_dir_url( __FILE__ ) . 'assets/css/wt-gdpr-promotion-banner.css', array(), self::$banner_version, 'all' );
        }

        /**
         * To show the banner, depending on the banner state
         *
         * @return void
         */
        public function show_banner() {
            $current_screen = get_current_screen();
            $is_edit = ( isset( $_GET['action'] ) && in_array( $_GET['action'], array( 'edit', 'new' ) ) ) || ( isset( $_SERVER['REQUEST_URI'] ) && false !== strpos( esc_url_raw( $_SERVER['REQUEST_URI'] ), 'post-new.php' ) );
            if ( $this->is_show_banner() && ( 
                false !== strpos( $current_screen->id, 'woocommerce' )
                || false !== strpos( $current_screen->id, 'product' )
                || false !== strpos( $current_screen->id, 'shop_order' )
                || false !== strpos( $current_screen->id, 'shop_coupon' )
                || false !== strpos( $current_screen->id, 'wt-smart-coupon-for-woo' )
            ) && ! $is_edit ) {
            ?>
            <div class="wt-gdpr-promotion-banner notice notice-info is-dismissible">
                    <div class="wt-gdpr-promotion-banner-content-wrap">
                        <div class="wt-header-section">
                            <p class="wt-header-title"><?php esc_html_e( 'Make Your WordPress Website Privacy Compliant!', 'wt-smart-coupons-for-woocommerce' ); ?></p>
                        </div>
                        <div class="wt-body-section">
                            <div class="wt-body-content">

                                <p class="wt-body-text">
                                    <?php
                                    echo sprintf(
                                        esc_html__( 'Ensure cookie compliance with GDPR and US State Privacy laws with our %1$s. Join over %2$s already trusting the GDPR Cookie Consent Plugin.', 'wt-smart-coupons-for-woocommerce' ),
                                        '<span>' . esc_html__( 'Google-certified CMP Partner', 'wt-smart-coupons-for-woocommerce' ) . '</span>',
                                        '<span>' . esc_html__( '1 million+ websites', 'wt-smart-coupons-for-woocommerce' ) . '</span>'
                                    );
                                    ?>
                                </p>


                            </div>

                            <div class="wt-button-wrap">
                                <div class="wt-button">
                                    <a href="<?php echo esc_url( self::$promotion_link ); ?>" class="product-page-btn" target="_blank"><?php esc_html_e( 'Get plugin now', 'wt-smart-coupons-for-woocommerce' ); ?></a>
                                </div>
                                <div class="certificate-section-wrap">
                                    <div class="certificate-text">
                                        <p><?php esc_html_e( 'Google-certified CMP Partner', 'wt-smart-coupons-for-woocommerce' ); ?></p>
                                    </div>

                                    <div class="certificate-image">
                                        <img src="<?php echo esc_url( WT_SMARTCOUPON_MAIN_URL . 'admin/modules/banner/assets/images/wt-certified-partner.svg' ); ?>" alt="<?php echo esc_attr__( 'Certified Partner', 'wt-smart-coupons-for-woocommerce' ); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            }
        }

        /**
         * To check if the banner should be shown
         *
         * @return boolean
         */
        private function is_show_banner() {
            if ( ! is_null( self::$show_banner ) ) { // Already checked
    			return self::$show_banner;
    		}
            
    		/**
    		 * 	Check current banner state
    		 */
            
    		if ( 1 === $this->banner_state ) {
    			self::$show_banner = true;
    			return self::$show_banner;
    		} else {
                self::$show_banner = false;
                return self::$show_banner;
            }
        }

        /**
         * Script to handle banner actions
         *
         * @return void
         */
        public function add_banner_scripts() {
			
			if( $this->is_show_banner() ) { // Show the banner
				$ajax_url = admin_url( 'admin-ajax.php' );
        		$nonce = wp_create_nonce( 'wt_gdpr_promotion_banner' );
				?>
		        <script type="text/javascript">
		        	(function($) {
		                "use strict";
		                /* Prepare ajax data object */
		                var data_obj = {
		                    _wpnonce: '<?php echo esc_html( $nonce ); ?>',
		                    action: '<?php echo esc_html( self::$ajax_action_name ); ?>',
		                    wt_gdpr_promotion_banner_action_type: ''
		                };
		                $(document).on('click', '.wt-gdpr-promotion-banner .product-page-btn', function(e) {
		                    
		                    e.preventDefault(); 
		                    var elm = $(this);
		                    window.open('<?php echo esc_url(self::$promotion_link); ?>'); 
		                    elm.parents('.wt-gdpr-promotion-banner').hide();
		                    data_obj['wt_gdpr_promotion_banner_action_type'] = 3; // Clicked the button
		                    
		                    $.ajax({
		                        url: '<?php echo esc_url($ajax_url); ?>',
		                        data: data_obj,
		                        type: 'POST'
		                    });
		                }).on('click', '.wt-gdpr-promotion-banner .notice-dismiss', function(e) {
		                    e.preventDefault();
                            var elm = $(this);
                            elm.parents('.wt-gdpr-promotion-banner').hide();
		                    data_obj['wt_gdpr_promotion_banner_action_type'] = 2; // Closed by user
		                    
		                    $.ajax({
		                        url: '<?php echo esc_url($ajax_url); ?>',
		                        data: data_obj,
		                        type: 'POST',
		                    });
		                });
	            })(jQuery)
		        </script>
		        <?php
			}
    	}

        /**
    	 * 	Update banner state ajax hook
    	 * 
    	 */
    	public function update_banner_state() {
    		check_ajax_referer( 'wt_gdpr_promotion_banner' );
    		if ( isset( $_POST['wt_gdpr_promotion_banner_action_type'] ) ) {
	            
	            $action_type = absint( sanitize_text_field($_POST['wt_gdpr_promotion_banner_action_type']) );
	            
                // Current action is allowed?
	            if ( in_array( $action_type, array( 2, 3 ) ) ) {
	                update_option( self::$banner_state_option_name, $action_type );
	            }
	        }
	        exit();
    	}
    }
    new Wt_Gdpr_Promotion_banner();
}