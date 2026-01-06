<?php

namespace PixelYourSite\SuperPack;

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use PixelYourSite;

function renderPixelIDs( $pixel ) {

	if ( PixelYourSite\SuperPack()->getOption( 'enabled' ) && PixelYourSite\SuperPack()->getCoreCompatible() && PixelYourSite\SuperPack()->getOption( 'additional_ids_enabled' ) ) {
		/** @noinspection PhpIncludeInspection */
		include PYS_SUPER_PACK_PATH . "/modules/superpack/views/html-$pixel-ids.php";

	}
}

function renderWPML( $pixel ) {
	if ( PixelYourSite\SuperPack()->getOption( 'enabled' ) && PixelYourSite\SuperPack()->getCoreCompatible() && isWPMLActive() ) {
		/** @noinspection PhpIncludeInspection */
		include PYS_SUPER_PACK_PATH . "/modules/superpack/views/html-$pixel-wpml.php";
	}
}

