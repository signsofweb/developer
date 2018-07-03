<?php

/**
 * Product in category grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Imaginato_Catalog_Block_Adminhtml_Category_Tab_Product extends Mage_Adminhtml_Block_Catalog_Category_Tab_Product
{

    protected function _prepareColumns()
    {
        $coll_attribute = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'evisu_collection');
        $optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
            ->setAttributeFilter($coll_attribute->getId())
            ->setPositionOrder('asc', true);
        $coll = array();
        foreach ($optionCollection as $key=>$item) {
            $coll[$key] = $item->getValue();
        }
        $this->addColumnAfter('evisu_collection',
            array(
                'header'  => Mage::helper('catalog')->__('Collection'),
                'width'   => '60px',
                'index'   => 'evisu_collection',
                'type'    => 'options',
                'options' => $coll,
            ), 'sku');

        return parent::_prepareColumns();
    }
}