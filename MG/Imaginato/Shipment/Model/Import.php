<?php

/**
 * Class Imaginato_Shipment_Model_Import
 */
class Imaginato_Shipment_Model_Import extends Mage_ImportExport_Model_Import
{
    /**
     * Key in config with entities.
     */
    const CONFIG_KEY_ENTITIES = 'global/importexport/import_entities';

    /**
     * Import behavior.
     */
    const BEHAVIOR_APPEND = 'append';

    const BEHAVIOR_REPLACE = 'replace';

    const BEHAVIOR_DELETE = 'delete';

    /**
     * Form field names (and IDs)
     */
    const FIELD_NAME_SOURCE_FILE = 'import_file';

    const FIELD_NAME_IMG_ARCHIVE_FILE = 'import_image_archive';

    /**
     * Import State
     */
    const STATE_SHIPPED         = 'imaginato_shipped';
    const STATE_PARTIAL_SHIPPED = 'imaginato_partial_shipped';

    protected $_debugMode = false;

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Move uploaded file and create source adapter instance.
     *
     * @throws Mage_Core_Exception
     * @return string Source file path
     */
    public function uploadSource()
    {
        $entity = $this->getEntity();
        /** @var Mage_Core_Model_File_Uploader $uploader */
        $uploader = Mage::getModel('core/file_uploader', self::FIELD_NAME_SOURCE_FILE);
        $uploader->skipDbProcessing(true);
        $result = $uploader->save(self::getWorkingDir());
        $extension = pathinfo($result['file'], PATHINFO_EXTENSION);

        $uploadedFile = $result['path'] . $result['file'];
        if (!$extension) {
            unlink($uploadedFile);
            Mage::throwException($this->_getHelper()->__('Uploaded file has no extension'));
        }
        $sourceFile = self::getWorkingDir() . $entity;

        $sourceFile .= '.' . $extension;

        if (strtolower($uploadedFile) != strtolower($sourceFile)) {
            if (file_exists($sourceFile)) {
                unlink($sourceFile);
            }

            if (!@rename($uploadedFile, $sourceFile)) {
                Mage::throwException($this->_getHelper()->__('Source file moving failed'));
            }
        }
        // trying to create source adapter for file and catch possible exception to be convinced in its adequacy
        try {
            $this->_getSourceAdapter($sourceFile);
        } catch (Exception $e) {
            unlink($sourceFile);
            Mage::throwException($e->getMessage());
        }
        return $sourceFile;
    }

    /**
     * Override standard entity getter.
     *
     * @throw Mage_Core_Exception
     * @return string
     */
    public function getEntity()
    {
        if (empty($this->_data['entity'])) {
            $this->_data['entity'] = $this->_getHelper()->getEntity();
        }
        return $this->_data['entity'];
    }

    /**
     * @return Imaginato_Shipment_Helper_Data|Mage_Core_Helper_Abstract
     */
    protected function _getHelper()
    {
        return Mage::helper('imaginato_shipment');
    }

    /**
     * Import/Export working directory (source files, result files, lock files etc.).
     *
     * @return string
     */
    public static function getWorkingDir()
    {
        return Mage::getBaseDir('var') . DS . 'importexport' . DS;
    }

    /**
     * Returns source adapter object.
     *
     * @param string $sourceFile Full path to source file
     * @return Mage_ImportExport_Model_Import_Adapter_Abstract
     */
    protected function _getSourceAdapter($sourceFile)
    {
        return Mage_ImportExport_Model_Import_Adapter::findAdapterFor($sourceFile);
    }

    protected function _getEntityAdapter()
    {
        if (!$this->_entityAdapter) {
            try {
                $this->_entityAdapter = Mage::getModel($this->_getHelper()->getEntityAdapter());
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::throwException(
                    Mage::helper('importexport')->__('Invalid entity model')
                );
            }
            if (!($this->_entityAdapter instanceof Mage_ImportExport_Model_Import_Entity_Abstract)) {
                Mage::throwException(
                    Mage::helper('importexport')->__('Entity adapter object must be an instance of Mage_ImportExport_Model_Import_Entity_Abstract')
                );
            }

            // check for entity codes integrity
            if ($this->getEntity() != $this->_entityAdapter->getEntityTypeCode()) {
                Mage::throwException(
                    Mage::helper('importexport')->__('Input entity code is not equal to entity adapter code')
                );
            }
            $this->_entityAdapter->setParameters($this->getData());
        }
        return $this->_entityAdapter;
    }
}