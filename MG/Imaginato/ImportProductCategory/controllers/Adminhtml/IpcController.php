<?php
class Imaginato_ImportProductCategory_Adminhtml_IpcController extends Mage_Adminhtml_Controller_action
{
    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('system/convert')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Import Product Category'), Mage::helper('adminhtml')->__('Import Product Category'));
        $this->getLayout()->getBlock('head')->setTitle($this->__('Import Product Category'));
        $this->renderLayout();
    }

    public function importAction()
    {
        $coreSession = Mage::getSingleton('core/session');
        if (isset($_FILES['file']['name']) and (file_exists($_FILES['file']['tmp_name'])))
        {
            $successCount = $errorCount = 0;
            $filename = $_FILES['file']['tmp_name'];
            if($this->validator($_FILES['file'])) {
                $csvData = $this->getCsvData($filename);
                if(!$csvData || count($csvData) < 2)
                {
                    $coreSession->addError('No data to import or the file is wrong format!');
                }
                else {
                    //ignore the first row
                    unset($csvData[0]);
                    $productModel = Mage::getModel('catalog/product');
                    $categoryModel = Mage::getModel('catalog/category');
                    foreach($csvData as $index => $row)
                    {
                        if(count($row) < 2) {
                            $coreSession->addError('Row no. '.$index.' is not valid the format!');
                            continue;
                        }
                        $productSkus = explode("|", $row[0]);
                        if(count($productSkus) < 1)
                        {
                            $coreSession->addError('Row no. '.$index.' is not valid the format!');
                            continue;
                        }
                        $categoriesName = explode("|", $row[1]);
                        if(count($categoriesName) < 1)
                        {
                            $coreSession->addError('Row no. '.$index.' is not valid the format!');
                            continue;
                        }
                        //get category ids
                        $categoryIds = array();
                        $categoryError = false;
                        foreach($categoriesName as $_index => $categoryName)
                        {
                            if(!$categoryName)
                            {
                                $coreSession->addError('Row no. '.$index.': category name must not be null, ignore the row!');
                                $categoryError = true;
                                break;
                            }
                            if($_index == 0)
                            {
                                $category = $categoryModel->loadByAttribute('name', trim($categoryName));
                                if($category && $category->getParentId() !== 0 && $category->getParentId() !== 1)
                                {
                                    $coreSession->addError('Row no. '.$index.': category '.$categoryName.' has parent id, please provide exactly its parent id! Ignore the row!');
                                    $categoryError = true;
                                    break;
                                }
                            }
                            else {
                                $category = $this->getCategoryByNameByParent(trim($categoryName),$categoryIds[$_index - 1]);
                            }
                            if(!$category)
                            {
                                $coreSession->addError('Row no. '.$index.': cannot get category name: "'.$categoryName.'", ignore the row!');
                                $categoryError = true;
                                break;
                            }
                            $categoryIds[] = $category->getId();
                        }
                        if(empty($categoryIds) || $categoryError)
                        {
                            continue;
                        }

                        foreach($productSkus as $productSku)
                        {
                            if(!$productSku)
                            {
                                $coreSession->addError('Row no. '.$index.': sku must not be null!');
                                continue;
                            }
                            $product = $productModel->loadByAttribute('sku', $productSku);
                            if(!$product)
                            {
                                $coreSession->addError('Row no. '.$index.': cannot get product sku: "'.$productSku.'"!');
                                continue;
                            }
                            try {
                                $oldCategoryIds = $product->getCategoryIds();
                                $newCategoryIds = $this->arrayMerge($oldCategoryIds, $categoryIds);
                                if($this->checkDuplicateArray($oldCategoryIds, $categoryIds))
                                {
                                    continue;
                                }
                                $product->setCategoryIds($newCategoryIds);
                                $product->save();
                                $successCount++;
                            }
                            catch (Exception $e)
                            {
                                $coreSession->addError('Product with sku "'.$productSku.'" on row no. '.$index.' has error when try to save!');
                            }
                        }
                    }
                }
                $coreSession->addNotice('Import completed! Success: '.$successCount.' product(s)');
            }
        }
        else {
            $coreSession->addError('No file selected!');
        }
        $this->_redirect('*/*/');
    }

    protected function getCsvData($file)
    {
        $csvObject = new Varien_File_Csv();
        try {
            return $csvObject->getData($file);
        } catch (Exception $e) {
            Mage::log('Csv: ' . $file . ' - getCsvData() error - '. $e->getMessage(), Zend_Log::ERR, 'exception.log', true);
            return false;
        }
    }

    //merge two two demensions arrays, delete the duplicate value
    protected function arrayMerge($array1, $array2)
    {
        foreach($array1 as $index => $value)
        {
            if($keyFound = array_search($value, $array2))
            {
                unset($array2[$keyFound]);
            }
        }
        return array_merge($array1, $array2);
    }

    protected function getCategoryByNameByParent($name, $parentId)
    {
        $category = Mage::getResourceModel('catalog/category_collection')
            ->addAttributeToFilter('parent_id',$parentId)
            ->addAttributeToFilter('name',$name)
            ->getFirstItem();
        if($category->getId())
        {
            return $category;
        }
        return false;
    }

    //check if two array is same values
    protected function checkDuplicateArray($array1, $array2)
    {
        foreach($array1 as $index => $value)
        {
            if($keyFound = array_search($value, $array2))
            {
                unset($array2[$keyFound]);
            }
        }
        if(empty($array2))
        {
            return true;
        }
        return false;
    }

    protected function validator($file)
    {
        $coreSession = Mage::getSingleton('core/session');
        $typeAllowed = array('application/vnd.ms-excel');
        if(array_search($file['type'], $typeAllowed) === FALSE || empty($this->validFile($file['tmp_name'])))
        {
            $coreSession->addError('File is wrong format. The correct format is CSV (Comma delimited).');
            return false;
        }
        return true;
    }

    protected function validFile($file)
    {
        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if(!is_array($data) || count($data) != 2)
                {
                    return false;
                }
            }
            return true;
        }
        return false;
    }
    public function exportPostAction()
    {
        $csv = '';
        $fileName   = 'category_skus_import_'.Mage::getModel('core/date')->date('Y-m-d').'.csv';
        $header = array('sku', 'category');
        $csv.= implode(',', $header)."\n";
        $data = array('17GHDJHSJ13|17JSDHDJ14|17HJJHFJKHJK|18GDHGJBHJS', 'AP|Men|Sale - 20%');
        $csv.= implode(',', $data)."\n";
        $this->_prepareDownloadResponse($fileName, $csv);
    }
}