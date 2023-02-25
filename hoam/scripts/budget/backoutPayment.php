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

if (isset ($_SESSION['current_user']['id']) AND
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']))) {
	if (isset ($_GET['customer_id'])) {

		$budget_class = new HOAM_budget ('budget');
		
		$trusted_customer_id = (string) $budget_class -> superClean ($_GET['customer_id'], $budget_class -> returnColumnSize ('customer_id'));
		if (isset ($_GET['memo'])) {
			$trusted_memo = (string) $budget_class -> superClean ($_GET['memo'], $budget_class -> returnColumnSize ('memo'));
			$query = "SELECT id FROM budget WHERE customer_id='$trusted_customer_id' AND memo='$trusted_memo' AND flags & " . $_HOAM_setting['budget']['flags']['payment'];
			$payment_list = $budget_class -> listSimpleQuery ($query);
			if (is_array ($payment_list)) {
				$total_payment_amount = $budget_class -> itemQuery ("SELECT SUM(amount) FROM budget WHERE customer_id='$trusted_customer_id' AND memo='$trusted_memo' AND flags & " . $_HOAM_setting['budget']['flags']['payment']);
				$remaining_payment_amount = $total_payment_amount;
				foreach ($payment_list as $payment) {
					$remaining_payment_amount -= $budget_class -> returnAmount ($payment);
					$parent_entry_id = $budget_class -> returnParentEntryId ($payment);
					// Find out if the parent_entry_id has been marked as posted.
					if ($budget_class -> returnDatePosted ($parent_entry_id) != NULL) {
						// Since we're backing out the payment, the parent_entry
						// can no longer be considered 'posted'.
//						$budget_class -> changeDatePosted ($parent_entry_id, '');
						// Because ChangeDatePosted doesn't want to see NULL
						// values, it overwrites them. This is a workaround
						// until that decision can be reviewed.
 						$query = "UPDATE budget SET dateposted=NULL WHERE id='$parent_entry_id'";
						$budget_class -> runQuery ($query);
					}
					// Remove the payment from the system.
					$budget_class -> deleteId ($payment);
				}
				$remaining_payment_amount = number_format ($remaining_payment_amount, 2);
				if ($remaining_payment_amount == 0) {
					echo 'payment backed out successfully.';
				} else {
					echo -$remaining_payment_amount . ' not backed out.';
				}
			} else {
				echo $_HOAM_language ['errors']['budget']['no_entries'];
			}
		} else {
			// We don't know which payment to back out
			header ('Location: /error/id_required/');
		}
	} else {
		// We don't know which id to modify
		header ('Location: /error/unknown_id/');
	}
} else {
	// User does not have the necessary permissions.
	header ('Location: /error/403/');
}

exit ();

?>