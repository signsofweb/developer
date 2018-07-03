<?php
class Imaginato_Contacts_Block_Adminhtml_Renderer_Subject extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
	public function render(Varien_Object $row)
	{
		$value =  $row->getData($this->getColumn()->getIndex());
		$model = Mage::getModel('imaginato_contacts/enqueries')->load($value);
		return $model->getTitle();
	 
	}	 
}
