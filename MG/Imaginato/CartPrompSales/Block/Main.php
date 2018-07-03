<?php

class Imaginato_CartPrompSales_Block_Main extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'main';
        $this->_blockGroup = 'cartprompsales';

        $this->_headerText = 'Shopping Cart Promp Sales';

        parent::__construct();
        $this->removeButton('back');
    }
}
