<?php

namespace PixelYourSite\SuperPack;

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
$serverUrl = ( isset( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] === 'on' ? "https" : "http" ) . "://$_SERVER[HTTP_HOST]";
?>

<div class="card about-params card-style3">
    <div class="card-header card-header-style2">
        <div class="d-flex align-items-center">
            <i class="icon-Info"></i>
            <h4 class="heading-with-icon bold-heading">Dynamic Parameters Help</h4>
        </div>
    </div>

    <div class="card-body" style="display: block;">
        <p class="mb-20">
            <span class="primary-text-color bold-heading">Important:</span>
            Don't use the Dynamic Parameters to send users' personal data with your events because it can trigger
            warnings or other similar actions.
        </p>

        <ul class="mb-20">
            <li>
                <span class="event-parameter-list">[id]</span> - it will pull the WordPress post ID
            </li>

            <li>
                <span class="event-parameter-list">[title]</span> - it will pull the content title
            </li>

            <li>
                <span class="event-parameter-list">[content_type]</span> - it will pull the post type (post, product,
                page and so on)
            </li>

            <li>
                <span class="event-parameter-list">[categories]</span> - it will pull the content categories
            </li>

            <li>
                <span class="event-parameter-list">[tags]</span> - it will pull the content tags
            </li>

            <li>
                <span class="event-parameter-list">[total]</span> - it will pull WooCommerce or EDD order's total when
                it exists
            </li>

            <li>
                <span class="event-parameter-list">[subtotal]</span> - it will pull WooCommerce or EDD order's subtotal
                when it exists
            </li>
        </ul>

        <p class="mb-20 primary-text-color bold-heading">
            Track URL parameters:
        </p>

        <p>
            Use <span class="event-parameter-list">[url_ParameterName]</span> where ParameterName = the name of the
            parameter.
        </p>

        <p>Example:</p>
        <p>This is your URL: <?= $serverUrl ?>?ParameterName=123</p>
        <p class="mb-20">The parameter value will be 123.</p>

        <p class="mb-20">
            <span class="primary-text-color bold-heading">Note:</span>
            if a parameter is missing from a particular page, the event won't include it.
        </p>

        <p class="mb-20 primary-text-color primary_heading">
            Track form parameters:
        </p>

        <p>
            Use <span class="event-parameter-list">[field_FieldName]</span> where FieldName = the name of the field.
        </p>

        <p>Example:</p>
        <p>This is your field name: filed-name</p>
        <p>The value of the dynamic parameter will be: [field_field-name]</p>
        <p class="mb-20">The parameter value will be the value of the field.</p>

        <p class="mb-20 primary-text-color bold-heading">
            Track MemberPress plugin parameters:
        </p>

        <p class="mb-20">
            These parameters only work on a "thankyou page" with shortcode
            <span class="event-parameter-list">[mepr-ecommerce-tracking]Message with %%variables%% in here[/mepr-ecommerce-tracking]</span>
        </p>
        <p class="mb-20">Available parameters are described <a
                    href="https://docs.memberpress.com/article/112-available-shortcodes" target="_blank" class="link">here</a>.
        </p>

        <p>All variables must have the prefix "mp_".</p>
        <p>Example:</p>
        <p>This is your MemberPress variable: total.</p>
        <p>The parameter value will be: [mp_total].</p>
    </div>
</div>