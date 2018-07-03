<?php

/**
 * Class Imaginato_Size_Block_Adminhtml_Size_Edit_Tab_Main
 */
class Imaginato_Size_Block_Adminhtml_Size_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Prepare content for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('size')->__('Chart Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('size')->__('Chart Information');
    }

    /**
     * Returns status flag about this tab can be showed or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $model = Mage::registry('size_block');
        $form = new Varien_Data_Form(
            array('id'     => 'edit_form',
                  'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                  'method' => 'post'
            )
        );

        $form->setHtmlIdPrefix('block_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => Mage::helper('size')->__('Chart Information'), 'class' => 'fieldset-wide'));

        if ($model->getBlockId()) {
            $fieldset->addField('block_id', 'hidden', array(
                'name' => 'block_id',
            ));
        }

        $fieldset->addField('title', 'text', array(
            'name'     => 'title',
            'label'    => Mage::helper('size')->__('Chart Title'),
            'title'    => Mage::helper('size')->__('Chart Title'),
            'required' => true,
        ));

        $fieldset->addField('identifier', 'text', array(
            'name'     => 'identifier',
            'label'    => Mage::helper('size')->__('Identifier'),
            'title'    => Mage::helper('size')->__('Identifier'),
            'required' => true,
            'class'    => 'validate-xml-identifier',
        ));

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $field = $fieldset->addField('store_id', 'multiselect', array(
                'name'     => 'stores[]',
                'label'    => Mage::helper('size')->__('Store View'),
                'title'    => Mage::helper('size')->__('Store View'),
                'required' => true,
                'values'   => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            ));
            $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField('store_id', 'hidden', array(
                'name'  => 'stores[]',
                'value' => Mage::app()->getStore(true)->getId()
            ));
            $model->setStoreId(Mage::app()->getStore(true)->getId());
        }

        $fieldset->addField('is_active', 'select', array(
            'label'    => Mage::helper('size')->__('Status'),
            'title'    => Mage::helper('size')->__('Status'),
            'name'     => 'is_active',
            'required' => true,
            'options'  => array(
                '1' => Mage::helper('size')->__('Enabled'),
                '0' => Mage::helper('size')->__('Disabled'),
            ),
        ));
        if (!$model->getId()) {
            $model->setData('is_active', '1');
        }

        $fieldset->addField('content', 'editor', array(
            'name'     => 'content',
            'label'    => Mage::helper('size')->__('Content'),
            'title'    => Mage::helper('size')->__('Content'),
            'style'    => 'height:36em',
            'required' => true,
            'config'   => Mage::getSingleton('cms/wysiwyg_config')->getConfig()
        ));

        $form->setValues($model->getData());

        if ($model->isReadonly()) {
            foreach ($fieldset->getElements() as $element) {
                $element->setReadonly(true, true);
            }
        }

        $this->setForm($form);

        Mage::dispatchEvent('adminhtml_size_edit_tab_main_prepare_form', array('form' => $form));

        return parent::_prepareForm();
    }
}
