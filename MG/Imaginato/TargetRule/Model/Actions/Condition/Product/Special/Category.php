<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Enterprise
 * @package     Enterprise_TargetRule
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * TargetRule Action Product Price (percentage) Condition Model
 *
 * @category   Enterprise
 * @package    Enterprise_TargetRule
 */
class Imaginato_TargetRule_Model_Actions_Condition_Product_Special_Category
    extends Enterprise_TargetRule_Model_Actions_Condition_Product_Attributes
{
    /**
     * Set rule type
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setType('enterprise_targetrule/actions_condition_product_special_category');
        $this->setValue(3);
        $this->setValueType('constant');
    }

    /**
     * Returns options for value type select box
     *
     * @return array
     */
    public function getValueTypeOptions()
    {
        $options = array(
            array(
                'value' => 'constant',
                'label' => Mage::helper('enterprise_targetrule')->__('the Same as Matched Product Categories')
            )
        );
        return $options;
    }

    public function getValueElementType()
    {
        return 'select';
    }


    /**
     * Set operator options
     *
     * @return Enterprise_TargetRule_Model_Actions_Condition_Product_Special_Category
     */
    public function loadOperatorOptions()
    {
        parent::loadOperatorOptions();
        $this->setOperatorOption($this->_getOperatorOptionArray());
        return $this;
    }

    /**
     * Retrieve operator select options array
     *
     * @return array
     */
    protected function _getOperatorOptionArray()
    {
        return array(
            '==' => Mage::helper('enterprise_targetrule')->__('is'),
            '!=' => Mage::helper('enterprise_targetrule')->__('is not')
        );
    }

    public function getValueSelectOptions(){

        $collection = Mage::getModel('catalog/category')->getCollection();

        $select = $collection->getSelect();
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->columns('level', 'e');
        $select->distinct(true);
        $level = array();
        foreach($collection->getData() as $item){
            $level[$item['level']] = $item['level'];
        }
        return $level;
    }

    /**
     * Retrieve rule as HTML formated string
     *
     * @return string
     */
    public function asHtml()
    {
        return $this->getTypeElementHtml()
            . Mage::helper('enterprise_targetrule')->__('Product Category %s of Matched Product(s) Level', $this->getOperatorElementHtml() . $this->getValueTypeElementHtml() . $this->getValueElementHtml())
            . $this->getRemoveLinkHtml();
    }

    /**
     * Retrieve SELECT WHERE condition for product collection
     *
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @param Enterprise_TargetRule_Model_Index $object
     * @param array $bind
     * @return Zend_Db_Expr
     */
    public function getConditionForCollection($collection, $object, &$bind)
    {
        $resource       = $object->getResource();
        $select = $object->select()
            ->from(array('cp'=>$resource->getTable('catalog/category_product')), 'COUNT(*)')
            ->where('cp.product_id=e.entity_id');

        $operator = $this->getOperator();
        $operator = ('!=' == $operator) ? '!()' : '()';
        $where = $resource->getOperatorBindCondition('cp.category_id', 'category_ids', $operator, $bind,
            array('bindArrayOfIds'));
        $select->where($where);

        $suffix = sprintf('store_%d', $object->getStoreId());
        $category = $resource->getTable(array('catalog/category_flat', $suffix));
        $select->joinInner(
            array('ce' => $category),
            'ce.entity_id = cp.category_id',
            array()
        );
        $select->where('ce.level=?',$this->getValue());
        if($operator=='()'){
            $category_ids = Mage::getStoreConfig('catalog/enterprise_targetrule/blacklist_categoryids');
            $select->where('cp.category_id not in(?)',explode(',',$category_ids));
        }

        return new Zend_Db_Expr(sprintf('(%s) > 0', $select->assemble()));
    }
}
