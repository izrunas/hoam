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

function tasklist_returnTaskMiniHTML ($task_id, $truncate = FALSE) {
	// Returns a string that contains HTML formatted output.
	$tasklist_class = new tasklist ('tasks');
	$user_class = new HOAM_user ('users');
	
	$HTML = "";
	
	if (isset ($_SESSION['current_user']['id'])) {
		$maxlength = $user_class -> returnPreferenceEditCol ($_SESSION['current_user']['id']);
	} else {
		$maxlength = 80;
	}
	
	$duedate = $tasklist_class -> returnDateDue ($task_id);
	$now = date ("Y-m-d g:i:s");
	$tomorrow = date ("Y-m-d g:i:s", mktime (0, 0, 0, date("m"), date("d") + 1, date("Y")));
	
	$HTML .= '<li id="' . $task_id . '"';
	if ($duedate < $now) {
		$HTML .= ' class="error">';
	} elseif (mb_substr ($duedate, 0, 10) == mb_substr ($now, 0, 10)) {
		$HTML .= ' class="warning">';
	} elseif (mb_substr ($duedate, 0, 10) == mb_substr ($tomorrow, 0, 10)) {
		$HTML .= ' class="caution">';
	} else {
		$HTML .= '>';
	}
	// If the task is assigned to this person by being a group member, add a
	// group task icon
	// $HTML .= "<span class="group">&#9758;</span>";
//	$HTML .= "<a href="/apps/tasklist/completed/" . $task_id . "/"><span class="complete">&#10003;</span></a>&nbsp;";
//	$HTML .= "<a href="/apps/tasklist/remove/" . $task_id . "/"><span class="delete">&#10007;</span></a>&nbsp;&nbsp;";
//	$HTML .= "<a href="/apps/tasklist/view/" . $task_id . "/">";
	
	$task = $tasklist_class -> returnTask ($task_id);
	$HTML .= HOAM_returnTruncatedString ($task, $maxlength);
//	$HTML .= "</span>";
//	$HTML .= "</a>";
	$HTML .= '</li>';
	return $HTML;
}

function tasklist_returnTaskHTML ($task_id, $truncate = TRUE) {
	// Returns a string that contains HTML formatted output.
	$tasklist_class = new tasklist ('tasks');
	$user_class = new HOAM_user ('users');
	
	$HTML = "";
	
	if (isset ($_SESSION['current_user']['id'])) {
		$maxlength = $user_class -> returnPreferenceEditCol ($_SESSION['current_user']['id']) * 3;
	} else {
		$maxlength = 240;
	}
	
	$status_array = array ('Not Started', 'In Progress', 'Waiting on Someone Else', 'Deferred', 'Completed');
	$completed_array = array ('0%', '10%', '20%', '30%', '40%', '50%', "60%', '70%", '80%', '90%', '100%');
	
	$HTML .= '<tr>';
	$HTML .= '<td><select name="complete">';
      foreach ($completed_array as $completed) {
          if ($completed == $tasklist_class -> returnComplete ($task_id)) {
            $HTML .= '<option value="' . $completed . '" selected>' . $completed . '</option>';
          } else {
            $HTML .= '<option value="' . $completed . '">' . $completed . '</option>';
          }
        }

	$HTML .= '</select></td>';
	$HTML .= '<td><a href="/apps/tasklist/delete/' . $task_id . '/"><img src="/images/icons/x.png" alt="Delete Task" /></a></td>';
	$HTML .= '<td ';
	
	$duedate = $tasklist_class -> returnDateDue ($task_id);
	$now = date ('Y-m-d');
	$tomorrow = date ('Y-m-d', mktime(0, 0, 0, date('m')  , date('d')+1, date('Y')));
	
	if ($duedate < $now) {
		$HTML .= 'class="error">';
	} elseif (mb_substr ($duedate, 0, 10) == $now) {
		$HTML .= 'class="warning">';
	} elseif (mb_substr ($duedate, 0, 10) == $tomorrow) {
		$HTML .= 'class="caution">';
	} else {
		$HTML .= '>';
	}
	
	echo '<a href="/apps/tasklist/view/' . $task_id . '">';
	$task = $tasklist_class -> returnTask ($task_id);
	$HTML .= HOAM_returnTruncatedString ($task, $maxlength);
	$HTML .= '</a></td>';
	$HTML .= '</tr>';
	return $HTML;
}

?>
