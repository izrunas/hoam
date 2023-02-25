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

// Include default Javascript libraries and functions.
// Because of dependencies, prototype must be the first javascript library / function loaded.
// Try to load libraries from CDNs first before local.
$HTML  = '<script type="application/javascript" src="//ajax.googleapis.com/ajax/libs/prototype/1.7.3.0/prototype.js"></script>';
$HTML .= '<script type="application/javascript">window.Prototype || document.write(\'<script type="application/javascript" src="/3rdparty/prototype/prototype.js"><\/script>\')</script>';
$HTML .= '<script type="application/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>';
$HTML .= '<script type="application/javascript">window.jQuery || document.write(\'<script type="application/javascript" src="/3rdparty/jquery/jquery-1.12.4.min.js"><\/script>\')</script>';

// Until we migrate off prototype, use another var for jQuery.
$HTML .= '<script type="application/javascript">var $j = jQuery.noConflict();</script>';

$HTML .= '<script type="application/javascript" src="/3rdparty/tabber/tabber-minimized.js"></script>';
$HTML .= '<script type="application/javascript" src="/hoam/javascript/default_library.js"></script>';
$HTML .= '<script type="application/javascript" src="/hoam/javascript/form.js"></script>';

// Leave the startup as the last item in the list, since it may be init()ing scripts loaded before it.
$HTML .= '<script type="application/javascript" src="/hoam/javascript/default_startup.js"></script>';

echo $HTML;

?>
