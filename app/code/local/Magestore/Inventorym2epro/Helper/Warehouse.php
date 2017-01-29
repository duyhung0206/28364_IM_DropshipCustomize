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
 * Inventorym2epro Helper
 *
 * @category    Magestore
 * @package     Magestore_Inventorym2epro
 * @author      Magestore Developer
 */
class Magestore_Inventorym2epro_Helper_Warehouse extends Mage_Core_Helper_Abstract
{
    /**
     * Get all enabled warehouses in system
     *
     * @return mixed
     */
    public function getAllWarehouses()
    {
        return Mage::getResourceModel('inventoryplus/warehouse_collection')
            ->addFieldToFilter('status', 1);
    }

    /**
     * Get primary warehouse
     *
     * @return mixed
     */
    public function getPrimaryWarehouse()
    {
        return $this->getAllWarehouses()
            ->addFieldToFilter('is_root', 1)
            ->getFirstItem();
    }

    /**
     * Check if Ebay store is associated with a warehouses
     *
     * @return bool
     */
    public function isEbayLinkedWithWarehouse()
    {
        $result = false;
        if (strcmp(Mage::getStoreConfig('inventoryplus/integrate_m2epro/associated_with', Mage::app()->getStore()->getStoreId()), 2) == 0)
        {
            $result = true;
        }
        return $result;
    }

    /**
     * Get warehouse associated with Ebay store in config
     *
     * @return mixed
     */
    public function getWarehouseForM2ePro()
    {
        if ($this->isEbayLinkedWithWarehouse())
        {
            if (Mage::getStoreConfig('inventoryplus/integrate_m2epro/warehouse', Mage::app()->getStore()->getStoreId()))
            {
                $warehouseId = Mage::getStoreConfig('inventoryplus/integrate_m2epro/warehouse', Mage::app()->getStore()->getStoreId());
                return $warehouseId;
            }
            return $this->getPrimaryWarehouse()->getId();
        }
        return null;
    }

    /**
     * Check if order is created from Ebay or not
     *
     * @param $orderId
     * @return bool
     */
    public function isOrderFromM2ePro($orderId)
    {
        $result = false;
        $m2eProOrdersCollection = Mage::getResourceModel('M2ePro/Order_Collection')
            ->addFieldToFilter('magento_order_id', array('eq' => $orderId));
        if ($m2eProOrdersCollection->getFirstItem()->getId())
        {
            $result = true;
        }
        return $result;
    }
}