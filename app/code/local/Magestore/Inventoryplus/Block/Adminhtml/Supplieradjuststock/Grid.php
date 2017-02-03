<?php
class Magestore_Inventoryplus_Block_Adminhtml_Supplieradjuststock_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct() {
        parent::__construct();
        $this->setId('supplieradjuststockGrid');
        $this->setDefaultSort('supplier_adjuststock_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
//        $this->setSaveParametersInSession(true);
    }
    
    /**
     * prepare collection for block to display
     *
     * @return Magestore_Inventory_Block_Adminhtml_Supplieradjuststock_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('inventorydropship/supplier_adjuststock')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    /**
     * prepare columns for this grid
     *
     * @return Magestore_Inventory_Block_Adminhtml_Adjuststock_Grid
     */
    protected function _prepareColumns()
    {
       $this->addColumn('supplier_adjuststock_id', array(
            'header' => Mage::helper('inventoryplus')->__('ID'),
            'sortable' => true,
            'width' => '60',
            'type' => 'number',
            'index' => 'supplier_adjuststock_id'
        ));
       
        $this->addColumn('created_at', array(
            'header' => Mage::helper('inventoryplus')->__('Created on'),
            'type'  => 'date',
            'width'     => '150px',
            'index' => 'created_at',
        ));

        $this->addColumn('created_by', array(
            'header' => Mage::helper('inventoryplus')->__('Created by'),
            'type'  => 'text',
            'width'     => '150px',
            'index' => 'created_by',
        ));

        $this->addColumn('warehouse_name', array(
            'header' => Mage::helper('inventoryplus')->__('Adjusted Warehouse'),
            'width'     => '150px',
            'index' => 'warehouse_name'
        ));

        $this->addColumn('confirmed_at', array(
            'header' => Mage::helper('inventoryplus')->__('Confirmed at'),
            'type'  => 'date',
            'width'     => '150px',
            'index' => 'confirmed_at',
        ));

        $this->addColumn('confirmed_by', array(
            'header' => Mage::helper('inventoryplus')->__('Confirmed by'),
            'type'  => 'text',
            'width'     => '150px',
            'index' => 'confirmed_by',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('inventoryplus')->__('Status'),
            'type'  => 'options',
            'width'     => '150px',
            'index' => 'status',
            'options' => array(
                0 => 'Pending',
                1 => 'Completed',
                2 => 'Canceled'
            ),
        ));
      
        $this->addColumn('action',
            array(
                'header'    =>    Mage::helper('inventoryplus')->__('Action'),
                'width'        => '100',
                'type'        => 'action',
                'getter'    => 'getId',
                'actions'    => array(
                    array(
                        'caption'    => Mage::helper('inventoryplus')->__('Edit'),
                        'url'        => array('base'=> '*/*/edit'),
                        'field'        => 'id'
                    )),
                'filter'    => false,
                'sortable'    => false,
                'index'        => 'stores',
                'is_system'    => true,
        ));

        return parent::_prepareColumns();
    }
    
    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

}