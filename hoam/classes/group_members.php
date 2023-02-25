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

class HOAM_group_members extends HOAM_common {
	// We rely upon the calling code to clean all of the data and format we're given.
	// This class does not perform any cleansing of data!
	
	function addUserToGroup ($user_id, $group_id) {
		global $_HOAM_setting;

		$id = $this -> add ($user_id);
		if ($id) {
			$this -> changeGroupId ($id, $group_id);
			$this -> changeMemberId ($id, $user_id);
			$this -> changeFlags ($id, $_HOAM_setting ['group']['flags']['individual']);
		}
		return ($id);
	}

	function changeGroupId ($id, $value) {
		return ($this -> changeString ($id, 'owner_id', $value) );
	}
	
	function changeMemberId ($id, $value) {
		return ($this -> changeString ($id, 'member_id', $value) );
	}
	
	function isGroup ($id) {
		global $_HOAM_setting;
		$result = $this -> bitCompare ($_HOAM_setting ['group']['flags']['group'], 'flags', $id, 'id');
		if ($result[0] == $_HOAM_setting ['group']['flags']['group']) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function isMember ($group_id, $member_id) {
		global $_HOAM_log, $_HOAM_setting;
		// Groups can be sub-groups of others. This function will look through
		// the group_members table to determine whether the given member_id is
		// part of either the requested group or a sub-group.
		//
		// *** This function needs to be optimized to only look at groups (via the
		// *** flags column) instead of every group member. Right now it also tries
		// *** to check if $member_id is a member of individuals
		//
		// *** Maybe add an array of group_ids that the member is KNOWN not to be a member of...
		// *** would need to check that we are at the bottom of the tree, and work backwards.
		// *** Would keep from checking the same branch over and over from different angles
		//
		// Get a list of all the members in a given group_id
		
		$group_class = new HOAM_group ('group_list');
		$user_class = new HOAM_user ('users');
				
		if (HOAM_DEBUG) {
			$backtrace = debug_backtrace();
			// Made change to the function name reporting based on warning msg.
			// was previously found to be [2], but changed to count - 1.
			$backtrace = debug_backtrace();
			if (empty ($backtrace[2]['function'])) {
				$message = '';
			} else {
				$message = PHP_EOL . "Backtrace Function :: " . $backtrace[2]['function'];
			}
			$message .= PHP_EOL . "Backtrace File :: " . $backtrace[0]['file'];
			$message .= PHP_EOL . "Backtrace Line :: " . $backtrace[0]['line'];
			$_HOAM_log -> add ('Tested Group :: backtrace: ' . $message, HOAM_MESSAGE_DEBUG);
			$_HOAM_log -> add ('Tested Group :: ' . $group_id . ' (' . $group_class -> returnName ($group_id) . ')', HOAM_MESSAGE_DEBUG);
		}
		
		if ($group_id == $member_id) {
			// If we somehow end up checking to see if we're identical, return.
			// This /should/ only happen if there's a loop in the group tree,
			// which would be an error that should be flagged somehow
			$_HOAM_log -> add ("Detected group_id ($group_id) the same as member_id ($member_id) in group_members" . $id, HOAM_MESSAGE_ERROR);
			return TRUE;
		} else {
			$group_members = $this -> listMembers ($group_id);
		}
		// If there are NO members of the group, then it won't be an array.
		if (is_array ($group_members)) {
			// Is the member_id part of the current $group_members array?
			if (in_array ($member_id, $group_members)) {
				// Yup, stop checking.
				$_HOAM_log -> add ('User is a direct member of the group.', HOAM_MESSAGE_DEBUG);
				return TRUE;
			} else {
				$_HOAM_log -> add ('Testing Subgroups', HOAM_MESSAGE_DEBUG);
				$anonymous_groups = array ($_HOAM_setting['group']['ids']['anonymous'], $_HOAM_setting['group']['ids']['everyone']);
				foreach ($group_members as $member) {
					$id = $this -> itemQuery ("SELECT id FROM $this->table WHERE member_id='$member' AND owner_id='$group_id'");
					$_HOAM_log -> add ('ID: ' . $id, HOAM_MESSAGE_DEBUG);
					if ($this -> isBitSet ($id, $_HOAM_setting['group']['flags']['group'])) {
						$_HOAM_log -> add ('Is a group: ' . $group_class -> returnName ($member), HOAM_MESSAGE_DEBUG);
						if (in_array ($member, $anonymous_groups)) {
							$_HOAM_log -> add ('Member is an anonymous group', HOAM_MESSAGE_DEBUG);
							return TRUE;
						} elseif ($this -> isMember ($member, $member_id)) {
							return TRUE;
						}
					} else {
						$_HOAM_log -> add ('NOT a group: ' . $user_class -> returnAccountName ($member), HOAM_MESSAGE_DEBUG);
					}
				}
				return FALSE;
			}
			return FALSE;
		}
		return FALSE;
	}
	
	function isPerson ($id) {
		global $_HOAM_setting;
		$result = $this -> bitCompare ($_HOAM_setting ['group']['flags']['individual'], "flags", $id, "id");
		if ($result[0] == $_HOAM_setting ['group']['flags']['individual']) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	function listByGroup ($group_id, $number = 0, $offset = 0) {
		$query = "SELECT id FROM $this->table
					WHERE owner_id = '$group_id'";
		// Are we retrieving a limited number of items?
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		// Execute the query
		return ($this -> listSimpleQuery ($query) );
	}

	function listByMember ($member_id, $number = 0, $offset = 0) {
		$query = "SELECT id FROM $this->table
					WHERE member_id = '$member_id'";
		// Are we retrieving a limited number of items?
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		// Execute the query
		return ($this -> listSimpleQuery ($query) );
	}

	function listGroupsByMember ($member_id, $number = 0, $offset = 0) {
		$query = "SELECT owner_id FROM $this->table
					WHERE member_id = '$member_id'";
		// Are we retrieving a limited number of items?
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		// Execute the query
		return ($this -> listSimpleQuery ($query) );
	}
	
	function listMembers ($group_id, $number = 0, $offset = 0) {
		$query = "SELECT member_id FROM $this->table
					WHERE owner_id = '$group_id'";
		// Are we retrieving a limited number of items?
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		// Execute the query
		return ($this -> listSimpleQuery ($query) );
	}

	function removeMember ($group, $member) {
		$query = "SELECT id FROM $this->table
					WHERE owner_id='$group' AND member_id='$member'";
		$id = $this -> itemQuery ($query);
		return ($this -> deleteId ($id));
	}

	function removeUserFromGroup ($user_id, $group_id) {
		global $_HOAM_setting;

		$id = $this -> itemQuery ("SELECT id FROM $this->table WHERE flags & " . $_HOAM_setting ['group']['flags']['individual'] . " AND member_id='$user_id' AND owner_id='$group_id'");
		if ($id) {
			return ($this -> deleteId ($id));
		}
		return ($id);
	}
		
	function returnCountMembers ($value) {
		return ($this -> returnCountQuery ("WHERE owner_id = '$value'"));
	}
	
	function returnGroupId ($id) {
		return ($this -> returnItem ($id, 'owner_id'));
	}

	function returnMemberId ($id) {
		return ($this -> returnItem ($id, 'member_id'));
	}
	
}

?>
