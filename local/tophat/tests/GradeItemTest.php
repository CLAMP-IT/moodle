<?php

require_once(dirname(dirname(__FILE__)) . '/vendor/autoload.php');
require_once(__DIR__ . '/BaseTestClass.php');

class GradeItemTest extends BaseTest
{
    public function testGradeItem() {
        $http = new \dcai\curl;
        $response = $http->post($this->makeEndpoint('local_tophat_create_grade_item'), [
            'courseid' => $this->fixture->courseid,
            'gradecategoryid' => 0,
            'itemname' => $this->fixture->gradeitemname,
            'grademax' => $this->fixture->grademax,
            'grademin' => $this->fixture->grademin,
        ]);
        $json = $response->json();
        $response = $http->post($this->makeEndpoint('local_tophat_get_grade_item'), [
            'courseid' => $this->fixture->courseid,
            'gradeitemid' => $json->data->id,
        ]);
        $gradedata = $response->json();
        $this->assertEquals(trim($json->data->itemname), trim($gradedata->data->itemname));

        $response = $http->post($this->makeEndpoint('local_tophat_delete_grade_item'), [
            'courseid' => $this->fixture->courseid,
            'gradeitemid' => $json->data->id,
        ]);
        $gradedata = $response->json();
    }

    public function testGradeItemWithParentCategory() {
        $http = new \dcai\curl;
        $params = [
            'courseid' => $this->fixture->courseid,
            'parent' => 0,
            'fullname' => $this->fixture->gradecategoryname,
            'aggregateonlygraded' => 1
        ];
        $response = $http->post($this->makeEndpoint('local_tophat_create_grade_category'), $params);
        $json = $response->json();
        $this->assertInternalType('int', $json->data->id);
        $categoryid = $json->data->id;

        $params = [
            'courseid' => $this->fixture->courseid,
            'gradecategoryid' => $categoryid,
            'itemname' => 'TH GradeItem With Parent Category',
            'grademax' => $this->fixture->grademax,
            'grademin' => $this->fixture->grademin,
        ];
        $response = $http->post($this->makeEndpoint('local_tophat_create_grade_item'), $params);

        $json = $response->json();
        $this->assertEquals($categoryid, $json->data->categoryid);

        $response = $http->post($this->makeEndpoint('local_tophat_delete_grade_item'), [
            'courseid' => $this->fixture->courseid,
            'gradeitemid' => $json->data->id,
        ]);
        $gradedata = $response->json();

        $params = [
            'courseid' => $this->fixture->courseid,
            'gradecategoryid' => $categoryid,
        ];

        $response = $http->post($this->makeEndpoint('local_tophat_delete_grade_category'), $params);
        $json = $response->json();
        $this->assertEquals($json->meta->result, 'success');
    }
}
