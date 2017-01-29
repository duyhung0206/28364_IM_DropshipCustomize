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
 * Inventorywarehouse Helper
 * 
 * @category    Magestore
 * @package     Magestore_Inventorydropship
 * @author      Magestore Developer
 */
class Magestore_Inventorydropship_Helper_Dropshipment extends Magestore_Inventoryplus_Helper_Shipment
{
	protected $_observerOb;
	protected $_postParams;
	protected $_shipmentOb;
	protected $_orderOb;
	
	public function sendObject($observer,$postParams,$shipment,$order){
		$this->_observerOb	= $observer;
		$this->_postParams	= $postParams;
		$this->_shipmentOb	= $shipment;
		$this->_orderOb	= $order;
	}
	/*
	 *	Check for break sales_order_shipment_save_after events
	 *	@param	var observer
	 *	@return	boolean
	 */
	public function isIgnoreObserver(){
		$ignore = true;
		$data = Mage::app()->getRequest()->getParams();
		if (isset($data['echeck_dropship']) && $data['echeck_dropship'] == 1)$ignore = false;
		return $ignore;
	}
	
	/*
	 *	Update available qty and on hold qty
	 *	@param	var orderItem, var data, var qtyShipped 
	 *	@return	null
	 */
	public function _updateOnHoldAndAvailableQty($product_id,$qtyShipped){
		$warehouseOr = Mage::getModel('inventoryplus/warehouse_order')->getCollection()
                            ->addFieldToFilter('order_id', $this->_orderOb->getId())
                            ->addFieldToFilter('product_id', $product_id)
							->setPageSize(1)
							->setCurPage(1)
                            ->getFirstItem();
		if($warehouseOr->getId()){					
			$OnHoldQty = $warehouseOr->getQty() - $qtyShipped;
			$warehouseId = $warehouseOr->getWarehouseId();
			if ($OnHoldQty >= 0) {
				$warehouseOr->setQty($OnHoldQty)
						->save();
			} else {
				$warehouseOr->setQty(0)
						->save();
			}
			$warehousePr = Mage::getModel('inventoryplus/warehouse_product')->getCollection()
                            ->addFieldToFilter('warehouse_id', $warehouseId)
                            ->addFieldToFilter('product_id', $product_id)
							->setPageSize(1)
							->setCurPage(1)
                            ->getFirstItem();
			if($warehousePr->getId()){
				$newAvailQty = $warehousePr->getAvailableQty()	+ $qtyShipped;
				$warehousePr->setAvailableQty($newAvailQty);
				$warehousePr->save();
			}
		}
	}
	/*
	 *	Update catalog qty 
	 *	@param	var product_id, var qtyShipped
	 *	@return	null
	 */
	public function _updateCatalogQty($product_id){
		$results = Mage::getResourceModel('inventorydropship/inventorydropship')->getSumTotalAvailableQtyByProduct($product_id);
		$newQty = $results[0]['total_avail_qty'];
		$product = Mage::getModel('catalog/product')->load($product_id);
		$stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id);
		$stockItem->setQty($newQty);
		$stockItem->save();
		$product->save();
	}
	/*
	 *	get prepare and save warehouse_shipment model
	 *	@param	var message,var orderItem, var product_id, var qtyShipped
	 *	@return	null
	 */
	public function _saveModelWarehouseShipment($message,$orderItem,$product_id,$qtyShipped){
		$inventoryShipmentModel = Mage::getModel('inventoryplus/warehouse_shipment');
		$inventoryShipmentModel->setItemId($orderItem->getId())
				->setProductId($product_id)
				->setOrderId($this->_orderOb->getId())
				->setWarehouseId(0)
				->setWarehouseName($message)
				->setShipmentId($this->_shipmentOb->getId())
				->setQtyShipped($qtyShipped)
				->setSubtotalShipped($orderItem->getPrice() * $qtyShipped);
		try{
			$inventoryShipmentModel->save();
		}catch(Exception $e){
			try{
				$data = $inventoryShipmentModel->getData();
				if (!$data['warehouse_shipment_id']) {
					Mage::getResourceModel('inventorydropship/inventorydropship')->insertDataToShipment($data);
				}
			}catch(Exception $e){
				Mage::log($e->getMessage(), null, 'inventory_management.log');
			}
		}
	}
}