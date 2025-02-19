<?php

/**
 * @ilCtrl_isCalledBy ilAImageGeneratorPluginGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI, ilAImageGeneratorEditorGUI, ilPCPluggedGUI
 * @ilCtrl_Calls      ilAImageGeneratorPluginGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI, ilExportGUI, ilAImageGeneratorEditorGUI, ilObjRootFolderGUI
 */

use ILIAS\ResourceStorage\Flavour\Definition\PagesToExtract;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Input\Container\Form\Standard;

class ilAImageGeneratorPluginGUI extends ilPageComponentPluginGUI
{
    public const LP_SESSION_ID = 'xaig_lp_session_state';
    private Factory $factory;
    private Renderer $renderer;
    private ilCtrlInterface $ctrl;
    private ilGlobalTemplateInterface $tpl;
    private ilTabsGUI $tabs;
    private UploadServiceAImageGeneratorGUI $uploader;

    private ilAImageGeneratorEditorGUI $editorGUI;

    protected static int $id_counter = 0;

    public function __construct()
    {
        parent::__construct();

        global $DIC;

        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tabs = $DIC->tabs();

    }

    public function update(): void
    {
        global $tpl, $DIC, $ilCtrl;
        $this->editorGUI = new ilAImageGeneratorEditorGUI($this->plugin, $this->generateImageCreator());
        $form = $this->editorGUI->getPromptForm();
        $form = $form->withRequest($DIC->http()->request());
        $result = $form->getData();
        //dump($result);exit();

        if(isset($result) && count($result) > 0 && count($result[0]['file']) > 0) {
            //dump($result[0]);exit();
            $result[0]['imageId'] = $result[0]['file'][0];
            $result[0]['file'] = null;
            $this->updateElement($result[0]);
        }
        $this->returnToParent();
        //dump($form->getInputs()[0]->getValue());exit();
    }

    protected function setSubTabs(string $active) {
        $this->tabs->addSubTab(
            "subtab_generic_settings",
            $this->plugin->txt("subtab_generic_settings"),
            $this->ctrl->getLinkTarget($this, "edit")
        );

        $this->tabs->addSubTab(
            "subtab_advanced_settings",
            $this->plugin->txt("subtab_advanced_settings"),
            $this->ctrl->getLinkTarget($this, "edit")
        );

        $this->tabs->activateSubTab($active);
    }


    // PageComponent methods

    public function executeCommand(): void
    {
        global $ilCtrl;
        $next_class = $ilCtrl->getNextClass();

        switch($next_class)
        {
            default:
                // perform valid commands
                $cmd = $ilCtrl->getCmd();
                if (in_array($cmd, array("create", "save", "edit", "edit2", "update", "cancel", "sendPrompt", "insert", "saveImage", "update")))
                {
                    $this->$cmd();
                }
                break;
        }
    }

    private function generateImageCreator(): AImageGeneratorRequestInterface {
        $url = 'https://api.openai.com/v1/images/generations';
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer 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'
        ];
        $aimageGeneratorProvider = new AImageGeneratorRequestImpl($url, $headers, null);
        return $aimageGeneratorProvider;
    }

    /**
     * @throws ilCtrlException
     */
    public function insert(): void
    {
        global $tpl, $DIC, $ilCtrl;

        $this->editorGUI = new ilAImageGeneratorEditorGUI($this->plugin, $this->generateImageCreator());
        $form = $this->editorGUI->getPromptForm();
        $request = $DIC->http()->request();
        $query = $DIC->http()->wrapper()->query();
        $refinery = $DIC->refinery();
        $action = $query->retrieve("cmd", $refinery->to()->string());
        $actionDesired = $query->has("methodDesired") ? $query->retrieve("methodDesired", $refinery->to()->string()) : null;

        $ilCtrl->setParameterByClass('ilAImageGeneratorPluginGUI', 'methodDesired', 'sendPrompt');

        if ($request->getMethod() == "POST" && $action != "post" && $actionDesired == "saveImage") {
            $this->editorGUI->saveImage();
            $request = $DIC->http()->request();
            $form = $this->editorGUI->getPromptForm();
            $form = $form->withRequest($request);
            $result = $form->getData();
            //dump($result);exit();

            if(isset($result) && count($result) > 0 && count($result[0]['file']) > 0) {
                //dump($result[0]);exit();
                $result[0]['imageId'] = $result[0]['file'][0];
                $result[0]['file'] = null;
                $this->createElement($result[0]);
            }
            $this->returnToParent();
        } else {
            // Check if we can download the image
            $this->editorGUI->manageDownloadImage();

            // Generate the image div
            $image = $this->editorGUI->generateImage();

            $res = $this->editorGUI->renderForm($form) .
                $image
            ;

            $tpl->addJavaScript("./Customizing/global/plugins/Services/COPage/PageComponent/AImageGenerator/templates/js/downloadImage.js");
            $tpl->addJavaScript("./Customizing/global/plugins/Services/COPage/PageComponent/AImageGenerator/templates/js/callSaveEndpoint.js");
            $tpl->addJavaScript("./Customizing/global/plugins/Services/COPage/PageComponent/AImageGenerator/templates/js/resendForm.js");
            $tpl->addCss("./Customizing/global/plugins/Services/COPage/PageComponent/AImageGenerator/templates/css/aimageGenerator_sheet.css");

            $this->tpl->setContent($res);
        }
    }

    public function edit(): void
    {
        //$this->setSubTabs("subtab_generic_settings");
        global $tpl, $DIC, $ilCtrl;

        $this->editorGUI = new ilAImageGeneratorEditorGUI($this->plugin, $this->generateImageCreator());
        $form = $this->editorGUI->getPromptFormWithProperties($this->getProperties());
        $irss = $DIC->resourceStorage();
        $file_name = $irss->consume()->src(new ResourceIdentification($this->getProperties()["imageId"]))->getSrc();
        $image = $this->editorGUI->generateImage($file_name ?? null);

        $res = $this->renderer->render($form) .
            $image
        ;

        $tpl->addJavaScript("./Customizing/global/plugins/Services/COPage/PageComponent/AImageGenerator/templates/js/downloadImage.js");
        $tpl->addJavaScript("./Customizing/global/plugins/Services/COPage/PageComponent/AImageGenerator/templates/js/callSaveEndpoint.js");
        $tpl->addJavaScript("./Customizing/global/plugins/Services/COPage/PageComponent/AImageGenerator/templates/js/resendForm.js");
        $tpl->addCss("./Customizing/global/plugins/Services/COPage/PageComponent/AImageGenerator/templates/css/aimageGenerator_sheet.css");

        $this->tpl->setContent($res);

        //$this->tpl->setContent($this->renderer->render($form));
    }

    public function create(): void
    {
        $this->ctrl->redirect($this, 'insert');
    }

    public function getElementHTML(string $a_mode, array $a_properties, string $plugin_version): string
    {
        global $DIC;
        $lng = $DIC->language();
        $this->editorGUI = new ilAImageGeneratorEditorGUI($this->plugin, $this->generateImageCreator());

        $old_path = ILIAS_WEB_DIR . '/' . CLIENT_ID . "/AImageGenerator/" . $a_properties["imageId"];

        if (!file_exists($old_path)) {
            $irss = $DIC->resourceStorage();
            $a_properties["old"] = false;
            $file_name = $irss->consume()->src(new ResourceIdentification($a_properties["imageId"]))->getSrc();

        } else {
            $a_properties["old"] = true;
            $file_name = $old_path;
        }
        $a_properties["fileName"] = $file_name;

        $tpl = new ilTemplate("aimage_generator_element.html", true, true, "Customizing/global/plugins/Services/COPage/PageComponent/AImageGenerator");

        $tpl->setVariable("ID", date_create()->format('Y-m-d_H-i-s'));
        $tpl->setVariable("SCALE_WRAPPER_WIDTH", $a_properties["widthInput"]);

        $raw_alignment = $a_properties["aligments"] ?? "left";
        $alignment = empty($raw_alignment) ? "left" : $raw_alignment;
        $tpl->setVariable("ALIGNMENT", $alignment);

        $tpl->setVariable("TEMPLATES_DIR", "Customizing/global/plugins/Services/COPage/PageComponent/AImageGenerator/templates");
        $tpl->setVariable("PLUGIN_DIR", "Customizing/global/plugins/Services/COPage/PageComponent/AImageGenerator");
        $tpl->setVariable("IMAGE_URL", $a_properties["fileName"]);

        $tpl->setVariable("PROPERTIES", json_encode($a_properties));


        return $tpl->get();
    }
}