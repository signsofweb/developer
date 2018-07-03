<?php

class Imaginato_GiftCardAccount_Block_Adminhtml_Giftcardaccount_Export_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Set defaults
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('giftcardaccountGrid');
        $this->setDefaultSort('giftcardaccount_id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('giftcardaccount_filter');
    }

    /**
     * Instantiate and prepare collection
     *
     * @return Enterprise_GiftCardAccount_Block_Adminhtml_Giftcardaccount_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('enterprise_giftcardaccount/giftcardaccount')->getCollection();
        $giftCartAccount_data = array();
        foreach($collection as $key=>$item){
            $giftCartAccount_data[$item->getData('giftcardaccount_id')][0] = $item->getData();
            $collection->removeItemByKey($key);
        }

        $creat_collection = Mage::getModel('enterprise_giftcardaccount/history')->getCollection();
        $creat_collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('giftcardaccount_id','generated_order_number' => 'additional_info', 'original_amount' => 'balance_delta'));
        $creat_collection->addFieldToFilter('action', 0);
        $creat_collection->setOrder('giftcardaccount_id', 'asc');
        $creat_collection->setOrder('updated_at', 'asc');
        $id = '';
        $i = 0;
        foreach($creat_collection as $item){
            $giftcardaccount_id = $item->getData('giftcardaccount_id');
            if($giftcardaccount_id == $id){
                $i++;
            }else{
                $id = $giftcardaccount_id;
                $i = 0;
            }
            $giftCartAccount_data[$giftcardaccount_id][$i]['generated_order_number'] = $item->getData('generated_order_number');
            $giftCartAccount_data[$giftcardaccount_id][$i]['original_amount'] = $item->getData('original_amount');
        }

        $use_collection = Mage::getModel('enterprise_giftcardaccount/history')->getCollection();
        $use_collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('giftcardaccount_id','used_date' => 'updated_at', 'used_order_number' => 'additional_info', 'used_amount' => 'ABS(main_table.balance_delta)'));
        $use_collection->addFieldToFilter('action', 1);
        $use_collection->setOrder('giftcardaccount_id', 'asc');
        $use_collection->setOrder('updated_at', 'asc');
        $id = '';
        $i = 0;
        foreach($use_collection as $item){
            $giftcardaccount_id = $item->getData('giftcardaccount_id');
            if($giftcardaccount_id == $id){
                $i++;
            }else{
                $id = $giftcardaccount_id;
                $i = 0;
            }
            $giftCartAccount_data[$giftcardaccount_id][$i]['used_date'] = $item->getData('used_date');
            $giftCartAccount_data[$giftcardaccount_id][$i]['used_order_number'] = $item->getData('used_order_number');
            $giftCartAccount_data[$giftcardaccount_id][$i]['used_amount'] = $item->getData('used_amount');
        }

        $send_collection = Mage::getModel('enterprise_giftcardaccount/history')->getCollection();
        $send_collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('giftcardaccount_id','additional_info'));
        $send_collection->addFieldToFilter('action', 2);
        $send_collection->setOrder('giftcardaccount_id', 'asc');
        $send_collection->setOrder('updated_at', 'asc');
        $id = '';
        $i = 0;
        foreach($send_collection as $item){
            $giftcardaccount_id = $item->getData('giftcardaccount_id');
            if($giftcardaccount_id == $id){
                $i++;
            }else{
                $id = $giftcardaccount_id;
                $i = 0;
            }
            $additional_info = $item->getData('additional_info');
            preg_match ('/^Recipient: (.*?) <(.*?)>.(.*)$/', $additional_info, $param);
            $giftCartAccount_data[$giftcardaccount_id][$i]['receptor_name'] = $param[1];
            $giftCartAccount_data[$giftcardaccount_id][$i]['receptor_email']= $param[2];
        }

        $total_collection = Mage::getModel('enterprise_giftcardaccount/giftcardaccount')->getCollection();
        $total_collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('main_table.giftcardaccount_id'))
            ->joinLeft(
                array('history' => $collection->getTable('enterprise_giftcardaccount/history')),
                'history.giftcardaccount_id = main_table.giftcardaccount_id and history.action = 1',
                array('total'=>'ABS(SUM(history.balance_delta))')
            )
            ->group('main_table.giftcardaccount_id');
        foreach($total_collection->getData() as $val){
            $giftCartAccount_data[$val['giftcardaccount_id']][0]['total_used_amount'] = $val['total']?$val['total']:'';
        }

        foreach($giftCartAccount_data as $value){
            foreach($value as $val){
                $item = $collection->getNewEmptyItem();
                $item->setData($val);
                $collection->addItem($item);
            }
        }
        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    /**
     * Define grid columns
     */
    protected function _prepareColumns()
    {
        $this->addColumn('giftcardaccount_id',
            array(
                'header' => Mage::helper('enterprise_giftcardaccount')->__('ID'),
                'type' => 'number',
                'index' => 'giftcardaccount_id'
            ));

        $this->addColumn('code',
            array(
                'header' => Mage::helper('enterprise_giftcardaccount')->__('Code'),
                'index' => 'code'
            ));

        $this->addColumn('website',
            array(
                'header' => Mage::helper('enterprise_giftcardaccount')->__('Website'),
                'index' => 'website_id',
                'type' => 'options',
                'options' => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash()
            ));

        $this->addColumn('date_created',
            array(
                'header' => Mage::helper('enterprise_giftcardaccount')->__('Date Created'),
                'type' => 'date',
                'index' => 'date_created'
            ));

        $this->addColumn('date_expires',
            array(
                'header' => Mage::helper('enterprise_giftcardaccount')->__('Expiration Date'),
                'type' => 'date',
                'index' => 'date_expires'
            ));

        $this->addColumn('status',
            array(
                'header' => Mage::helper('enterprise_giftcardaccount')->__('Active'),
                'index' => 'status',
                'type' => 'options',
                'options' => array(
                    Enterprise_GiftCardAccount_Model_Giftcardaccount::STATUS_ENABLED =>
                        Mage::helper('enterprise_giftcardaccount')->__('Yes'),
                    Enterprise_GiftCardAccount_Model_Giftcardaccount::STATUS_DISABLED =>
                        Mage::helper('enterprise_giftcardaccount')->__('No'),
                )
            ));

        $this->addColumn('state',
            array(
                'header' => Mage::helper('enterprise_giftcardaccount')->__('Status'),
                'index' => 'state',
                'type' => 'options',
                'options' => Mage::getModel('enterprise_giftcardaccount/giftcardaccount')->getStatesAsOptionList()
            ));

        $this->addColumn('balance',
            array(
                'header' => Mage::helper('enterprise_giftcardaccount')->__('Balance'),
                'currency_code' => Mage::app()->getStore()->getBaseCurrency()->getCode(),
                'type' => 'number',
                'renderer' => 'enterprise_giftcardaccount/adminhtml_widget_grid_column_renderer_currency',
                'index' => 'balance'
            ));

        $this->addColumn('original_amount',
            array(
                'header' => Mage::helper('enterprise_giftcardaccount')->__('Original Amount'),
                'index' => 'original_amount'
            )
        );

        $this->addColumn('generated_order_number',
            array(
                'header' => Mage::helper('enterprise_giftcardaccount')->__('Gift Card Generated to Order Number'),
                'index' => 'generated_order_number'
            )
        );

        $this->addColumn('used_date',
            array(
                'header' => Mage::helper('enterprise_giftcardaccount')->__('Used Date'),
                'index' => 'used_date',
                'type' => 'datetime'
            )
        );

        $this->addColumn('used_order_number',
            array(
                'header' => Mage::helper('enterprise_giftcardaccount')->__('Used In Order Number'),
                'index' => 'used_order_number'
            )
        );

        $this->addColumn('used_amount',
            array(
                'header' => Mage::helper('enterprise_giftcardaccount')->__('Used Amount'),
                'index' => 'used_amount'
            )
        );

        $this->addColumn('total_used_amount',
            array(
                'header' => Mage::helper('enterprise_giftcardaccount')->__('Total Used Amount'),
                'index' => 'total_used_amount',
            )
        );

        $this->addColumn('receptor_name',
            array(
                'header' => Mage::helper('enterprise_giftcardaccount')->__('Receptor Name'),
                'index' => 'receptor_name'
            )
        );

        $this->addColumn('receptor_email',
            array(
                'header' => Mage::helper('enterprise_giftcardaccount')->__('Receptor Email '),
                'index' => 'receptor_email'
            )
        );

        return parent::_prepareColumns();
    }
}
