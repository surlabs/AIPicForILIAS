<?php

/**
 * @ilCtrl_isCalledBy ilAIPicPluginGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI, ilAIPicEditorGUI, ilPCPluggedGUI
 * @ilCtrl_Calls      ilAIPicPluginGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI, ilExportGUI, ilAIPicEditorGUI, ilObjRootFolderGUI
 */

use ILIAS\ResourceStorage\Flavour\Definition\PagesToExtract;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Input\Container\Form\Standard;
use platform\AIPicConfig;

class  ilAIPicPluginGUI extends ilPageComponentPluginGUI
{
    public const LP_SESSION_ID = 'xaip_lp_session_state';
    private Renderer $renderer;
    private ilCtrlInterface $ctrl;
    private ilGlobalTemplateInterface $tpl;
    private UploadServiceAIPicGUI $uploader;
    private ilAIPicEditorGUI $editorGUI;
    protected static int $id_counter = 0;

    public function __construct()
    {
        parent::__construct();

        global $DIC;
        $this->uploader = new UploadServiceAIPicGUI();
        $this->renderer = $DIC->ui()->renderer();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();

    }

    public function update(): void
    {
        global $tpl, $DIC, $ilCtrl;

        $this->editorGUI = new ilAIPicEditorGUI($this->plugin, $this->generateImageCreator());
        $form = $this->editorGUI->getPromptForm();
        $form = $form->withRequest($DIC->http()->request());
        $result = $form->getData();
        if($form->getError() === null) {
            if(isset($result) && count($result) > 0 && count($result[0]['file']) > 0) {
                if(array_key_exists("imageId", $this->getProperties())) {
                    $this->uploader->removeFromOutside($this->getProperties()["imageId"]);
                }
                $result[0]['imageId'] = $result[0]['file'][0];
                $result["legacyFileName"] = $this->uploader->getInfoResult($result[0]["file"][0])->getName();
                $result["fileName"] = $result[0]["file"][0];
                unset($result[0]['file']);
            } else {

                $result[0]['imageId'] = $this->getProperties()["imageId"];
                unset($result[0]['file']);
            }

            $this->updateElement($result[0]);
            $this->returnToParent();
        } else {
            $this->tpl->addJavaScript("./Customizing/global/plugins/Services/COPage/PageComponent/AIPic/templates/js/downloadImage.js");
            $this->tpl->addJavaScript("./Customizing/global/plugins/Services/COPage/PageComponent/AIPic/templates/js/callSaveEndpoint.js");
            $this->tpl->addJavaScript("./Customizing/global/plugins/Services/COPage/PageComponent/AIPic/templates/js/widthInput.js");
            $this->tpl->addJavaScript("./Customizing/global/plugins/Services/COPage/PageComponent/AIPic/templates/js/resendForm.js");
            $this->tpl->addJavaScript("./Customizing/global/plugins/Services/COPage/PageComponent/AIPic/templates/js/adjustImage.js");
            $this->tpl->addCss("./Customizing/global/plugins/Services/COPage/PageComponent/AIPic/templates/css/aIPic_sheet.css");
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
    private function generateImageCreator(): AIPicRequestInterface {
        $config = new AIPicConfig();
        $config->loadFromDB();
        $AIPicProvider = AIPicRequestImpl::from($config);

        return $AIPicProvider;
    }

    /**
     * @throws ilCtrlException
     * @throws Exception
     */
    public function insert(): void
    {
        global $tpl, $DIC, $ilCtrl;

        $DIC->logger()->root()->info("FORM LOADED");
        $this->editorGUI = new ilAIPicEditorGUI($this->plugin, $this->generateImageCreator());
        $form = $this->editorGUI->getPromptForm();
        $request = $DIC->http()->request();
        $query = $DIC->http()->wrapper()->query();
        $refinery = $DIC->refinery();
        $action = $query->retrieve("cmd", $refinery->to()->string());
        $actionDesired = $query->has("methodDesired") ? $query->retrieve("methodDesired", $refinery->to()->string()) : null;

        $ilCtrl->setParameterByClass('ilAIPicPluginGUI', 'methodDesired', 'sendPrompt');

        if ($request->getMethod() == "POST" && $action != "post" && $actionDesired == "saveImage") {
            $this->editorGUI->saveImage();
            $request = $DIC->http()->request();
            $form = $this->editorGUI->getPromptForm();
            $form = $form->withRequest($request);
            $result = $form->getData();

            if(isset($result) && count($result) > 0 && count($result[0]['file']) > 0) {
                $result[0]['imageId'] = $result[0]['file'][0];

                $result["legacyFileName"] = $this->uploader->getInfoResult($result[0]["file"][0])->getName();
                $result["fileName"] = $result[0]["file"][0];
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

            $tpl->addJavaScript("./Customizing/global/plugins/Services/COPage/PageComponent/AIPic/templates/js/downloadImage.js");
            $tpl->addJavaScript("./Customizing/global/plugins/Services/COPage/PageComponent/AIPic/templates/js/callSaveEndpoint.js");
            $tpl->addJavaScript("./Customizing/global/plugins/Services/COPage/PageComponent/AIPic/templates/js/widthInput.js");
            $tpl->addJavaScript("./Customizing/global/plugins/Services/COPage/PageComponent/AIPic/templates/js/resendForm.js");
            $tpl->addJavaScript("./Customizing/global/plugins/Services/COPage/PageComponent/AIPic/templates/js/adjustImage.js");

            $tpl->addCss("./Customizing/global/plugins/Services/COPage/PageComponent/AIPic/templates/css/aIPic_sheet.css");

            $this->tpl->setContent($res);
        }
    }

    /**
     * @throws ilCtrlException
     * @throws Exception
     */
    public function edit(): void
    {
        global $tpl, $DIC, $ilCtrl;

        $this->editorGUI = new ilAIPicEditorGUI($this->plugin, $this->generateImageCreator());
        $form = $this->editorGUI->getPromptFormWithProperties($this->getProperties());
        $irss = $DIC->resourceStorage();
        $file_name = $irss->consume()->src(new ResourceIdentification($this->getProperties()["imageId"]))->getSrc();
        $image = $this->editorGUI->generateImage($file_name ?? null);

        $res = $this->renderer->render($form) . $image;

        $tpl->addJavaScript("./Customizing/global/plugins/Services/COPage/PageComponent/AIPic/templates/js/downloadImage.js");
        $tpl->addJavaScript("./Customizing/global/plugins/Services/COPage/PageComponent/AIPic/templates/js/callSaveEndpoint.js");
        $tpl->addJavaScript("./Customizing/global/plugins/Services/COPage/PageComponent/AIPic/templates/js/widthInput.js");
        $tpl->addJavaScript("./Customizing/global/plugins/Services/COPage/PageComponent/AIPic/templates/js/resendForm.js");
        $tpl->addJavaScript("./Customizing/global/plugins/Services/COPage/PageComponent/AIPic/templates/js/adjustImage.js");

        $tpl->addCss("./Customizing/global/plugins/Services/COPage/PageComponent/AIPic/templates/css/aIPic_sheet.css");

        $this->tpl->setContent($res);
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

        $this->editorGUI = new ilAIPicEditorGUI($this->plugin, $this->generateImageCreator());

        $old_path = ILIAS_WEB_DIR . '/' . CLIENT_ID . "/AIPic/" . $a_properties["imageId"];

        if (!file_exists($old_path)) {
            $irss = $DIC->resourceStorage();
            $file_name = $irss->consume()->src(new ResourceIdentification($a_properties["imageId"]))->getSrc();

        } else {
            $file_name = $old_path;
        }
        $a_properties["fileName"] = $file_name;

        $tpl = new ilTemplate("aIPic_element.html", true, true, "Customizing/global/plugins/Services/COPage/PageComponent/AIPic");

        $tpl->setVariable("ID", date_create()->format('Y-m-d_H-i-s'));
        $tpl->setVariable("SCALE_WRAPPER_WIDTH", $a_properties["widthInput"]);

        $raw_alignment = $a_properties["aligments"] ?? "center";
        $alignment = empty($raw_alignment) ? "left" : $raw_alignment;
        $tpl->setVariable("ALIGNMENT", $alignment);

        $tpl->setVariable("TEMPLATES_DIR", "Customizing/global/plugins/Services/COPage/PageComponent/AIPic/templates");
        $tpl->setVariable("PLUGIN_DIR", "Customizing/global/plugins/Services/COPage/PageComponent/AIPic");
        $tpl->setVariable("IMAGE_URL", $a_properties["fileName"]);

        $tpl->setVariable("PROPERTIES", json_encode($a_properties));

        return $tpl->get();
    }
}