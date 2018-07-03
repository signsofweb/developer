<?php
class Imaginato_ImportProductVideo_Adminhtml_ImprovdController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('System'))->_title($this->__('Import Product Videos'));

        $this->loadLayout()
             ->_setActiveMenu('system');
        $block = $this->getLayout()->createBlock(
                'Mage_Core_Block_Template',
                'import.product.video',
                array('template' => 'imaginato/product/import/video/index.phtml')
                );
        $this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();

    }

    public function importAction() {
        $message = array(); //message return
        $count = $this->getRequest()->getParam('count');
        if($count === Null) {
            $message['error'] = "Nothing to import";
            echo Mage::helper('core')->jsonEncode($message);
            return;
        }
        $folder = $this->getRequest()->getParam('folder');
        $rootDir = Mage::helper('imaginato_importproductvideo')->getVideoDir($folder, true);
        $listProduct = Mage::helper('imaginato_importproductvideo')->getListProductVideo($folder);
        $productCount = count($listProduct);

        if($count > $productCount) {
            $count = $productCount;
        }
        $i = 0; //counting product
        $successCount = 0; //counting success product upload
        $faileds = array(); //listing failed product
        foreach($listProduct as $sku => $files)
        {
            if($i >= $count) {
                break;
            }
            $product = $this->getProduct($sku);
            if($product->getId()) {
                $save = false;
                $videoCols = Mage::helper('imaginato_importproductvideo')->getVideosByProductId($product->getId());
                $product_video_path_dir = Mage::helper('imaginato_importproductvideo')->getProductVideoDir($product->getId());
                $product_video_thumbnail_path_dir = Mage::helper('imaginato_importproductvideo')->getProductVideoThumbnailDir($product->getId());
                if($videoCols['totalRecords']){
                    foreach($files as $key => $_file){
                        $file_exists = true;
                        foreach($videoCols['items'] as $_item){
                            if('video-local-'.$key . '.' .pathinfo($_file, PATHINFO_EXTENSION) == $_item['video_id']){
                                $file_exists = false;
                                continue;
                            }
                        }
                        if($file_exists){
                            $data = array();
                            $productVideoModel = Mage::getModel('productvideo/productvideo');
                            rename($rootDir . pathinfo($_file, PATHINFO_BASENAME), $product_video_path_dir. 'video-local-'.$key . '.' .pathinfo($_file, PATHINFO_EXTENSION));
                            $data['product_id'] = $product->getId();
                            $data['video_name'] = pathinfo($_file, PATHINFO_BASENAME);
                            $data['video_id'] = 'video-local-'.$key . '.' .pathinfo($_file, PATHINFO_EXTENSION);
                            $data['provider'] = 'local';
                            $data['status'] = 1;
                            $data['thumbnail'] = 'logo.png';
                            //move thumbnail image
                            if(file_exists($rootDir.'logo.png'))
                            {
                                if(!copy($rootDir.'logo.png', $product_video_thumbnail_path_dir.'logo.png'))
                                {
                                    $faileds[] = '<li>' . pathinfo($_file, PATHINFO_BASENAME) . ' - ' . $sku .' - cannot import thumbnail image</li>';
                                }
                                else {
                                    $data['thumbnail'] = 'logo.png';
                                }
                            }
                            $productVideoModel->addData($data);
                            if($productVideoModel->save()){
                                $save = true;
                            }
                        }else{
                            $newRoorDirt = Mage::helper('imaginato_importproductvideo')->getVideoExistsDir($folder, true);
                            rename($rootDir . pathinfo($_file, PATHINFO_BASENAME), $newRoorDirt. pathinfo($_file, PATHINFO_BASENAME));
                            $faileds[] = '<li>' . pathinfo($_file, PATHINFO_BASENAME) . ' - ' . $sku .' - video already exists, move to existsvideo folder</li>';
                        }
                    }
                }else{
                    foreach($files as $key => $_file){
                        $data = array();
                        $productVideoModel = Mage::getModel('productvideo/productvideo');
                        rename($rootDir . pathinfo($_file, PATHINFO_BASENAME), $product_video_path_dir. 'video-local-'.$key . '.' .pathinfo($_file, PATHINFO_EXTENSION));
                        $data['product_id'] = $product->getId();
                        $data['video_name'] = pathinfo($_file, PATHINFO_BASENAME);
                        $data['video_id'] = 'video-local-'.$key . '.' .pathinfo($_file, PATHINFO_EXTENSION);
                        $data['provider'] = 'local';
                        $data['status'] = 1;
                        //move thumbnail image
                        if(file_exists($rootDir.'logo.png'))
                        {
                            if(!copy($rootDir.'logo.png', $product_video_thumbnail_path_dir.'logo.png'))
                            {
                                $faileds[] = '<li>' . pathinfo($_file, PATHINFO_BASENAME) . ' - ' . $sku .' - cannot import thumbnail image</li>';
                            }
                            else {
                                $data['thumbnail'] = 'logo.png';
                            }
                        }
                        $productVideoModel->addData($data);
                        if($productVideoModel->save()){
                            $save = true;
                        }
                    }
                }
                if($save)
                {
                    $successCount++;
                }
            }
            else {
                $newRoorDirt = Mage::helper('imaginato_importproductvideo')->getVideoUnmatchDir($folder, true);
                foreach($files as $key => $_file){
                    rename($rootDir . pathinfo($_file, PATHINFO_BASENAME), $newRoorDirt. pathinfo($_file, PATHINFO_BASENAME));
                    $faileds[] = '<li>' . pathinfo($_file, PATHINFO_BASENAME) . ' - ' . $sku .' - SKU not found, move to unmatched folder</li>';
                }
            }
            $i++;
        }
        $message['success'] = $successCount;
        if(count($faileds) == 0) {
            $message['faileds'] = 0;
        }
        else {
            $message['faileds'] = implode(" ", $faileds);
        }
        echo Mage::helper('core')->jsonEncode($message);
    }
    public function getProduct($sku) {
        return Mage::helper('catalog/product')->getProduct($sku, null, 'sku');
    }

    public function validateAction() {
        $folder = $this->getRequest()->getParam('folder');
        if(!Mage::helper('imaginato_importproductvideo')->getVideoDir($folder)) 
        {
            //folder isn't exist
            $message = array('error'=>"The folder {$folder} does not exist!");
            echo Mage::helper('core')->jsonEncode($message);
            return;
        }
        echo Mage::helper('core')->jsonEncode(Mage::helper('imaginato_importproductvideo')->getProductCount($folder));
        return;
    }
}