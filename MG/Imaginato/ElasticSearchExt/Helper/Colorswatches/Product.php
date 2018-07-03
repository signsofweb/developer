<?php

class Imaginato_ElasticSearchExt_Helper_Colorswatches_Product{
    public function getSPConfigAsJSON(Mage_Catalog_Model_Product $product, $containerId = null){
        $config = Mage::helper('core')->jsonDecode($this->getJsonConfig($product));
        if (null !== $containerId) {
            $config['containerId'] = $containerId;
        }
        return Mage::helper('core')->jsonEncode($config);
    }

    public static function getOptionsPriceConfigAsJSON(Mage_Catalog_Model_Product $product){
        $config = array();
        if ($product->getTypeId() != 'configurable') {
            return Mage::helper('core')->jsonEncode($config);
        }

        $_request = Mage::getSingleton('tax/calculation')->getRateRequest(false, false, false);
        /* @var $product Mage_Catalog_Model_Product */
        $_request->setProductClassId($product->getTaxClassId());
        $defaultTax = Mage::getSingleton('tax/calculation')->getRate($_request);

        $_request = Mage::getSingleton('tax/calculation')->getRateRequest();
        $_request->setProductClassId($product->getTaxClassId());
        $currentTax = Mage::getSingleton('tax/calculation')->getRate($_request);

        $_regularPrice = $product->getPrice();
        $_finalPrice = $product->getFinalPrice();
        $_priceInclTax = Mage::helper('tax')->getPrice($product, $_finalPrice, true);
        $_priceExclTax = Mage::helper('tax')->getPrice($product, $_finalPrice);
        $_tierPrices = array();
        $_tierPricesInclTax = array();
        foreach ($product->getTierPrice() as $tierPrice) {
            $_tierPrices[] = Mage::helper('core')->currency($tierPrice['website_price'], false, false);
            $_tierPricesInclTax[] = Mage::helper('core')->currency(
                Mage::helper('tax')->getPrice($product, (int)$tierPrice['website_price'], true),
                false, false);
        }
        $config = array(
            'productId'           => $product->getId(),
            'priceFormat'         => Mage::app()->getLocale()->getJsPriceFormat(),
            'includeTax'          => Mage::helper('tax')->priceIncludesTax() ? 'true' : 'false',
            'showIncludeTax'      => Mage::helper('tax')->displayPriceIncludingTax(),
            'showBothPrices'      => Mage::helper('tax')->displayBothPrices(),
            'productPrice'        => Mage::helper('core')->currency($_finalPrice, false, false),
            'productOldPrice'     => Mage::helper('core')->currency($_regularPrice, false, false),
            'priceInclTax'        => Mage::helper('core')->currency($_priceInclTax, false, false),
            'priceExclTax'        => Mage::helper('core')->currency($_priceExclTax, false, false),
            /**
             * @var skipCalculate
             * @deprecated after 1.5.1.0
             */
            'skipCalculate'       => ($_priceExclTax != $_priceInclTax ? 0 : 1),
            'defaultTax'          => $defaultTax,
            'currentTax'          => $currentTax,
            'idSuffix'            => '_clone',
            'oldPlusDisposition'  => 0,
            'plusDisposition'     => 0,
            'plusDispositionTax'  => 0,
            'oldMinusDisposition' => 0,
            'minusDisposition'    => 0,
            'tierPrices'          => $_tierPrices,
            'tierPricesInclTax'   => $_tierPricesInclTax,
        );
        return Mage::helper('core')->jsonEncode($config);
        return $result;
    }


    public function getJsonConfig($product)
    {
        $attributes = array();
        $options    = array();
        $stockData  = array();
        $store      = Mage::app()->getStore();
        $taxHelper  = Mage::helper('tax');
        $searchHelper = Mage::helper('smile_elasticsearch');
        $currentProduct = $product;

        $allowedAttributes = Mage::registry('option_fields');
        $childrenIds = $currentProduct->getChildrenIds();
        $children = $currentProduct->getChildren();
        $configurableAttributes = array();
        for ($i = 0; $i < count($childrenIds); $i++) {
            $productId  = $childrenIds[$i];
            $product = $children[$productId]; // this is done to ensure media gallery is accessable
            $_attributes = array();

            foreach ($currentProduct->getData('supper_attribute_ids') as $productAttributeId) {

                $attribute =  $allowedAttributes[$productAttributeId];
                $attribute_code = $attribute['code'];
                $attribute_values = $currentProduct->getData($attribute_code);
                $attribute_labels = $currentProduct->getData("options_" . $attribute_code . '_' . $searchHelper->getLanguageCodeByStore($store));
                $attributeValue     = $attribute_values[$i];
                $attributeLabel    = $attribute_labels[$i];
                if(empty($attributeValue)){
                    continue;
                }
                if (!isset($options[$productAttributeId])) {
                    $options[$productAttributeId] = array();
                }

                if(isset($configurableAttributes[$productAttributeId])){
                    $configurableAttributes[$productAttributeId]['value'][$attributeValue] = $attributeLabel;
                }else{
                    $attribute['value'][$attributeValue] = $attributeLabel;
                    $configurableAttributes[$productAttributeId] = $attribute;
                }

                if (!isset($options[$productAttributeId][$attributeValue])) {
                    $options[$productAttributeId][$attributeValue] = array();
                }
                $options[$productAttributeId][$attributeValue][] = $productId;
                $OOS[$productAttributeId][$attributeValue] = !$product['in_stock'][0];
                $qty[$productAttributeId][$attributeValue] = $product['qty'][0];
                $pId[$productAttributeId][$attributeValue] = $productId;
                $sku[$productAttributeId][$attributeValue] = $product['sku'][0];
                $_attributes[$productAttributeId] =  $attributeValue;
            }
            unset($attribute);
            $stockData[] = array(
                'id'            => $productId,
                'sku'            => $product['sku'],
                'attributes'    => $_attributes,
                'outOfStock'    => $product['qty'][0] == 0 ? true : false,
                'qty'           => $product['qty'][0]
            );
        }

        $this->_resPrices = array(
            $this->_preparePrice($currentProduct->getMinPrice())
        );

        foreach ($configurableAttributes as  $attributeId => $attribute) {

            $info = array(
                'id'        => $attributeId,
                'code'      => $attribute['code'],
                'label'     => $attribute['label'],
                'options'   => array()
            );

            $optionPrices = array();
            $prices = $attribute['value'];
            if (is_array($prices)) {
                foreach ($prices as $key => $value) {
                    $currentProduct->setParentId(true);
                    $configurablePrice = $currentProduct->getMinPrice();

                    if (isset($options[$attributeId][$key])) {
                        $productsIndex = $options[$attributeId][$key];
                    } else {
                        $productsIndex = array();
                    }

                    if($OOS[$attributeId][$key])
                    {
                        $label = $value.' (' . Mage::helper('evisu_alertoos')->__('Out of Stock') . ')';
                    }
                    elseif($qty[$attributeId][$key] <=0)
                    {
                        $label = $value.' (' . Mage::helper('evisu_alertoos')->__('Preorder Only') . ')';
                    }
                    else
                    {
                        $label = $value;
                    }

                    $info['options'][] = array(
                        'id'            => $key,
                        'label'         => $label,
                        'price'         => $configurablePrice,
                        'oldPrice'      => $currentProduct->getPrice(),
                        'products'      => $productsIndex,
                        'outOfStock'    => $OOS[$attributeId][$key],
                        'qty'           => $qty[$attributeId][$key],
                        'productId'     => $pId[$attributeId][$key],
                        'sku'           => $sku[$attributeId][$key],
                    );
                    $optionPrices[] = $configurablePrice;
                }
            }

            $attributes[$attributeId] = $info;
        }

        $taxCalculation = Mage::getSingleton('tax/calculation');
        if (!$taxCalculation->getCustomer() && Mage::registry('current_customer')) {
            $taxCalculation->setCustomer(Mage::registry('current_customer'));
        }

        $_request = $taxCalculation->getRateRequest(false, false, false);
        $_request->setProductClassId($currentProduct->getTaxClassId());
        $defaultTax = $taxCalculation->getRate($_request);

        $_request = $taxCalculation->getRateRequest();
        $_request->setProductClassId($currentProduct->getTaxClassId());
        $currentTax = $taxCalculation->getRate($_request);

        $taxConfig = array(
            'includeTax'        => $taxHelper->priceIncludesTax(),
            'showIncludeTax'    => $taxHelper->displayPriceIncludingTax(),
            'showBothPrices'    => $taxHelper->displayBothPrices(),
            'defaultTax'        => $defaultTax,
            'currentTax'        => $currentTax,
            'inclTaxTitle'      => Mage::helper('catalog')->__('Incl. Tax')
        );
        $currentProduct->setAllowedAttributes($configurableAttributes);
        $currentProduct->setConfigurableAttributes($attributes);
        $config = array(
            'attributes'        => $attributes,
            'template'          => str_replace('%s', '#{price}', $store->getCurrentCurrency()->getOutputFormat()),
            'basePrice'         => $this->_registerJsPrice($this->_convertPrice($currentProduct->getMinPrice())),
            'oldPrice'          => $this->_registerJsPrice($this->_convertPrice($currentProduct->getPrice())),
            'productId'         => $currentProduct->getId(),
            'chooseText'        => Mage::helper('catalog')->__('Choose an Option...'),
            'taxConfig'         => $taxConfig,
            'stockData'         => $stockData,
            //'mediaData'         => $mediaData
        );

        return Mage::helper('core')->jsonEncode($config);
    }

    public function getMediaJSON($product)
    {
        $imageHelper = Mage::helper('catalog/image');
        $media = array();
        if ($product->getTypeId() == "simple"){
            return $media;
        }
        $attributeName='color_family';
        $allProductIdsArray = $product->getChildrenIds();
        $allProduct = $product->getChildren();
        $allColorFamily= $product->getData($attributeName);
        $product = Mage::getModel('catalog/product');
        for($i = 0; $i < count($allProductIdsArray); $i++){
            $product_id = $allProductIdsArray[$i];
            if(!isset($media[$allColorFamily[$i]])){

                $small_image = $allProduct[$product_id]['small_image'];
                $alt_small_image = $allProduct[$product_id]['alt_small_image'];
                $mainImageUrl =  (string)$imageHelper->init($product, 'small_image',$small_image)->resize(305,392);
                $hoverImageUrl =  (string)$imageHelper->init($product, 'alt_small_image',$alt_small_image)->resize(305,392);

                $media[$allColorFamily[$i]] = array(
                    'main' => $mainImageUrl,
                    'hover' =>$hoverImageUrl
                );
            }
        }

        return Mage::helper('core')->jsonEncode($media);
    }

    public function getAllowAttributes($product)
    {
        if ($product->getTypeId() == "simple"){
            return array();
        }
        return $product->getTypeInstance(true)
            ->getConfigurableAttributes($product);
    }

    protected function _preparePrice($price, $isPercent = false)
    {
        if ($isPercent && !empty($price)) {
            $price = $this->getProduct()->getMinPrice() * $price / 100;
        }

        return $this->_registerJsPrice($this->_convertPrice($price, true));
    }

    protected function _convertPrice($price, $round = false)
    {
        if (empty($price)) {
            return 0;
        }
        $store = Mage::app()->getStore();
        $price = $store->convertPrice($price);
        if ($round) {
            $price = $store->roundPrice($price);
        }

        return $price;
    }

    protected function _registerJsPrice($price)
    {
        return str_replace(',', '.', $price);
    }
}