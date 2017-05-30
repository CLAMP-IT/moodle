<?php

require_once(dirname(dirname(__FILE__)) . '/vendor/autoload.php');

class BaseTest extends PHPUnit_Framework_TestCase
{
    const FIXTURES = [
        'local' => [
            'userid' => 2,
            'courseid' => 29,
            'endpoint' => 'http://localhost/moodle/webservice/rest/server.php',
            'token' => 'd54f871e53706fb4fbdcc7e5a6e6f09f',
            'gradecategoryname' => 'TOP HAT 2017.02.03',
            'customroles' => 'tttt,eeeee',
            'gradeitemname' => 'donghsheng grade itemjhhhhhhhhhhhhhh',
            'gradetypevalue' => 1,
            'gradetypescale' => 2,
            'gradeiteminstance' => 86,
            'graderaw' => 41,
            'grademax' => 909.03,
            'grademin' => 0.02,
            'gradescaleid' => 0,
            'pagesize' => 2,
            'numberofcourses' => 2,
            'largepagesize' => 20,
            'numberofstudents' => 11,
        ],
        'tophat' => [
            'userid' => 4,
            'courseid' => 6,
            'endpoint' => 'https://moodle.tophat.com/webservice/rest/server.php',
            'token' => '505c2d94ee939072374429698e38ac54',
            'gradecategoryname' => 'TOP HAT 2017.02.03',
            'customroles' => 'tttt,eeeee',
            'gradeitemname' => 'donghsheng grade itemjhhhhhhhhhhhhhh',
            'gradetypevalue' => 1,
            'gradetypescale' => 2,
            'gradeiteminstance' => 86,
            'graderaw' => 41,
            'grademax' => 909.03,
            'grademin' => 0.02,
            'gradescaleid' => 0,
            'pagesize' => 2,
            'numberofcourses' => 1,
            'largepagesize' => 20,
            'numberofstudents' => 3,
        ]
    ];

    protected function makeEndpoint($function, $token = '') {
        $wstoken = $this->fixture->token ;
        if (!empty($token)) {
            $wstoken = $token;
        }
        return $this->fixture->endpoint . '?' . 'wstoken=' . $wstoken . '&wsfunction=' . $function . '&moodlewsrestformat=json';
    }

    protected function setUp() {
        $this->fixture = (object)self::FIXTURES[getenv('ENV')];
    }
}
