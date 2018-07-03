<?php
/**
 * Class Imaginato_Contacts_Block_Adminhtml_Contact_Grid
 *
 */
class Imaginato_Contacts_Block_Adminhtml_Contact_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * Imaginato_Contacts_Block_Adminhtml_Contact_Grid constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('imaginatoContactGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

	/**
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
		$collection = Mage::getModel('imaginato_contacts/contacts')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }
	
    /**
     * @return Imaginato_Contacts_Block_Adminhtml_Contact_Grid|Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('entity_id',
            array(
                'header' => $this->_getHelper()->__('ID'),
                'width'  => '1',
                'type'   => 'number',
                'sortable'=>true,
                'index'  => 'entity_id',
            ));
		$this->addColumn('name', array(
            'header'=>$this->_getHelper()->__('Name'),
            'sortable'=>true,
            'index'=>'name',
        ));
		/**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'        => Mage::helper('cms')->__('Store View'),
                'index'         => 'store_id',
                'type'          => 'store',
                'store_all'     => true,
                'store_view'    => true,
                'sortable'      => false,
                'filter_condition_callback'
                                => array($this, '_filterStoreCondition'),
            ));
        }
		$this->addColumn('subject', array(
            'header'=>$this->_getHelper()->__('Subject'),
            'sortable'=>false,
			'filter'    => false,
			'index'         => 'subject',
            'renderer' => 'imaginato_contacts/adminhtml_renderer_subject',
        ));
		$this->addColumn('email', array(
            'header'=>$this->_getHelper()->__('Email Address'),
            'sortable'=>true,
            'index'=>'email',
        ));
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
        $this->addExportType('*/*/exportCsv', $this->_getHelper()->__('CSV'));

        return $this;
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->setMassactionIdFilter('entity_id');
        $this->getMassactionBlock()->setFormFieldName('entity_id');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'   => $this->_getHelper()->__('Delete'),
            'url'     => $this->getUrl('*/*/massDelete', array('' => '')),
            'confirm' => $this->_getHelper()->__('Are you sure?')
        ));
        return parent::_prepareMassaction();
    }
	
	/**
     * @return Imaginato_Contacts_Helper_Data|Mage_Core_Helper_Abstract
     */
    public function _getHelper()
    {
        return Mage::helper('imaginato_contacts');
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
        return $this->getUrl('*/*/edit', array('entity_id' => $row->getId()));
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
