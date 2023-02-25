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

require_once (HOAM_PATH . '3rdparty/tcpdf/tcpdf.php');

class HOAM_LetterPDF extends TCPDF {
	
    //Page header
    public function Header () {
    	// Logo
		if ($this -> getPage () == 1) {
			$this -> SetHeaderMargin (0);
		} else {
			// set font
			$this -> SetFont ('helvetica', 'I', 7);
			$this -> SetHeaderMargin (12.7); // 1/2 inch.
			$this -> SetY (2.7);
			$this -> Cell (0, 10, 'Page ' . $this -> getAliasNumPage () . '/' . $this -> getAliasNbPages (), 1, 0, 'R');
		}
    }
    
    // Page footer
    public function Footer () {
    	$this -> SetFooterMargin (12.7); // 1/2 inch
        // Set font
        $this -> SetFont ('helvetica', 'I', 7);
        // Page number
        $this -> SetY (-12.7);
        $this -> Cell (0, 10, 'Page ' . $this -> getAliasNumPage () . '/' . $this -> getAliasNbPages (), 0, 0, 'R');
    }
} 

?>
