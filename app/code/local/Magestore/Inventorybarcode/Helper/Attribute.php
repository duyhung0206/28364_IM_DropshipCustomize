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
 * @package     Magestore_Inventorybarcode
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorybarcode Helper
 * 
 * @category    Magestore
 * @package     Magestore_Inventorybarcode
 * @author      Magestore Developer
 */
class Magestore_Inventorybarcode_Helper_Attribute extends Mage_Core_Helper_Abstract {

    /**
     * get All column
     * 
     * return Array
     */
    public function getAllColumOfTable($model) {
       
        $resource = Mage::getSingleton('core/resource');
        $tablename = $resource->getTableName($model);
        $readConnection = $resource->getConnection('core_read');
        $results = $readConnection->fetchAll("SHOW COLUMNS FROM " . $tablename . ";");
        $return = array();
        foreach ($results as $result) {
            $return[] = $result['Field'];
        }
         
        return $return;
    }

    /**
     * get Html for attribute code
     * 
     * return Html
     */
    public function listBarcodeAttribute($attributeType) {

        $model = Mage::getModel('inventorybarcode/barcodeattribute_type');
        $model->load($attributeType, 'attribute_type');
      
        $html = '';
        $id = Mage::app()->getRequest()->getParam('id');
       
        $value = '';
        if ($id) {
            $barcodeattribute = Mage::getModel('inventorybarcode/barcodeattribute')->load($id);
            $value = explode('_',$barcodeattribute->getAttributeCode());
        }
        $select = '';

        switch ($attributeType) {
            case 'custom':
                if($value){
                    $html .= '<input type="text" class="required-entry input-text required-entry" value="'.$value[1].'" name="attribute_code" id="attribute_code">';
                }else{
                    $html .= '<input type="text" class="required-entry input-text required-entry" value="" name="attribute_code" id="attribute_code">';
                }
                break;
            case 'product':
                $attributes = Mage::getSingleton('eav/config')
                        ->getEntityType(Mage_Catalog_Model_Product::ENTITY)->getAttributeCollection()
                        ->addFieldToFilter('apply_to', array('like' => '%simple%'));
                
                $html .='<select class=" select" name="attribute_code" id="attribute_code">';
                foreach ($attributes as $attribute) {  
                    if ($value && $attribute->getAttributeCode()==$value[1]) {
                        $select = 'selected = "selected"';                    
                    $html .='<option '.$select.' value="' . $attribute->getAttributeCode() . '">' . $attribute->getAttributeCode() . '</option>';
                    }else{
                        $html .='<option value="' . $attribute->getAttributeCode() . '">' . $attribute->getAttributeCode() . '</option>';
                    }
                }
                $html .='</select>';
                break;
            default:                
                $attributes = $this->getAllColumOfTable($model->getModel());
              
                $html .= '<select class=" select" name="attribute_code" id="attribute_code">';
                foreach ($attributes as $attribute) {
                    if($value)
                        foreach($value as $id => $key){
                            if($id==1){
                                $check = $key;
                            }
                            if($id > 1){
                                $check .= '_'.$key;
                            }
                        }
                    
                    if ($value && $attribute==$check) {
                        $select = 'selected = "selected"';
                    
                        $html .='<option '.$select.' value="' . $attribute . '">' . $attribute . '</option>';
                    }else{
                        $html .='<option value="' . $attribute . '">' . $attribute . '</option>';
                    }
                }
                $html .='</select>';
                break;
        }


        return $html;
    }
    
    public function getBarcodeProductFields(){
        $resource = Mage::getSingleton('core/resource');
        $tablename = $resource->getTableName('inventorybarcode/barcode');
        $readConnection = $resource->getConnection('core_read');
        $results = $readConnection->fetchAll("SHOW COLUMNS FROM " . $tablename . ";");
      
        $return = array();
        foreach($results as $id => $result){
            if($id==0)
                continue;
            
            $return[] = $result['Field'];
        }
        
        return $return;
    }

    public function getBarcodeAttributes($type = null)
    {
        $attributeTypes = array(
            'product' => array(
                'model' => $this->__('catalog/product'),
                'codes' => array(
                    'product_entity_id' => array(
                        'name' => 'Product Id',
                        'display' => 0,
                        'unique' => 0,
                        'require' => 0,
                        'status' => 1
                    ),
                    'product_name' => array(
                        'name' => 'Product Name',
                        'display' => 1,
                        'unique' => 0,
                        'require' => 0,
                        'status' => 1
                    ),
                    'product_sku' => array(
                        'name' => 'Product Sku',
                        'display' => 1,
                        'unique' => 0,
                        'require' => 0,
                        'status' => 1
                    ),
                    'product_price' => array(
                        'name' => 'Product Price',
                        'display' => 0,
                        'unique' => 0,
                        'require' => 0,
                        'status' => 1
                    )
                )
            ),
            'warehouse' => array(
                'model' => $this->__('inventoryplus/warehouse'),
                'codes' => array(
                    'warehouse_warehouse_id' => array(
                        'name' => 'Warehouse Id',
                        'display' => 0,
                        'unique' => 0,
                        'require' => 0,
                        'status' => 1
                    ),
                    'warehouse_warehouse_name' => array(
                        'name' => 'Warehouse Name',
                        'display' => 1,
                        'unique' => 0,
                        'require' => 0,
                        'status' => 1
                    )
                )
            ),
            'supplier' => array(
                'model' => $this->__('inventorypurchasing/supplier'),
                'codes' => array(
                    'supplier_supplier_id' => array(
                        'name' => 'Supplier Id',
                        'display' => 0,
                        'unique' => 0,
                        'require' => 0,
                        'status' => 1
                    ),
                    'supplier_supplier_name' => array(
                        'name' => 'Supplier Name',
                        'display' => 0,
                        'unique' => 0,
                        'require' => 0,
                        'status' => 1
                    )
                )
            ),
            'purchaseorder' => array(
                'model' => $this->__('inventorypurchasing/purchaseorder'),
                'codes' => array(
                    'purchaseorder_purchase_order_id' => array(
                        'name' => 'Purchase Order Id',
                        'display' => 0,
                        'unique' => 0,
                        'require' => 0,
                        'status' => 1
                    )
                )
            ),
            'custom' => array(
                'model' => $this->__(''),
            )
        );

        return $type ? (isset($attributeTypes[$type]) ? $attributeTypes[$type] : null) : $attributeTypes;
    }

}
