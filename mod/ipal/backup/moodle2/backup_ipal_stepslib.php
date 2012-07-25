<?php

class backup_ipal_activity_structure_step extends backup_activity_structure_step {
 
    protected function define_structure() {
 
        $userinfo = $this->get_setting_value('userinfo');
 
$ipal = new backup_nested_element('ipal', array('id'), array('course', 
            'name', 'intro', 'introformat', 'timeopen',
            'timeclose', 'preferredbehaviour', 'attempts',
            'attemptonlast', 'grademethod', 'decimalpoints', 'questiondecimalpoints',
            'reviewattempt', 'reviewcorrectness', 'reviewmarks',
            'reviewspecificfeedback', 'reviewgeneralfeedback',
            'reviewrightanswer', 'reviewoverallfeedback',
            'questionsperpage', 'shufflequestions', 'shuffleanswers',
            'questions', 'sumgrades', 'grade', 'timecreated',
            'timemodified', 'timelimit', 'password', 'subnet', 'popup', 
            'delay1', 'delay2', 'showuserpicture',
            'showblocks'));
 
        $answered = new backup_nested_element('answered');
 
        $answer = new backup_nested_element('answer', array('id'), array(
            'user_id', 'question_id', 'quiz_id', 'answer_id', 'class_id', 'ipal_id', 'ipal_code', 'a_text', 'time_created'));
 
        $answered_archive = new backup_nested_element('answered_archive');
 
        $answered_archive_element = new backup_nested_element('answered_archive_element', array('id'), array(
            'user_id', 'question_id', 'quiz_id', 'answer_id', 'class_id', 'ipal_id', 'ipal_code', 'a_text', 'shortname', 'instructor', 'time_created', 'sent'));
 
       $ipal->add_child($answered);
	$answered->add_child($answer);

        $ipal->add_child($answered_archive);    
	$answered_archive->add_child($answered_archive_element);

	$ipal->set_source_table('ipal', array('id' => backup::VAR_ACTIVITYID));

        if ($userinfo) {
            $answer->set_source_sql('
            SELECT *
              FROM {ipal_answered}
             WHERE ipal_id = ?',
            array(backup::VAR_ACTIVITYID));

	    $answered_archive_element->set_source_sql('
            SELECT *
              FROM {ipal_answered_archive}
             WHERE ipal_id = ?',
            array(backup::VAR_ACTIVITYID));

        } 

 	return $this->prepare_activity_structure($ipal);
    }
}
