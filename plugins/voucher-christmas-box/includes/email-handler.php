<?php
// email-handler.php

// Function to send email to printer with order and customer details
function send_email_to_printer($order, $voucher_code, $printer_email) {
    log_to_debug("Preparing email for printer for Order #{$order->get_id()}");


        // Get the sequential order number if available

        $order_number = $order->get_meta('_order_number'); // Fetch sequential order number

        if (!$order_number) {
    
            $order_number = $order->get_id(); // Fallback to post ID if sequential number is not available
    
        }

    // Get customer details
    $customer_name = $order->get_formatted_billing_full_name();
    $customer_email = $order->get_billing_email();
    $customer_phone = $order->get_billing_phone();
    $shipping_address = $order->get_formatted_shipping_address(); // Get formatted shipping address

    // Get order items
    $items = '';
    foreach ($order->get_items() as $item) {
        $product_name = $item->get_name();
        $quantity = $item->get_quantity();
        $items .= "$product_name x $quantity<br>";
    }

    // Prepare email content
    $subject = "Order #$order_number - Print Voucher";

    $message = "
    <html>
    <body style='margin: 0; padding: 0;'>
        <div style='display: flex; flex-direction: column; min-height: 260mm;'>
            <!-- Top Half -->
            <div style='flex: 1; padding: 20px; display: flex;'>
                <div style='width: 50%;'>
                    <h2>Order Details</h2>
                     <p><strong>Order ID:</strong> $order_number</p>
                    <p><strong>Purchased Items:</strong><br>{$items}</p>
                    <p><strong>Voucher Code:</strong> $voucher_code</p>
                </div>
                <div style='width: 50%; padding: 20px;'>
                   
                    <h2>Sent To:</h2>
                    <div style='border: 1px solid #000; padding: 10px;'>
                        <p><strong>Name:</strong> $customer_name</p>
                        <p><strong>Email:</strong> <a href='mailto:$customer_email'>$customer_email</a></p>
                        <p><strong>Phone:</strong> $customer_phone</p>
                        <p><strong>Shipping Address:</strong><br>$shipping_address</p>
                    </div>
                </div>
            </div>
            <!-- Bottom Half -->
            <div style='padding: 95px 20px 20px 20px; text-align: left;'>
                <div style='padding-left: 30px;'>
                    <strong>Exp.</strong><br>
                    Bubble Tent Deutschland<br>
                    Eschenauer Str. 10<br>
                    90411 Nürnberg
                </div>
            </div>
            <div style='margin-top: auto; padding: 30px; text-align: right; padding-right: 30px;'>
               <div style='padding-right: 30px;'>
			  $shipping_address
			  </div>
            </div>
        </div>
    </body>
    </html>";

    // Set email headers
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers[] = 'From: Bubble Tent <no-reply@bubbletent.com>';

    // Send email
    $email_sent = wp_mail($printer_email, $subject, $message, $headers);

    if ($email_sent) {
        log_to_debug("Email sent to printer for Order #{$order_number}.");
    } else {
        log_to_debug("Failed to send email to printer for Order #{$order_number}.");
    }
}
