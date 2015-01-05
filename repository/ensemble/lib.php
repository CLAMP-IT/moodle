<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * Ensemble Video repository plugin.
 *
 * @package    repository_ensemble
 * @copyright  2012 Liam Moran, Nathan Baxley, University of Illinois
 *             2013 Symphony Video, Inc.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class repository_ensemble extends repository {

  public static function get_instance_option_names() {
    return array('evtype');
  }

  public static function instance_config_form($mform) {
    $mform->addElement('select', 'evtype', get_string('type', 'repository_ensemble'), array('video' => get_string('video', 'repository_ensemble'), 'playlist' => get_string('playlist', 'repository_ensemble')));
    $mform->addElement('static', null, '', get_string('typeHelp', 'repository_ensemble'));
  }

  public static function get_type_option_names() {
    return array('ensembleURL', 'serviceUser', 'servicePass', 'authDomain');
  }

  public static function type_config_form($mform, $classname = 'repository') {
    $ensembleURL = get_config('ensemble', 'ensembleURL');
    if (empty($ensembleURL)){
      $ensembleURL = '';
    }
    $serviceUser = get_config('ensemble', 'serviceUser');
    if (empty($serviceUser)){
      $serviceUser = '';
    }
    $servicePass = get_config('ensemble', 'servicePass');
    if (empty($servicePass)){
      $servicePass = '';
    }
    $authDomain = get_config('ensemble', 'authDomain');
    if (empty($authDomain)){
      $authDomain = '';
    }

    $required = get_string('required');
    $mform->addElement('text', 'ensembleURL', get_string('ensembleURL', 'repository_ensemble'), array('value' => $ensembleURL, 'size' => '40'));
    $mform->setType('ensembleURL', PARAM_URL);
    $mform->addRule('ensembleURL', $required, 'required', null, 'client');
    $mform->addElement('static', null, '', get_string('ensembleURLHelp', 'repository_ensemble'));
    $mform->addElement('text', 'serviceUser', get_string('serviceUser', 'repository_ensemble'), array('value' => $serviceUser, 'size' => '40'));
    $mform->setType('serviceUser', PARAM_TEXT);
    $mform->addElement('static', null, '', get_string('serviceUserHelp', 'repository_ensemble'));
    $mform->addElement('passwordunmask', 'servicePass', get_string('servicePass', 'repository_ensemble'), array('value' => $servicePass, 'size' => '40'));
    $mform->addElement('static', null, '', get_string('servicePassHelp', 'repository_ensemble'));
    $mform->addElement('text', 'authDomain', get_string('authDomain', 'repository_ensemble'), array('value' => $authDomain, 'size' => '40'));
    $mform->setType('authDomain', PARAM_HOST);
    $mform->addElement('static', null, '', get_string('authDomainHelp', 'repository_ensemble'));
  }

  public static function plugin_init() {
    $videoRepoId = repository::static_function('ensemble', 'create', 'ensemble', 0, get_system_context(), array('name' => get_string('videoRepo', 'repository_ensemble'), 'evtype' => 'video'), 0);
    $playlistRepoId = repository::static_function('ensemble', 'create', 'ensemble', 0, get_system_context(), array('name' => get_string('playlistRepo', 'repository_ensemble'), 'evtype' => 'playlist'), 0);
    return !empty($videoRepoId) && !empty($playlistRepoId);
  }

  public function __construct($repositoryid, $context = SYSCONTEXTID, $options = array()) {
    parent::__construct($repositoryid, $context, $options);
  }

  public function get_listing($path='', $page='0') {
    global $CFG;
    $list = array();
    $list['object'] = array();
    $list['object']['type'] = 'text/html';
    $list['object']['src'] = $CFG->wwwroot . '/repository/ensemble/ext_chooser/index.php?type=' . $this->options['evtype'];
    $list['nologin']  = true;
    $list['nosearch'] = true;
    $list['norefresh'] = true;
    return $list;
  }

  public function supported_filetypes() {
    return '*';
  }

  public function supported_returntypes() {
    return FILE_EXTERNAL;
  }

}
