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
 * @package     Magestore_Inventorybarcode
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorybarcode Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_Inventorybarcode
 * @author      Magestore Developer
 */
class Magestore_Inventorybarcode_Adminhtml_Inb_BarcodeController extends Magestore_Inventoryplus_Controller_Action {

    
    /*
     * Menu path
     * 
     * @var string
     */
    protected $_menu_path = 'inventoryplus/settings/barcode/manage_barcode';
    
    /**
     * init layout and set active for current menu
     *
     * @return Magestore_Inventorybarcode_Adminhtml_BarcodeattributeController
     */
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu($this->_menu_path)
                ->_addBreadcrumb(
                        Mage::helper('adminhtml')->__('Manage Barcodes'), Mage::helper('adminhtml')->__('Manage Barcodes')
        );
        $this->_title($this->__('Inventory'))
                ->_title($this->__('Manage Barcodes'));
        return $this;
    }

    /**
     * index action
     */
    public function indexAction() {
        if (!$this->_helper()->isMultipleBarcode()) {
            /* switch to single barcode mode */
            return $this->_forward('indexsingle');
        }
        Mage::getModel('admin/session')->setData('barcode_product_import', null);
        Mage::getModel('admin/session')->setData('barcode_product_import', null);
        $this->loadLayout();
        $this->_setActiveMenu($this->_menu_path);
        $this->_title($this->__('Inventory Management'))
                ->_title($this->__('Manage Barcodes'));
        $this->renderLayout();
    }

    public function indexsingleAction() {
        $this->loadLayout();
        $this->_setActiveMenu($this->_menu_path);
        $this->_title($this->__('Inventory Management'))
                ->_title($this->__('Manage Barcodes'));
        $this->renderLayout();
    }

    public function productbarcodetabAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function productbarcodegridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('inventorybarcode/adminhtml_catalog_product_barcode')->toHtml()
        );
    }

    public function printbarcodeAction() {
        Mage::getModel('admin/session')->setData('barcode_product_import', null);
        Mage::getModel('admin/session')->setData('barcode_product_import', null);
        $this->loadLayout();
        $this->getLayout()->getBlock('barcode.print')
                ->setBarcodes($this->getRequest()->getPost('barcode_product_list', null));
        $this->renderLayout();
    }

    public function printbarcodegridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('barcode.print')
                ->setBarcodes($this->getRequest()->getPost('barcode_product_list', null));
        $this->renderLayout();
    }

    /**
     * view and edit item action
     */
    public function editAction() {
        $inventorybarcodeId = $this->getRequest()->getParam('id');
        $print = $this->getRequest()->getParam('print');
        $model = Mage::getModel('inventorybarcode/barcode')->load($inventorybarcodeId);
        $this->_title($this->__('Inventory'));
        if (!$inventorybarcodeId && !$print) {
            $this->_title($this->__('Add New Barcode'));
        } elseif ($inventorybarcodeId) {
            $this->_title($this->__('View Barcode'));
        } else {
            $this->_title($this->__('Print Barcode'));
        }
        if ($model->getId() || $inventorybarcodeId == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('barcode_data', $model);

            $this->loadLayout()->_setActiveMenu($this->_menu_path);

            $this->_addBreadcrumb(
                    Mage::helper('adminhtml')->__('Manage Barcodes'), Mage::helper('adminhtml')->__('Manage Barcodes')
            );
            $this->_addBreadcrumb(
                    Mage::helper('adminhtml')->__('Add New Custom Barcode'), Mage::helper('adminhtml')->__('Add New Custom Barcode')
            );
            if (!$inventorybarcodeId) {
                $this->getLayout()->getBlock('head')
                        ->addCss('css/magestore/inventorybarcode/hiddenleftslide.css');
            }
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true)
                    ->removeItem('js', 'mage/adminhtml/grid.js')
                    ->addItem('js', 'magestore/adminhtml/inventory/grid.js');

            $this->_addContent($this->getLayout()->createBlock('inventorybarcode/adminhtml_barcode_edit'))
                    ->_addLeft($this->getLayout()->createBlock('inventorybarcode/adminhtml_barcode_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('inventorybarcode')->__('Barcode does not exist')
            );
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {

        $this->_forward('edit');
    }

    /**
     * check barcode dupplicate
     */
    public function checkDupplicate($barcode) {
        $code = Mage::helper('inventorybarcode')->generateCode(Mage::getStoreConfig('inventoryplus/barcode/pattern'));
        if (in_array($code, $barcode)) {
            $code = $this->checkDupplicate($barcode);
        }
        return $code;
    }

    /**
     * save item action
     */
    public function saveAction() {

        if ($post = $this->getRequest()->getPost()) {

            $model = Mage::getModel('inventorybarcode/barcode')->load($this->getRequest()->getParam('id'));

            $admin = Mage::getModel('admin/session')->getUser()->getUsername();

            if (!$post['barcode_status'] && $this->getRequest()->getParam('barcode_status'))
                $post['barcode_status'] = $this->getRequest()->getParam('barcode_status');

            try {
                if ($model->getId()) {
                    $model->setData('barcode_status', $post['barcode_status'])->save();

                    //update action log

                    if ($post['barcode_status'] == 1) {
                        Mage::getModel('inventorybarcode/barcode_actionlog')->setBarcodeAction(Mage::helper('inventorybarcode')->__('Account "%s" enabled barcode "%s"', $admin, $model->getBarcode()))
                                ->setCreatedAt(now())
                                ->setCreatedBy($admin)
                                ->setBarcode($model->getBarcode())
                                ->save();
                    } else {
                        Mage::getModel('inventorybarcode/barcode_actionlog')->setBarcodeAction(Mage::helper('inventorybarcode')->__('Account "%s" disabled barcode "%s"', $admin, $model->getBarcode()))
                                ->setCreatedAt(now())
                                ->setCreatedBy($admin)
                                ->setBarcode($model->getBarcode())
                                ->save();
                    }

                    if ($this->getRequest()->getParam('back')) {
                        if (!$this->getRequest()->getParam('id')) {
                            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('inventorybarcode')->__('The barcode(s) have been created successfully.'));
                        } else {
                            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('inventorybarcode')->__('The barcode(s) have been saved successfully.'));
                        }
                        $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                        return;
                    }
                    if (!$this->getRequest()->getParam('id')) {
                        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('inventorybarcode')->__('The barcode(s) have been created successfully.'));
                    } else {
                        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('inventorybarcode')->__('The barcode(s) have been saved successfully.'));
                    }

                    $this->_redirect('*/*');
                    return;
                }
                $resource = Mage::getSingleton('core/resource');

                $writeConnection = $resource->getConnection('core_write');


                $sqlNews = array();
                $sqlOlds = '';
                $countSqlOlds = 0;

                $tablename = 'inventorybarcode/barcode';

                $results = Mage::helper('inventorybarcode/attribute')->getAllColumOfTable($tablename);

                $columns = array();
                $string = '';
                $type = '';

                foreach ($results as $result) {
                    $fields = explode('_', $result);
                    if ($fields[0] == 'barcode' || $fields[0] == 'qty')
                        continue;
                    foreach ($fields as $id => $field) {
                        if ($id == 0)
                            $type = $field;
                        if ($id == 1) {
                            $string = $field;
                        }
                        if ($id > 1)
                            $string = $string . '_' . $field;
                    }
                    $columns[] = array($type => $string);
                    $string = '';
                    $type = '';
                }


                if (isset($post['barcode_products'])) {
                    $products = array();
                    $productsExplodes = explode('&', urldecode($post['barcode_products']));

                    if (count($productsExplodes) <= 900) {
                        Mage::helper('inventoryplus')->parseStr(urldecode($post['barcode_products']), $products);
                    } else {
                        foreach ($productsExplodes as $productsExplode) {
                            $product = '';
                            Mage::helper('inventoryplus')->parseStr($supplierProductsExplode, $product);
                            $products = $products + $product;
                        }
                    }

                    if (count($products)) {
                        $productIds = '';
                        $qtys = '';
                        $count = 0;
                        $j = 0;
                        $barcode = array();

                        $success = false;
                        $back = false;
                        $errorValues = array();
                        foreach ($products as $pId => $enCoded) {

                            $errors = false;
                            $codeArr = array();
                            Mage::helper('inventoryplus')->parseStr(Mage::helper('inventoryplus')->base64Decode($enCoded), $codeArr);

                            // check qty
                            if (!$codeArr['warehouse_warehouse_id']) {
                                $product = Mage::getModel('catalog/product')->load($pId);
                                $maxQty = (int) $product->getStockItem()->getQty();

                                $barcode_product = Mage::getModel('inventorybarcode/barcode')
                                    ->getCollection()
                                    ->addFieldToFilter('product_entity_id', $pId);

                                    $barcode_product->getSelect()->columns(
                                        array(
                                            'qty_barcode' => new Zend_Db_Expr("SUM(main_table.qty)")
                                        )
                                    );
                                foreach ($barcode_product as $barcodeqty){
                                    $qtyBarcode = $barcodeqty ->getQtyBarcode();
                                }
                                if ($post['qty'] < ($maxQty - $qtyBarcode) || ($post['qty'] > ($maxQty - $qtyBarcode)) ) {
                                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('inventoryplus')->__('You have not selected warehouse of product "%s"', $product->getName()));
                                    $this->_redirect('*/*/edit');
                                    return;
                                }
                                if ($codeArr['qty'] > $product->getStockItem()->getQty()) {

                                    $errorValues[] = array('SKU' => $product->getSku(),
                                        'BARCODE' => $codeArr['barcode'],
                                        'WAREHOUSE_ID' => $codeArr['warehouse_warehouse_id'],
                                        'SUPPLIER_ID' => $codeArr['supplier_supplier_id'],
                                        'PURCHASE_ORDER_ID' => $codeArr['purchaseorder_purchase_order_id'],
                                        'PRODUCT_ID' => $pId,
                                        'BARCODE_STATUS' => $codeArr['barcode_status'],
                                        'BARCODE_QTY' => $maxQty
                                    );

                                    $errors = true;
                                    $back = true;
                                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('inventoryplus')->__('The Qty. of product "%s" using this barcode must be equal or less than its Total Qty. in your system: %s item(s).', $product->getName(), $maxQty));
                                }
                            } else {
                                $product = Mage::getModel('catalog/product')->load($pId);
                                $warehouseProduct = Mage::getModel('inventoryplus/warehouse_product')->getCollection()
                                        ->addFieldToFilter('product_id', $pId)
                                        ->addFieldToFilter('warehouse_id', $codeArr['warehouse_warehouse_id'])
                                        ->setPageSize(1)->setCurPage(1)->getFirstItem();
                                $maxQty = (int) $warehouseProduct->getTotalQty();
                                if ($codeArr['qty'] > $warehouseProduct->getTotalQty()) {

                                    $errorValues[] = array('SKU' => $product->getSku(),
                                        'BARCODE' => $codeArr['barcode'],
                                        'WAREHOUSE_ID' => $codeArr['warehouse_warehouse_id'],
                                        'SUPPLIER_ID' => $codeArr['supplier_supplier_id'],
                                        'PURCHASE_ORDER_ID' => $codeArr['purchaseorder_purchase_order_id'],
                                        'PRODUCT_ID' => $pId,
                                        'BARCODE_STATUS' => $codeArr['barcode_status'],
                                        'BARCODE_QTY' => $maxQty
                                    );

                                    $errors = true;
                                    $back = true;

                                    $warehouseName = Mage::getModel('inventoryplus/warehouse')->load($codeArr['warehouse_warehouse_id']);
                                    $str = '(' . $warehouseName->getWarehouseName() . '): ' . $maxQty . ' item(s).';
                                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('inventoryplus')->__('The Qty. of product "%s" using this barcode must be equal or less than its Total Qty. in warehouse %s', $product->getName(), $str));
                                }
                            }

                            //auto generate barcode
                            if (!$errors) {
                                $codeArr['warehouse_warehouse_id'] = array($codeArr['warehouse_warehouse_id']);

                                if ($codeArr['barcode'] == '') {

                                    //check barcode dupplicate
                                    $codeArr['barcode'] = $this->checkDupplicate($barcode);

                                    $barcode[] = $codeArr['barcode'];
                                } else {
                                    //generate barcode by hand
                                    //check barcode already exist
                                    if (!$model->getId()) {
                                        $checkBarcodeExist = Mage::getModel('inventorybarcode/barcode')->load($codeArr['barcode'], 'barcode');
                                        if ($checkBarcodeExist->getId()) {
                                            Mage::getSingleton('adminhtml/session')->addError(
                                                    Mage::helper('inventoryplus')->__('The barcode "%s" was already exist!', $codeArr['barcode'])
                                            );
                                            Mage::getSingleton('adminhtml/session')->setFormData($post);
                                            $this->_redirect('*/*/edit', array('id' => $model->getId()));
                                            return;
                                        }
                                    }



                                    //check barcode dupplicate
                                    if (in_array($codeArr['barcode'], $barcode)) {
                                        Mage::getSingleton('adminhtml/session')->addError(
                                                Mage::helper('inventoryplus')->__('The barcode "%s" was already duplicate!', $codeArr['barcode'])
                                        );
                                        Mage::getSingleton('adminhtml/session')->setFormData($post);
                                        $this->_redirect('*/*/edit', array('id' => $model->getId()));
                                        return;
                                    } else {
                                        $barcode[] = $codeArr['barcode'];
                                    }
                                }

                                $sqlNews[$j] = array(
                                    'barcode' => $codeArr['barcode'],
                                    'barcode_status' => $codeArr['barcode_status'],
                                    'qty' => $codeArr['qty']
                                );

                                foreach ($columns as $id => $column) {
                                    $i = 0;
                                    $columnName = '';

                                    foreach ($column as $_id => $key) {
                                        if ($i == 0)
                                            $columnName = $_id . '_' . $key;
                                        if ($i > 0)
                                            $columnName = $columnName . '_' . $key;

                                        $i++;
                                    }

                                    if ($_id != 'custom') {

                                        $return = Mage::helper('inventorybarcode')->getValueForBarcode($_id, $key, $pId, $codeArr);
                                        if (is_array($return)) {
                                            foreach ($return as $_columns) {
                                                foreach ($_columns as $_column => $value) {
                                                    if (!isset($sqlNews[$_id . '_' . $_column])) {
                                                        $sqlNews[$j][$_id . '_' . $_column] = $value;
                                                    } else {
                                                        $sqlNews[$j][$_id . '_' . $_column] .= ',' . $value;
                                                    }
                                                }
                                            }
                                        } else {
                                            $sqlNews[$j][$columnName] = $return;
                                        }
                                    } else {
                                        if (isset($codeArr[$columnName]))
                                            $sqlNews[$j][$columnName] = $codeArr[$columnName];
                                    }
                                }
                                $sqlNews[$j]['created_date'] = now();
                                $sqlNews[$j]['qty_original'] = $codeArr['qty'];
                                if (count($sqlNews) == 1000) {
                                    $writeConnection->insertMultiple($resource->getTableName('inventorybarcode/barcode'), $sqlNews);
                                    $sqlNews = array();
                                }

                                //create action log
                                Mage::getModel('inventorybarcode/barcode_actionlog')->setData('barcode_action', Mage::helper('inventorybarcode')->__('Account "%s" created barcode "%s"', $admin, $codeArr['barcode']))
                                        ->setData('created_at', now())
                                        ->setData('created_by', $admin)
                                        ->setData('barcode', $codeArr['barcode'])
                                        ->save();
                                $success = true;
                                $j++;
                            }
                        }
                    }
                }

                if (!empty($sqlNews)) {
                    $writeConnection->insertMultiple($resource->getTableName('inventorybarcode/barcode'), $sqlNews);
                }
                if ($back) {
                    Mage::helper('inventorybarcode')->importProduct($errorValues);
                    if ($success) {
                        if (!$this->getRequest()->getParam('id')) {
                            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('inventorybarcode')->__('The barcode(s) have been created successfully.'));
                        } else {
                            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('inventorybarcode')->__('The barcode(s) have been saved successfully.'));
                        }
                    }
                    $this->_redirect('*/*/new');
                    return;
                }

                if ($success) {
                    Mage::getModel('admin/session')->setData('barcode_product_import', null);

                    if ($this->getRequest()->getParam('back')) {
                        if (!$this->getRequest()->getParam('id')) {
                            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('inventorybarcode')->__('The barcode(s) have been created successfully.'));
                        } else {
                            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('inventorybarcode')->__('The barcode(s) have been saved successfully.'));
                        }
                        $this->_redirect('*/*/new');
                        return;
                    }
                    if (!$this->getRequest()->getParam('id')) {
                        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('inventorybarcode')->__('The barcode(s) have been created successfully.'));
                    } else {
                        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('inventorybarcode')->__('The barcode(s) have been saved successfully.'));
                    }
                    $this->_redirect('*/*');
                    return;
                } else {
                    $this->_redirect('*/*/new');
                    return;
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($post);
                $this->_redirect('*/*/edit', array('id' => $model->getId()));
                return;
            }

        Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('inventoryplus')->__('Unable to find barcode to save!')
        );
        $this->_redirect('*/*/');
        }

    }

    /**
     * mass change status for item(s) action
     */
    public function massStatusAction() {
        $barcodeIds = $this->getRequest()->getParam('barcodeIds');

        if (!is_array($barcodeIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($barcodeIds as $barcodeId) {
                    $model = Mage::getSingleton('inventorybarcode/barcode')
                            ->load($barcodeId)
                            ->setBarcodeStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                    if ($this->getRequest()->getParam('status') == 1) {
                        $admin = Mage::getModel('admin/session')->getUser()->getUsername();
                        Mage::getModel('inventorybarcode/barcode_actionlog')->setBarcodeAction(Mage::helper('inventorybarcode')->__('Account "%s" enabled barcode "%s"', $admin, $model->getBarcode()))
                                ->setCreatedAt(now())
                                ->setCreatedBy($admin)
                                ->setBarcode($model->getBarcode())
                                ->save();
                    } else {
                        $admin = Mage::getModel('admin/session')->getUser()->getUsername();
                        Mage::getModel('inventorybarcode/barcode_actionlog')->setBarcodeAction(Mage::helper('inventorybarcode')->__('Account "%s" disabled barcode "%s"', $admin, $model->getBarcode()))
                                ->setCreatedAt(now())
                                ->setCreatedBy($admin)
                                ->setBarcode($model->getBarcode())
                                ->save();
                    }
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($barcodeIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirectReferer();
    }

    /**
     * change attribute code in form
     */
    public function changeattributetypeAction() {
        $result = array();
        $attributeType = $this->getRequest()->getParam('attribute_type');
        $html = Mage::helper('inventorybarcode/attribute')->listBarcodeAttribute($attributeType);
        $result['html'] = $html;
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * export grid item to CSV type
     */
    public function exportCsvAction() {
        $fileName = 'barcodes.csv';
        if($this->_helper()->isMultipleBarcode()) {
            $grid = 'inventorybarcode/adminhtml_barcode_grid';
        } else {
            $grid = 'inventorybarcode/adminhtml_singlebarcode_grid';
        }
        $content = $this->getLayout()
                ->createBlock($grid)
                ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export grid item to XML type
     */
    public function exportXmlAction() {
        $fileName = 'barcodes.xml';
        if($this->_helper()->isMultipleBarcode()) {
            $grid = 'inventorybarcode/adminhtml_barcode_grid';
        } else {
            $grid = 'inventorybarcode/adminhtml_singlebarcode_grid';
        }
        $content = $this->getLayout()
                ->createBlock($grid)
                ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('inventoryplus/settings/barcode/manage_barcode');
    }

    public function productsAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('barcode.edit.tab.products')
                ->setProducts($this->getRequest()->getPost('barcode_products', null));
        $fields = Mage::helper('inventorybarcode/attribute')->getBarcodeProductFields();
        foreach ($fields as $field) {
            $this->getLayout()->getBlock('related_grid_serializer')->addColumnInputName($field);
        }
        $this->getLayout()->getBlock('related_grid_serializer')->addColumnInputName('warehouse_warehouse_id');
        $this->getLayout()->getBlock('related_grid_serializer')->addColumnInputName('supplier_supplier_id');
        $this->getLayout()->getBlock('related_grid_serializer')->addColumnInputName('purchaseorder_purchase_order_id');
        $this->getLayout()->getBlock('related_grid_serializer')->addColumnInputName('barcode_status');
        $this->getLayout()->getBlock('related_grid_serializer')->addColumnInputName('barcode_auto');
        $this->getLayout()->getBlock('related_grid_serializer')->addColumnInputName('barcode');
        $this->getLayout()->getBlock('related_grid_serializer')->addColumnInputName('qty');

        $this->renderLayout();
    }

    public function productsGridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('barcode.edit.tab.products')
                ->setProducts($this->getRequest()->getPost('barcode_products', null));
        $this->renderLayout();
    }

    public function productsfrompoAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('barcode.edit.tab.productsfrompo')
                ->setProducts($this->getRequest()->getPost('barcode_products', null));
        $fields = Mage::helper('inventorybarcode/attribute')->getBarcodeProductFields();
        foreach ($fields as $field) {
            $this->getLayout()->getBlock('related_grid_serializer')->addColumnInputName($field);
        }
        $this->getLayout()->getBlock('related_grid_serializer')->addColumnInputName('warehouse_warehouse_id');
        $this->getLayout()->getBlock('related_grid_serializer')->addColumnInputName('barcode_status');
        $this->getLayout()->getBlock('related_grid_serializer')->addColumnInputName('barcode_auto');
        $this->getLayout()->getBlock('related_grid_serializer')->addColumnInputName('barcode');
        $this->getLayout()->getBlock('related_grid_serializer')->addColumnInputName('qty');

        $this->renderLayout();
    }

    public function productsGridfrompoAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('barcode.edit.tab.productsfrompo')
                ->setProducts($this->getRequest()->getPost('barcode_products', null));
        $this->renderLayout();
    }

    public function historyAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function getImportCsvAction() {
        if (isset($_FILES['fileToUpload']['name']) && $_FILES['fileToUpload']['name'] != '') {

            try {
                $fileName = $_FILES['fileToUpload']['tmp_name'];
                $Object = new Varien_File_Csv();
                $dataFile = $Object->getData($fileName);
                $product = array();
                $products = array();
                $fields = array();
                $count = 0;
                $helper = Mage::helper('inventorybarcode');

                if (count($dataFile))
                    foreach ($dataFile as $col => $row) {
                        if ($col == 0) {
                            if(!empty($row))
                                foreach ($row as $index => $cell)
                                    $fields[$index] = (string) $cell;
                        }elseif ($col > 0) {

                            if(!empty($row))
                                foreach ($row as $index => $cell) {

                                    if (isset($fields[$index])) {
                                        $product[$fields[$index]] = $cell;
                                    }
                                }

                            $productId = Mage::getModel('catalog/product')->getIdBySku($product['SKU']);
                            $product['PRODUCT_ID'] = $productId;

                            if ($productId) {
                                $products[] = $product;
                            }
                        }
                    }

                $helper->importProduct($products);
            } catch (Exception $e) {
                Mage::log($e->getMessage(), null, 'inventory_management.log');
            }
        }
    }

    /**
     * add new barcode from purchase order action
     */
    public function newfrompoAction() {

        $this->_title($this->__('Inventory'))
                ->_title($this->__('Add New Barcode'));

        $this->loadLayout();
        $this->_setActiveMenu($this->_menu_path);

        $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Manage Barcodes'), Mage::helper('adminhtml')->__('Manage Barcodes')
        );
        $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Create Barcodes from Purchase Order'), Mage::helper('adminhtml')->__('Create Barcodes from Purchase Order')
        );

        $this->getLayout()->getBlock('head')
                ->addCss('css/magestore/inventorybarcode/hiddenleftslide.css');

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true)
                ->removeItem('js', 'mage/adminhtml/grid.js')
                ->addItem('js', 'magestore/adminhtml/inventory/grid.js');

        $this->_addContent($this->getLayout()->createBlock('inventorybarcode/adminhtml_barcodefrompo_edit'))
                ->_addLeft($this->getLayout()->createBlock('inventorybarcode/adminhtml_barcodefrompo_edit_tabs'));

        $this->renderLayout();
    }

    /**
     * add information for barcode from purchase order action
     */
    public function prepareAction() {
        $purchaseOrderId = $this->getRequest()->getParam('po_id');

        if ($purchaseOrderId) {
            $this->_title($this->__('Inventory'))
                    ->_title($this->__('Add New Barcode'));

            $this->loadLayout();
            $this->_setActiveMenu('inventoryplus');

            $this->_addBreadcrumb(
                    Mage::helper('adminhtml')->__('Manage Barcodes'), Mage::helper('adminhtml')->__('Manage Barcodes')
            );
            $this->_addBreadcrumb(
                    Mage::helper('adminhtml')->__('Create Barcodes from Purchase Order'), Mage::helper('adminhtml')->__('Create Barcodes from Purchase Order')
            );

            $this->getLayout()->getBlock('head')
                    ->addCss('css/magestore/inventorybarcode/hiddenleftslide.css');

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true)
                    ->removeItem('js', 'mage/adminhtml/grid.js')
                    ->addItem('js', 'magestore/adminhtml/inventory/grid.js');

            $this->_addContent($this->getLayout()->createBlock('inventorybarcode/adminhtml_barcodefrompo_edit'))
                    ->_addLeft($this->getLayout()->createBlock('inventorybarcode/adminhtml_barcodefrompo_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('inventorybarcode')->__('Purchase Order does not exist')
            );
            $this->_redirect('*/*/');
        }
    }

    /**
     * save barcode from PO action
     */
    public function savefrompoAction() {

        if ($post = $this->getRequest()->getPost()) {
            $purchaseOrderId = $this->getRequest()->getParam('po_id');
            $purchaseOrder = Mage::getModel('inventorypurchasing/purchaseorder')->load($purchaseOrderId);
            $suppliererId = $purchaseOrder->getSupplierId();

            $admin = Mage::getModel('admin/session')->getUser()->getUsername();

            try {
                $resource = Mage::getSingleton('core/resource');
                $writeConnection = $resource->getConnection('core_write');
                $sqlNews = array();
                $sqlOlds = '';
                $countSqlOlds = 0;

                $tablename = 'inventorybarcode/barcode';

                $results = Mage::helper('inventorybarcode/attribute')->getAllColumOfTable($tablename);

                $columns = array();
                $string = '';
                $type = '';

                foreach ($results as $result) {
                    $fields = explode('_', $result);
                    if ($fields[0] == 'barcode' || $fields[0] == 'qty')
                        continue;
                    foreach ($fields as $id => $field) {
                        if ($id == 0)
                            $type = $field;
                        if ($id == 1) {
                            $string = $field;
                        }
                        if ($id > 1)
                            $string = $string . '_' . $field;
                    }
                    $columns[] = array($type => $string);
                    $string = '';
                    $type = '';
                }

                if (isset($post['barcode_products'])) {
                    $products = array();
                    $productsExplodes = explode('&', urldecode($post['barcode_products']));

                    if (count($productsExplodes) <= 900) {
                        Mage::helper('inventoryplus')->parseStr(urldecode($post['barcode_products']), $products);
                    } else {
                        foreach ($productsExplodes as $productsExplode) {
                            $product = '';
                            Mage::helper('inventoryplus')->parseStr($supplierProductsExplode, $product);
                            $products = $products + $product;
                        }
                    }

                    if (count($products)) {
                        $productIds = '';
                        $qtys = '';
                        $count = 0;
                        $j = 0;
                        $barcode = array();

                        $success = false;
                        $back = false;
                        $errorValues = array();
                        foreach ($products as $pId => $enCoded) {
                            $errors = false;

                            $codeArr = array();
                            Mage::helper('inventoryplus')->parseStr(Mage::helper('inventoryplus')->base64Decode($enCoded), $codeArr);

                            $codeArr['purchaseorder_purchase_order_id'] = $purchaseOrderId;
                            $codeArr['supplier_supplier_id'] = $suppliererId;

                            // check qty
                            if (!$codeArr['warehouse_warehouse_id']) {
                                $product = Mage::getModel('catalog/product')->load($pId);
                                $maxQty = (int) $product->getStockItem()->getQty();
                                if ($codeArr['qty'] > $product->getStockItem()->getQty()) {
                                    $errorValues[] = array('SKU' => $product->getSku(),
                                        'BARCODE' => $codeArr['barcode'],
                                        'WAREHOUSE_ID' => $codeArr['warehouse_warehouse_id'], 'PRODUCT_ID' => $pId,
                                        'BARCODE_STATUS' => $codeArr['barcode_status'],
                                        'BARCODE_QTY' => $maxQty
                                    );

                                    $errors = true;
                                    $back = true;
                                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('inventoryplus')->__('The Qty. of product "%s" using this barcode must be equal or less than its Total Qty. in your system: %s item(s).', $product->getName(), $maxQty));
                                }
                            } else {
                                $product = Mage::getModel('catalog/product')->load($pId);
                                $warehouseProduct = Mage::getModel('inventoryplus/warehouse_product')->getCollection()
                                        ->addFieldToFilter('product_id', $pId)
                                        ->addFieldToFilter('warehouse_id', $codeArr['warehouse_warehouse_id'])
                                        ->setPageSize(1)->setCurPage(1)->getFirstItem();
                                $maxQty = (int) $warehouseProduct->getTotalQty();
                                if ($codeArr['qty'] > $warehouseProduct->getTotalQty()) {
                                    $errorValues[] = array('SKU' => $product->getSku(),
                                        'BARCODE' => $codeArr['barcode'],
                                        'WAREHOUSE_ID' => $codeArr['warehouse_warehouse_id'], 'PRODUCT_ID' => $pId,
                                        'BARCODE_STATUS' => $codeArr['barcode_status'],
                                        'BARCODE_QTY' => $maxQty
                                    );

                                    $errors = true;
                                    $back = true;

                                    $warehouseName = Mage::getModel('inventoryplus/warehouse')->load($codeArr['warehouse_warehouse_id']);
                                    $str = '(' . $warehouseName->getWarehouseName() . '): ' . $maxQty . ' item(s).';
                                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('inventoryplus')->__('The Qty. of product "%s" using this barcode must be equal or less than its Total Qty. in warehouse %s', $product->getName(), $str));
                                }
                            }
                            if (!$errors) {
                                $codeArr['warehouse_warehouse_id'] = array($codeArr['warehouse_warehouse_id']);
                                //auto generate barcode
                                if ($codeArr['barcode'] == '') {

                                    //check barcode dupplicate                                 
                                    $codeArr['barcode'] = $this->checkDupplicate($barcode);

                                    $barcode[] = $codeArr['barcode'];
                                } else {
                                    //generate barcode by hand
                                    //check barcode already exist

                                    $checkBarcodeExist = Mage::getModel('inventorybarcode/barcode')->load($codeArr['barcode'], 'barcode');

                                    if ($checkBarcodeExist->getId()) {
                                        Mage::getSingleton('adminhtml/session')->addError(
                                                Mage::helper('inventoryplus')->__('The barcode "%s" was already exist!', $codeArr['barcode'])
                                        );
                                        Mage::getSingleton('adminhtml/session')->setFormData($post);
                                        $this->_redirect('*/*/newfrompo');
                                        return;
                                    }

                                    //check barcode dupplicate
                                    if (in_array($codeArr['barcode'], $barcode)) {
                                        Mage::getSingleton('adminhtml/session')->addError(
                                                Mage::helper('inventoryplus')->__('The barcode "%s" was already duplicate!', $codeArr['barcode'])
                                        );
                                        Mage::getSingleton('adminhtml/session')->setFormData($post);
                                        $this->_redirect('*/*/newfrompo');
                                        return;
                                    } else {
                                        $barcode[] = $codeArr['barcode'];
                                    }
                                }

                                $sqlNews[$j] = array(
                                    'barcode' => $codeArr['barcode'],
                                    'barcode_status' => $codeArr['barcode_status'],
                                    'qty' => $codeArr['qty']
                                );

                                foreach ($columns as $id => $column) {
                                    $i = 0;
                                    $columnName = '';

                                    foreach ($column as $_id => $key) {
                                        if ($i == 0)
                                            $columnName = $_id . '_' . $key;
                                        if ($i > 0)
                                            $columnName = $columnName . '_' . $key;

                                        $i++;
                                    }


                                    if ($_id != 'custom') {

                                        $return = Mage::helper('inventorybarcode')->getValueForBarcode($_id, $key, $pId, $codeArr);
                                        if (is_array($return)) {
                                            foreach ($return as $_columns) {
                                                foreach ($_columns as $_column => $value) {
                                                    if (!isset($sqlNews[$_id . '_' . $_column])) {
                                                        $sqlNews[$j][$_id . '_' . $_column] = $value;
                                                    } else {
                                                        $sqlNews[$j][$_id . '_' . $_column] .= ',' . $value;
                                                    }
                                                }
                                            }
                                        } else {
                                            $sqlNews[$j][$columnName] = $return;
                                        }
                                    } else {
                                        if (isset($codeArr[$columnName]))
                                            $sqlNews[$j][$columnName] = $codeArr[$columnName];
                                    }
                                }

                                $sqlNews[$j]['created_date'] = now();
                                $sqlNews[$j]['qty_original'] = $codeArr['qty'];

                                if (count($sqlNews) == 1000) {
                                    $writeConnection->insertMultiple($resource->getTableName('inventorybarcode/barcode'), $sqlNews);
                                    $sqlNews = array();
                                }

                                //create action log
                                Mage::getModel('inventorybarcode/barcode_actionlog')->setData('barcode_action', Mage::helper('inventorybarcode')->__('Account "%s" created barcode "%s"', $admin, $codeArr['barcode']))
                                        ->setData('created_at', now())
                                        ->setData('created_by', $admin)
                                        ->setData('barcode', $codeArr['barcode'])
                                        ->save();

                                $success = true;
                                $j++;
                            }
                        }
                    }
                }

                if (!empty($sqlNews)) {
                    $writeConnection->insertMultiple($resource->getTableName('inventorybarcode/barcode'), $sqlNews);
                }

                if ($back) {
                    Mage::helper('inventorybarcode')->importProduct($errorValues);
                    if ($success) {
                        if (!$this->getRequest()->getParam('id')) {
                            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('inventorybarcode')->__('The barcode(s) have been created successfully.'));
                        } else {
                            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('inventorybarcode')->__('The barcode(s) have been saved successfully.'));
                        }
                    }
                    $this->_redirect('*/*/prepare', array('po_id' => $purchaseOrderId));
                    return;
                }

                if ($success) {
                    Mage::getModel('admin/session')->setData('barcode_product_import', null);

                    if ($this->getRequest()->getParam('back')) {
                        if (!$this->getRequest()->getParam('id')) {
                            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('inventorybarcode')->__('The barcode(s) have been created successfully.'));
                        } else {
                            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('inventorybarcode')->__('The barcode(s) have been saved successfully.'));
                        }
                        $this->_redirect('*/*/newfrompo');
                        return;
                    }
                    if (!$this->getRequest()->getParam('id')) {
                        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('inventorybarcode')->__('The barcode(s) have been created successfully.'));
                    } else {
                        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('inventorybarcode')->__('The barcode(s) have been saved successfully.'));
                    }
                    $this->_redirect('*/*');
                    return;
                } else {
                    $this->_redirect('*/*/new');
                    return;
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($post);
                $this->_redirect('*/*/newfrompo');
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('inventoryplus')->__('Unable to find barcode to save!')
        );
        $this->_redirect('*/*/newfrompo');
    }

    public function massPrintAction() {
        if ($this->getRequest()->getPost('barcode_product_list', null) == null) {
            Mage::getSingleton('adminhtml/session')->addNotice(
                    Mage::helper('inventorybarcode')->__('Please select barcodes before printing.')
            );
            return $this->_redirect('*/*/edit', array('print' => 'true', '_secure' => true));
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    public function _helper() {
        return Mage::helper('inventorybarcode');
    }

    /**
     * Scan item action
     * 
     */
    public function scanAction() {
        $currentAdmin = Mage::getSingleton('admin/session');
        $currentAdminId = $currentAdmin->getUser()->getUserId();
        $result = array();
        $notFound = false;
        $barcodeQuery = $this->getRequest()->getParam('barcode_query');
        $action = $this->getRequest()->getParam('action');
        $action = $action ? $action : 'default';
        $productId = $this->_helper()->getProductIdByBarcode($barcodeQuery);
        $scanItemModel = Mage::getModel('inventorybarcode/barcode_scanitem');
        /* Michael 201602 - add more field to scan product when create delivery */
        $fieldScans = '';
        if((strpos($action, 'delivery_po') != -1) && (strpos($action, 'delivery_po') !== false))
            $fieldScans = Mage::getStoreConfig('inventoryplus/purchasing/delivery_field_scan');
        try {
//            if ($productId) {
//                $product = Mage::getModel('catalog/product')->load($productId);
//                $scanItem = $scanItemModel->addItem($productId, $action);
//                $result['status'] = 1;
//                $result['productId'] = $productId;
//                $result['qty'] = $scanItem->getScanQty();
//                $result['productName'] = $product->getName();
//                $result['productSku'] = $product->getSku();
//                $result['barcode'] = $barcodeQuery;
//                //$result['productImage'] = Mage::helper('catalog/image')->init($product, 'image')->resize(90)->__toString();
//                $result['media'] = Mage::getBaseUrl('media');
//                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
//            }


           if($fieldScans) {
                $fieldScans = explode(',',$fieldScans);
                foreach($fieldScans as $field){
                    if($field == 'supplier_sku') {
                        $supplierProduct = Mage::getModel('inventorypurchasing/supplier_product')
                                                            ->getCollection()
                                                            ->addFieldToFilter('supplier_sku', $barcodeQuery)
                                                            ->setPageSize(1)->setCurPage(1)->getFirstItem();
                        if ($supplierProduct->getProductId()) {
                            $productId = $supplierProduct->getProductId();
                            break;
                        }
                    }else{
                        $product = Mage::getResourceModel('catalog/product_collection')
                                        ->addAttributeToSelect($field)
                                        ->addAttributeToFilter($field, $barcodeQuery)
                                        ->setPageSize(1)->setCurPage(1)->getFirstItem();
                        if($product->getId()) {
                            $productId = $product->getId();
                            break;
                        }
                    }
                }
            }
            if ($productId) {
                $product = Mage::getModel('catalog/product')->load($productId);
                $scanItem = $scanItemModel->addItem($productId, $action);
                $result['status'] = 1;
                $result['productId'] = $productId;
                $result['qty'] = $scanItem->getScanQty();
                $result['productName'] = $product->getName();
                $result['productSku'] = $product->getSku();
                $result['barcode'] = $barcodeQuery;
                //$result['productImage'] = Mage::helper('catalog/image')->init($product, 'image')->resize(90)->__toString();
                $result['media'] = Mage::getBaseUrl('media');
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }else{
                $notFound = true;
            }
            if ($notFound) {
                $block = $this->getLayout()->createBlock('adminhtml/template')
                        ->setTemplate('inventorybarcode/search/autocomplete.phtml');
                $this->getResponse()->setBody($block->toHtml());
            }
        } catch (Exception $e) {
            $result['status'] = 0;
            $result['media'] = Mage::getBaseUrl('media');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            Mage::log($e->getMessage(), null, 'physicalstocktaking.log');
        }
    }

    /**
     * Reset scan data
     * 
     */
    public function resetscanAction() {
        $action = $this->getRequest()->getParam('action');
        $action = $action ? $action : 'default';
        try {
            $scanItems = Mage::getModel('inventorybarcode/barcode_scanitem')->reset($action);
            $result = array('status' => 1);
        } catch (Exception $ex) {
            $result = array('status' => 0, 'message' => $ex->getMessage());
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * reupdate qty product when scan barcode
     * Michael 201602
     */
    public function reupdateqtyAction(){
        $action = $this->getRequest()->getParam('action');
        $action = $action ? $action : 'default';
        $currentAdminId = Mage::getSingleton('admin/session')->getUser()->getUserId();
        $productId = $this->getRequest()->getPost('product_id');
        $productQty = $this->getRequest()->getPost('product_qty_update');
        $item = Mage::getModel('inventorybarcode/barcode_scanitem')->getCollection()
            ->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('user_id', $currentAdminId)
            ->addFieldToFilter('action', $action)
            ->addFieldToFilter('is_finished', Magestore_Inventorybarcode_Model_Barcode_Scanitem::STATUS_PENDING)
            ->setPageSize(1)->setCurPage(1)->getFirstItem();

        $item->setData('product_id', $productId)
            ->setData('user_id', $currentAdminId)
            ->setData('action', $action)
            ->setData('is_finished', Magestore_Inventorybarcode_Model_Barcode_Scanitem::STATUS_PENDING)
            ->setData('scan_qty', $productQty)
            ->setData('last_scanned_at', now())
            ->save();
    }

    /**
     * reupdate qty product when manual edit qty
     * Michael 201602
     */
    public function reupdateqtymanualAction(){
        $allQtyData = $this->getRequest()->getPost('qtydata');
        $action = $this->getRequest()->getParam('action');
        if($allQtyData){
            $allQtyData = explode('&&',$allQtyData);
            foreach($allQtyData as $qtyData){
                $qtyData = explode(';',$qtyData);
                $row = $qtyData[0];
                $productQty = $qtyData[1];
                $row = explode(' ',$row);
                if($row[0] && $productQty != null && $productQty != ''){
                    $pId = explode('row-',$row[0]);
                    if($productId = $pId[1]){
                        $currentAdminId = Mage::getSingleton('admin/session')->getUser()->getUserId();
                        $item = Mage::getModel('inventorybarcode/barcode_scanitem')->getCollection()
                            ->addFieldToFilter('product_id', $productId)
                            ->addFieldToFilter('user_id', $currentAdminId)
                            ->addFieldToFilter('action', $action)
                            ->addFieldToFilter('is_finished', Magestore_Inventorybarcode_Model_Barcode_Scanitem::STATUS_PENDING)
                            ->setPageSize(1)->setCurPage(1)->getFirstItem();
                        if(!$item->getId() && $productQty <= 0)
                            continue;
                        $item->setData('product_id', $productId)
                            ->setData('user_id', $currentAdminId)
                            ->setData('action', $action)
                            ->setData('is_finished', Magestore_Inventorybarcode_Model_Barcode_Scanitem::STATUS_PENDING)
                            ->setData('scan_qty', $productQty)
                            ->setData('last_scanned_at', now())
                            ->save();
                    }
                }
            }
        }

//        $action = $this->getRequest()->getParam('action');
//        $action = $action ? $action : 'default';
//        $currentAdminId = Mage::getSingleton('admin/session')->getUser()->getUserId();
//        $productId = $this->getRequest()->getPost('product_id');
//        $productQty = $this->getRequest()->getPost('product_qty_update');
//        $item = Mage::getModel('inventorybarcode/barcode_scanitem')->getCollection()
//            ->addFieldToFilter('product_id', $productId)
//            ->addFieldToFilter('user_id', $currentAdminId)
//            ->addFieldToFilter('action', $action)
//            ->addFieldToFilter('is_finished', Magestore_Inventorybarcode_Model_Barcode_Scanitem::STATUS_PENDING)
//            ->getFirstItem();
//
//        $item->setData('product_id', $productId)
//            ->setData('user_id', $currentAdminId)
//            ->setData('action', $action)
//            ->setData('is_finished', Magestore_Inventorybarcode_Model_Barcode_Scanitem::STATUS_PENDING)
//            ->setData('scan_qty', $productQty)
//            ->setData('last_scanned_at', now())
//            ->save();
    }



    public function getAjaxQtyAction() {
        $warehouse_id = $this->getRequest()->getParam('warehouseID');
        $product_id = $this->getRequest()->getParam('productID');

        $qty = Mage::helper('inventorybarcode')->checkQtyBarcode($product_id);
        if($qty == 0){
            echo 0;
            return;
        }else{
            $qtySelectBarcode = Mage::helper('inventorybarcode')->getQtyBarcore($product_id,$warehouse_id);
            echo $qtySelectBarcode;
        }
    }
}
