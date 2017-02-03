<?php

class Magestore_Inventoryplus_Block_Adminhtml_Supplieradjuststock_Edit_Tab_Products extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('supplieradjuststockproductGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }


    protected function _prepareCollection() {
        $supplier_adjuststock_id = $this->getRequest()->getParam('id');
        $supplier_adjuststock = Mage::getModel('inventorydropship/supplier_adjuststock')->load($supplier_adjuststock_id);
        $supplier_adjuststock_product = Mage::getModel('inventorydropship/supplier_adjuststock_product')->getCollection()
            ->addFieldToFilter('supplier_adjuststock_id', $supplier_adjuststock_id);
        $listProduct = array();
        foreach ($supplier_adjuststock_product as $item){
            $listProduct[] = $item->getProductId();
        }
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('entity_id', array('in' => $listProduct))
            ->addAttributeToFilter('type_id', array('nin' => array('configurable', 'bundle', 'grouped')));
        $collection->joinField('adjust_qty', 'inventorydropship/supplier_adjuststock_product', 'adjust_qty', 'product_id=entity_id', '{{table}}.supplier_adjuststock_id=' . $supplier_adjuststock_id, 'left');
        $collection->joinField('qty_warehouse', 'inventoryplus/warehouse_product', 'total_qty', 'product_id=entity_id', '{{table}}.warehouse_id=' . $supplier_adjuststock->getWarehouseId(), 'left');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $model = Mage::getModel('inventoryplus/adjuststock')->load($this->getRequest()->getParam('id'));
        $currencyCode = Mage::app()->getStore()->getBaseCurrency()->getCode();

        $this->addColumn('entity_id', array(
            'header' => Mage::helper('catalog')->__('ID'),
            'sortable' => true,
            'width' => '60',
            'index' => 'entity_id'
        ));


        $this->addColumn('product_name', array(
            'header' => Mage::helper('catalog')->__('Name'),
            'align' => 'left',
            'index' => 'name',
        ));

        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

        $this->addColumn('set_name', array(
            'header' => Mage::helper('catalog')->__('Attrib. Set Name'),
            'width' => '100px',
            'index' => 'attribute_set_id',
            'type' => 'options',
            'options' => $sets,
        ));

        $this->addColumn('product_status', array(
            'header' => Mage::helper('catalog')->__('Status'),
            'width' => '90px',
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));
        $this->addColumn('product_sku', array(
            'header' => Mage::helper('catalog')->__('SKU'),
            'width' => '80px',
            'index' => 'sku'
        ));

        if (!$this->_isExport) {
            $this->addColumn('product_image', array(
                'header' => Mage::helper('catalog')->__('Image'),
                'width' => '90px',
                'filter' => false,
                'renderer' => 'inventoryplus/adminhtml_renderer_productimage'
            ));
        }

        $this->addColumn('product_price', array(
            'header' => Mage::helper('catalog')->__('Price'),
            'type' => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index' => 'price'
        ));

        $supplier_adjuststock_id = $this->getRequest()->getParam('id');
        $supplier_adjuststock = Mage::getModel('inventorydropship/supplier_adjuststock')->load($supplier_adjuststock_id);
        if($supplier_adjuststock->getStatus() == 0)
            $this->addColumn('qty_warehouse', array(
                'header' => Mage::helper('inventoryplus')->__('Qty in warehouse'),
                'name' => 'qty_warehouse',
                'type' => 'number',
                'index' => 'qty_warehouse',
            ));
        $this->addColumn('adjust_qty', array(
            'header' => Mage::helper('inventoryplus')->__('Adjusted Qty'),
            'name' => 'adjust_qty',
            'type' => 'number',
            'index' => 'adjust_qty',
        ));

    }

    public function getGridUrl() {
        return $this->getUrl('*/*/productGrid', array(
            '_current' => true,
            'id' => $this->getRequest()->getParam('id'),
            'store' => $this->getRequest()->getParam('store')
        ));
    }

    public function getRowUrl($row) {
        return false;
    }


    public function getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

}
