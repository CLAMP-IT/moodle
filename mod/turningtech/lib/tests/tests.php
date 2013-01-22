<?php

error_reporting(-1);

$default_request                    = new stdClass();
$default_request->encryptedUserId   = 'teacher1';
$default_request->encryptedPassword = 'test';

$encrypt = TRUE;

if ($encrypt) {
    // ~~ $default_request->encryptedUserId = 'WPOtTGinAYwYC1/pIIxZVQ==';
    // ~~ $default_request->encryptedPassword = 'WPOtTGinAYwYC1/pIIxZVQ==';
    $default_request->encryptedUserId   = 'WPOtTGinAYwYC1/pIIxZVQ==';
    $default_request->encryptedPassword = 'cACpuYktRMBVgumwr6zdMw==';
}
/*
if(get_class($objClient) == "SoapClient")
{
echo "<pre>";
var_dump($objClient->__getFunctions());
echo "</pre>";
}
*/
switch ($service) {
    case "course":

        /******************
         * TEST COURSE OPERATIONS
         **/

?>
		<h1>Course Service</h1>

<?php
        /*
        // ------------------
        $name = 'getTaughtCourses';
        $request = clone $default_request;

        runTest($objClient, $name, $request);
        */
        /*
        // ------------------
        $name = 'getTaughtCoursesExt';
        $request = clone $default_request;

        runTest($objClient, $name, $request);
        */
        /*
        // ------------------
        $name = 'getClassRoster';
        $request = clone $default_request;
        $request->siteId = 2;

        runTest($objClient, $name, $request);
        */

        // ------------------
        $name            = 'getClassRosterExt';
        $request         = clone $default_request;
        $request->siteId = 3;

        runTest($objClient, $name, $request);

        break;

    case "func":

        /*****************
         * TEST FUNCTIONAL CAPABILITIES OPERATIONS
         */

?>

		<h1>Functional Capability Service</h1>

<?php
        // ----------------------
        $name    = 'getFunctionalCapabilities';
        $request = clone $default_request;

        runTest($objClient, $name, $request);

        break;

    case 'grade':

        /*********************************
         * TEST GRADES OPERATIONS
         */

?>

		<h1>Grade Service</h1>

<?php
        /*
        //-------------------------
        $name = 'createGradebookItem';
        $request = clone $default_request;
        $request->siteId = 2;
        $request->itemTitle = 'Now Posting';
        $request->pointsPossible = 25;

        runTest($objClient, $name, $request);

        //------------------------------
        $name = 'listGradebookItems';
        $request = clone $default_request;
        $request->siteId = 2;

        //runTest($objClient, $name, $request);

        //---------------------------------
        $name = 'postIndividualScore';
        $request = clone $default_request;
        $request->siteId = 2;
        $request->itemTitle = 'Now Posting';
        $request->deviceId = '12345AA';
        $request->pointsEarned = 20.0;
        $request->pointsPossible = 25.0;

        runTest($objClient, $name, $request);

        //-------------------------------
        $name = 'postIndividualScoreByDto';
        $request = clone $default_request;
        $request->siteId = 2;
        $request->sessionGradeDto = new stdClass();
        $request->sessionGradeDto->deviceId = '12345';
        $request->sessionGradeDto->itemTitle = 'New Type';
        $request->sessionGradeDto->pointsEarned = 5;
        $request->sessionGradeDto->pointsPossible = 10.0;

        //runTest($objClient, $name, $request);

        //----------------------------------
        $name = 'overrideIndividualScore';
        $request = clone $default_request;
        $request->siteId = 2;
        $request->itemTitle = 'New Type';
        $request->deviceId = '12345';
        $request->pointsEarned = 1.0;
        $request->pointsPossible = 10.0;

        //runTest($objClient, $name, $request);

        //-----------------------------------
        $name = 'overrideIndividualScoreByDto';
        $request = clone $default_request;
        $request->siteId = 2;
        $request->sessionGradeDto = new stdClass();
        $request->sessionGradeDto->deviceId = '12345';
        $request->sessionGradeDto->itemTitle = 'New Type';
        $request->sessionGradeDto->pointsEarned = 1.0;
        $request->sessionGradeDto->pointsPossible = 10.0;

        //runTest($objClient, $name, $request);

        //-------------------------------------
        $name = 'addToIndividualScore';

        $request = clone $default_request;
        $request->siteId = 2;
        $request->itemTitle = 'New Type';
        $request->deviceId = '12345';
        $request->pointsEarned = 1.0;
        $request->pointsPossible = 10.0;

        //runTest($objClient, $name, $request);

        //---------------------------------------
        $name = 'addToIndividualScoreByDto';
        $request = clone $default_request;
        $request->siteId = 2;
        $request->sessionGradeDto = new stdClass();
        $request->sessionGradeDto->deviceId = '12345';
        $request->sessionGradeDto->itemTitle = 'New Type';
        $request->sessionGradeDto->pointsEarned = 1.0;
        $request->sessionGradeDto->pointsPossible = 10.0;

        //runTest($objClient, $name, $request);

        //----------------------------------------
        $name = 'postScores';
        $request = clone $default_request;
        $request->siteId = 2;

        $scores = array();

        $dto = new stdClass();
        $dto->deviceId = '12345';
        $dto->itemTitle = 'New Type';
        $dto->pointsEarned = 5.0;
        $dto->pointsPossible = 10.0;
        $scores[] = $dto;

        $dto = new stdClass();
        $dto->deviceId = '55555';
        $dto->itemTitle = 'New Type';
        $dto->pointsEarned = 6.0;
        $dto->pointsPossible = 10.0;
        $scores[] = $dto;

        $dto = new stdClass();
        $dto->deviceId = '11111';
        $dto->itemTitle = 'New Type';
        $dto->pointsEarned = 7.0;
        $dto->pointsPossible = 10.0;
        $scores[] = $dto;

        $request->sessionGradeDtos = $scores;

        //runTest($objClient, $name, $request);

        //-----------------------------
        $name = 'postScoresOverrideAll';
        $request = clone $default_request;
        $request->siteId = 2;

        $scores = array();

        $dto = new stdClass();
        $dto->deviceId = '12346';
        $dto->itemTitle = 'New Type';
        $dto->pointsEarned = 5.0;
        $dto->pointsPossible = 10.0;
        $scores[] = $dto;

        $dto = new stdClass();
        $dto->deviceId = '55555';
        $dto->itemTitle = 'New Type';
        $dto->pointsEarned = 1.0;
        $dto->pointsPossible = 10.0;
        $scores[] = $dto;

        $dto = new stdClass();
        $dto->deviceId = '11111';
        $dto->itemTitle = 'New Type';
        $dto->pointsEarned = 2.0;
        $dto->pointsPossible = 10.0;
        $scores[] = $dto;

        $request->sessionGradeDtos = $scores;

        //runTest($objClient, $name, $request);
        */
        $name                = 'exportSessionData';
        $request             = clone $default_request;
        $request->exportData = <<<XML
<?xml version='1.0'?><ExportData><courseId>3</courseId><exportobject name="WX123YZ" maxscore="25"></exportobject><participants><participant userid="teacher1" score="5"></participant><participant userid="admin" score="8"></participant><participant userid="guest" score="9"></participant><participant userid="ojas" score="20"></participant></participants></ExportData>
XML;

        runTest($objClient, $name, $request);

        break;
}

?>