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
 * Inventory Adjust Stock Edit Block
 * 
 * @category     Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */
class Magestore_Inventoryplus_Block_Adminhtml_Supplieradjuststock_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'inventoryplus';
        $this->_controller = 'adminhtml_supplieradjuststock';
        $supplier_adjuststock_id = $this->getRequest()->getParam('id');
        $supplier_adjuststock = Mage::getModel('inventorydropship/supplier_adjuststock')->load($supplier_adjuststock_id);
        if($supplier_adjuststock->getStatus() == 0){
            $this->_addButton('cancel', array('label' => Mage::helper('inventoryplus')->__('Cancel'), 'class' => 'cancel', 'onclick' => 'cancelAction()'));
            $this->_addButton('confirm', array('label' => Mage::helper('inventoryplus')->__('Confirm'), 'class' => 'save', 'onclick' => 'confirmAction()'));
        }
        $this->removeButton('delete');
        $this->removeButton('reset');
        $this->removeButton('save');

        $this->_formScripts[] = "
            
            function confirmAction(){
                var r=confirm(\"" . Mage::helper('inventoryplus')->__('Are you sure you want to confirm this supplier stock adjustment? This comfirmation will instantly update product\'s Qty. in this warehouse.') . "\");  
                if (r==true)
                    editForm.submit($('edit_form').action+'confirm/1/');
            }

            function cancelAction(){
                var r=confirm('" . Mage::helper('inventoryplus')->__('Are you sure you want to cancel this supplier stock adjustment?') . "');  
                if (r==true){
                    editForm.submit($('edit_form').action+'cancel/1/');
                }
            }

        ";
    }

    /**
     * get text to show in header when edit an item
     *
     * @return string
     */
    public function getHeaderText() {
        if (Mage::registry('supplieradjuststock_data') && Mage::registry('supplieradjuststock_data')->getId()
        ) {
            return Mage::helper('inventoryplus')->__("View Supplier Stock Adjustment No. '%s'", $this->htmlEscape(Mage::registry('supplieradjuststock_data')->getId())
            );
        }
    }

}
