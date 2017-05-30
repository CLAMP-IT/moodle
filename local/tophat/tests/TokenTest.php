<?php

require_once(dirname(dirname(__FILE__)) . '/vendor/autoload.php');
require_once(__DIR__ . '/BaseTestClass.php');

class TokenTest extends BaseTest
{
    public function testInvalidToken() {
        $http = new \dcai\curl;
        $response = $http->post(self::makeEndpoint('local_tophat_validate_token', 'invalidtoken'), []);
        $json = $response->json();
        $this->assertEquals(trim($json->errorcode), 'invalidtoken');
    }
    public function testValdiateToken() {
        $http = new \dcai\curl;
        $response = $http->post(self::makeEndpoint('local_tophat_validate_token'), []);
        $json = $response->json();
        $this->assertEquals(trim($json->errorcode), 'invalidtoken');
    }
}
