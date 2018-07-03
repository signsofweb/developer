<?php
class Imaginato_SpecialPrice_Model_Resource_Record extends Mage_Core_Model_Resource_Db_Abstract
{
    protected $_defaultPercent = 100;

    protected $_recordProductTable;
    protected $_recordWebsiteTable;

    /**
     * Initialize connection and define main table
     *
     */
    protected function _construct()
    {
        $this->_init('imaginato_specialprice/record', 'record_id');
        $this->_recordProductTable = $this->getTable('imaginato_specialprice/record_product');
        $this->_recordWebsiteTable = $this->getTable('imaginato_specialprice/record_website');
    }

    protected function _beforeSave(Varien_Object $record)
    {
        parent::_beforeSave($record);
        if($record->getPostedProducts() && $record->getPostedWebsites()){
            $this->_checkRepeatWebsite($record);
            $this->_checkRepeatProduct($record);
        }

        return $this;
    }

    protected function _checkRepeatWebsite($record){
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from(array('main'=>$this->getMainTable()), array())
            ->joinLeft(array('website' => $this->_recordWebsiteTable),
                'main.record_id=website.record_id',
                array('website_id')
            )
            ->where('main.discount_rate = :discount_rate');
        if ($record->getPostedWebsites()) {
            $select->where('website.website_id IN(?)',$record->getPostedWebsites());
        }
        if ($record->getId()) {
            $select->where('main.record_id != ?',(int)$record->getId());
        }

        $bind    = array('discount_rate' => $record->getDiscountRate());
        $result = $adapter->fetchCol($select, $bind);
        if (!empty($result)) {
            $website_name = array();
            foreach($result as $website_id){
                $website_name[] = Mage::app()->getWebsite($website_id)->getName();
            }

            throw new Mage_Core_Exception(
                Mage::helper('imaginato_specialprice')->__('Website MD%s is existing: %s',$record->getDiscountRate(),implode(',',$website_name))
            );
        }
    }

    protected function _checkRepeatProduct($record){
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from(array('main'=>$this->getMainTable()), array())
            ->joinLeft(array('website' => $this->_recordWebsiteTable),
                'main.record_id=website.record_id',
                array('website_id')
            )
            ->joinLeft(array('product' => $this->_recordProductTable),
                'main.record_id=product.record_id',
                array('product_id')
            );
        if ($record->getPostedProducts()) {
            $select->where('product.product_id IN(?)',$record->getPostedProducts());
        }
        if ($record->getPostedWebsites()) {
            $select->where('website.website_id IN(?)',$record->getPostedWebsites());
        }
        if ($record->getId()) {
            $select->where('main.record_id != ?',(int)$record->getId());
        }

        $result = $adapter->fetchAll($select);
        if (!empty($result)) {
            $errormsgs = array();
            foreach($result as $row){
                $website_name = Mage::app()->getWebsite($row['website_id'])->getName();
                $product_sku = $record->getData('skuCollection')->getItemById($row['product_id'])->getSku();
                $errormsgs[] = $website_name.':'.$product_sku;
            }

            throw new Mage_Core_Exception(
                Mage::helper('imaginato_specialprice')->__('Product MD in Website is existing: </br>%s',implode(',</br>',$errormsgs))
            );
        }
    }

    protected function _afterSave(Varien_Object $record)
    {
        $this->_saveRecordProducts($record);
        $this->_saveRecordWebsites($record);
        $this->_saveProductSpecialPrice($record);
        return parent::_afterSave($record);
    }

    /**
     * Save record products relation
     *
     * @param Imaginato_SpecialPrice_Model_Record $record
     * @return Imaginato_SpecialPrice_Model_Resource_Record
     */
    protected function _saveRecordProducts($record)
    {
        $record->setIsChangedProductList(false);
        $id = $record->getId();
        /**
         * new record-product relationships
         */
        $products = $record->getPostedProducts();

        /**
         * old record-product relationships
         */
        $oldProducts = $record->getProductIds();

        $insert = array_diff($products, $oldProducts);
        $delete = array_diff($oldProducts, $products);

        $update = array_intersect($products, $oldProducts);

        $adapter = $this->_getWriteAdapter();

        /**
         * Delete products from record
         */
        if (!empty($delete)) {
            $cond = array(
                'product_id IN(?)' => $delete,
                'record_id=?' => $id
            );
            $adapter->delete($this->_recordProductTable, $cond);
        }

        /**
         * Add products to record
         */
        if (!empty($insert)) {
            $data = array();
            foreach ($insert as $productId) {
                $data[] = array(
                    'record_id' => (int)$id,
                    'product_id'  => (int)$productId
                );
            }
            $adapter->insertMultiple($this->_recordProductTable, $data);
        }


        if (!empty($insert) || !empty($update) || !empty($delete)) {
            $record->setIsChangedProductList(true);

            /**
             * Setting affected products to record for third party engine index refresh
             */
            $productIds = $insert + $delete + $update;
            $record->setAffectedProductList($productIds);
        }
        return $this;
    }

    /**
     * Save record website relation
     *
     * @param Imaginato_SpecialPrice_Model_Record $record
     * @return Imaginato_SpecialPrice_Model_Resource_Record
     */
    protected function _saveRecordWebsites($record)
    {
        $record->setIsChangedWebsiteList(false);
        $id = $record->getId();

        /**
         * new record-website relationships
         */
        $website = $record->getPostedWebsites();

        /**
         * old record-website relationships
         */
        $oldWebSites = $record->getWebSiteIds();

        $insert = array_diff($website, $oldWebSites);
        $delete = array_diff($oldWebSites, $website);

        $update = array_intersect($website, $oldWebSites);

        $adapter = $this->_getWriteAdapter();

        /**
         * Delete website from record
         */
        if (!empty($delete)) {
            $cond = array(
                'website_id IN(?)' => $delete,
                'record_id=?' => $id
            );
            $adapter->delete($this->_recordWebsiteTable, $cond);
        }

        /**
         * Add website to record
         */
        if (!empty($insert)) {
            $data = array();
            foreach ($insert as $websiteId) {
                $data[] = array(
                    'record_id' => (int)$id,
                    'website_id'  => (int)$websiteId
                );
            }
            $adapter->insertMultiple($this->_recordWebsiteTable, $data);
        }


        if (!empty($insert) || !empty($update) || !empty($delete)) {
            $record->setIsChangedWebsiteList(true);

            /**
             * Setting affected products to record for third party engine index refresh
             */
            $websiteIds = $insert + $delete + $update;
            $record->setAffectedWebsiteList($websiteIds);
        }
        return $this;
    }

    protected function _saveProductSpecialPrice($record){

        $websiteIds = $record->getAffectedWebsiteList();
        $productIds = $record->getAffectedProductList();
        $newWebsites = $record->getPostedWebsites();
        $newProducts = $record->getPostedProducts();
        foreach($websiteIds as $_weisiteId){
            $storeId = Mage::app()->getWebsite($_weisiteId)->getDefaultStore()->getId();
            foreach($productIds as $_productId) {
                $_product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($_productId);
                if($_product->getId()){
                    $attributesData = array();
                    if(!in_array($_productId,$newProducts) || !in_array($_weisiteId,$newWebsites)){
                        $attributesData['special_price'] = null;
                        $attributesData['special_from_date'] = null;
                        $attributesData['special_to_date'] = null;
                    }else{
                        $attributesData['special_price'] = $_product->getPrice() * (float)(($this->_defaultPercent - $record->getDiscountRate())/$this->_defaultPercent);
                        $attributesData['special_from_date'] = $record->getFromDate();
                        $attributesData['special_to_date'] = $record->getToDate();
                    }
                    Mage::getSingleton('catalog/product_action')->updateAttributes(array($_productId), $attributesData, $storeId);
                }
            }
        }
    }

    public function getProductIds($record)
    {
        $select = $this->_getWriteAdapter()->select()
            ->from($this->_recordProductTable, array('product_id'))
            ->where('record_id = ?', (int)$record->getId());

        return $this->_getWriteAdapter()->fetchCol($select);
    }

    public function getWebsiteIds($record)
    {
        $select = $this->_getWriteAdapter()->select()
            ->from($this->_recordWebsiteTable, array('website_id'))
            ->where('record_id = ?', (int)$record->getId());

        return $this->_getWriteAdapter()->fetchCol($select);
    }
}
