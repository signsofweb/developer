<?php

class Imaginato_Catalog_Adminhtml_Product_UrlkeyController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/url_key');
    }

    protected function layoutDetail(){
        $this->_title($this->__('Catalog'))->_title($this->__('Url Key Deal'));

        $this->loadLayout();
        $this->_setActiveMenu('catalog/url_key');
        $this->_addContent($this->getLayout()->createBlock('imaginato_catalog/adminhtml_product_urlkey'));
        $this->renderLayout();
    }

    public function indexAction()
    {
        $this->layoutDetail();
    }

    public function clearProductAction(){
        $resource_model = Mage::getSingleton('core/resource');
        $store_table = $resource_model->getTableName('core/store');
        $website_table = $resource_model->getTableName('core/website');
        $entity_table = $resource_model->getTableName('catalog/product');
        $url_key_table = $resource_model->getTableName(array('catalog/product', 'url_key'));

        $writer = $resource_model->getConnection('core_write');
        $sql = "delete
                    store_data
                from
                    {$url_key_table} AS store_data
                        left join
                    {$url_key_table} as default_data ON default_data.entity_id = store_data.entity_id
                where
                    default_data.store_id = 0
                        and store_data.store_id <> 0
                        and default_data.value <> store_data.value";
        $writer->query($sql);
        $this->_getSession()->addSuccess(
            $this->__('The key url for product has clear up.')
        );

        $read = $resource_model->getConnection('core_read');
        $sql = "select
                    entity_table.value_id,product.sku as name,website.name as website_name,store.name as store_name,entity_table.value as key_url
                from
                    {$url_key_table} as entity_table
                        left join
                    {$entity_table} as product on entity_table.entity_id = product.entity_id
                        left join
                    {$store_table} as store on entity_table.store_id = store.store_id
                        left join
                    {$website_table} as website on store.website_id = website.website_id
                where
                    entity_table.entity_id not in (select
                            entity_id
                        from
                            {$url_key_table}
                        where
                            store_id = 0)";
        $key_data = $read->fetchAll($sql);
        Mage::register('no_default_data', $key_data);

        $this->layoutDetail();
    }

    public function clearCategoryAction(){
        $resource_model = Mage::getSingleton('core/resource');
        $store_table = $resource_model->getTableName('core/store');
        $website_table = $resource_model->getTableName('core/website');

        $name_attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_category', 'name');
        $name_table = $name_attribute->getBackendTable();
        $name_attribute_id = $name_attribute->getAttributeId();

        $url_key_table = $resource_model->getTableName(array('catalog/category', 'url_key'));

        $writer = $resource_model->getConnection('core_write');
        $sql = "delete
                    store_data
                from
                    {$url_key_table} AS store_data
                        left join
                    {$url_key_table} as default_data ON default_data.entity_id = store_data.entity_id
                where
                    default_data.store_id = 0
                        and store_data.store_id <> 0
                        and default_data.value <> store_data.value";
        $writer->query($sql);
        $this->_getSession()->addSuccess(
            $this->__('The key url for category has clear up.')
        );

        $read = $resource_model->getConnection('core_read');
        $sql = "select
                    entity_table.value_id,category.value as name,website.name as website_name,store.name as store_name,entity_table.value as key_url
                from
                    {$url_key_table} as entity_table
                        left join
                    {$name_table} as category on entity_table.entity_id = category.entity_id and category.attribute_id = {$name_attribute_id} and category.store_id = 0
                        left join
                    {$store_table} as store on entity_table.store_id = store.store_id
                        left join
                    {$website_table} as website on store.website_id = website.website_id
                where
                    entity_table.entity_id not in (select
                            entity_id
                        from
                            {$url_key_table}
                        where
                            store_id = 0)";
        $key_data = $read->fetchAll($sql);
        Mage::register('no_default_data', $key_data);


        $urlkey_model = Mage::getModel('imaginato_catalog/urlkey');
        Mage::register('diff_url_key_data', $urlkey_model->getDiffCategory());
        Mage::register('repeat_url_key_data', $urlkey_model->getRepeatCategory());

        $this->layoutDetail();
    }
}
