<?php

namespace FlexibleCouponsProVendor;

/**
 * Szablon do wyświetlania kuponów PDF na stronie "Moje konto".
 *
 * @var array{coupons?: array<int, array<string, mixed>>} $params
 * @var array<int, array<string, mixed>> $coupons
 */
$coupons = isset($params['coupons']) ? $params['coupons'] : [];
if (!empty($coupons)) {
    ?>
	<section class="woocommerce-coupons-file">
		<h2 class="woocommerce-column__title">
		<?php 
    \esc_html_e('PDF Coupons', 'flexible-coupons-pro');
    ?>
		</h2>

		<table class="shop_table shop_table_responsive my_account_orders">
			<thead>
			<tr>
				<th class="coupon-details"><span class="nobr">
				<?php 
    \esc_html_e('Coupon', 'flexible-coupons-pro');
    ?>
				</span></th>
				<?php 
    if (!empty($coupon['recipient_name']) || !empty($coupon['recipient_email'])) {
        ?>
					<th class="coupon-initial-value"><span class="nobr">
					<?php 
        \esc_html_e('Coupon recipient', 'flexible-coupons-pro');
        ?>
				</span></th>
				<?php 
    }
    ?>
				<th class="coupon-initial-value"><span class="nobr">
				<?php 
    \esc_html_e('Coupon value', 'flexible-coupons-pro');
    ?>
				</span></th>
				<th class="coupon-value-to-use"><span class="nobr">
				<?php 
    \esc_html_e('Value to use', 'flexible-coupons-pro');
    ?>
				</span></th>
				<th class="coupon-expiration-date"><span class="nobr">
				<?php 
    \esc_html_e('Expiration date', 'flexible-coupons-pro');
    ?>
				</span></th>
				<th class="coupon-download"><span class="nobr"></span></th>
			</tr>
			</thead>
			<tbody>
			<?php 
    foreach ($coupons as $coupon) {
        ?>
				<tr class="order">
					<td class="coupon-details" data-title="
					<?php 
        \esc_attr_e('Coupon', 'flexible-coupons-pro');
        ?>
					">
						<?php 
        if (!empty($coupon['product_url']) && !empty($coupon['product_name'])) {
            echo '<a href="' . \esc_url($coupon['product_url']) . '">' . \esc_html($coupon['product_name']) . '</a>';
        } elseif (!empty($coupon['product_name'])) {
            echo \esc_html($coupon['product_name']);
        }
        ?>
						<br>
						<small>
							<?php 
        if (!empty($coupon['coupon_code'])) {
            echo '<span><strong>' . \esc_html__('Coupon Code:', 'flexible-coupons-pro') . '</strong> ' . \esc_html($coupon['coupon_code']) . '<br></span>';
        }
        ?>
						</small>
					</td>
					<?php 
        if (!empty($coupon['recipient_name']) || !empty($coupon['recipient_email'])) {
            ?>
					<td class="coupon-recipient" data-title="
						<?php 
            \esc_attr_e('Coupon recipient', 'flexible-coupons-pro');
            ?>
					">
						<?php 
            if (!empty($coupon['recipient_name'])) {
                echo \esc_html($coupon['recipient_name']) . '<br>';
            }
            if (!empty($coupon['recipient_email'])) {
                echo \esc_html($coupon['recipient_email']);
            }
            ?>
					</td>
					<?php 
        }
        ?>
					<td class="coupon-initial-value" data-title="
					<?php 
        \esc_attr_e('Coupon value', 'flexible-coupons-pro');
        ?>
					">
						<?php 
        if (!empty($coupon['coupon_initial_value'])) {
            echo $coupon['coupon_initial_value'];
            //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
        ?>
					</td>
					<td class="coupon-value" data-title="
					<?php 
        \esc_attr_e('Coupon value', 'flexible-coupons-pro');
        ?>
					">
						<?php 
        if (!empty($coupon['coupon_value']) && $coupon['usage_limit'] === 'yes') {
            echo $coupon['coupon_value'];
            //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        } else {
            \esc_html_e('One-time use coupon', 'flexible-coupons-pro');
        }
        ?>
					</td>

					<td class="coupon-expiration-date" data-title="
					<?php 
        \esc_attr_e('Expiration date', 'flexible-coupons-pro');
        ?>
					">
						<?php 
        if (!empty($coupon['expiration_date'])) {
            echo \esc_html($coupon['expiration_date']);
        }
        ?>
					</td>
					<td style="text-align: center;">
						<?php 
        if (!empty($coupon['download_url'])) {
            echo '<a href="' . \esc_url($coupon['download_url']) . '" class="button woocommerce-button">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
                                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/>
                                     </svg>
                                </a>';
        }
        ?>
					</td>
				</tr>
				<?php 
    }
    ?>
			</tbody>
		</table>
	</section>
	<?php 
}
