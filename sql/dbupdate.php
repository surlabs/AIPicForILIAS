<#1>
<?php

global $DIC;
$db = $DIC->database();

$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'is_online' => array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false
	),
	'option_one' => array(
		'type' => 'text',
		'length' => 10,
		'fixed' => false,
		'notnull' => false
	),
	'option_two' => array(
		'type' => 'text',
		'length' => 10,
		'fixed' => false,
		'notnull' => false
	)
);
if(!$db->tableExists("rep_robj_xaig_data")) {
    $db->createTable("rep_robj_xaig_data", $fields);
    $db->addPrimaryKey("rep_robj_xaig_data", array("id"));
}
?>