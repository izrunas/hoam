<?php
/*
 * This file is part of HOAM, copyright (C) 2002-2020 Robert Butler
 *
 * HOAM is free software; you can redistribute it and/or modify it under the
 * terms of the GNU Affero General Public License as published by the Free
 * Software Foundation; either version 3 of the License, or (at your option)
 * any later version.
 *
 * HOAM is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with HOAM; if not, see http://www.gnu.org/licenses or write to the
 * Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301
 *
 * Questions specific to HOAM should be directed to ARP Realty. Please see
 * the HOAM web site at http://hoam.arprs.com/
 *
 * Some portions of HOAM incorporate ideas and/or code from other sources, and
 * those portions are explicitly mentioned and attributed in the relevant
 * section of HOAM source code. Questions about that code should be directed to
 * the original authors.
 *
 */

function bodyClose () {
	echo '</body>';
}

function bodyContainerClose () {
	echo '</div>';
}

function bodyContainerOpen () {
	echo '<div id="container">';
}

function bodyContent ($parameters = NULL) {
	global $_HOAM_language, $_HOAM_setting;
	
	switch (TRUE) {
		case (!isset ($parameters['content'])) :
			$parameters['content'] = '';
			break;
	}
	echo '<div id="page_content">';
	// Display a notice if the site is currently being upgraded
	if ($_HOAM_setting ['hoam']['updating'] == '1') {
		echo $_HOAM_language ['help_pages']['website']['update-in-process'];
	}
	// Display a notice to the client about javascript requirement
	echo $_HOAM_language ['help_pages']['website']['javascript_required'];
	echo $parameters['content'];
	echo '</div>';
}

function bodyFooter () {
	ob_start();
	eval ("?>" . file_get_contents (HOAM_PATH_TEMPLATE . 'footer.php') . "<?php ");
	$template_eval = ob_get_contents();
	ob_end_clean();
	echo $GLOBALS['wiki'] -> parse ($template_eval);
}

function bodyLogo () {
	ob_start();
	eval ("?>" . file_get_contents (HOAM_PATH_TEMPLATE . 'logo.php') . "<?php ");
	$template_eval = ob_get_contents();
	ob_end_clean();
	echo $GLOBALS['wiki'] -> parse ($template_eval);
}

function bodyNavigation () {
	ob_start();
	eval ("?>" . file_get_contents (HOAM_PATH_TEMPLATE . 'navigation.php') . "<?php ");
	$template_eval = ob_get_contents();
	ob_end_clean();
	echo $GLOBALS['wiki'] -> parse ($template_eval);
}

function bodyToolbar () {
	ob_start();
	eval ("?>" . file_get_contents (HOAM_PATH_TEMPLATE . 'toolbar.php') . "<?php ");
	$template_eval = ob_get_contents();
	ob_end_clean();
	echo $GLOBALS['wiki'] -> parse ($template_eval);
}

function bodyOpen () {
	echo '<body>';
}

function headers ($parameters = NULL) {
	// Parse any parameters received, and use default values otherwise.
	if (!isset ($parameters['replace'])) {
		if (!isset ($parameters['content-type'])) {
			$parameters['content-type'] = 'Content-Type: text/html; charset=UTF-8';
		}
		if (!isset ($parameters['status'])) {
			$parameters['status'] = 'HTTP/1.1 200 OK';
		}
		if (!isset ($parameters['x-frame'])) {
			$parameters['x-frame'] = 'X-FRAME-OPTIONS: DENY';
		}
	}
	// If 'replace' is set, those contents will replace other output of the
	// function.
	if (!isset ($parameters['replace'])) {
		header ($parameters['content-type']);
		header ($parameters['status']);
		header ($parameters['x-frame']);
	} else {
		$HTML = $parameters['replace'];
		echo $HTML;
	}
}

function documentClose () {
	echo '</html>';
}

function documentOpen () {
	$output  = '<?xml version="1.0" encoding="utf-8"?>';
	$output .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
	$output .= '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="' . HOAM_LANGUAGE . '" lang="' . HOAM_LANGUAGE . '">';
	echo $output;
}

function headOpen () {
	echo '<head>';
}

function headClose () {
	echo '</head>';
}

function headMeta ($parameters = NULL) {
	// Parse any parameters received, and use default values otherwise.
	switch (TRUE) {
		case (!isset ($parameters['output'])) :
			$parameters['output'] = '';
			break;
	}
	$HTML = $parameters['output'];
	$HTML .= '<link rel="SHORTCUT ICON" href="/hoam/images/icons/house.png" />';
	echo $HTML;
}

function headCSS () {
	require_once (HOAM_PATH_TEMPLATE . 'css.php');
}

function headScripts () {
	require_once (HOAM_PATH_TEMPLATE . 'scripts.php');
}

function headTitle ($parameters = NULL) {
	global $wiki;
	
	$HTML = '<title>' . HOAM_returnWebsiteTitle ();
	switch (TRUE) {
		case (isset ($parameters['title'])) :
			$HTML .= ' :: ' . $parameters['title'];
			break;
		default :
			break;
	}
	$HTML .= '</title>';
	
	echo $wiki -> parse ($HTML);
}

?>
