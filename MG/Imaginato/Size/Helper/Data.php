<?php

/**
 * Size chart Data helper
 *
 * @package    Imaginato_Size
 */
class Imaginato_Size_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_NODE_BLOCK_TEMPLATE_FILTER = 'global/size/block/tempate_filter';

    /**
     * Retrieve Template processor for Block Content
     *
     * @return Varien_Filter_Template
     */
    public function getBlockTemplateProcessor()
    {
        $model = (string)Mage::getConfig()->getNode(self::XML_NODE_BLOCK_TEMPLATE_FILTER);
        return Mage::getModel($model);
    }

    public function getChartByProductId($productId)
    {
        /** @var Imaginato_Size_Model_Block $chartModel */
        $chartModel = Mage::getModel('size/block');
        $chartId = $chartModel->getChartByProductId($productId);

        return $chartId;
    }
}
