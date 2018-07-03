<?php
class Imaginato_ImportProductImage_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $productList = array();
    protected $i = 0; //counting

    public function getProductList($folder)
    {
        $this->getList($folder);
        return $this->productList;
    }
    public function getProductCount($folder)
    {
        $this->getList($folder);
        return count($this->productList);
    }

    public function getList($folder, $childFolder = null)
    {

        $currentFolder = $this->getImageDir($folder, $childFolder);
        //list image files in parent
        $imgList = $this->getListFiles($currentFolder);
        //folder is empty
        if(count($imgList) <= 0) 
        {
            return;
        }

        foreach($imgList as $image) 
        {
            if(is_dir($currentFolder.DS.$image))
            {
                //nested folder's files
                $this->getList($folder, $image);
            }
            else
            {
                $names = explode("(", $image); //$names[0] = sku
                $this->productList[trim($names[0])][$childFolder][] = $image;
            }
        }
    }

    public function getImageDir($folder, $childFolder = null) 
    {
        if(!$childFolder) 
        {
            $dir = Mage::getBaseDir('base'). DS . $folder;
        }
        else 
        {
            $dir = Mage::getBaseDir('base') . DS . $folder . DS. $childFolder;
        }
        if(is_dir($dir)) 
        {
            return $dir;
        }
        return false;
    }

    protected function getListFiles($dir)
    {
        $listFiles = array();
        if(is_dir($dir)) 
        {
            if(count(scandir($dir))) {
                $listFiles = scandir($dir);
                unset($listFiles[array_search('.', $listFiles, true)]);
                unset($listFiles[array_search('..', $listFiles, true)]);
                foreach(scandir($dir) as $file) {
                    if(!is_dir($dir.DS.$file) && !in_array(pathinfo($dir.DS.$file, PATHINFO_EXTENSION), array('jpg', 'png', 'bmp')))
                    {
                        unset($listFiles[array_search($file, $listFiles, true)]);
                    }
                }
            }
        }
        return $listFiles;
    }
}