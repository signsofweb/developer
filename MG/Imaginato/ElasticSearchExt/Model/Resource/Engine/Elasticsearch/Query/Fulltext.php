<?php

class Imaginato_ElasticSearchExt_Model_Resource_Engine_Elasticsearch_Query_Fulltext
    extends Smile_ElasticSearch_Model_Resource_Engine_Elasticsearch_Query_Fulltext
{

    /**
     * Run the query against ElasticSearch.
     *
     * @return array
     */
    public function search()
    {
        $result = array();

        Varien_Profiler::start('ES:ASSEMBLE:QUERY');
        $query = $this->_assembleQuery();
        Varien_Profiler::stop('ES:ASSEMBLE:QUERY');

        $eventData = new Varien_Object(array('query' => $query, 'query_type' => $this->getQueryType()));

        Varien_Profiler::start('ES:ASSEMBLE:QUERY:OBSERVERS');
        Mage::dispatchEvent('smile_elasticsearch_query_assembled', array('query_data' => $eventData));
        Varien_Profiler::stop('ES:ASSEMBLE:QUERY:OBSERVERS');

        $query = $eventData->getQuery();
        if ($this->getConfig('enable_debug_mode')) {
            Mage::log(json_encode($query), Zend_Log::DEBUG, 'es-queries.log');
        }

        Varien_Profiler::start('ES:EXECUTE:QUERY');
        $response = $this->getClient()->search($query);
        Varien_Profiler::stop('ES:EXECUTE:QUERY');

        if (!isset($response['error'])) {
            $result = array(
                'total_count'  => $response['hits']['total'],
                'faceted_data' => array(),
                'docs'         => array(),
                'ids'          => array()
            );

            foreach ($response['hits']['hits'] as $doc) {
                $id = (int) current($doc['fields']['entity_id']);
                $result['docs'][$id] = $doc['fields'];
                $result['ids'][] = $id;
            }

            if (isset($response['facets'])) {
                foreach ($this->_facets as $facetName => $facetModel) {
                    $currentFacet = clone $facetModel;
                    if ($facetModel->isGroup()) {
                        $currentFacet->setResponse($response['facets']);
                        $result['faceted_data'][$facetName] = $facetModel->getItems($response['facets']);
                    } else if (isset($response['facets'][$facetName])) {
                        $currentFacet->setResponse($response['facets'][$facetName]);
                        $result['faceted_data'][$facetName] = $facetModel->getItems($response['facets'][$facetName]);
                    }
                    $result['facets'][$facetName] = $currentFacet;
                }
            }
        } else {
            Mage::log($response['error'], Zend_Log::ERR, 'search_errors.log');
        }

        return $result;
    }

    /**
     * Transform the query into an ES syntax compliant array.
     *
     * @return array
     */
    protected function _assembleQuery()
    {
        $query = array('index' => $this->getAdapter()->getCurrentIndex()->getCurrentName(), 'type' => $this->getType());
        $query['body']['query']['filtered']['query']['bool']['must'][] = $this->_prepareFulltextCondition();

        foreach ($this->_facets as $facetName => $facet) {

            $facets = $facet->getFacetQuery();

            if (!$facet->isGroup()) {
                $facets = array($facetName => $facets);
            }

            foreach ($facets as $realFacetName => $facet) {
                foreach ($this->_filters as $filterFacetName => $filters) {
                    $rawFilter = array();

                    foreach ($filters as $filter) {
                        $rawFilter[] = $filter->getFilterQuery();
                    }

                    if ($filterFacetName != $facetName && $filterFacetName != '_none_') {
                        $mustConditions = $rawFilter;
                        if (isset($facet['facet_filter']['bool']['must'])) {
                            $mustConditions = array_merge($facet['facet_filter']['bool']['must'], $rawFilter);
                        }
                        $facet['facet_filter']['bool']['must'] = $mustConditions;
                    }
                }
                $query['body']['facets'][$realFacetName] = $facet;
            }
        }

        foreach ($this->_filters as $facetName => $filters) {
            $rawFilter = array();
            foreach ($filters as $filter) {
                $rawFilter[] = $filter->getFilterQuery();
            }
            if ($facetName == '_none_') {
                if (!isset($query['body']['query']['filtered']['filter']['bool']['must'])) {
                    $query['body']['query']['filtered']['filter']['bool']['must'] = array();
                    $query['body']['query']['filtered']['filter']['bool']['_cache'] = true;
                }
                $mustConditions = array_merge($query['body']['query']['filtered']['filter']['bool']['must'], $rawFilter);
                $query['body']['query']['filtered']['filter']['bool']['must'] = $mustConditions;
            } else {
                if (!isset($query['body']['filter']['bool']['must'])) {
                    $query['body']['filter']['bool']['must'] = array();
                }
                $query['body']['filter']['bool']['must'] = array_merge($query['body']['filter']['bool']['must'], $rawFilter);
            }
        }
        // Patch : score not computed when using another sort order than score
        //         as primary sort order
        if (isset($this->_page['size']) && $this->_page['size'] > 0) {
            $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
            $websiteId = Mage::app()->getStore()->getWebsiteId();

            $options = array();
            $option_fields = array();
            $option_fields_label = array();
            if(!Mage::registry('option_fields')){
                $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
                    ->addFilter('entity_type_id',4)
                    ->addFilter('is_configurable',1)
                    ->addFilter('frontend_input','select')
                    ->addFilter('is_global',1);
                foreach($attributes as $attribute){
                    $options[] = $attribute->getAttributeCode();
                    $option_fields[$attribute->getId()] = array(
                        'id' => $attribute->getId(),
                        'code' => $attribute->getAttributeCode(),
                        'label' => $attribute->getFrontendLabel()
                    );
                    $option_fields_label[] = 'options_' . $attribute->getAttributeCode() . '_' . $this->getLanguageCode();
                }
                Mage::register('option_fields',$option_fields);
            }

            $query['body']['fields'] = array(
                'entity_id',
                'request_path_' . $this->getLanguageCode(),
                'price_' . $customerGroupId . '_' . $websiteId,
                'price_org_' . $customerGroupId . '_' . $websiteId,
                'children_ids',
                'supper_attribute_ids',
                'in_stock',
                'qty',
                'thumbnail',
                'small_image',
                'alt_small_image',
                'sku'
                );
            $query['body']['fields'] = array_merge($query['body']['fields'],$options,$option_fields_label);
            $query['body']['track_scores'] = true;
            $query['body']['sort'] = $this->_prepareSortCondition();
            $query['body'] = array_merge($query['body'], $this->_page);
        } else {
            $query['body'] = array_merge($query['body'], $this->_page);
        }

        return $query;
    }
}