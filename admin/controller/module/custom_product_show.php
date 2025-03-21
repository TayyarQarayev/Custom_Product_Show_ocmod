<?php
namespace Opencart\Admin\Controller\Extension\CustomProductShow\Module;

class CustomProductShow extends \Opencart\System\Engine\Controller
{
    /**
     * @return void
     */
    public function index(): void
    {
        $this->load->language('extension/custom_product_show/module/custom_product_show');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')
        ];

        if (!isset($this->request->get['module_id'])) {
            $data['breadcrumbs'][] = [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/custom_product_show/module/custom_product_show', 'user_token=' . $this->session->data['user_token'])
            ];
        } else {
            $data['breadcrumbs'][] = [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/custom_product_show/module/custom_product_show', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'])
            ];
        }

        if (!isset($this->request->get['module_id'])) {
            $data['save'] = $this->url->link('extension/custom_product_show/module/custom_product_show|save', 'user_token=' . $this->session->data['user_token']);
        } else {
            $data['save'] = $this->url->link('extension/custom_product_show/module/custom_product_show|save', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id']);
        }

        $data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');

        if (isset($this->request->get['module_id'])) {
            $this->load->model('setting/module');
            $module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
        } else {
            $module_info = [];
        }

        $data['name'] = $module_info['name'] ?? '';
        $data['twig_name'] = $module_info['twig_name'] ?? '';

        $this->load->model('catalog/product');

        $data['products'] = [];

        $products = $module_info['product'] ?? [];

        foreach ($products as $product_id) {
            $product_info = $this->model_catalog_product->getProduct($product_id);

            if ($product_info) {
                $data['products'][] = [
                    'product_id' => $product_info['product_id'],
                    'name' => $product_info['name']
                ];
            }
        }

        $data['status'] = $module_info['status'] ?? '';
        $data['module_id'] = $this->request->get['module_id'] ?? 0;
        $data['user_token'] = $this->session->data['user_token'];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/custom_product_show/module/custom_product_show', $data));
    }

    /**
     * @return void
     */
    public function save(): void
    {
        $this->load->language('extension/custom_product_show/module/custom_product_show');

        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/custom_product_show/module/custom_product_show')) {
            $json['error']['warning'] = $this->language->get('error_permission');
        }

        if ((oc_strlen($this->request->post['name']) < 2) || (oc_strlen($this->request->post['name']) > 50)) {
            $json['error']['name'] = $this->language->get('error_name');
        }

        if (!$json) {
            $this->load->model('setting/module');

            if (empty($this->request->post['module_id'])) {
                $json['module_id'] = $this->model_setting_module->addModule('custom_product_show.custom_product_show', $this->request->post);
            } else {
                $this->model_setting_module->editModule($this->request->post['module_id'], $this->request->post);
            }
            $json['success'] = $this->language->get('text_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
