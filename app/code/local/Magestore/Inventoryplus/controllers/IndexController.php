<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Magestore_Inventoryplus_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $collection = Mage::getModel('cataloginventory/stock_item')->getCollection()->addFieldToFilter('product_id', 384);
        Zend_Debug::dump($collection->getData());
    }
}