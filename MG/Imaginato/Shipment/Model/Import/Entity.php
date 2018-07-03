<?php

/**
 * Class Imaginato_Shipment_Model_Import_Entity
 *
 * @see Imaginato_ImportExport_Model_Import_Entity_Product
 */
class Imaginato_Shipment_Model_Import_Entity extends Mage_ImportExport_Model_Import_Entity_Abstract
{
    /**
     * Error codes.
     */
    const ERROR_DUPLICATE_ID            = 'duplicateOrderId';
    const ERROR_ID_NOT_FOUND_FOR_DELETE = 'idNotFoundToDelete';
    const ERROR_ID_NOT_FOUND_FOR_APPEND = 'idNotFoundToAppend';
    const ERROR_INVALID_SCOPE           = 'invalidScope';
    const ERROR_INVALID_WEBSITE         = 'invalidWebsite';
    const ERROR_INVALID_STORE           = 'invalidStore';
    const ERROR_CANNOT_SHIP             = 'cannotShip';
    const ERROR_INVALID_CARRIER         = 'invalidCarrier';
    const ERROR_VALUE_IS_REQUIRED       = 'isRequired';
    const ERROR_TYPE_CHANGED            = 'typeChanged';
    const ERROR_ID_IS_EMPTY             = 'idEmpty';
    const ERROR_NO_DEFAULT_ROW          = 'noDefaultRow';
    const ERROR_DUPLICATE_SCOPE         = 'duplicateScope';
    const ERROR_ROW_IS_ORPHAN           = 'rowIsOrphan';
    const ERROR_TYPE_UNSUPPORTED        = 'entityTypeUnsupported';
    const ERROR_QTY_ABOVE_ORDER         = 'orderQtybelowQtydata';
    const ERROR_QTY_NOT_AVAILABLE       = 'qtyNotAvailable';
    const ERROR_QTY_REQUIRED            = 'qtyIsRequired';
    const ERROR_SKU_REQUIRED            = 'skuIsRequired';
    const ERROR_VIRTUAL_ITEM            = 'isVirtualItem';
    const ERROR_SKU_INFO                = 'invalidSku';
    const ERROR_TRACKING_EXISTS         = 'trackingExists';
    const ERROR_TRACKING_EXISTS_TRACK   = 'trackingExistsTrack';
    const ERROR_TRACKING_EXISTS_SHIP    = 'trackingExistsShip';
    const NOTICE_SHIPMENT_NEW_SHIPMENT  = 'noteShipmentExistsNewShipment';
    const NOTICE_NEW_SHIPMENT           = 'noteNewShipment';
    const NOTICE_SHIPMENT_NO_TRACKS     = 'noteShipmentExistsNoTracks';

    /**
     * Data row scopes.
     */
    const SCOPE_DEFAULT = 1;
    const SCOPE_WEBSITE = 2;
    const SCOPE_STORE   = 0;
    const SCOPE_NULL    = -1;
    /**
     * Permanent column names.
     *
     * Names that begins with underscore is not an attribute. This name convention is for
     * to avoid interference with same attribute name.
     */
    const COL_INCREMENT = 'increment_id';
    const COL_STORE     = '_store';
    const COL_ID        = 'Order Number';
    const COL_CARRIER   = 'carrier';
    const COL_TITLE     = 'title';
    const COL_NUMBER    = 'number';
    const COL_SKU       = 'sku';
    const COL_QTY       = 'qty';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::ERROR_INVALID_SCOPE           => 'Invalid value in Scope column',
        self::ERROR_INVALID_WEBSITE         => 'Invalid value in Website column (website does not exists?)',
        self::ERROR_INVALID_STORE           => 'Invalid value in Store column (store does not exists?)',
        self::ERROR_CANNOT_SHIP             => "Unable to create new shipment, order is fully shipped or no more available items for shipping.",
        self::ERROR_INVALID_CARRIER         => "Carrier Code is invalid '%s'",
        self::ERROR_VALUE_IS_REQUIRED       => "Required attribute '%s' has an empty value",
        self::ERROR_ID_IS_EMPTY             => 'Order Number is invalid',
        self::ERROR_TYPE_CHANGED            => 'Trying to change type of existing products',
        self::ERROR_NO_DEFAULT_ROW          => 'Default values row does not exists',
        self::ERROR_DUPLICATE_SCOPE         => 'Duplicate scope',
        self::ERROR_DUPLICATE_ID            => 'Duplicate Order Number',
        self::ERROR_ROW_IS_ORPHAN           => 'Orphan rows that will be skipped due to default row errors',
        self::ERROR_ID_NOT_FOUND_FOR_DELETE => 'Order with specified Order Number not found',
        self::ERROR_ID_NOT_FOUND_FOR_APPEND => "Order with specified Order Number not found '%s'",
        self::ERROR_TYPE_UNSUPPORTED        => 'Order ID is not supported',
        self::ERROR_QTY_ABOVE_ORDER         => 'Quantity data is more than quantity ordered',
        self::ERROR_QTY_NOT_AVAILABLE       => 'There are no available quantity for shipment',
        self::ERROR_QTY_REQUIRED            => 'Found SKU number but no QTY defined.',
        self::ERROR_SKU_REQUIRED            => 'Shipment already exists, but no SKU defined in csv. Please add SKU number.',
        self::ERROR_VIRTUAL_ITEM            => 'Virtual items cannot be shipped.',
        self::ERROR_SKU_INFO                => 'Invalid SKU number in csv.',
        self::ERROR_TRACKING_EXISTS         => 'Order Shipment already exist with the same Tracking information. Skipped.',
        self::ERROR_TRACKING_EXISTS_TRACK   => 'Cannot create new shipment, the same Tracking information exists in csv with different shipment. Skipped.',
        self::ERROR_TRACKING_EXISTS_SHIP    => 'Cannot append tracking info, the same Tracking information exists in csv with different shipment. Skipped.',
        self::NOTICE_SHIPMENT_NEW_SHIPMENT  => 'Shipment for this SKU already exist with different Tracking Info. New Shipment will be made.',
        self::NOTICE_NEW_SHIPMENT           => 'New Shipment will be made for data',
        self::NOTICE_SHIPMENT_NO_TRACKS     => 'Order Shipment already exist without Tracking Info. Tracking Info will be added from data',
    );

    /**
     * Permanent entity columns.
     *
     * @var array
     */
    protected $_permanentAttributes = array(self::COL_ID, self::COL_CARRIER, self::COL_TITLE, self::COL_NUMBER);

    /**
     * Column names that holds values with particular meaning.
     *
     * @var array
     */
    protected $_particularAttributes = array('_store', self::COL_ID);

    protected $_requiredAttributes = array(self::COL_ID, self::COL_CARRIER, self::COL_NUMBER);

    protected $_ignoredAttributes = array(self::COL_SKU, self::COL_QTY);

    /**
     * @var array
     */
    protected $_newId;

    /**
     * @var array
     */
    protected $_oldId;

    /**
     * All stores code-ID pairs.
     *
     * @var array
     */
    protected $_storeCodeToId = array();

    /**
     * Store ID to its website stores IDs.
     *
     * @var array
     */
    protected $_storeIdToWebsiteStoreIds = array();

    /**
     * Website code-to-ID
     *
     * @var array
     */
    protected $_websiteCodeToId = array();

    /**
     * Website code to store code-to-ID pairs which it consists.
     *
     * @var array
     */
    protected $_websiteCodeToStoreIds = array();

    /**
     * Contains available Carrier Code
     *
     * @var array
     */
    protected $_carrierCodeToId = array();

    /**
     * @var bool
     */
    protected $_importAllowed = true;

    /**
     * Notice messages.
     *
     * @var array
     */
    protected $_notices = array();

    /**
     * Contains rowData which has Shipments
     *
     * @var array
     */
    protected $_orderHasShipments = array();

    /**
     * @var Mage_Sales_Model_Order
     */
    protected $_currentOrder;

    /**
     * @var array
     */
    protected $_saveShipments = array();

    /**
     * @var array
     */
    protected $_saveTracks = array();

    /**
     * @var array
     */
    protected $_shipmentNoTrack = array();

    /**
     * @var array
     */
    protected $_skuInShipment = array();

    /**
     * @var bool
     */
    protected $_canAppendTrack = true;

    /**
     * @var array
     */
    protected $_noticeForRow = array();

    /**
     * Constructor.
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->_initWebsites()
            ->_initStores()
            ->_initOrderIds()
            ->_initCarriers();

        $this->_currentOrder = Mage::getModel('sales/order');
    }

    /**
     * Get all available Carrier Code
     */
    protected function _initCarriers()
    {
        $this->_carrierCodeToId = $this->getHelper()->getAvailableCarriers();
        return;
    }

    /**
     * @return Imaginato_Shipment_Helper_Data|Mage_Core_Helper_Abstract
     */
    protected function getHelper()
    {
        return Mage::helper('imaginato_shipment');
    }

    /**
     * Initialize existent product OrderIds.
     *
     * @return Imaginato_Shipment_Model_Import_Entity
     */
    protected function _initOrderIds()
    {
        $columns = array(self::COL_INCREMENT, 'state', 'status', 'entity_id', 'store_id', 'is_virtual');

        $entities = $this->getHelperImport()->getOrderEntities($columns);
        foreach ($entities as $info) {
            $orderId = $info[self::COL_INCREMENT];
            foreach ($columns as $column) {
                $this->_oldId[$orderId][$column] = $info[$column];
            }
        }
        return $this;
    }

    /**
     * @return Imaginato_Shipment_Helper_Import|Mage_Core_Helper_Abstract
     */
    protected function getHelperImport()
    {
        return Mage::helper('imaginato_shipment/import');
    }

    /**
     * Initialize stores hash.
     *
     * @return Imaginato_Shipment_Model_Import_Entity
     */
    protected function _initStores()
    {
        /** @var Mage_Core_Model_Store $store */
        $stores = Mage::app()->getStores(true);
        foreach ($stores as $store) {
            $this->_storeCodeToId[$store->getCode()] = $store->getId();
            $this->_storeIdToWebsiteStoreIds[$store->getId()] = $store->getWebsite()->getStoreIds();
        }
        return $this;
    }

    /**
     * Initialize website values.
     *
     * @return Imaginato_Shipment_Model_Import_Entity
     */
    protected function _initWebsites()
    {
        /** @var $website Mage_Core_Model_Website */
        foreach (Mage::app()->getWebsites(true) as $website) {
            $this->_websiteCodeToId[$website->getCode()] = $website->getId();
            $this->_websiteCodeToStoreIds[$website->getCode()] = array_flip($website->getStoreCodes());
        }
        return $this;
    }

    /**
     * EAV entity type code getter.
     *
     * @abstract
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'order';
    }

    /**
     * Is all of data valid?
     *
     * @return bool
     */
    public function isDataValid()
    {
        $this->validateMethod();

        parent::isDataValid();

        return 0 == $this->_errorsCount;
    }

    /**
     * @return Imaginato_Shipment_Model_Import_Entity
     */
    protected function validateMethod()
    {
        if (Mage_ImportExport_Model_Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            Mage::throwException(
                Mage::helper('importexport')->__('Only Append import method is allowed.')
            );
        }

        if (Mage_ImportExport_Model_Import::BEHAVIOR_REPLACE == $this->getBehavior()) {
            Mage::throwException(
                Mage::helper('importexport')->__('Only Append import method is allowed.')
            );
        }

        return $this;
    }

    /**
     * Returns model notices.
     *
     * @return array
     */
    public function getNotices()
    {
        $translator = $this->getHelper();
        $rawMessages = $messages = array();

        foreach ($this->_notices as $noticeCode => $noticeRows) {
            if (isset($this->_messageTemplates[$noticeCode])) {
                $noticeCode = $translator->__($this->_messageTemplates[$noticeCode]);
            }
            foreach ($noticeRows as $noticeRowData) {
                if (isset($this->_invalidRows[$noticeRowData[0] - 1])) {
                    continue;
                }

                $key = $noticeRowData[1] ? sprintf($noticeCode, $noticeRowData[1]) : $noticeCode;
                $rawMessages[$key][] = $noticeRowData[0];
            }
        }

        // notice info
        foreach ($rawMessages as $noticeCode => $rows) {
            $notice = $noticeCode . ' '
                . $this->getHelper()->__('in rows') . ': '
                . implode(', ', $rows);
            $messages[] = $notice;
        }

        return $messages;
    }

    /**
     * @param array $rowData
     * @param $rowNum
     */
    public function addSavedTracks(array $rowData, $rowNum)
    {
        $trackHash = $this->getHelperImport()->getHashFromRow($rowData);

        //check if same tracking already exists before in savedShipments array
        if (isset($this->_saveShipments[$rowData[self::COL_ID]][$trackHash])) {
            $this->addRowError(self::ERROR_TRACKING_EXISTS_SHIP, $rowNum);
            return;
        }

        $this->_saveTracks[$rowData[self::COL_ID]][$trackHash][$rowData[self::COL_SKU]] = $rowData;
    }

    /**
     * @param array $rowData
     * @param $rowNum
     */
    public function addSavedShipments(array $rowData, $rowNum)
    {
        if (isset($this->_invalidRows[$rowNum])) {
            return;
        }

        $trackHash = $this->getHelperImport()->getHashFromRow($rowData);
        $this->_saveShipments[$rowData[self::COL_ID]][$trackHash][$rowData[self::COL_SKU]] = $rowData;
    }

    /**
     * @param string $noticeCode
     * @param $noticeRowNum
     * @param $colName
     * @return $this
     */
    public function addNotice($noticeCode, $noticeRowNum, $colName = null)
    {
        $this->_notices[$noticeCode][] = array($noticeRowNum + 1, $colName); // one added for human readability
        $this->_noticeForRow[$noticeRowNum] = true;

        return $this;
    }

    /**
     * Create Product entity from raw data.
     *
     * @throws Exception
     * @return bool Result of operation.
     */
    protected function _importData()
    {
        $entity_type = $this->getHelper()->getEntity();
        if (Imaginato_Shipment_Model_Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            //no delete behaviour in this version
        } else {
            Mage::dispatchEvent('imaginato_shipment_init_eav_before', array('object' => $entity_type));
            $this->_saveItems();
        }
        Mage::dispatchEvent('imaginato_shipment_init_eav_after', array('object' => $entity_type));
        Mage::dispatchEvent('imaginato_shipment_import_finish_before', array('adapter' => $this));
        return true;
    }

    /**
     * Gather and save information about product entities.
     *
     * @return Imaginato_Shipment_Model_Import_Entity
     */
    protected function _saveItems()
    {
        $orderId = null;

        /** @var Mage_Core_Model_Resource_Transaction $resourceTrans */
        $resourceTrans = Mage::getModel('core/resource_transaction');

        /** @var Mage_Sales_Model_Convert_Order $convertor */
        $convertor = Mage::getModel('sales/convert_order');

        $bunchOrder = $bunchTrack = array();
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                $this->_filterRowData($rowData);

                if (!$this->validateRow($rowData, $rowNum)) {
                    continue;
                }

                $orderId = $rowData[self::COL_ID];

                $trackHash = $this->getHelperImport()->getHashFromRow($rowData);

                //new Shipment array
                if (isset($this->_saveShipments[$orderId][$trackHash][$rowData[self::COL_SKU]])
                    && !empty($this->_saveShipments[$orderId][$trackHash][$rowData[self::COL_SKU]])
                ) {
                    $bunchOrder[$orderId][$trackHash][$rowData[self::COL_SKU]] = $rowData;
                }

                //append track array
                $bunchTrack = $this->_saveTracks;
            }

            try {
                //For new shipment
                foreach ($bunchOrder as $orderId => $bunchShip) {
                    /** @var Mage_Sales_Model_Order $order */
                    $order = Mage::getModel('sales/order');
                    $order->loadByIncrementId((String)$orderId);

                    /**
                     * @var Mage_Sales_Model_Order $orderItems
                     * @var Mage_Sales_Model_Order_Shipment $shipment
                     * @var Mage_Sales_Model_Resource_Order_Shipment_Collection $shipments
                     * @var Mage_Sales_Model_Order_Shipment_Track $modelShipmentTrack
                     */
                    $orderItems = $order->getAllItems();
                    $totalQty = 0;

                    //Save New Shipment
                    end($bunchShip);
                    $lastShip = key($bunchShip);
                    //Loop foreach Shipment
                    foreach ($bunchShip as $hash => $skuData) {
                        /**
                         * Attempting to create New shipment
                         */
                        $savedQtys = array();
                        $performTransaction = false;

                        //Loop foreach sku / orderItems
                        $firstSku = key($skuData);
                        foreach ($skuData as $index => $rowData) {
                            /** @var Mage_Sales_Model_Order_Item $orderItem */
                            foreach ($orderItems as $orderItem) {
                                $itemId = $orderItem->getId();
                                $thisSku = $orderItem->getSku();
                                $thisQtyAvailable = $orderItem->getQtyOrdered() - $orderItem->getQtyShipped();
                                $savedQtys[$itemId] = 0;

                                //If no Quantity available to ship skip item
                                if (!$orderItem->getQtyToShip()) {
                                    continue;
                                }

                                if ($orderItem->getIsVirtual()) {
                                    continue;
                                }

                                if ($orderItem->canShip()) {

                                    //Use available Qty if it's empty
                                    if (!isset($rowData[self::COL_QTY]) || empty($rowData[self::COL_QTY])) {
                                        $baseQty = $thisQtyAvailable;
                                    } else {
                                        $baseQty = $rowData[self::COL_QTY];
                                    }

                                    if ((isset($rowData[self::COL_SKU]) && !empty($rowData[self::COL_SKU]))) {
                                        if ($rowData[self::COL_SKU] == $thisSku) {
                                            if ($thisQtyAvailable >= $baseQty) {
                                                $qty = $baseQty;
                                            } elseif (0 < $thisQtyAvailable
                                                && $thisQtyAvailable < $baseQty
                                            ) {
                                                $qty = $thisQtyAvailable;
                                            } else {
                                                //Qty above order
                                                continue;
                                            }

                                            $savedQtys[$itemId] = $qty;
                                        } else {
                                            continue;
                                        }
                                    } else {
                                        $savedQtys[$itemId] = $orderItem->getQtyToShip();
                                    }
                                }
                            }

                            if ($order->canShip() && !empty($savedQtys)) {
                                if ($index == $firstSku) {
                                    $performTransaction = true;
                                    $carrierCode = $this->getHelperImport()->getCarrierCode($rowData[self::COL_CARRIER]);
                                    $carrierTitle = $this->getHelperImport()->getCarrierTitle($rowData);
                                    $number = $rowData[self::COL_NUMBER];

                                }
                            } else {
                                continue;
                            }
                        }

                        if ($performTransaction) {
                            /** @var Mage_Sales_Model_Service_Order $serviceOrder */
                            $serviceOrder = Mage::getModel('sales/service_order', $order);
                            $shipment = $serviceOrder->prepareShipment($savedQtys);

                            if (isset($number) && isset($carrierCode) && isset($carrierTitle)) {
                                $modelShipmentTrack = Mage::getModel('sales/order_shipment_track');
                                $track = $modelShipmentTrack
                                    ->setNumber($number)
                                    ->setCarrierCode($carrierCode)
                                    ->setTitle($carrierTitle);
                                $shipment->addTrack($track);
                            }

                            $shipment->register();
                            $shipment->setEmailSent(false);

                            /** @var Mage_Sales_Model_Order $salesOrder */
                            $salesOrder = $shipment->getOrder();

                            if ($salesOrder->canShip()
                                && $salesOrder->canInvoice()
                                && $salesOrder->getStatus() !== Imaginato_Shipment_Model_Import::STATE_PARTIAL_SHIPPED
                            ) {
                                //If not all sku are shipped and no invoice set to Partial Shipped
                                $salesOrder = $this->getHelperImport()->setPartialShippedStatus($salesOrder);
                            } else if (!$salesOrder->canShip()
                                && $salesOrder->canInvoice()
                                && $salesOrder->getStatus() !== Imaginato_Shipment_Model_Import::STATE_SHIPPED
                            ) {
                                //If no invoice set order status to Shipped
                                $salesOrder = $this->getHelperImport()->setShippedStatus($salesOrder);
                            }

                            //Add some comment???
                            //$comment = 'Shipped Date: ' . $date;
                            //$shipment->addComment($comment);

                            //Send email to customer?
                            //$shipment->sendEmail($email, $comment);

                            $resourceTrans->addObject($shipment);

                            if ($hash == $lastShip) {
                                $salesOrder->setIsInProcess(true);
                                $resourceTrans->addObject($salesOrder);
                            }
                            $shipment->cleanModelCache();
                        }

                        if (isset($modelShipmentTrack) && $modelShipmentTrack instanceof Mage_Sales_Model_Order_Shipment_Track) {
                            $modelShipmentTrack->cleanModelCache();
                        }

                    }

                    $order->cleanModelCache();
                }

                foreach ($bunchTrack as $orderId => $bunchShip) {

                    /** @var Mage_Sales_Model_Order $order */
                    $order = Mage::getModel('sales/order');
                    $order->loadByIncrementId((String)$orderId);

                    /**
                     * @var Mage_Sales_Model_Order_Shipment $shipment
                     * @var Mage_Sales_Model_Resource_Order_Shipment_Collection $shipments
                     * @var Mage_Sales_Model_Order_Shipment_Track $modelShipmentTrack
                     */
                    //Loop foreach Shipment
                    foreach ($bunchShip as $hash => $skuData) {

                        //Loop foreach sku / orderItems
                        foreach ($skuData as $index => $rowData) {
                            /**
                             * Add tracking information for order which already has order shipment
                             */
                            if (isset($rowData['inShipment'][0])) {
                                /** @var Mage_Sales_Model_Order_Shipment $modelShipment */
                                $modelShipment = Mage::getModel('sales/order_shipment');
                                $shipment = $modelShipment->load($rowData['inShipment'][0]);
                            } else {
                                $shipments = $order->getShipmentsCollection();
                                $shipment = $shipments->getFirstItem();
                            }

                            $carrierCode = $this->getHelperImport()->getCarrierCode($rowData[self::COL_CARRIER]);
                            $modelShipmentTrack = Mage::getModel('sales/order_shipment_track');

                            $track = $modelShipmentTrack
                                ->setNumber($rowData[self::COL_NUMBER])
                                ->setCarrierCode($carrierCode)
                                ->setTitle($rowData[self::COL_TITLE]);

                            $shipment->addTrack($track);

                            $resourceTrans->addObject($shipment);
                        }
                    }
                    $order->cleanModelCache();
                }

                //Save all transactions
                $resourceTrans->save();

            } catch (Exception $e) {
                Mage::throwException($e->getMessage());
                continue;
            }
        }

        return $this;
    }

    /**
     * Removes empty keys in case value is null or empty string
     *
     * @param array $rowData
     */
    protected function _filterRowData(&$rowData)
    {
        $rowData = array_filter($rowData, 'strlen');
        if (!isset($rowData[self::COL_ID])) {
            $rowData[self::COL_ID] = null;
        }
    }

    /**
     * Validate data row.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return boolean
     */
    public function validateRow(array $rowData, $rowNum)
    {
        static $order_id = null;

        if (isset($this->_validatedRows[$rowNum])) { // check that row is already validated
            return !isset($this->_invalidRows[$rowNum]);
        }
        $this->_validatedRows[$rowNum] = true;

        //check if Increment ID/Order Number exists
        if (!isset($rowData[self::COL_ID]) || empty($rowData[self::COL_ID]) || !is_numeric($rowData[self::COL_ID])) {
            $this->addRowError(self::ERROR_ID_IS_EMPTY, $rowNum);
            return false;
        }
        //check if sku exists
        if (empty($rowData[self::COL_SKU])) {
            $this->addRowError(self::ERROR_SKU_INFO, $rowNum);
            return false;
        }
        //check if qty exists
        if (empty($rowData[self::COL_QTY]) || !is_numeric($rowData[self::COL_QTY])) {
            $this->addRowError(self::ERROR_QTY_NOT_AVAILABLE, $rowNum);
            return false;
        }

        $rowScope = $this->getRowScope($rowData);

        $this->_validate($rowData, $rowNum);

        if (self::SCOPE_DEFAULT == $rowScope) { // ID is specified, row is SCOPE_DEFAULT, new entity block begins
            $this->_processedEntitiesCount++;
            $order_id = $rowData[self::COL_ID];
            if (isset($this->_oldId[$order_id])) { // can we get all necessary data from existant DB entity?
                // check for supported type of existing entity
                $this->_newId[$order_id][] = $rowData;
            } else { // validate new entity
                if (!isset($this->_newId[$order_id])) {
                    $this->_newId[$order_id] = null;
                }
                if (isset($this->_invalidRows[$rowNum])) {
                    // mark SCOPE_DEFAULT row as invalid for future child rows if product not in DB already
                    $order_id = false;
                }
            }
        } else {
            if (null === $order_id) {
                $this->addRowError(self::ERROR_ID_IS_EMPTY, $rowNum);
            } elseif (false === $order_id) {
                $this->addRowError(self::ERROR_ROW_IS_ORPHAN, $rowNum);
            } elseif (self::SCOPE_STORE == $rowScope && !isset($this->_storeCodeToId[$rowData[self::COL_STORE]])) {
                $this->addRowError(self::ERROR_INVALID_STORE, $rowNum);
            }
        }

        return !isset($this->_invalidRows[$rowNum]);
    }

    /**
     * Obtain scope of the row from row data.
     *
     * @param array $rowData
     * @return int
     */
    public function getRowScope(array $rowData)
    {
        if (isset($rowData[self::COL_ID]) && strlen(trim($rowData[self::COL_ID]))) {
            return self::SCOPE_DEFAULT;
        } elseif (empty($rowData[self::COL_STORE])) {
            return self::SCOPE_NULL;
        } else {
            return self::SCOPE_STORE;
        }
    }

    /**
     * Common validation
     *
     * @param array $rowData
     * @param int $rowNum
     */
    protected function _validate($rowData, $rowNum)
    {
        $attrValid = $this->_checkAllRequiredAttr($rowData, $rowNum);
        $this->_isCarrierValid($rowData, $rowNum);
        $validId = $this->_isIncrementIdValid($rowData, $rowNum);
        $hasSku = $this->_hasSkuAttr($rowData, $rowNum);
        $skuHasQty = $this->_doesSkuHasQty($rowData, $rowNum);

        if ($validId && $attrValid) {
            //does sku exists in order?
            $validSku = $this->_isSkuValid($rowData, $rowNum);

            $canShip = $this->_orderCanShip($rowData, $rowNum);

            //does order has shipment?
            $hasShipment = $this->_isShipmentExist($rowData, $rowNum);
            if ($hasShipment) {
                //check shipment for tracking information
                //save shipment which has no tracking info to $this->_shipmentNoTrack
                $this->_checkTracking($rowData, $rowNum);

                //does shipment has same tracking information (carrier, title, number)?
                $hasSameTracking = $this->_hasSameTracking($rowData, $rowNum);

                //if no sku and order already has shipments made :
                //set sku required for specific shipment
                $countShip = (count($this->_orderHasShipments[$rowData[self::COL_ID]]));
                if (!$hasSku && (!$this->_checkRowExistsInNoTrack($rowData) && $countShip > 1)) {
                    $this->addRowError(self::ERROR_SKU_REQUIRED, $rowNum);
                }

                if ($validSku) { //sku exists in order

                    //does sku exists in shipment?
                    $skuInShipment = $this->_skuExistsInShipment($rowData, $rowNum);

                    if ($this->_checkRowExistsInNoTrack($rowData) && $countShip == 1) {
                        //if order have only 1 existing shipment and no tracking information
                    } else if (!$canShip) {
                        $this->addRowError(self::ERROR_CANNOT_SHIP, $rowNum, $rowData[self::COL_ID]);
                    }

                    if ($skuInShipment || !$hasSameTracking) {
                        //check if qty is available for shipment
                        $qtyAvailable = $this->_isQtyAvailable($rowData, $rowNum);

                        if ($skuInShipment) {
                            if ($shipmentID = $this->_checkRowExistsInNoTrack($rowData)) {
                                $this->getHelperImport()->addNewTrack($this, $rowData, $rowNum);
                            } else if ($qtyAvailable) {
                                $this->getHelperImport()->addNewShipment($this, $rowData, $rowNum, self::NOTICE_SHIPMENT_NEW_SHIPMENT);
                            }
                        } elseif (!$hasSameTracking) {
                            //for row which does not have sku or sku not shipped already
                            if ($shipmentID = $this->_checkRowExistsInNoTrack($rowData)) {
                                $this->getHelperImport()->addNewTrack($this, $rowData, $rowNum);
                            } else {
                                $this->getHelperImport()->addNewShipment($this, $rowData, $rowNum);
                            }
                        }
                    }
                }

            } elseif ($validSku && $canShip) {
                $hasSameTracking = $this->_hasSameTracking($rowData, $rowNum);
                $qtyAvailable = $this->_isQtyAvailable($rowData, $rowNum);
                if(!$hasSameTracking && $qtyAvailable){
                    $this->getHelperImport()->addNewShipment($this, $rowData, $rowNum);
                }
            }
        }
    }

    /**
     * @param array $rowData
     * @param $rowNum
     * @return bool
     */
    protected function _checkAllRequiredAttr(array $rowData, $rowNum)
    {
        // check simple attributes
        $hasInvalidAttrs = false;
        foreach ($this->_requiredAttributes as $attrCode) {
            if (in_array($attrCode, $this->_ignoredAttributes)) {
                continue;
            }
            if (isset($rowData[$attrCode]) && strlen($rowData[$attrCode])) {
                // TODO: check if attribute is valid : $this->isAttributeValid()
            } else {
                $this->addRowError(self::ERROR_VALUE_IS_REQUIRED, $rowNum, $attrCode);
                $hasInvalidAttrs = true;
            }
        }

        if ($hasInvalidAttrs) {
            return false;
        }
        return true;
    }

    /**
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    protected function _isCarrierValid(array $rowData, $rowNum)
    {
        if (!empty($rowData[self::COL_CARRIER])) {
            $carrierCode = $this->getHelperImport()->getCarrierCode($rowData[self::COL_CARRIER]);
            if (!isset($this->_carrierCodeToId[$carrierCode])) {
                $this->addRowError(self::ERROR_INVALID_CARRIER, $rowNum, $rowData[self::COL_CARRIER]);
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $rowData
     * @param $rowNum
     * @return bool
     */
    protected function _isIncrementIdValid(array $rowData, $rowNum)
    {
        if (!empty($rowData[self::COL_ID]) && !isset($this->_oldId[$rowData[self::COL_ID]])) {
            $this->addRowError(self::ERROR_ID_NOT_FOUND_FOR_APPEND, $rowNum, $rowData[self::COL_ID]);
            return false;
        } else {
            $this->_currentOrder = Mage::getModel('sales/order');
            $this->_currentOrder->loadByIncrementId((String)$rowData[self::COL_ID]);
        }
        return true;
    }

    /**
     * @param array $rowData
     * @param $rowNum
     * @return bool
     */
    protected function _hasSkuAttr(array $rowData, $rowNum)
    {
        return (isset($rowData[self::COL_SKU]) && !empty($rowData[self::COL_SKU]));
    }

    /**
     * @param array $rowData
     * @param $rowNum
     * @return bool
     */
    protected function _doesSkuHasQty(array $rowData, $rowNum)
    {
        if (isset($rowData[self::COL_SKU]) && !empty($rowData[self::COL_SKU])) {
            if (!isset($rowData[self::COL_QTY]) || empty($rowData[self::COL_QTY])) {
                $this->addRowError(self::ERROR_QTY_REQUIRED, $rowNum);
                return false;
            }
        }
        return true;
    }

    /**
     * Check if SKU found in Order
     *
     * @param array $rowData
     * @param $rowNum
     * @return bool
     */
    protected function _isSkuValid(array $rowData, $rowNum)
    {
        if (isset($rowData[self::COL_SKU]) && !empty($rowData[self::COL_SKU])) {
            $found = false;

            /** @var Mage_Sales_Model_Order $order */
            $order = $this->_currentOrder;

            if ($order->hasData()) {
                $orderItems = $order->getAllItems();

                /** @var Mage_Sales_Model_Order_Item $orderItem */
                foreach ($orderItems as $orderItem) {
                    $thisSku = $orderItem->getSku();
                    if ($thisSku == $rowData[self::COL_SKU]) {
                        $found = true;
                        break;
                    }
                }
            }

            if (!$found) {
                $this->addRowError(self::ERROR_SKU_INFO, $rowNum);
                return false;
            }
        }
        return true;
    }

    /**
     * @param array $rowData
     * @param $rowNum
     * @return bool
     */
    protected function _orderCanShip(array $rowData, $rowNum)
    {
        if ($this->_currentOrder->canShip()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param array $rowData
     * @param $rowNum
     * @return bool
     */
    protected function _isShipmentExist(array $rowData, $rowNum)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $this->_currentOrder;

        //check has shipment
        if ($order->hasData() && $order->hasShipments()) {
            /** @var Mage_Sales_Model_Resource_Order_Shipment_Collection $shipmentColl */
            $shipmentColl = $order->getShipmentsCollection();
            $shipmentIds = $shipmentColl->getAllIds();

            $shipments = array();
            foreach ($shipmentIds as $shipmentId) {
                /** @var Mage_Sales_Model_Order_Shipment $shipment */
                $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
                $allTracks = $shipment->getAllTracks();
                $shipments[$shipmentId] = $allTracks;
            }

            $this->_orderHasShipments[$rowData[self::COL_ID]] = $shipments;
            return true;
        }

        return false;
    }

    protected function _checkTracking(array $rowData, $rowNum)
    {
        //check if has tracking
        if (isset($this->_orderHasShipments[$rowData[self::COL_ID]])) {
            $shipmentIds = $this->_orderHasShipments[$rowData[self::COL_ID]];

            foreach ($shipmentIds as $shipmentId => $allTracks) {

                /** @var Mage_Sales_Model_Order_Shipment_Track $track */
                $hasTrack = false;
                foreach ($allTracks as $track) {
                    if ($track->hasData()) {
                        $hasTrack = true;
                        break;
                    }
                }

                if (!$hasTrack) {
                    $hashTrack = $this->getHelperImport()->getHashFromRow($rowData);
                    //always reserve first "shipment without track" to first encountered $rowData
                    if (!isset($this->_shipmentNoTrack[$rowData[self::COL_ID]][$shipmentId])) {

                        //if this row have sku, set track empty and saved it for later use
                        if (isset($rowData[self::COL_SKU]) && !empty($rowData[self::COL_SKU])) {
                            $hashTrack = array();
                        }

                        $this->_shipmentNoTrack[$rowData[self::COL_ID]][$shipmentId] = $hashTrack;
                        break;
                    }
                }
            }
        }
    }

    /**
     * @param array $rowData
     * @param $rowNum
     * @return bool
     */
    protected function _hasSameTracking(array $rowData, $rowNum)
    {
        //check if has tracking
        $hasSimilarTrack = false;
        $tracks = array();

        if (isset($this->_orderHasShipments[$rowData[self::COL_ID]])) {
            $shipmentIds = $this->_orderHasShipments[$rowData[self::COL_ID]];
            foreach ($shipmentIds as $shipmentId => $allTracks) {

                /** @var Mage_Sales_Model_Order_Shipment_Track $track */
                foreach ($allTracks as $track) {
                    if ($track->hasData()) {

                        $trackHash = $this->getHelperImport()->trackingInfoHash($track->getCarrierCode(), $track->getTitle(), $track->getNumber());

                        //check if has similar Tracking Info (Carrier, Title, Number)
                        $rowHash = $this->getHelperImport()->getHashFromRow($rowData);
                        if ($rowHash == $trackHash) {
                            $hasSimilarTrack = true;
                        }

                        $tracks[$track->getId()] = array(
                            'hash'            => $trackHash,
                            self::COL_CARRIER => $track->getCarrierCode(),
                            self::COL_TITLE   => $track->getTitle(),
                            self::COL_NUMBER  => $track->getNumber(),
                        );
                    }
                }
            }

            if ($hasSimilarTrack) {
                $this->addRowError(self::ERROR_TRACKING_EXISTS, $rowNum);
                return true;
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * @param array $rowData
     * @return bool
     */
    protected function _checkRowExistsInNoTrack(array $rowData)
    {
        if (!$this->_canAppendTrack) {
            return false;
        }

        $orderNumber = $rowData[self::COL_ID];
        $trackHash = $this->getHelperImport()->getHashFromRow($rowData);

        $countShip = (count($this->_orderHasShipments[$rowData[self::COL_ID]]));

        if (isset($this->_shipmentNoTrack[$orderNumber]) && !empty($this->_shipmentNoTrack[$orderNumber])) {
            foreach ($this->_shipmentNoTrack[$orderNumber] as $shipmentId => $hash) {
                if ((empty($hash) && isset($rowData['inShipment'])
                        && in_array($shipmentId, $rowData['inShipment'])
                    )
                    //if no sku defined
                    || (empty($rowData[self::COL_SKU]) && $countShip == 1)
                ) {
                    $this->_shipmentNoTrack[$rowData[self::COL_ID]][$shipmentId] = $trackHash;
                }

                if (($this->_shipmentNoTrack[$rowData[self::COL_ID]][$shipmentId] == $trackHash
                        && isset($rowData['inShipment'])
                        && in_array($shipmentId, $rowData['inShipment'])
                    )
                    //if no sku defined
                    || ($this->_shipmentNoTrack[$rowData[self::COL_ID]][$shipmentId] == $trackHash
                        && empty($rowData[self::COL_SKU]) && $countShip == 1
                    )
                ) {
                    return $shipmentId;
                    break;
                }
            }
        }
        return false;
    }

    /**
     * @param array $rowData
     * @param $rowNum
     * @return bool
     */
    protected function _skuExistsInShipment(array &$rowData, $rowNum)
    {
        //check if sku exists in shipment
        $shipmentFounds = array();

        if (isset($this->_orderHasShipments[$rowData[self::COL_ID]])) {
            $shipmentIds = $this->_orderHasShipments[$rowData[self::COL_ID]];
            foreach ($shipmentIds as $shipmentId => $allTracks) {
                /** @var Mage_Sales_Model_Order_Shipment $shipment */
                $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
                $shipmentItems = $shipment->getAllItems();
                /** @var Mage_Sales_Model_Order_Shipment_Item $shipmentItem */
                foreach ($shipmentItems as $shipmentItem) {
                    $thisSku = $shipmentItem->getSku();
                    if ($thisSku == $rowData[self::COL_SKU]) {
                        $shipmentFounds[$thisSku][] = $shipmentId;
                        break;
                    }
                }
            }
        }

        if (!empty($shipmentFounds)) {
            //sku exists in shipment
            $this->_skuInShipment = array_merge($this->_skuInShipment, $shipmentFounds);
            $rowData['inShipment'] = $shipmentFounds[$rowData[self::COL_SKU]];
            return true;
        } else {
            //sku not in shipment
            return false;
        }
    }

    /**
     * @param array $rowData
     * @param $rowNum
     * @return bool
     */
    protected function _isQtyAvailable(array $rowData, $rowNum)
    {
        if ($shipmentID = $this->_checkRowExistsInNoTrack($rowData)) {
            return true;
        }

        /** @var Mage_Sales_Model_Order $order */
        $order = $this->_currentOrder;

        $orderItems = $order->getAllItems();

        /** @var Mage_Sales_Model_Order_Item $orderItem */
        foreach ($orderItems as $orderItem) {
            $thisSku = $orderItem->getSku();

            //if sku the same, continue
            if ($thisSku == $rowData[self::COL_SKU]) {

                $thisQtyAvailable = $orderItem->getQtyOrdered() - $orderItem->getQtyShipped();

                //If no Quantity available to ship skip item
                if (!$orderItem->getQtyToShip()) {
                    $this->addRowError(self::ERROR_QTY_NOT_AVAILABLE, $rowNum);
                    return false;
                }

                if ($orderItem->getIsVirtual()) {
                    $this->addRowError(self::ERROR_VIRTUAL_ITEM, $rowNum);
                    return false;
                }

                if ($orderItem->canShip()) {

                    //Use available Qty if it's empty
                    if (!isset($rowData[self::COL_QTY]) || empty($rowData[self::COL_QTY])) {
                        $baseQty = $thisQtyAvailable;
                    } else {
                        $baseQty = $rowData[self::COL_QTY];
                    }

                    if ($thisQtyAvailable >= $baseQty) {
                        //if baseQty is the same or less than available Qty
                        $qty = $baseQty;
                    } elseif (0 < $thisQtyAvailable
                        && $thisQtyAvailable < $baseQty
                    ) {
                        $this->addRowError(self::ERROR_QTY_ABOVE_ORDER, $rowNum);
                        return false;
                        //if baseQty is bigger, use available Qty
                        //$qty = $thisQtyAvailable;
                    } else {
                        $this->addRowError(self::ERROR_QTY_ABOVE_ORDER, $rowNum);
                        return false;
                    }

                    if (isset($qty) && !empty($qty)) {
                        return true;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Set valid attribute set and product type to rows with all scopes
     * to ensure that existing products doesn't changed.
     *
     * @see Mage_ImportExport_Model_Import_Entity_Product::_prepareRowForDb()
     *
     * @param array $rowData
     * @return array
     */
    protected function _prepareRowForDb(array $rowData)
    {
        $rowData = parent::_prepareRowForDb($rowData);

        static $lastId = null;

        if (Mage_ImportExport_Model_Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            return $rowData;
        }
        if (self::SCOPE_DEFAULT == $this->getRowScope($rowData)) {
            $lastId = $rowData[self::COL_ID];
        }
        if (isset($this->_oldId[$lastId])) {

        }

        return $rowData;
    }
}