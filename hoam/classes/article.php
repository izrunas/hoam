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

class HOAM_article extends HOAM_common {
	// We rely upon the calling code to clean all of the data and format we're given.
	// This class does not perform any cleansing of data!
	
	function changeActions ($id, $value) {
		return ($this -> changeString ($id, 'actions', $value));
	}
	
	function changeAppbar ($id, $value) {
		return ($this -> changeString ($id, 'sidebar', $value));
	}
	
	function changeArticle ($id, $article) {
		return ($this -> changeString ($id, 'article', $article));
	}
	
	function changeChildcount ($id, $value) {
		return ($this -> changeInt ($id, 'childcount', $value));
	}
	
	function changeDatePostStart ($id, $value) {
		return ($this -> changeString ($id, 'datepoststart', $value));
	}

	function changeDatePostEnd ($id, $value) {
		return ($this -> changeString ($id, 'datepostend', $value));
	}

	function changeGroups ($id, $value) {
		return ($this -> changeString ($id, "group_membership", $value));
	}
	
	function changeKeywords ($id, $value) {
		return ($this -> changeString ($id, 'keywords', $value));
	}
	
	function changeLeadin ($id, $value) {
		return ($this -> changeString ($id, 'leadin', $value));
	}

	function changeRedirect ($id, $value) {
		return ($this -> setBit ($id, $value));
	}
	
	function changeRedirectLocation ($id, $value) {
		// Stub. Since we use the 'keywords' field for storing
		// the redirect location, just change that column
		return ($this -> changeString ($id, 'keywords', $value));
	}
	
	function changeRootId ($id, $value) {
		return ($this -> changeString ($id, 'root_id', $value));
	}
	
	function changeScripts ($id, $value) {
		return ($this -> changeString ($id, 'scripts', $value));
	}
	
	function changeSummary ($id, $value) {
		return ($this -> changeString ($id, 'summary', $value) );
	}
	
	function changeTitle ($id, $value) {
		return ($this -> changeString ($id, 'title', $value));
	}
	
	function changeURLName ($id, $value) {
		return ($this -> changeString ($id, 'urlname', $value));
	}
	
	function changeUserId ($id, $value) {
		return ($this -> changeString ($id, 'user_id', $value));
	}
	
	function changeWordCount ($id, $value) {
		return ($this -> changeInt ($id, 'word_count', $value) );
	}
	
	function findId ($urlname, $root_id)
	{
		// Try and locate an article in the table that matches the given urlname
		// Formulate the SQL query
		$query = "SELECT id FROM $this->table
					WHERE urlname = '$urlname'
			        AND root_id = '$root_id'";
		// Execute the query
		$result = $this -> listSimpleQuery ($query);
		return $result[0];
	}
	
	function returnActions ($id) {
		return ($this -> returnItem ($id, 'actions') );
	}
	
	function returnAppbar ($id) {
		return ($this -> returnItem ($id, 'sidebar') );
	}
	
	function returnArticle ($id) {
		return ($this -> returnItem ($id, 'article') );
	}
	
	function returnChildcount ($id) {
		return ($this -> returnItem ($id, 'childcount') );
	}
	
	function returnChildren ($id, $order = " ORDER BY title ASC") {
		// Formulate the SQL query
		$query = "SELECT id FROM $this->table
					WHERE root_id = '$id'
					$order";
		return ($this -> listSimpleQuery ($query) );
	}
	
	function returnCountChildren ($id) {
		return ($this -> returnCountQuery ("WHERE root_id = '$id'") );
	}
	
	function returnCountSearch ($keywords) {
		return ($this -> returnCountQuery ("WHERE MATCH (title, article) AGAINST ('$keywords' IN BOOLEAN MODE)") );
	}
	
	function returnDatePostStart ($id) {
		return ($this -> returnItem ($id, 'datepoststart'));
	}
	
	function returnDatePostEnd ($id) {
		return ($this -> returnItem ($id, 'datepostend'));
	}
	
	function returnGroups ($id) {
		return ($this -> returnItem ($id, "group_membership"));
	}

	function returnKeywords ($id) {
		return ($this -> returnItem ($id, 'keywords'));
	}
	
	function returnLeadin ($id) {
		return ($this -> returnItem ($id, 'leadin'));
	}
	
	function returnRedirect ($id) {
		global $_HOAM_setting;
		return ($this -> isBitSet ($id, $_HOAM_setting ['article']['flags']['redirect']));
	}
	
	function returnRedirectLocation ($id) {
		// Stub. Since we use the 'keywords' field for storing
		// the redirect location, just return that column
		return ($this -> returnItem ($id, 'keywords'));
	}
	
	function returnRootId ($id) {
		return ($this -> returnItem ($id, 'root_id'));
	}
	
	function returnScripts ($id) {
		return ($this -> returnItem ($id, 'scripts'));
	}
	
	function returnSummary ($id) {
		return ($this -> returnItem ($id, 'summary'));
	}
	
	function returnTitle ($id) {
		return ($this -> returnItem ($id, 'title'));
	}
	
	function returnURLName ($id) {
		return ($this -> returnItem ($id, 'urlname'));
	}
	
	function returnWordCount ($id) {
		return ($this -> returnItem ($id, 'word_count'));
	}
	
	function search ($string, $number = 0, $offset = 0) {
		// Formulate the SQL query
		$query = "SELECT id, MATCH (title, article) AGAINST ('$string') AS score
					FROM $this->table
					WHERE MATCH (title, article) AGAINST ('$string' IN BOOLEAN MODE)
					ORDER BY score DESC, datemodified DESC";
		// Are we retrieving a limited number of items?
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		
		return ($this -> listQuery ($query) );
	}
	
}

?>
