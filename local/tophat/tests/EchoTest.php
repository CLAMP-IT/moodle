<?php

require_once(dirname(dirname(__FILE__)) . '/vendor/autoload.php');
require_once(__DIR__ . '/BaseTestClass.php');

class EchoTest extends BaseTest
{
    public function testEchoMessage() {
        $http = new \dcai\curl;
        $response = $http->post(self::makeEndpoint('local_tophat_echo_message'), array(
            'message' => 'cool?'
        ));
        $json = $response->json();
        $this->assertEquals(trim($json->message), 'ECHO: cool?');
    }
}
