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

// We're note doing any authentication on this, since there's really no need.
// It's just the publicly available data, nothing secret.
if (isset ($_GET['string'])) {
	global $_HOAM_log;
	$trusted_string = (string) $_HOAM_log -> superClean ($_GET['string'], 128);
	$trusted_string = preg_replace ('/^\//', '', $trusted_string, 1);
	$array = explode ('|', $trusted_string);
	$elem =& $GLOBALS['_HOAM_country'];
	foreach ($array as &$key ) {
       	$elem =& $elem[$key];
	}
	echo $elem;
} else {
	// We don't know what action we're being asked to perform
	header ('Location: /error/unknown_action/');
}

exit ();

?>