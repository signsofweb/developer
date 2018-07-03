<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento enterprise edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Colorswatches
 * @version    1.1.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class Imaginato_ElasticSearchExt_Helper_Colorswatches_Swatch extends AW_Colorswatches_Helper_Swatch
{
    protected $_swatches = array();
    protected $_options = array();

    protected $_enabledSwatchAttributes = null;
    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return array
     */
    public function getSwatchAttributeCollectionForProduct(Mage_Catalog_Model_Product $product)
    {
        if ($product->getTypeId() != 'configurable') {
            return array();
        }
        $attribute_ids = $product->getData('supper_attribute_ids');
        $returnSwatchAttributes = array();
        if(!$this->_enabledSwatchAttributes){
            /** @var AW_Colorswatches_Model_Resource_Swatchattribute_Collection $swatchAttributeCollection */
            $swatchAttributeCollection = Mage::getModel('awcolorswatches/swatchattribute')->getCollection();
            $swatchAttributeCollection
                ->addIsEnabledFilter();
            foreach($swatchAttributeCollection as $swatchAttribute){
                $this->_enabledSwatchAttributes[$swatchAttribute->getAttributeId()] = $swatchAttribute;
            }
        }
        if(!empty($attribute_ids)){
            foreach ($attribute_ids as $attribute_id){
                $returnSwatchAttributes[$attribute_id] = $this->_enabledSwatchAttributes[$attribute_id];
            }
        }
        return $returnSwatchAttributes;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute_Collection
     */
    public function getConfigurableAttributeCollectionForProduct(Mage_Catalog_Model_Product $product)
    {
        if ($product->getTypeId() =="simple"){
            return array();
        }
        return $product->getAllowedAttributes();
    }

    /**
     * @param AW_Colorswatches_Model_Resource_Swatchattribute_Collection $swatchCollection
     * @param int $attributeId
     *
     * @return AW_colorswatches_Model_Swatch|null
     */
    public function getSwatchAttributeFromCollectionByAttributeId($swatchCollection, $attributeId)
    {
        return $swatchCollection->getItemByColumnValue('attribute_id', $attributeId);
    }

    /**
     * @param AW_Colorswatches_Model_Swatchattribute $swatchAttribute
     * @param Mage_Catalog_Model_Product $product
     * @param int $imgWidth
     * @param int $imgHeight
     * @param int $tooltipWidth
     * @param int $tooltipHeight
     *
     * @return array
     */
    public function getOptionDataForSwatch(
        AW_Colorswatches_Model_Swatchattribute $swatchAttribute, Mage_Catalog_Model_Product $product,
        $imgWidth = 100, $imgHeight = 100, $tooltipWidth = 300, $tooltipHeight = 300
    ) {
        $searchHelper = Mage::helper('smile_elasticsearch');
        $store = Mage::app()->getStore();
        $totalAttributeCount = count($this->getConfigurableAttributeCollectionForProduct($product));
        $isCanOverrideWithChild = $swatchAttribute->getIsOverrideWithChild() && $totalAttributeCount === 1;
        $attribute_id = $swatchAttribute->getAttributeId();
        if(!isset($this->_swatches[$attribute_id])){
            $collection = $swatchAttribute->getSwatchCollection();
            foreach($collection as $swatch){
                $this->_swatches[$attribute_id][$swatch->getOptionId()] = array(
                    'image' => $swatch->getImage(),
                    'sort_order' => $swatch->getSortOrder()
                );
            }
        }

        $childProducts = $product->getChildren();
        $childIds = $product->getChildrenIds();
        $childOptions = $product->getData($swatchAttribute->getAttributeModel()->getAttributeCode());
        $childOptionLabels = $product->getData('options_' . $swatchAttribute->getAttributeModel()->getAttributeCode() . '_' . $searchHelper->getLanguageCodeByStore($store));
        $result = array();

        /** @var AW_Colorswatches_Model_Swatch $swatch */
        $productList = array();
        $notSaleableList = array();
        for($i = 0; $i < count($childIds); $i++) {
            $child = $childProducts[$childIds[$i]];
            $value = $childOptions[$i];
            $valueLabel = $childOptionLabels[$i];
            $productList[$value][] = $child['entity_id'][0];
            if (!($child['in_stock'][0])) {
                $notSaleableList[$value][] = $child['entity_id'][0];
            }
            if ($isCanOverrideWithChild) {
                $childProduct = Mage::getModel('catalog/product');
                $image = Mage::helper('catalog/image')->init($childProduct, 'image', $child['thumbnail'])->resize($imgWidth, $imgWidth)->__toString();
                $ttImage = Mage::helper('catalog/image')->init($childProduct, 'image', $child['thumbnail'])->resize($tooltipWidth, $tooltipHeight)->__toString();
            }else{
                $image = AW_Colorswatches_Helper_Image::resizeImage($this->_swatches[$attribute_id][$value]['image'], $imgWidth, $imgHeight);
                $ttImage = AW_Colorswatches_Helper_Image::resizeImage($this->_swatches[$attribute_id][$value]['image'], $tooltipWidth, $tooltipHeight);
            }
            $result[$value] = array(
                'title'        => $valueLabel,
                'img'          => $image,
                'tooltipImg'   => $ttImage,
                'products'     => $productList[$value],
                'not_saleable' => $notSaleableList[$value],
                'sort_order'   => $this->_swatches[$attribute_id][$value]['sort_order'],
            );
        }
        return $result;
    }

    /**
     * @param Mage_Catalog_Model_Product_Type_Configurable_Attribute $attribute
     * @param Mage_Catalog_Model_Product $product
     *
     * @return array
     */
    public function getOptionDataForAttributeData($attribute, Mage_Catalog_Model_Product $product)
    {
        $result = array();
        $childIds = $product->getChildrenIds();
        $childOptions = $product->getData($attribute['code']);
        for($i = 0; $i < count($childIds); $i++){
            $result[$childOptions[$i]]['products'][] = $childIds[$i];
        }
        return $result;
    }


    /**
     * @param Mage_Catalog_Model_Product    $product
     *
     * @return array
     */
    protected function _getAssociatedProductList(
        Mage_Catalog_Model_Product $product
    ) {
        if (!$product->isConfigurable()) {
            return array();
        }
        return $product->getTypeInstance(true)->getUsedProducts(null, $product);
    }
}