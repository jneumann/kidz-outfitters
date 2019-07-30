<?php
	require_once('config.php');

	forEach($sc_inventory as $v) {
		$sku = $v->sku;
		$return = array();
		$results = $shopify('GET /admin/products.json?query=sku:' . $sku );

		foreach($results as $res) {
			// Shopify stores sku in variants
			foreach($res['variants'] as $var) {
				// If there are no results, shopify will return the first 50 products from the inventory
				if ($var['sku'] == $sku ) {
					array_push($return, $var['product_id']);
				}
			}
		}

		$name = $v->name;
		$description = $v->description;
		$category = $v->category;
		$size = $v->size;
		$color = $v->color;
		$gender = $v->brand;
		$inventory = $v->quantity;

		// Price from string to int to  float to string
		$price = (int)$v->retail / 100;
		$price = sprintf("%.2f", $price);

		/**
		 *
		 * Brand is mapped to gener
		 * Sku is set by SimpleConsign
		 *
		 */

		$images = array();

		$i = 1;
		foreach($v->images as $k => $v) {
			array_push($images, array( 'src' => $v ));
		}

		$options = array(
			array(
				'name' => 'Size',
				'position'=> 1,
				'value' => $size
			),
			array(
				'name' => 'Color',
				'position'=> 2,
				'value' => $color
			),
			array(
				'name' => 'Gender',
				'position'=> 3,
				'value' => $gender
			)
		);

		$return = array_unique($return);

		if (sizeof($return) == 0) {
			// This sku is not found in Shopify
			// Create new product

			$variants = array(
				array(
					"option1" => $size,
					"option2" => $color,
					"option3" => $gender,
					"price" => $price,
					"sku" => $sku,
					"inventory_management" => "shopify",
					"inventory_quantity" => $inventory
				)
			);

			$args = [
				"product" => array(
					"published_scope" => "global",
					"vendor" => 'SimpleConsign',
					"title"=> $name,
					"body_html"=> $description,
					"product_type"=> $category,
					"options" => $options,
					"images" => $images,
					"variants" => $variants
				)
			];

			try {
				$response = $sc->call('POST', '/admin/products.json', $args);
			} catch (ShopifyApiException $e) {
				var_dump( $e->getResponse() );
				die();
			}

			print_r ( $name . ' added to Shopify!<br />');
		} else {
			// This sku is found in Shopify
			// Update product

			$variants = array(
				'variant' => array(
					"price" => $price,
					"sku" => $sku,
					"inventory_management" => "shopify",
					"inventory_quantity" => $inventory
				)
			);

			$args = [
				"product" => array(
					"published_scope" => "global",
					"vendor" => 'SimpleConsign',
					"title"=> $name,
					"body_html"=> $description,
					"product_type"=> $category,
					"options" => $options,
					"images" => $images,
				)
			];

			try {
				$response = $sc->call('PUT', '/admin/products/' . $return['0'] . '.json', $args);
			} catch (ShopifyApiException $e) {
				var_dump( $e->getResponse() );
				die();
			}

			$var_id = $response['variants']['0']['id'];

			try {
				$response = $sc->call('PUT', '/admin/variants/' . $var_id . '.json', $variants);
			} catch (ShopifyApiException $e) {
				var_dump( $e );
				die();
			}

			print_r ( $name . ' updated in Shopify!<br />' );
		}
	}

	print_r( 'Done!' );
