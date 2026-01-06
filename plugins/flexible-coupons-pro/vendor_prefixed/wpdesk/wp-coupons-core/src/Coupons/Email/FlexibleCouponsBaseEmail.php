<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Email;

use WC_Email;
use PHPMailer\PHPMailer\PHPMailer;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Data\Email\EmailMeta;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Exception\EmailException;
/**
 * Base email template.
 *
 * @package WPDesk\Library\WPCoupons\Email
 */
class FlexibleCouponsBaseEmail extends WC_Email implements Email
{
    const SLUG = 'FlexibleCouponsEmail';
    const SLUG_RECIPIENT = 'FlexibleCouponsRecipientEmail';
    /**
     * @var bool
     */
    public $can_attach_pdf = \false;
    /**
     * Coupon meta data.
     *
     * @var EmailMeta
     */
    protected $meta = null;
    /**
     * @param string $template_path Email template path.
     */
    public function __construct(string $template_path)
    {
        $this->template_base = $template_path;
        $this->customer_email = \true;
        $this->manual = \true;
        parent::__construct();
    }
    /**
     * Get HTML content.
     *
     * @return string
     */
    public function get_content_html(): string
    {
        ob_start();
        wc_get_template($this->template_html, ['order' => $this->object, 'email_heading' => $this->get_heading(), 'meta' => $this->meta, 'email' => $this->customer_email, 'order_number' => $this->object->get_order_number(), 'date_order' => $this->get_date_order(), 'sent_to_admin' => \false, 'plain_text' => \false], '', $this->template_base);
        return ob_get_clean();
    }
    /**
     * Get plain content.
     *
     * @return string
     */
    public function get_content_plain(): string
    {
        ob_start();
        wc_get_template($this->template_plain, ['order' => $this->object, 'email_heading' => $this->get_heading(), 'meta' => $this->meta, 'email' => $this->customer_email, 'order_number' => $this->object->get_order_number(), 'date_order' => $this->get_date_order(), 'sent_to_admin' => \false, 'plain_text' => \true], '', $this->template_base);
        return ob_get_clean();
    }
    /**
     * Initialise Settings Form Fields
     *
     * @return void
     */
    public function init_form_fields()
    {
        // phpcs:disable WordPress.WP.I18n.TextDomainMismatch
        $this->form_fields = ['subject' => ['title' => esc_html__('Subject', 'woocommerce'), 'type' => 'text', 'placeholder' => $this->subject, 'default' => ''], 'heading' => ['title' => esc_html__('Email Heading', 'woocommerce'), 'type' => 'text', 'placeholder' => $this->heading, 'default' => ''], 'email_type' => ['title' => esc_html__('Email type', 'woocommerce'), 'type' => 'select', 'description' => esc_html__('Choose which format of email to send.', 'woocommerce'), 'default' => 'html', 'class' => 'email_type', 'options' => ['plain' => esc_html__('Plain text', 'woocommerce'), 'html' => esc_html__('HTML', 'woocommerce'), 'multipart' => esc_html__('Multipart', 'woocommerce')]]];
        // phpcs:enable WordPress.WP.I18n.TextDomainMismatch
    }
    /**
     * @param int $order_id
     * @param EmailMeta $meta
     *
     * @return bool success
     */
    public function send_mail($order_id, EmailMeta $meta)
    {
        $order = \wc_get_order(\absint($order_id));
        if (!$order instanceof \WC_Order) {
            throw new EmailException('Order not found');
        }
        $this->object = $order;
        $this->meta = $meta;
        // child classes can and should add recipients on their own.
        $recipient = $this->get_recipient();
        if (empty($recipient)) {
            return \false;
        }
        // is_enabled allow us to use woocommerce filters for disabling emails.
        if (!$this->is_enabled()) {
            return \false;
        }
        $this->setup_placeholders();
        $this->setup_locale();
        \add_action('phpmailer_init', [$this, 'add_string_attachments']);
        $result = $this->send($recipient, $this->get_subject(), $this->get_content(), $this->get_headers(), []);
        \remove_action('phpmailer_init', [$this, 'add_string_attachments']);
        $this->restore_locale();
        return $result;
    }
    public function setup_preview(EmailMeta $meta): void
    {
        $this->meta = $meta;
        $this->setup_placeholders();
    }
    protected function setup_placeholders()
    {
        $this->placeholders = array_merge(['{order_date}' => $this->get_date_order(), '{order_number}' => $this->object->get_order_number()], $this->placeholders);
    }
    /**
     * A filter function which adds string attachments to the email.
     *
     * @param \PHPMailer $phpmailer
     *
     * @return void
     */
    public function add_string_attachments($phpmailer)
    {
        if (!$this->can_attach_pdf) {
            return;
        }
        $string_attachments = $this->get_string_attachments();
        if (empty($string_attachments)) {
            return;
        }
        foreach ($string_attachments as $attachment) {
            $phpmailer->addStringAttachment($attachment['pdf']['content'], $attachment['pdf']['fileName']);
        }
    }
    /**
     * Any child class can override this function to add string attachments to the email.
     *
     * String attachments are not possible with the default WC_Email class.
     *
     * @return array<array{pdf: array{fileName: string, content: string}}> An array of PDF attachments.
     */
    protected function get_string_attachments(): array
    {
        return [];
    }
    protected function get_date_order(): string
    {
        return \date_i18n(\wc_date_format(), $this->object->get_date_created() ? $this->object->get_date_created()->getTimestamp() : \current_time('timestamp'));
    }
}
