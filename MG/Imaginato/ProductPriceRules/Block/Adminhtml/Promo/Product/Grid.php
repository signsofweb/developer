<?php
/**
 * Class Imaginato_ProductPriceRules_Block_Adminhtml_Promo_Product_Grid
 *
 */
class Imaginato_ProductPriceRules_Block_Adminhtml_Promo_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * Imaginato_ProductPriceRules_Block_Adminhtml_Promo_Product_Grid constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('productruleGrid');
        $this->setDefaultSort('rule_id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('skusrule/rule_product_price')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }
    
    /**
     * @return Imaginato_ProductPriceRules_Block_Adminhtml_Promo_Product_Grid|Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('rule_id',
            array(
                'header' => $this->_getHelper()->__('ID'),
                'width'  => '1',
                'type'   => 'number',
                'sortable'=>true,
                'index'  => 'rule_id',
            ));
        $this->addColumn('name', array(
            'header'    => $this->_getHelper()->__('Rule Name'),
            'align'     =>'left',
            'index'     => 'name',
        ));
        $this->addColumn('percent', array(
            'header'    => $this->_getHelper()->__('MD%'),
            'align'     =>'left',
            'type'      => 'number',
            'index'     => 'percent',
        ));
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('rule_website', array(
                'header'    => $this->_getHelper()->__('Website'),
                'align'     =>'left',
                'index'     => 'website_id',
                'type'      => 'options',
                'sortable'  => false,
                'options'   => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(),
                'width'     => 200,
            ));
        }
        $this->addColumn('created_at', array( 
            'header' => $this->_getHelper()->__('Created At'), 
            'align' =>    'left', 
            'type' => 'datetime', 
            'sortable'=>true,
            'index' => 'created_at'
        ));
        $this->addColumn('updated_at', array( 
            'header' => $this->_getHelper()->__('Updated At'), 
            'align' =>    'left', 
            'type' => 'datetime', 
            'sortable'=>true,
            'index' => 'updated_at'
        ));

        return $this;
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('rule_id');
        $this->setMassactionIdFilter('rule_id');
        $this->getMassactionBlock()->setFormFieldName('rule_id');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'   => $this->_getHelper()->__('Delete'),
            'url'     => $this->getUrl('*/*/massDelete', array('' => '')),
            'confirm' => $this->_getHelper()->__('Are you sure?')
        ));
        return parent::_prepareMassaction();
    }
    
    /**
     * @return Imaginato_ProductPriceRules_Helper_Data|Mage_Core_Helper_Abstract
     */
    public function _getHelper()
    {
        return Mage::helper('skusrule');
    }

    /**
     * Used for Ajax Based Grid
     * 
     * URL which is called in the Ajax Request, to the get
     *  the content of the grid. _current Uses the current module, controller, 
     * action and parameters.
     *
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    /**
     * @param Imaginato_Contacts_Model_Block $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('rule_id' => $row->getId()));
    }
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }
    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }
}
