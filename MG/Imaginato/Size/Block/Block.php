<?php

class Imaginato_Size_Block_Block extends Mage_Core_Block_Abstract
{
    /**
     * Prepare Content HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $blockId = $this->getBlockId();
        $html = '';
        if ($blockId) {
            $block = Mage::getModel('size/block')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($blockId);
            if ($block->getIsActive()) {
                /* @var $helper Imaginato_Size_Helper_Data */
                $helper = Mage::helper('size');
                $processor = $helper->getBlockTemplateProcessor();
                $html = $processor->filter($block->getContent());
                $this->addModelTags($block);
            }
        }
        return $html;
    }
}
