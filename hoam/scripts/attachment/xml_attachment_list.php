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

$group_members_class = new HOAM_group_members ('group_members');

// We're using a slightly different permissions check on this, to make sure that
// unauthorized users can't view all of the attachments. Normally we'd check
// against the attachment_view group, but because (by default) members of the
// registered users group can view attachments, we don't want someone to be able
// to view all attachments.
if (isset ($_SESSION['current_user']['id']) AND
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['attachment_edit'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['website']['users']['ids']['root'] === $_SESSION['current_user']['id']))) {

	$attachment_class = new HOAM_attachments ('attachments');
	$advertising_class = new HOAM_advertising ('advertising');
	$budget_class = new HOAM_budget ('budget');
	$budget_invoice_class = new HOAM_budget_invoice ('budget_invoice');
	$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');
	$homeowner_class = new HOAM_homeowner ('homeowners');
	$homeowner_sale_class = new HOAM_homeowner_sale ('homeowner_sale');
	$insurance_class = new HOAM_insurance ('insurance');
	$user_class = new HOAM_user ('users');
	$violaton_class = new HOAM_violation ('violations');
	$work_request_class = new HOAM_work_request ('work_requests');
	
	require_once (HOAM_PATH_FUNCTION . 'attachments.php');

	if (isset ($_GET['id'])) {
		$trusted_id = (string) $attachment_class -> superClean ($_GET['id'], $attachment_class -> returnColumnSize ('id'));
	} else {
		$trusted_id = '';
	}
	
	if (isset ($_GET['num'])) {
		$trusted_number = (int) $_GET['num'];
		if (($trusted_number < 2) OR ($trusted_number > 50)) {
			$trusted_number = 10;
		}
	} else {
		$trusted_number = 10;
	}
	if (($trusted_id != '') AND ($attachment_class -> idExists ($trusted_id))) {
		$attachment_list = $attachment_class -> listByDate ($trusted_id, $trusted_number);
	} else {
		$attachment_list = $attachment_class -> listByDate ('', $trusted_number);
	}
	$xw = xmlwriter_open_memory ();
	xmlwriter_set_indent ($xw, 1);
	xmlwriter_set_indent_string ($xw, ' ');
	xmlwriter_start_document ($xw, '1.0', 'UTF-8');
	// A first element
	xmlwriter_start_element ($xw, 'list');

	// Populate the XML file with the list of addresses on the requested street.
	if (is_array ($attachment_list)) {
		foreach ($attachment_list as $attachment) {
			// The second parameter is for tag attributes
			// This is done using an associative array
			xmlwriter_start_element ($xw, 'attachment');
			xmlwriter_write_element ($xw, 'id', $attachment['attachment_id']);
			xmlwriter_start_element ($xw, 'creator');
			xmlwriter_write_cdata ($xw, $user_class -> returnAccountname ($attachment_class -> returnUserId ($attachment['attachment_id'])));
			xmlwriter_end_element ($xw); // creator
			xmlwriter_start_element ($xw, 'date');
			xmlwriter_write_cdata ($xw, $attachment_class -> returnDateCreated ($attachment['attachment_id']));
			xmlwriter_end_element ($xw); // date
			xmlwriter_start_element ($xw, 'description');
			xmlwriter_write_cdata ($xw, stripslashes ($attachment_class -> returnDescription ($attachment['attachment_id'])));
			xmlwriter_end_element ($xw); // description
			xmlwriter_start_element ($xw, 'html');
			xmlwriter_write_cdata ($xw, HOAM_attachmentReturnHTML ($attachment['attachment_id']));
			xmlwriter_end_element ($xw); // html
								
			// Really long check to display proper attachment owner information.
			$flags = $attachment_class -> returnFlags ($attachment['attachment_id']);
			$owners = $attachment_class -> returnOwners ($attachment['attachment_id']);
			$owner_HTML = NULL;
			switch (TRUE) {
				case (int) $flags & (int) $_HOAM_setting ['attachment']['flags']['advertising'] :
					$owner_HTML .= $_HOAM_language ['field_names']['attachment']['flags']['advertising'] . '<br />';
					if (is_array ($owners)) {
						foreach ($owners as $owner) {
							if ($advertising_class -> idExists ($owner))  {
								$owner_HTML .= '<a href="#">' . $owner . '</a><br />';
							} else {
								$owner_HTML .= '<span class="error">' . $_HOAM_language ['errors']['attachment']['unknown_owner'] . '</span><br />';							
							}
						}
					} elseif ($owners != NULL) {
						if ($advertising_class -> idExists ($owners)) {
							$owner_HTML .= '<a href="/website/advertising/view/' . $owners . '/">' . $owners . '</a>';
						} else {
							$owner_HTML .= '<span class="error">' . $_HOAM_language ['errors']['attachment']['unknown_owner'] . '</span>';							
						}
					} else {
						$owner_HTML .= $_HOAM_language ['common']['na'];
					}
					break;
				case (int) $flags & (int) $_HOAM_setting ['attachment']['flags']['budget'] :
					$owner_HTML .= $_HOAM_language ['field_names']['attachment']['flags']['budget'] . '<br />';
					if (is_array ($owners)) {
						foreach ($owners as $owner) {
							if ($budget_invoice_class -> idExists ($owner)) {
								$inv_number = $budget_invoice_class -> returnNumber ($owner);
								if (empty ($inv_number)) {
									// If we have a known owner, but no invoice
									// number, then it's most probable we have a
									// homeowner invoice (since vendor invoices are
									// required to provide an invoice number when
									// adding to the system).
									$homeowner = $homeowner_class -> returnName ($budget_invoice_class -> returnCustomerId ($owner));
									$owner_HTML .= ucwords ($_HOAM_language ['common']['invoice']) . ' : ' . html_encode (stripslashes ($homeowner)) . '<br />';	
								} else {
									$owner_HTML .= '<a href="/admin/financial/view/' . $owner . '/">' . ucwords ($_HOAM_language ['common']['invoice']) . " #$inv_number</a> from " . html_encode (stripslashes ($budget_vendor_class -> returnName ($budget_invoice_class -> returnVendorId ($owner)))) . '<br />';	
								}
							} else {
								$owner_HTML .= '<span class="error">' . $_HOAM_language ['errors']['attachment']['unknown_owner'] . '</span><br />';							
							}
						}
					} elseif ($owners != NULL) {
						$invoice_id = $budget_class -> returnInvoiceId ($owners);
						if ($invoice_id) {
							$owner_HTML .= '<a href="/admin/financial/view/' . $invoice_id . '/">' . $budget_invoice_class -> returnNumber ($invoice_id) . '</a>';
						} else {
							$owner_HTML .= '<span class="error">' . $_HOAM_language ['errors']['attachment']['unknown_owner'] . '</span>';							
						}
					} else {
						$owner_HTML .= '<span class="error">' . $_HOAM_language ['errors']['attachment']['unknown_owner'] . '</span>';
					}
					break;
				case (int) $flags & (int) $_HOAM_setting ['attachment']['flags']['budget_vendor'] :
					$owner_HTML .= $_HOAM_language ['field_names']['attachment']['flags']['budget_vendor'] . '<br />';
					if (is_array ($owners)) {
						foreach ($owners as $owner) {
							if ($budget_vendor_class -> idExists ($owner)) { 
								$owner_HTML .= '<a href="/admin/budget/vendor/edit/' . $owner . '/">' . html_encode (stripslashes ($budget_vendor_class -> returnName ($owner))) . '</a><br />';
							} else {
								$owner_HTML .= '<span class="error">' . $_HOAM_language ['errors']['attachment']['unknown_owner'] . '</span><br />';							
							}
						}
					} else {
						$owner_HTML .= '<span class="error">' . $_HOAM_language ['errors']['attachment']['unknown_owner'] . '</span>';
					}
					break;
				case (int) $flags & (int) $_HOAM_setting ['attachment']['flags']['homeowner'] :
					$owner_HTML .= $_HOAM_language ['field_names']['attachment']['flags']['homeowner'] . '<br />';
					if (is_array ($owners)) {
						foreach ($owners as $owner) {
							if ($homeowner_class -> idExists ($owner)) { 
								$owner_HTML .= '<a href="/admin/homeowner/edit/' . $owner . '/">' . html_encode (stripslashes ($homeowner_class -> returnName ($owner))) . '</a><br />';
							} else {
								$owner_HTML .= '<span class="error">' . $_HOAM_language ['errors']['attachment']['unknown_owner'] . '</span><br />';							
							}
						}
					} elseif ($owners != NULL) {
						$owner_HTML .= '<a href="/admin/homeowner/edit/' . $owners . '/">' . html_encode (stripslashes ($homeowner_class -> returnName ($owners))) . '</a>';
					} else {
						$owner_HTML .= '<span class="error">' . $_HOAM_language ['errors']['attachment']['unknown_owner'] . '</span>';
					}
					break;
				case (int) $flags & (int) $_HOAM_setting ['attachment']['flags']['homeowner_sale'] :
					$owner_HTML .= $_HOAM_language ['field_names']['attachment']['flags']['homeowner_sale'] . '<br />';
					if (is_array ($owners)) {
						foreach ($owners as $owner) {
							if ($homeowner_sale_class -> idExists ($owner)) { 
								$owner_HTML .= '<a href="/admin/homeowner/edit/' . $owner . '/">' . html_encode (stripslashes (HOAM_returnAddress ($homeowner_sale_class -> returnLotId ($owner)))) . '</a><br />';
							} else {
								$owner_HTML .= '<span class="error">' . $_HOAM_language ['errors']['attachment']['unknown_owner'] . '</span><br />';							
							}
						}
					} elseif ($owners != NULL) {
						$owner_HTML .= '<a href="/admin/homeowner/edit/' . $owners . '/">' . html_encode (stripslashes ($homeowner_class -> returnName ($owners))) . '</a>';
					} else {
						$owner_HTML .= '<span class="error">' . $_HOAM_language ['errors']['attachment']['unknown_owner'] . '</span>';
					}
					break;
				case (int) $flags & (int) $_HOAM_setting ['attachment']['flags']['insurance'] :
					$owner_HTML .= $_HOAM_language ['field_names']['attachment']['flags']['insurance'] . '<br />';
					if (is_array ($owners)) {
						foreach ($owners as $owner) {
							if ($insurance_class -> idExists ($owner)) {
								$owner_HTML .= '<a href="/admin/financial/insurance/edit/' . $owner . '/">' . html_encode (stripslashes ($insurance_class -> returnPolicyNum ($owner))) . '</a><br />';
							} else {
								$owner_HTML .= '<span class="error">' . $_HOAM_language ['errors']['attachment']['unknown_owner'] . '</span><br />';							
							}
						}
					} elseif ($owners != NULL) {
						if ($insurance_class -> idExists ($owners)) {
							$owner_HTML .= '<a href="/admin/financial/insurance/edit/' . $owners . '/">' . html_encode (stripslashes ($insurance_class -> returnPolicyNum ($owners))) . '</a>';
						} else {
							$owner_HTML .= '<span class="error">' . $_HOAM_language ['errors']['attachment']['unknown_owner'] . '</span>';							
						}
					} else {
						$owner_HTML .= '<span class="error">' . $_HOAM_language ['errors']['attachment']['unknown_owner'] . '</span>';
					}
					break;
					case (int) $flags & (int) $_HOAM_setting ['attachment']['flags']['lot'] :
					$owner_HTML .= $_HOAM_language ['field_names']['attachment']['flags']['lot'] . '<br />';
					if (is_array ($owners)) {
						foreach ($owners as $owner) {
							$owner_HTML .= '<a href="/admin/lot/edit/' . $owner . '/">' . HOAM_returnAddress ($owner) . '</a><br />';
						}
					} elseif ($owners != NULL) {
						$owner_HTML .= '<a href="/admin/lot/edit/' . $owners . '/">' . HOAM_returnAddress ($owners) . '</a>';
					} else {
						$owner_HTML .= '<span class="error">' . $_HOAM_language ['errors']['attachment']['unknown_owner'] . '</span>';
					}
					break;
				case (int) $flags & (int) $_HOAM_setting ['attachment']['flags']['violation'] :
					$owner_HTML .= $_HOAM_language ['field_names']['attachment']['flags']['violation'] . '<br />';
					if (is_array ($owners)) {
						foreach ($owners as $owner) {
							$owner_HTML .= '<a href="/admin/violation/edit/' . $owner . '/">' . HOAM_returnAddress ($violation_class -> returnLotId ($owner)) . '</a><br />';
						}
					} elseif ($owners != NULL) {
						$owner_HTML .= '<a href="/admin/violation/letter/' . $owners . '/">' . HOAM_returnAddress ($violation_class -> returnLotId ($owners)) . '</a>';
					} else {
						$owner_HTML .= '<span class="error">' . $_HOAM_language ['errors']['attachment']['unknown_owner'] . '</span>';
					}
					break;
				case (int) $flags & (int) $_HOAM_setting ['attachment']['flags']['work_request'] :
					$owner_HTML .= $_HOAM_language ['field_names']['attachment']['flags']['work_request'] . '<br />';
					if (is_array ($owners)) {
						foreach ($owners as $owner) {
							$owner_HTML .= '<a href="/admin/work_request/edit/' . $owner . '/">' . HOAM_returnAddress ($work_request_class -> returnLotId ($owner)) . '</a><br />';
						}
					} elseif ($owners != NULL) {
						$owner_HTML .= '<a href="/admin/work_request/edit/' . $owners . '/">' . $owners . '</a>';
					} else {
						$owner_HTML .= '<span class="error">' . $_HOAM_language ['errors']['attachment']['unknown_owner'] . '</span>';
					}
					break;
				default:
						$owner_HTML .= '<span class="error">' . $_HOAM_language ['errors']['attachment']['unknown_owner'] . '</span>';
					break;
			}
			xmlwriter_start_element ($xw, 'owner');
			xmlwriter_write_cdata ($xw, $owner_HTML);
			xmlwriter_end_element ($xw); // owner
	
			xmlwriter_end_element ($xw); // attachment
		}
	} else {
		xmlwriter_start_element ($xw, 'attachment');
		xmlwriter_write_element ($xw, 'id', '');
		xmlwriter_write_element ($xw, 'creator', '');
		xmlwriter_write_element ($xw, 'date', '');
		xmlwriter_write_element ($xw, 'description', '');
		xmlwriter_write_element ($xw, 'html', '');
		xmlwriter_write_element ($xw, 'owner', '');
		xmlwriter_end_element ($xw); // attachment
	}
	// Generate the XML file and echo it back to the client
	xmlwriter_end_element ($xw); // list
	xmlwriter_end_document ($xw);
	header ('Content-type: text/xml');
	echo xmlwriter_output_memory ($xw);
} else {
	// User does not have the necessary permissions.
	header ('Location: /error/403/');
}

exit ();

?>