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

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once DOKU_PLUGIN.'syntax.php';
require_once DOKU_INC.'inc/form.php';

/**
 * @author Tobias Sarnowski
 */
class syntax_plugin_actickets extends DokuWiki_Syntax_Plugin {

    const PATTERN_TICKET = '\d+';
    const PATTERN_TICKET_GRP = '(\d+)';
    const PATTERN_FULLTICKET = '\d+-\d+';
    const PATTERN_FULLTICKET_GRP = '(\d+)-(\d+)';
    const PATTERN_PROJECT = '{{Project:\d+}}';
    const PATTERN_PROJECT_GRP = '{{Project:(\d+)}}';

    /**
     * @var int use to globaly define the project's ID
     */
    private $projectId = null;


    public function getType() {
        return 'substition';
    }

    public function getPType() {
        return 'normal';  // wtf? docs doent tell me. normal|block|stack
    }

    public function getSort() {
        return 32; // http://www.dokuwiki.org/devel:parser:getsort_list
    }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern(
            $this->getConf('actickets.hash').self::PATTERN_FULLTICKET.'|'.
            $this->getConf('actickets.hash').self::PATTERN_TICKET.'|'.
            self::PATTERN_PROJECT
            ,$mode,'plugin_actickets');
    }

    public function handle($match, $state, $pos, &$handler){
        $data = array();
        if (preg_match('/'.self::PATTERN_PROJECT_GRP.'/', $match, $matches)) {
            $this->projectId = $matches[1];
        } else if (preg_match('/'.$this->getConf('actickets.hash').self::PATTERN_FULLTICKET_GRP.'/', $match, $matches)) {
            $data['ticketId'] = $matches[1];
            $data['projectId'] = $matches[2];
        } else if (preg_match('/'.$this->getConf('actickets.hash').self::PATTERN_TICKET_GRP.'/', $match, $matches)) {
            $data['ticketId'] = $matches[1];
            $data['projectId'] = $this->projectId;
            if (is_null($this->projectId)) {
                $data['original'] = $match;
            }
        } else {
            throw new Exception("This should not happen, blame the actickets plugin.");
        }
        return $data;
    }

    public function render($mode, &$renderer, $data) {
        if($mode != 'xhtml') return false;

        if (!empty($data)) {
            if (!is_null($data['projectId'])) {
                $url = $this->getConf('actickets.url');
                if (substr($url, -1) != '/') {
                    $url .= '/';
                }
                $url .= 'public/index.php/projects/'.$data['projectId'].'/tickets/'.$data['ticketId'];
                $renderer->doc .= '<a href="'.$url.'" target="_blank" class="acticket">';
                $renderer->doc .= $this->getConf('actickets.hash').$data['ticketId'];
                $renderer->doc .= '</a>';
            } else {
                $renderer->doc .= $data['original'];
            }
        }

        return true;
    }
}

// vim:ts=4:sw=4:et:
