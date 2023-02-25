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

class HOAM_wiki_parser {
	// This parser is based on http://www.koders.com/php/fidAD83E74BDC1EB90F49F561245C004B9D78312592.aspx

	private $code;
	private $vars;
	
	function __construct () {
		global $_HOAM_country, $_HOAM_language, $_HOAM_setting;
		require (HOAM_PATH . '/hoam/wiki/wiki_code.php');
		require (HOAM_PATH . '/hoam/wiki/wiki_var.php');
		$this -> code = $_HOAM_wiki_code;
		$this -> vars = $_HOAM_wiki_var;
	}

	function parse ($string) {
		require_once (HOAM_PATH_FUNCTION . 'attachments.php');
		require_once (HOAM_PATH_FUNCTION . 'wiki.php');
		
		$i = 1;
		do {
			// Run the var check at least once in case something was replaced in the
			// code (like an internal link, or with {{ORG_EMAIL_BOARD}}).
			$string = preg_replace (array_keys ($this -> vars), array_values ($this -> vars), $string);
			$string = preg_replace (array_keys ($this -> code), array_values ($this -> code), $string);
		} while (strpos ($string, '{{', 0) AND $i++ < 3);

		// Process any attachments listed.
		$attachment_sub = '/\[\[attach:(.*?)\]\]/i';
		$string = preg_replace_callback ($attachment_sub, 'HOAM_attachmentCallback', $string);

		// Create a document tree if requested.
		$wiki_tree_sub = '/\[\[tree:(.*?)\]\]/i';
		$string = preg_replace_callback ($wiki_tree_sub, 'HOAM_wikiTreeCallback', $string);

		// Process any attachments listed.
		$wiki_url_sub = '/\[\[url:(.*?)\]\]/i';
		$string = preg_replace_callback ($wiki_url_sub, 'HOAM_wikiURLCallback', $string);

		// Process any multicolor text
		$color_sub = "/%%%(.*?)%%%/sim";
		$string = preg_replace_callback ($color_sub, 'HOAM_returnColoredStringCallback', $string); 

		return ($string);
	}

}

?>