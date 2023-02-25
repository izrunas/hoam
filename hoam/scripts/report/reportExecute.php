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

require_once ('../../hoam-startup.php');

session_start();

$group_members_class = new HOAM_group_members ('group_members');

if (isset ($_GET['report'])) {
	if (isset ($_SESSION['current_user']['id']) AND
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['board'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['officer'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']))) {
		$trusted_report = $group_members_class -> superClean ($_GET['report'], 128);
		$trusted_report = escapeshellcmd ($trusted_report);
		
		if (file_exists (HOAM_PATH_REPORT . $trusted_report)) {
			if (is_readable (HOAM_PATH_REPORT . $trusted_report)) {
				$contents = file_get_contents (HOAM_PATH_REPORT . $trusted_report);
				$index = array ();
				$values = array ();

				$parser = xml_parser_create ('');
				xml_parse_into_struct ($parser, $contents, $values, $index);

				// print_r ($index);
				// print_r ($values);

				// echo $values [$index['NAME'][0]]['value'];
				// echo $values [$index['CONTENT'][0]]['value'];

				ob_start ();
				eval ($values [$index['PHP'][0]]['value']);
				$report_result = ob_get_contents ();
				ob_end_clean ();

				$xml = new XmlWriter ();
				$xml -> openMemory ();
				$xml -> startDocument ('1.0', 'UTF-8');
				$xml -> startElement ('ajax-response');

				$xml -> startElement ('report');
				$xml -> writeCData ($report_result);
				$xml -> endElement ();

				if (isset ($index['SCRIPT']) AND !empty ($values [$index['SCRIPT'][0]]['value'])) {
					$xml -> startElement ('script');
					$xml -> writeCData ($values [$index['SCRIPT'][0]]['value']);
					$xml -> endElement ();
				}

				if (isset ($index['STYLE']) AND !empty ($values [$index['STYLE'][0]]['value'])) {
					$xml -> startElement ('style');
					$xml -> writeCData ($values [$index['STYLE'][0]]['value']);
					$xml -> endElement ();
				}

				$xml -> endElement ();

				// end the document and output
				header ('Content-type: text/xml');
				echo $xml -> outputMemory (TRUE);
			} else {
				printf ($_HOAM_language ['errors']['hoam']['installation']['file-not-readable'], $trusted_report);
			}
		} else {
			printf ($_HOAM_language ['errors']['hoam']['installation']['missing-file'], $trusted_report);
		}
	} else {
		echo $_HOAM_language ['help_pages']['website']['403'];
	}
} else {
	echo $_HOAM_language ['errors']['report']['name_required'];
}

exit ();

?>