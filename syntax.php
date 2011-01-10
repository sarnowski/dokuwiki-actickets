<?php
/*
 * Copyright 2011 Tobias Sarnowski
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once DOKU_PLUGIN.'syntax.php';

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
                $renderer->doc .= '<a href="'.$url.'" target="_blank">';
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
