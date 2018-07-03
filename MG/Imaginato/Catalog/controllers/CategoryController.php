<?php
require_once Mage::getModuleDir('controllers', 'Mage_Catalog').DS.'CategoryController.php';

class Imaginato_Catalog_CategoryController extends Mage_Catalog_CategoryController
{
    /**
     * Category view action
     */
    public function viewAction()
    {
        $isAjax = $this->getRequest()->getParam('jump_page');
        if (!$isAjax) {
            parent::viewAction();
        }else{
            if ($category = $this->_initCatagory()) {
                Mage::getSingleton('catalog/session')->setLastViewedCategoryId($category->getId());

                $update = $this->getLayout()->getUpdate();
                $update->addHandle('default');

                $this->addActionLayoutHandles();
                $update->addHandle($category->getLayoutUpdateHandle());
                $update->addHandle('CATEGORY_' . $category->getId());
                $update->addHandle('catalog_category_ajax');
                $this->loadLayoutUpdates();
                $this->generateLayoutXml()->generateLayoutBlocks();
                $this->renderLayout();
            }
        }
    }
}
