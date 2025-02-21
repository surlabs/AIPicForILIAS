<?php

/**
 * @ilCtrl_isCalledBy ilAImageGeneratorPluginGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI, ilAImageGeneratorEditorGUI, ilPCPluggedGUI
 * @ilCtrl_Calls      ilAImageGeneratorPluginGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI, ilExportGUI, ilAImageGeneratorEditorGUI, ilObjRootFolderGUI
 */

use ILIAS\ResourceStorage\Flavour\Definition\PagesToExtract;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Input\Container\Form\Standard;
use platform\AImageGeneratorConfig;

class ilAImageGeneratorPluginGUI extends ilPageComponentPluginGUI
{
    public const LP_SESSION_ID = 'xaig_lp_session_state';
    private Renderer $renderer;
    private ilCtrlInterface $ctrl;
    private ilGlobalTemplateInterface $tpl;
    private UploadServiceAImageGeneratorGUI $uploader;

    private ilAImageGeneratorEditorGUI $editorGUI;

    protected static int $id_counter = 0;

    public function __construct()
    {
        parent::__construct();

        global $DIC;
        $this->uploader = new UploadServiceAImageGeneratorGUI();
        $this->renderer = $DIC->ui()->renderer();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();

    }

    public function update(): void
    {
        global $tpl, $DIC, $ilCtrl;
        $this->editorGUI = new ilAImageGeneratorEditorGUI($this->plugin, $this->generateImageCreator());
        $form = $this->editorGUI->getPromptForm();
        $form = $form->withRequest($DIC->http()->request());
        $result = $form->getData();
        if($form->getError() === null) {
            if(isset($result) && count($result) > 0 && count($result[0]['file']) > 0) {
                $result[0]['imageId'] = $result[0]['file'][0];
                $properties["legacyFileName"] = $this->uploader->getInfoResult($result[0]["file"][0])->getName();
                $properties["fileName"] = $result[0]["file"][0];
                unset($result[0]['file']);
            } else {

                $result[0]['imageId'] = $this->getProperties()["imageId"];
                unset($result[0]['file']);
            }

            $this->updateElement($result[0]);
            $this->returnToParent();
        } else {
            $this->tpl->addJavaScript("./Customizing/global/plugins/Services/COPage/PageComponent/AImageGenerator/templates/js/downloadImage.js");
            $this->tpl->addJavaScript("./Customizing/global/plugins/Services/COPage/PageComponent/AImageGenerator/templates/js/callSaveEndpoint.js");
            $this->tpl->addJavaScript("./Customizing/global/plugins/Services/COPage/PageComponent/AImageGenerator/templates/js/resendForm.js");
            $this->tpl->addCss("./Customizing/global/plugins/Services/COPage/PageComponent/AImageGenerator/templates/css/aimageGenerator_sheet.css");
            $this->tpl->setContent($this->renderer->render($form));
        }
    }


    // PageComponent methods

    /**
     * @throws ilCtrlException
     */
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

    /**
     * @throws Exception
     */
    private function generateImageCreator(): AImageGeneratorRequestInterface {
        $config = new AImageGeneratorConfig();
        $config->loadFromDB();
        $aimageGeneratorProvider = AImageGeneratorRequestImpl::from($config);

        return $aimageGeneratorProvider;
    }

    /**
     * @throws ilCtrlException
     * @throws Exception
     */
    public function insert(): void
    {
        global $tpl, $DIC, $ilCtrl;
        $DIC->logger()->root()->info("FORM LOADED");
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

            if(isset($result) && count($result) > 0 && count($result[0]['file']) > 0) {
                $result[0]['imageId'] = $result[0]['file'][0];

                $properties["legacyFileName"] = $this->uploader->getInfoResult($result[0]["file"][0])->getName();
                $properties["fileName"] = $result[0]["file"][0];
                unset($result[0]['file']);
                $this->createElement($result[0]);
            }

            $this->returnToParent();
        } else {
            // Check if we can download the image
            $this->editorGUI->manageDownloadImage();

            // Generate the image div
            $image = $this->editorGUI->generateImage();

            $res = $this->editorGUI->renderForm($form) .
                $image;
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
        global $tpl, $DIC, $ilCtrl;

        $this->editorGUI = new ilAImageGeneratorEditorGUI($this->plugin, $this->generateImageCreator());
        $form = $this->editorGUI->getPromptFormWithProperties($this->getProperties());
        //dump($form->getInputs()->getValues());exit();
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

    /**
     * @throws ilCtrlException
     */
    public function create(): void
    {
        $this->ctrl->redirect($this, 'insert');
    }

    /**
     * @throws ilTemplateException
     */
    public function getElementHTML(string $a_mode, array $a_properties, string $plugin_version): string
    {
        global $DIC;
        $this->editorGUI = new ilAImageGeneratorEditorGUI($this->plugin, $this->generateImageCreator());

        $old_path = ILIAS_WEB_DIR . '/' . CLIENT_ID . "/AImageGenerator/" . $a_properties["imageId"];

        if (!file_exists($old_path)) {
            $irss = $DIC->resourceStorage();
            $file_name = $irss->consume()->src(new ResourceIdentification($a_properties["imageId"]))->getSrc();

        } else {
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