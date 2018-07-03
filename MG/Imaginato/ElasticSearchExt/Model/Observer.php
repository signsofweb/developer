<?php

class Imaginato_ElasticSearchExt_Model_Observer
{

    public function cleanProductAfterDelete(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('smile_elasticsearch');
        if ($helper->isActiveEngine()) {
            $product = $observer->getEvent()->getProduct();
            $engine = Mage::helper('catalogsearch')->getEngine();
            $engine->cleanIndex(null, $product->getId());
        }
    }
}

