<?php

namespace FlexibleCouponsProVendor;

/**
 * Template for editor meta box.
 *
 * @var array|string $editor_data
 * @var WP_Post $post
 * @var string $pro_url
 * @var bool $is_pl
 */
if (\is_array($editor_data)) {
    $editor_data['areaObjects'] = isset($editor_data['areaObjects']) ? \array_map(function ($object) {
        switch ($object['type']) {
            case 'image':
                $object['url'] = \esc_url($object['url']);
                break;
            case 'text':
            default:
                $object['text'] = \wp_kses_post($object['text']);
                break;
        }
        return $object;
    }, $editor_data['areaObjects']) : [];
}
?>
<script>
	window.WPDeskCanvaEditorData = <?php 
echo \wp_json_encode($editor_data, \JSON_NUMERIC_CHECK);
?>;
</script>
<div class="publishing-actions-box">
	<div class="black-box"></div>
	<div class="pro-link-box">
	<?php 
\printf(
    /* translators: %1$s: break line, %2$s: anchor opening tag, %3$s: anchor closing tag */
    \esc_html__('%1$sUpgrade to PRO →%2$s to fully unlock all the features', 'flexible-coupons-pro'),
    '<a href="' . \esc_url($pro_url) . '-button" target="_blank" class="pro-link">',
    '</a>'
);
?>
	</div>
	<span class="process_save"><span id="process_save_template"></span><span class="spinner"></span></span>
	<input name="save_wpdesk_canva_template" type="button" class="button button-primary button-large" id="save_wpdesk_canva_template" value="<?php 
\esc_attr_e('Save template', 'flexible-coupons-pro');
?>">
</div>
<div id="wpdesk-canva-root"></div>
<input type="hidden" id="editor_post_id" name="post_ID" value="<?php 
echo isset($post->ID) ? \esc_attr($post->ID) : '';
?>"/>
<?php 
