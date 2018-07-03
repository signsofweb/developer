<?php


class Imaginato_Reward_Block_Reward_Renderer_Action
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Array to store all options data
     *
     * @var array
     */
    protected $_actions = array();

    public function render(Varien_Object $row)
    {
        $this->_actions = array();
        if ($row->getCustomerId()) {
            $reorderAction = array(
                '@' => array('href' => $this->getUrl('*/customer/edit', array('id'=>$row->getCustomerId(),'active_tab'=>'customer_edit_tab_reward'))),
                '#' => Mage::helper('imaginato_reward')->__('Edit Customer')
            );
            $this->addToActions($reorderAction);
        }
        Mage::dispatchEvent('adminhtml_reward_history_action_renderer', array('renderer' => $this, 'row' => $row));
        return $this->_actionsToHtml();
    }

    protected function _getEscapedValue($value)
    {
        return addcslashes(htmlspecialchars($value),'\\\'');
    }

    /**
     * Render options array as a HTML string
     *
     * @param array $actions
     * @return string
     */
    protected function _actionsToHtml(array $actions = array())
    {
        $html = array();
        $attributesObject = new Varien_Object();

        if (empty($actions)) {
            $actions = $this->_actions;
        }

        foreach ($actions as $action) {
            $attributesObject->setData($action['@']);
            $html[] = '<a ' . $attributesObject->serialize() . '>' . $action['#'] . '</a>';
        }
        return  implode($html, '<span class="separator">|</span>');
    }

    /**
     * Add one action array to all options data storage
     *
     * @param array $actionArray
     * @return void
     */
    public function addToActions($actionArray)
    {
        $this->_actions[] = $actionArray;
    }
}
