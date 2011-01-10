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

require_once DOKU_INC . 'inc/HTTPClient.php';
require_once DOKU_INC . 'inc/JSON.php';

/**
 * Partially copied from Adrian Lang's syntax_plugin_ac_ac.
 *
 * @author Tobias Sarnowski
 */
class actickets_acclient {
	private $base_url;

	public function __construct($url, $token) {
		if (substr($url, -1) != '/') {
			$url .= '/';
		}
		$url .= 'public/api.php';
		$this->base_url = "{$url}?token={$token}&format=json";
	}

	public function get($path, $data = array()) {
		$client = new DokuHTTPClient();
		$json = new JSON();
		return $json->decode($client->get($this->base_url . '&' .
			"path_info=/{$path}&" .
			buildURLparams($data, '&')));
	}
}
