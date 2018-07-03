<?php
/**
 * Ebizmarts_MageMonkey Magento JS component
 *
 * @category    Ebizmarts
 * @package     Ebizmarts_MageMonkey
 * @author      Ebizmarts Team <info@ebizmarts.com>
 * @copyright   Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Convert\Newsletter\Model\Plugin;

class Subscriber extends \Ebizmarts\MageMonkey\Model\Plugin\Subscriber
{
   public function beforeSubscribeCustomerById(
        $subscriber,
        $customerId
    ) {
        $customer = $this->_customer->load($customerId);
        $emailHash = $customer->getEmail();
        $subscriber->loadByEmail($emailHash);
        return $this->beforeSubscribe($subscriber,$emailHash);
   }
   public function beforeSubscribe(
        $subscriber,
        $email
    ) {
    
        $storeId = $this->_storeManager->getStore()->getId();

        $isSubscribeOwnEmail = '';
        if (!$isSubscribeOwnEmail) {
            if ($this->_helper->isMonkeyEnabled($storeId)) {
                $subscriber->setImportMode(false);
                $api = $this->_api;
                if ($this->_helper->isDoubleOptInEnabled($storeId)) {
                    $status = 'pending';
                } else {
                    $status = 'subscribed';
                }
                $data = ['list_id' => $this->_helper->getDefaultList(), 'email_address' => $email, 'email_type' => 'html', 'status' => $status, 'merge_fields' => ['EMAIL'=>$email]];
                try {
                    $return = $api->listCreateMember($this->_helper->getDefaultList(), json_encode($data));
                    if (isset($return->id)) {
                        $subscriber->setMagemonkeyId($return->id);
                    }
                } catch (\Exception $e) {
                    $this->_helper->log($e->getMessage());
                }
            }
        }
        return [$email];
    }

}
