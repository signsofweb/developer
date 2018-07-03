<?php
class Imaginato_ProductPriceRules_Model_Resource_Rule_Product_Price extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize connection and define main table
     *
     */
    protected function _construct()
    {
        $this->_init('skusrule/rule_product_price', 'rule_id');
    }

    public function checkValidateSku($sku, $websiteId)
    {
        /** @var $write Varien_Db_Adapter_Interface */
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
        ->from(array('rb' => $this->getTable('skusrule/rule_product_price')));
        $select->join(
            array('rs'=>$this->getTable('skusrule/rule_product_sku')),
            'rs.rule_id = rb.rule_id',
            array()
        );
        $select -> where('website_id = ?', $websiteId)
                -> where('rs.sku = ?', $sku);
        return $adapter->fetchAll($select);
    }

    public function getRuleBySkuAndWebsiteId($sku, $websiteId)
    {
        /** @var $write Varien_Db_Adapter_Interface */
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
        ->from(array('rb' => $this->getTable('skusrule/rule_product_price')), array('rule_id','percent', 'skus'));
        $select->join(
            array('rs'=>$this->getTable('skusrule/rule_product_sku')),
            'rs.rule_id = rb.rule_id',
            array()
        );
        $select -> where('website_id = ?', $websiteId)
                -> where('rs.sku = ?', $sku);
        return $adapter->fetchAll($select);
    }

    public function getRuleByPercentAndWebsiteId($percent, $websiteId)
    {
        /** @var $write Varien_Db_Adapter_Interface */
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
        -> from($this->getTable('skusrule/rule_product_price'), array('rule_id','percent', 'skus'))
        -> where('website_id = ?', $websiteId)
        -> where('percent = ?', $percent);
        return $adapter->fetchAll($select);
    }

    public function insertSkuForRuleByWebsiteIdAndPercent($sku, $websiteId, $percent)
    {
        /** @var $write Varien_Db_Adapter_Interface */
        $adapter    = $this->_getWriteAdapter();
        $result = $this->getRuleByPercentAndWebsiteId($percent, $websiteId);
        if(count($result)){
            $str = str_replace("\n",",",$result[0]['skus']);
            $skus = explode(',', $str);
            $skus[] = $sku;
            $this->insertSkuByRuleId((int)$result[0]['rule_id'], $skus);
        }
        return $this;
    }

    public function insertSkuByRuleId($ruleId, $listSku)
    {
        $result = array();
        $rows = array();
        $_listArray = array();
        /** @var $write Varien_Db_Adapter_Interface */
        $adapter = $this->_getWriteAdapter();
        $select = $adapter->select()
        ->from($this->getTable('skusrule/rule_product_sku'), array('sku'))
        ->where('rule_id = ?', $ruleId);
        $result = $adapter->fetchAll($select);
        if(count($result)){
            foreach($result as $_sku){
                $_listArray[] = $_sku['sku'];
            }
        }
        $skuDelete = array_diff($_listArray, $listSku);
        if(count($skuDelete)){
            foreach($skuDelete as $_sku){
                $conds = array();
                $conds[] = $adapter->quoteInto('rule_id = ?', $ruleId);
                $conds[] = $adapter->quoteInto('sku = ?', $_sku);
                $adapter->delete($this->getTable('skusrule/rule_product_sku'), $conds);
            }
        }
        $skuInsert = array_diff($listSku, $_listArray);
        if(count($skuInsert)){
            foreach($skuInsert as $_sku){
                $rows[] = array(
                    'rule_id' => $ruleId,
                    'sku' => $_sku,
                );
            }
            $adapter->insertMultiple($this->getTable('skusrule/rule_product_sku'), $rows);
        }
        $where = ['rule_id = ?' => $ruleId];
        $adapter->update($this->getTable('skusrule/rule_product_price'), ['skus' => implode(',',$listSku)], $where);
        return $this;
    }

    public function removeSkuByRuleId($ruleId)
    {
        $conds = array();
        /** @var $write Varien_Db_Adapter_Interface */
        $adapter = $this->_getWriteAdapter();
        $conds[] = $adapter->quoteInto('rule_id = ?', $ruleId);
        $adapter->delete($this->getTable('skusrule/rule_product_sku'), $conds);
    }
}
