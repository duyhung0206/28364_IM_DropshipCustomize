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
class Magestore_Inventorystoresfinder_Helper_Data extends Mage_Core_Helper_Abstract {
    public function getWarehouseAddress($warehouseId){
        $warehouse = Mage::getModel('inventoryplus/warehouse')->load($warehouseId);
        if(!($street = $warehouse->getStreet()) || !($city = $warehouse->getCity()) || !($country = $warehouse->getCountryId())){
            return false;
        }
        $warehouseAddress = $warehouse->getStreet().','.$warehouse->getCity().','. $warehouse->getCountryId();
        return $warehouseAddress;
    }
    public function getWarehouseLocation($warehouseId){
        $warehouseLocation = Mage::getModel('inventorystoresfinder/warehouse_location')->load($warehouseId,'warehouse_id');
        if( ($lat = $warehouseLocation->getLat()) && ($lng = $warehouseLocation->getLng())){
            return array('lat'=>$lat,'lng'=>$lng);
        }
        $warehouseAddress = $this->getWarehouseAddress($warehouseId);
        if($warehouseAddress){
            $api_key = Mage::getStoreConfig('inventoryplus/storesfinder/api_key');
            $url = "https://maps.googleapis.com/maps/api/geocode/json?address=".urlencode($warehouseAddress)."&key=".$api_key;
            $json = file_get_contents($url);
            $data = json_decode($json, true);
            if( strcasecmp($data['status'], 'Ok') == 0  ){
                $address = $data['results'][0];
                $location = $address['geometry']['location'];
                return $location;
            }
            return false;
        }
        return false;
    }

    public function getWarehousesProducts($productIds){
        $collection = Mage::getModel('inventoryplus/warehouse_product')->getCollection();
        $collection->addFieldToSelect(array('product_id','available_qty'));
        $collection->addFieldToFilter('product_id',array('in' => array($productIds)) );
        $collection->addFieldToFilter('status',1);
        $collection->addFieldToFilter('is_store',1);
        $collection->getSelect()->joinLeft(
            array('warehouse' => $collection->getTable('inventoryplus/warehouse')), 'main_table.warehouse_id = warehouse.warehouse_id', array('warehouse_id','warehouse_name','street','city','country_id','state','state_id')
        )->joinLeft(
            array('storesfinder' => $collection->getTable('inventorystoresfinder/warehouse_location')), 'main_table.warehouse_id = storesfinder.warehouse_id', array('lat','lng')
        );
        $data = $collection->getData();
        return $data;
    }

}