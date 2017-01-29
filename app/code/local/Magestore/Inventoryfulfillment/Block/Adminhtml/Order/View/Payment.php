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
 * @package     Magestore_Inventoryfulfillment
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventoryfulfillment Edit Block
 * 
 * @category     Magestore
 * @package     Magestore_Inventoryfulfillment
 * @author      Magestore Developer
 */
class Magestore_Inventoryfulfillment_Block_Adminhtml_Order_View_Payment
    extends Magestore_Inventoryfulfillment_Block_Adminhtml_Order_View_Abstract
{
    
    protected function _prepareLayout() {
        parent::_prepareLayout();
        $this->setTemplate('inventoryfulfillment/order/view/payment.phtml');
    }
    
    public function getPaymentHtml(){
        $payment = $this->getLayout()->createBlock('adminhtml/sales_order_payment');
        $payment->setParentBlock($this);
        return $payment->toHtml();
    }
    
    /*alan customize Show payment status in Order & Stock Availability Verification */
    
    public function getgrandTotal(){
        $grandTotals = $this->getOrder()->getGrandTotal();
        $grandTotals = Mage::helper('core')->currency($grandTotals, true, false);
        return $grandTotals;
    }
    public function gettotalPaid(){
        $totalPaid = $this->getOrder()->getTotalPaid();
        $totalPaid = Mage::helper('core')->currency($totalPaid, true, false);
        return $totalPaid;
    }
    public function gettotalRefunded(){
        $totalRefunded = $this->getOrder()->getTotalRefunded();
        $totalRefunded = Mage::helper('core')->currency($totalRefunded, true, false);
        return $totalRefunded;
    }
    public function gettotalDue(){
        $totalDue = $this->getOrder()->getTotalDue();
        $totalDue = Mage::helper('core')->currency($totalDue, true, false);
        return $totalDue;
    }
    /*end customize Show payment status in Order & Stock Availability Verification */
}