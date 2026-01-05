<?php
/**
 * Class for Non profit order Email.
 *
 * @package     cashier/cost-of-goods/includes/emails/
 * @version     1.0.0
 * @since       1.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SA_CFW_COG_Non_Profit_Email' ) ) {

	/**
	 * The Non Profit Email
	 *
	 * @extends \WC_Email
	 */
	class SA_CFW_COG_Non_Profit_Email extends WC_Email {

		/**
		 * Set email defaults
		 */
		public function __construct() {

			// Set ID, this simply needs to be a unique name.
			$this->id = 'sa_cfw_cog_non_profit';

			// This is the title in WooCommerce Email settings.
			$this->title = __( 'Cashier - Cost of Good non-profit Order', 'cashier' );

			// This is the description in WooCommerce email settings.
			$this->description = __( 'This email will be sent to the store owner when they get a non-profitable order', 'cashier' );

			// These are the default heading and subject lines that can be overridden using the settings.
			$this->subject = __( 'You got a non-profitable order : {site_title}', 'cashier' );
			$this->heading = __( 'Loss Order', 'cashier' );

			// Email template location.
			$this->template_html  = 'cfw-cog-non-profit.php';
			$this->template_plain = 'plain/cfw-cog-non-profit.php';
			// Use our plugin templates directory as the template base.
			$this->template_base = SA_COG_PLUGIN_DIRPATH . '/templates/';

			$this->placeholders = array();

			$this->recipient = apply_filters( 'sa_cfw_cog_loss_order_receiver_email', get_option( 'admin_email' ) );

			// Trigger on new conversion.
			add_action( 'sa_cfw_cog_non_profit_email', array( $this, 'trigger' ), 10, 1 );

			// Call parent constructor to load any other defaults not explicity defined here.
			parent::__construct();

		}

		/**
		 * Determine if the email should actually be sent and setup email merge variables
		 *
		 * @param array $args Email arguements.
		 */
		public function trigger( $args = array() ) {

			if ( empty( $args ) ) {
				return;
			}

			$this->email_args = '';
			$this->email_args = wp_parse_args( $args, $this->email_args );

			// Set the locale to the store locale for customer emails to make sure emails are in the store language.
			$this->setup_locale();

			// For any email placeholders.
			$this->set_placeholders();

			$email_content = $this->get_content();
			// Replace placeholders with values in the email content.
			$email_content = ( is_callable( array( $this, 'format_string' ) ) ) ? $this->format_string( $email_content ) : $email_content;

			// Send email.
			if ( $this->is_enabled() && $this->get_recipient() ) {
				$this->send( $this->get_recipient(), $this->get_subject(), $email_content, $this->get_headers(), $this->get_attachments() );
			}

			$this->restore_locale();

		}

		/**
		 * Function to set placeholder variables used in email.
		 */
		public function set_placeholders() {
			// For any email placeholders.
			$this->placeholders = array(
				'{site_title}' => $this->get_blogname(),
			);
		}

		/**
		 * Function to load email html content
		 *
		 * @return string Email content html
		 */
		public function get_content_html() {
			$default_path  = $this->template_base;
			$template_path = sa_cfw_cog()->get_template_base_dir( $this->template_html );

			$email_heading = $this->get_heading();

			ob_start();

			wc_get_template(
				$this->template_html,
				array(
					'email'              => $this,
					'email_heading'      => $email_heading,
					'order_id'           => $this->email_args['order_id'],
					'additional_content' => is_callable( array( $this, 'get_additional_content' ) ) ? $this->get_additional_content() : '',
				),
				$template_path,
				$default_path
			);

			return ob_get_clean();
		}

		/**
		 * Function to load email plain content
		 *
		 * @return string Email plain content
		 */
		public function get_content_plain() {
			$default_path  = $this->template_base;
			$template_path = sa_cfw_cog()->get_template_base_dir( $this->template_plain );

			$email_heading = $this->get_heading();

			ob_start();

			wc_get_template(
				$this->template_plain,
				array(
					'email'              => $this,
					'email_heading'      => $email_heading,
					'order_id'           => $this->email_args['order_id'],
					'additional_content' => is_callable( array( $this, 'get_additional_content' ) ) ? $this->get_additional_content() : '',
				),
				$template_path,
				$default_path
			);

			return ob_get_clean();
		}

		/**
		 * Initialize Settings Form Fields
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled'            => array(
					'title'   => __( 'Enable/Disable', 'cashier' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable this email notification', 'cashier' ),
					'default' => 'yes',
				),
				'subject'            => array(
					'title'       => __( 'Subject', 'cashier' ),
					'type'        => 'text',
					'description' => sprintf( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', $this->subject ),
					'placeholder' => $this->subject,
					'default'     => '',
				),
				'heading'            => array(
					'title'       => __( 'Email Heading', 'cashier' ),
					'type'        => 'text',
					/* translators: %s Email heading. */
					'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.' ), $this->heading ),
					'placeholder' => $this->heading,
					'default'     => '',
				),
				'additional_content' => array(
					'title'       => __( 'Additional content', 'cashier' ),
					'description' => __( 'Text to appear below the main email content.', 'cashier' ),
					'css'         => 'width:400px; height: 75px;',
					'placeholder' => __( 'N/A', 'cashier' ),
					'type'        => 'textarea',
					'default'     => is_callable( array( $this, 'get_additional_content' ) ) ? $this->get_additional_content() : '', // WC 3.7 introduced an additional content field for all emails.
				),
				'email_type'         => array(
					'title'       => __( 'Email type', 'cashier' ),
					'type'        => 'select',
					'description' => __( 'Choose which format of email to send.', 'cashier' ),
					'default'     => 'html',
					'class'       => 'email_type wc-enhanced-select',
					'options'     => $this->get_email_type_options(),
				),
			);
		}

	}

}
