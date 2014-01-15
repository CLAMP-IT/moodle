<?php
/**
 * @package   turnitintool
 * @copyright 2012 Turnitin
 *
 */
require_once ($CFG->dirroot.'/course/moodleform_mod.php');
require_once ($CFG->dirroot.'/mod/turnitintool/lib.php');

class mod_turnitintool_mod_form extends moodleform_mod {

    function definition() {

        global $CFG, $DB, $COURSE, $USER;
        $mform    =& $this->_form;
        
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('turnitintoolname', 'turnitintool'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $input = new stdClass();
        $input->length=40;
        $input->field=get_string('turnitintoolname','turnitintool');
        $mform->addRule('name', get_string('maxlength','turnitintool',$input), 'maxlength', 40, 'client');
        $mform->addRule('name', get_string('maxlength','turnitintool',$input), 'maxlength', 40, 'server');
        
        if (is_callable(array($this,'add_intro_editor'))) {
            $this->add_intro_editor(true, get_string('turnitintoolintro', 'turnitintool'));
        } else {
            $mform->addElement('htmleditor', 'intro', get_string('turnitintoolintro', 'turnitintool'));
            $mform->setType('intro', PARAM_RAW);
            $mform->addRule('intro', get_string('required'), 'required', null, 'client');
            $input = new stdClass();
            $input->length=1000;
            $input->field=get_string('turnitintoolintro','turnitintool');
            $mform->addRule('intro', get_string('maxlength','turnitintool',$input), 'maxlength', 1000, 'client');
            $mform->addRule('intro', get_string('maxlength','turnitintool',$input), 'maxlength', 1000, 'server');
        }
        
        $typeoptions = turnitintool_filetype_array();

        $mform->addElement('select', 'type', get_string('type', 'turnitintool'), $typeoptions);
        turnitintool_modform_help_icon('type', 'types', 'turnitintool', $mform);
        $mform->addRule('type', get_string('required'), 'required', null, 'client');
        $mform->setDefault('type', $CFG->turnitin_default_type);
        
        $options = array();
        for($i = 1; $i <= 5; $i++) {
            $options[$i] = $i;
        }
        
        $mform->addElement('select', 'numparts', get_string('numberofparts', 'turnitintool'), $options);
        turnitintool_modform_help_icon('numparts', 'numberofparts', 'turnitintool', $mform);
        $mform->setDefault('numparts', $CFG->turnitin_default_numparts);
        
        $suboptions = array( 0 => get_string('namedparts','turnitintool'), 1 => get_string('portfolio','turnitintool'));
        
        $mform->addElement('hidden','portfolio',0);
        $mform->setType('portfolio', PARAM_RAW);
        
        $maxtii=20971520;
        if ($CFG->maxbytes>$maxtii) {
            $maxbytes1=$maxtii;
        } else {
            $maxbytes1=$CFG->maxbytes;
        }
        if ($COURSE->maxbytes>$maxtii) {
            $maxbytes2=$maxtii;
        } else {
            $maxbytes2=$COURSE->maxbytes;
        }
        
        $options=get_max_upload_sizes($maxbytes1, $maxbytes2);
        
        $mform->addElement('select', 'maxfilesize', get_string('maxfilesize', 'turnitintool'), $options);
        turnitintool_modform_help_icon('maxfilesize', 'maxfilesize', 'turnitintool', $mform);
        
        unset($options);
        for ($i=0;$i<=100;$i++) {
            $options[$i]=$i;
        }
        if ( !is_callable(array($this,'standard_grading_coursemodule_elements')) ) {
            $mform->addElement('modgrade', 'grade', get_string('overallgrade', 'turnitintool'));
            turnitintool_modform_help_icon('grade', 'overallgrade', 'turnitintool', $mform);
            $mform->setDefault('grade', $CFG->turnitin_default_grade);
        }

        $ynoptions = array( 0 => get_string('no'), 1 => get_string('yes'));

        $mform->addElement('hidden','defaultdtstart',time());
        $mform->setType('defaultdtstart', PARAM_RAW);
        $mform->addElement('hidden','defaultdtdue',strtotime('+7 days'));
        $mform->setType('defaultdtdue', PARAM_RAW);
        $mform->addElement('hidden','defaultdtpost',strtotime('+7 days'));
        $mform->setType('defaultdtpost', PARAM_RAW);
        
        if (isset($this->_cm->id)) {
            $turnitintool=turnitintool_get_record("turnitintool", "id", $this->_cm->instance);
            $updating=true;
            $numsubs=turnitintool_count_records('turnitintool_submissions','turnitintoolid',$turnitintool->id);
        } else {
            $updating=false;
            $numsubs=0;
        }

        if ($updating AND $CFG->turnitin_useanon AND isset($turnitintool->anon) AND $numsubs>0) {
            $staticout=(isset($turnitintool->anon) AND $turnitintool->anon) ? get_string('yes', 'turnitintool') : get_string('no', 'turnitintool');
            $mform->addElement('static', 'static', get_string('turnitinanon', 'turnitintool'), $staticout);
            $mform->addElement('hidden', 'anon', $turnitintool->anon);
            $mform->setType('anon', PARAM_RAW);
            turnitintool_modform_help_icon('anon', 'turnitinanon', 'turnitintool', $mform);
        } else if ($CFG->turnitin_useanon) {
            $mform->addElement('select', 'anon', get_string('turnitinanon', 'turnitintool'), $ynoptions);
            $mform->setType('anon', PARAM_RAW);
            turnitintool_modform_help_icon('anon', 'turnitinanon', 'turnitintool', $mform);
            $anon_default = isset($CFG->turnitin_default_anon) ? $CFG->turnitin_default_anon : 0;
            $mform->setDefault('anon', $anon_default);
        } else {
            $mform->addElement('hidden', 'anon', 0);
            $mform->setType('anon', PARAM_RAW);
        }
        
        $mform->addElement('select', 'studentreports', get_string('studentreports', 'turnitintool'), $ynoptions);
        turnitintool_modform_help_icon('studentreports', 'studentreports', 'turnitintool', $mform);
        $mform->setDefault('studentreports', $CFG->turnitin_default_studentreports);
        
        $mform->addElement('header', 'general', get_string('advancedoptions', 'turnitintool'));
        $mform->addElement('select', 'allowlate', get_string('allowlate', 'turnitintool'), $ynoptions);
        $mform->setDefault('allowlate', $CFG->turnitin_default_allowlate);
        
        $genoptions = array( 0 => get_string('genimmediately1','turnitintool'), 1 => get_string('genimmediately2','turnitintool'), 2 => get_string('genduedate','turnitintool'));
        $mform->addElement('select', 'reportgenspeed', get_string('reportgenspeed', 'turnitintool'), $genoptions);
        $mform->setDefault('reportgenspeed', $CFG->turnitin_default_reportgenspeed);
        
        $suboptions = array( 0 => get_string('norepository','turnitintool'), 1 => get_string('standardrepository','turnitintool'));

        if ($CFG->turnitin_userepository=="1") {
            $suboptions[2] = get_string('institutionalrepository','turnitintool');
        }
        
        $mform->addElement('select', 'submitpapersto', get_string('submitpapersto', 'turnitintool'), $suboptions);
        $mform->setDefault('submitpapersto', $CFG->turnitin_default_submitpapersto);
        
        $mform->addElement('select', 'spapercheck', get_string('spapercheck', 'turnitintool'), $ynoptions);
        $mform->setDefault('spapercheck', $CFG->turnitin_default_spapercheck);
        
        $mform->addElement('select', 'internetcheck', get_string('internetcheck', 'turnitintool'), $ynoptions);
        $mform->setDefault('internetcheck', $CFG->turnitin_default_internetcheck);

        $mform->addElement('select', 'journalcheck', get_string('journalcheck', 'turnitintool'), $ynoptions);
        $mform->setDefault('journalcheck', $CFG->turnitin_default_journalcheck);

        if ($numsubs>0) {

            $staticout=(isset($turnitintool->excludebiblio) AND $turnitintool->excludebiblio)
                    ? get_string('yes', 'turnitintool') : get_string('no', 'turnitintool');
            $mform->addElement('static', 'static', get_string('excludebiblio', 'turnitintool'), $staticout);
            $mform->addElement('hidden', 'excludebiblio', $turnitintool->excludebiblio);
            $mform->setType('excludebiblio', PARAM_RAW);

            $staticout=(isset($turnitintool->excludequoted) AND $turnitintool->excludequoted)
                    ? get_string('yes', 'turnitintool') : get_string('no', 'turnitintool');
            $mform->addElement('static', 'static', get_string('excludequoted', 'turnitintool'), $staticout);
            $mform->addElement('hidden', 'excludequoted', $turnitintool->excludequoted);
            $mform->setType('excludequoted', PARAM_RAW);

            $staticout=(isset($turnitintool->excludetype) AND $turnitintool->excludetype==1)
                    ? get_string('excludewords', 'turnitintool') : get_string('excludepercent', 'turnitintool');
            $staticval=(isset($turnitintool->excludevalue) AND empty($turnitintool->excludevalue))
                    ? get_string('nolimit', 'turnitintool') : $turnitintool->excludevalue.' '.$staticout;
            $mform->addElement('static', 'static', get_string('excludevalue', 'turnitintool'), $staticval);
            $mform->addElement('hidden', 'excludevalue', $turnitintool->excludevalue);
            $mform->setType('excludevalue', PARAM_RAW);
            $mform->addElement('hidden', 'excludetype', $turnitintool->excludetype);
            $mform->setType('excludetype', PARAM_RAW);

        } else {
            $mform->addElement('select', 'excludebiblio', get_string('excludebiblio', 'turnitintool'), $ynoptions);
            $mform->setDefault('excludebiblio', $CFG->turnitin_default_excludebiblio);

            $mform->addElement('select', 'excludequoted', get_string('excludequoted', 'turnitintool'), $ynoptions);
            $mform->setDefault('excludequoted', $CFG->turnitin_default_excludequoted);

            $mform->addElement('text', 'excludevalue', get_string('excludevalue', 'turnitintool'), array('size'=>'12'));
            $mform->setType('excludevalue', PARAM_RAW);
            $input = new stdClass();
            $input->length=9;
            $input->field=get_string('excludevalue','turnitintool');
            $mform->addRule('excludevalue', get_string('maxlength','turnitintool',$input), 'maxlength', 9, 'client');
            $mform->addRule('excludevalue', get_string('maxlength','turnitintool',$input), 'maxlength', 9, 'server');
            $mform->addRule('excludevalue', null, 'numeric', null, 'client');
            $mform->addRule('excludevalue', null, 'numeric', null, 'server');

            $typeoptions = array( 1 => get_string('excludewords','turnitintool'), 2 => get_string('excludepercent','turnitintool'));

            $mform->addElement('select', 'excludetype', '', $typeoptions);
            $mform->setDefault('excludetype', 1);
        }
        
        if ( isset($CFG->turnitin_useerater) && $CFG->turnitin_useerater=='1') {
            $handbook_options = array(
                                        1 => get_string('erater_handbook_advanced','turnitintool'),
                                        2 => get_string('erater_handbook_highschool','turnitintool'),
                                        3 => get_string('erater_handbook_middleschool','turnitintool'),
                                        4 => get_string('erater_handbook_elementary','turnitintool'),
                                        5 => get_string('erater_handbook_learners','turnitintool'),
                                    );
            $dictionary_options = array(
                                        'en_US' => get_string('erater_dictionary_enus','turnitintool'),
                                        'en_GB' => get_string('erater_dictionary_engb','turnitintool'),
                                        'en'    => get_string('erater_dictionary_en','turnitintool')
                                    );
            $mform->addElement('select', 'erater', get_string('erater', 'turnitintool'), $ynoptions);
            $mform->setDefault('erater', 0);
            
            $mform->addElement('select', 'erater_handbook', get_string('erater_handbook', 'turnitintool'), $handbook_options);
            $mform->setDefault('erater_handbook', 2);
            $mform->disabledIf('erater_handbook','erater', 'eq', 0);
            
            $mform->addElement('select', 'erater_dictionary', get_string('erater_dictionary', 'turnitintool'), $dictionary_options);
            $mform->setDefault('erater_dictionary', 'en_US');
            $mform->disabledIf('erater_dictionary','erater', 'eq', 0);
            
            $mform->addElement('checkbox', 'erater_spelling', get_string('erater_categories', 'turnitintool'), " ".get_string('erater_spelling', 'turnitintool'));
            $mform->setDefault('erater_spelling', false);
            $mform->disabledIf('erater_spelling','erater', 'eq', 0);
            
            $mform->addElement('checkbox', 'erater_grammar', '', " ".get_string('erater_grammar', 'turnitintool'));
            $mform->setDefault('erater_grammar', false);
            $mform->disabledIf('erater_grammar','erater', 'eq', 0);
            
            $mform->addElement('checkbox', 'erater_usage', '', " ".get_string('erater_usage', 'turnitintool'));
            $mform->setDefault('erater_usage', false);
            $mform->disabledIf('erater_usage','erater', 'eq', 0);
            
            $mform->addElement('checkbox', 'erater_mechanics', '', " ".get_string('erater_mechanics', 'turnitintool'));
            $mform->setDefault('erater_mechanics', false);
            $mform->disabledIf('erater_mechanics','erater', 'eq', 0);
            
            $mform->addElement('checkbox', 'erater_style', '', " ".get_string('erater_style', 'turnitintool'));
            $mform->setDefault('erater_style', false);
            $mform->disabledIf('erater_style','erater', 'eq', 0);
            
        }

        if ( isset($CFG->turnitin_transmatch) && $CFG->turnitin_transmatch=='1') {
            $mform->addElement( 'select', 'transmatch', get_string('transmatch', 'turnitintool'), $ynoptions );
            $mform->setDefault( 'transmatch', false);
        } else {
            $mform->addElement( 'hidden', 'transmatch', 0 );
            $mform->setType('transmatch', PARAM_RAW);
        }
        
        $mform->addElement('hidden','ownerid',NULL);
        $mform->setType('ownerid', PARAM_RAW);

        $features = new stdClass;
        $features->groups = true;
        $features->groupings = true;
        $features->groupmembersonly = true;
        
        if ( is_callable(array($this,'standard_grading_coursemodule_elements')) ) {
            $this->standard_grading_coursemodule_elements();
        }
        
        $this->standard_coursemodule_elements($features);
        $this->add_action_buttons();

    }
}

/* ?> */
