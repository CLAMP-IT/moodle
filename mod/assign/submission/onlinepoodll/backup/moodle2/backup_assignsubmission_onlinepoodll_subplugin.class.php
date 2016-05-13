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
 * This file contains the class for backup of this submission plugin
 *
 * @package    assignsubmission_onlinepoodll
 * @copyright 2012 Justin Hunt {@link http://www.poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ( ! defined( 'ASSIGNSUBMISSION_ONLINEPOODLL_TABLE' ) ){ 
	define('ASSIGNSUBMISSION_ONLINEPOODLL_TABLE', 'assignsubmission_onlinepoodl');
	define('ASSIGNSUBMISSION_ONLINEPOODLL_COMPONENT', 'assignsubmission_onlinepoodll');
	define('ASSIGNSUBMISSION_ONLINEPOODLL_FILEAREA', 'submissions_onlinepoodll');
}

/**
 * Provides the information to backup onlinepoodll submissions
 *
 * This just adds its filearea to the annotations and records the submissiontext and format
 *
 * @package    assignsubmission_onlinepoodll
 * @copyright 2012 Justin Hunt {@link http://www.poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_assignsubmission_onlinepoodll_subplugin extends backup_subplugin {

    /**
     *
     * Returns the subplugin information to attach to submission element
     * @return backup_subplugin_element
     */
    protected function define_submission_subplugin_structure() {

        // create XML elements
        $subplugin = $this->get_subplugin_element(); // virtual optigroup element
        $subpluginwrapper = new backup_nested_element($this->get_recommended_name());
        $subpluginelement = new backup_nested_element('submission_onlinepoodll', null, array('widgettype', 'submission'));

        // connect XML elements into the tree
        $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($subpluginelement);

        // set source to populate the data
        $subpluginelement->set_source_table(ASSIGNSUBMISSION_ONLINEPOODLL_TABLE, array('submission' => backup::VAR_PARENTID));

        $subpluginelement->annotate_files(ASSIGNSUBMISSION_ONLINEPOODLL_COMPONENT, 'submissions_onlinepoodll', 'submission');
        return $subplugin;
    }

}
