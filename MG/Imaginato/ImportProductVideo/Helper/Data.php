<?php
class Imaginato_ImportProductVideo_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $productListVideo = array();
    public function getListProductVideo($folder)
    {
        $this->getList($folder);
        return $this->productListVideo;
    }
    public function getProductCount($folder)
    {
        $this->getList($folder);
        return count($this->productListVideo);
    }
    public function getList($folder)
    {
        $currentFolder = $this->getVideoDir($folder);
        //list video files in parent
        $videoList = $this->getListFiles($currentFolder);
        //folder is empty
        if(count($videoList) <= 0) 
        {
            return;
        }

        foreach($videoList as $video) 
        {
            if('mp4' == strtolower(pathinfo($video, PATHINFO_EXTENSION))){
                $videoNames = explode("(", pathinfo($video)['filename']); //$videoNames[0] = sku, $videoNames[1] = video name upload show
                $this->productListVideo[trim($videoNames[0])][str_replace(')','',$videoNames[1])] = $video;
            }
        }
    }

    protected function getListFiles($dir)
    {
        $listFiles = array();
        if(is_dir($dir)) 
        {
            $listFiles = scandir($dir);
            unset($listFiles[array_search('.', $listFiles, true)]);
            unset($listFiles[array_search('..', $listFiles, true)]);
        }
        return $listFiles;
    }

    public function getVideoDir($folder, $ds = false) 
    {
        $dir = Mage::getBaseDir('base'). DS;
        $folders = explode('/', $folder);
        foreach($folders as $_folder){
            $dir = $dir . $_folder;
            chmod($dir . $_folder, 0777);
            $dir = $dir . DS;
        }
        $_dir = Mage::getBaseDir('base'). DS . $folder;
        if($ds){
            $_dir = $_dir . DS;
        }
        if(is_dir($_dir)) 
        {
            return $_dir;
        }
        return false;
    }
    public function getVideoExistsDir($folder) 
    {
        $dir = Mage::getBaseDir('base'). DS . $folder . DS .'existsvideo';
        if(!is_dir($dir)) 
        {
            mkdir($dir, 0777, true);
        }
        $dir = $dir . DS;
        return $dir;
    }
    public function getVideoUnmatchDir($folder) 
    {
        $dir = Mage::getBaseDir('base'). DS . $folder . DS .'unmatched';
        if(!is_dir($dir)) 
        {
            mkdir($dir, 0777, true);
        }
        $dir = $dir . DS;
        return $dir;
    }
    public function getProductVideoDir($productId)
    {
        $product_video_path_dir = Mage::getBaseDir('media') . DS . 'cmsmart' . DS . 'productvideo' . DS . 'video' . DS . 'product' . DS . $productId;
        if(!is_dir($product_video_path_dir)) 
        {
            mkdir($product_video_path_dir, 0777, true);
        }
        $product_video_path_dir = $product_video_path_dir . DS;
        return $product_video_path_dir;
    }

    public function getVideosByProductId($productId){
        $videoCols = Mage::getModel('productvideo/productvideo')->getCollection()
                    ->addFieldToFilter('product_id',$productId)
                    ->addFieldToFilter('provider','local');
        return $videoCols->toArray();
    }

    public function getProductVideoThumbnailDir($productId)
    {
        $product_video_thumbnail_path_dir = Mage::getBaseDir('media') . DS . 'cmsmart' . DS . 'productvideo' . DS . 'thumbnail' . DS . 'product' . DS . $productId;
        if(!is_dir($product_video_thumbnail_path_dir)) 
        {
            mkdir($product_video_thumbnail_path_dir, 0777, true);
        }
        $product_video_thumbnail_path_dir = $product_video_thumbnail_path_dir . DS;
        return $product_video_thumbnail_path_dir;
    }
}