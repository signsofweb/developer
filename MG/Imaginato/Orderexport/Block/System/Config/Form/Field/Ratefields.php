<?php
class Imaginato_Orderexport_Block_System_Config_Form_Field_Ratefields extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn(
            'from', array(
                'label' => Mage::helper('mailchimp')->__('From'),
                'style' => 'width:120px',
            )
        );
        $this->addColumn(
            'to', array(
                'label' => Mage::helper('mailchimp')->__('To'),
                'style' => 'width:120px',
            )
        );
        $this->addColumn(
            'rate', array(
                'label' => Mage::helper('mailchimp')->__('Rate'),
                'style' => 'width:120px',
            )
        );
        parent::__construct();
        $this->setTemplate('imaginato/orderexport/system/config/form/field/array_dropdown.phtml');
    }

    public function getDateHtml($id,$value,$i,$from=true){
        if($from){
            $class = 'validate-date validate-date-range date-range-rate'.$i.'-from';
        }else{
            $class = 'validate-date validate-date-range date-range-rate'.$i.'-to';
        }
        $element = new Varien_Data_Form_Element_Date(array(
            'format'   => Varien_Date::DATE_INTERNAL_FORMAT,
            'name'     => $id,
            'image'    => $this->getSkinUrl('images/grid-cal.gif'),
            'value'    => $value,
            'class'    => $class
        ));
        $element->setId($id);
        $element->setForm(new Varien_Object());
        return $element->getElementHtml();
    }
}