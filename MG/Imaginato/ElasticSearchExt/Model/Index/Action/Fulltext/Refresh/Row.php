<?php

class Imaginato_ElasticSearchExt_Model_Index_Action_Fulltext_Refresh_Row
    extends Enterprise_CatalogSearch_Model_Index_Action_Fulltext_Refresh_Row
{

    public function execute()
    {
        if (Mage::helper('smile_elasticsearch')->isActiveEngine() == false) {
            parent::execute();
        } else {
            $engine = Mage::helper('catalogsearch')->getEngine();

            $this->_setProductIdsFromValue();
            $productIds = $this->_productIds;
            $this->_setProductIdsFromParents();
            $productIds = array_merge($productIds, $this->_productIds);

            $engine->cleanAndRebuildIndex($productIds);
        }

        return $this;
    }

}