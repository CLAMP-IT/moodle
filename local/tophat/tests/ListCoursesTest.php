<?php

require_once(dirname(dirname(__FILE__)) . '/vendor/autoload.php');
require_once(__DIR__ . '/BaseTestClass.php');

class ListCoursesTest extends BaseTest
{
    public function testListCourses() {
        $http = new \dcai\curl;
        $response = $http->post(self::makeEndpoint('local_tophat_get_courses'), array(
            'roles' => '',
            'page' => 0,
            'per_page' => 2
        ));
        $json = $response->json();
        $this->assertCount($this->fixture->numberofcourses, $json->data);
    }
}
