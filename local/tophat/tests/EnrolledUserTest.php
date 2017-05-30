<?php

require_once(dirname(dirname(__FILE__)) . '/vendor/autoload.php');
require_once(__DIR__ . '/BaseTestClass.php');

class EnrolledUserTest extends BaseTest
{
    const PAGESIZE = 20;
    public function testEnrolledUser() {
        $http = new \dcai\curl;
        $response = $http->post($this->makeEndpoint('local_tophat_get_enrolled_users'), [
            'courseid' => $this->fixture->courseid,
            'page' => 0,
            'per_page' => $this->fixture->largepagesize,
        ]);
        $json = $response->json();
        $this->assertCount($this->fixture->numberofstudents, $json->data);
    }

    public function testEnrolledUserNoResults() {
        $http = new \dcai\curl;
        $response = $http->post($this->makeEndpoint('local_tophat_get_enrolled_users'), [
            'courseid' => $this->fixture->courseid,
            'page' => 1000,
            'per_page' => $this->fixture->pagesize,
        ]);
        $json = $response->json();
        $this->assertCount(0, $json->data);
    }
}
