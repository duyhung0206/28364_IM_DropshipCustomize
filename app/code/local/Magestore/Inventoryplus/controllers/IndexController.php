<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Magestore_Inventoryplus_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $dropshipId = 1;
        $dropship = Mage::getModel('inventorydropship/inventorydropship')->load($dropshipId);
        $supplierId = $dropship->getSupplierId();

        $supplier = Mage::getModel('inventorypurchasing/supplier')->load($supplierId);
        $templateId = Mage::getStoreConfig('inventoryplus/dropship/email_admin_to_customer');
        $mailTemplate = Mage::getModel('core/email_template');
        $translate = Mage::getSingleton('core/translate');
        $order = Mage::getModel('sales/order')->load($dropship->getOrderId());

        $from_email = $dropship->getAdminEmail();
        $from_name = $dropship->getAdminName();
        $customer_email = $order->getData('customer_email');
        $customer_name = $order->getData('customer_firstname').' '.$order->getData('customer_lastname').'';
        $sender = array('email' => $from_email, 'name' => $from_name);
        $receipientEmail = $customer_email;
        $receipientName = $customer_name;
        $mailTemplate
            ->setTemplateSubject('Notify to customer about drop ship')
            ->sendTransactional(
                $templateId, $sender, $receipientEmail, $receipientName, array(
                    'dropship' => $dropship,
                    'shipment_title' => explode(';',$dropship->getTrackingInformation())[0],
                    'delivery_tracking_number' => explode(';',$dropship->getTrackingInformation())[1],
                    'receipient_name' => $receipientName,
                    'supplier' => $supplier,
                )
            );
        $translate->setTranslateInline(true);
    }
}