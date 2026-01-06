<?php

/**
 * Integration. Manage post meta.
 *
 * @package WPDesk\Library\WPCoupons\PDF
 */
namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration;

/**
 * Manage post meta.
 *
 * @package WPDesk\Library\WPCoupons\Integration
 */
class PostMeta
{
    /**
     * Update public post meta.
     *
     * @param int    $post_id  Post ID.
     * @param string $meta_key Meta key.
     * @param mixed  $value    Value.
     *
     * @return int|bool
     */
    public function update($post_id, $meta_key, $value)
    {
        return update_post_meta($post_id, $meta_key, $value);
    }
    /**
     * Update private post meta.
     *
     * @param int    $post_id  Post ID.
     * @param string $meta_key Meta key.
     * @param mixed  $value    Value.
     *
     * @return int|bool
     */
    public function update_private($post_id, $meta_key, $value)
    {
        return update_post_meta($post_id, '_' . $meta_key, $value);
    }
    /**
     * Add public post meta.
     *
     * @param int    $post_id  Post ID.
     * @param string $meta_key Meta key.
     * @param mixed  $value    Value.te.
     *
     * @return int|bool
     */
    public function add($post_id, $meta_key, $value)
    {
        return add_post_meta($post_id, $meta_key, $value);
    }
    /**
     * Add private post meta.
     *
     * @param int    $post_id  Post ID.
     * @param string $meta_key Meta key.
     * @param mixed  $value    Value.te.
     *
     * @return int|bool
     */
    public function add_private($post_id, $meta_key, $value)
    {
        return add_post_meta($post_id, '_' . $meta_key, $value);
    }
    /**
     * @param $post_id
     * @param $meta_key
     *
     * @return bool
     */
    public function has($post_id, $meta_key): bool
    {
        return metadata_exists('post', $post_id, $meta_key);
    }
    /**
     * Get public post meta.
     *
     * @param int    $post_id  Post ID.
     * @param string $meta_key Meta key.
     * @param mixed  $default  Value.
     * @param bool   $single   Is single.
     *
     * @return mixed
     */
    public function get($post_id, $meta_key, $default = null, $single = \true)
    {
        $meta_value = get_post_meta($post_id, $meta_key, $single);
        if (!$this->has($post_id, $meta_key)) {
            return $default;
        }
        return $meta_value;
    }
    /**
     * Get private post meta.
     *
     * @param int    $post_id  Post ID.
     * @param string $meta_key Meta key.
     * @param mixed  $default  Value.
     * @param bool   $single   Is single.
     *
     * @return mixed
     */
    public function get_private($post_id, $meta_key, $default = null, $single = \true)
    {
        $meta_value = get_post_meta($post_id, '_' . $meta_key, $single);
        if (!$this->has($post_id, '_' . $meta_key)) {
            return $default;
        }
        return $meta_value;
    }
    /**
     * Delete public post meta.
     *
     * @param int    $post_id  Post ID.
     * @param string $meta_key Meta key.
     *
     * @return bool
     */
    public function delete($post_id, $meta_key)
    {
        return delete_post_meta($post_id, $meta_key);
    }
    /**
     * Delete private post meta.
     *
     * @param int    $post_id  Post ID.
     * @param string $meta_key Meta key.
     *
     * @return bool
     */
    public function delete_private($post_id, $meta_key)
    {
        return delete_post_meta($post_id, '_' . $meta_key);
    }
}
