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

require_once ('../../hoam-init.php');

session_start();

// We're note doing any authentication on this, since there's really no need.
// It's just the publicly available help, nothing secret.
if (isset ($_GET['string'])) {
	$wiki = new HOAM_wiki_parser ();
	
	$string = (string) addslashes ($_GET['string']);
	// I don't believe there are any items in the language file longer than 64 chars, this is just arbitrary number.
	if (isset ($_GET['param1']) OR isset ($_GET['param2']) OR isset ($_GET['param3']) OR isset ($_GET['param4']) OR isset ($_GET['param5']) ) {
		// 255 seems like a nice round number to truncate to.
		if (!isset ($_GET['param1'])) {
			$_GET['param1'] = NULL;
		}
		if (!isset ($_GET['param2'])) {
			$_GET['param2'] = NULL;
		}
		if (!isset ($_GET['param3'])) {
			$_GET['param3'] = NULL;
		}
		if (!isset ($_GET['param4'])) {
			$_GET['param4'] = NULL;
		}
		if (!isset ($_GET['param5'])) {
			$_GET['param5'] = NULL;
		}
		$params = array (addslashes ($_GET['param1']),
						 addslashes ($_GET['param2']),
						 addslashes ($_GET['param3']),
						 addslashes ($_GET['param4']),
						 addslashes ($_GET['param5']));
	}
	if (empty ($params)) {
		// If we didn't receive any paremters, just retrieve the requested string.
		$languageString = HOAM_returnLocalizedString ($string);
	} else {
		$languageString = HOAM_returnLocalizedString ($string, $params[0], $params[1], $params[2], $params[3], $params[4]);
	}
	if (empty ($languageString)) {
		$string = explode ('|', $string);
		$languageString = '<h3>' . $string[count ($string) - 1] . '</h3><p>' . $_HOAM_language ['errors']['generic']['no-help-available'] . '</p>';
	}
	echo $wiki -> parse ($languageString);
} else {
	// We don't know what action we're being asked to perform
	header ('Location: /error/unknown_action/');
}

exit ();

?>