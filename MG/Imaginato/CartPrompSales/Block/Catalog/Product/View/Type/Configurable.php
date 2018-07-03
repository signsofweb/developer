<?php
class Imaginato_CartPrompSales_Block_Catalog_Product_View_Type_Configurable 
    extends Innoexts_Warehouse_Block_Catalog_Product_View_Type_Configurable 
{
	protected $_product;
    public function setProduct($product)
	{
		$this->_product = $product;
		return $this;
	}
	
	public function getProduct()
	{
		return $this->_product;
	}
	
	/**
     * Composes configuration for js
     *
     * @return string
     */
    public function getJsonConfig()
    {
        $attributes = array();
        $options    = array();
        $stockData  = array();
        $store      = $this->getCurrentStore();
        $taxHelper  = Mage::helper('tax');
        $currentProduct = $this->getProduct();
        $mediaData = array();

        $preconfiguredFlag = $currentProduct->hasPreconfiguredValues();
        if ($preconfiguredFlag) {
            $preconfiguredValues = $currentProduct->getPreconfiguredValues();
            $defaultValues       = array();
        }
        foreach ($this->getAllowProducts() as $product) {
            $productId  = $product->getId();
            $product = mage::getModel('catalog/product')->load($productId); // this is done to ensure media gallery is accessable
            /*if($product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_DISABLED){
                continue;
            }*/
            $_attributes = array();
            foreach ($this->getAllowAttributes() as $attribute) {
                $productAttribute   = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue     = $product->getData($productAttribute->getAttributeCode());
                if (!isset($options[$productAttributeId])) {
                    $options[$productAttributeId] = array();
                }

                if (!isset($options[$productAttributeId][$attributeValue])) {
                    $options[$productAttributeId][$attributeValue] = array();
                }
                $options[$productAttributeId][$attributeValue][] = $productId;
                $OOS[$productAttributeId][$attributeValue] = $product->getOutOfStock();
                $qty[$productAttributeId][$attributeValue] = $product->getStockItem()->getQty();
                $pId[$productAttributeId][$attributeValue] = $product->getId();
                $sku[$productAttributeId][$attributeValue] = $product->getSku();
                $_attributes[$productAttributeId] =  $attributeValue;
                if (($productAttribute->getAttributeCode() == 'color_family') && (!isset($mediaData[$attributeValue]))) {
                    //need to set registry product
                    $block = $this->getLayout()
                                ->createBlock('catalog/product_view_media')
                                ->setProduct($product)
                                ->setTemplate('cmsmart/productvideo/productmedia.phtml');
                                //->setTemplate('catalog/product/view/media.phtml');
                    $mediaData[$attributeValue] = $block->toHtml();
                }
            }
            $stockData[] = array(
                        'id'            => $productId,
                        'sku'            => $product->getSku(),
                        'attributes'    => $_attributes,
                        'outOfStock'    => $product->getStockItem()->getIsInStock()==0?true:($product->getStockItem()->getQty() == 0 ? true : false),
                        'qty'           => $product->getStockItem()->getQty(),
                        'maxSaleQty'    => $product->getStockItem()->getMaxSaleQty()
                        );


        }

        $this->_resPrices = array(
            $this->_preparePrice($currentProduct->getFinalPrice())
        );

        foreach ($this->getAllowAttributes() as $attribute) {
            $productAttribute = $attribute->getProductAttribute();
            $attributeId = $productAttribute->getId();
            $info = array(
                'id'        => $productAttribute->getId(),
                'code'      => $productAttribute->getAttributeCode(),
                'label'     => $attribute->getLabel(),
                'options'   => array()
            );

            $optionPrices = array();
            $prices = $attribute->getPrices();
            if (is_array($prices)) {
                foreach ($prices as $value) {
                    if(!$this->_validateAttributeValue($attributeId, $value, $options)) {
                        continue;
                    }
                    $currentProduct->setConfigurablePrice(
                        $this->_preparePrice($value['pricing_value'], $value['is_percent'])
                    );
                    $currentProduct->setParentId(true);
                    Mage::dispatchEvent(
                        'catalog_product_type_configurable_price',
                        array('product' => $currentProduct)
                    );
                    $configurablePrice = $currentProduct->getConfigurablePrice();

                    if (isset($options[$attributeId][$value['value_index']])) {
                        $productsIndex = $options[$attributeId][$value['value_index']];
                    } else {
                        $productsIndex = array();
                    }

                    /*if($OOS[$attributeId][$value['value_index']])
                    {
                        $label = $value['label'].' (' . Mage::helper('evisu_alertoos')->__('Out of Stock') . ')';
                    }
                    elseif($qty[$attributeId][$value['value_index']] <=0)
                    {
                        $label = $value['label'].' (' . Mage::helper('evisu_alertoos')->__('Preorder Only') . ')';
                    }
                    else
                    {
                        $label = $value['label'];
                    }*/

                    $info['options'][] = array(
                        'id'            => $value['value_index'],
                        'label'         => $value['label'],
                        'price'         => $configurablePrice,
                        'oldPrice'      => $this->_prepareOldPrice($value['pricing_value'], $value['is_percent']),
                        'products'      => $productsIndex,
                        'outOfStock'    => $OOS[$attributeId][$value['value_index']],
                        'qty'           => $qty[$attributeId][$value['value_index']],
                        'productId'     => $pId[$attributeId][$value['value_index']],
                        'sku'           => $sku[$attributeId][$value['value_index']],
                    );
                    $optionPrices[] = $configurablePrice;
                }
            }
            /**
             * Prepare formated values for options choose
             */
            foreach ($optionPrices as $optionPrice) {
                foreach ($optionPrices as $additional) {
                    $this->_preparePrice(abs($additional-$optionPrice));
                }
            }
            if($this->_validateAttributeInfo($info)) {
                $attributes[$attributeId] = $info;
            }

            // Add attribute default value (if set)
            if ($preconfiguredFlag) {
                $configValue = $preconfiguredValues->getData('super_attribute/' . $attributeId);
                if ($configValue) {
                    $defaultValues[$attributeId] = $configValue;
                }
            }
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

        $config = array(
            'attributes'        => $attributes,
            'template'          => str_replace('%s', '#{price}', $store->getCurrentCurrency()->getOutputFormat()),
            'basePrice'         => $this->_registerJsPrice($this->_convertPrice($currentProduct->getFinalPrice())),
            'oldPrice'          => $this->_registerJsPrice($this->_convertPrice($currentProduct->getPrice())),
            'productId'         => $currentProduct->getId(),
            'chooseText'        => Mage::helper('catalog')->__('Choose an Option...'),
            'taxConfig'         => $taxConfig,
            'stockData'         => $stockData,
            'mediaData'         => $mediaData
        );
        if ($preconfiguredFlag && !empty($defaultValues)) {
            $config['defaultValues'] = $defaultValues;
        }
        $config = array_merge($config, $this->_getAdditionalConfig());
        return Mage::helper('core')->jsonEncode($config);
    }

	/**
     * Get JSON encoded configuration array which can be used for JS dynamic
     * price calculation depending on product options
     *
     * @return string
     */
    public function getJsonOptionPriceConfig()
    {
        $config = array();
        if (!$this->hasOptions()) {
            return Mage::helper('core')->jsonEncode($config);
        }

        $_request = Mage::getSingleton('tax/calculation')->getRateRequest(false, false, false);
        /* @var $product Mage_Catalog_Model_Product */
        $product = $this->getProduct();
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

        $responseObject = new Varien_Object();
        Mage::dispatchEvent('catalog_product_view_config', array('response_object' => $responseObject));
        if (is_array($responseObject->getAdditionalOptions())) {
            foreach ($responseObject->getAdditionalOptions() as $option => $value) {
                $config[$option] = $value;
            }
        }

        return Mage::helper('core')->jsonEncode($config);
    }
}
