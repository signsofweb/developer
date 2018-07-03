<?php

class Imaginato_ElasticSearchExt_Model_Product_Url extends  Enterprise_Catalog_Model_Product_Url
{
    protected function _getProductUrl($product, $requestPath, $routeParams)
    {
        $categoryId = $this->_getCategoryIdForUrl($product, $routeParams);

        if (!empty($requestPath)) {
            if ($categoryId) {
                if(!Mage::registry('category_request_path_' . $categoryId)){
                    $category = $this->_factory->getModel('catalog/category', array('disable_flat' => true))
                        ->load($categoryId);
                    if ($category->getId()) {
                        $categoryRewrite = $this->_factory->getModel('enterprise_catalog/category')
                            ->loadByCategory($category);
                        if ($categoryRewrite->getId()) {
                            $categoryRequestPath = $categoryRewrite->getRequestPath();
                            Mage::register('category_request_path_' . $categoryId , $categoryRequestPath);
                        }
                    }
                }else{
                    $categoryRequestPath = Mage::registry('category_request_path_' . $categoryId);
                }
                $requestPath = $categoryRequestPath . '/' . $requestPath;
            }
            $product->setRequestPath($requestPath);

            $storeId = $this->getUrlInstance()->getStore()->getId();
            $requestPath = $this->_factory->getHelper('enterprise_catalog')
                ->getProductRequestPath($requestPath, $storeId);

            return $this->getUrlInstance()->getDirectUrl($requestPath, $routeParams);
        }

        $routeParams['id'] = $product->getId();
        $routeParams['s'] = $product->getUrlKey();
        if ($categoryId) {
            $routeParams['category'] = $categoryId;
        }
        return $this->getUrlInstance()->getUrl('catalog/product/view', $routeParams);
    }
}