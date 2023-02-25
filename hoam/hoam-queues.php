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

/* The below arrays contain a list of HOAM functions that generally are called
 * in order; think of them as REALLY simply FIFO queues.
 *
 * There are five queue functions: HOAM_queueInsertAfter, HOAM_queueInsertBefore
 * HOAM_queueInsertValue, HOAM_queueRun, and HOAM_queueRemove and the names
 * should be fairly self-explanatory.
 *
 * The one nice additional touch is being able to insert one queue inside
 * another -- HOWEVER, please note that changes made to included queues will
 * NOT be reflected unless the queue is inserted by reference (see $_HOAM_
 * queue_document for an example).
 */

$_HOAM_queue_body = array (
'bodyOpen',
'bodyLogo',
'bodyNavigation',
'bodyContainerOpen',
'bodyToolbar',
'bodyContent',
'bodyContainerClose',
'bodyFooter',
'bodyClose'
);

$_HOAM_queue_head = array (
'headOpen',
'headMeta',
'headCSS',
'headScripts',
'headTitle',
'headClose'
);

$_HOAM_queue_document = array (
'HOAM_timerLog',
'headers',
'documentOpen',
'documentClose',
'HOAM_timerLog'
);

$_HOAM_queuePlugins = array (
);

?>