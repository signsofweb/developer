<?php

class Imaginato_ImportProductImage_Adminhtml_IpiController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('System'))->_title($this->__('Import Product Images'));

        $this->loadLayout()
             ->_setActiveMenu('system');
        $block = $this->getLayout()->createBlock(
                'Mage_Core_Block_Template',
                'importproductimage',
                array('template' => 'imaginato/importproductimage/index.phtml')
                );
        $this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();

    }

    public function importAction() {
        $message = array(); //message return

        $folder = $this->getRequest()->getParam('folder');

        if(!Mage::helper('importproductimage')->getImageDir($folder)) 
        {
            //folder isn't exist
            $message = array('error'=>$this->message('error','The folder '.$folder.' is not exist!'));
            echo Mage::helper('core')->jsonEncode($message);
            return;
        }
        
        $count = $this->getRequest()->getParam('count');
        if($count === null) {
            return;
        }
        $productSkus = Mage::helper('importproductimage')->getProductList($folder);
        $productCount = count($productSkus);

        if($count > $productCount) {
            $count = $productCount;
        }


        $i = 0; //counting product
        $successCount = 0; //counting success product upload
        $faileds = array(); //listing failed product
        $mainCount = 0;
        $altCount = 0;
        $baseCount = 0;
        $smallCount = 0;
        $thumbCount = 0;
        
        foreach($productSkus as $sku => $files)
        {
            $attr = array();
            if(isset($files['base'])) {
                $baseCount += count($files['base']);
                $attr['image'] = $this->getFirstItem('base', $files['base']); 
            }
            if(isset($files['small'])) {
                $smallCount += count($files['small']);
                $attr['small'] = $this->getFirstItem('small', $files['small']); 
            }
            if(isset($files['thumb'])) {
                $thumbCount += count($files['thumb']);
                $attr['thumb'] = $this->getFirstItem('thumb', $files['thumb']);
            } 
            if(isset($files['alt'])) {
                $altCount += count($files['alt']);
                $attr['alt'] = $this->getFirstItem('alt', $files['alt']);
            } 
            if(isset($files[null])) {
                $mainCount += count($files[null]);
                $attr['parent'] = $files[null];
            }
            

            $product = $this->getProduct($sku);
            if($product->getId()) {
                $setting_level = array(); //for detect attribute
                //set product base image
                if(isset($attr['image'])) {
                    $base_image_name = $attr['image']; //the first image will use
                    $image_path = Mage::helper('importproductimage')->getImageDir($folder).DS.'base'.DS.$base_image_name;

                    //check media type
                    $mediaAttr = array('image');
                    $setting_level[] = 1; //base image is setted
                    //check small image
                    if(isset($attr['small'])) {
                        if($base_image_name == $attr['small']) {
                            $mediaAttr[] = 'small_image';
                            //delete file in small folder
                            unlink(Mage::helper('importproductimage')->getImageDir($folder).DS.'small'.DS.$base_image_name);
                            unset($attr['small']);
                            $setting_level[] = 2; //small image is setted
                        }
                    }
                    //check thumbnail image
                    if(isset($attr['thumb'])) {
                        if($base_image_name == $attr['thumb']) {
                            $mediaAttr[] = 'thumbnail';
                            //delete file in thumb folder
                            unlink(Mage::helper('importproductimage')->getImageDir($folder).DS.'thumb'.DS.$base_image_name);
                            unset($attr['thumb']);
                            $setting_level[] = 3; //thumb image is setted
                        }
                    }

                    if(isset($attr['alt'])) {
                        if($base_image_name == $attr['alt']) {
                            $mediaAttr[] = 'alt_small_image';
                            //delete file in alt folder
                            unlink(Mage::helper('importproductimage')->getImageDir($folder).DS.'alt'.DS.$base_image_name);
                            unset($attr['alt']);
                            $setting_level[] = 4;                 
                        }
                    }

                    if(file_exists($image_path)) {
                        $product->addImageToMediaGallery($image_path, $mediaAttr, true, false);
                    }
                    

                }

                if(isset($attr['small'])) {
                    $small_image_name = $attr['small'];
                    $image_path = Mage::helper('importproductimage')->getImageDir($folder).DS.'small'.DS.$small_image_name;
                    $setting_level[] = 2;
                    $mediaAttr = array('small_image');
                    //check thumbnail image
                    if(isset($attr['thumb'])) {
                        if($small_image_name == $attr['thumb']) {
                            $mediaAttr[] = 'thumbnail';
                            //delete file in thumb folder
                            unlink(Mage::helper('importproductimage')->getImageDir($folder).DS.'thumb'.DS.$small_image_name);
                            unset($attr['thumb']);
                            $setting_level[] = 3; //thumb image is setted
                        }
                    }

                    if(isset($attr['alt'])) {
                        if($small_image_name == $attr['alt']) {
                            $mediaAttr[] = 'alt_small_image';
                            //delete file in alt folder
                            unlink(Mage::helper('importproductimage')->getImageDir($folder).DS.'alt'.DS.$small_image_name);
                            unset($attr['alt']);
                            $setting_level[] = 4;                 
                        }
                    }

                    if(file_exists($image_path)) {
                        $product->addImageToMediaGallery($image_path, $mediaAttr, true, false);
                    }
                }

                if(isset($attr['thumb'])) {
                    $thumb_image_name = $attr['thumb'];
                    $image_path = Mage::helper('importproductimage')->getImageDir($folder).DS.'thumb'.DS.$thumb_image_name;
                    $setting_level[] = 3;
                    $mediaAttr = array('thumbnail');

                    if(isset($attr['alt'])) {
                        if($thumb_image_name == $attr['alt']) {
                            $mediaAttr[] = 'alt_small_image';
                            //delete file in alt folder
                            unlink(Mage::helper('importproductimage')->getImageDir($folder).DS.'alt'.DS.$thumb_image_name);
                            unset($attr['alt']);
                            $setting_level[] = 4;                 
                        }
                    }
                    
                    if(file_exists($image_path)) {
                        $product->addImageToMediaGallery($image_path, $mediaAttr, true, false);
                    }
                }

                if(isset($attr['alt'])) {
                    $alt_image_name = $attr['alt'];
                    $image_path = Mage::helper('importproductimage')->getImageDir($folder).DS.'alt'.DS.$alt_image_name;
                    $setting_level[] = 3;
                    $mediaAttr = array('alt_small_image');
                    
                    if(file_exists($image_path)) {
                        $product->addImageToMediaGallery($image_path, $mediaAttr, true, false);
                    }
                }

                //check image in root image folder
                if(isset($attr['parent'])) {
                    for($i = 0; $i < count($attr['parent']); $i++) {
                        $image_name = $attr['parent'][$i];
                        $image_path = Mage::helper('importproductimage')->getImageDir($folder).DS.$image_name;
                        if(file_exists($image_path)) {
                            $product->addImageToMediaGallery($image_path, null, true, false);
                            
                        }
                    }
                }
                if($product->save())
                {
                    $successCount++;
                }
                else {
                    $faileds[] = $sku.' - '.$this->__('Cannot save product').'<br>';
                    if(isset($files['base'])) {
                        $baseCount -= count($files['base']); 
                    }
                    if(isset($files['small'])) {
                        $smallCount -= count($files['small']);
                    }
                    if(isset($files['thumb'])) {
                        $thumbCount -= count($files['thumb']);
                    } 
                    if(isset($files['alt'])) {
                        $altCount -= count($files['alt']);
                    } 
                    if(isset($files[null])) {
                        $mainCount -= count($files[null]);
                    }
                }
            }
            else {
               $faileds[] = $sku.' - '.$this->__('Product is not available').'<br>';
               if(isset($files['base'])) {
                    $baseCount -= count($files['base']); 
                }
                if(isset($files['small'])) {
                    $smallCount -= count($files['small']);
                }
                if(isset($files['thumb'])) {
                    $thumbCount -= count($files['thumb']);
                } 
                if(isset($files['alt'])) {
                    $altCount -= count($files['alt']);
                } 
                if(isset($files[null])) {
                    $mainCount -= count($files[null]);
                }
            }
            if($i >= $count) {
                break;
            }
            $i++;
        }
        $message['success'] = $successCount;
        $message['mainCount'] = $mainCount;
        $message['altCount'] = $altCount;
        $message['baseCount'] = $baseCount;
        $message['smallCount'] = $smallCount;
        $message['thumbCount'] = $thumbCount;
        if(count($faileds) == 0) {
            $message['failedCount'] = 0;
        }
        else {
            $message['failedCount'] = count($faileds);
        }
        echo Mage::helper('core')->jsonEncode($message);
    }
    public function getProduct($sku) {
        return Mage::helper('catalog/product')->getProduct($sku, null, 'sku');
    }

    public function cpAction() {
        $folder = $this->getRequest()->getParam('folder');
        if(!Mage::helper('importproductimage')->getImageDir($folder)) 
        {
            //folder isn't exist

            $message = array('error'=>$this->message('error','The folder '.$folder.' is not exist!'));
            echo Mage::helper('core')->jsonEncode($message);
            return;
        }
        if(!Mage::helper('importproductimage')->getProductCount($folder))
        {
            $message = array('error'=>$this->message('error', 'Nothing to import! No image found on your folder!'));
            echo Mage::helper('core')->jsonEncode($message);
            return;
        }
        echo Mage::helper('core')->jsonEncode(Mage::helper('importproductimage')->getProductCount($folder));
        return;
    }

    protected function getFirstItem($folder, $files)
    {
        //return first value and delete all image begin by 2
        $dir = Mage::helper('importproductimage')->getImageDir($folder);
        if(is_array($files)) {
            if(count($files) > 1) {
                for($i = 1; $i < count($files); $i++)
                {
                    unlink($dir.DS.$files[$i]);
                }
            }
            return $files[0];
        }
        return $files;
    }

    protected function message($type, $content)
    {
        $html = '<div id="messages"><ul class="messages"><li class="'.$type.'-msg"><ul><li><span>';
        $html .= $this->__($content);
        $html .= '</span></li></ul></li></ul></div>';
        return $html;
    }

}