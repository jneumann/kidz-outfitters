<?php
	require_once('vendor/autoload.php');
	require_once('./shopify.php');

	use phpish\shopify;

	define('SIMPLE_CONSIGN_KEY', '***');

	define('SHOPIFY_SHOP', '***');
	define('SHOPIFY_APP_API_KEY', '***');
	define('SHOPIFY_APP_PASSWORD', '***');

	/**
	 * Set up some stuff that we need.
	 */
	$shopify = shopify\client(SHOPIFY_SHOP, SHOPIFY_APP_API_KEY, SHOPIFY_APP_PASSWORD, true);

	$sc = new ShopifyClient(SHOPIFY_SHOP, null, SHOPIFY_APP_API_KEY, SHOPIFY_APP_PASSWORD);

	$data = array(
		'key' => SIMPLE_CONSIGN_KEY,
		'includeItemsWithQuantityZero' => false
	);
	$data_string = json_encode($data);
	$context = stream_context_create(array(
		'http' => array(
			'method' => "GET",
			'header' => "Accept: application/json\r\n".
									"Content-Type: application/json\r\n",
			'content' => $data_string
		)
	));

	$sc_inventory = file_get_contents('http://user.traxia.com/app/api/inventory', false, $context);
	$sc_inventory = json_decode($sc_inventory);
	$sc_inventory = $sc_inventory->results;

	$lastError = error_get_last();
	if (is_null($lastError)) {
		// No error. Move along.
	} else {
		var_dump($lastError);
	}
