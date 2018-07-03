<?php
require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml').DS.'Catalog/ProductController.php';
/**
 * Catalog product controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Imaginato_Catalog_Adminhtml_Catalog_ProductController extends Mage_Adminhtml_Catalog_ProductController
{
    /**
     * Update product(s) status action
     *
     */
    public function massStatusAction()
    {
        $productIds = (array)$this->getRequest()->getParam('product');
        $storeId    = (int)$this->getRequest()->getParam('store', 0);
        $status     = (int)$this->getRequest()->getParam('status');

        try {
            $this->_validateMassStatus($productIds, $status);
            Mage::getSingleton('catalog/product_action')
                ->updateAttributes($productIds, array('status' => $status), $storeId);
            if(isset($status)){
                $importModel = Mage::getModel('imaginato_importexport/import_observer');
                $simpleIds = $importModel->getSimples($productIds,'id');
                if(!empty($simpleIds) && $status == Mage_Catalog_Model_Product_Status::STATUS_ENABLED){
                    $skus = $importModel->checkSimpleImage($simpleIds,$storeId);
                    if(!empty($skus)){
                        Mage::getSingleton('core/session')->addNotice("please check product ". implode(' , ',$skus) . ' no images');
                    }
                }
            }
            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) have been updated.', count($productIds))
            );
        }
        catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()
                ->addException($e, $this->__('An error occurred while updating the product(s) status.'));
        }

        $this->_redirect('*/*/', array('store'=> $storeId));
    }
}
