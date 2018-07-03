<?php
$installer = $this;

$installer->startSetup();

$installer->addAttribute('customer', 'bir_year',
    array(
        'type' => 'int',
        'label'=> 'Birthday Year',
        'required' => false,
        'visible' => 1,
        'visible_on_front' => 1,
        'validate_rules'   => 'a:1:{s:16:"input_validation";s:7:"numeric";}',
        'is_visible' => 1,
        'sort_order'=> 90,
        'position' => 90,
        'is_system' => 0,
        'admin_checkout'=> 1,
        'system'    => false
    )
);
$installer->addAttribute('customer', 'bir_month',
    array(
        'type' => 'int',
        'label'=> 'Birthday Mouth',
        'required' => false,
        'visible' => 1,
        'visible_on_front' => 1,
        'validate_rules'   => 'a:1:{s:16:"input_validation";s:7:"numeric";}',
        'is_visible' => 1,
        'sort_order'=> 90,
        'position' => 90,
        'is_system' => 0,
        'admin_checkout'=> 1,
        'system'    => false
    )
);
$attribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'bir_year');
$attribute->setData('used_in_forms', array(
    'customer_account_create',
    'customer_account_edit',
    'checkout_register',
    'adminhtml_checkout'
));
$attribute->setData('is_used_for_customer_segment', 1);
$attribute->save();
$attribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'bir_month');
$attribute->setData('used_in_forms', array(
    'customer_account_create',
    'customer_account_edit',
    'checkout_register',
    'adminhtml_checkout'
));
$attribute->setData('is_used_for_customer_segment', 1);
$attribute->save();

$attributeId = $installer->getAttributeId('customer_address', 'prefix');
$installer->updateAttribute('customer_address', $attributeId, 'is_visible', 0);

$installer->endSetup();