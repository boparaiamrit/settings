<?php

if (!function_exists('settings')) {
	/**
	 * @param string|null $key
	 * @param null        $default
	 *
	 * @return mixed|\Settings
	 */
	function settings($key = null, $default = null)
	{
		if (is_null($key)) {
			return app('settings');
		}
		
		return app('settings')->get($key, $default);
	}
}
