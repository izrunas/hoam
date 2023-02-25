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

function HOAM_wikiInit () {
	if ($('article_form')) {
		// We need to select all of the allowed group names so they will be
		// submitted
		article_form = $('article_form');
		Event.observe (article_form, 'submit', function () { setSelectAll ('article|groups'); });

		// If the article is (or will be) a redirect stub, make the necessary
		// changes to the page.
		Element.observe ($('article|redirect'), 'change', HOAM_wikiRedirect);

		Element.observe ($('article|group|remove'), 'click', function () {
			HOAM_moveOption (article_form, 'article|allgroups', 'article|groups');
		});
		Element.observe ($('article|group|add'), 'click', function () {
			HOAM_moveOption (article_form, 'article|groups', 'article|allgroups');
		});
		
		if ($('wiki_history')) {
			Element.observe ($('wiki_history').parentNode.getElementsByTagName ('li')[2], 'click', HOAM_wikiHistory);
		}

		HOAM_wikiRedirect ();
	}
}

function HOAM_wikiHistory () {
	HOAM_tabLoading ($('wiki_history'));
	var url = '/hoam/scripts/article/wikiHistory.php';
	var pars = 'id=' + $F('article|id');
	var myAjax = new Ajax.Updater ($('wiki_history'), url, {method: 'get', parameters: pars, onComplete: function (originalRequest) {
		ZebraTable.stripe (($('wiki_history').getElementsByTagName ('TBODY'))[0]);
	}
	});
}

function HOAM_wikiRedirect () {
	if ($('article|redirect').checked) {
		Element.hide ('hide_if_redirect_1');
		Element.hide ('hide_if_redirect_2');
		Element.hide ('hide_if_redirect_3');
		Element.hide ('hide_if_redirect_4');
		Element.show ('show_if_redirect');
	} else {
		Element.show ('hide_if_redirect_1');
		Element.show ('hide_if_redirect_2');
		Element.show ('hide_if_redirect_3');
		Element.show ('hide_if_redirect_4');
		Element.hide ('show_if_redirect');
	}
}

Event.observe (window, 'load', HOAM_wikiInit);
