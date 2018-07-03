<?php
class Imaginato_Contacts_Block_Index extends Mage_Core_Block_Template
{
    protected function getEnqueries()
    {
        $enqueryModel = Mage::getModel('imaginato_contacts/enqueries')->getCollection();

        $enqueries = $enqueryModel->addEnqueryStoreFilter();

        $returnval = array();

        foreach($enqueries as $enquery)
        {
            if(!isset($returnval[$enquery->getEnquerytypeId()]['enquerytype']))
            {
                $returnval[$enquery->getEnquerytypeId()]['enquerytype'] = array(
                    'title' => $enquery->getEnquerytypeTitle(), 
                    'short_order' => $enquery->getEnquerytypeShortOrder(),
                );
            }
            $returnval[$enquery->getEnquerytypeId()]['enqueries'][] = array(
                'id' => $enquery->getId(),
                'title' => $enquery->getTitle(),
                'short_order' => $enquery->getShortOrder(),
                'email' => $enquery->getEmail(),
            );
        }
        return $returnval;
    }
}