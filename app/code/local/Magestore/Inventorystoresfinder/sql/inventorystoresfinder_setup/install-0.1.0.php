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
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();


$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('inventorystoresfinder/warehouse_location')};
    CREATE TABLE {$this->getTable('inventorystoresfinder/warehouse_location')} (
        `location_id` int(11) unsigned NOT NULL auto_increment,
        `warehouse_id` int(11) unsigned NOT NULL UNIQUE,
        `warehouse_description` TEXT ,
        `lat` DECIMAL (20,16),
        `lng` DECIMAL (20,16), 
        `is_store` tinyint(1) NOT NULL default '1',
        PRIMARY KEY  (`location_id`),
        FOREIGN KEY (`warehouse_id`) REFERENCES {$this->getTable('inventoryplus/warehouse')}(`warehouse_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$installer->endSetup();