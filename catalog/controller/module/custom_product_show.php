<?php
namespace Opencart\Catalog\Controller\Extension\CustomProductShow\Module;
/**
 * Class CustomProductShow
 *
 * @package
 */
class CustomProductShow extends \Opencart\System\Engine\Controller
{
    /**
     * @return string
     */
    public function index(array $setting): string
    {
        $this->load->language('extension/custom_product_show/module/custom_product_show');

        $this->load->model('catalog/product');
        $this->load->model('tool/image');

        $data['products'] = [];

        if (!empty($setting['product'])) {
            $products = [];

            foreach ($setting['product'] as $product_id) {
                $product_info = $this->model_catalog_product->getProduct($product_id);

                if ($product_info) {
                    $products[] = $product_info;
                }
            }

            $width = !empty($setting['width']) ? $setting['width'] : 20;
            $height = !empty($setting['height']) ? $setting['height'] : 20;

            foreach ($products as $product) {

                if ($product['image']) {
                    $image = $this->model_tool_image->resize($product['image'], $width, $height);
                } else {
                    $image = $this->model_tool_image->resize('placeholder.png', $width, $height);
                }

                $data['products'][] = [
                    'product_id' => $product['product_id'],
                    'name' => $product['name'],
                    'thumb' => $image,
                    'alt' => $product['name'] . " image",
                    'price' => $this->currency->format($product['price'], $this->config->get('config_currency')),
                    'href' => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product['product_id'])
                ];
            }
        }

        if ($data['products']) {
            $route = 'extension/custom_product_show/module/';

            if (!empty($setting['twig_name']) && is_file(DIR_EXTENSION . "/custom_product_show/catalog/view/template/module/" . $setting['twig_name'] . '.twig')) {
                $route .= $setting['twig_name'];
            } else {
                $route .= 'custom_product_show';
            }

            return $this->load->view($route, $data);

        } else {
            return '';
        }
    }
}
