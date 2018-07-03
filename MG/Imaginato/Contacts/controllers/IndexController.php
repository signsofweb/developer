<?php
require_once 'Mage/Contacts/controllers/IndexController.php';
class Imaginato_Contacts_IndexController extends Mage_Contacts_IndexController
{

  public function indexAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('imaginato.contactForm')
            ->setFormAction( Mage::getUrl('*/*/post') );

        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
        $this->renderLayout();
    }

  public function postAction()
  {
	$result = [];
      $post = $this->getRequest()->getPost();
      if ( $post ) {
          $translate = Mage::getSingleton('core/translate');
          /* @var $translate Mage_Core_Model_Translate */
          $translate->setTranslateInline(false);
          
          try {
              $error = false;

              if (!Zend_Validate::is(trim($post['name']) , 'NotEmpty')) {
                  $error = true;
              }

              if (!Zend_Validate::is(trim($post['email']), 'EmailAddress')) {
                  $error = true;
              }

              if (Zend_Validate::is(trim($post['hideit']), 'NotEmpty')) {
                  $error = true;
              }
              
              if (!Zend_Validate::is(trim($post['subject']) , 'NotEmpty')) {
                  $error = true;
              }
              if(!$this->checkContactsCaptcha($post['captcha']))
              {
                $result['success'] = false;
                $result['captcha'] = false;
                $result['message'] = Mage::helper('imaginato_contacts')->__('INCORRECT CAPTCHA!');
                echo Mage::helper('core')->jsonEncode($result);
                return;
              }
              
              if ($error) {
                  throw new Exception();
              }
              $file = '';
              if(isset($_FILES['file']['name'])) {
				$helper = Mage::helper('imaginato_contacts');
                $file = $helper->upload($_FILES['file']['name']);
              }
              $translate->setTranslateInline(true);
              $contactModel = Mage::getModel('imaginato_contacts/contacts');
              $contactModel->setName($post['name'])
                           ->setStoreId(Mage::app()->getStore()->getId())
                           ->setSubject($post['subject'])
                           ->setOrderNumber($post['order_number'])
                           ->setEmail($post['email'])
                           ->setComment($post['comment'])
                           ->setFile($file)
                           ->setCreatedAt(date('Y-m-d H:m:s'))
                           ->setUpdatedAt(date('Y-m-d H:m:s'))
                           ->save();
			$model = Mage::getModel('imaginato_contacts/enqueries')->load($post['subject']);
            $postObject = new Varien_Object();
			$data = [];
			$data['name'] = $post['name'];
      $data['email'] = $post['email'];
			$data['order_number'] = $post['order_number'];
			$data['subject'] = $model->getTitle();
			$data['comment'] = $post['comment'];
			$data['attachment'] = $file ? Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $file : '';
            $postObject->setData($data);
            $mailTemplate = Mage::getModel('core/email_template');
              /* @var $mailTemplate Mage_Core_Model_Email_Template */
              $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                  ->setReplyTo($post['email'])
                  ->sendTransactional(
                      Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE),
                      Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
                      $model->getEmail(),
                      null,
                      array('data' => $postObject)
                  );

              if (!$mailTemplate->getSentSuccess()) {
                  throw new Exception();
              } 
            $message = Mage::helper('imaginato_contacts')->__('Thank you for your message. We will be in touch soon');
			$result['success'] = true;
			$result['message'] = $message;
          } catch (Exception $e) {
            $translate->setTranslateInline(true);
            $message = Mage::helper('imaginato_contacts')->__('Unable to submit your request. Please, try again later');
			$result['success'] = false;
			$result['message'] = $message;
          }

      } else {
          $message = Mage::helper('imaginato_contacts')->__('No data for send. Please, try again later');
		  $result['success'] = false;
		$result['message'] = $message;
      }
	  echo Mage::helper('core')->jsonEncode($result);
  }

  public function checkContactsCaptcha($captcha)
  {
    $formId = 'contacts';
    $captchaModel = Mage::helper('captcha')->getCaptcha($formId);
    if ($captchaModel->isRequired()) {
      if (!$captchaModel->isCorrect($captcha[$formId])) {
        return false;
      }
    }
    return true;
  }
}