<?php
/**
 * SOAP server class for courses service
 * @author jacob
 *
 * NOTE: callers which include/require this class MUST also include/require the following:
 * - [moodle root]/config.php
 * - mod/turningtech/lib.php
 * - mod/turningtech/lib/soapClasses/AbstractSoapServiceClass.php
 */
class TurningTechCoursesService extends TurningTechSoapService {
    /**
     * constructor
     * @return void
     */
    public function TurningTechCoursesService() {
        parent::TurningTechSoapService();
    }

    /**
     *
     * @param $request
     * @return array of courseSiteView
     */
    public function getTaughtCourses($request) {
        $instructor = NULL;
        $courses    = NULL;

        $instructor = $this->authenticateRequest($request);
        $courses    = $this->service->getCoursesByInstructor($instructor);
        if ($courses === FALSE) {
            $this->throwFault('CourseException', get_string('couldnotgetlistofcourses', 'turningtech'));
        }

        return $courses;
    }

    /**
     *
     * @param $request
     * @return array of courseSiteView
     */
    public function getTaughtCoursesExt($request) {
        $instructor = NULL;
        $courses    = NULL;

        $instructor = $this->authenticateRequest($request);
        $courses    = $this->service->getExtCoursesByInstructor($instructor);
        if ($courses === FALSE) {
            $this->throwFault('CourseException', get_string('couldnotgetlistofcourses', 'turningtech'));
        }

        return $courses;
    }

    /**
     *
     * @param $request
     * @return array of courseParticipantDTO
     */
    public function getClassRoster($request) {
        $instructor = NULL;
        $course     = NULL;
        $roster     = NULL;

        $instructor = $this->authenticateRequest($request);
        $course     = $this->getCourseFromRequest($request);

        if ($this->service->userHasRosterPermission($instructor, $course)) {
            $roster = $this->service->getClassRoster($course);
            if ($roster === FALSE) {
                $this->throwFault("CourseException", get_string('couldnotgetroster', 'turningtech', $request->siteId));
            }
            return $roster;
        } else {
            $this->throwFault("SiteConnectException", get_string('norosterpermission', 'turningtech'));
        }
    }

    /**
     *
     * @param $request
     * @return array of courseParticipantDTO
     */
    public function getClassRosterExt($request) {
        $instructor = NULL;
        $course     = NULL;
        $roster     = NULL;

        $instructor = $this->authenticateRequest($request);
        $course     = $this->getCourseFromRequest($request);

        if ($this->service->userHasRosterPermission($instructor, $course)) {
            $roster = $this->service->getExtClassRoster($course);
            if ($roster === FALSE) {
                $this->throwFault("CourseException", get_string('couldnotgetroster', 'turningtech', $request->siteId));
            }
            return $roster;
        } else {
            $this->throwFault("SiteConnectException", get_string('norosterpermission', 'turningtech'));
        }
    }
}
?>