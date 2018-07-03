<?php

class Imaginato_ElasticSearchExt_Model_Index_Action_Fulltext_Refresh_Changelog
    extends Enterprise_CatalogSearch_Model_Index_Action_Fulltext_Refresh_Changelog
{

    public function execute()
    {
        if (!$this->_metadata->isValid()) {
            throw new Enterprise_Index_Model_Action_Exception("Can't perform operation, incomplete metadata!");
        }

        if (Mage::helper('smile_elasticsearch')->isActiveEngine() == false) {
            parent::execute();
        } else {

            try {
                if (!empty($this->_changedIds)) {
                    $engine = Mage::helper('catalogsearch')->getEngine();
                    $this->_metadata->setInProgressStatus()->save();
                    // Index basic products
                    $this->_setProductIdsFromValue();
                    $productIds = $this->_productIds;
                    $this->_setProductIdsFromParents();
                    $productIds = array_merge($productIds, $this->_productIds);

                    $max_size = Mage::helper('imaginato_elasticsearchext/debug')->refresh_max_size();
                    if($max_size<count($productIds)){
                        Mage::log('count:'.count($productIds),1,'debug/refresh_elastic.log');
                        Mage::log(json_encode($productIds),1,'debug/refresh_elastic.log');
                        $slack_message = 'Refresh a lot of ElasticSearch data!!!\r\ncount: '.count($productIds).'\r\n';
                        Mage::helper('imaginato_elasticsearchext/debug')->addSlackLog('RefreshIndex-Robot',$slack_message);
                    }

                    $engine->cleanAndRebuildIndex($productIds);

                    // Clear search results
                    $this->_resetSearchResults();
                    $this->_updateMetadata();
                }
            } catch (Exception $e) {
                $this->_metadata->setInvalidStatus()->save();
                throw new Enterprise_Index_Model_Action_Exception($e->getMessage(), $e->getCode());
            }
        }

        return $this;
    }

}