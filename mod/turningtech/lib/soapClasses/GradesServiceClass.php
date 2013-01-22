<?php
/**
 * SOAP service class for Grades services
 * @author jacob
 *
 * NOTE: callers which include/require this class MUST also include/require the following:
 * - [moodle root]/config.php
 * - mod/turningtech/lib.php
 * - mod/turningtech/lib/soapClasses/AbstractSoapServiceClass.php
 */
class TurningTechGradesService extends TurningTechSoapService {
    /**
     * constructor
     * @return void
     */
    public function TurningTechGradesService() {
        parent::TurningTechSoapService();
    }

    /**
     *
     * @param $request
     * @return unknown_type
     */
    public function createGradebookItem($request) {
        $instructor = $this->authenticateRequest($request);
        $course     = $this->getcourseFromRequest($request);
        $dto        = $this->service->createGradebookItem($course, $request->itemTitle, $request->pointsPossible);

        return $dto ? array(
            'return' => $dto
        ) : NULL;
    }

    /**
     *
     * @param $request
     * @return unknown_type
     */
    public function listGradebookItems($request) {
        $instructor = $this->authenticateRequest($request);
        $course     = $this->getcourseFromRequest($request);

        $items = $this->service->getGradebookItemsByCourse($course);
        if ($items === FALSE) {
            $this->throwFault('GradeException', 'Could not get gradebook items for course ' . $request->siteId);
        }

        return $items;
    }

    /**
     *
     * @param $request
     * @return unknown_type
     */
    public function postIndividualScore($request) {
        $instructor = $this->authenticateRequest($request);
        $course     = $this->getcourseFromRequest($request);
        try {
            $dto = $this->createGradebookDto($request);
        }
        catch (Exception $e) {
            $error               = new stdClass();
            $error->deviceId     = isset($request->deviceId) ? $request->deviceId : NULL;
            $error->itemTitle    = isset($request->itemTitle) ? $request->itemTitle : NULL;
            $error->errorMessage = $e->getMessage();
            return array(
                'return' => $error
            );
        }

        if ($error = $this->service->saveGradebookItem($course, $dto, TURNINGTECH_SAVE_NO_OVERRIDE)) {
            return array(
                'return' => $error
            );
        }
    }

    /**
     *
     * @param $request
     * @return unknown_type
     */
    public function postIndividualScoreByDto($request) {
        $instructor = $this->authenticateRequest($request);
        $course     = $this->getCourseFromRequest($request);
        $dto        = $request->sessionGradeDto;

        if ($error = $this->service->saveGradebookItem($course, $dto, TURNINGTECH_SAVE_NO_OVERRIDE)) {
            return array(
                'return' => $error
            );
        }
    }

    /**
     *
     * @param $request
     * @return unknown_type
     */
    public function overrideIndividualScore($request) {
        $instructor = $this->authenticateRequest($request);
        $course     = $this->getCourseFromRequest($request);
        try {
            $dto = $this->createGradebookDto($request);
        }
        catch (Exception $e) {
            $error               = new stdClass();
            $error->deviceId     = isset($request->deviceId) ? $request->deviceId : NULL;
            $error->itemTitle    = isset($request->itemTitle) ? $request->itemTitle : NULL;
            $error->errorMessage = $e->getMessage();
            return array(
                'return' => $error
            );
        }

        if ($error = $this->service->saveGradebookItem($course, $dto, TURNINGTECH_SAVE_ONLY_OVERRIDE)) {
            return array(
                'return' => $error
            );
        }
    }

    /**
     *
     * @param $request
     * @return unknown_type
     */
    public function overrideIndividualScoreByDto($request) {
        $instructor = $this->authenticateRequest($request);
        $course     = $this->getCourseFromRequest($request);
        $dto        = $request->sessionGradeDto;

        if ($error = $this->service->saveGradebookItem($course, $dto, TURNINGTECH_SAVE_ONLY_OVERRIDE)) {
            return array(
                'return' => $error
            );
        }
    }

    /**
     *
     * @param $request
     * @return unknown_type
     */
    public function addToIndividualScore($request) {
        $instructor = $this->authenticateRequest($request);
        $course     = $this->getCourseFromRequest($request);
        try {
            $dto = $this->createGradebookDto($request);
        }
        catch (Exception $e) {
            $error               = new stdClass();
            $error->deviceId     = isset($request->deviceId) ? $request->deviceId : NULL;
            $error->itemTitle    = isset($request->itemTitle) ? $request->itemTitle : NULL;
            $error->errorMessage = $e->getMessage();
            return array(
                'return' => $error
            );
        }

        if ($error = $this->service->addToExistingScore($course, $dto)) {
            return array(
                'return' => $error
            );
        }
    }

    /**
     *
     * @param $request
     * @return unknown_type
     */
    public function addToIndividualScoreByDto($request) {
        $instructor = $this->authenticateRequest($request);
        $course     = $this->getCourseFromRequest($request);
        $dto        = $request->sessionGradeDto;

        if ($error = $this->service->addToExistingScore($course, $dto)) {
            return array(
                'return' => $error
            );
        }
    }

    /**
     *
     * @param $request
     * @return unknown_type
     */
    public function postScores($request) {
        $instructor = $this->authenticateRequest($request);
        $course     = $this->getCourseFromRequest($request);

        //check if the request is for more than 1 score
        if (!is_array($request->sessionGradeDtos)) {
            //if not post one score
            $dto = $request->sessionGradeDtos;
            if ($error = $this->service->saveGradebookItem($course, $dto, TURNINGTECH_SAVE_NO_OVERRIDE)) {
                return array(
                    'return' => $error
                );
            }
        } else { //is so iterate through the array
            $dtoList = $request->sessionGradeDtos;

            $errors = array();

            foreach ($dtoList as $dto) {
                if ($error = $this->service->saveGradebookItem($course, $dto, TURNINGTECH_SAVE_NO_OVERRIDE)) {
                    $errors[] = $error;
                }
            }
            return array(
                'return' => $errors
            );
        }
    }
    /**
     *
     * @param $request
     * @return unknown_type
     */
    public function postScoresOverrideAll($request) {
        $instructor = $this->authenticateRequest($request);
        $course     = $this->getCourseFromRequest($request);

        //check if the request is for more than 1 score
        if (!is_array($request->sessionGradeDtos)) {
            //if not post one score
            $dto = $request->sessionGradeDtos;
            if ($error = $this->service->saveGradebookItem($course, $dto, TURNINGTECH_SAVE_ONLY_OVERRIDE)) {
                return array(
                    'return' => $error
                );
            }
        } else { //if so iterate through the array

            $dtoList = $request->sessionGradeDtos;
            $errors  = array();

            foreach ($dtoList as $dto) {
                if ($error = $this->service->saveGradebookItem($course, $dto, TURNINGTECH_SAVE_ONLY_OVERRIDE)) {
                    $errors[] = $error;
                }
            }
            return $errors;
        }
    }

    public function exportSessionData($request) {
        global $userCourses, $instructor;

        $instructor  = $this->authenticateRequest($request);
        $userCourses = $this->service->getCoursesByInstructor($instructor);

        if (count($userCourses) == 0) {
            $this->throwFault('AuthenticationException', get_string('userisnotinstructor', 'turningtech'));
        }

        return $this->service->importSessionData($request->exportData);
    }

    /**
     * builds a gradebook DTO from the given object
     * @param $request
     * @return DTO
     */
    private function createGradebookDto($request) {
        $dto = new stdClass();

        $fields = array(
            'deviceId',
            'itemTitle',
            'pointsEarned',
            'pointsPossible'
        );
        foreach ($fields as $field) {
            if (!isset($request->$field)) {
                $a        = new stdClass();
                $a->field = $field;
                throw new Exception(get_string('missinggradedtofield', 'turningtech', $a));
            }
            $dto->$field = $request->$field;
        }
        return $dto;
    }
}
?>