<?php

class Imaginato_SocialLogin_Model_Resource_Setup extends Mage_Eav_Model_Entity_Setup
{
    /**
     * Prepare customer attribute values to save in additional table
     *
     * @param array $attr
     * @return array
     */
    protected function _prepareValues($attr)
    {
        $data = parent::_prepareValues($attr);
        $data = array_merge($data, array(
            'is_visible'                => $this->_getValue($attr, 'visible', 1),
            'is_system'                 => $this->_getValue($attr, 'system', 1),
            'input_filter'              => $this->_getValue($attr, 'input_filter', null),
            'multiline_count'           => $this->_getValue($attr, 'multiline_count', 0),
            'validate_rules'            => $this->_getValue($attr, 'validate_rules', null),
            'data_model'                => $this->_getValue($attr, 'data', null),
            'sort_order'                => $this->_getValue($attr, 'position', 0)
        ));

        return $data;
    }
}
