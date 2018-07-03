<?php
namespace Convert\Newsletter\Block;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;


/**
 * Customer front  newsletter manage block
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Newsletter extends \Magento\Customer\Block\Account\Dashboard
{

    /**
     * @var string
     */
    protected $_template = 'form/newsletter.phtml';

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsSubscribed()
    {
        return $this->getSubscriptionObject()->isSubscribed();
    }
    public function getInterestsItem(){
        return $this->getSubscriptionObject()->getInterests();
    }
    public function getProgramItem(){
        return $this->getSubscriptionObject()->getProgram();
    }
    /**
     * Return the save action Url.
     *
     * @return string
     */
    public function getAction()
    {
        return $this->getUrl('newsletter/manage/save');
    }
}