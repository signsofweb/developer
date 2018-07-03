<?php

class Imaginato_ElasticSearchExt_Model_Resource_Engine_Elasticsearch extends Smile_ElasticSearch_Model_Resource_Engine_Elasticsearch
{
    public function cleanAndRebuildIndex($productIds){
        $rebuildIds = array();
        $productCollection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('status')
            ->addIdFilter($productIds);
        foreach($productCollection->getItems() as $value){
            if($value->getData('status') == Mage_Catalog_Model_Product_Status::STATUS_ENABLED){
                $rebuildIds[] = $value->getData('entity_id');
            }
        }

        if($cleanIds = array_diff($productIds,$rebuildIds)){
            $this->cleanIndex(null, $cleanIds);
        }
        if($rebuildIds){
            $this->getCurrentIndex()
                ->getMapping('product')
                ->rebuildIndex(null, $rebuildIds);
        }
    }
}
