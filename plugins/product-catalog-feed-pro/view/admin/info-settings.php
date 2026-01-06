<?php
/**
 * Created by PhpStorm.
 * User: v0id
 * Date: 04.06.19
 * Time: 13:35
 */
$license_data = get_option( 'pcbpys_license_info', false );
$txt_status   = "";
$case_status  = "";
$txt_until    = ""; ?>

<div class="wpwoof-content wpwoof-box wpoof-settings-accordion">
	<div class="wpoof-settings-accordion-wrapper">
		<h2>License:</h2>
		<svg class="wpoof-settings-accordion-btn" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
			<!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
			<path
				d="M496 384H160v-16c0-8.8-7.2-16-16-16h-32c-8.8 0-16 7.2-16 16v16H16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h80v16c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16v-16h336c8.8 0 16-7.2 16-16v-32c0-8.8-7.2-16-16-16zm0-160h-80v-16c0-8.8-7.2-16-16-16h-32c-8.8 0-16 7.2-16 16v16H16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h336v16c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16v-16h80c8.8 0 16-7.2 16-16v-32c0-8.8-7.2-16-16-16zm0-160H288V48c0-8.8-7.2-16-16-16h-32c-8.8 0-16 7.2-16 16v16H16C7.2 64 0 71.2 0 80v32c0 8.8 7.2 16 16 16h208v16c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16v-16h208c8.8 0 16-7.2 16-16V80c0-8.8-7.2-16-16-16z"/>
		</svg>
	</div>
    <?php
	if ( $license_data ) {
		$txt_status  = $license_data->license == "valid" ? "Active" : ( $license_data->license == "expired" ? "<span style='color:red;'>EXPIRED</span>" : ucfirst( strtolower( $license_data->license ) ) );
		$case_status = $license_data->license;
		$txt_until   = ( $license_data->license == "disabled" ) ? "" : $license_data->expires;
	} else {
		$case_status = trim( get_option( 'pcbpys_license_status', '' ) );
		$txt_status  = $case_status == "valid" ? "Active" : "Inactive";
		$txt_until   = $case_status == "valid" ? "lifetime" : "";

	}

	switch ( $case_status ) {
		case "disabled":
		case "expired":
		case "valid":
        ?>
		<div class="wpoof-settings-accordion-content"><p>Status: <b><?php echo $txt_status; ?></b></p><?php
        if (!empty($txt_until) && $case_status != 'invalid') {
            ?><p>Your license key valid until: <?php echo $txt_until; ?></p><?php
        } ?>
		<form method="post" action="<?php echo admin_url() ?>?page=wpwoof-settings">
			<div> <?php /*class="wpwoof-aligncenter"*/ ?>
                <?php wp_nonce_field('pcbpys_nonce', 'pcbpys_nonce');

                if ($case_status == "expired") {
                    ?><br><p style="font-weight: bold;">Your license is not active: License has expired. <a
						target="_blank"
						href="<?php echo WPWOOF_SL_STORE_URL; ?>/checkout/?edd_license_key=<?php echo trim(get_option('pcbpys_license_key')); ?>&utm_campaign=admin&utm_source=licenses&utm_medium=renew">Please
						renew your license.</a> If you still see this error message after renewal please use "Update
					the license status" button.</p><?php
                } elseif ($case_status == "disabled") {
                    ?><br><p style="font-weight: bold;">Your license is not
					active<?= isset($license_data->license_status) ? ': <span style="color: red">' . $license_data->license_status . '</span>' : '' ?>
					.</p><?php
                }
                ?>
				<input type="submit" class="wpwoof-button wpwoof-button-blue" name="pcbpys_license_deactivate"
					   value="<?php _e('Deactivate License'); ?>"/>
				<input type="submit" class="wpwoof-button wpwoof-button-blue" name="pcbpys_license_update"
					   value="Update the License status"/>
			</div>
		</form>
		</div>
        <?php

			break;
		default :
			?>
            <form method="post" action="options.php">
			<?php settings_fields( 'pcbpys_license' ); ?>

            <div class="wpwoof-container">
                <div>
                    <input id="pcbpys_license_key" name="pcbpys_license_key" type="text" class="regular-text"
                           placeholder="<?php _e( 'Enter your license key' ); ?>"/>
                </div><?php
				if ( $case_status == "no activation left key" ) {
					?>
                    <p style="color: red">No activations left</p>
					<?php
					$case_status = "";
				} else if ( $case_status == "invalid" ) {
					?>
                    <p style="color: red">License is invalid</p>
					<?php
					$case_status = "";
				}

				?>
                <br/>
                <div>
					<?php wp_nonce_field( 'pcbpys_nonce', 'pcbpys_nonce' ); ?>
                    <input type="submit" class="wpwoof-button wpwoof-button-blue" name="pcbpys_license_activate"
                           value="<?php _e( 'Activate License' ); ?>"/>
                </div><?php

				if ( empty( $case_status ) ) {
					?>
                    <p>We sent you license key by email right after you bought the plugin, and you can also
                        find it inside <a target="_blank"
                                          href="<?php echo WPWOOF_SL_STORE_URL; ?>/my-account">your
                            account.</a></p>
                    <h4>If you bought a bundle, use the plugin specific license.</h4><?php
				}
				?>

            </form><?php
			break;
	} ?>
</div>