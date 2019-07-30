<?php
	$data = '';
	$total = 0;
	$scOrder = array();

	$webhook = fopen('php://input', 'rb');
	while(!feof($webhook)) {
		$data .= fread($webhook, 4096);
	}
	fclose($webhook);

	// $data = '{"id":123456,"email":"jon@doe.ca","closed_at":null,"created_at":"2016-01-20T11:42:50-05:00","updated_at":"2016-01-20T11:42:50-05:00","number":234,"note":null,"token":null,"gateway":null,"test":true,"total_price":"234.94","subtotal_price":"224.94","total_weight":0,"total_tax":"0.00","taxes_included":false,"currency":"USD","financial_status":"voided","confirmed":false,"total_discounts":"5.00","total_line_items_price":"229.94","cart_token":null,"buyer_accepts_marketing":true,"name":"#9999","referring_site":null,"landing_site":null,"cancelled_at":"2016-01-20T11:42:50-05:00","cancel_reason":"customer","total_price_usd":null,"checkout_token":null,"reference":null,"user_id":null,"location_id":null,"source_identifier":null,"source_url":null,"processed_at":null,"device_id":null,"browser_ip":null,"landing_site_ref":null,"order_number":1234,"discount_codes":[],"note_attributes":[],"payment_gateway_names":["bogus"],"processing_method":"","checkout_id":null,"source_name":"web","fulfillment_status":"pending","tax_lines":[],"tags":"","contact_email":"jon@doe.ca","line_items":[{"id":56789,"variant_id":null,"title":"Sledgehammer","quantity":1,"price":"199.99","grams":5000,"sku":"SKU2006-001","variant_title":null,"vendor":null,"fulfillment_service":"manual","product_id":327475578523353102,"requires_shipping":true,"taxable":true,"gift_card":false,"name":"Sledgehammer","variant_inventory_management":null,"properties":[],"product_exists":true,"fulfillable_quantity":1,"total_discount":"0.00","fulfillment_status":null,"tax_lines":[]},{"id":98765,"variant_id":null,"title":"Wire Cutter","quantity":1,"price":"29.95","grams":500,"sku":"SKU2006-020","variant_title":null,"vendor":null,"fulfillment_service":"manual","product_id":327475578523353102,"requires_shipping":true,"taxable":true,"gift_card":false,"name":"Wire Cutter","variant_inventory_management":null,"properties":[],"product_exists":true,"fulfillable_quantity":1,"total_discount":"5.00","fulfillment_status":null,"tax_lines":[]}],"shipping_lines":[{"id":null,"title":"Generic Shipping","price":"10.00","code":null,"source":"shopify","phone":null,"tax_lines":[]}],"billing_address":{"first_name":"Bob","address1":"123 Billing Street","phone":"555-555-BILL","city":"Billtown","zip":"K2P0B0","province":"Kentucky","country":"United States","last_name":"Biller","address2":null,"company":"My Company","latitude":null,"longitude":null,"name":"Bob Biller","country_code":"US","province_code":"KY"},"shipping_address":{"first_name":"Steve","address1":"123 Shipping Street","phone":"555-555-SHIP","city":"Shippington","zip":"K2P0S0","province":"Kentucky","country":"United States","last_name":"Shipper","address2":null,"company":"Shipping Company","latitude":null,"longitude":null,"name":"Steve Shipper","country_code":"US","province_code":"KY"},"fulfillments":[],"refunds":[],"customer":{"id":null,"email":"john@test.com","accepts_marketing":false,"created_at":null,"updated_at":null,"first_name":"John","last_name":"Smith","orders_count":0,"state":"disabled","total_spent":"0.00","last_order_id":null,"note":null,"verified_email":true,"multipass_identifier":null,"tax_exempt":false,"tags":"","last_order_name":null,"default_address":{"id":null,"first_name":null,"last_name":null,"company":null,"address1":"123 Elm St.","address2":null,"city":"Ottawa","province":"Ontario","country":"Canada","zip":"K2H7A8","phone":"123-123-1234","name":"","province_code":"ON","country_code":"CA","country_name":"Canada","default":true}}}';

	$data = json_decode( $data );

	foreach ($data->line_items as $item) {
		if ($item['vendor'] == 'SimpleConsign') {
			$price = ( $item['price'] * 100 );

			$total += $price;
			$temp = array(
				"sku" => $item['sku'],
				"price" => $price,
				"quantity" => $item['quantity']
			);

			array_push($scOrder, $temp);
		}
	}


	$scData = array(
		"key" => SIMPLE_CONSIGN_KEY,
		"nonTaxableSaleTotal" => $total,
		"items" => $scOrder
	);
	$scData = json_encode($scData);
	$context = stream_context_create(array(
		'http' => array(
			'method' => "GET",
			'header' => "Accept: application/json\r\n".
									"Content-Type: application/json\r\n",
			'content' => $scData
		)
	));

	$sc_order = file_get_contents('http://user.traxia.com/app/api/inventory', false, $context);
	$sc_order = json_decode($sc_order);
	$sc_order = $sc_order->results;

	$lastError = error_get_last();
	if (is_null($lastError)) {
		// No error. Move along.
	} else {
		print_r($lastError);
		die();
	}
