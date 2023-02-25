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

// The name of the plugin that will be displayed on the administration page.
$plugin ['name'] = 'Google Analytics';
// Priority ranges from 0-10, with 0 being the most important, 10 the least.
$plugin ['priority'] = '10';
// Does the plugin have a user-interface (eg, does a typical end-user need to
// interact with the plugin, or does it only affect the back-end of the system)
$plugin ['user'] = 0;
// What path should be used to access this plugin when administering, providing
// user access, etc.?
$plugin ['path'] = 'ga';
// Define functions for required actions
$plugin ['config'] = 'HOAM_plugingaConfig';
$plugin ['disable'] = 'HOAM_plugingaDisable';
$plugin ['enable'] = 'HOAM_plugingaEnable';
$plugin ['init'] = 'HOAM_plugingaInit';
$plugin ['install'] = 'HOAM_plugingaInstall';
$plugin ['remove'] = 'HOAM_plugingaRemove';

function HOAM_plugingaInit () {
	global $_HOAM_language, $_HOAM_setting;
	// Usually the init code will simply insert itself into the necessary
	// queues and possibly load configuration information.
	//
	// You generally don't want the init code to be doing actual work until the
	// system requires / user requests it.
	//
	// Eventually the language files could be dumped into separate files and
	// loaded as needed (like is done at startup).
	$_HOAM_language ['field_names']['plugin']['ga']['code'] = 'Your Personalized Google Analytics Tracking Code';
	$_HOAM_language ['field_names']['plugin']['ga']['code-format'] = 'The code does not match the format required.';
	$_HOAM_language ['field_names']['plugin']['ga']['config'] = 'Configure Google Analytics Plugin';
	$_HOAM_language ['field_names']['plugin']['ga']['disable'] = 'Disable Google Analytics Plugin';
	$_HOAM_language ['field_names']['plugin']['ga']['enable'] = 'Enable Google Analytics Plugin';
	$_HOAM_language ['field_names']['plugin']['ga']['enabled'] = 'Enable Google Analytics Plugin';
	$_HOAM_language ['field_names']['plugin']['ga']['install'] = 'Install Google Analytics Plugin';
	$_HOAM_language ['field_names']['plugin']['ga']['remove'] = 'Remove Google Analytics Plugin';
	
	$_HOAM_language ['help_popups']['plugin']['ga']['code'] = '<h3>Tracking Code</h3><p>Enter your personal tracking code from Google.</p><p>If you can\'t find this information, it is available once you log into the Google Analytics website.</p><p>Only the numeric portion, not the leading "UA-" is required.</p>';
	$_HOAM_language ['help_popups']['plugin']['ga']['enabled'] = '<h3>Enabled</h3><p>This affects whether the plugin is enabled or not; it may still be configured and any changes made are saved, but the Analytics code will not be displayed to end-users.</p>';
	
	// Probably should move this to the configuration as part of installing the
	// plugin rather than polluting language.
	$_HOAM_language ['plugin']['ga']['format'] = '$1-$2';
	$_HOAM_language ['plugin']['ga']['match'] = '/^(\d{5})(\d+)$/';
	
	if ($_HOAM_setting ['plugin']['ga']['enabled']) {
		HOAM_queueInsertAfter ($GLOBALS['_HOAM_queue_head'], 'headScripts', 'HOAM_pluginga');
	}
}

function HOAM_plugingaConfig () {
	global $_HOAM_language, $_HOAM_queue_head, $_HOAM_setting;
	require_once (HOAM_PATH_FUNCTION . 'forms.php');
	HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['plugin']['ga']['config'])));
	$HTML = '<h1>' . $_HOAM_language ['field_names']['plugin']['ga']['config'] . '</h1>';
	$HTML .= '<form id="plugin_modify" action="/hoam/plugins/GoogleAnalytics/configure.php" method="post">';
	if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
		$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
	}
	$HTML .= '<fieldset>';
	$HTML .= '<button type="submit" name="modify">' . $_HOAM_language ['form_buttons']['submit'] . '</button>';
	$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
	$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
	$HTML .= '</fieldset>';
	$HTML .= $_HOAM_language ['help_popups']['mandatory'];
	$HTML .= '<div class="tabber">';

	$HTML .= '<div class="tabbertab" id="plugin_modify" title="' . $_HOAM_language ['form_tabs']['plugin']['information'] . '">';
	$HTML .= '<div class="mandatory">';
	$HTML .= '<fieldset>';
	$HTML .= HOAM_formCheckbox ('plugin|ga|enabled', $_HOAM_language ['field_names']['plugin']['ga']['enabled'], 1, $_SESSION['plugin_information']['trusted_plugin|ga|enabled'], $_HOAM_setting ['plugin']['ga']['enabled']);
	$HTML .= '</fieldset>';
	$HTML .= HOAM_formField ('plugin|ga|code', $_HOAM_language ['field_names']['plugin']['ga']['code'], $_SESSION['plugin_information']['trusted_plugin|ga|code'], $_HOAM_setting ['plugin']['ga']['code'], 10, 1);
	$HTML .= '</div>';			
	$HTML .= '</div>'; // End plugin configuration

	$HTML .= '</div>'; // End Tabber
	$HTML .= '</form>';
	return ($HTML);
}

function HOAM_plugingaDisable () {
//	global $_HOAM_language, $_HOAM_queue_head;
//	HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['plugin']['ga']['disable'])));
//	$HTML = '<h1>' . $_HOAM_language ['field_names']['plugin']['ga']['disable'] . '</h1>';
	return (HOAM_plugingaConfig);
}

function HOAM_plugingaEnable () {
//	global $_HOAM_language, $_HOAM_queue_head;
//	HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['plugin']['ga']['enable'])));
//	$HTML = '<h1>' . $_HOAM_language ['field_names']['plugin']['ga']['enable'] . '</h1>';
	return (HOAM_plugingaConfig);
}

function HOAM_plugingaInstall () {
	global $_HOAM_language, $_HOAM_queue_head;
	HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['plugin']['ga']['install'])));
	$HTML = '<h1>' . $_HOAM_language ['field_names']['plugin']['ga']['install'] . '</h1>';
}

function HOAM_plugingaRemove () {
	global $_HOAM_language, $_HOAM_queue_head;
	HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['plugin']['ga']['remove'])));
	$HTML = '<h1>' . $_HOAM_language ['field_names']['plugin']['ga']['remove'] . '</h1>';
}

function HOAM_pluginga () {
	global $_HOAM_language, $_HOAM_setting;
	if (preg_replace ($_HOAM_language ['plugin']['ga']['match'], $_HOAM_language ['plugin']['ga']['format'], $_HOAM_setting ['plugin']['ga']['code'], 1)) {
		// Include Google Tracking Code
		$HTML = '<script type="text/javascript">';
		$HTML .= 'if ("http:" == document.location.protocol) {';
		$HTML .= 'var _gaq = _gaq || [];';
		$HTML .= '_gaq.push([\'_setAccount\', \'UA-' . preg_replace ($_HOAM_language ['plugin']['ga']['match'], $_HOAM_language ['plugin']['ga']['format'], $_HOAM_setting ['plugin']['ga']['code'], 1) . '\']);';
		$HTML .= '_gaq.push([\'_trackPageview\']);';
		$HTML .= '(function() {';
		$HTML .= 'var ga = document.createElement(\'script\'); ga.type = \'text/javascript\'; ga.async = true;';
		$HTML .= 'ga.src = (\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.google-analytics.com/ga.js\';';
		$HTML .= 'var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(ga, s);';
		$HTML .= '})();';
		$HTML .= '}';
		$HTML .= '</script>';
	} else {
		// The site code is not in the correct format, so don't display
		// anything.
	}
	echo $HTML;
}

?>