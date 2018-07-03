<?php

class Imaginato_CartPrompSales_Block_Banner extends Mage_Core_Block_Template
{
    protected $_template = 'imaginato/cartprompsales/banner.phtml';
    protected $_block_id;

    public function _construct()
    {
        if(!Mage::getStoreConfig('cartprompsales/generl/block_enable')){
            $this->setTemplate('');
        }
        $block_id = Mage::getStoreConfig('cartprompsales/generl/block_id');
        if(!$block_id){
            $this->setTemplate('');
        }
        $this->_block_id = $block_id;
        parent::_construct();
    }

    public function getBlockId(){
        return $this->_block_id;
    }
}
