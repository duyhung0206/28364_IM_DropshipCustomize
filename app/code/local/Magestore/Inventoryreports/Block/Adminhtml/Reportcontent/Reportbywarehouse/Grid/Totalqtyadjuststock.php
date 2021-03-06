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
 * @package     Magestore_Inventorysupplyneeds
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventoryreports Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryreports
 * @author      Magestore Developer
 */
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbywarehouse_Grid_Totalqtyadjuststock extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('totalqtyadjuststockgrid');
        $this->setDefaultSort('total_adjust');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection() {
        $filterData = new Varien_Object();
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        $resource =Mage::getModel('core/resource');

        if (empty($requestData)) {
            $requestData = Mage::Helper('inventoryreports')->getDefaultOptionsWarehouse();
        }
        $gettime = Mage::Helper('inventoryreports')->getTimeSelected($requestData);

        if (!$requestData['warehouse_select']) {  //All Warehouses
            $collection = Mage::getModel('inventoryplus/adjuststock')->getCollection();
            $collection->getSelect()
                    ->join(array('adjust_product' => $resource->getTableName('inventoryplus/adjuststock_product')), 'main_table.adjuststock_id = adjust_product.adjuststock_id', array('SUM(adjust_product.adjust_qty) as total_adjust'))
                    ->where('main_table.status = 1 AND main_table.confirmed_at BETWEEN "' . $gettime['date_from'] . '" and "' . $gettime['date_to'] . '"');
            $collection->groupBy('main_table.warehouse_name');
        } else {   //  WAREHOUSE SELECTED
            $prodNameAttrId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product','name');
            $collection = Mage::getModel('inventoryplus/adjuststock_product')->getCollection();
            $collection->getSelect()
                    ->joinLeft(array('adjust' => $resource->getTableName('inventoryplus/adjuststock')), 'main_table.adjuststock_id = adjust.adjuststock_id', array('SUM(main_table.adjust_qty) as total_adjust'))
                    ->joinLeft(array('flat' => $resource->getTableName('catalog_product_entity_varchar')), 'main_table.product_id = flat.entity_id AND flat.attribute_id='.$prodNameAttrId, array('flat.value as name'))
                    ->where('adjust.warehouse_id = ' . $requestData['warehouse_select'] . ' AND adjust.status = 1 AND adjust.confirmed_at BETWEEN "' . $gettime['date_from'] . '" and "' . $gettime['date_to'] . '"');
            $collection->groupBy('main_table.product_id');
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare columns for this grid
     *
     * @return Magestore_Inventory_Block_Adminhtml_Inventory_Grid
     */
    protected function _prepareColumns() {
        $filterData = new Varien_Object();
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        if (empty($requestData)) {
            $requestData = Mage::Helper('inventoryreports')->getDefaultOptionsWarehouse();
        }
//                Zend_Debug::dump($requestData);die;
        $warehouseId = $requestData['warehouse_select'];
        if (!$warehouseId) {  //All Warehouses
            $this->addColumn('warehouse_name', array(
                'header' => Mage::helper('inventoryreports')->__('Warehouse Name'),
                'align' => 'left',
                'index' => 'warehouse_name',
            ));
            $this->addColumn('total_adjust', array(
                'header' => Mage::helper('inventoryreports')->__('Total Adjusted Qty'),
                'align' => 'right',
                'index' => 'total_adjust',
                'type' => 'number',
                'width' => '100px',
                'filter_condition_callback' => array($this, '_filterCallback'),
            ));
        } else {  //Warehouse Selected
            $this->addColumn('name', array(
                'header' => Mage::helper('inventoryreports')->__('Product Name'),
                'align' => 'left',
                'index' => 'name',
                'filter_condition_callback' => array($this, '_filterCallback'),
            ));

            $this->addColumn('total_adjust', array(
                'header' => Mage::helper('inventoryreports')->__('Total Adjusted Qty'),
                'align' => 'right',
                'index' => 'total_adjust',
                'type' => 'number',
                'width' => '100px',
                'filter_condition_callback' => array($this, '_filterCallback'),
            ));
        }
//        $this->addExportType('*/*/exportCsv', Mage::helper('inventoryreports')->__('CSV'));
//        $this->addExportType('*/*/exportXml', Mage::helper('inventoryreports')->__('XML'));

        return parent::_prepareColumns();
    }

    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row) {
//        return $this->getUrl('*/*/view', array('id' => $row->getId()));
    }

    public function getGridUrl() {
        return $this->getUrl('adminhtml/inr_report/totalqtyadjuststockgrid', array('top_filter' => $this->getRequest()->getParam('top_filter')));
    }

    public function _filterCallback($collection, $column) {
        $filter = $column->getFilter()->getValue();
        $filterData = $this->getFilterData();
        $arr = array();
        foreach ($collection as $item) {
            $fieldValue = $item->getData($column->getId());
            $pass = TRUE;
            if (!is_array($filter)) {
                if (strpos(strtolower($fieldValue), strtolower($filter)) === false) {
                    $pass = FALSE;
                }
            }
            if (isset($filter['from']) && $filter['from'] >= 0) {
                if (floatval($fieldValue) < floatval($filter['from'])) {
                    $pass = FALSE;
                }
            }
            if ($pass) {
                if (isset($filter['to']) && $filter['to'] >= 0) {
                    if (floatval($fieldValue) > floatval($filter['to'])) {
                        $pass = FALSE;
                    }
                }
            }
            if ($pass) {
                $item->setData($column->getId(),$fieldValue);
                $arr[] = $item;
            }
        }
        $temp = Mage::helper('inventoryreports')->_tempCollection(); // A blank collection 
        $count = count($arr);
        for ($i = 0; $i < $count; $i++) {
            $temp->addItem($arr[$i]);
        }
        $this->setCollection($temp);
    }

}
