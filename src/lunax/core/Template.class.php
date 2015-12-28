<?php

class Template
{
    private $view;
    private $viewName;

	private function includeFile($type, $dir, $file)
	{
		$filename = implode(DS, [
			APPDIR,
			$type,
			$dir,
			$file
		]);

		if (file_exists($filename)) {
			if (include $filename) {
				Utils::log("File \"$filename\" included!");
				return true;
			}
		} else {
			return false;
		}
	}

    /**
     * Include content of template
     */
    public function content()
    {
        # Include the logical file
		$this->includeFile('views', 'logical', "$this->viewName.php");

		# Include the output file
		if (!$this->includeFile('views', 'output', "$this->viewName.phtml")) {
			Utils::error("View file \"$this->viewName.phtml\" not found!");
		}
    }

    /**
     * Make applications onePage
     */
    public function onePage($subTemplate, $views = null)
    {
        # onePage([...]);
        if (gettype($subTemplate) == 'array') {
            $views = $subTemplate;
            $subTemplate = null;
        }

        # onePage('sub_template', [...]);
        if (!is_null($subTemplate)) {

            $subTemplate = implode(DS, [
                APPDIR,
                'layout',
                "$subTemplate.page.phtml"
            ]);

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

        # Setting data of controller to template/view
        foreach ($dataView as $key => $value) {
            $this->$key = $value;
        }

        # Include the logical file
		$this->includeFile('layouts', 'logical', "$name.php");

        if (!RequestURL::getUsingLunajax()) {
            # Include the output file
            if (!$this->includeFile('layouts', 'output', "$name.phtml")) {
                Utils::error("Layout file \"$filename\" not found!");
            }
        } else {
            $this->content();
        }
    }
}
