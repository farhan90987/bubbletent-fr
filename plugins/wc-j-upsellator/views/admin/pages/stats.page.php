<?php

if (!defined('ABSPATH') || !is_admin()) {
    exit;
}

$tab = sanitize_text_field( $_GET['tab'] );

if( $tab == '' )         include( 'stats/base.page.php');
if( $tab == 'advanced' ) include( 'stats/advanced.page.php');
if( $tab == 'settings' ) include( 'stats/settings.page.php');

?>
