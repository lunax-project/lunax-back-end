<?php

class Template
{
    private $view;
    private $viewName;

    /**
     * Include content of template
     */
    public function content() {
        $filename = implode(DS, [APPDIR, 'views', "$this->viewName.phtml"]);

        if (file_exists($filename)) {
            include_once $filename;
            Utils::log("View file \"$filename\" readed!");
        } else {
            Utils::error("View file \"$this->viewName\" not found!");
        }
    }

    /**
     * Make applications onePage
     */
    public function onePage($subTemplate, $views = null) {
        # onePage([...]);
        if (gettype($subTemplate) == 'array') {
            $views = $subTemplate;
            $subTemplate = null;
        }

        # onePage('sub_template', [...]);
        if (!is_null($subTemplate)) {

            $subTemplate = implode(DS, [APPDIR, 'layout', "$subTemplate.page.phtml"]);

            if (file_exists($subTemplate)) {
                foreach ($views as $name) {
                    $this->viewName = $name;
                    include $subTemplate;
                }
            } else {
                Utils::error("Page layout $subTemplate not found!");
            }

        } else {
            foreach ($views as $name) {
                $this->viewName = $name;
                $this->content();
            }
        }
    }

    function __construct($name, $view, $dataView)
    {
        $this->viewName = $view;

        $filename = implode(DS, [
            APPDIR,
            'layouts',
            "$name.phtml"
        ]);

        # Setting data of controller to template/view
        foreach ($dataView as $key => $value) {
            $this->$key = $value;
        }

        if (file_exists($filename)) {
            require_once $filename;
            Utils::log("Layout file \"$filename\" readed!");
        } else {
            Utils::error("Layout file \"$filename\" not found!");
        }
    }
}
