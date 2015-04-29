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
 * File for SOAP Grades services
 * @author jacob
 * @package    mod_turningtech
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * NOTE: callers which include/require this class MUST also include/require the following:
 * - [moodle root]/config.php
 * - mod/turningtech/lib.php
 * - mod/turningtech/lib/soapClasses/AbstractSoapServiceClass.php
 */
/**
 * SOAP service class for Grades services
 * @author jacob
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * NOTE: callers which include/require this class MUST also include/require the following:
 * - [moodle root]/config.php
 * - mod/turningtech/lib.php
 * - mod/turningtech/lib/soapClasses/AbstractSoapServiceClass.php
 */
class TurningTechGradesService extends TurningTechSoapService
{
    /**
     * Create gradebook items
     * @param unknown_type $request
     * @return unknown_type
     */
    public function creategradebookitem($request) {
        $instructor = $this->authenticaterequest($request);
        $course     = $this->getcourseFromRequest($request);
        $dto        = $this->service->createGradebookItem($course, $request->itemTitle, $request->pointsPossible);
        return $dto ? array(
            'return' => $dto
        ) : null;
    }
    /**
     * List gradebook items
     * @param unknown_type $request
     * @return unknown_type
     */
    public function listgradebookitems($request) {
        $instructor = $this->authenticaterequest($request);
        $course     = $this->getcourseFromRequest($request);
        $items = $this->service->getGradebookItemsByCourse($course);
        if ($items === false) {
            $this->throwfault('GradeException', 'Could not get gradebook items for course ' . $request->siteId);
        }
        return $items;
    }
    /**
     * Post individual score
     * @param unknown_type $request
     * @return unknown_type
     */
    public function postindividualscore($request) {
        $instructor = $this->authenticaterequest($request);
        $course     = $this->getcourseFromRequest($request);
        try {
            $dto = $this->creategradebookdto($request);
        } catch (Exception $e) {
            $error               = new stdClass();
            $error->deviceId     = isset($request->deviceId) ? $request->deviceId : null;
            $error->itemTitle    = isset($request->itemTitle) ? $request->itemTitle : null;
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
     * Post individual score by dto
     * @param unknown_type $request
     * @return unknown_type
     */
    public function postindividualscorebydto($request) {
        $instructor = $this->authenticaterequest($request);
        $course     = $this->getcoursefromrequest($request);
        $dto        = $request->sessionGradeDto;
        if ($error = $this->service->saveGradebookItem($course, $dto, TURNINGTECH_SAVE_NO_OVERRIDE)) {
            return array(
                'return' => $error
            );
        }
    }
    /**
     * Override individual score
     * @param unknown_type $request
     * @return unknown_type
     */
    public function overrideindividualscore($request) {
        $instructor = $this->authenticaterequest($request);
        $course     = $this->getcoursefromrequest($request);
        try {
            $dto = $this->creategradebookdto($request);
        } catch (Exception $e) {
            $error               = new stdClass();
            $error->deviceId     = isset($request->deviceId) ? $request->deviceId : null;
            $error->itemTitle    = isset($request->itemTitle) ? $request->itemTitle : null;
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
     * Override individual score by dto
     * @param unknown_type $request
     * @return unknown_type
     */
    public function overrideindividualscorebydto($request) {
        $instructor = $this->authenticaterequest($request);
        $course     = $this->getcoursefromrequest($request);
        $dto        = $request->sessionGradeDto;
        if ($error = $this->service->saveGradebookItem($course, $dto, TURNINGTECH_SAVE_ONLY_OVERRIDE)) {
            return array(
                'return' => $error
            );
        }
    }
    /**
     * Add to individual Scores
     * @param unknown_type $request
     * @return unknown_type
     */
    public function addtoindividualscore($request) {
        $instructor = $this->authenticaterequest($request);
        $course     = $this->getcoursefromrequest($request);
        try {
            $dto = $this->creategradebookdto($request);
        } catch (Exception $e) {
            $error               = new stdClass();
            $error->deviceId     = isset($request->deviceId) ? $request->deviceId : null;
            $error->itemTitle    = isset($request->itemTitle) ? $request->itemTitle : null;
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
     * Add to individual Scores by dto
     * @param unknown_type $request
     * @return unknown_type
     */
    public function addtoindividualscorebydto($request) {
        $instructor = $this->authenticaterequest($request);
        $course     = $this->getcoursefromrequest($request);
        $dto        = $request->sessionGradeDto;
        if ($error = $this->service->addToExistingScore($course, $dto)) {
            return array(
                'return' => $error
            );
        }
    }
    /**
     * Post score
     * @param unknown_type $request
     * @return unknown_type
     */
    public function postscores($request) {
        $instructor = $this->authenticaterequest($request);
        $course     = $this->getcoursefromrequest($request);
        // Check if the request is for more than 1 score.
        if (!is_array($request->sessionGradeDtos)) {
            // If not post one score.
            $dto = $request->sessionGradeDtos;
            if ($error = $this->service->saveGradebookItem($course, $dto, TURNINGTECH_SAVE_NO_OVERRIDE)) {
                return array(
                    'return' => $error
                );
            }
        } else { // Is so iterate through the array.
            $dtolist = $request->sessionGradeDtos;
            $errors = array();
            foreach ($dtolist as $dto) {
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
     * Post score extended
     * @param unknown_type $request
     * @return unknown_type
     */
    public function postscoresext($request) {
        $instructor = $this->authenticaterequest($request);
        $course     = $this->getcoursefromrequest($request);
        // Check if the request is for more than 1 score.
        if (!is_array($request->sessionGradeDtosExt)) {
            // If not post one score.
            $dto = $request->sessionGradeDtosExt;
            if ($error = $this->service->saveGradebookItemExt($course, $dto, TURNINGTECH_SAVE_NO_OVERRIDE)) {
                return array(
                    'return' => $error
                );
            }
        } else { // Is so iterate through the array.
            $dtolist = $request->sessionGradeDtosExt;
            $errors = array();
            foreach ($dtolist as $dto) {
                if ($error = $this->service->saveGradebookItemExt($course, $dto, TURNINGTECH_SAVE_NO_OVERRIDE)) {
                    $errors[] = $error;
                }
            }
            return array(
                'return' => $errors
            );
        }
    }
    /**
     * Post score override all
     * @param unknown_type $request
     * @return unknown_type
     */
    public function postscoresoverrideall($request) {
        $instructor = $this->authenticaterequest($request);
        $course     = $this->getcoursefromrequest($request);
        // Check if the request is for more than 1 score.
        if (!is_array($request->sessionGradeDtos)) {
            // If not post one score.
            $dto = $request->sessionGradeDtos;
            if ($error = $this->service->saveGradebookItem($course, $dto, TURNINGTECH_SAVE_ONLY_OVERRIDE)) {
                return array(
                    'return' => $error
                );
            }
        } else { // If so iterate through the array.
            $dtolist = $request->sessionGradeDtos;
            $errors  = array();
            foreach ($dtolist as $dto) {
                if ($error = $this->service->saveGradebookItem($course, $dto, TURNINGTECH_SAVE_ONLY_OVERRIDE)) {
                    $errors[] = $error;
                }
            }
            return $errors;
        }
    }
    /**
     * Post score override all extended
     * @param unknown_type $request
     * @return unknown_type
     */
    public function postscoresoverrideallext($request) {
        $instructor = $this->authenticaterequest($request);
        $course     = $this->getcoursefromrequest($request);
        // Check if the request is for more than 1 score.
        if (!is_array($request->sessionGradeDtosExt)) {
            // If not post one score.
            $dto = $request->sessionGradeDtosExt;
            if ($error = $this->service->saveGradebookItemExt($course, $dto, TURNINGTECH_SAVE_ONLY_OVERRIDE)) {
                return array(
                    'return' => $error
                );
            }
        } else { // If so iterate through the array.
            $dtolist = $request->sessionGradeDtosExt;
            $errors  = array();
            foreach ($dtolist as $dto) {
                if ($error = $this->service->saveGradebookItemExt($course, $dto, TURNINGTECH_SAVE_ONLY_OVERRIDE)) {
                    $errors[] = $error;
                }
            }
            return $errors;
        }
    }
    /**
     * Export Session Data
     * @param unknown_type $request
     * @return unknown_type
     */
    public function exportsessiondata($request) {
        global $usercourses, $instructor;
        $instructor  = $this->authenticaterequest($request);
        $usercourses = $this->service->getCoursesByInstructor($instructor);
        if (count($usercourses) == 0) {
            $this->throwfault('AuthenticationException', get_string('userisnotinstructor', 'turningtech'));
        }
        return $this->service->importSessionData($request->exportData);
    }
    /**
     * builds a gradebook DTO from the given object
     * @param unknown_type $request
     * @return DTO
     */
    private function creategradebookdto($request) {
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