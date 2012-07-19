<?php

require_once(dirname(__FILE__).'/locallib.php');
require_once($CFG->libdir.'/formslib.php');
require_once(dirname(__FILE__).'/imageclass.php');

class mod_lightboxgallery_imageadd_form extends moodleform {

    function definition() {

        global $COURSE, $cm;

        $mform =& $this->_form;
        $gallery = $this->_customdata;

        $handlecollisions = !get_config('lightboxgallery', 'overwritefiles');
        $mform->addElement('header', 'general', get_string('addimage', 'lightboxgallery'));

        $mform->addElement('filepicker', 'image', get_string('file'), '0', array('maxbytes' => $COURSE->maxbytes, 'accepted_types' => array('web_image', 'archive')));
        $mform->addRule('image', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('image', 'addimage', 'lightboxgallery');

        if ($this->can_resize()) {
            $resizegroup = array();
            $resizegroup[] = &$mform->createElement('select', 'resize', get_string('edit_resize', 'lightboxgallery'), lightboxgallery_resize_options());
            $resizegroup[] = &$mform->createElement('checkbox', 'resizedisabled', null, get_string('disable'));
            $mform->setType('resize', PARAM_INT);
            $mform->addGroup($resizegroup, 'resizegroup', get_string('edit_resize', 'lightboxgallery'), ' ', false);
            $mform->setDefault('resizedisabled', 1);
            $mform->disabledIf('resizegroup', 'resizedisabled', 'checked');
            $mform->setAdvanced('resizegroup');
        }

        $mform->addElement('hidden', 'id', $cm->id);

        $this->add_action_buttons(true, get_string('addimage', 'lightboxgallery'));

    }

    function validation($data, $files) {
        global $USER;

        if ($errors = parent::validation($data, $files)) {
            return $errors;
        }

        $usercontext = get_context_instance(CONTEXT_USER, $USER->id);
        $fs = get_file_storage();

        if (!$files = $fs->get_area_files($usercontext->id, 'user', 'draft', $data['image'], 'id', false)) {
            $errors['image'] = get_string('required');
            return $errors;
        } else {
            $file = reset($files);
            if ($file->get_mimetype() != 'application/zip' && !$file->is_valid_image()) {
                $errors['image'] = get_string('invalidfiletype', 'error', $file->get_filename());
                // better delete current file, it is not usable anyway
                $fs->delete_area_files($usercontext->id, 'user', 'draft', $data['image']);
            }
        }

        return $errors;
    }


    function can_resize() {
        global $gallery;

        return !in_array($gallery->autoresize, array(AUTO_RESIZE_UPLOAD, AUTO_RESIZE_BOTH));
    }
}

