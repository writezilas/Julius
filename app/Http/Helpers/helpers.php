<?php

	function get_general_settings($name) {
	    $config = null;
	    foreach (GeneralSetting::all() as $setting) {
	        if ($setting['key'] == $name) {
	            $config = json_decode($setting['value'], true);
	        }
	    }
	    return $config;
	}

	function get_gs_value($key, $full = false) {

	    $config = GeneralSetting::where('key', $key)->first();

	    if(!$full){
	        return $config->value;
	    }
	    return $config;
}
