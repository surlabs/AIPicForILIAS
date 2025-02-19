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
	'prompt' => array(
		'type' => 'text',
		'length' => 255,
		'notnull' => false
	),
	'image_identifier' => array(
		'type' => 'text',
		'length' => 255,
		'notnull' => false
	)
);
if(!$db->tableExists("aimage_generator")) {
    $db->createTable("aimage_generator", $fields);
    $db->addPrimaryKey("aimage_generator", array("id"));
}
?>
<#2>
<?php

global $DIC;
$db = $DIC->database();

$fields = array(
    'id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
    ),
    'prompt' => array(
        'type' => 'text',
        'length' => 255,
        'notnull' => false
    ),
    'image_identifier' => array(
        'type' => 'text',
        'length' => 255,
        'notnull' => false
    )
);
if(!$db->tableExists("aimage_generator")) {
    $db->createTable("aimage_generator", $fields);
    $db->addPrimaryKey("aimage_generator", array("id"));
}
?>

<#3>
<?php
global $DIC;
$db = $DIC->database();
if ($db->tableExists('aimage_generator')) {
    $db->addTableColumn('aimage_generator', 'obj_id', [
        "type" => "integer",
        "length" => 8,
        "notnull" => true
    ]);

    $db->addTableColumn('aimage_generator', 'user_id', [
        "type" => "integer",
        "length" => 8,
        "notnull" => true
    ]);
}
?>

<#4>
<?php
global $DIC;
$db = $DIC->database();
if ($db->tableExists('aimage_generator')) {
    $db->addTableColumn('aimage_generator', 'created_at', [
        "type" => "timestamp",
        "notnull" => true
    ]);

    $db->addTableColumn('aimage_generator', 'updated_at', [
        "type" => "timestamp",
        "notnull" => false
    ]);
}
?>

<#5>
<?php
global $DIC;
$db = $DIC->database();
if ($db->tableExists('aimage_generator')) {
    $db->createSequence('aimage_generator');
}
?>