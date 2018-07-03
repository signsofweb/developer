<?php

class Imaginato_ImportExport_Model_Import_Observer
{
    protected  $adapter = null;

    public function getSimples($parentKeys,$type = 'sku'){
        $resource = Mage::getSingleton('core/resource');
        $write = $resource->getConnection('core_write');
        $select = $write->select()
            ->from(
                array('t1' => 'catalog_product_super_link'),
                array('simple_id' => 't1.product_id')
            )
            ->joinLeft(
                array('t2' => 'catalog_product_entity'),
                't1.parent_id = t2.entity_id',
                array()
            );
        if($type == 'sku'){
            $select->where('t2.sku IN(?)', $parentKeys);
        }else{
            $select->where('t2.entity_id IN(?)', $parentKeys);
        }
        $collection = $write->fetchAll($select);
        $returnData = array();
        foreach($collection as $simple){
            $returnData[] = $simple['simple_id'];
        }
        return $returnData;
    }
    public function checkSimpleImage($simpleIds){
        $eavConfig = Mage::getSingleton('eav/config');
        $attribute_id = $eavConfig->getAttribute('catalog_product', 'image')->getId();
        $resource = Mage::getSingleton('core/resource');
        $write = $resource->getConnection('core_write');
        $select = $write->select()
            ->from(
                array('t1' => 'catalog_product_entity'),
                array('simple_id' => 't1.entity_id',
                    'sku' => 't1.sku'
                    ))
            ->joinLeft(
                array('t2' => 'catalog_product_entity_varchar'),
                "t1.entity_id = t2.entity_id AND t2.attribute_id = '{$attribute_id}'",
                array('value' => 'ANY_VALUE(t2.value)')
            )
            ->where('t1.entity_id IN(?)', $simpleIds)
            ->group('t1.entity_id');
        $collection = $write->fetchAll($select);
        $skus = array();
        foreach($collection as $simple){
            if(empty($simple['value']) || $simple['value'] == 'no_selection'){
                $skus[] = $simple['sku'];
            }
        }
        return $skus;
    }
}
