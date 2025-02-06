<?php

use ILIAS\COPage\Editor\Components\PageComponentEditor;
use ILIAS\COPage\Editor\Server\UIWrapper;
use ILIAS\UI\Component\Input\Container\Form\Standard;

class ilAImageGeneratorEditorGUI implements PageComponentEditor
{

    protected ilTemplate $template;

    private ilPageComponentPlugin $plugin;

    private AImageGeneratorRequestInterface $aimageGeneratorProvider;

    public function __construct(ilPageComponentPlugin $plugin, AImageGeneratorRequestInterface $aimageGeneratorProvider)
    {
        $this->plugin = $plugin;
        $this->aimageGeneratorProvider = $aimageGeneratorProvider;
    }


    public function getEditorElements(UIWrapper $ui_wrapper, string $page_type, \ilPageObjectGUI $page_gui, int $style_id): array
    {
        global $DIC;
        $lng = $DIC->language();
        $rederer = $DIC->ui()->renderer();

        $acc = new ilAccordionGUI();
        //$acc->addItem($lng->txt("prompt_selector"), $rederer->render($this->getPromptForm()));
        return [
            "icon" => $ui_wrapper->getRenderedIcon("xaimg"),
            "title" => $lng->txt("image_generator") // Esto ayuda a identificar el componente
        ];
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

    public function getPromptForm(): Standard {
        global $DIC;
        $ui = $DIC->ui()->factory();
        $lng = $DIC->language();

        $prompt = $ui->input()->field()->textarea($this->plugin->txt("prompt"), $this->plugin->txt("prompt"));

        $section1 = $ui->input()->field()->section(["prompt"=>$prompt], "Configuración");

        $DIC->ctrl()->setParameterByClass(
            'ilAImageGeneratorPluginGUI',
            'methodDesired',
            'sendPrompt'
        );
        $form_action = $DIC->ctrl()->getLinkTargetByClass('ilAImageGeneratorPluginGUI', "insert");

        return $ui->input()->container()->form()->standard($form_action, [$section1])->withSubmitLabel($lng->txt("send"));

    }

    public function checkImagesAndGenerateForm(Standard $form)
    {
        global $DIC;

        $refinery = $DIC->refinery();
        $renderer = $DIC->ui()->renderer();
        $request = $DIC->http()->request();
        $query = $DIC->http()->wrapper()->query();
        $action = $query->retrieve("cmd", $refinery->to()->string());

        if ($request->getMethod() == "POST" && $action != "post") {
            $form = $form->withRequest($request);
            $result = $form->getData();
            if(isset($result)) {
                $this->sendPrompt();
            }
        }

        $formHtml = $renderer->render($form);
        $imagesUrls = $this->getImageResults();
        $images = "";
        if(count($imagesUrls) > 0) {
            foreach($imagesUrls as $url) {
                $image = $this->generateImage($url);
                $images .= $image;
            }
        }

        return $formHtml . $images;
    }

    private function generateImage(string $url): string
    {
        global $DIC;
        $ui = $DIC->ui()->factory();
        $renderer = $DIC->ui()->renderer();
        $image = $ui->image()->responsive($url, "Thumbnail Example");
        return '<div style="width: 100%; display: flex; flex-direction: row; justify-content: center;">' .
                    '<div style="width: 30%;">' . $renderer->render($image) . '</div>' .
                    '<div style="align-content: center;"><button style="height: 30px; margin-left: 10px; background-color: #4c6586; color: white; border: none; padding: 3px 13px;">Descargar</button></div>'.
            '</div>';
    }

    public function sendPrompt(): void
    {
        global $DIC;
        $renderer = $DIC->ui()->renderer();
        $request = $DIC->http()->request();

        $form = $this->getPromptForm();
        if ($request->getMethod() == "POST") {
            $form = $form->withRequest($request);
            $result = $form->getData();
            if(isset($result)) {
                $prompt = $result[0]["prompt"];
                $this->aimageGeneratorProvider->sendPrompt($prompt);
            }
        }
    }

    public function getImageResults(): array
    {
        return $this->aimageGeneratorProvider->getImagesUrlsArray();
    }

}