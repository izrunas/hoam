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

function HOAM_userNameBanned ($string, $words = NULL) {
	global $_HOAM_setting;
	// Checks to see whether the username the new user is requesting is
	// permitted. The caller may provide a list of words to censor, or rely on
	// the default list.

	// If we weren't given a list of words check, import the default list.
	if ($words === NULL) {
		// Get the list of words to check from the config file.
		$file = $_HOAM_setting ['files']['banned_username'];
		if (is_readable (HOAM_PATH . $file)) {
			$words = file (HOAM_PATH . $file);
		}
	}
	if (!empty ($words)) {
		// Because file reads in the trailing  or \r, we need to strip it
		// out before looking in the array. Also, remove any lines beginning
		// with # (comments)
		$temp_array = array();
		foreach ($words as $word) {
			if (mb_substr ($word, 0, 1) != '#') {
				array_push ($temp_array, trim ($word));
			}
		}
		$words = $temp_array;
		unset ($temp_array);
		if (in_array_nocase ($string, $words)) {
			// If the username was found in the banned list, return TRUE
			return TRUE;
		}
	}
	// Assume the username is OK and return FALSE
	return FALSE;
}

?>
