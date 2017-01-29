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
class Magestore_Inventorystoresfinder_Model_Observer {
    public function warehouseSaveAfter($observer){
        $params = Mage::app()->getRequest()->getParams();
        $warehouse = $observer->getEvent()->getWarehouse();
        $model = Mage::getModel('inventorystoresfinder/warehouse_location')->load($warehouse->getId(),'warehouse_id');
        try {
            $model->setWarehouseId($warehouse->getId());
            $model->setLat($params['lat']);
            $model->setLng($params['lng']);
            $model->setIsStore($params['is_store']);
            $model->save();
        }catch (Exception $e){
            Mage::log($e);
        }
    }
    public function addWarehouseMap($observer){
        $helper = Mage::helper('inventorystoresfinder');
        $event = $observer->getEvent();
        $block = $event->getBlock();
        $form = $block->getForm();
        $warehouse_data = Mage::registry('warehouse_data');
        if(($warehouse_id = $warehouse_data['warehouse_id']) && $warehouse_data['street'] ){

            $locationWarehouse = Mage::getModel('inventorystoresfinder/warehouse_location')->load($warehouse_id,'warehouse_id');
            $data = $locationWarehouse->getData();
            if(!$data['lat'] || !$data['lng']){
                $location = Mage::helper('inventorystoresfinder')->getWarehouseLocation($warehouse_id,'warehouse_id');
                $data['lat'] = $location['lat'];
                $data['lng'] = $location['lng'];
            }
            $fieldset = $form->addFieldset('shop_infomation', array(
                    'legend' => $helper->__('Shop Infomation'),
                    'class' => 'fieldset-wide',
                    'expanded'  => true,
                )
            );
            $yesnoSource = Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray();
            $fieldset->addField('lat', 'hidden', array(
                'label' => Mage::helper('inventoryplus')->__('Latitude'),
                'name' => 'lat',
            ));
            $fieldset->addField('lng', 'hidden', array(
                'label' => Mage::helper('inventoryplus')->__('Longitude'),
                'name' => 'lng',
            ));
            $fieldset->addField('is_store', 'select', array(
                'label' => Mage::helper('inventoryplus')->__('Warehouse is Shop'),
                'class' => 'required-entry',
                'values' => $yesnoSource,
                'required' => true,
                'name' => 'is_store',
            ));
            $fieldset->addType('map', 'Magestore_Inventorystoresfinder_Block_Adminhtml_Warehouse_Edit_Tab_Form_Renderer_Map');
            $fieldset->addField('map', 'map', array('label' => Mage::helper('inventoryplus')->__('Map')));
            $form->addValues($data);
        }

    }
}