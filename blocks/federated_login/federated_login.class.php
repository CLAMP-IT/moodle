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
 * Manage federated logins
 *
 * @package block_federated_login
 * @copyright 2014 Smith College ITS
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Definitions
 */
define('BLOCK_FEDERATED_LOGIN_DEFAULT_SCHOOL_COUNT', 5);

/**
 * The federated login
 *
 * @copyright 2014 Smith College ITS
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_federated_login_handler {

    public $content;
    public $cookiename = '';
    public $cookievalue = '';
    public $numberofschools = BLOCK_FEDERATED_LOGIN_DEFAULT_SCHOOL_COUNT;
    public $schools = array();
    public $homeschool = array();
    public $cookiemanager = '';

    public function __construct() {

        $this->get_cookievalue();
        $this->get_schools();
        $this->get_homeschool();
        $this->get_cookiemanager();

    }

    public function get_content() {
        $this->content .= $this->print_login_url();
	$this->content .= $this->print_logindescription();
        $this->content .= $this->print_homeschool();
        $this->content .= $this->print_cookiemanager();
        $this->content .= $this->print_help_link();
        return $this->content;;
    }

    public function get_cookievalue() {

        global $CFG;

        if (isset($CFG->block_federated_login_home_cookie_name)) {
            $this->cookiename = $CFG->block_federated_login_home_cookie_name;
        }

        if ( array_key_exists( $this->cookiename , $_COOKIE )) {
            $this->cookievalue = $_COOKIE[$this->cookiename];
        }
    }

    public function get_schools() {

        global $CFG;

        if (isset($CFG->block_federated_login_school_count)) {
            $this->numberofschools = $CFG->block_federated_login_school_count;
        }

        for ($i = 1; $i <= $this->numberofschools; $i++) {
            $idproperty = "block_federated_login_school_id_$i";
            $id   = $CFG->$idproperty;
            $nameproperty = "block_federated_login_school_name_$i";
            $name = $CFG->$nameproperty;
            $idpproperty = "block_federated_login_school_idp_$i";
            $idp  = $CFG->$idpproperty;
            if (empty($id) || empty($name) || empty($idp)) {
                continue;
            }
            $this->schools[$id] = array(
                'id' => $id,
                'name' => $name,
                'idp'  => $idp
            );
        }
    }

    public function get_homeschool() {
        if (empty($this->cookievalue)) {
            $this->homeschool = '';
            return;
        }
        foreach ($this->schools as $school) {
            if ($school['idp'] == $this->cookievalue) {
                $this->homeschool = $school;
                return;
            }
        }
    }

    public function get_cookiemanager() {

        global $CFG;

        if (isset($CFG->block_federated_login_home_cookie_manager)) {
            $this->cookiemanager = $CFG->block_federated_login_home_cookie_manager;
        }
    }

    public function print_homeschool() {

        $accounthome = (!empty($this->homeschool)) ? $this->homeschool['name'] : get_string('notset', 'block_federated_login');

	/*
        $accounthomediv = html_writer::tag('div',
            get_string('accounthome', 'block_federated_login') . " $accounthome",
            array('class' => 'login-account-home'));
	*/
	// removed by sryder for now since we don't have the school idp info set up
	$accounthomediv = '';

        return $accounthomediv;
    }

    public function print_login_url() {

        global $CFG;

        if (!isloggedin() || isguestuser()) {
            $url = get_login_url();
            $loginlink = html_writer::link($url, get_string('login'));
            $currentstatus = get_string('notloggedin', 'block_federated_login');
        } else {
            $url = new moodle_url($CFG->httpswwwroot.'/login/logout.php', array('sesskey' => sesskey()));
            $loginlink = html_writer::link($url, get_string('logout'));
            $currentstatus = get_string('loggedin', 'block_federated_login');
        }
        $currently = get_string('currently', 'block_federated_login');
        $currentdiv = html_writer::tag('div', "$currently $currentstatus", array('class' => 'login-status-div'));
        $logindiv = html_writer::tag('div', $loginlink, array('class' => 'login-link-div'));
        //return "$currentdiv\n$logindiv";
	// changed by sryder to not print the current status of whether or not the user is logged in, just show the log in and out links
        return "$logindiv";
    }

    // added by sryder to print a description with login help
    public function print_logindescription() {

        global $CFG;

	$logindescription = '';
        if (!isloggedin() || isguestuser()) {
            $logindescription = get_string('logindescription', 'block_federated_login');
        }
        $logindescdiv = html_writer::tag('div', $logindescription, array('class' => 'login-description-div'));
        return "$logindescdiv";
    }




    public function print_cookiemanager() {

        if (empty($this->cookiemanager)) {
            return '';
        }

        $linkattributes = array('target' => '_blank');
        $cookiemanagerlink = html_writer::link( $this->cookiemanager ,
            get_string('managehome', 'block_federated_login') ,
            $linkattributes );
        $cookiemanagerdiv = html_writer::tag('div', $cookiemanagerlink, array('class' => 'cookie-manager'));
        return $cookiemanagerdiv;
    }

    public function print_help_link() {

        global $CFG;

        $helpurl = $CFG->block_federated_login_help_url;
        $helptext = $CFG->block_federated_login_help_text;

        if (empty($helpurl) || empty($helptext)) {
            return '';
        }

        $link = html_writer::link($helpurl, $helptext, array('target' => '_blank'));
        $helpdiv = html_writer::tag('div', $link, array('class' => 'federated-login-help'));
        return $helpdiv;

    }

}
