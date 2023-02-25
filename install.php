<?php
/*
 * This file is part of HOAM, copyright (C) 2002-2020 ARP Realty, Inc.
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

define ('HOAM_INSTALLING', TRUE);

require_once ('hoam/hoam-init.php');

require_once (HOAM_PATH_CLASS . 'errors.php');
require_once (HOAM_PATH_FUNCTION . 'default_library.php');
require_once (HOAM_PATH_FUNCTION . 'forms.php');
require_once (HOAM_PATH_FUNCTION . 'render.php');
require_once (HOAM_PATH . '/hoam/install/install-queues.php');

session_start ();

// If the referrer doesn't match this current page, then we know we came from
// somewhere else, and thus the information is suspect.
if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ("http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
	if (isset ($_SESSION['error_class_item'])) {
		unset ($_SESSION['error_class_item']);
	}
}
				
$wiki = new HOAM_wiki_parser ();
// Start log instance. We're not going to use it, but it's called in a lot of
// code we want to reuse, and it's easier to just stub it out for the install.
//$_HOAM_log = new HOAM_log ('log');
HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_installJS');

if (file_exists (HOAM_PATH . 'hoam-config.ini')) {
	$HTML = $_HOAM_language ['help_pages']['hoam']['already_installed'];
} else {
	$HTML = $_HOAM_language ['help_pages']['hoam']['new_install'];

	$HTML .= '<form action="/hoam/install/initial-install.php" method="post">';
	if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
		$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
	}

	$HTML .= '<button type="submit" id="submit" name="add">' . $_HOAM_language ['form_buttons']['submit'] . '</button>';
	$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
	$HTML .= '<div class="mandatory">';
	$HTML .= HOAM_formRadio ('config-hoam-db_install_type', $_HOAM_language ['field_names']['config']['hoam']['db_install_type'], array (ucfirst ($_HOAM_language ['common']['automatic']), ucfirst ($_HOAM_language ['common']['manual'])), $_SESSION['install_information']['trusted_config-hoam-db_install_type'], ucfirst ($_HOAM_language ['common']['automatic']));
	$HTML .= '</div>';
	$HTML .= '<fieldset id="config-hoam-automatic">';
	$HTML .= '<div class="mandatory">';
	$HTML .= HOAM_formText ('config-hoam-db_root_user', $_HOAM_language ['field_names']['config']['hoam']['db_root_user'], $_SESSION['install_information']['trusted_config-hoam-db_root_user'], 'root', 16, 1);
	$HTML .= HOAM_formPassword ('config-hoam-db_root_password', $_HOAM_language ['field_names']['config']['hoam']['db_root_password'], $_SESSION['install_information']['trusted_config-hoam-db_root_password'], '', 128, 1);
	$HTML .= '</div>';
	$HTML .= '</fieldset>';
	$HTML .= '<fieldset id="config-hoam-manual">';
	$HTML .= '<div class="mandatory">';
	$HTML .= HOAM_formText ('config-hoam-db_user', $_HOAM_language ['field_names']['config']['hoam']['db_user'], $_SESSION['install_information']['trusted_config-hoam-db_user'], '', 16, 1);
	$HTML .= HOAM_formPassword ('config-hoam-db_password', $_HOAM_language ['field_names']['config']['hoam']['db_password'], $_SESSION['install_information']['trusted_config-hoam-db_password'], '', 128, 1);
	$HTML .= HOAM_formText ('config-hoam-db_name', $_HOAM_language ['field_names']['config']['hoam']['db_name'], $_SESSION['install_information']['trusted_config-hoam-db_name'], '', 128, 1);
	$HTML .= '</div>';
	$HTML .= '<div class="optional">';
	$HTML .= HOAM_formText ('config-hoam-db_server', $_HOAM_language ['field_names']['config']['hoam']['db_server'], $_SESSION['install_information']['trusted_config-hoam-db_server'], 'localhost', 128, 1);
	$HTML .= HOAM_formField ('config-hoam-db_port', $_HOAM_language ['field_names']['config']['hoam']['db_port'], $_SESSION['install_information']['trusted_config-hoam-db_port'], '', 5, 1);
	$HTML .= '</div>';
	$HTML .= '</fieldset>';
	$HTML .= '</form>';
}

$template_output = $wiki -> parse ($HTML);

HOAM_queueInsertValue ($_HOAM_queue_body, 'bodyContent', array ('params' => array ('content' => $template_output)));
HOAM_queueInsertAfter ($_HOAM_queue_document, 'documentOpen', $_HOAM_queue_head);
HOAM_queueInsertBefore ($_HOAM_queue_document, 'documentClose', $_HOAM_queue_body);
echo HOAM_queueRun ($_HOAM_queue_document);

function HOAM_installJS () {
	echo '<script type="application/javascript" src="/hoam/install/install.js"></script>';
}

?>
 
