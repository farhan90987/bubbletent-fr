<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Data\Email;

/**
 * Meta accessor coupons data.
 */
class EmailMeta implements \ArrayAccess
{
    private const RECIPIENT_MESSAGE_META_KEY = 'flexible_coupon_recipient_message';
    private const RECIPIENT_NAME_META_KEY = 'flexible_coupon_recipient_name';
    private const RECIPIENT_EMAIL_META_KEY = 'flexible_coupon_recipient_email';
    private const CUSTOMER_DELAY_DATE_FIELD = 'fc_sending_customer_delay_date';
    private const COUPONS_ARRAY_KEY = 'coupons';
    private const EXPIRY_DATE_KEY = 'coupon_expiry';
    private const COUPON_VALUE_KEY = 'coupon_value';
    private const COUPON_CODE_KEY = 'coupon_code';
    private const COUPON_URL_KEY = 'coupon_url';
    private const COUPON_ID_KEY = 'coupon_id';
    private const PRODUCT_ID_KEY = 'product_id';
    private const VARIATION_ID_KEY = 'variation_id';
    private const ORDER_ID_KEY = 'order_id';
    private const HASH_KEY = 'hash';
    private const ITEM_ID_KEY = 'item_id';
    private const FIRST_ARRAY_KEY = 0;
    /**
     * @var array
     */
    private $meta;
    public function __construct(array $meta)
    {
        $this->meta = $meta;
    }
    public function get_meta(): array
    {
        return $this->meta;
    }
    public function get_coupons_array(): array
    {
        return $this->meta[self::COUPONS_ARRAY_KEY];
    }
    public function get_recipient_name(): string
    {
        return $this->meta[self::RECIPIENT_NAME_META_KEY] ?? '';
    }
    public function get_recipient_email(): string
    {
        return $this->meta[self::RECIPIENT_EMAIL_META_KEY] ?? '';
    }
    public function get_recipient_message(): string
    {
        return $this->meta[self::RECIPIENT_MESSAGE_META_KEY] ?? '';
    }
    public function get_coupon_expiry(): string
    {
        return $this->meta[self::COUPONS_ARRAY_KEY][self::FIRST_ARRAY_KEY][self::EXPIRY_DATE_KEY] ?? '';
    }
    public function get_coupon_value(): string
    {
        return $this->meta[self::COUPONS_ARRAY_KEY][self::FIRST_ARRAY_KEY][self::COUPON_VALUE_KEY] ?? '';
    }
    public function get_coupon_url(): string
    {
        return $this->meta[self::COUPONS_ARRAY_KEY][self::FIRST_ARRAY_KEY][self::COUPON_URL_KEY] ?? '';
    }
    public function get_coupon_code(): string
    {
        return $this->meta[self::COUPONS_ARRAY_KEY][self::FIRST_ARRAY_KEY][self::COUPON_CODE_KEY] ?? '';
    }
    public function get_coupon_hash(): string
    {
        return $this->meta[self::COUPONS_ARRAY_KEY][self::FIRST_ARRAY_KEY][self::HASH_KEY] ?? '';
    }
    public function get_coupon_id(): int
    {
        return $this->meta[self::COUPONS_ARRAY_KEY][self::FIRST_ARRAY_KEY][self::COUPON_ID_KEY] ?? 0;
    }
    public function get_product_id(): int
    {
        return $this->meta[self::COUPONS_ARRAY_KEY][self::FIRST_ARRAY_KEY][self::PRODUCT_ID_KEY] ?? 0;
    }
    public function get_delay_date(): string
    {
        if (isset($this->meta[self::CUSTOMER_DELAY_DATE_FIELD])) {
            return (string) $this->meta[self::CUSTOMER_DELAY_DATE_FIELD];
        }
        return '';
    }
    public function get_variation_id(): int
    {
        return $this->meta[self::COUPONS_ARRAY_KEY][self::FIRST_ARRAY_KEY][self::VARIATION_ID_KEY] ?? 0;
    }
    public function get_item_id(): int
    {
        return $this->meta[self::COUPONS_ARRAY_KEY][self::FIRST_ARRAY_KEY][self::ITEM_ID_KEY] ?? 0;
    }
    public function get_order_id(): int
    {
        return $this->meta[self::COUPONS_ARRAY_KEY][self::FIRST_ARRAY_KEY][self::ORDER_ID_KEY] ?? 0;
    }
    public function get_coupon_count(): int
    {
        return count($this->get_coupons_array());
    }
    public function get_coupon_codes(): array
    {
        return array_column($this->get_coupons_array(), self::COUPON_CODE_KEY);
    }
    public function get_coupon_urls(): array
    {
        return array_column($this->get_coupons_array(), self::COUPON_URL_KEY);
    }
    /**
     * ArrayAccess methods
     *
     * Used for backwards compatibility (some clients have overwritten our email templates)
     */
    public function offsetExists($offset): bool
    {
        return isset($this->meta[self::COUPONS_ARRAY_KEY][self::FIRST_ARRAY_KEY][$offset]);
    }
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->meta[self::COUPONS_ARRAY_KEY][self::FIRST_ARRAY_KEY][$offset];
        } else {
            return null;
        }
    }
    public function offsetSet($offset, $value): void
    {
        $this->meta[self::COUPONS_ARRAY_KEY][self::FIRST_ARRAY_KEY][$offset] = $value;
    }
    public function offsetUnset($offset): void
    {
        unset($this->meta[self::COUPONS_ARRAY_KEY][self::FIRST_ARRAY_KEY][$offset]);
    }
}
