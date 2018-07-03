<?php

class Imaginato_Catalog_Model_Urlkey
{
    private $categoryToStore;
    private $categoryToName;
    private $rootCount;

    private $diffCategory;
    private $repeatCategory;
    private $error_category;

    function __construct()
    {
        $storeRootCategory = array();
        $categoryToWebsite = array();
        $this->categoryToName = array();
        $this->categoryToStore = array();
        $this->error_category = array();
        $store_conn = Mage::getModel('core/store_group')->getCollection();
        foreach ($store_conn->getData() as $val) {
            if ($val['root_category_id']) {
                $storeRootCategory[] = $val['root_category_id'];
                $categoryToWebsite[$val['website_id']] = $val['root_category_id'];
                $this->categoryToName[$val['root_category_id']] = $val['name'];
            }
        }


        $all_store = Mage::app()->getStores();
        foreach($all_store as $val){
            $this->categoryToStore[$categoryToWebsite[$val->getWebsiteId()]][] = $val->getId();
        }

        $this->rootCount = count($all_store);

        $this->detail_diff($storeRootCategory);
        $this->detail_repeat($storeRootCategory);
    }

    function detail_diff($parentCategory)
    {
        $nameArray = array();
        foreach ($parentCategory as $value) {
            $categoryData = Mage::getModel('catalog/category')->load($value);
            $path = explode('/', $categoryData->getData('path'));
            $idToName = array();
            if(empty($path[1]) || empty($this->categoryToStore[$path[1]])){
                continue;
            }
            foreach($this->categoryToStore[$path[1]] as $store_id){
                $child_data = $categoryData->setStoreId($store_id)->getCollection();
                $child_data->addAttributeToSelect('name')->addAttributeToSelect('url_key');
                $child_data->addAttributeToFilter('parent_id',$value);
                $store = Mage::app()->getStore($store_id);
                $storeName = $store->getName();
                $website = Mage::app()->getWebsite($store->getWebsiteId());
                $websiteName = $website->getName();
                foreach ($child_data as $key => $val) {
                    $val->setData('website', $websiteName);
                    $val->setData('store', $storeName);
                    if(empty($idToName[$val->getId()])){
                        $idToName[$val->getId()] = $val->getData('name');
                    }
                    $nameArray[$idToName[$val->getId()]][] = $val;
                    $this->categoryToName[$val->getId()] = $val->getData('name');
                }
            }
        }
        foreach ($nameArray as $value) {
            if (count($value) == $this->rootCount) {
                $key_url = array();
                $category_ids = array();
                foreach ($value as $val) {
                    $key_url[] = $val->getData('url_key');
                    $category_ids[] = $val->getId();
                }
                if (count(array_unique($key_url)) != 1) {
                    foreach ($value as $val) {
                        $category_data = array();
                        $parentNameArray = array();
                        $category_data['category_id'] = $val->getId();
                        $category_data['name'] = $val->getData('name');
                        $parentArray = explode('/', $val->getData('path'));
                        for ($i = 1; $i < count($parentArray); $i++) {
                            $parentNameArray[] = $this->categoryToName[$parentArray[$i]];
                        }
                        $parentString = implode('>>', $parentNameArray);
                        $category_data['parent'] = $parentString;
                        $category_data['website'] = $val->getData('website');
                        $category_data['store'] = $val->getData('store');
                        $category_data['url_key'] = $val->getData('url_key');
                        $this->diffCategory[] = $category_data;
                    }
                }
                $this->detail_diff(array_unique($category_ids));
            }
        }
    }

    function detail_repeat($parentCategory){
        $urlArray = array();
        $category_ids = array();
        foreach ($parentCategory as $value) {
            $categoryData = Mage::getModel('catalog/category')->load($value);
            $path = explode('/', $categoryData->getData('path'));
            $idToName = array();
            if(empty($path[1]) || empty($this->categoryToStore[$path[1]])){
                $this->error_category[$value] = $categoryData;
                continue;
            }
            foreach($this->categoryToStore[$path[1]] as $store_id){
                $child_data = $categoryData->setStoreId($store_id)->getCollection();
                $child_data->addAttributeToSelect('name')->addAttributeToSelect('url_key');
                $child_data->addAttributeToFilter('parent_id',$value);
                $store = Mage::app()->getStore($store_id);
                $storeName = $store->getName();
                $website = Mage::app()->getWebsite($store->getWebsiteId());
                $websiteName = $website->getName();
                foreach ($child_data as $key => $val) {
                    $category_ids[] = $val->getId();
                    $val->setData('website', $websiteName);
                    $val->setData('store', $storeName);
                    if(empty($idToName[$val->getId()])){
                        $idToName[$val->getId()] = $val->getData('name');
                    }
                    $urlArray[$store_id][$val->getData('parent_id')][$val->getData('url_key')][] = $val;
                    $this->categoryToName[$val->getId()] = $val->getData('name');
                }
            }
        }
        if(empty($urlArray)){
            return;
        }
        foreach($urlArray as $array){
            foreach($array as $key=>$values){
                foreach($values as $value){
                    if(count($value)!=1){
                        foreach ($value as $val) {
                            $category_data = array();
                            $parentNameArray = array();
                            $category_data['category_id'] = $val->getId();
                            $category_data['name'] = $val->getData('name');
                            $parentArray = explode('/', $val->getData('path'));
                            for ($i = 1; $i < count($parentArray); $i++) {
                                $parentNameArray[] = $this->categoryToName[$parentArray[$i]];
                            }
                            $parentString = implode('>>', $parentNameArray);
                            $category_data['parent'] = $parentString;
                            $category_data['website'] = $val->getData('website');
                            $category_data['store'] = $val->getData('store');
                            $category_data['url_key'] = $val->getData('url_key');
                            $this->repeatCategory[] = $category_data;
                        }
                    }
                }
            }
        }
        $this->detail_repeat(array_unique($category_ids));
    }

    public function getDiffCategory()
    {
        return $this->diffCategory;
    }

    public function getRepeatCategory()
    {
        return $this->repeatCategory;
    }


    public function getErrorCategory()
    {
        $errorCategory = array();
        foreach($this->error_category as $val){
            $category_data = array();
            $category_data['category_id'] = $val->getId();
            $category_data['name'] = $val->getData('name');
            $category_data['parent'] = $this->categoryToName[$val->getData('parent_id')];
            $category_data['path'] = $this->getParentName($val->getData('parent_id'));
            $errorCategory[] = $category_data;
        }
        return $errorCategory;
    }
}
