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
class Magestore_Inventoryfulfillment_Block_Adminhtml_Fulfillment_Shipprogressing_Renderer_Shipping extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $shippingProgress = $this->getShippingProgress();
        $backgroundColor = $this->getBackgroundColor();
        $html = '<div style="background-color: '.$backgroundColor[$row->getShippingProgress()].'; color: #FFFFFF;">'.$shippingProgress[$row->getShippingProgress()].'</div>';
        $htmlExport = $shippingProgress[$row->getShippingProgress()];
        if(in_array(Mage::app()->getRequest()->getActionName(),array('exportCsv','exportExcel')))
            return $htmlExport;
        return $html;
    }

    //get shipping progress

    public function getShippingProgress()
    {
        return array(
            0 => Mage::helper('inventoryshipment')->__('Not shipped'),
            1 => Mage::helper('inventoryshipment')->__('Partially shipped'),
            2 => Mage::helper('inventoryshipment')->__('Complete'),
            3 => Mage::helper('inventoryshipment')->__('Canceled'),
            4 => Mage::helper('inventoryshipment')->__('Closed')
        );
    }


    //get Background color
    public function getBackgroundColor()
    {
        return array(
            '0' => '#FF0000',
            '1' => '#0000FF',
            '2' => '#008000',
            '3' => '#800000',
            '4' => '#000000',
        );
    }
}