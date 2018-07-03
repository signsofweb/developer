<?php

class Imaginato_ElasticSearchExt_Model_Resource_Engine_Index extends Smile_ElasticSearch_Model_Resource_Engine_Index
{
    /**
     * Retrieves product price data for advanced index.
     *
     * @param array $productIds Product ids to reindex
     *
     * @return array
     */
    protected function _getCatalogProductPriceData($productIds = null)
    {
        $result = array();

        if (!empty($productIds)) {
            $adapter = $this->_getWriteAdapter();

            $select = $adapter->select()
                ->from(
                    $this->getTable('catalog/product_index_price'),
                    array(
                        'entity_id',
                        'customer_group_id',
                        'website_id',
                        'min_price',
                        'price',
                        'has_discount' => new Zend_Db_Expr('COALESCE((price - min_price) > 0, 0)')
                    )
                );

            if ($productIds) {
                $select->where('entity_id IN (?)', $productIds);
            }

            foreach ($adapter->fetchAll($select) as $row) {
                $productId = (int) $row['entity_id'];

                $priceKey = sprintf('price_%s_%s', $row['customer_group_id'], $row['website_id']);
                $priceOrgKey = sprintf('price_org_%s_%s', $row['customer_group_id'], $row['website_id']);
                $result[$productId][$priceKey] = round($row['min_price'], 2);
                $result[$productId][$priceOrgKey] = round($row['price'], 2);

                $discountKey = sprintf('has_discount_%s_%s', $row['customer_group_id'], $row['website_id']);
                $result[$productId][$discountKey] = (bool) $row['has_discount'];
            }
        }

        return $result;
    }

    public function addAdvancedIndexForChild($index, $storeId, $productIds = null){
        if (is_null($productIds) || !is_array($productIds)) {
            $productIds = array();
            foreach ($index as $entityData) {
                $productIds[] = $entityData['entity_id'];
            }
            $index = array_combine($productIds, $index);
        }

        if (count($productIds)) {
            $priceData = $this->_getCatalogProductPriceData($productIds);
            foreach ($index as $productId => &$productData) {
                if (isset($priceData[$productId])) {
                    $productData += $priceData[$productId];
                } else {
                    unset($index[$productId]);
                }
            }
        }
        return $index;
    }
}
