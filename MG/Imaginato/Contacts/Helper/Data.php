<?php
class Imaginato_Contacts_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function upload($file)
  {
      $uploader = new Varien_File_Uploader('file');
      $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png', 'pdf', 'doc', 'docx'));
      $uploader->setAllowRenameFiles(true);
      $uploader->setFilesDispersion(false);
      $path = Mage::getBaseDir('media') . DS . 'contacts' . DS;
      $uploader->save($path, $file);
      return 'contacts/'.$uploader->getUploadedFileName();
  }
  public function getActiveGoogleCapcha()
  {
	$storeId = Mage::app()->getStore()->getStoreId();
	return Mage::getStoreConfig('contacts/google_capcha/enabled', $storeId);
  }
  public function getSiteKey()
  {
	$storeId = Mage::app()->getStore()->getStoreId();
	return Mage::getStoreConfig('contacts/google_capcha/site_key', $storeId);
  }
  public function getSecretKey()
  {
	$storeId = Mage::app()->getStore()->getStoreId();
	return Mage::getStoreConfig('contacts/google_capcha/secret_key', $storeId);
  }
}
