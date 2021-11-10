<?php

/*
This file is subject to the terms and conditions defined in the "LICENSE.txt"
file that is part of this source code package and is also available on the
page: https://raw.githubusercontent.com/ocmod-space/license/main/LICENSE.txt.
*/

class ModelExtensionModulePriceRange extends Model {
	public function getPriceRange($product_id = 0) {
		$price_range = array(
			'min_price' => 0,
			'max_price' => 0,
		);

		if ($product_id) {
			// $query = $this->db->query('SELECT * FROM ' . DB_PREFIX . 'product WHERE product_id = "' . (int)$product_id . '"');
			$query = $this->db->query('SELECT min_price, max_price FROM ' . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");

			$price_range['min_price'] = (float)abs($query->row['min_price']);
			$price_range['max_price'] = (float)abs($query->row['max_price']);
		}

		return $price_range;
	}

	public function addPriceRange($min_price = 0, $max_price = 0) {
		// Workaround to get the last product id
		$result = $this->db->query('SHOW TABLE STATUS LIKE "' . DB_PREFIX . 'product"');
		$product_id = ($result->row['Auto_increment'] - 1);

		if ($product_id) {
			$this->db->query('UPDATE ' . DB_PREFIX . 'product SET min_price = "' . (float)abs($min_price) . '", max_price = "' . (float)abs($max_price) . '" WHERE product_id = "' . (int)$product_id . '"');
		}
	}

	public function editPriceRange($product_id = 0, $min_price = 0, $max_price = 0) {
		if ($product_id) {
			$this->db->query('UPDATE ' . DB_PREFIX . 'product SET min_price = "' . (float)$min_price . '", max_price = "' . (float)$max_price . '" WHERE product_id = "' . (int)$product_id . '"');
		}
	}

	public function addPriceRangeColumns() {
		if (!$this->hasPriceRangeColumns()) {
			$this->db->query('ALTER TABLE ' . DB_PREFIX . 'product ADD COLUMN min_price decimal(15,4) NOT NULL DEFAULT "0.0000", ADD COLUMN max_price decimal(15,4) NOT NULL DEFAULT "0.0000"');

			return TRUE;
		}

		return FALSE;
	}

	public function delPriceRangeColumns() {
		if ($this->hasPriceRangeColumns()) {
			$this->db->query('ALTER TABLE ' . DB_PREFIX . 'product DROP COLUMN min_price, DROP COLUMN max_price');

			return TRUE;
		}

		return FALSE;
	}

	public function hasPriceRangeColumns() {
		$query = $this->db->query('SELECT * FROM ' . DB_PREFIX . 'product');

		if (isset($query->row['min_price']) && isset($query->row['max_price'])) {
			return true;
		}

		return false;
	}
}
