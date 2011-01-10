<?php
/*
 * DokuWiki ActiveCollab Ticket Plugin
 * Copyright (C) 2011  Tobias Sarnowski
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/../../../');
define('NOSESSION',true);
require_once(DOKU_INC.'inc/init.php');

require_once('acclient.php');

if (!isset($_REQUEST['tickets'])) {
	die('missing parameter tickets');
}

$actickets_plugin = plugin_load('syntax', 'actickets');

$acclient = new actickets_acclient(
	$actickets_plugin->getConf('actickets.url'),
	$actickets_plugin->getConf('actickets.token')
);

$tickets = array();
foreach ($_REQUEST['tickets'] as $ticket) {
	$tickets[] = $acclient->get('/projects/'.$ticket['projectId'].'/tickets/'.$ticket['ticketId']);
}

$json = new JSON();
die($json->encode($tickets));
