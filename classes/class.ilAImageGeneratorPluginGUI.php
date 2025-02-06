<?php

/**
 * @ilCtrl_isCalledBy ilAImageGeneratorPluginGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI, ilAImageGeneratorEditorGUI, ilPCPluggedGUI
 * @ilCtrl_Calls      ilAImageGeneratorPluginGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI, ilExportGUI, ilAImageGeneratorEditorGUI, ilObjRootFolderGUI
 */

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
    private UploadServiceGUI $uploader;

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
        $this->uploader = new UploadServiceGUI();

    }


//
// DISPLAY TABS
//

    /**
     * Set tabs
     */
    protected function setTabs() : void
    {
        global $ilCtrl, $ilAccess;

        // tab for the "show content" command
        if ($ilAccess->checkAccess("read", "", $this->object->getRefId())) {
            $this->tabs->addTab("content", $this->txt("content"), $ilCtrl->getLinkTarget($this, "showContent"));
        }

        // standard info screen tab
        $this->addInfoTab();

        // a "properties" tab
        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $this->tabs->addTab(
                "properties",
                $this->txt("properties"),
                $ilCtrl->getLinkTarget($this, "editProperties")
            );
        }

        // standard export tab
        $this->addExportTab();

        // standard permission tab
        $this->addPermissionTab();
        $this->activateTab();
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
                if (in_array($cmd, array("create", "save", "edit", "edit2", "update", "cancel", "sendPrompt", "insert")))
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

    public function insert(): void
    {
        global $DIC;
        $this->editorGUI = new ilAImageGeneratorEditorGUI($this->plugin, $this->generateImageCreator());
        $ui = $DIC->ui()->factory();
        $rederer = $DIC->ui()->renderer();
        $form = $this->editorGUI->getPromptForm();
        $refinery = $DIC->refinery();

        $res = $this->editorGUI->checkImagesAndGenerateForm($form);


/*


        $jsonData = json_encode($data);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true); // Indicamos que es una solicitud POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData); // Convertimos el array en una cadena URL-encoded
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        $response = json_decode($response, true);

        if (curl_errno($ch)) {
            dumpt( "Error en cURL: " . curl_error($ch)); exit();
        } else {
            // Opcional: obtener el código de respuesta HTTP
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $url = $response["data"][0]["url"];

            $imagen = file_get_contents($url);
            $nombreArchivo = 'imagen.jpg';

            // Configura las cabeceras para forzar la descarga
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream'); // También se puede usar el Content-Type real, p.ej., image/jpeg
            header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . strlen($imagen));

            // Envía el contenido de la imagen al navegador
            echo $imagen;
            //dump($response["data"][0]["url"]);exit();
        }
*/

        $this->tpl->setContent($res);
    }

    public function edit(): void
    {
        dump('edit');exit();
    }

    public function create(): void
    {
        dump('edit');exit();
    }

    public function sendPrompt(): void
    {
        $this->editorGUI->sendPrompt();
    }

    public function getElementHTML(string $a_mode, array $a_properties, string $plugin_version): string
    {
        global $DIC;
        $lng = $DIC->language();

        $ui = $DIC->ui()->factory();
        $rederer = $DIC->ui()->renderer();
        $form = $rederer->render($this->editorGUI->getPromptForm());
       // dump($form);exit();

        return $form;
    }
}