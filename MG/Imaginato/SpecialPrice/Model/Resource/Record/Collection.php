<?php

class Imaginato_SpecialPrice_Model_Resource_Record_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('imaginato_specialprice/record');
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();
        if ($this->getFlag('add_websites_to_result') && $this->_items) {
            $select = $this->getConnection()->select()
                ->from($this->getTable('imaginato_specialprice/record_website'), array(
                    'record_id',
                    'website_id'
                ))
                ->where('record_id IN (?)', array_keys($this->_items));
            $websites = $this->getConnection()->fetchAll($select);
            $websiteIds = array();
            foreach ($websites as $website) {
                if(!isset($websiteIds[$website['record_id']])){
                    $websiteIds[$website['record_id']] = array();
                }
                $websiteIds[$website['record_id']][] = $website['website_id'];
            }
            foreach ($this->_items as $item) {
                if (count($websiteIds[$item->getId()])) {
                    $item->setWebsiteIds($websiteIds[$item->getId()]);
                }
            }
        }
        if ($this->getFlag('add_products_to_result') && $this->_items) {
            $select = $this->getConnection()->select()
                ->from(
                    array('main'=>$this->getTable('imaginato_specialprice/record_product')),
                    array('record_id','product_id')
                )
                ->joinLeft(
                    array('p' => $this->getTable('catalog/product')),
                    'p.entity_id = main.product_id',
                    array('sku')
                )
                ->where('record_id IN (?)', array_keys($this->_items));
            $products = $this->getConnection()->fetchAll($select);
            $productIds = array();
            foreach ($products as $product) {
                $productIds[$product['record_id']][$product['product_id']] = $product['sku'];
            }
            foreach ($this->_items as $item) {
                if (count($productIds[$item->getId()])) {
                    $item->setProductIds($productIds[$item->getId()]);
                }
            }
        }
        return $this;
    }

    public function addWebsitesToResult($flag = null)
    {
        $flag = ($flag === null) ? true : $flag;
        $this->setFlag('add_websites_to_result', $flag);
        return $this;
    }

    public function addProductsToResult($flag = null)
    {
        $flag = ($flag === null) ? true : $flag;
        $this->setFlag('add_products_to_result', $flag);
        return $this;
    }

    /**
     * Limit gift record collection by specific website
     *
     * @param  int|array|Mage_Core_Model_Website $websiteId
     * @return Imaginato_SpecialPrice_Model_Resource_Record_Collection
     */
    public function applyWebsiteFilter($websiteId)
    {
        if (!$this->getFlag('is_website_table_joined')) {
            $this->setFlag('is_website_table_joined', true);
            $this->getSelect()->joinInner(
                array('website' => $this->getTable('imaginato_specialprice/record_website')),
                'main_table.record_id = website.record_id',
                array()
            );
        }

        if ($websiteId instanceof Mage_Core_Model_Website) {
            $websiteId = $websiteId->getId();
        }
        $this->getSelect()->where('website.website_id IN (?)', $websiteId);

        return $this;
    }

    /**
     * Add specified field to collection filter
     * Redeclared in order to be able to limit collection by specific website
     * @see self::applyWebsiteFilter()
     *
     * @param  string $field
     * @param  mixed $condition
     * @return Imaginato_SpecialPrice_Model_Resource_Record_Collection
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'website_ids') {
            return $this->applyWebsiteFilter($condition);
        }
        return parent::addFieldToFilter($field, $condition);
    }

}
