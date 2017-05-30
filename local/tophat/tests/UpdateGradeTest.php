<?php

require_once(dirname(dirname(__FILE__)) . '/vendor/autoload.php');
require_once(__DIR__ . '/BaseTestClass.php');

class UpdateGradeTest extends BaseTest
{
    public function testUpdateUserGrade() {
        $http = new \dcai\curl;
        $response = $http->post($this->makeEndpoint('local_tophat_update_grade'), [
            'courseid' => $this->fixture->courseid,
            'userid' => $this->fixture->userid,
            'iteminstance' => $this->fixture->gradeiteminstance,
            'grade' => $this->fixture->graderaw,
        ]);
        $json = $response->json();
        $this->assertEquals($json->meta->result, 'success');
    }
}
