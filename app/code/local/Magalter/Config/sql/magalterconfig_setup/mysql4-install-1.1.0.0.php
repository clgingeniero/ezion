<?php


$installer = $this;

$installer->startSetup();

$installer->run("

CREATE TABLE IF NOT EXISTS {$this->getTable('magalterconfig/storage')} (
  `id` int(11) NOT NULL auto_increment,
  `code` varchar(255) NOT NULL,
  `value` TEXT,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

");

$installer->endSetup(); 