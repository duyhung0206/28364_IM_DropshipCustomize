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

$installer = $this;

$installer->startSetup();

$installer->run("
    CREATE TABLE IF NOT EXISTS {$this->getTable('erp_inventory_supplier_adjuststock')} (
            `supplier_adjuststock_id` int(11) unsigned NOT NULL auto_increment,
            `supplier_id` int(11) unsigned NOT NULL,
            `warehouse_id` int(11) unsigned NOT NULL,
            `warehouse_name` varchar(255) NOT NULL,
            `reason` text NOT NULL,
            `created_by` varchar(255) default NULL,
            `created_at` date default NULL,
            `confirmed_by` varchar(255) default NULL,
            `confirmed_at` date default NULL,
            `status` tinyint(1) NOT NULL,	
            PRIMARY KEY(`supplier_adjuststock_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    
    CREATE TABLE IF NOT EXISTS {$this->getTable('erp_inventory_supplier_adjuststock_product')} (
                `supplier_adjuststock_product_id` int(11) unsigned NOT NULL auto_increment,
                `supplier_adjuststock_id` int(11) unsigned  NOT NULL,
                `product_id` int(11) unsigned  NOT NULL,
                `adjust_qty` decimal(10,0) default '0',
                PRIMARY KEY  (`supplier_adjuststock_product_id`),
                INDEX(`supplier_adjuststock_id`),
                FOREIGN KEY (`supplier_adjuststock_id`) REFERENCES {$this->getTable('erp_inventory_supplier_adjuststock')}(`supplier_adjuststock_id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();