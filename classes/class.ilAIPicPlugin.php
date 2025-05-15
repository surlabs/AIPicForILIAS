<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

include_once("./Services/COPage/classes/class.ilPageComponentPlugin.php");

class ilAIPicPlugin extends ilPageComponentPlugin
{
    public const ID = "xaip";
    private static $instance;
    private UploadServiceAIPicGUI $uploader;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            global $DIC;

            $component_repository = $DIC["component.repository"];
            $info = $component_repository->getPluginByName("AIPic");
            $component_factory = $DIC["component.factory"];
            $plugin_obj = $component_factory->getPlugin($info->getId());

            self::$instance = $plugin_obj;
        }

        return self::$instance;
    }
    function getComponentType(): string
    {
        return self::ID;
    }


    function getClass(): string
    {
        return "ilAIPicGUI";
    }

    protected function uninstallCustom() : void
    {
        // TODO: Nothing to do here.
    }


    function getPluginName(): string
    {
        return "AIPic";
    }

    public function isValidParentType(string $a_type): bool
    {
        return true;
    }

    public function isValidObjectType($a_type): bool
    {
        return true;
    }

    public function onDelete(array $a_properties, string $a_plugin_version, bool $move_operation = false): void
    {
        $this->uploader = new UploadServiceAIPicGUI();
        if($a_properties["imageId"] != null) {
            $this->uploader->removeFromOutside($a_properties["imageId"]);
        }
    }

}