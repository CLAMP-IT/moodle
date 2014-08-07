<?php
/**
 * Form for editing library block instances.
 *
 * @package   block_library
 */
class block_library_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
    	global $DB, $COURSE;
        // Fields for editing HTML block title and contents.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        //$mform->addElement('text', 'config_title', get_string('configtitle', 'block_library'));
        //$mform->setType('config_title', PARAM_MULTILANG);

	$context = get_context_instance(CONTEXT_COURSE, $COURSE->id);

	if(!$role = $DB->get_record('role', array('shortname' => 'librarian'))) {
		print_error('missinglibrole');
	}
	$libusers = get_role_users($role->id, $context, true, 'u.id, u.firstname, u.lastname');

	$librarians = array();
	$librarians[0] = 'None';
	foreach($libusers as $lu) {
		$librarians[$lu->id] = "$lu->lastname, $lu->firstname";
	}

	$mform->addElement('select', 'config_librarian', get_string('select_librarian', 'block_library'), $librarians);

        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean'=>true, 'context'=>$this->block->context);
        $mform->addElement('editor', 'config_text', get_string('configcontent', 'block_library'), null, $editoroptions);
        $mform->setType('config_text', PARAM_RAW); // XSS is prevented when printing the block contents and serving files
    }

    function set_data($defaults) {
        if (!empty($this->block->config) && is_object($this->block->config)) {

            $defaults->config_librarian = $this->block->config->librarian;

            $text = $this->block->config->text;
            $draftid_editor = file_get_submitted_draft_itemid('config_text');
            if (empty($text)) {
                $currenttext = '';
            } else {
                $currenttext = $text;
            }
            $defaults->config_text['text'] = file_prepare_draft_area($draftid_editor, $this->block->context->id, 'block_library', 'content', 0, array('subdirs'=>true), $currenttext);
            $defaults->config_text['itemid'] = $draftid_editor;
            $defaults->config_text['format'] = $this->block->config->format;
        } else {
            $text = '';
            $defaults->config_librarian = '0';
        }

        if (!$this->block->user_can_edit() && !empty($this->block->config->title)) {
            // If a title has been set but the user cannot edit it format it nicely
            $title = $this->block->config->title;
            $defaults->config_title = format_string($title, true, $this->page->context);
            // Remove the title from the config so that parent::set_data doesn't set it.
            unset($this->block->config->title);
        }

        // have to delete text here, otherwise parent::set_data will empty content
        // of editor
        unset($this->block->config->text);
        parent::set_data($defaults);
        // restore $text
        $this->block->config->text = $text;
        if (isset($title)) {
            // Reset the preserved title
            $this->block->config->title = $title;
        }
    }
}
