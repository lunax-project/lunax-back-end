<?php

class Configs
{
	# Local copy of application configs
	private static $configs;

	/**
	 * Load configurations file by directory root
	 */
	private static function loadConfigFile($dir)
	{
		$fullFileName = implode(DS, [
			$dir,
			'configs',
			'application.json'
		]);

		if (file_exists($fullFileName)) {
			return json_decode(file_get_contents($fullFileName));
		} else {
			return new StdClass;
		}
	}

	/**
	 * Extends last configurations
	 */
	private static function extendConfigs($newConfigs)
	{
		foreach ($newConfigs as $name => $value) {
			self::set($name, $value);
		}
	}

	/**
	 * Get the value of application by name of configuration
	 */
	public static function get($name)
	{
		if (array_key_exists($name, self::$configs)) {
			return self::$configs->$name;
		} else {
			return null;
		}
	}

	/**
	 * Set a new value to application config
	 */
	public static function set($name, $value)
	{
		if (array_key_exists($name, self::$configs)) {
			self::$configs->$name = $value;
		}
	}

	/**
     * Load configurations of application
     */
	public static function load()
	{
		# Default configurations
        $defaultConfigs = (object)[
            'name'                  => null,

            'save_log'              => false,
            'display_errors'        => false,

            'template'              => false,
            'template_map'          => new StdClass,

            'router_url'            => new StdClass,
            'not_found_controller'  => '',

            'auth'                  => false,
            'auth_class'            => '',
            'auth_controller'       => '',
            'auth_action'           => '',
            'not_auth'              => [],

            'use_restful'           => true,
            'allow_restful'         => [],
            'denny_restful'         => []
        ];

        # Low priority default configs
        self::$configs = $defaultConfigs;

        # Medium priority lunax configs (global)
        self::extendConfigs(self::loadConfigFile(LUNAXDIR));

        # Hight priority application configs (local)
        self::extendConfigs(self::loadConfigFile(APPDIR));
	}
}
