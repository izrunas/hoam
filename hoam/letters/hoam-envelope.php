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

$_HOAM_envelope = array ();

// NOTE! As of 20110426 there is a formatting bug in TCPDF that if a TD width
// such as the indent value) is zero (0), it formats incorrectly. Thus, the
// value for indents has currently been set to 0.1  

// All measurements are in mm.
$_HOAM_envelope [0]['description'] = '#10 Invoice Double Window (4 1/8" x 9 1/2")';
$_HOAM_envelope [0]['address']['association'] = -72.4;
$_HOAM_envelope [0]['address']['owner'] = -26.2;
$_HOAM_envelope [0]['indent'] = 0.1;
$_HOAM_envelope [0]['info'] = -46.2;
$_HOAM_envelope [0]['perforation'] = -76.2;
$_HOAM_envelope [0]['value'] = 0;
$_HOAM_envelope [1]['description'] = '#10 Invoice Double Window (4 1/8" x 9 1/2") Offset';
$_HOAM_envelope [1]['address']['association'] = -72.4;
$_HOAM_envelope [1]['address']['owner'] = -26.2;
$_HOAM_envelope [1]['indent'] = 10;
$_HOAM_envelope [1]['info'] = -46.2;
$_HOAM_envelope [1]['perforation'] = -76.2;
$_HOAM_envelope [1]['value'] = 1;

?>
