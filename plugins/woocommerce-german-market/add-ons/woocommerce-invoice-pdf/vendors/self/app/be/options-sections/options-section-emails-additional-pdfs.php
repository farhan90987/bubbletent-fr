<?php
$options	= array(	
	
				array( 'title' 		=> __( 'Emails Legal Texts PDFs', 'woocommerce-german-market' ), 'type' => 'title','desc' => '', 'id' => 'wp_wc_invoice_pdf_emails' ),
				

				array(
						'name'		=> __( 'Customer Order Confirmation', 'woocommerce-german-market' ),
						'desc_tip'	=> __( 'Add additonal PDFs as an attachment to "Customer Order Confirmation" email', 'woocommerce-german-market' ) . '<br />' . __( 'Customer Order Confirmation emails are sent after a successful customer order.', 'woocommerce-german-market' ),
						'tip'  		=> __( 'Add additonal PDFs as an attachment to "Customer Order Confirmation" email', 'woocommerce-german-market' ) . '<br />' . __( 'Customer Order Confirmation emails are sent after a successful customer order', 'woocommerce-german-market' ),
						'id'   		=> 'wp_wc_invoice_pdf_emails_customer_order_confirmation_add_pdfs',
						'type' 		=> 'wgm_ui_checkbox',
						'default'  	=> 'off',
						
					),

				array(
						'name'		=> __( 'New Order', 'woocommerce-german-market' ),
						'desc_tip'	=> __( 'Add additonal PDFs as an attachment to "New Order" email', 'woocommerce-german-market' ) . '<br />' . __( 'New order emails are sent to chosen recipient(s) when an order is received.', 'woocommerce-german-market' ),
						'tip'  		=> __( 'Add additonal PDFs as an attachment to "New Order" email', 'woocommerce-german-market' ) . '<br />' . __( 'New order emails are sent to chosen recipient(s) when an order is received.', 'woocommerce-german-market' ),
						'id'   		=> 'wp_wc_invoice_pdf_emails_new_order_add_pdfs',
						'type' 		=> 'wgm_ui_checkbox',
						'default'  	=> 'off',
						
					),
										
				array(
						'name'		=> __( 'Customer Invoice', 'woocommerce-german-market' ),
						'desc_tip' 	=> __( 'Add additonal PDFs as an attachment to "Customer invoice" email', 'woocommerce-german-market' ) . '<br />' . __( 'Customer invoice emails can be sent to the user containing order info and payment links.', 'woocommerce-german-market' ),
						'tip'  		=> __( 'Add additonal PDFs as an attachment to "Customer invoice" email', 'woocommerce-german-market' ) . '<br /> ' . __( 'Customer invoice emails can be sent to the user containing order info and payment links.', 'woocommerce-german-market' ),
						'id'   		=> 'wp_wc_invoice_pdf_emails_customer_invoice_add_pdfs',
						'type' 		=> 'wgm_ui_checkbox',
						'default'  	=> 'off',
						
				),

				array(
						'name'		=> __( 'Customer On-Hold', 'woocommerce-german-market' ),
						'desc_tip' 	=> __( 'Add additonal PDFs as an attachment to "Customer on-hold" email', 'woocommerce-german-market' ) . '<br />' . __( 'Customer on-hold emails can be sent to customers containing order details after an order is placed on-hold.', 'woocommerce-german-market' ),
						'tip'  		=> __( 'Add additonal PDFs as an attachment to "Customer on hold" email', 'woocommerce-german-market' ) . '<br /> ' . __( 'Customer on-hold emails can be sent to customers containing order details after an order is placed on-hold.', 'woocommerce-german-market' ),
						'id'   		=> 'wp_wc_invoice_pdf_emails_customer_on_hold_order_add_pdfs',
						'type' 		=> 'wgm_ui_checkbox',
						'default'  	=> 'off',
						
				),
				
				array(
						'name' 		=> __( 'Customer Processing Order', 'woocommerce-german-market' ),
						'desc_tip' 	=> __( 'Add additonal PDFs as an attachment to "Customer processing order" email', 'woocommerce-german-market' ) . '<br />' . __( 'This is an order notification sent to the customer after payment containing order details.', 'woocommerce-german-market' ),
						'tip'  		=> __( 'Add additonal PDFs as an attachment to "Customer processing order" email', 'woocommerce-german-market' ) . '<br />' . __( 'This is an order notification sent to the customer after payment containing order details.', 'woocommerce-german-market' ),
						'id'   		=> 'wp_wc_invoice_pdf_emails_customer_processing_order_add_pdfs',
						'type' 		=> 'wgm_ui_checkbox',
						'default' 	=> 'off',
						
					),	
										
				array(
						'name' 		=> __( 'Customer Completed Order', 'woocommerce-german-market' ),
						'desc_tip' 	=> __( 'Add additonal PDFs as an attachment to "Customer completed order" email', 'woocommerce-german-market' ) . '<br />' . __( 'Order complete emails are sent to the customer when the order is marked complete and usual indicates that the order has been shipped.', 'woocommerce-german-market' ),
						'tip'  		=> __( 'Add additonal PDFs as an attachment to "Customer completed order" email', 'woocommerce-german-market' ) . '<br /> ' . __( 'Order complete emails are sent to the customer when the order is marked complete and usual indicates that the order has been shipped.', 'woocommerce-german-market' ),
						'id'   		=> 'wp_wc_invoice_pdf_emails_customer_completed_order_add_pdfs',
						'type' 		=> 'wgm_ui_checkbox',
						'default' 	=> 'off',	
					),

				array(
						'name' 		=> __( 'Refunded Order', 'woocommerce-german-market' ),
						'desc_tip' 	=> __( 'Add additional PDFs as an attachment to "Customer refunded order" email', 'woocommerce-german-market' ) . '<br />' . __( 'Order refunded emails are sent to the customer when the order is marked refunded', 'woocommerce-german-market' ),
						'tip'  		=> __( 'Add refund pdf as an attachment to "Customer refunded order" email', 'woocommerce-german-market' ) . '<br />' . __( 'Order refunded emails are sent to the customer when the order is marked refunded', 'woocommerce-german-market' ),
						'id'   		=> 'wp_wc_invoice_pdf_emails_customer_refunded_order_add_pdfs',
						'type' 		=> 'wgm_ui_checkbox',
						'default' 	=> 'off',
					),

				array(
						'name' 		=> __( 'Customer Note', 'woocommerce-german-market' ),
						'desc_tip' 	=> __( 'Customer note emails are sent when you add a note to an order.', 'woocommerce-german-market' ),
						'tip'  		=> __( 'Customer note emails are sent when you add a note to an order.', 'woocommerce-german-market' ),
						'id'   		=> 'wp_wc_invoice_pdf_emails_customer_note_add_pdfs',
						'type' 		=> 'wgm_ui_checkbox',
						'default' 	=> 'off',
					),
									
				array( 'type' => 'sectionend', 'id' => 'wp_wc_invoice_pdf_emails' )
	
);

$options = apply_filters( 'gm_invoice_pdf_email_settings_additonal_pdfs', $options );