<?php

require_once(dirname(dirname(__FILE__)) . '/vendor/autoload.php');
require_once(__DIR__ . '/BaseTestClass.php');

class GradeCategoryTest extends BaseTest
{
    public function testGradeCategory() {
        $http = new \dcai\curl;
        $params = [
            'courseid' => $this->fixture->courseid,
            'parent' => 0,
            'fullname' => $this->fixture->gradecategoryname,
            'aggregateonlygraded' => 1
        ];
        $response = $http->post($this->makeEndpoint('local_tophat_create_grade_category'), $params);
        $json = $response->json();
        $this->assertInternalType("int", $json->data->id);
        $categoryid = $json->data->id;

        $response = $http->post($this->makeEndpoint('local_tophat_delete_grade_category'), [
            'courseid' => $this->fixture->courseid,
            'gradecategoryid' => $categoryid,
        ]);
        $json = $response->json();
        $this->assertEquals($json->meta->result, 'success');
    }

}
