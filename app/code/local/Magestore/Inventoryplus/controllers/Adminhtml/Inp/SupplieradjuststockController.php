<?php

/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventory Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */
class Magestore_Inventoryplus_Adminhtml_Inp_SupplieradjuststockController extends Magestore_Inventoryplus_Controller_Action
{

    /**
     * Menu Path
     *
     * @var string
     */
    protected $_menu_path = 'inventoryplus/stock_control/stock_onhand/supplier_adjust_stock';

    /**
     *
     * @return \Magestore_Inventoryplus_Adminhtml_Inp_AdjuststockController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu($this->_menu_path)
            ->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Supplier Adjust stock Manager'), Mage::helper('adminhtml')->__('Supplier Adjust stock Manager')
            );
        $this->_title($this->__('Inventory'))
            ->_title($this->__('Manage Supplier Adjust Stocks'));
        return $this;
    }

    /**
     * index action
     */
    public function indexAction()
    {
        $this->_initAction()
            ->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('Magestore_Inventoryplus_Block_Adminhtml_Listsupplieradjuststock_Grid')->toHtml()
        );
    }

    public function editAction() {
        $this->_title($this->__('Inventory'))
            ->_title($this->__('Edit Supplier Adjust Stock'));

        $supplierAdjustStockId = $this->getRequest()->getParam('id');
        $model = Mage::getModel('inventorydropship/supplier_adjuststock')->load($supplierAdjustStockId);
        if ($model->getId() || $supplierAdjustStockId == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('supplieradjuststock_data', $model);

            $this->loadLayout()->_setActiveMenu($this->_menu_path);
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true)
                ->removeItem('js', 'mage/adminhtml/grid.js')
                ->addItem('js', 'magestore/adminhtml/inventory/grid.js');
            $this->_addContent($this->getLayout()->createBlock('inventoryplus/adminhtml_supplieradjuststock_edit'))
                ->_addLeft($this->getLayout()->createBlock('inventoryplus/adminhtml_supplieradjuststock_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('inventoryplus')->__('Supplier Adjust Stock does not exist')
            );
            $this->_redirect('*/*/');
        }
    }

    public function productAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('inventoryplus.supplieradjuststock.edit.tab.products');
        $this->renderLayout();

    }

    public function productGridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('inventoryplus.supplieradjuststock.edit.tab.products');
        $this->renderLayout();
    }

    public function saveAction(){
        $warehouse_id = $this->getRequest()->getParam('warehouse_id');
        $supplier_adjuststock_id = $this->getRequest()->getParam('id');
        $supplier_adjuststock = Mage::getModel('inventorydropship/supplier_adjuststock')->load($supplier_adjuststock_id);
        if ($this->getRequest()->getParam('confirm')) {
            $supplier_adjuststock_product = Mage::getModel('inventorydropship/supplier_adjuststock_product')->getCollection()
                ->addFieldToFilter('supplier_adjuststock_id', $supplier_adjuststock_id);
            $arrayProductAdjust = array();
            foreach ($supplier_adjuststock_product as $item){
                $arrayProductAdjust[$item->getProductId()] = $item->getAdjustQty();
            }

            foreach ($arrayProductAdjust as $productId => $qtyAdjust){

                /*Update in warehouse*/
                $warehouse_product = Mage::getModel('inventoryplus/warehouse_product')->getCollection()
                    ->addFieldToFilter('warehouse_id', $warehouse_id)
                    ->addFieldToFilter('product_id', $productId)
                    ->getFirstItem();
                $qtyChanged = 0;
                $warehouse_product_id = $warehouse_product->getId();
                if(isset($warehouse_product_id)){
                    $totalQty = $warehouse_product->getTotalQty();
                    $availableQty = $warehouse_product->getAvailableQty();

                    $qtyChanged = $qtyAdjust - $totalQty;
                    $newQtyAvailable = $availableQty + $qtyChanged;
                    $warehouse_product->setTotalQty($qtyAdjust)
                        ->setAvailableQty($newQtyAvailable)
                        ->save();
                }else{
                    $qtyChanged = $qtyAdjust;
                    Mage::getModel('inventoryplus/warehouse_product')->setWarehouseId($warehouse_id)
                        ->setProductId($productId)
                        ->setTotalQty($qtyAdjust)
                        ->setAvailableQty($qtyAdjust)
                        ->save();
                }

                /*Update in cataloginventory*/
                $thresholdStockStatus = Mage::getStoreConfig('cataloginventory/item_options/min_qty');
                $manageStock = Mage::getStoreConfig('cataloginventory/item_options/manage_stock', Mage::app()->getStore()->getStoreId());
                $backorders = Mage::getStoreConfig('cataloginventory/item_options/backorders', Mage::app()->getStore()->getStoreId());
                $stockStatus = Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK;
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
                if (!$stockItem->getUseConfigManageStock()) {
                    $manageStock = $stockItem->getManageStock();
                }
                if (!$manageStock) {
                    continue;
                }
                if (!$stockItem->getUseConfigBackorders()) {
                    $backorders = $stockItem->getBackorders();
                }
                if (!$stockItem->getUseConfigMinQty()) {
                    $thresholdStockStatus = $stockItem->getMinQty();
                }
                if ($stockItem->getQty() + $qtyChanged <= $thresholdStockStatus) {
                    $stockStatus = Mage_CatalogInventory_Model_Stock_Status::STATUS_OUT_OF_STOCK;
                }
                $stockStatus = $backorders ? Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK : $stockStatus;
                $stock_item = Mage::getModel('cataloginventory/stock_item')->getCollection()
                    ->addFieldToFilter('product_id', $productId)->getFirstItem();
                $stock_item->setQty($stock_item->getQty() + $qtyChanged)
                    ->setIsInStock($stockStatus)
                    ->save();
                $resource = Mage::getSingleton('core/resource');
                $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');

                $updateSql = ' UPDATE ' . $resource->getTableName('cataloginventory/stock_status')
                    . ' SET `qty` = `qty` + ' . $qtyChanged . ', `stock_status` = ' . $stockStatus
                    . ' WHERE (`product_id` = ' . $productId . ');';


                try {
                    $writeConnection->beginTransaction();
                    if ($updateSql)
                        $writeConnection->query($updateSql);
                    $writeConnection->commit();
                } catch (Exception $e) {
                    $writeConnection->rollback();
                    throw $e;
                }

                /*Update status supplier adjuststock*/
                $supplier_adjuststock->setStatus(1)
                    ->setConfirmedBy(Mage::getSingleton('admin/session')->getUser()->getUsername())
                    ->setConfirmedAt(date("Y-m-d"))
                    ->save();

                $this->_redirect('*/*/edit', array('id' => $supplier_adjuststock_id));
                return;
            }
        }
        if ($this->getRequest()->getParam('cancel')) {
            /*Update status supplier adjuststock*/
            $supplier_adjuststock->setStatus(2)->save();
            $this->_redirect('*/*/edit', array('id' => $supplier_adjuststock_id));
            return;
        }
    }
}