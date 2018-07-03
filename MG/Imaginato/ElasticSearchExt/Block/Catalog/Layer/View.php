<?php
class Imaginato_ElasticSearchExt_Block_Catalog_Layer_View extends Smile_ElasticSearch_Block_Catalog_Layer_View
{

    /**
     * Returns current catalog layer.
     *
     * @return Mage::registry('current_layer')|Smile_ElasticSearch_Model_Catalog_Layer|Mage_Catalog_Model_Layer
     */
    public function getLayer()
    {
        /** @var $helper Smile_ElasticSearch_Helper_Data */
        $helper = Mage::helper('smile_elasticsearch');
        if ($helper->isActiveEngine()) {
            if(Mage::registry('current_layer')){
                return Mage::registry('current_layer');
            }else{
                return Mage::getSingleton('smile_elasticsearch/catalog_layer');
            }
        }

        return parent::getLayer();
    }
}
