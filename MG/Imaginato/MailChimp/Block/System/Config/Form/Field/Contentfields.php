<?php
class Imaginato_MailChimp_Block_System_Config_Form_Field_Contentfields extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected $_customerAttributes;

    public function __construct()
    {
        $this->addColumn(
            'mailchimp', array(
                'label' => Mage::helper('mailchimp')->__('MailChimp'),
                'style' => 'width:120px',
            )
        );
        $this->addColumn(
            'magento', array(
                'label' => Mage::helper('mailchimp')->__('Title'),
                'style' => 'width:120px',
            )
        );
        parent::__construct();
        $this->setTemplate('imaginato/mailchimp/system/config/form/field/array_dropdown.phtml');

        $this->_customerAttributes = array();

        $scopeArray = explode('-', Mage::helper('mailchimp')->getScopeString());

        $helper = Mage::helper('mailchimp');
        $mailchimpApi = $helper->getApi($scopeArray[1], $scopeArray[0]);
        $listId = $helper->getGeneralList($scopeArray[1], $scopeArray[0]);
        $groupId = $helper->getConfigValueForScope('mailchimp/general/content',$scopeArray[1], $scopeArray[0]);
        if(empty($groupId)){
            return;
        }
        try{
            $interestCategory = $mailchimpApi->lists->interestCategory->interests->getAll($listId,$groupId);
        }catch(Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            return;
        }
        foreach($interestCategory['interests'] as $interest){
            $label = $interest['name'].' ('.$interest['subscriber_count'].' members)';
            $id = $interest['id'];
            $this->_customerAttributes[$id] = $label;
        }
    }
}