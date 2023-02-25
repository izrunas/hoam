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

function HOAM_budgetReviewInit () {
	$j('#review_select_all').bind ('click', HOAM_budgetReviewSelectAll);
	$j('#review_selected_action').bind ('change', HOAM_budgetReviewActionSelect);
	$j('#perform_selected_action').bind ('click', HOAM_budgetReviewPerformAction);
	$j('#account_review_tbody img').each (function () {
		if ($j(this).hasClass ('donotprint') && $j(this).hasClass ('less-opacity')) {
			// We found a magnifying glass. Add a few triggers to each.
			$j(this).bind ('click', function (event) {
				HOAM_budgetReviewItem ($j(this));
			});
			$j(this).bind ('mouseover', function (event) {
				$j(this).removeClass ('less-opacity');
			});
			$j(this).bind ('mouseout', function (event) {
				$j(this).addClass ('less-opacity');
			});
		}
	});
}

function HOAM_budgetReviewActionSelect () {
	if ($j('#review_selected_action').val () == '0') {
		$j('#perform_selected_action').prop ('disabled', true)
	} else {
		$j('#perform_selected_action').prop ('disabled', false)
	}
}

function HOAM_budgetReviewItem (object) {
	alert ($j(object).attr ('id'));
}

function HOAM_budgetReviewSelectAll () {
	$j('#account_review_tbody tr').each (function () {
		$j(this).find ('input').each (function () {
			if ($j(this).attr ('type') == 'checkbox') {
				if ($j('#review_select_all').prop ('checked')) {
					$j(this).prop ('checked', true);
				} else {
					$j(this).prop ('checked', false);
				}
			}
		});
	});
}

function HOAM_budgetReviewPerformAction () {
	switch ($j('#review_selected_action').val ()) {
		case 'delete' :
			alert ('delete');
			break;
		case 'insert' :
			alert ('insert');
			break;
		case 'edit' :
			alert ('edit');
			break;
		default:
			break;
	}
}

$j(document).ready (HOAM_budgetReviewInit, false);
