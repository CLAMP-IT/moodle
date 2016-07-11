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
 * This file defines the edit form settings for this plugin
 *
 * @package   block_annotate
 * @copyright Textensor Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Annotate block config form class
 *
 * @copyright Textensor Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_annotate_edit_form extends block_edit_form {

    /**
     * Defines the form fields
     */
    protected function specific_definition($mform) {
        $mform->addElement ( 'header', 'configheader', get_string ( 'config_header_label', 'block_annotate' ) );

        $individual = get_string ( 'config_individual_access', 'block_annotate' );
        $group = get_string ( 'config_group_access', 'block_annotate' );
        $accessoptions = array (
                'individual' => $individual,
                'group' => $group
        );
        $mform->addElement ( 'select', 'config_access', get_string ( 'config_access_label', 'block_annotate' ), $accessoptions );
        $mform->setDefault ( 'config_access', $individual );
        $mform->setType ( 'config_access', PARAM_TEXT );

        $mform->addElement ( 'text', 'config_shareuser', get_string ( 'config_shareuser_label', 'block_annotate' ), array (
                'size' => '30'
        ) );
        $mform->setDefault ( 'config_shareuser', '' );
        $mform->setType ( 'config_shareuser', PARAM_EMAIL );
        $mform->disabledIf ( 'config_shareuser', 'config_access', 'eq', 'individual' );
        $mform->addRule ( 'config_shareuser', get_string ( 'invalid_email_msg', 'block_annotate' ), 'email', null, 'client' );

        $mform->addHelpButton ( 'config_access', 'config_access_label', 'block_annotate' );
        $mform->addHelpButton ( 'config_shareuser', 'config_shareuser_label', 'block_annotate' );
    }

    /**
     * Ensures that doc owner email provided if group access
     */
    public function validation($data, $files) {
        $errors = parent::validation ( $data, $files );
        // .
        if ($data ['config_access'] == 'group') {
            if ($data ['config_shareuser'] == "") {
                $errors ['config_shareuser'] = get_string ( 'enter_email_msg', 'block_annotate' );
            }
        }
        return $errors;
    }
}
