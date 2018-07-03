<?php

namespace Convert\Newsletter\Controller\Subscriber;
use Magento\Newsletter\Model\Subscriber;
class NewAction extends \Magento\Newsletter\Controller\Subscriber\NewAction
{
	protected  $resultJsonFactory;

	
    public function __construct(
		\Magento\Framework\App\Action\Context $context,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Subscriber $subscriber
    ) {
        parent::__construct(
            $context,
            $subscriberFactory,
            $customerSession,
            $storeManager,
            $customerUrl,
			$customerAccountManagement
        );
        $this->subscriber = $subscriber;
		$this->resultJsonFactory = $resultJsonFactory;   
    }
    /**
     * New subscription action
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function execute()
    {
        parse_str($this->getRequest()->getParam('serializedForm'),$data);
		
        if ($data) {

            $email = $data['email'];
            $name =  isset($data['name']) ? $data['name'] : null;
            $gender = isset($data['gender']) ? $data['gender'] : null;
            $dob = isset($data['dob']) ? $data['dob'] : null;
            $interests = [];
            if (isset($data['interests']['general_health']) && $data['interests']['general_health'] == 'on'){
                $interests[] = 'General Health';
            }
            if (isset($data['interests']['weight_management']) && $data['interests']['weight_management'] == 'on'){
                $interests[] = 'Weight Management';
            }
            if (isset($data['interests']['superfoods']) && $data['interests']['superfoods'] == 'on'){
                $interests[] = 'SuperFoods';
            }
            if (isset($data['interests']['kids_health']) && $data['interests']['kids_health'] == 'on'){
                $interests[] = 'Kids Health';
            }
            if (isset($data['interests']['e50_health']) && $data['interests']['e50_health'] == 'on'){
                $interests[] = '50+ Health';
            }
            $interests = implode(', ',$interests);
            $interests = trim($interests);
			$resultJson = $this->resultJsonFactory->create();
			$postcode=isset($data['post_code']) ? $data['post_code'] : null;
            try {
                $this->validateEmailFormat($email);
                $this->validateGuestSubscription();
                $this->validateEmailAvailable($email);

                $subscriber = $this->_subscriberFactory->create();
                $checkSubscriber = $this->subscriber->loadByEmail($email);
                if ($checkSubscriber->isSubscribed()) {
                    $status = $subscriber->subscribe($email);
                    $subscriber->setEmail($email)->setName($name)
                                ->setGender($gender)
                                ->setPostCode($postcode)
                                ->setDob($dob)
                                ->setInterests(json_encode($interests))->save();
                    
				}else{
                    $subscriber->setName($name)
                                ->setGender($gender)
                                ->setPostCode($postcode)
                                ->setDob($dob)
                                ->setInterests(json_encode($interests));
                                
                    $status = $subscriber->subscribe($email);
                    
                }
                if ($status == \Magento\Newsletter\Model\Subscriber::STATUS_NOT_ACTIVE) {
                   return $resultJson->setData(['error' => false, 'message' => 'Done', 'alert'=>'The confirmation request has been sent']);
                } else {
                   return $resultJson->setData(['error' => false, 'message' => 'Done', 'alert'=>'Thank you for your subscription.']);
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
               return $resultJson->setData(['error' => true, 'message' => $e->getMessage()]);
            } catch (\Exception $e) {
               return $resultJson->setData(['error' => true, 'message' => $e->getMessage()]);
            }
        }
    }

}
