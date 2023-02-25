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

function HOAM_returnAdvertisement ($position) {
	global $_HOAM_setting;
	$advertising_class = new HOAM_advertising ('advertising');
	$attachment_class = new HOAM_attachments ('attachments');

	if (isset ($position)) {
		$advertisement_list = $advertising_class -> ListCurrentForPosition ($position);
		if (is_array ($advertisement_list)) {
			// If there is more than one advertisement for this location, display one at random.
			$count = count ($advertisement_list) - 1;
			$ad = rand (0, $count);
			$advertising_class -> recordImpression ($advertisement_list[$ad]['id']);
			$HTML = '<a href="/hoam/scripts/advertising/link.php?id=' . $advertisement_list[$ad]['id'] . '"';
			$desc = $advertising_class -> returnDescription ($advertisement_list[$ad]['id']);
			if ($desc) {
				$HTML .= ' title="' . $desc . '"';
			}
			$HTML .= '>';
			$attachment_list = $attachment_class -> listWithOwner ($advertisement_list[$ad]['id'], $_HOAM_setting ['attachment']['flags']['advertising']);
			if (is_array ($attachment_list)) {
				$HTML .= '<img src="/hoam/scripts/attachment/view.php?id=' . $attachment_list[0] . '" class="advertisement" />';
			}
			$HTML .= '</a>';
			return ($HTML);
		}
	}
	
}

?>
