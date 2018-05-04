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
 * Defines the editing form for the poodllrecording question type.
 *
 * @package    qtype
 * @subpackage poodllrecording
 * @copyright  2012 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * PoodLL Recording question type editing form.
 *
 * @copyright  2012 PoodLL Recording Question 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_poodllrecording_edit_form extends question_edit_form {

    protected function definition_inner($mform) {
        $qtype = question_bank::get_qtype('poodllrecording');

        $mform->addElement('select', 'responseformat',
                get_string('responseformat', 'qtype_poodllrecording'), $qtype->response_formats());
        $mform->setDefault('responseformat', 'editor');

        $mform->addElement('editor', 'graderinfo', get_string('graderinfo', 'qtype_poodllrecording'),
                array('rows' => 10), $this->editoroptions);
				
		//Add a place to set a maximum recording time.
	   $mform->addElement('duration', 'timelimit', get_string('timelimit', 'qtype_poodllrecording'));    
       $mform->setDefault('timelimit',0);
	   $mform->disabledIf('timelimit', 'responseformat', 'eq', 'picture');

		// added Justin 20120814 bgimage, part of whiteboard response
		$mform->addElement('filemanager', 'qresource', get_string('qresource', 'qtype_poodllrecording'), null,array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 1));
		$mform->addElement('select', 'boardsize',
			get_string('boardsize', 'qtype_poodllrecording'), $qtype->board_sizes());
			$mform->setDefault('boardsize', 'editor');
		$mform->disabledIf('boardsize', 'responseformat', 'ne', 'picture' );

    }

    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        if (empty($question->options)) {
            return $question;
        }
        $question->responseformat = $question->options->responseformat;
        $question->responsefieldlines = $question->options->responsefieldlines;
        $question->attachments = $question->options->attachments;

		$question->boardsize=$question->options->boardsize;
		$question->timelimit=$question->options->timelimit;



	//Set backimage details, and configure a draft area to accept any uploaded pictures
	//all this and this whole method does, is to load existing files into a filearea
	//so it is not called when creating a new question, only when editing an existing one

	//best to use file_get_submitted_draft_itemid - because copying questions gets weird otherwise
	//$draftitemid =$question->options->backimage;
	$draftitemid = file_get_submitted_draft_itemid(\qtype_poodllrecording\constants::FILEAREA_QRESOURCE);

	file_prepare_draft_area($draftitemid, $this->context->id, 'qtype_poodllrecording', \qtype_poodllrecording\constants::FILEAREA_QRESOURCE,
		!empty($question->id) ? (int) $question->id : null,
		array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 1));
	$question->qresource = $draftitemid;

        $draftid = file_get_submitted_draft_itemid(\qtype_poodllrecording\constants::FILEAREA_GRADERINFO);
        $question->graderinfo = array();
        $question->graderinfo['text'] = file_prepare_draft_area(
            $draftid,           // draftid
            $this->context->id, // context
            'qtype_poodllrecording',      // component
            \qtype_poodllrecording\constants::FILEAREA_GRADERINFO,       // filarea
            !empty($question->id) ? (int) $question->id : null, // itemid
            $this->fileoptions, // options
            $question->options->graderinfo // text
        );
        $question->graderinfo['format'] = $question->options->graderinfoformat;
        $question->graderinfo['itemid'] = $draftid;

        return $question;
    }


    public function qtype() {
        return 'poodllrecording';
    }
}
