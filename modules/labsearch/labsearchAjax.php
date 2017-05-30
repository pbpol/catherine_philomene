<?php
include_once('../../config/config.inc.php');
include_once('../../init.php');
$product_link = new Link();
$ajaxSearch = Tools::getValue('ajaxSearch');
$searchquery = Tools::replaceAccentedChars(urldecode(Tools::getValue('q')));
if ($ajaxSearch) {
    $ajaxsearchResult = Search::find((int) (Tools::getValue('id_lang')), $searchquery, 1, 10, 'position', 'desc', true);
    foreach ($ajaxsearchResult as &$product) :
        $product['product_link'] = $product_link->getProductLink($product['id_product'], $product['prewrite'], $product['crewrite']);
        $cproduct_id = Product::getCover($product['id_product']);
        if (sizeof($cproduct_id) > 0) {
            $cproduct_image = new Image($cproduct_id['id_image']);
            $cproductimg_url = _PS_BASE_URL_ . _THEME_PROD_DIR_ . $cproduct_image->getExistingImgPath() . '-small_default' . '.jpg';
        }
        $product['ajaxsearchimage'] = $cproductimg_url;
    endforeach;
    die(Tools::jsonEncode($ajaxsearchResult));
}