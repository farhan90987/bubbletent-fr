<?php
/**
 * Plugin settings
 *
 * @package smoobu-calendar
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No skiddies please!' );
}

/**
 * Settings class
 */
final class Smoobu_Settings {
	/**
	 * Class instance
	 *
	 * @var Smoobu_Settings
	 */
	protected static $instance = null;

	/**
	 * Main instance
	 *
	 * @return Smoobu_Settings
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		// register submenu.
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
	}

	/**
	 * Register submenu
	 *
	 * @return void
	 */
	public function register_menu() {
		// main page.
		add_menu_page(
			__( 'General Settings', 'smoobu-calendar' ),
			__( 'Smoobu Calendar', 'smoobu-calendar' ),
			'manage_options',
			'smoobu-calendar-settings',
			array( $this, 'show_general_settings' ),
			'dashicons-clipboard',
			107
		);

		// submenu pages.
		add_submenu_page( 'smoobu-calendar-settings', __( 'General Settings', 'smoobu-calendar' ), __( 'General Settings', 'smoobu-calendar' ), 'manage_options', 'smoobu-calendar-settings', array( $this, 'show_general_settings' ), 5 );
		add_submenu_page( 'smoobu-calendar-settings', __( 'My Properties', 'smoobu-calendar' ), __( 'My Properties', 'smoobu-calendar' ), 'manage_options', 'smoobu-calendar-settings-properties', array( $this, 'show_properties_settings' ), 5 );
		add_submenu_page( 'smoobu-calendar-settings', __( 'Data Renewal', 'smoobu-calendar' ), __( 'Data Renewal', 'smoobu-calendar' ), 'manage_options', 'smoobu-calendar-settings-renewal', array( $this, 'show_renewal_settings' ), 10 );
		add_submenu_page( 'smoobu-calendar-settings', __( 'Webhook', 'smoobu-calendar' ), __( 'Webhook', 'smoobu-calendar' ), 'manage_options', 'smoobu-calendar-settings-webhook', array( $this, 'show_webhook_settings' ), 15 );
		add_submenu_page( 'smoobu-calendar-settings', __( 'Styling', 'smoobu-calendar' ), __( 'Styling', 'smoobu-calendar' ), 'manage_options', 'smoobu-calendar-settings-styling', array( $this, 'show_styling_settings' ), 20 );
		add_submenu_page( 'smoobu-calendar-settings', __( 'FAQ', 'smoobu-calendar' ), __( 'FAQ', 'smoobu-calendar' ), 'manage_options', 'smoobu-calendar-settings-faq', array( $this, 'show_faq_settings' ), 25 );
	}

	/**
	 * Display general settings
	 *
	 * @return void
	 */
	public function show_general_settings() {
		$saved = $this->save_general_settings();

		Smoobu_Main::load_template(
			'admin/settings/general',
			array(
				'success' => $saved['success'],
				'message' => $saved['message'],
				'nonce'   => wp_create_nonce( 'smoobu_settings_nonce' ),
			)
		);
	}

	/**
	 * Display properties settings
	 *
	 * @return void
	 */
	public function show_properties_settings() {
		Smoobu_Main::load_template(
			'admin/settings/properties',
			array(
				'properties' => Smoobu_Utility::get_available_properties(),
			)
		);
	}

	/**
	 * Display renewal settings
	 *
	 * @return void
	 */
	public function show_renewal_settings() {
		$saved = $this->save_renewal_settings();

		Smoobu_Main::load_template(
			'admin/settings/renewal',
			array(
				'success' => $saved['success'],
				'message' => $saved['message'],
				'nonce'   => wp_create_nonce( 'smoobu_settings_nonce' ),
			)
		);
	}

	/**
	 * Call template to show webhook settings
	 *
	 * @return void
	 */
	public function show_webhook_settings() {
		Smoobu_Main::load_template( 'admin/settings/webhook' );
	}

	/**
	 * Display styling settings
	 *
	 * @return void
	 */
	public function show_styling_settings() {
		$saved = $this->save_styling_settings();

		// load calendar styling for a calendar preview.
		$calendar = new Smoobu_Calendar();
		$calendar->run();
		$calendar->enqueue_custom_styles( true );

		Smoobu_Main::load_template(
			'admin/settings/styling',
			array(
				'success'          => $saved['success'],
				'message'          => $saved['message'],
				'nonce'            => wp_create_nonce( 'smoobu_settings_nonce' ),
				'current_theme'    => Smoobu_Utility::get_current_theme(),
				'styling_settings' => Smoobu_Utility::get_theme_styling(),
				'default_settings' => Smoobu_Utility::get_default_theme_styling(),
				'translations'     => Smoobu_Utility::get_abbr_translations(),
			)
		);
	}

	/**
	 * Display FAQ page
	 *
	 * @return void
	 */
	public function show_faq_settings() {
		$content = Smoobu_Utility::get_faq_content();

		Smoobu_Main::load_template(
			'admin/settings/faq',
			array(
				'content' => $content,
			)
		);
	}

	/**
	 * Save general settings on submit
	 *
	 * @return array
	 */
	private function save_general_settings() {
		// nonce verification.
		if (
			isset( $_POST['smoobu_settings_nonce'] ) &&
			wp_verify_nonce(
				sanitize_key( $_POST['smoobu_settings_nonce'] ),
				'smoobu_settings_nonce'
			)
		) {
			// update only if submit in this specific page is pressed.
			if ( isset( $_POST['smoobu_general_settings_save'] ) ) {
				// update API key.
				if ( isset( $_POST['smoobu_api_key'] ) ) {
					$smoobu_api_key = sanitize_text_field( wp_unslash( $_POST['smoobu_api_key'] ) );
				}

				update_option( 'smoobu_api_key', $smoobu_api_key );

				// check if the API key is correct and if so, update properties list & availability.
				$api          = new Smoobu_Api();
				$check_result = $api->get_api_check( $smoobu_api_key, SMOOBU_API_USER_ENDPOINT );

				if ( false === $check_result ) {
					// update properties list.
					$properties_api = new Smoobu_Api_Properties();
					$properties_api->fetch_properties();

					$availability_api = new Smoobu_Api_Availability();
					$availability_api->fetch_availability();

					$user_api = new Smoobu_Api_User();
					$user_api->fetch_user();

					$properties_error   = $properties_api->get_error();
					$availability_error = $availability_api->get_error();
					$user_error         = $user_api->get_error();

					if (
						! empty( $properties_error ) ||
						! empty( $availability_error ) ||
						! empty( $user_error )
					) {
						$error  = ! empty( $properties_error ) ? $properties_error : '';
						$error .= ! empty( $availability_error ) ? $availability_error : '';
						$error .= ! empty( $user_error ) ? $user_error : '';

						return array(
							'success' => false,
							'message' => __( 'API key was saved, but we encountered an error while trying to update properties & availability information:', 'smoobu-calendar' ) . $error,
						);
					} else {
						return array(
							'success' => true,
							'message' => __( 'API key was saved and properties list & availability was sucessfully updated.', 'smoobu-calendar' ),
						);
					}
				} else {
					return array(
						'success' => true,
						'message' => __( 'Settings saved.', 'smoobu-calendar' ),
					);
				}
			}
		}

		return array(
			'success' => false,
			'message' => false,
		);
	}

	/**
	 * Save renewal settings on submit
	 *
	 * @return array
	 */
	private function save_renewal_settings() {
		// nonce verification.
		if (
			isset( $_POST['smoobu_settings_nonce'] ) &&
			wp_verify_nonce(
				sanitize_key( $_POST['smoobu_settings_nonce'] ),
				'smoobu_settings_nonce'
			)
		) {
			// update only if submit in this specific page is pressed.
			if ( isset( $_POST['smoobu_renewal_settings_properties'] ) ) {
				// update properties list.
				$properties_api = new Smoobu_Api_Properties();
				$properties_api->fetch_properties();

				$error = $properties_api->get_error();

				if ( ! empty( $error ) ) {
					return array(
						'success' => false,
						'message' => $error,
					);
				} else {
					return array(
						'success' => true,
						'message' => __( 'Properties list was sucessfully updated', 'smoobu-calendar' ),
					);
				}
			} elseif ( isset( $_POST['smoobu_renewal_settings_availability'] ) ) {
				// update properties availability.
				$availability_api = new Smoobu_Api_Availability();
				$availability_api->fetch_availability();

				$error = $availability_api->get_error();

				if ( ! empty( $error ) ) {
					return array(
						'success' => false,
						'message' => $error,
					);
				} else {
					return array(
						'success' => true,
						'message' => __( 'Properties availability was sucessfully updated', 'smoobu-calendar' ),
					);
				}
			}
		}

		return array(
			'success' => false,
			'message' => false,
		);
	}

	/**
	 * Save styling settings on submit
	 *
	 * @return array
	 */
	private function save_styling_settings() {
		// nonce verification.
		if ( isset( $_POST['smoobu_settings_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['smoobu_settings_nonce'] ), 'smoobu_settings_nonce' ) ) {
			if ( isset( $_POST['smoobu_styling_settings_save'] ) ) {
				// update only if save changes button was pressed.
				// set default checkboxes as they are not set when not checked.
				$full_width                   = false;
				$custom_styling               = false;
				$custom_styling_border_shadow = false;

				// save basic options.
				if ( isset( $_POST['smoobu_calendar_theme'] ) ) {
					$calendar_theme = sanitize_text_field( wp_unslash( $_POST['smoobu_calendar_theme'] ) );
				}

				if ( isset( $_POST['smoobu_full_width'] ) ) {
					$full_width = sanitize_text_field( wp_unslash( $_POST['smoobu_full_width'] ) );
				}

				if ( isset( $_POST['smoobu_custom_styling'] ) ) {
					$custom_styling = sanitize_text_field( wp_unslash( $_POST['smoobu_custom_styling'] ) );
				}

				// check if theme has changed.
				$theme_changed = false;

				$prev_calendar_theme = get_option( 'smoobu_calendar_theme' );

				if ( $prev_calendar_theme !== $calendar_theme ) {
					$theme_changed = true;
				}

				// update options.
				update_option( 'smoobu_calendar_theme', $calendar_theme );
				update_option( 'smoobu_full_width', $full_width );
				update_option( 'smoobu_custom_styling', $custom_styling );

				// rewrite some values if theme has changed.
				if ( true === $theme_changed ) {
					$default_settings = Smoobu_Utility::get_default_theme_styling();

					$custom_styling_border_shadow = $default_settings['border_shadow'];
					$custom_styling_border_radius = $default_settings['border_radius'];
				} else {
					if ( isset( $_POST['smoobu_custom_styling_border_shadow'] ) ) {
						$custom_styling_border_shadow = sanitize_text_field( wp_unslash( $_POST['smoobu_custom_styling_border_shadow'] ) );
					}

					if ( isset( $_POST['smoobu_custom_styling_border_radius'] ) ) {
						$custom_styling_border_radius = sanitize_text_field( wp_unslash( $_POST['smoobu_custom_styling_border_radius'] ) );
					}
				}

				update_option( 'smoobu_custom_styling_border_shadow', $custom_styling_border_shadow );
				update_option( 'smoobu_custom_styling_border_radius', $custom_styling_border_radius );

				// save loop variables (colors).
				$styling_settings = Smoobu_Utility::get_theme_styling();

				if ( ! empty( $styling_settings['colors'] ) ) {
					foreach ( $styling_settings['colors'] as $key => $color ) {
						$option_name = 'smoobu_custom_styling_color_' . $key;

						if ( true === $theme_changed ) {
							$option_value = '';
						} else {
							if ( isset( $_POST[ $option_name ] ) ) {
								$option_value = sanitize_text_field( wp_unslash( $_POST[ $option_name ] ) );
							}
						}

						update_option( $option_name, $option_value );
					}
				}

				return array(
					'success' => true,
					'message' => __( 'Settings saved.', 'smoobu-calendar' ),
				);
			} elseif ( isset( $_POST['smoobu_styling_settings_reset'] ) ) {
				// reset custom styling.
				$default_settings = Smoobu_Utility::get_default_theme_styling();

				update_option( 'smoobu_custom_styling_border_shadow', $default_settings['border_shadow'] );
				update_option( 'smoobu_custom_styling_border_radius', $default_settings['border_radius'] );

				// save loop variables (colors).
				if ( ! empty( $default_settings['colors'] ) ) {
					foreach ( $default_settings['colors'] as $key => $color ) {
						$option_name = 'smoobu_custom_styling_color_' . $key;

						update_option( $option_name, $color );
					}
				}

				return array(
					'success' => true,
					'message' => __( 'Settings saved.', 'smoobu-calendar' ),
				);
			}
		}

		return array(
			'success' => false,
			'message' => false,
		);
	}
}
