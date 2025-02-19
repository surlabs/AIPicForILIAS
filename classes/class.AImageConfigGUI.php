<?php

namespace platform;

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

class AImageConfigGUI
{

    private Factory $ui;
    private Renderer $renderer;

    public function __construct()
    {
        global $DIC;
        $this->ui = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
    }





}