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

function HOAM_housekeepingAttachmentOwners () {
	// Make sure every attachment has an owner that can be identified.
	global $_HOAM_language, $_HOAM_setting;
	require_once (HOAM_PATH_FUNCTION . 'attachments.php');
	
	$attachment_class = new HOAM_attachments ('attachments');
	$advertising_class = new HOAM_advertising ('advertising');
	$budget_class = new HOAM_budget ('budget');
	$budget_invoice_class = new HOAM_budget_invoice ('budget_invoice');
	$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');
	$homeowner_class = new HOAM_homeowner ('homeowners');
	$homeowner_sale_class = new HOAM_homeowner_sale ('homeowner_sale');
	$insurance_class = new HOAM_insurance ('insurance');
	$lot_class = new HOAM_lot ('lots');
	$property_class = new HOAM_property ('misc_property');
	$violaton_class = new HOAM_violation ('violations');
	$work_request_class = new HOAM_work_request ('work_requests');

	$HTML = '';

	$attachment_list = $attachment_class -> listAllId ();
	if (is_array ($attachment_list)) {
		foreach ($attachment_list as $attachment) {
			$flags = $attachment_class -> returnFlags ($attachment);
			$owners = $attachment_class -> returnOwners ($attachment);
			switch (TRUE) {
				case ((int) $flags & (int) $_HOAM_setting ['attachment']['flags']['advertising']) :
					if (is_array ($owners)) {
						foreach ($owners as $owner) {
							if (!$advertising_class -> idExists ($owner))  {
								$HTML .= '<li>' . HOAM_attachmentUnknown ($attachment, $owner) . '</li>';
							}
						}
					} else {
						$HTML .= '<li>' . HOAM_attachmentUnknown ($attachment) . '</li>';
					}
					break;
				case ((int) $flags & (int) $_HOAM_setting ['attachment']['flags']['budget']) :
					if (is_array ($owners)) {
						foreach ($owners as $owner) {
							if (!$budget_class -> idExists ($owner))  {
								$HTML .= '<li>' . HOAM_attachmentUnknown ($attachment, $owner) . '</li>';
							}
						}
					} else {
						$HTML .= '<li>' . HOAM_attachmentUnknown ($attachment) . '</li>';
					}
					break;
				case ((int) $flags & (int) $_HOAM_setting ['attachment']['flags']['budget_invoice']) :
					if (is_array ($owners)) {
						foreach ($owners as $owner) {
							if (!$budget_invoice_class -> idExists ($owner))  {
								$HTML .= '<li>' . HOAM_attachmentUnknown ($attachment, $owner) . '</li>';
							}
						}
					} else {
						$HTML .= '<li>' . HOAM_attachmentUnknown ($attachment) . '</li>';
					}
					break;
				case ((int) $flags & (int) $_HOAM_setting ['attachment']['flags']['budget_vendor']) :
					if (is_array ($owners)) {
						foreach ($owners as $owner) {
							if (!$budget_vendor_class -> idExists ($owner))  {
								$HTML .= '<li>' . HOAM_attachmentUnknown ($attachment, $owner) . '</li>';
							}
						}
					} else {
						$HTML .= '<li>' . HOAM_attachmentUnknown ($attachment) . '</li>';
					}
					break;
				case ((int) $flags & (int) $_HOAM_setting ['attachment']['flags']['homeowner']) :
					if (is_array ($owners)) {
						foreach ($owners as $owner) {
							if (!$homeowner_class -> idExists ($owner))  {
								$HTML .= '<li>' . HOAM_attachmentUnknown ($attachment, $owner) . '</li>';
							}
						}
					} else {
						$HTML .= '<li>' . HOAM_attachmentUnknown ($attachment) . '</li>';
					}
					break;
				case ((int) $flags & (int) $_HOAM_setting ['attachment']['flags']['homeowner_sale']) :
					if (is_array ($owners)) {
						foreach ($owners as $owner) {
							if (!$homeowner_sale_class -> idExists ($owner))  {
								$HTML .= '<li>' . HOAM_attachmentUnknown ($attachment, $owner) . '</li>';
							}
						}
					} else {
						$HTML .= '<li>' . HOAM_attachmentUnknown ($attachment) . '</li>';
					}
					break;
				case ((int) $flags & (int) $_HOAM_setting ['attachment']['flags']['insurance']) :
					if (is_array ($owners)) {
						foreach ($owners as $owner) {
							if (!$insurance_class -> idExists ($owner))  {
								$HTML .= '<li>' . HOAM_attachmentUnknown ($attachment, $owner) . '</li>';
							}
						}
					} else {
						$HTML .= '<li>' . HOAM_attachmentUnknown ($attachment) . '</li>';
					}
					break;
				case ((int) $flags & (int) $_HOAM_setting ['attachment']['flags']['lot']) :
					if (is_array ($owners)) {
						foreach ($owners as $owner) {
							if (!$lot_class -> idExists ($owner))  {
								$HTML .= '<li>' . HOAM_attachmentUnknown ($attachment, $owner) . '</li>';
							}
						}
					} else {
						$HTML .= '<li>' . HOAM_attachmentUnknown ($attachment) . '</li>';
					}
					break;
				case ((int) $flags & (int) $_HOAM_setting ['attachment']['flags']['property']) :
					if (is_array ($owners)) {
						foreach ($owners as $owner) {
							if (!$property_class -> idExists ($owner))  {
								$HTML .= '<li>' . HOAM_attachmentUnknown ($attachment, $owner) . '</li>';
							}
						}
					} else {
						$HTML .= '<li>' . HOAM_attachmentUnknown ($attachment) . '</li>';
					}
					break;
				case ((int) $flags & (int) $_HOAM_setting ['attachment']['flags']['violation']) :
					if (is_array ($owners)) {
						foreach ($owners as $owner) {
							if (!$violation_class -> idExists ($owner))  {
								$HTML .= '<li>' . HOAM_attachmentUnknown ($attachment, $owner) . '</li>';
							}
						}
					} else {
						$HTML .= '<li>' . HOAM_attachmentUnknown ($attachment) . '</li>';
					}
					break;
				case ((int) $flags & (int) $_HOAM_setting ['attachment']['flags']['work_request']) :
					if (is_array ($owners)) {
						foreach ($owners as $owner) {
							if (!$work_request_class -> idExists ($owner))  {
								$HTML .= '<li>' . HOAM_attachmentUnknown ($attachment, $owner) . '</li>';
							}
						}
					} else {
						$HTML .= '<li>' . HOAM_attachmentUnknown ($attachment) . '</li>';
					}
					break;
				default :
					$HTML .= '<li>' . HOAM_attachmentUnknown ($attachment) . '</li>';
					break;
			}
		}
		if (empty ($HTML)) {
			$HTML = '<p>' . $_HOAM_language ['errors']['generic']['none_found'] . '</p>';	
		} else {
			$HTML = '<ul>' . $HTML . '</ul>';
		}
	} else {
		$HTML = '<p>' . $_HOAM_language ['errors']['generic']['none_found'] . '</p>';	
	}
	return ($HTML);
}

function HOAM_housekeepingAttachments () {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting;

	$attachment_class = new HOAM_attachments ('attachments');
	require_once (HOAM_PATH_FUNCTION . 'attachments.php');

/* 
 *//* The below tasks should be completed:
 * 
 * 2) Check for any 'lost' attachments, ie, ones that either
 *   a) Are listed in the database but aren't in the filesystem
 * 
 * */
 
	$HTML = '<h3 style="text-decoration: underline">Attachment Checks</h3>';
	
	$HTML .= '<h4>New Attachments</h4>';
	
	$yesterday = new DateTime ();
	$yesterday -> modify ('-1 day');
	$new_attachments = $attachment_class -> listByDateRange ($yesterday -> format ($_HOAM_country ['date']['format_mysql']), date ($_HOAM_country ['date']['format_mysql']));
	if (is_array ($new_attachments)) {
		foreach ($new_attachments as $attachment) {
			$HTML .= HOAM_attachmentReturnHTML ($attachment);
		}
	} else {
		$HTML .= '<ul><li>No new attachments in the past day.</li></ul>';
	}
	
	$HTML .= '<h4 style="clear:both">Missing Attachments</h4>';
	
	$attachment_list = $attachment_class -> listAllId ();
	if (is_array ($attachment_list)) {
		$table = '<table style="width: 100%;">';
		$table .= '<caption class="error">' . $_HOAM_language ['errors']['attachment']['missing'] . '</caption>';
		$thead = '<thead style="background: silver"><tr style="font-weight: bold">';
		$thead .= '<th style="border-bottom: .1em solid black; border-top: .1em solid black; text-align: center">' . $_HOAM_language ['tables']['column']['generic']['id'] . '</th>';
		$thead .= '<th style="border-bottom: .1em solid black; border-top: .1em solid black; text-align: center">' . $_HOAM_language ['tables']['column']['generic']['path'] . '</th>';
		$thead .= '</tr></thead>';
		$table .= $thead . preg_replace ('/thead/', 'tfoot', $thead);
		$tbody = '';
		foreach ($attachment_list as $attachment) {
			if (!file_exists (HOAM_PATH . HOAM_attachmentReturnPath ($attachment))) {
				$tbody .= '<tr style="color: red"><td>' . $attachment . '</td><td>' . HOAM_attachmentReturnPath ($attachment) . '</td></tr>';
			}
		}
		if (!empty ($tbody)) {
			$table .= '<tbody>' . $tbody . '</tbody>';
			$table .= '</table>';
			$HTML .= $table;
		} else {
			$HTML .= '<ul><li>' . $_HOAM_language ['errors']['generic']['none_found'] . '</li></ul>';
		}
	} else {
		$HTML .= '<ul><li>' . $_HOAM_language ['errors']['attachment']['none-found'] . '</li></ul>';
	}

	$HTML .= '<h4>Abandoned Attachments</h4>';

	$attachment_class = new HOAM_attachments ('attachments');
	$path = new RecursiveDirectoryIterator (HOAM_PATH_ATTACHMENT);
	$table = '<table style="width: 100%;">';
	$table .= '<caption class="error">' . $_HOAM_language ['errors']['attachment']['abandoned'] . '</caption>';
	$thead = '<thead style="background: silver"><tr style="font-weight: bold">';
	$thead .= '<th style="border-bottom: .1em solid black; border-top: .1em solid black; text-align: center">' . $_HOAM_language ['tables']['column']['generic']['path'] . '</th>';
	$thead .= '<th style="border-bottom: .1em solid black; border-top: .1em solid black; text-align: center">' . $_HOAM_language ['tables']['column']['generic']['size'] . '</th>';
	$thead .= '</tr></thead>';
	$table .= $thead . preg_replace ('/thead/', 'tfoot', $thead);
	$tbody = '';
	foreach (new RecursiveIteratorIterator ($path) as $filename => $file) {
		if ($file -> isFile () AND !$attachment_class -> idExists (substr ($file -> getFilename (), 0, 32))) {
    		$tbody .= '<tr style="color: red"><td>' . $filename . '</td><td>' . $file -> getSize() . ' bytes</td></tr>';
		}
	}
	if (!empty ($tbody)) {
		$table .= '<tbody>' . $tbody . '</tbody>';
		$table .= '</table>';
		$HTML .= $table;	
	} else {
		$HTML .= '<ul><li>' . $_HOAM_language ['errors']['generic']['none_found'] . '</li></ul>';
	}
	
	$HTML .= '<h4>Attachments with Unknown Owners</h4>';
	$HTML .= HOAM_housekeepingAttachmentOwners ();
	
	return $HTML;
}

function HOAM_housekeepingDatabase () {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting;
	
	$HTML = '<h3 style="text-decoration: underline">Database Table Checks</h3>';
	$HTML .= '<ul>';
	$_HOAM_log -> add ('Housekeeping: checking database tables.', HOAM_MESSAGE_INFORMATIONAL);
	$_HOAM_db = new HOAM_db ('configuration');
	$table_list = $_HOAM_db -> listSimpleQuery ("SHOW TABLES");
	$HTML .= '<h4>' . count ($table_list) . ' tables found.</h4>'; 
	$db_table_problem = FALSE;
	foreach ($table_list as $table) {
		$HTML .= '<li>' . $table . ': ';
		$result = $_HOAM_db -> listQuery ("CHECK TABLE $table EXTENDED");
		if ($result[0]['Msg_text'] != 'OK') {
			$db_table_problem = TRUE;
			$HTML .= '<span style="color: red">' . $result[0]['Msg_text'] . '</span>';
		} else {
			$HTML .= $result[0]['Msg_text'];
		}
		$HTML .= '</li>';
	}
	$HTML .= '</ul>';
	if ($db_table_problem) {
		$HTML .= '<span class="error">Need to check database integrity!</span>';
	} else {
		$HTML .= '<ul><li>' . $_HOAM_language ['errors']['generic']['none_found'] . '</li></ul>';
	}

	$HTML .= '<h4>Purging Stale User Validation Requests</h4>';
	$HTML .= '<ul>';
	$result = $_HOAM_db -> runQuery ("TRUNCATE TABLE user_validation");
	if ($result) {
		$HTML .= '<li>' . $_HOAM_language ['common']['complete'] . '.</li>';
	} else {
		$HTML .= '<li>' . sprintf ($_HOAM_language ['errors']['hoam']['database']['error'], $result) . '</li>';
	}
	$HTML .= '</ul>';
	$_HOAM_log -> add ('Housekeeping: database table check complete.', HOAM_MESSAGE_INFORMATIONAL);

	return ($HTML);	
}

function HOAM_housekeepingGroups () {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting;

	$_HOAM_log -> add ('Beginning group maintenance.', HOAM_MESSAGE_NOTICE);
	$HTML = '<h3 style="text-decoration: underline">Group Maintenance</h3>';
	$group_class = new HOAM_group ('group_list');
	$group_member_class = new HOAM_group_members ('group_members');

	$HTML .= '<h4>Group_members with a NULL member_id.</h4>';
	$member_list = $group_member_class -> listSimpleQuery ("SELECT id FROM group_members WHERE member_id IS NULL");
	if (is_array ($member_list)) {
		$HTML .= '<ul>';
		foreach ($member_list as $member) {
			$HTML .= '<li>"';
			$HTML .= $group_class -> returnName ($group_member_class -> returnGroupId ($member));
			$group_member_class -> deleteId ($member);
			$HTML .= '": deleted group_member id: ' . $member;
			$HTML .= '</li>';
		}
		$HTML .= '</ul>';
	} else {
		$HTML .= '<ul><li>' . $_HOAM_language ['errors']['generic']['none_found'] . '</li></ul>';
	}
	
	$_HOAM_log -> add ('Group maintenance complete.', HOAM_MESSAGE_NOTICE);
	return ($HTML);
	
	function cleanup () {
		// This function will delete any group_member database entries for which
		// there is not a corresponding user or group entry. This /shouldn't/
		// need to be run more than the first and only time it was intended to
		// be run, however it's possible there are other cleanups in the code
		// that necessitate it being kept around.
		
		$group_class = new HOAM_group ('group_list');
		$group_members_class = new HOAM_group_members ('group_members');
		$user_class = new HOAM_user ('users');
		
		$all_entries = $group_members_class -> listAll ();
		$deleted = array ();
		foreach ($all_entries as $entry) {
			if ($group_class -> idExists ($entry['member_id'])) {
			} elseif ($user_class -> idExists ($entry['member_id'])) { 
			} else {
				array_push ($deleted, $entry);
				$group_members_class -> deleteId ($entry['id']);
			}
		}
	}
}

function HOAM_housekeepingHomeowners () {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting;

	$homeowner_class = new HOAM_homeowner ('homeowners');

	$HTML = '<h3 style="text-decoration: underline">Homeowner Checks</h3>';
	$_HOAM_log -> add ('Housekeeping: checking homeowners.', HOAM_MESSAGE_INFORMATIONAL);
	
	$HTML .= '<h4>Valid Purchase Date</h4>';
	$homeowner_list = $homeowner_class -> listSimpleQuery ("SELECT id FROM homeowners WHERE purchasedate IS NULL OR purchasedate='1970-01-01' OR purchasedate='0000-00-00'");
	if (is_array ($homeowner_list)) {
		$HTML .= '<span class="warning">' . $_HOAM_language ['errors']['homeowner']['purchasedate_not_null'] . '</span>';
		$HTML .= '<ul>';
		foreach ($homeowner_list as $homeowner) {
			$name = stripslashes (html_encode ($homeowner_class -> returnName ($homeowner)));
			$address = HOAM_returnAddress ($homeowner_class -> returnLotId ($homeowner));
			$HTML .= '<li>"' . $name . '" ' . $address . '</li>';
			$_HOAM_log -> add ('Housekeeping: homeowner purchasedate invalid: "' . $name . '" ' . $address, HOAM_MESSAGE_WARNING);
		}
		$HTML .= '</ul>';
	} else {
		$HTML .= '<ul><li>' . $_HOAM_language ['errors']['generic']['none_found'] . '</li></ul>';
	}

	$HTML .= '<h4>Valid Sale Date</h4>';
	$HTML .= '<h5>Checking if Sale Date is in the Future</h5>';
	$homeowner_list = $homeowner_class -> listSimpleQuery ("SELECT id FROM homeowners WHERE saledate>'" . date ($_HOAM_country ['date']['format_mysql']) . "'");
	if (is_array ($homeowner_list)) {
		$HTML .= '<span class="warning">' . $_HOAM_language ['errors']['generic']['date_future'] . '</span>';
		$HTML .= '<ul>';
		foreach ($homeowner_list as $homeowner) {
			$name = stripslashes (html_encode ($homeowner_class -> returnName ($homeowner)));
			$address = HOAM_returnAddress ($homeowner_class -> returnLotId ($homeowner));
			$HTML .= '<li>"' . $name . '" ' . $address . '</li>';
			$_HOAM_log -> add ('Housekeeping: homeowner saledate invalid: "' . $name . '" ' . $address, HOAM_MESSAGE_WARNING);
		}
		$HTML .= '</ul>';
	} else {
		$HTML .= '<ul><li>' . $_HOAM_language ['errors']['generic']['none_found'] . '</li></ul>';
	}
	
	$HTML .= '<h5>Checking if Sale Date is set to "0000-00-00"</h5>';
	$homeowner_list = $homeowner_class -> listSimpleQuery ("SELECT id FROM homeowners WHERE saledate='0000-00-00'");
	if (is_array ($homeowner_list)) {
		$HTML .= '<span class="warning">Fixing Sale Date (Changing 0000-00-00 to 1970-01-01)</span>';
		$HTML .= '<ul>';
		foreach ($homeowner_list as $homeowner) {
			$homeowner_class -> changeSaleDate ($homeowner, '1970-01-01');
			$name = stripslashes (html_encode ($homeowner_class -> returnName ($homeowner)));
			$address = HOAM_returnAddress ($homeowner_class -> returnLotId ($homeowner));
			$HTML .= '<li>"' . $name . '" ' . $address . '</li>';
			$_HOAM_log -> add ('Housekeeping: updating homeowner saledate to 1970-01-01: "' . $name . '" ' . $address, HOAM_MESSAGE_WARNING);
		}
		$HTML .= '</ul>';
	} else {
		$HTML .= '<ul><li>' . $_HOAM_language ['errors']['generic']['none_found'] . '</li></ul>';
	}
	
	return $HTML;
}

function HOAM_housekeepingInstall () {
	global $_HOAM_config, $_HOAM_language, $_HOAM_log, $_HOAM_setting;
	// Send a quick URL GET request to the ARP servers informing them of this
	// installation

	$wiki = new HOAM_wiki_parser ();
	
	$HTML = '<h3 style="text-decoration: underline">System Installation Checks</h3>';
	$HTML .= '<ul>';

	// Does the initial install.php file still exist?
	if (file_exists (HOAM_PATH . 'install.php')) {
		$HTML .= '<li>Original installation file <var>install.php</var> still exists.<ul>';
		$success = unlink (HOAM_PATH . 'install.php');
		if ($success) {
			$HTML .= '<li>Deleted <var>install.php</var>.</li>'; 
		} else {
			$HTML .= '<li class="error">Could not delete <var>install.php</var>. Please check file permissions and/or manually delete <var>install.php</var> as it\'s no longer required.</li>';
		}
		$HTML .= '</ul></li>';
	}
	// Record the existing install (if enabled)
	if (isset ($_HOAM_setting ['website']['record']) AND $_HOAM_setting ['website']['record']) {
		$HTML .= '<li>Record system installation.<ul>';
		$data = array('email_board' => $wiki -> parse ($_HOAM_setting ['organization']['email']['board']),
			'email_management' => $wiki -> parse ($_HOAM_setting ['organization']['email']['management']),
			'name' => $wiki -> parse ($_HOAM_setting ['organization']['name']),
			'url' => $_HOAM_setting ['website']['url'],
			'version' => HOAM_VERSION);
		$success = file_get_contents ("https://hoam.arprs.com/installs/record.php?" . http_build_query ($data));
		if ($success == 1) {
			$HTML .= '<li>' . $_HOAM_language ['field_names']['hoam']['recorded'] . '</li>';
		} else {
			$HTML .= '<li class="error">' . $_HOAM_language ['errors']['hoam']['recorded'] . '</li>';
		}
	} else {
		$HTML .= '<li>' . $_HOAM_language ['errors']['website']['feature_disabled'] . '</li>';
	}
	$HTML .= '</ul></li>';
	// Check if a newer version is available
	$HTML .= '<li>' . $_HOAM_language ['log']['hoam']['version_check'] . '<ul>';
	$success = file_get_contents ("https://hoam.arprs.com/installs/version.php");
	if ($success) {
		// not that I don't trust myself, but on the off chance someone ever
		// hacks the server, let's make sure we don't corrupt any customer
		// installs
		$result = (float) $success;
		if (version_compare (HOAM_VERSION, $result, '<')) {
			$_HOAM_config -> setValue ('hoam/latest', $result);
			$HTML .= '<li>' . sprintf ($_HOAM_language ['help_pages']['hoam']['new_version'], $result) . '/li>';
		}
		$HTML .= '<li>' . $_HOAM_language ['log']['hoam']['version_check_complete'] . '</li>';
	} else {
		$HTML .= '<li class="error">' . $_HOAM_language ['errors']['hoam']['version'] . '</li>';
	}
	$HTML .= '</ul></li>';
	
	$HTML .= '</ul>';
	return ($HTML);
}

function HOAM_housekeepingLog () {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting;

	$HTML = '<h3 style="text-decoration: underline">System Log Checks</h3>';
	$HTML .= '<ul>';
	$_HOAM_log -> add ('Housekeeping: checking logs.', HOAM_MESSAGE_INFORMATIONAL);
	$first_entry = $_HOAM_log -> itemQuery ('SELECT sequence FROM log ORDER BY sequence ASC limit 1');
	$first_entry_date = $_HOAM_log -> itemQuery ("SELECT datecreated FROM log WHERE sequence='$first_entry'");
	$last_entry = $_HOAM_log -> itemQuery ('SELECT sequence FROM log ORDER BY sequence DESC limit 1');
	$last_entry_date = $_HOAM_log -> itemQuery ("SELECT datecreated FROM log WHERE sequence='$last_entry'");
	$HTML .= '<li>First log entry: ' . number_format ($first_entry) . ' on ' . date ($_HOAM_country ['date']['format_long'], strtotime ($first_entry_date)) . '</li>';
	$HTML .= '<li>Last log entry: ' . number_format ($last_entry) . ' on ' . date ($_HOAM_country ['date']['format_long'], strtotime ($last_entry_date)) . '</li>';
	$missing_logs = $_HOAM_log -> findGaps ();
	if (is_array ($missing_logs)) {
		$HTML .= '<li><span class="warning"><strong>' . $_HOAM_language ['errors']['log']['missing_data'] . '</strong></span>';
		$_HOAM_log -> add ('Housekeeping: ' . $_HOAM_language ['errors']['log']['missing_data'], HOAM_MESSAGE_ALERT);
		$HTML .= '<ul>';
		foreach ($missing_logs as $gap) {
			if (strpos ($gap, '%s') === FALSE) {
				$_HOAM_log -> add ('Housekeeping: Logs missing sequence #: ' . $gap . '.', HOAM_MESSAGE_ALERT);
				$HTML .= '<li>Missing sequence #: ' . $gap . '.</li>';
			} else {
				$_HOAM_log -> add ('Housekeeping: Logs missing sequences: ' . sprintf ($gap, 'through') . ' .', HOAM_MESSAGE_ALERT);
				$HTML .= '<li>Missing log sequences: ' . sprintf ($gap, 'through') . '.</li>';
			}
		}
		$HTML .= '</ul></li>';
	} else {
		$HTML .= '<li>' . $_HOAM_language ['errors']['generic']['none_found'] . '</li>';
	}
	$_HOAM_log -> add ('Housekeeping: log check complete.', HOAM_MESSAGE_INFORMATIONAL);
	$HTML .= '</ul>';
	
	return ($HTML);
}

function HOAM_housekeepingViolations () {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting;

	$homeowner_class = new HOAM_homeowner ('homeowners');
	$violation_class = new HOAM_violation ('violations');

	$HTML = '<h3 style="text-decoration: underline">Violation Checks</h3>';
	$_HOAM_log -> add ('Housekeeping: checking violations.', HOAM_MESSAGE_INFORMATIONAL);
	
	$HTML .= '<h5>Checking if Resolution Date is set to "0000-00-00"</h5>';
	$violation_list = $violation_class -> listSimpleQuery ("SELECT id FROM violations WHERE dateresolution='0000-00-00'");
	if (is_array ($violation_list)) {
		$HTML .= '<span class="warning">Fixing Resolution Date (Changing 0000-00-00 to 1970-01-01)</span>';
		$HTML .= '<ul>';
		foreach ($violation_list as $violation) {
			$violation_class -> changeResolutionDate ($violation, '1970-01-01');
			$homeowner = $homeowner_class -> returnOwner ($violation_class -> returnViolationDate ($violaton), $violation_class -> returnLotId ($violation));
			$name = stripslashes (html_encode ($homeowner_class -> returnName ($homeowner)));
			$address = HOAM_returnAddress ($homeowner_class -> returnLotId ($homeowner));
			$HTML .= '<li>"' . $name . '" ' . $address . '</li>';
			$_HOAM_log -> add ('Housekeeping: updating violation dateresolution to 1970-01-01: "' . $name . '" ' . $address, HOAM_MESSAGE_WARNING);
		}
		$HTML .= '</ul>';
	} else {
		$HTML .= '<ul><li>' . $_HOAM_language ['errors']['generic']['none_found'] . '</li></ul>';
	}
	
	return $HTML;
}

function HOAM_housekeepingWiki () {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting;
	
	$HTML = '<h3 style="text-decoration: underline">Wiki/Article Checks</h3>';
	$_HOAM_log -> add ('Housekeeping: checking wiki/articles.', HOAM_MESSAGE_INFORMATIONAL);
	$article_class = new HOAM_article ('articles');
	$article_list = $article_class -> listAllId ();
	if (is_array ($article_list)) {
		require_once (HOAM_PATH_FUNCTION . 'wiki.php');
		foreach ($article_list as $article) {
			if (HOAM_wikiURL ($article) == FALSE) {
				$changes .= '<li>';
				$changes .= 'URLName: "' . stripslashes (html_encode ($article_class -> returnURLname ($article))) . '" ';
				$changes .= 'Title: "' . stripslashes (html_encode ($article_class -> returnTitle ($article))) . '"';  
				$changes .= '</li>';
			}
		}
		if (!empty ($changes)) {
			$HTML .= '<span class="warning">' . $_HOAM_language ['errors']['article']['unknown_root'] . '</span>';
			$HTML .= '<ul>';
			$HTML .= $changes;
			$HTML .= '</ul>';
		} else {
			$HTML .= '<ul><li>' . $_HOAM_language ['errors']['generic']['none_found'] . '</li></ul>';
		}
	} else {
		$HTML .= '<ul></li>' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</li></ul>';
	}

	// Need to add a check for budget entries with the parent_entry_id the same
	// as the id.

	return ($HTML);
}

?>
