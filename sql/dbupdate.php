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

<#2>
<?php
/**
 * Migration step: convert legacy IRSS UUID imageIds to ilObjMediaObject integer IDs.
 *
 * In older versions of AIPic, images were stored via ILIAS Resource Storage Service (IRSS)
 * and the imageId property contained a UUID string (e.g. "abc12-...").
 * In the current version, images are stored as ilObjMediaObject and imageId is a numeric string.
 *
 * This step scans all page_object entries that contain an AIPic plugin element and,
 * for each element whose imageId looks like an IRSS UUID, attempts to:
 *   1. Retrieve the file from IRSS.
 *   2. Create a new ilObjMediaObject with that file.
 *   3. Replace the UUID imageId with the new integer mob ID in the page XML.
 *
 * Elements whose IRSS resource no longer exists are left unchanged (the defensive PHP
 * code in ilAIPicPluginGUI will handle them gracefully at runtime).
 */
global $DIC;
$db = $DIC->database();

// Find all page_object rows whose XML contains an AIPic Plugged element
$result = $db->queryF(
    "SELECT id, parent_type, lang FROM page_object WHERE content LIKE %s",
    ['text'],
    ['%PluginName="AIPic"%']
);

$rows = [];
while ($row = $db->fetchAssoc($result)) {
    $rows[] = $row;
}

foreach ($rows as $row) {
    $page_id     = (int) $row['id'];
    $parent_type = (string) $row['parent_type'];
    $lang        = (string) $row['lang'];

    // Re-fetch the full content for this row
    $contentRes = $db->queryF(
        "SELECT content FROM page_object WHERE id = %s AND parent_type = %s AND lang = %s",
        ['integer', 'text', 'text'],
        [$page_id, $parent_type, $lang]
    );
    $contentRow = $db->fetchAssoc($contentRes);
    if (!$contentRow || empty($contentRow['content'])) {
        continue;
    }

    $xml_content = $contentRow['content'];

    // Parse the page XML
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    if (!$dom->loadXML($xml_content)) {
        libxml_clear_errors();
        continue;
    }
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);
    // Search for AIPic Plugged elements regardless of namespace
    $plugged_nodes = $xpath->query('//*[local-name()="Plugged" and @PluginName="AIPic"]');
    if ($plugged_nodes->length === 0) {
        continue;
    }

    $page_updated = false;

    foreach ($plugged_nodes as $plugged_node) {
        // Collect all PluginProperty nodes for this element
        $prop_nodes = $xpath->query('.//*[local-name()="PluginProperty"]', $plugged_node);

        $imageId = null;
        $imageIdNode = null;
        $fileNameNode = null;

        foreach ($prop_nodes as $prop_node) {
            $name = $prop_node->getAttribute('Name');
            if ($name === 'imageId') {
                $imageId     = $prop_node->getAttribute('Value');
                $imageIdNode = $prop_node;
            }
            if ($name === 'fileName') {
                $fileNameNode = $prop_node;
            }
        }

        if ($imageId === null || $imageIdNode === null) {
            continue;
        }

        // Already new format (pure integer) → nothing to do
        if (ctype_digit(trim($imageId))) {
            continue;
        }

        // Legacy UUID format → try to migrate via IRSS
        try {
            $irss = $DIC->resourceStorage();
            $rid  = $irss->manage()->find($imageId);

            if ($rid === null) {
                // IRSS resource no longer exists; leave as-is, PHP defensive code handles it
                continue;
            }

            // Create a new ilObjMediaObject to hold the migrated image
            $mob = new ilObjMediaObject();
            $mob->setTitle('AIPic_migrated');
            $mob->create();
            $mob->createDirectory();

            // Determine a safe filename from the revision info
            $revision  = $irss->manage()->getCurrentRevision($rid);
            $file_name = 'aipic_migrated_' . $mob->getId() . '.png';
            $dest_file = ilObjMediaObject::_getDirectory($mob->getId()) . '/' . $file_name;

            // Stream the file content from IRSS into the mob directory
            $stream = $irss->consume()->stream($rid)->getStream();
            file_put_contents($dest_file, $stream->getContents());

            // Register the media item
            $mediaItem = $mob->addMediaItemFromLocalFile('Standard', $dest_file, $file_name);
            $mediaItem->setNr(1);
            $mob->update();

            // Update the imageId property node with the new integer mob ID
            $imageIdNode->setAttribute('Value', (string) $mob->getId());

            // Also update fileName if present (it stored the same UUID)
            if ($fileNameNode !== null) {
                $fileNameNode->setAttribute('Value', (string) $mob->getId());
            }

            // Optionally remove the now-obsolete IRSS resource
            try {
                $irss->manage()->remove($rid, new StorageStakeHolderAIPic());
            } catch (Throwable $ignored) {
                // Non-critical: if removal fails the IRSS resource stays orphaned
            }

            $page_updated = true;

        } catch (Throwable $e) {
            // Migration failed for this element; defensive PHP will handle it at runtime
            continue;
        }
    }

    if ($page_updated) {
        // Serialize the modified DOM back to XML string
        $new_content = $dom->saveXML($dom->documentElement);
        $db->update(
            'page_object',
            ['content' => ['text', $new_content]],
            [
                'id'          => ['integer', $page_id],
                'parent_type' => ['text', $parent_type],
                'lang'        => ['text', $lang],
            ]
        );
    }
}
?>

