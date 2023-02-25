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

class HOAM_config extends HOAM_db {

	function __construct ($table) {
		parent::__construct ($table);
	}

	function getValue ($option) {
		$query = "SELECT config_value FROM $this->table
					WHERE config_option = '$option'";
		return ($this -> itemQuery ($query));
	}

	function setValue ($column, $value) {
		global $_HOAM_language;
		if ($value === NULL) {
			$query = "INSERT INTO $this->table (config_option, config_value)
					VALUES ('$column', NULL) ON DUPLICATE KEY UPDATE
					config_value=NULL";
		} else {
			$query = "INSERT INTO $this->table (config_option, config_value)
					VALUES ('$column', '$value') ON DUPLICATE KEY UPDATE
					config_value='$value'";
		}
		// Execute the query
		$result = mysqli_query ($this -> connection, $query);
		if (!$result) {
			echo $_HOAM_language ['errors']['hoam']['database']['problem'];
			if (HOAM_DEBUG) {
				printf ($_HOAM_language ['errors']['hoam']['database']['error'], mysqli_error ($this -> connection));
				printf ($_HOAM_language ['errors']['hoam']['database']['query'], $query);
				$backtrace = debug_backtrace();
				printf ('Backtrace Functions :: %s &rArr; %s &rArr; %s<br />', $backtrace[2]['function'], $backtrace[1]['function'], $backtrace[0]['function']);
				printf ('Backtrace File :: %s<br />', $backtrace[0]['file']);
				printf ('Backtrace Line :: %d<br />', $backtrace[0]['line']);
			} else {
				echo $_HOAM_language ['errors']['hoam']['debugging'];
			}
			echo $_HOAM_language ['errors']['hoam']['contact-support'];
			exit ();
		} else {
			return TRUE;
		}
	}
	
	function loadConfig () {
		$all_config = $this -> listQuery ("SELECT * from $this->table ORDER BY config_option ASC");
		$temp = 'global $_HOAM_setting; $_HOAM_setting = array ();';
		foreach ($all_config as $config_item) {
			$temp .= '$_HOAM_setting';
			$config_item['config_option'] = trim ($config_item['config_option'], "/");
			$exploded = explode ('/', $config_item['config_option']);
			foreach ($exploded as $current) {
				$temp .= "['" . $current . "']";
			}
			$temp .= '="' . preg_replace ('/"/', '\\"', $config_item['config_value']) . '";';
		}
		eval ($temp);
		unset ($temp);
	}
}

?>
