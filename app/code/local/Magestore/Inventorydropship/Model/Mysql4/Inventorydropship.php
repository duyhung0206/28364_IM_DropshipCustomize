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
 * @package     Magestore_Inventorydropship
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorydropship Resource Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventorydropship
 * @author      Magestore Developer
 */
class Magestore_Inventorydropship_Model_Mysql4_Inventorydropship extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('inventorydropship/inventorydropship', 'dropship_id');
    }

    /**
     * @param $supplierId
     * @return bool
     */
    public function checkSupplerId($supplierId)
    {
        $adapter = $this->_getReadAdapter();
        $bind    = array('supplier_id' => (int)$supplierId);
        $select  = $adapter->select()
            ->from($this->getTable('inventorypurchasing/supplier'), 'supplier_id')
            ->where('supplier_id = :supplier_id')
            ->limit(1);

        $result = $adapter->fetchOne($select, $bind);
        if ($result) {
            return true;
        }
        return false;
    }

    /**
     * @param $itemId
     * @return array
     */
    public function getParentItemIdByItem($itemId)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
                          ->from($this->getTable('sales/order_item'), 'parent_item_id')
                          ->where('item_id = ?', $itemId);
        $query = $adapter->query($select);
        $parentIds = array();
        while ($row = $query->fetch()) {
            $parentIds[] = $row;
        }
        return $parentIds;
    }

    /**
     * @param $productId
     * @return array
     */
    public function getQtyProductByProduct($productId)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
                          ->from($this->getTable('cataloginventory_stock_item'), 'qty')
                          ->where('product_id = ?', $productId);
        $query = $adapter->query($select);
        $qty = array();
        while ($row = $query->fetch()) {
            $qty[] = $row;
        }
        return $qty;
    }

    /**
     * @param $product_id
     * @param $order_id
     * @return array
     */
    public function getDropShip($product_id , $order_id)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from(array('dropship' => $this->getTable('inventorydropship/inventorydropship'), array('*')))
            ->joinInner(
                array('dropship_product' => $this->getTable('inventorydropship/inventorydropship_product')),
                'dropship.dropship_id = dropship_product.dropship_id',
                array()
            )
            ->where('dropship.order_id = ?', $order_id)
            ->where('dropship_product.product_id = ?', $product_id);
        $query = $adapter->query($select);
        $dropships = array();
        while ($row = $query->fetch()) {
            $dropships[] = $row;
        }
        return $dropships;
    }

    /**
     * @param $product_id
     * @return array
     */
    public function getSumTotalAvailableQtyByProduct($product_id)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->getTable('erp_inventory_warehouse_product'), array('total_avail_qty' => 'SUM(available_qty)'))
            ->where('product_id = ?', $product_id);
        $query = $adapter->query($select);
        $availableQty = array();
        while ($row = $query->fetch()) {
            $availableQty[] = $row;
        }
        return $availableQty;
    }

    public function insertDataToShipment($data)
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->insertMultiple($this->getTable('erp_inventory_warehouse_shipment'), $data);
    }
}