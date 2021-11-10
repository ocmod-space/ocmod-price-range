<?php

/*
This file is subject to the terms and conditions defined in the "LICENSE.txt"
file that is part of this source code package and is also available on the
page: https://raw.githubusercontent.com/ocmod-space/license/main/LICENSE.txt.
*/

class ControllerExtensionModulePriceRange extends Controller {
	private $error = array();
	private $words = array('from', 'upto');

	public function index() {
		$this->load->language( 'extension/module/price_range');

		$data['heading_title']  = $this->language->get('heading_title');

		$this->document->setTitle($data['heading_title']);

		$this->load->model('setting/setting');

		if (('POST' == $this->request->server['REQUEST_METHOD']) && $this->validate()) {
			$this->model_setting_setting->editSetting('module_price_range', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect(
				$this->url->link(
					'marketplace/extension',
					'user_token=' . $this->session->data['user_token'] . '&type=module',
					true
				)
			);
		}

		if (isset($this->error['permission'])) {
			$data['error_permission'] = $this->error['permission'];
		} else {
			$data['error_permission'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link(
				'marketplace/extension',
				'user_token=' . $this->session->data['user_token'] . '&type=module',
				true
			),
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link(
				'extension/module/price_range',
				'user_token=' . $this->session->data['user_token'],
				true
			),
		);

		$data['action'] = $this->url->link(
			'extension/module/price_range', 'user_token=' . $this->session->data['user_token'],	true
		);

		$data['cancel'] = $this->url->link(
			'marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true
		);

		if (isset($this->request->post['module_price_range_status'])) {
			$data['status'] = $this->request->post['module_price_range_status'];
		} else {
			$data['status'] = $this->config->get('module_price_range_status');
		}

		if (isset($this->request->post['module_price_range'])) {
			$options = $this->request->post['module_price_range'];
		} elseif (is_array($this->config->get('module_price_range')))  {
			$options = $this->config->get('module_price_range');
		} else {
			$options = array();
		}

		if ($this->hasPriceRangeColumns()) {
			$data['has_price_range'] = true;
		} else {
			$data['has_price_range'] = false;
		}

		if (isset($options['view'])) {
			$data['view'] = $options['view'];
		} else {
			$store['view'] = 'list';
		}

		if (isset($options['style'])) {
			$data['style'] = $options['style'];
		} else {
			$data['style'] = 'full';
		}

		if (isset($options['cleanout'])) {
			$data['cleanout'] = $options['cleanout'];
		} else {
			$data['cleanout'] =  false;
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();
		$data['words'] = $this->words;
		$data['text'] = array();

		foreach ($data['languages'] as $language) {
			$language_id = $language['language_id'];
			$data['text'][$language_id] = array();

			foreach ($this->words as $word) {
				if (isset($options['text'][$language_id][$word])) {
					$data['text'][$language_id][$word] = $options['text'][$language_id][$word];
				} else {
					$data['text'][$language_id][$word] = '';
				}
			}
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/price_range', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/price_range')) {
			$this->error['permission'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function install() {
		// add 'min_price' and 'max_price' columns into 'product' table
		$this->load->model('extension/module/price_range');

		$this->model_extension_module_price_range->addPriceRangeColumns();

		// Add events
		$this->load->model('setting/event');

		$this->model_setting_event->deleteEventByCode('price_range_get');
		$this->model_setting_event->deleteEventByCode('price_range_add');
		$this->model_setting_event->deleteEventByCode('price_range_edit');
		$this->model_setting_event->deleteEventByCode('price_range_copy');
		$this->model_setting_event->deleteEventByCode('price_range_show');

		$this->model_setting_event->addEvent(
			'price_range_get',
			'admin/view/catalog/product_form/before',
			'extension/module/price_range/getPriceRange'
		);

		$this->model_setting_event->addEvent(
			'price_range_add',
			'admin/model/catalog/product/addProduct/after',
			'extension/module/price_range/addPriceRange'
		);

		$this->model_setting_event->addEvent(
			'price_range_edit',
			'admin/model/catalog/product/editProduct/after',
			'extension/module/price_range/editPriceRange'
		);

		$this->model_setting_event->addEvent(
			'price_range_copy',
			'admin/model/catalog/product/copyProduct/after',
			'extension/module/price_range/copyPriceRange'
		);

		$this->model_setting_event->addEvent(
			'price_range_show',
			'catalog/view/product/*/before',
			'extension/module/price_range/showPriceRange'
		);

		$this->model_setting_event->addEvent(
			'price_range_show',
			'catalog/view/extension/module/*/before',
			'extension/module/price_range/showPriceRange'
		);
	}

	public function uninstall() {
		// add 'min_price' and 'max_price' columns into 'product' table
		$this->load->model('extension/module/price_range');

		if (!empty($this->config->get('module_price_range')['cleanout'])) {
			$this->model_extension_module_price_range->delPriceRangeColumns();
		}

		// Delete events
		$this->load->model('setting/event');

		$this->model_setting_event->deleteEventByCode('price_range_get');
		$this->model_setting_event->deleteEventByCode('price_range_add');
		$this->model_setting_event->deleteEventByCode('price_range_edit');
		$this->model_setting_event->deleteEventByCode('price_range_copy');
		$this->model_setting_event->deleteEventByCode('price_range_show');
	}

	// admin/view/catalog/product_form/before
	public function getPriceRange(&$route, &$data) {
		if ($this->config->get('module_price_range_status')) {
			$data['price_range'] = true;

			$this->load->language('extension/module/price_range');

			$data['entry_min_price'] = $this->language->get('entry_min_price');
			$data['entry_max_price'] = $this->language->get('entry_max_price');

			if (isset($this->request->get['product_id']) &&	$this->request->server['REQUEST_METHOD'] != 'POST') {
			// open existing product to edit
				$this->load->model('extension/module/price_range');

				$price_range = $this->model_extension_module_price_range->getPriceRange($this->request->get['product_id']);

				// Leave empty place ('') instead 0 for convinient editing
				if ($price_range['min_price']) {
					$data['min_price'] = $price_range['min_price'];
				} else {
					$data['min_price'] = '';
				}

				if ($price_range['max_price']) {
					$data['max_price'] = $price_range['max_price'];
				} else {
					$data['max_price'] = '';
				}
			} else {
			// add new product
				$data['min_price'] = '';
				$data['max_price'] = '';
			}
		}
	}

	// admin/model/catalog/product/addProduct/after
	public function addPriceRange(&$route, &$args, $output) {
		if ($this->config->get('module_price_range_status')) {
			if (isset($this->request->post['min_price'])) {
				$min_price = (float)$this->request->post['min_price'];
			} else {
				$min_price = 0;
			}

			if (isset($this->request->post['max_price'])) {
				$max_price = (float)$this->request->post['max_price'];
			} else {
				$max_price = 0;
			}

			if ($min_price || $max_price) {
				$this->load->model('extension/module/price_range');

				$this->model_extension_module_price_range->addPriceRange($min_price, $max_price);
			}
		}
	}

	// admin/model/catalog/product/editProduct/after
	public function editPriceRange(&$route, &$args, $output) {
		if ($this->config->get('module_price_range_status')) {
			if (isset($args[0])) {
				$product_id = $args[0];
			} else {
				$product_id = 0;
			}

			if (isset($this->request->post['min_price'])) {
				$min_price = (float)$this->request->post['min_price'];
			} else {
				$min_price = 0;
			}

			if (isset($this->request->post['max_price'])) {
				$max_price = (float)$this->request->post['max_price'];
			} else {
				$max_price = 0;
			}

			if ($product_id) {
				$this->load->model('extension/module/price_range');

				$this->model_extension_module_price_range->editPriceRange($product_id, $min_price, $max_price);
			}
		}
	}

	// admin/model/catalog/product/copyProduct/after
	public function copyPriceRange(&$route, &$args, $output) {
		if ($this->config->get('module_price_range_status')) {
			if (isset($args[0])) {
				$product_id = $args[0];

				$this->load->model('extension/module/price_range');

				$price_range = $this->model_extension_module_price_range->getPriceRange($product_id);

				$min_price = $price_range['min_price'];
				$max_price = $price_range['max_price'];

				$this->model_extension_module_price_range->addPriceRange($min_price, $max_price);
			}
		}
	}

	// Check if "min_price" and "max_price" colums exist in "product" table
	// Check if "count_once" column is already exist in "option" table
	private function hasPriceRangeColumns() {
		$this->load->model('extension/module/price_range');

		return $this->model_extension_module_price_range->hasPriceRangeColumns();
	}

}
