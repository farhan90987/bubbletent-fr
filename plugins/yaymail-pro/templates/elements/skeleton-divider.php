<?php
defined( 'ABSPATH' ) || exit;
use YayMail\Utils\TemplateHelpers;
use YayMail\Models\TemplateModel;
use YayMail\Elements\ElementsLoader;
use YayMail\Integrations\TranslationModule;

/**
 * $args includes
 * $element
 * $render_data
 * $is_nested
 */
if ( empty( $args['element'] ) ) {
    return;
}

$element = $args['element'];

ob_start();
?>
<div class="yaymail-skeleton-divider"><div class="yaymail-skeleton yaymail-skeleton-round css-dev-only-do-not-override-scpxro" style="margin-bottom: 10px;"><div class="yaymail-skeleton-content"><ul class="yaymail-skeleton-paragraph"><li style="width: 30%;"></li></ul></div></div><div class="yaymail-skeleton yaymail-skeleton-round yaymail-skeleton-divider__image css-dev-only-do-not-override-scpxro" style="margin-bottom: 10px;"><div class="yaymail-skeleton-content"><ul class="yaymail-skeleton-paragraph"><li style="width: 100%;"></li></ul></div></div><div class="yaymail-skeleton yaymail-skeleton-round css-dev-only-do-not-override-scpxro"><div class="yaymail-skeleton-content"><ul class="yaymail-skeleton-paragraph"><li style="width: 70%;"></li></ul></div></div><div class="yaymail-skeleton yaymail-skeleton-round css-dev-only-do-not-override-scpxro"><div class="yaymail-skeleton-content"><ul class="yaymail-skeleton-paragraph"><li style="width: 100%;"></li></ul></div></div><div class="yaymail-skeleton yaymail-skeleton-round css-dev-only-do-not-override-scpxro"><div class="yaymail-skeleton-content"><ul class="yaymail-skeleton-paragraph"><li style="width: 100%;"></li></ul></div></div></div>
<?php
$element_content = ob_get_clean();

TemplateHelpers::wrap_element_content( $element_content, $element );
