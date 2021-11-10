<?php

/*
This file is subject to the terms and conditions defined in the "LICENSE.txt"
file that is part of this source code package and is also available on the
page: https://raw.githubusercontent.com/ocmod-space/license/main/LICENSE.txt.
*/

class ModelExtensionModulePriceRange extends Model {
	public function getPriceRanges($product_id, $quantity = 1) {
		$manual_range = $this->get($product_id);

		if ($manual_range) {
			$this->load->model('catalog/product');

			$manual_range['min'] *= $quantity;
			$manual_range['max'] *= $quantity;

			$product_info = $this->model_catalog_product->getProduct($product_id);

			$config_tax = $this->config->get('config_tax');

			if ($config_tax) {
				$price_range = array(
					'min' => $manual_range['min'] ? $this->tax->calculate($manual_range['min'], $product_info['tax_class_id'], $config_tax) : 0,
					'max' => $manual_range['max'] ? $this->tax->calculate($manual_range['max'], $product_info['tax_class_id'], $config_tax) : 0,
				);

				$price_range = $this->format($price_range);
				$extax_range = $this->format($manual_range);

				if ((float)$product_info['special']) {
					$co = (float)$product_info['special'] / (float)$product_info['price'];

					$special_range = array(
						'min' => $manual_range['min'] ? $this->tax->calculate($manual_range['min'] * $co, $product_info['tax_class_id'], $config_tax) : 0,
						'max' => $manual_range['max'] ? $this->tax->calculate($manual_range['max'] * $co, $product_info['tax_class_id'], $config_tax) : 0,
					);

					$special_range = $this->format($special_range);

					$extax_range = array(
						'min' => $manual_range['min'] * $co,
						'max' => $manual_range['max'] * $co,
					);

					$extax_range = $this->format($extax_range);
				} elseif ($this->model_catalog_product->getProductDiscounts($product_id)) {
					$discounts = array();

					foreach ($this->model_catalog_product->getProductDiscounts($product_id) as $discount) {
						$co = (float)$discount['price'] + ($manual_range['max'] - $manual_range['min']);

						$discount_extax_range = array(
							'min' => $manual_range['min'] ? (float)$discount['price'] + ($manual_range['min'] / $quantity - (float)$product_info['price']) : 0,
							'max' => $manual_range['max'] ? (float)$discount['price'] + ($manual_range['max'] / $quantity - (float)$product_info['price']) : 0,
						);

						$discount_extax_range['min'] *= $quantity;
						$discount_extax_range['max'] *= $quantity;

						$discount_range = array(
							'min' => $manual_range['min'] ? $this->tax->calculate($discount_extax_range['min'], $product_info['tax_class_id'], $config_tax) : 0,
							'max' => $manual_range['max'] ? $this->tax->calculate($discount_extax_range['max'], $product_info['tax_class_id'], $config_tax) : 0,
						);

						$discount_range = $this->format($discount_range);
						$discount_extax_range = $this->format($discount_extax_range);

						$discounts[] = array(
							'quantity' => $discount['quantity'],
							'price'    => $discount_range,
							'extax'    => $discount_extax_range,
						);

						if ($quantity >= $discount['quantity']) {
							$price_range = $discount_range;
							$extax_range = $discount_extax_range;
						}
					}
				}
			} else {
				$price_range = $this->format($manual_range);

				if ((float)$product_info['special']) {
					$co = (float)$product_info['special'] / (float)$product_info['price'];

					$special_range = array(
						'min' => $manual_range['min'] * $co,
						'max' => $manual_range['max'] * $co,
					);

					$special_range = $this->format($special_range);
				} elseif ($this->model_catalog_product->getProductDiscounts($product_id)) {
					$discounts = array();

					foreach ($this->model_catalog_product->getProductDiscounts($product_id) as $discount) {
						$discount_range = array(
							'min' => $manual_range['min'] ? (float)$discount['price'] + ($manual_range['min'] / $quantity - (float)$product_info['price']) : 0,
							'max' => $manual_range['max'] ? (float)$discount['price'] + ($manual_range['max'] / $quantity - (float)$product_info['price']) : 0,
						);

						$discount_range = $this->format($discount_range);

						$discounts[] = array(
							'quantity' => $discount['quantity'],
							'price'    => $discount_range,
						);

						if ($quantity >= $discount['quantity']) {
							$price_range = $discount_range;
						}
					}
				}
			}

			return array(
				'price'     => $price_range,
				'extax'     => $config_tax && isset($extax_range) ? $extax_range : null,
				'special'   => isset($special_range) ? $special_range : null,
				'discounts' => isset($discounts) ? $discounts : null,
			);
		}
	}

	// format currencies
	private function format($range) {
		$currency = $this->session->data['currency'];

		$style = $this->config->get('module_price_range')['style'];
		$text = $this->config->get('module_price_range')['text'][$this->config->get('config_language_id')];

		if ($range['min'] && !$range['max']) {
			$style = 'from';
		} elseif (!$range['min'] && $range['max']) {
			$style = 'upto';
		} elseif ($range['min'] === $range['max']) {
			return $this->currency->format($range['min'], $currency);
		}

		if ($style === 'from') {
			$range['min'] = $this->currency->format($range['min'], $currency);

			return $text['from'] . ' ' . $range['min'];
		} elseif ($style === 'upto') {
			$range['max'] = $this->currency->format($range['max'], $currency);

			return $text['upto'] . ' ' . $range['max'];
		} else {
			$range['min'] = $this->currency->format($range['min'], $currency);
			$range['max'] = $this->currency->format($range['max'], $currency);

			return $range['min'] . ' - ' . $range['max'];
		}
	}

	// Returns min and max price values from DB
	private function get($product_id) {
		// $query = $this->db->query('SELECT * FROM ' . DB_PREFIX . 'product WHERE product_id = "' . (int)$product_id . '"');
		$query = $this->db->query('SELECT min_price, max_price FROM ' . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");

		if (!isset($query->row['min_price']) || !isset($query->row['max_price'])) {
			return null;
		}

		$min = (float)$query->row['min_price'];
		$max = (float)$query->row['max_price'];

		if ($min == $max || ($min >= $max && $max != 0)) {
			return null;
		}

		return array(
			'min' => $min,
			'max' => $max,
		);
	}
}
