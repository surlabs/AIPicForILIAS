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
    'api_url' => array(
        'type' => 'text',
        'length' => 255,
        'notnull' => true
    ),
    'authentication_key_label' => array(
        'type' => 'text',
        'length' => 255,
        'notnull' => false
    ),
    'authentication_value' => array(
        'type' => 'text',
        'length' => 255,
        'notnull' => false
    ),
    'additional_header_options' => array(
        'type' => 'text',
        'length' => 255,
        'notnull' => false
    ),
    'request_body_prompt' => array(
        'type' => 'text',
        'length' => 255,
        'notnull' => true
    ),
    'prompt_context' => array(
        'type' => 'text',
        'length' => 255,
        'notnull' => false
    ),
    'model' => array(
        'type' => 'text',
        'length' => 255,
        'notnull' => false
    ),
    'additional_body_options' => array(
        'type' => 'text',
        'length' => 255,
        'notnull' => false
    ),
    'response_body_key' => array(
        'type' => 'text',
        'length' => 255,
        'notnull' => true
    ),
    'response_body_subkey' => array(
        'type' => 'text',
        'length' => 255,
        'notnull' => false
    ),
    "created_at" => [
        "type" => "timestamp",
        "notnull" => true
    ],
    "updated_at" => [
        "type" => "timestamp",
        "notnull" => false
    ]
);
if(!$db->tableExists("aip_config")) {
    $db->createTable("aip_config", $fields);
    $db->createSequence('aip_config');
    $db->addPrimaryKey("aip_config", array("id"));
}
?>


