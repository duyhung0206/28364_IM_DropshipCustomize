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

class Magestore_Inventorystoresfinder_AjaxController extends Mage_Core_Controller_Front_Action {
    public function loadWarehouseAction(){
        $id = $this->getRequest()->getParam('id');

        $collection = Mage::getModel('inventoryplus/warehouse_product')->getCollection();
        $collection->addFieldToFilter('product_id',$id);
        $collection->addFieldToFilter('status',1);
        $collection->addFieldToFilter('is_store',1);
        $collection->getSelect()->joinLeft(
            array('warehouse' => $collection->getTable('inventoryplus/warehouse')), 'main_table.warehouse_id = warehouse.warehouse_id', array('*')
        )->joinLeft(
            array('location' => $collection->getTable('inventorystoresfinder/warehouse_location')), 'main_table.warehouse_id = location.warehouse_id', array('*')
        );
        $data = $collection->getData();
        $result = array();
        if($data){
            $result = array('results'=>$data, 'status'=>'OK');
        }else{
            $result = array('results'=>$data, 'status'=>'ZERO_RESULTS');
        }

        $jsonData = Mage::helper('core')->jsonEncode($result);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }
}