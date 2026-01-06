<?php
require_once dirname( __FILE__ ) . '/../../inc/common.php';
require_once dirname( __FILE__ ) . '/../../inc/feedfbgooglepro.php';


?>
<?php if ( ! empty( $_REQUEST['edit'] ) || ( ! empty( $_REQUEST['feed_type'] ) && in_array( $_REQUEST['feed_type'], array(
			'fb_localize',
			'fb_country',
            'google_local_inventory'
		) ) ) ) { ?>
    <script> let wpwoof_current_page = 'edit_page'; </script>
<?php } ?>
<div class="wpwoof-box">
	<?php
	//trace($wpwoof_values);
	/* output store/back buttons */

	$WpWoofTopSave = "wpwoof-addfeed-button-top";
	include dirname( __FILE__ ) . '/../inc/store_action.php';

	$WpWoofTopSave = "";


	$all_fields = wpwoof_get_all_fields();

	$oFeedFBGooglePro = new FeedFBGooglePro();


	/* output settings part */
	include dirname( __FILE__ ) . '/../inc/types.php';
	include dirname( __FILE__ ) . '/../inc/settings.php';

	/* Output require fields */
	$oFeedFBGooglePro->renderFields( $all_fields['required'], $wpwoof_values );

	/* Output optinal fields */
	$oFeedFBGooglePro->renderFields( $all_fields['extra'], $wpwoof_values );

	$oFeedFBGooglePro->renderUTMFields( $wpwoof_values );
	?>
    <script>jQuery("select[name*='custom_label_'], select[name='field_mapping[gtin][value]'], select[name='field_mapping[mpn][value]']").fastselect();</script>
    <br/><br/>
    <hr class="wpwoof-break"/>
    <br/><br/>
	<?php include dirname( __FILE__ ) . '/../inc/store_action.php'; /* output store/back buttons */ ?>

	<?php if ( ! empty( $_REQUEST['edit'] ) ) { ?>
        <input type="hidden" name="edit_feed" value="<?php echo esc_attr( $_REQUEST['edit'] ); ?>">
	<?php } ?>
</div>
