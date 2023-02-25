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

session_start ();

if (isset ($_GET['id'])) {
	$attachment_class = new HOAM_attachments ('attachments');
	
	require_once (HOAM_PATH_FUNCTION . 'attachments.php');
	$item_id = (string) $attachment_class -> superClean ($_GET['id'], 32);
	if ($attachment_class -> idExists ($item_id)) {
		$datecreated = $attachment_class -> returnDateCreated ($item_id);
		header ('Content-type: ' . $attachment_class -> returnMime ($item_id));
		header ('Content-Disposition: attachment; filename="' . $attachment_class -> returnFilename ($item_id) . '"; modification-date="' . date ('r', strtotime ($datecreated)) . '";');
		header ('Cache-Control: max-age=28800');
//		header ('Cache-Control: must-revalidate');
		// Expire one day in the future.
		header ('Expires: ' . gmdate ('D, d M Y H:i:s e', time() + 86399));
		$attachment_filename = HOAM_PATH . HOAM_attachmentReturnPath ($item_id);
		$handle = fopen ($attachment_filename, 'r');
		// Could this lead to a potential security hole if the file is replaced/
		// corrupted and we try to read past the end of the file?
		echo fread ($handle, filesize ($attachment_filename));
		fclose ($handle);
	} else {
		echo $_HOAM_language ['errors']['attachment']['not-found'];
	}
} else {
	echo $_HOAM_language ['help_pages']['generic']['do_not_call'];
}

?>
