<?php

require_once($CFG->dirroot . '/mod/turningtech/lib.php');

class block_turningtech extends block_base {
    // maintain reference to integration service
    private $service;

    /**
     * set values for the block
     * @return unknown_type
     */
    function init() {
        $this->title   = get_string('blocktitle', 'block_turningtech');
        $this->version = 2011030720;
        $this->service = new TurningTechIntegrationServiceProvider();

        TurningTechTurningHelper::updateUserDeviceMappings();
    }

    /**
     * (non-PHPdoc)
     * @see docroot/blocks/block_base#specialization()
     */
    function specialization() {
    }

    /**
     * (non-PHPdoc)
     * @see docroot/blocks/block_base#get_content()
     */

    function get_content() {
        /**
        // if content is already set, just return it * generate the javascript that creates the popup
        if($this->content !== NULL) { * @param $message
        return $this->content; * @return unknown_type
        }   */

        // set up content
        $this->content         = new stdClass();
        $this->content->text   = 'hello world';
        $this->content->footer = '';

        // look up device ID
        global $CFG, $USER, $COURSE;
        // verify the user is a student in the current course
        if (TurningTechMoodleHelper::isUserStudentInCourse($USER, $COURSE)) {
            $devicemap = TurningTechTurningHelper::getDeviceIdByCourseAndStudent($COURSE, $USER);
            if ($devicemap) {
                $link                = $devicemap->displayLink();
                $this->content->text = get_string('usingdeviceid', 'block_turningtech', $link);
            } else {
                $this->content->text = get_string('nodeviceforthiscourse', 'block_turningtech');
                // get reminder pop-up messages
                $reminder            = TurningTechTurningHelper::getReminderMessage($USER, $COURSE);
                if (!empty($reminder)) {
                    $this->content->text .= self::popupCode($reminder);
                }
            }
            $this->content->footer .= "<a href='{$CFG->wwwroot}/mod/turningtech/index.php?id={$COURSE->id}'>" . get_string('managemydevices', 'block_turningtech') . "</a>\n";

        } else if (TurningTechMoodleHelper::isUserInstructorInCourse($USER, $COURSE)) {
            $this->content->text = "<a href='{$CFG->wwwroot}/mod/turningtech/index.php?id={$COURSE->id}'>" . get_string('manageturningtechcourse', 'block_turningtech') . "</a>\n";
        }


        if (!empty($this->content->text)) {
            $this->content->text .= "<link rel='stylesheet' type='text/css' href='{$CFG->wwwroot}/mod/turningtech/css/style.css'>";
        }
        return $this->content;
    }

    /**
     * generate the javascript that creates the popup
     * @param $message
     * @return unknown_type
     */
    static function popupCode($message) {
        $popup = "<script type='text/javascript'>";
        $popup .= "alert('" . $message . "')";
        $popup .= "</script>";
        return $popup;
    }

}
