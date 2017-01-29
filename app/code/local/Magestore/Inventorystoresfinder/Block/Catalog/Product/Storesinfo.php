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
class Magestore_Inventorystoresfinder_Block_Catalog_Product_Storesinfo extends Mage_Core_Block_Template
{
    public function getWarehousesProducts(){
        $helper = Mage::helper('inventorystoresfinder');
        $productIds = $this->getConfigProductIds();
        $data = $helper->getWarehousesProducts($productIds);
        return $data;
    }
    public function getConfigProductIds(){
        $current_product = Mage::registry('current_product');
        $productIds = array();
        $productType=$current_product->getTypeID();
        if($productType=="configurable"){
            $productIds = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($current_product->getId());
            $productIds = array_values($productIds[0]);
        }
        if($productType=="simple"){
            $productIds[] = $current_product->getId();
        }
        return $productIds;
    }
    
    public function getConfigCustomer(){
        $customerAddressId = Mage::getSingleton('customer/session')->getCustomer()->getDefaultShipping();
        $shippingAddress = "";
        if ($customerAddressId){
            $address = Mage::getModel('customer/address')->load($customerAddressId);
            $address->getData();
            $strests = $address->getStreet();
            $shippingAddress = $strests[0].','.$address->getCity().','.$address->getCountry();
        }
        $customer = array(
            'shipping_address' => $shippingAddress,
            'lat_shipping' => "",
            'lng_shipping' => ""
        );
        return $customer;
    }
    public function getConfigDisplay(){
        $displaySetting = array(
            "showdescription" => Mage::getStoreConfig('inventoryplus/storesfinder/display_show_store_description'),
            "showname" => Mage::getStoreConfig('inventoryplus/storesfinder/display_show_store_name'),
            "showaddress" => Mage::getStoreConfig('inventoryplus/storesfinder/display_show_store_address'),
            "showstocking" => Mage::getStoreConfig('inventoryplus/storesfinder/display_show_store_stocking'),
            "instock_label" => 'In Stock',
            "item_label"  => 'Items',
            "number_item_display" => 2,
        );
        return $displaySetting;
    }
    public function getConfigGeoLocation(){

    }
    public function getConfigStoreDefault(){

    }
    public function getConfigDistanceMatrix(){

    }
    public function getConfigMapEmbed(){

    }
    public function getConfigAPIKey(){
        return  Mage::getStoreConfig('inventoryplus/storesfinder/api_key');
    }
    public function getConfigOrderBy(){
        $orderby = array(
            'type' => Mage::getStoreConfig('inventoryplus/storesfinder/display_orderby_type'),
            'sort' => Mage::getStoreConfig('inventoryplus/storesfinder/display_orderby_sort')
        );
        return $orderby;
    }
    public function getJSONConfigStores(){
        $data = array(
            'customer' => $this->getConfigCustomer(),
            'productIds' => $this->getConfigProductIds(),
            'store_products' => $this->getWarehousesProducts(),
            'DisplaySetting' => $this->getConfigDisplay(),
            'api_key' => $this->getConfigAPIKey(),
            'orderby' => $this->getConfigOrderBy()
        );
        $jsonData = Mage::helper('core')->jsonEncode($data);
        return $jsonData;
    }
}