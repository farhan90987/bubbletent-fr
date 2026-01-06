<?php

namespace FlexibleCouponsProVendor;

/**
 * @var string $link
 * @var string $text
 * @var string $tooltip_text
 * @var bool|null $is_addon
 */
if (!isset($is_addon)) {
    $is_addon = \true;
}
$addon_text = $is_addon === \true ? \__('Add-on - ', 'flexible-coupons-pro') . $text : $text;
echo '<div class="form-field addon-pill-container">';
\printf('<a href="%s" target="_blank" class="addon-pill">', \esc_url($link . '&utm_content=edit-product'));
echo '<span class="addon-pill-text">' . \esc_html($addon_text) . '</span>';
echo '<span class="addon-pill-arrow"> →</span>';
echo '<span class="tooltip">' . \esc_html($tooltip_text) . '</span>';
echo '</a>';
echo '</div>';
