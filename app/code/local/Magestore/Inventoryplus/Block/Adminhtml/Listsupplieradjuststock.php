<?php

class Magestore_Inventoryplus_Block_Adminhtml_Listsupplieradjuststock extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_supplieradjuststock';
        $this->_blockGroup = 'inventoryplus';
        $this->_headerText = Mage::helper('inventoryplus')->__('Manage Stock Adjustments');
        parent::__construct();
        $this->_removeButton('add');
    }
}