<?php
/**
 * Print invoice template
 *
 * @package cashier/modules/print-invoices/template/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<style type="text/css">
	body {
		font-family:"Helvetica Neue", Helvetica, Arial, Verdana, sans-serif;
	}
	h1 span {
		font-size:0.75em;
	}
	h2 {
		color: #333;
	}
	.no-page-break {
		page-break-after: avoid;
	}
	#wrapper {
		margin:0 auto;
		width:80%;
		page-break-after: always;
	}
	#wrapper_last {
		margin:3em auto;
		width:80%;
		page-break-after: avoid;
	}
	.address{
		width:98%;
		border-top:1px;
		border-right:1px;
		margin:1em auto;
		border-collapse:collapse;
	}
	.address_border{
		border-bottom:1px;
		border-left:1px ;
		padding:.2em 1em;
		text-align:left;
	}
	table {
		width:98%;
		border-top:1px solid #e5eff8;
		border-right:1px solid #e5eff8;
		margin:1em auto;
		border-collapse:collapse;
		font-size:10pt;
	}
	td {
		border-bottom:1px solid #e5eff8;
		border-left:1px solid #e5eff8;
		padding:.3em 1em;
		text-align:center;
	}
	tr.odd td,
	tr.odd .column1 {
		background:#f4f9fe url(background.gif) no-repeat;
	}
	.column1 {
		background:#f4f9fe;
	}
	thead th {
		background:#f4f9fe;
		text-align:center;
		font:bold 1.2em/2em "Century Gothic","Trebuchet MS",Arial,Helvetica,sans-serif;
	}
	.cfw_datagrid {
		position: relative;
		top:-30pt;
	}
	.producthead{ 
		text-align: left;
	}
	.pricehead{
		text-align: right;
	}
	.cfw_address_div{
		position: relative;
		left:28pt;
	}
	.cfw_email_span{
		position: relative;
		left:10pt;
	}
</style>
<?php
	$order_data           = $order->get_data();
	$order_date           = $order->get_date_created()->date( 'Y-m-d H:i:s' );
	$billing_email        = $order_data['billing']['email'];
	$billing_phone        = $order_data['billing']['phone'];
	$customer_note        = $order_data['customer_note'];
	$order_discount       = $order_data['discount_total'];
	$order_total          = $order_data['total'];
	$payment_method_title = $order_data['payment_method_title'];
	$coupons              = $order->get_coupon_codes();

	$date_format = get_option( 'date_format' );

if ( is_plugin_active( 'woocommerce-sequential-order-numbers-pro/woocommerce-sequential-order-numbers.php' ) ) {
	$purchase_display_id = ( isset( $order_data->order_custom_fields['_order_number_formatted'][0] ) ) ? $order_data->order_custom_fields['_order_number_formatted'][0] : $order_id;
} else {
	$purchase_display_id = $order_id;
}

	echo '<div id="wrapper_last">';
	echo '<div>';
	echo '<h4 style="font:bold 1.2em/2em "Century Gothic","Trebuchet MS",Arial,Helvetica,sans-serif;
                position:relative; 12pt;">&nbsp; ' . esc_attr( get_bloginfo( 'name' ) ) . '</h4>';
	echo '</br> <table class="address" style="position:relative; top:-22pt; left:-35pt;">';
	echo '<tr><td class="address_border" colspan="2" valign="top" width="50%"><span style="position:relative; left:27pt; top:10pt;">
            <b>Order # ' . esc_attr( $purchase_display_id ) . ' - ' . esc_attr( gmdate( $date_format, strtotime( $order_date ) ) ) . '</b></span><br/></td></tr>';
	echo '<tr><td class="address_border" width="35%" align="center"><br/><div class="cfw_address_div">';

	$formatted_billing_address = $order->get_formatted_billing_address();
if ( '' !== $formatted_billing_address ) {
	echo '<b>' . esc_attr__( 'Billing Address', 'cashier' ) . '</b><p>';
	echo wp_kses_post( $formatted_billing_address );
	echo '</p></td>';
}

	$formatted_shipping_address = $order->get_formatted_shipping_address();
if ( '' !== $formatted_shipping_address ) {
	echo '<td class="address_border" width="30%"><br/><div style="position:relative; top:3pt;"><b>' . esc_attr__( 'Shipping Address', 'cashier' ) . '</b><p>';
	echo wp_kses_post( $formatted_shipping_address );
	echo '</p></div></td>';
}

	echo '</tr>';
	echo '<tr><td colspan="2" class="address_border"><span class="cfw_email_span"><table class="address"><tr><td colspan="2" class="address_border" >
            <b>' . esc_attr__( 'Email id', 'cashier' ) . ':</b> ' . esc_attr( $billing_email ) . '</td></tr>
            <tr><td class="address_border"><b>' . esc_attr__( 'Tel', 'cashier' ) . ' :</b> ' . esc_attr( $billing_phone ) . '</td></tr></table> </span></td></tr>';
	echo '</table>';
	echo '<div class="cfw_datagrid"><table><tr class="column1">
            <td class="producthead">' . esc_attr__( 'Product', 'cashier' ) . '</td><td>' . esc_attr__( 'SKU', 'cashier' ) . '</td>
            <td>' . esc_attr__( 'Quantity', 'cashier' ) . '</td><td class="pricehead">' . esc_attr__( 'Price', 'cashier' ) . '</td></tr>';

	$total_order = 0;

foreach ( $order->get_items() as $order_item ) {
	$_product      = wc_get_product( $order_item['product_id'] );
	$_product_data = $_product->get_data();

	$item                = $order_item->get_data();
	$formatted_variation = ( ! empty( $_product ) ) ? wc_get_formatted_variation( $_product, true ) : '';
	$sku                 = '';
	$variation           = '';
	$qty                 = $order_item['qty'];
	$sku                 = ( ! empty( $_product ) ) ? $_product->get_sku() : '';
	$variation           = ( ! empty( $formatted_variation ) ) ? ' (' . $formatted_variation . ')' : '';
	$item_total          = $order_item->get_total();
	$total_order        += $item_total;
	echo '<tr><td class="producthead">';
	echo esc_attr( $item['name'] ) . esc_attr( $variation );
	echo '</td><td>' . esc_attr( $sku ) . '</td><td>';
	echo esc_attr( $qty );
	echo '</td><td class="pricehead">';
	echo wp_kses_post( wc_price( $item_total ) );
	echo '</td></tr>';
}

	echo '<tr><td colspan="2" rowspan="5" class="address_border" valign="top"><br/>
            <i>' . ( ( '' !== $customer_note ) ? esc_attr__( 'Order Notes', 'cashier' ) . ' : ' . esc_attr( $customer_note ) : '' ) . '</i></td><td style="text-align:right;" class="address_border" valign="top">
            <b>Subtotal </b></td><td class="pricehead">' . wp_kses_post( $order->get_subtotal_to_display() ) . '</td></tr>';
	echo '<tr><td style="text-align:right;" class="address_border"><b>' . esc_attr__( 'Shipping', 'cashier' ) . ' </b></td><td class="pricehead">' . wp_kses_post( $order->get_shipping_to_display() ) . '</td></tr>';

if ( $order_discount > 0 ) {
	$order_discount = wc_price( $order_discount );
	echo '<tr><td style="text-align:right;" class="address_border"><b>' . esc_attr__( 'Order Discount', 'cashier' ) . ' </b></td>';
	echo '<td class="pricehead">' . esc_attr( $order_discount ) . '</td></tr>';
}

	$order_tax   = wc_price( $order->get_total_tax() );
	$order_total = wc_price( $order_total );

	echo '<tr><td style="text-align:right;" class="address_border"><b>' . esc_attr__( 'Tax', 'cashier' ) . ' </b></td><td class="pricehead">' . wp_kses_post( $order_tax ) . '</td></tr>';
	echo '<tr><td class="column1" style="text-align:right;"><b>' . esc_attr__( 'Total', 'cashier' ) . ' </b></td><td class="column1" style="text-align:right;">' . wp_kses_post( $order_total ) . ' -via ' . esc_attr( $payment_method_title ) . '</td></tr>';
	echo '</table></div></div>';
if ( ! empty( $coupons ) ) {
	echo '<strong>&nbsp;' . esc_attr__( 'Coupon used: ', 'cashier' ) . '</strong>';
	echo wp_kses_post( implode( '</span>, <span class="cfw_coupon">', $coupons ) );
}
	echo '</div>';
