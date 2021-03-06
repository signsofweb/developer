<?php

class Imaginato_Contacts_Model_Resource_Enquerytype extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('imaginato_contacts/enquerytype', 'entity_id');
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $oldStores = $this->lookupStoreIds($object->getId());
        $newStores = (array)$object->getStores();

        $table  = $this->getTable('imaginato_contacts/enquerytypestoreview');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);

        if ($delete) {
            $where = array(
                'enquery_type_id = ?'     => (int) $object->getId(),
                'store_id IN (?)' => $delete
            );

            $this->_getWriteAdapter()->delete($table, $where);
        }

        if ($insert) {
            $data = array();

            foreach ($insert as $storeId) {
                $data[] = array(
                    'enquery_type_id'  => (int) $object->getId(),
                    'store_id' => (int) $storeId
                );
            }

            $this->_getWriteAdapter()->insertMultiple($table, $data);
        }

        return parent::_afterSave($object);

    }

    public function lookupStoreIds($id)
    {
        $adapter = $this->_getReadAdapter();

        $select  = $adapter->select()
            ->from($this->getTable('imaginato_contacts/enquerytypestoreview'), 'store_id')
            ->where('enquery_type_id = :enquery_type_id');

        $binds = array(
            ':enquery_type_id' => (int) $id
        );

        return $adapter->fetchCol($select, $binds);
    }
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());
            $object->setData('store_id', $stores);
            $object->setData('stores', $stores);
        }

        return parent::_afterLoad($object);
    }

    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $stores = array(
                (int) $object->getStoreId(),
                Mage_Core_Model_App::ADMIN_STORE_ID,
            );

            $select->join(
                array('enquerytype_store' => $this->getTable('imaginato_contacts/enquerytypestoreview')),
                $this->getMainTable().'.entity_id = enquerytype_store.enquery_type_id',
                array('store_id')
            )
            ->where('enquerytype_store.store_id in (?) ', $stores)
            ->order('store_id DESC')
            ->limit(1);
        }

        return $select;
    }
}