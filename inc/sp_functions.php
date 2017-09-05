<?php

######################################
######################################
##### STRIPEPRESS CORE FUNCTIONS #####
######################################
######################################

/**
  *
  * Gets custom Stripepress templates
  * @param name
  *
 **/

function sp_get_template($name = null, $directory = '') {
	
	$template = '';
	
	$directory = str_replace('/','',trim($directory));
	$name = trim($name," \t\n\r\0\x0B/");
	$ext = (!strpos($name,'.php')) ? '.php' : '';
	$temp_path = trailingslashit($directory) . $name . $ext;
	
	if (file_exists(get_stylesheet_directory() . '/' . trailingslashit(SP_PLUGIN_SLUG) . $temp_path)) :
		$template = get_stylesheet_directory() . '/' . trailingslashit(SP_PLUGIN_SLUG) . $temp_path;
	endif;
	
	if (!$template) :
		$template = SP_TEMPLATE_DIR . $temp_path;
	endif;
	
	if ($template) : 
		load_template($template);
	endif;
	
}