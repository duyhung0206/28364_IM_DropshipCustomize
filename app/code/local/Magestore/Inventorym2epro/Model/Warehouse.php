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
 * Inventorywarehouse Model
 *
 * @category    Magestore
 * @package     Magestore_Inventorym2epro
 * @author      Magestore Developer
 */
class Magestore_Inventorym2epro_Model_Warehouse
{
    public function toOptionArray()
    {
        $result = array();
        $warehouses = Mage::helper('inventorym2epro/warehouse')->getAllWarehouses();
        foreach ($warehouses as $warehouse) {
            $warehouseData = array(
                'value' => $warehouse->getId(),
                'label' => Mage::helper('inventorym2epro')->__($warehouse->getWarehouseName())
            );
            array_push($result, $warehouseData);
        }

        return $result;
    }
}

