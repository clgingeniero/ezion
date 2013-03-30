<?php

$installer = $this;

$installer->addAttribute('customer', 'capture_uuid', array(
    'type' => 'varchar',
    'input' => 'text',
    'label' => 'Capture UUID',
    'visible' => false,
    'required' => false,
    'position' => 69,
));
