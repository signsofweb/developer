<?php

class Imaginato_ImportExport_Model_Import_Entity_Product_Type_Configurable
    extends Mage_ImportExport_Model_Import_Entity_Product_Type_Configurable
{

    /**
     * Array of SKU to array of super attribute values for all products.
     *
     * @return Mage_ImportExport_Model_Import_Entity_Product_Type_Configurable
     */
    protected function _loadSkuSuperAttributeValues()
    {
        if ($this->_superAttributes) {
            $attrSetIdToName   = $this->_entityModel->getAttrSetIdToName();
            $allowProductTypes = array();

            foreach (Mage::getConfig()
                    ->getNode('global/catalog/product/type/configurable/allow_product_types')->children() as $type) {
                $allowProductTypes[] = $type->getName();
            }
            $product_collection = Mage::getResourceModel('catalog/product_collection')
                ->addFieldToFilter('type_id', $allowProductTypes);
            $product_collection->addAttributeToSelect(array_keys($this->_superAttributes),'left');
            foreach ($product_collection->getData() as $product) {
                $attrSetName = $attrSetIdToName[$product['attribute_set_id']];

                $data = array_intersect_key(
                    $product,
                    $this->_superAttributes
                );
                foreach ($data as $attrCode => $value) {
                    if(is_null($value)){
                        continue;
                    }
                    $attrId = $this->_superAttributes[$attrCode]['id'];
                    $this->_skuSuperAttributeValues[$attrSetName][$product['entity_id']][$attrId] = $value;
                }
            }
        }
        return $this;
    }
}
