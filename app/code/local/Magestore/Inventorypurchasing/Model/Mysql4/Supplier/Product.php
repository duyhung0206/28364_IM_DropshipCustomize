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
 * @package     Magestore_Inventorywarehouse
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorywarehouse Resource Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventorywarehouse
 * @author      Magestore Developer
 */
class Magestore_Inventorypurchasing_Model_Mysql4_Supplier_Product extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct() {
        $this->_init('inventorypurchasing/supplier_product', 'supplier_product_id');
    }

    /**
     * Get product item in supplier
     * 
     * @param int $productId
     * @param int $supplierId
     * @return array
     */
    public function getItem($productId, $supplierId) {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
                ->from($this->getMainTable())
                ->where('supplier_id = :supplier_id')
                ->where('product_id = :product_id');
        $bind = array(':supplier_id' => $supplierId, ':product_id' => $productId);

        $query = $adapter->query($select, $bind);
        while ($row = $query->fetch()) {
            return $row;
        }
    }

    /**
     * 
     * @param int $productId
     * @param int $warehouseId
     */
    public function insertItem($productId, $supplierId) {
        $adapter = $this->_getWriteAdapter();
        $adapter->insert($this->getMainTable(), array(
            'supplier_id' => $supplierId,
            'product_id' => $productId
        ));
    }

}
