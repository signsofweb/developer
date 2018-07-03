<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Adminhtml creditmemo create
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Imaginato_Alipay_Block_Refund extends Imaginato_Alipay_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'order_id';
        $this->_controller = 'refund';

        parent::__construct();

        $this->_removeButton('delete');
        $this->_removeButton('save');
    }

    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    public function getHeaderText()
    {
        $header = Mage::helper('imaginato_alipay')->__('New Refund for Order #%s', $this->getOrder()->getRealOrderId());

        return $header;
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/sales_order/view', array('order_id'=>$this->getOrder()->getId()));
    }
}
