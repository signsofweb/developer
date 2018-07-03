<?php

class Imaginato_Catalog_Model_Observer
{
    protected $_stock_store;
    protected $_process_product;
    protected $_process_product_status;

    /**
     * Cron job method for category flats to reindex
     *
     * @param Mage_Cron_Model_Schedule $schedule
     */
    public function reindexCategoryFlats(Mage_Cron_Model_Schedule $schedule)
    {
        $indexProcess = Mage::getSingleton('index/indexer')->getProcessByCode('catalog_category_flat');
        if ($indexProcess) {
            Mage::dispatchEvent($indexProcess->getIndexerCode() . '_shell_reindex_after');
            $indexProcess->reindexAll();
        }
    }

    public function saveStockAfter($observer)
    {
        $this->initProcessProduct();
        $inventory = $observer->getEvent()->getDataObject();
        $product_id = $inventory->getData('product_id');
        $stock_id = $inventory->getData('stock_id');
        $is_in_stock = $inventory->getData('is_in_stock');
        $this->processProductStatus($product_id,$stock_id,$is_in_stock);
        $this->updateProductStatus();
    }

    public function importStockAfter($observer)
    {
        $this->initProcessProduct();
        $stock_data = $observer->getEvent()->getStock();
        foreach($stock_data as $stock){
            $this->processProductStatus($stock['product_id'],$stock['stock_id'],$stock['is_in_stock']);
        }
        $this->updateProductStatus();
    }

    public function processProductStatus($productId,$stock_id,$is_in_stock=null){
        if($this->_process_product->getData($productId.'_'.$stock_id)){
            return;
        }else{
            $this->_process_product->setData($productId.'_'.$stock_id,1);
        }
        if ($productId instanceof Mage_Catalog_Model_Product) {
            $product = $productId;
        }else{
            $product = Mage::getModel('catalog/product')->load($productId);
        }
        if(!$product->getId()){
            return;
        }
        if($product->getTypeId()==Mage_Catalog_Model_Product_Type::TYPE_SIMPLE){
            if(is_null($is_in_stock)){
                $stock_item = Mage::getModel('cataloginventory/stock_item')->setStockId($stock_id)->loadByProduct($productId);
                $is_in_stock = $stock_item->getIsInStock();
            }
            $status = $is_in_stock?Mage_Catalog_Model_Product_Status::STATUS_ENABLED:Mage_Catalog_Model_Product_Status::STATUS_DISABLED;
            foreach($this->getStoreIdByStockId($stock_id) as $storeId){
                $this->addProcessProduct($productId,$storeId,$status);
            }
            $type_configurable = Mage::getResourceSingleton('catalog/product_type_configurable');
            $parentIds = $type_configurable->getParentIdsByChild($productId);
            if(!empty($parentIds)){
                foreach($parentIds as $parentId){
                    $this->processProductStatus($parentId,$stock_id);
                }
            }
        }elseif($product->getTypeId()==Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE){
            $parent_status = $this->getParentStatus($productId,$stock_id);
            foreach($this->getStoreIdByStockId($stock_id) as $storeId){
                $this->addProcessProduct($productId,$storeId,$parent_status);
            }
        }
    }

    protected function getParentStatus($parentId,$stock_id){
        if ($parentId instanceof Mage_Catalog_Model_Product) {
            $parentProduct = $parentId;
        }else{
            $parentProduct = Mage::getModel('catalog/product')->load($parentId);
        }
        $childProductIds = $parentProduct->getTypeInstance(true)->getUsedProductIds($parentProduct);
        $parent_status = Mage_Catalog_Model_Product_Status::STATUS_DISABLED;
        foreach($childProductIds as $childProductId){
            $childStockItem = Mage::getModel('cataloginventory/stock_item')->setStockId($stock_id)->loadByProduct($childProductId);
            if($childStockItem->getIsInStock()){
                $parent_status = Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
                break;
            }
        }
        return $parent_status;
    }

    public function initProcessProduct(){
        $this->_stock_store = array();
        $this->_process_product = new Varien_Object();
        $this->_process_product_status = new Varien_Object();
        foreach(Mage::app()->getWebsites() as $website){
            $storeId = $website->getDefaultStore()->getId();
            $this->_process_product_status->setData(
                $storeId,
                array(
                    Mage_Catalog_Model_Product_Status::STATUS_ENABLED=>array(),
                    Mage_Catalog_Model_Product_Status::STATUS_DISABLED=>array()
                )
            );
        }
    }

    protected function addProcessProduct($productId,$storeId,$status){
        $store_data = $this->_process_product_status->getData($storeId);
        if(isset($store_data[$status])){
            array_push($store_data[$status],$productId);
            $this->_process_product_status->setData($storeId,$store_data);
        }
    }

    public function updateProductStatus(){
        foreach($this->_process_product_status->getData() as $storeId=>$store_data){
            foreach($store_data as $status=>$productId){
                if(empty($productId)){
                    continue;
                }
                Mage::getSingleton('catalog/product_action')->updateAttributes($productId, array('status' => $status), $storeId);
            }
        }
    }

    protected function getStoreIdByStockId($stock_id){
        if(!isset($this->_stock_store[$stock_id])){
            $helper = Mage::helper('warehouse');
            if(!$helper->isStockIdExists($stock_id)){
                return array();
            }
            foreach($helper->getWarehouses() as $warehouse){
                $stockId = $warehouse->getStockId();
                $storeIds = $warehouse->getStoreIds();
                $stock_store = array();
                foreach($storeIds as $storeId){
                    $store = Mage::app()->getStore($storeId);
                    array_push($stock_store,$store->getWebsite()->getDefaultStore()->getId());
                }
                $this->_stock_store[$stockId] = array_unique($stock_store);
            }
        }
        return $this->_stock_store[$stock_id];
    }

    public function doUpdateForProductStatus($product){
        $simples = $product->getTypeInstance(true)->getUsedProducts(null,$product);
        $changeStatus = $product->isSalable()?(Mage_Catalog_Model_Product_Status::STATUS_ENABLED):(Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
        foreach($simples as $simple){
            $productStatus[$simple->getId()] = $changeStatus;
        }
        $currentStore = Mage::app()->getRequest()->getParam('store');
        if($currentStore > 0 ){
            $productStatus = $this->getProductStatusByStore($currentStore,array_keys($productStatus));
            $productStatus = $this->getUpdateProductStatus($productStatus,$changeStatus);
        }
        if(!empty($productStatus)){
            $this->updateProductStatus($productStatus,$currentStore);
            if($product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED){
                $skus = Mage::getSingleton('imaginato_importexport/import_observer')->checkSimpleImage(array_keys($productStatus));
                if(!empty($skus)){
                    Mage::getSingleton('core/session')->addNotice("please check product ". implode(' , ',$skus) . ' no images');
                }
            }
        }
    }
}
