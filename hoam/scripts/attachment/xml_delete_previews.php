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
 
if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {

	$xw = xmlwriter_open_memory ();
	xmlwriter_set_indent ($xw, 1);
	xmlwriter_set_indent_string ($xw, ' ');
	xmlwriter_start_document ($xw, '1.0', 'UTF-8');
	// A first element
	xmlwriter_start_element ($xw, 'ajax-response');

	$bytestotal = 0;
	if (isset ($_GET['delete'])) {
		// First, gathter a list of all items in the preview directory.
		$objects = new RecursiveIteratorIterator (new RecursiveDirectoryIterator (HOAM_PATH_ATTACHMENT_PREVIEW, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
		// We want the list in reverse order so we delete any files found, then
		// the parent directories. Otherwise it's directories first, then files
		// meaning we'd need to do multiple passes.
		$reversed = array_reverse (iterator_to_array ($objects));
		foreach ($reversed as $name => $object) {
			if (is_file ($name)) {
				unlink ($name);
			} else {
				rmdir ($name);
			}
		}
		$_HOAM_log -> add ($_HOAM_language ['log']['attachment']['deleted']['previews'], HOAM_MESSAGE_WARNING);
		unset ($objects);
		// Now, regather statistics and report back.
		$objects = new RecursiveIteratorIterator (new RecursiveDirectoryIterator (HOAM_PATH_ATTACHMENT_PREVIEW, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
		$count = iterator_count ($objects);
		foreach ($objects as $name => $object){
			$filesize = $objects -> getSize ();
			$bytestotal += $filesize;
		}
		xmlwriter_write_element ($xw, 'count', number_format ($count));
		xmlwriter_write_element ($xw, 'size', HOAM_humanFilesize ($bytestotal));
		xmlwriter_write_element ($xw, 'status', $_HOAM_language ['log']['attachment']['deleted']['previews']);
	} else {
		// Don't delete anything, just gather statistics
		$objects = new RecursiveIteratorIterator (new RecursiveDirectoryIterator (HOAM_PATH_ATTACHMENT_PREVIEW, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
		foreach ($objects as $name => $object){
			$filesize = $objects -> getSize ();
			$bytestotal += $filesize;
		}
		$regex = new RegexIterator ($objects, '/^.+\.png$/i', RecursiveRegexIterator::GET_MATCH);
		$count = iterator_count ($regex);
		xmlwriter_write_element ($xw, 'count', number_format ($count));
		xmlwriter_write_element ($xw, 'size', human_filesize ($bytestotal));
	}
	// Generate the XML file and echo it back to the client
	xmlwriter_end_element ($xw); // ajax-response
	xmlwriter_end_document ($xw);
	header ('Content-type: text/xml');
	echo xmlwriter_output_memory ($xw);
} else {
	header ('Location: /error/403/');
}

exit ();

?>
