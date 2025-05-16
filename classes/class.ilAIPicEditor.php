<?php


use ILIAS\COPage\Editor\Server\UIWrapper;
use ILIAS\UI\Component\Input\Container\Form\Standard;
use JetBrains\PhpStorm\NoReturn;

class ilAIPicEditorGUI
{

    protected ilTemplate $template;
    private ilPageComponentPlugin $plugin;
    private AIPicRequestInterface $AIPicProvider;
    private UploadServiceAIPicGUI $uploader;

    private string $placeHolderUrl = "./Customizing/global/plugins/Services/COPage/PageComponent/AIPic/templates/images/placeholder.png";

    public function __construct(ilPageComponentPlugin $plugin, AIPicRequestInterface $AIPicProvider)
    {
        $this->plugin = $plugin;
        $this->AIPicProvider = $AIPicProvider;
        $this->uploader = new UploadServiceAIPicGUI();
    }


    public function getEditorElements(UIWrapper $ui_wrapper, string $page_type, \ilPageObjectGUI $page_gui, int $style_id): array
    {
        global $DIC;

        $lng = $DIC->language();

        return [
            "icon" => $ui_wrapper->getRenderedIcon("xaimg"),
            "title" => $lng->txt("image_generator")
        ];
    }

    /**
     * @throws Exception
     */
    public function saveImage(): void
    {
        global $DIC;

        $request = $DIC->http()->request();
        $form = $this->getPromptForm();
        $form = $form->withRequest($request);
    }

    public function getEditComponentForm(UIWrapper $ui_wrapper, string $page_type, \ilPageObjectGUI $page_gui, int $style_id, string $pcid): string
    {
        global $DIC;

        $ui = $DIC->ui()->factory();
        $rederer = $DIC->ui()->renderer();

        $form = $rederer->render($this->getPromptForm());
        $image = $ui->image()->responsive(
            "https://ssantiago.ilias9.com/src/UI/examples/Image/HeaderIconLarge.svg",
            "Thumbnail Example"
        );

        $res = $form . $rederer->render($image);
        return $res;
    }

    public function getPromptForm(): Standard
    {
        global $DIC;

        $ui = $DIC->ui()->factory();
        $lng = $DIC->language();

        $file = $ui->input()->field()->file($this->uploader, "")->withAcceptedMimeTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/gif']);
        $aligments = array(
            "center" => $this->plugin->txt("select_aligment_center"),
            "left" => $this->plugin->txt("select_aligment_left"),
            "right" => $this->plugin->txt("select_aligment_right")
        );

        $styles = array(
            "realistic" => $this->plugin->txt("style_realistic"),
            "artistic" => $this->plugin->txt("style_artistic"),
            "minimal" => $this->plugin->txt("style_minimal"),
            "anime" => $this->plugin->txt("style_anime"),
            "vintage" => $this->plugin->txt("style_vintage"),
            "cartoon" => $this->plugin->txt("style_cartoon"),
        );

        $selectAligment = $ui->input()->field()->select($this->plugin->txt("select_aligment"), $aligments, $this->plugin->txt("select_aligment_image_position"))->withValue("center")->withRequired(true);
        $styles_select_input = $ui->input()->field()->select($this->plugin->txt("select_style"), $styles, $this->plugin->txt("select_style_image"))->withRequired(false);

        $widthInput = $ui->input()->field()->numeric($this->plugin->txt("width"), $this->plugin->txt("width_px"))->withRequired(true);

        $prompt = $ui->input()->field()->textarea($this->plugin->txt("prompt"), $this->plugin->txt("prompt_description"))->withRequired(true);
        $section1 = $ui->input()->field()->section(["prompt" => $prompt, "file" => $file, "styles" => $styles_select_input, "aligments" => $selectAligment, "widthInput" => $widthInput], $this->plugin->txt("configuration"));

        $DIC->ctrl()->setParameterByClass(
            'ilAIPicPluginGUI',
            'methodDesired',
            'saveImage'
        );

        $form_action = $DIC->ctrl()->getLinkTargetByClass('ilAIPicPluginGUI', "insert");

        return $ui->input()->container()->form()->standard($form_action, [$section1])->withSubmitLabel($lng->txt("send"))->withDedicatedName("AIPicForm");
    }

    public function getPromptFormWithProperties(array $properties): Standard
    {
        global $DIC;
        $ui = $DIC->ui()->factory();
        $lng = $DIC->language();

        $prompt = $ui->input()->field()->textarea($this->plugin->txt("prompt"), $this->plugin->txt("prompt_description"))->withValue($properties["prompt"] ?? "")->withRequired(true);
        $file = $ui->input()->field()->file($this->uploader, "")->withAcceptedMimeTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/gif']);

        $aligments = array(
            "center" => $this->plugin->txt("select_aligment_center"),
            "left" => $this->plugin->txt("select_aligment_left"),
            "right" => $this->plugin->txt("select_aligment_right")
        );

        $styles_options = array(
            "realistic" => $this->plugin->txt("style_realistic"),
            "artistic" => $this->plugin->txt("style_artistic"),
            "minimal" => $this->plugin->txt("style_minimal"),
            "anime" => $this->plugin->txt("style_anime"),
            "vintage" => $this->plugin->txt("style_vintage"),
            "cartoon" => $this->plugin->txt("style_cartoon"),
        );

        $selectAligment = $ui->input()->field()->select($this->plugin->txt("select_aligment"), $aligments, $this->plugin->txt("select_aligment_image_position"))->withValue($properties["aligments"] ?? "center")->withRequired(true);
        $selecStyle = $ui->input()->field()->select($this->plugin->txt("select_style"), $styles_options, $this->plugin->txt("select_style_image"))->withValue($properties["styles"] ?? "realistic")->withRequired(false);
        $widthInput = $ui->input()->field()->numeric($this->plugin->txt("width"), $this->plugin->txt("width_px"))->withRequired(true)->withValue($properties["widthInput"]);
        $section1 = $ui->input()->field()->section(["prompt" => $prompt, "file" => $file, "styles" => $selecStyle, "aligments" => $selectAligment, "widthInput" => $widthInput], $this->plugin->txt("configuration"));
        $DIC->ctrl()->setParameterByClass(
            'ilAIPicPluginGUI',
            'methodDesired',
            'saveImage'
        );

        $form_action = $DIC->ctrl()->getLinkTargetByClass('ilAIPicPluginGUI', "update");

        return $ui->input()->container()->form()->standard($form_action, [$section1])->withSubmitLabel($lng->txt("send"))->withDedicatedName("AIPicForm");
    }

    public function renderForm(Standard $form): string
    {
        global $DIC;

        $refinery = $DIC->refinery();
        $renderer = $DIC->ui()->renderer();
        $request = $DIC->http()->request();
        $query = $DIC->http()->wrapper()->query();
        $action = $query->retrieve("cmd", $refinery->to()->string());

        if ($request->getMethod() == "POST" && $action != "post") {
            // Send the prompt
            $this->sendPromptByJs();
        }

        $formHtml = $renderer->render($form);

        return $formHtml;
    }

    public function manageDownloadImage(): void
    {
        global $DIC;

        $refinery = $DIC->refinery();
        $request = $DIC->http()->request();
        $query = $DIC->http()->wrapper()->query();

        $actionFinal = "";
        if ($query->has("methodDesired")) {
            $actionFinal = $query->retrieve("methodDesired", $refinery->to()->string());
        }

        if ($request->getMethod() == "GET" && $actionFinal == "downloadImage" && $query->has("urlDownload")) {
            $destiny = $query->retrieve("urlDownload", $refinery->to()->string());
            $destiny = urldecode($destiny);
            $imagen = file_get_contents($destiny);
            $query_string = parse_url($destiny, PHP_URL_QUERY);
            parse_str($query_string, $params);
            $extension = pathinfo($destiny, PATHINFO_EXTENSION);

            $content_type = $params['rsct'] ?? 'image/png';
            $now = date_create()->format('Y-m-d_H-i-s');
            $fileName = "AIPic$$now.$extension";

            header('Content-Description: File Transfer');
            header("Content-Type: $content_type");
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . strlen($imagen));

            echo $imagen;
        }
    }

    /**
     * @throws ilCtrlException
     */
    public function generateImage(?string $url = null): string
    {
        global $DIC, $ilCtrl;

        $url = $url ?? $this->placeHolderUrl;
        $ui = $DIC->ui()->factory();
        $renderer = $DIC->ui()->renderer();
        $image = $ui->image()->responsive($url, "Generated_image");

        $urlButtonDownload = $ilCtrl->getLinkTargetByClass("ilAIPicPluginGUI", "insert");
        $buttonDownload = $ui->button()->standard($this->plugin->txt("button_download"), "#")->withOnLoadCode(function ($id) use ($urlButtonDownload) {
            return "$(\"#$id\").click(function() { callSaveEndpoint(\"$urlButtonDownload\"); });";
        });
        $ilCtrl->setParameterByClass('ilAIPicPluginGUI', 'methodDesired', 'sendPrompt');
        $urlButtonPrompt = $ilCtrl->getLinkTargetByClass("ilAIPicPluginGUI", "insert");

        $urlBase = $DIC->ctrl()->getLinkTargetByClass('ilAIPicPluginGUI', 'insert');

        $buttonGenerateImage = $ui->button()->standard($this->plugin->txt("generate_image"), "#")->withOnLoadCode(function ($id) use ($urlButtonPrompt, $urlBase) {
            return "$(\"#$id\").click(function(e) { e.preventDefault(); resendForm(\"$urlButtonPrompt\", \"$urlBase\"); });";
        });


        $buttonDownloadHtml = '<div id="downloadButton" style="display: none; margin-bottom: 10px; margin-top: 10px; width: 10%;">' . $renderer->render($buttonDownload) . '</div>';
        return '<div style="display: flex; align-items: center; flex-direction: column; justify-content: center;">' .

            '<div id="imageDiv" style="padding: 10px; position: relative; display: flex;">' .
            '<div id="loadingSpinner" style="display: none; position: absolute; 
                                    background-color: white;
                                    box-shadow: 0 0 5px 2px #d1d1d1;
                                    top: 50%;
                                    left: 50%;
                                    transform: translate(-50%, -50%);">' .
            '<img src="./Customizing/global/plugins/Services/COPage/PageComponent/AIPic/templates/images/loading.gif" alt="loading""/>'
            . '</div>' .
            $renderer->render($image) .
            '</div>' .
            $buttonDownloadHtml .
            '<div id="redirectButton" style="align-content: center;">' .
            $renderer->render($buttonGenerateImage) .
            '</div>' .
            '</div>';
    }

    #[NoReturn] public function sendPromptByJs($httpCode = 200): void
    {
        http_response_code($httpCode);

        $rawPrompt = $_POST["prompt"] ?? "";
        $success = $this->AIPicProvider->sendPrompt($rawPrompt);
        if ($success) {
            $res = $this->AIPicProvider->getImagesUrlsArray();
            header('Content-type: application/json');
            if (count($res) !== 0) {
                echo json_encode(["image" => end($res)]);
                exit();
            }
        }

        echo json_encode(["Error" => $this->plugin->txt("no_images_found")]);
        exit();
    }
}