<?php

namespace PixelYourSite;

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<button type="button" class="button-remove-row button-remove-condition remove-conditions-row <?= count($definedCondition) == 1 ? 'hidden' : '' ?>">
    <i class="icon-delete" aria-hidden="true"></i>
</button>
