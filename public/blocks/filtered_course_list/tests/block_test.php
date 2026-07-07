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
 * This file defines PHPUnit tests for the Filtered course list block.
 *
 * @package    block_filtered_course_list
 * @copyright  2016 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_filtered_course_list;

use advanced_testcase;
use moodle_page;
use stdClass;
use block_filtered_course_list;
use DOMDocument;
use DOMElement;
use completion_completion;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/blocks/moodleblock.class.php');
require_once($CFG->dirroot . '/lib/pagelib.php');
require_once(dirname(__FILE__) . '/../block_filtered_course_list.php');
require_once(dirname(__FILE__) . '/../renderer.php');

/**
 * PHPUnit tests
 *
 * @package    block_filtered_course_list
 * @copyright  2016 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_test extends advanced_testcase {
    /** @var int The admin user's id number */
    private $adminid;
    /** @var object A test user */
    private $user1;
    /** @var object A test user */
    private $user2;
    /** @var object A test user */
    private $user3;
    /** @var array A list of course objects to be used in several tests */
    private $courses = [];
    /** @var array A list of categories into which the test courses can be grouped */
    private $categories = [];

    /**
     * General setup for PHPUnit testing
     */
    protected function setUp(): void {
        parent::setUp();

        global $CFG;
        unset($CFG->maxcategorydepth);
        $this->resetAfterTest(true);
        $this->setupusers();
        $this->setupfilter();
    }

    protected function tearDown(): void {
        parent::tearDown();

        $rmpath = __DIR__ . '/behat/data/fcl_filter.php';
        if (file_exists($rmpath)) {
            unlink($rmpath);
        }
    }

    /**
     * Text external filter detection.
     */
    public function test_external_filter_detection() {
        $files = \block_filtered_course_list\lib::get_filter_files();
        $this->assertCount(1, $files);
        $this->assertEquals('fcl_filter.php', $files[0]->getFilename());
        require_once($files[0]->getPathname());
        $classes = \block_filtered_course_list\lib::get_filter_classes();
        $this->assertCount(1, $classes);
        $this->assertEquals('test_fcl_filter', reset($classes));
    }

    /**
     * Test that the default settings are applying as expected
     */
    public function test_default_settings() {

        // Confirm expected defaults.
        // We omit the multiline default for the 'filters' setting.

        $fclconfig = get_config('block_filtered_course_list');

        $configdefaults = [
            'hideallcourseslink'  => 0,
            'hidefromguests'      => 0,
            'hideothercourses'    => 0,
            'maxallcourse'        => 10,
            'coursenametpl'       => 'FULLNAME',
            'catrubrictpl'        => 'NAME',
            'catseparator'        => ' / ',
            'managerview'         => 'all',
            'persistentexpansion' => 1,
            'primarysort'         => 'fullname',
            'primaryvector'       => 'ASC',
            'secondarysort'       => 'none',
            'secondaryvector'     => 'ASC',
        ];

        foreach ($configdefaults as $name => $value) {
            $expected = $configdefaults[$name];
            $actual = $fclconfig->$name;
            $this->assertEquals($expected, $actual, "$actual should be set to $expected");
        }
    }

    /**
     * Test a site that has no courses
     */
    public function test_site_with_no_courses() {

        // On a site with no courses, no users should see a block.
        $this->noblock(
            [
                'none'  => true,
                'guest' => true,
                'user1' => true,
                'admin' => true,
            ]
        );
    }

    /**
     * Test a site that has only one category and no enrollments
     */
    public function test_single_category_site_with_no_enrollments() {
        // Create 8 courses in the default category: Category 1.
        $this->create_misc_courses(1, 8);

        // Everyone should see all courses.
        $this->courselistincludes([
            'none'  => [ 'Course 1', 'Course 8' ],
            'guest' => [ 'Course 1', 'Course 8' ],
            'admin' => [ 'Course 1', 'Course 8' ],
            'user1' => [ 'Course 1', 'Course 8' ],
        ]);
    }

    /**
     * Test a small site with one category and some enrollments
     */
    public function test_small_single_category_site_with_enrollments() {

        // Create 8 courses in the default category: Category 1.
        $this->create_misc_courses(1, 8);

        // Enroll user1 as a teacher in course 1 and a student in courses 3 and 5.
        $this->getDataGenerator()->enrol_user($this->user1->id, $this->courses['c_1']->id, 3);
        $this->getDataGenerator()->enrol_user($this->user1->id, $this->courses['c_3']->id);
        $this->getDataGenerator()->enrol_user($this->user1->id, $this->courses['c_5']->id);

        // Anonymous, Guest and Admin should see all courses.
        $this->courselistincludes([
            'none'  => [ 'Course 1', 'Course 8' ],
            'guest' => [ 'Course 1', 'Course 8' ],
            'admin' => [ 'Course 1', 'Course 8' ],
        ]);

        // User1 should see courses 1, 3 and 5.
        $this->courselistincludes([
            'user1' => [ 'Course 1', 'Course 3', 'Course 5' ],
        ]);

        // User1 should not see course 2 or course 6.
        $this->courselistexcludes([
            'user1' => [ 'Course 2', 'Course 6' ],
        ]);

        // We should also check the generic filter while we have this configuration.
        set_config('filters', 'generic|e', 'block_filtered_course_list');
        // User1 should now see all courses.
        $this->courselistincludes([
            'user1' => [ 'Course 1', 'Course 2', 'Course 3', 'Course 5', 'Course 6' ],
        ]);
    }

    /**
     * Test a larger site with enrollments in one category
     */
    public function test_larger_single_category_site_with_enrollments() {

        // Create 12 courses in the default category: Category 1.
        $this->create_misc_courses(1, 12);

        // Enroll user1 as a teacher in course 1 and a student in courses 3 and 5.
        $this->getDataGenerator()->enrol_user($this->user1->id, $this->courses['c_1']->id, 3);
        $this->getDataGenerator()->enrol_user($this->user1->id, $this->courses['c_3']->id);
        $this->getDataGenerator()->enrol_user($this->user1->id, $this->courses['c_5']->id);

        // The block should not display individual courses to anonymous, guest, or admin.
        $this->courselistexcludes([
            'none'  => [ 'Course 1' ],
            'guest' => [ 'Course 1' ],
            'admin' => [ 'Course 1' ],
        ]);

        // The block should offer a category link to anonymous, guest, and admin.
        $this->courselistincludes([
            'none'  => [ 'Category 1' ],
            'guest' => [ 'Category 1' ],
            'admin' => [ 'Category 1' ],
        ]);

        // User1 should still see courses 1, 3 and 5.
        $this->courselistincludes([
            'user1' => [ 'Course 1', 'Course 3', 'Course 5' ],
        ]);

        // User1 should not see course 2 or course 6.
        $this->courselistexcludes([
            'user1' => [ 'Course 2', 'Course 6' ],
        ]);
    }

    /**
     * Test a rich site with several courses in various categories
     */
    public function test_rich_site_with_defaults() {

        $this->create_rich_site();

        // With no special settings, the behavior should be as for a larger single-category site.
        set_config('filters', '', 'block_filtered_course_list');

        // The block should not display individual courses to anonymous, guest, or admin.
        // The block should not display links to categories below the top level.
        $this->courselistexcludes([
            'none'  => [ 'Course 1', 'Child', 'Grandchild' ],
            'guest' => [ 'Course 1', 'Child', 'Grandchild' ],
            'admin' => [ 'Course 1', 'Child', 'Grandchild' ],
        ]);

        // The block should offer top-level category links to anonymous, guest, admin or an unenrolled user.
        $this->courselistincludes([
            'none'  => [ 'Category 1', 'Sibling' ],
            'guest' => [ 'Category 1', 'Sibling' ],
            'admin' => [ 'Category 1', 'Sibling' ],
            'user3' => [ 'Category 1', 'Sibling' ],
        ]);

        // Regular users should see links to visible courses in visible categories under 'Other courses'.
        // This includes visible courses in visible categories in hidden categories.
        // Teachers should see links to all their courses, visible or hidden, and under hidden categories.
        // Students should see links to visible courses in hidden categories under 'Other courses'.
        $this->courseunderrubric([
            'user1' => [
                'c_1'   => 'Other courses',
                'cc1_2' => 'Other courses',
                'gc1_1' => 'Other courses',
                'sc_2'  => 'Other courses',
                'øthér' => 'Other courses',
                'hcvc_1' => 'Other courses',
            ],
            'user2' => [
                'c_1'   => 'Other courses',
                'cc1_3' => 'Other courses',
                'gc1_1' => 'Other courses',
                'hc_1'  => 'Other courses',
                'hcc_2' => 'Other courses',
                'hcc_3' => 'Other courses',
                'sc_2'  => 'Other courses',
            ],
        ]);

        // Students should not see links to hidden courses or visible courses under hidden categories.
        $this->courselistexcludes([
            'user1' => [ 'cc1_3', 'gc1_3' ],
        ]);
    }

    /**
     * Test a complicated site filtered by category
     */
    public function test_rich_site_filtered_by_category() {

        $this->create_rich_site();

        // Change setting to filter by categories.
        $filterconfig = <<<EOF
category | c | 0
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        // The block should not display individual courses to anonymous, guest, or admin.
        // The block should not display links to categories below the top level.
        $this->courselistexcludes([
            'none'  => [ 'Course 1', 'Child', 'Grandchild' ],
            'guest' => [ 'Course 1', 'Child', 'Grandchild' ],
            'admin' => [ 'Course 1', 'Child', 'Grandchild' ],
        ]);

        // The block should offer top-level category links to anonymous, guest, and admin.
        // A user not enrolled in any visible courses should see the same.
        $this->courselistincludes([
            'none'  => [ 'Category 1', 'Sibling' ],
            'guest' => [ 'Category 1', 'Sibling' ],
            'admin' => [ 'Category 1', 'Sibling' ],
            'user3' => [ 'Category 1', 'Sibling' ],
        ]);

        // Regular users should see links to visible courses under corresponding visible categories.
        // Teachers should see links to all their courses, visible or hidden, and under hidden categories.
        // Courses under a hidden category will appear under "Other courses" to those who can't see the category.
        // With admins_sees_own an admin should see hidden courses under hidden categories.

        // Change the managerview setting to 'own'.
        set_config('managerview', 'own', 'block_filtered_course_list');

        // Enroll admin in 'hc_1'.
        $this->getDataGenerator()->enrol_user($this->adminid, $this->courses['hc_1']->id, 3);

        $this->courseunderrubric([
            'user1' => [
                'c_1'   => 'Category 1',
                'cc1_2' => 'Child category 1',
                'gc1_1' => 'Grandchild category 1',
                'sc_2'  => 'Sibling category',
                'hc_1'  => 'Other courses',
                'hcc_2' => 'Other courses',
            ],
            'user2' => [
                'c_1'   => 'Category 1',
                'cc1_3' => 'Child category 1',
                'gc1_1' => 'Grandchild category 1',
                'hc_1'  => 'Other courses',
                'hcc_3' => 'Other courses',
                'sc_2'  => 'Sibling category',
                'øthér' => 'Sibling category',
            ],
            'admin' => [
                'hc_1' => 'Hidden category',
            ],
        ]);

        // Courses should appear only under immediate parents.
        $this->courseunderrubric([
            'user1' => [ 'gc1_1' => 'Category 1' ],
            'user2' => [ 'gc1_1' => 'Child category 1' ],
        ], 'not');

        // Students should not see links to hidden courses.
        // Students shoudl see links to visible courses even if they are in hidden categories.
        $this->courselistexcludes([
            'user1' => [ 'cc1_3', 'gc1_3' ],
        ]);

        // Now try switching the root category setting.
        $cc2id = $this->categories['cc2']->id;
        $filterconfig = <<<EOF
category | collapsed | $cc2id
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        // There should be no rubric for Category 1.
        $this->courselistexcludes([
            'user1' => [ 'Category 1' ],
        ]);

        // Courses directly under Category 1 should continue to appear in 'Other courses'.
        $this->courseunderrubric([
            'user1' => [
                'c_1'   => 'Other courses',
                'cc1_2' => 'Other courses',
                'cc2_1' => 'Child category 2',
                'gc1_1' => 'Grandchild category 1',
                'sc_2'  => 'Other courses',
            ],
        ]);

        // Test the ability to set recursion depth on top-level category filter.
        // Confirm also that comments in the category and depth fields will be ignored.
        $filterconfig = <<<EOF
category | collapsed | 0 (Top level) | 1 deep
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        $this->courseunderrubric([
            'user1' => [
                'c_1'   => 'Category 1',
                'cc1_2' => 'Other courses',
                'gc1_1' => 'Other courses',
                'sc_2'  => 'Sibling category',
            ],
        ]);

        $this->sectionexpanded([
            'Category 1'    => 'collapsed',
            'Sibling category' => 'collapsed',
        ]);

        // Test the ability to set recursion depth on specific categories.
        $cc1id = $this->categories['cc1']->id;
        $scid = $this->categories['sc']->id;
        $filterconfig = <<<EOF
category | expanded | $cc1id | 1
category | collapsed | $scid  | 1
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        $this->courseunderrubric([
            'user1' => [
                'c_1'   => 'Other courses',
                'cc1_2' => 'Child category 1',
                'gc1_1' => 'Other courses',
                'sc_2'  => 'Sibling category',
            ],
        ]);

        $this->sectionexpanded([
            'Child category 1' => 'expanded',
            'Sibling category' => 'collapsed',
        ]);

        // Test the behavior of hidden categories and their potentially visible descendants.
        // Set the filter target to the be the visible child of the hidden category.
        // It should behave like any other visible category.
        $hcvcid = $this->categories['hcvc']->id;
        $filterconfig = <<<EOF
category | expanded | $hcvcid | 0
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        $this->courseunderrubric([
            'user1' => [
                'hcvc_1' => 'Hidden category visible child',
            ],
            'user2' => [
                'hcvc_1' => 'Hidden category visible child',
                'hcvc_3' => 'Hidden category visible child',
            ],
        ]);

        $this->courselistexcludes([
            'user1' => [
                'hcvc_3',
            ],
        ]);

        // Test the behavior of hidden categories and their potentially visible descendants.
        // Set the filter target to the be the hidden category itself.
        // We should get a rubric for the visible child.
        // We should get no rubric for the hidden category.
        // Accessible courses in the hidden category should appear under "Other courses".
        $hcid = $this->categories['hc']->id;
        $filterconfig = <<<EOF
category | expanded | $hcid | 0
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        $this->courseunderrubric([
            'user1' => [
                'hcvc_1' => 'Hidden category visible child',
                'hc_1'   => 'Other courses',
            ],
            'user2' => [
                'hcvc_1' => 'Hidden category visible child',
                'hcvc_3' => 'Hidden category visible child',
                'hc_1'   => 'Other courses',
                'hc_3'   => 'Other courses',
            ],
        ]);

        $this->courselistexcludes([
            'user1' => [
                'hcvc_3',
            ],
        ]);

        // Test that we can display a visible child category of a hidden parent category.
        // We can do this by pointing directly to the top-level category.
        $filterconfig = <<<EOF
category | expanded | 0 | 0
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        $this->courseunderrubric([
            'user1' => [
                'hcvc_1' => 'Hidden category visible child',
            ],
        ]);
    }

    /**
     * Test shortname filtering
     */
    public function test_shortnames() {

        $this->shared_test('shortname');
    }

    /**
     * Test idnumber filtering
     */
    public function test_idnumbers() {

        $this->shared_test('idnumber');
    }

    /**
     * Generic test for shortname or idnumber filters
     *
     * @param string $filter 'shortname' or 'idnumber'
     */
    public function shared_test($filter) {

        $this->create_rich_site();

        // We'll need to use uppercase for the idnumber_filter.
        $transformation = ($filter == 'idnumber') ? 'mb_strtoupper' : 'mb_strtolower';

        // Set up some idnumber filters.
        $filterconfig = <<<EOF
$filter | expanded | Current courses       | _1
$filter | expanded | Future courses        | _2
$filter | expanded | Non-ascii             | {$transformation('ø')}
$filter | expanded | Child courses         | {$transformation('cc')}
$filter | expanded | Unnumbered categories | {$transformation('c_')}
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        // The block should not display individual courses to anonymous, guest, or admin.
        // The block should not display links to categories below the top level.
        $this->courselistexcludes([
            'none'  => [ 'Course 1', 'Child', 'Grandchild' ],
            'guest' => [ 'Course 1', 'Child', 'Grandchild' ],
            'admin' => [ 'Course 1', 'Child', 'Grandchild' ],
        ]);

        // The block should offer top-level category links to anonymous, guest, and admin.
        $this->courselistincludes([
            'none'  => [ 'Category 1', 'Sibling' ],
            'guest' => [ 'Category 1', 'Sibling' ],
            'admin' => [ 'Category 1', 'Sibling' ],
        ]);

        // The block should list 'Current', 'Future', and 'Other courses'.
        $this->courseunderrubric([
            'user1' => [
                'c_1'   => 'Current courses',
                'cc1_1' => 'Current courses',
                'gc1_1' => 'Current courses',
                'sc_1'  => 'Current courses',
                'øthér' => 'Non-ascii',
                'cc1_2' => 'Child courses',
                'cc2_1' => 'Child courses',
                'sc_2'  => 'Unnumbered categories',
            ],
            'user2' => [
                'c_2'   => 'Future courses',
                'cc1_2' => 'Future courses',
                'gc1_2' => 'Future courses',
                'sc_2'  => 'Future courses',
                'hc_2'  => 'Future courses',
                'hc_3'  => 'Unnumbered categories',
                'c_3'   => 'Unnumbered categories',
                'cc2_2' => 'Future courses',
                'gc1_3' => 'Other courses',
            ],
        ]);
    }

    /**
     * Test shortnames with regex
     */
    public function test_regex_shortnames() {

        $this->create_rich_site();

        // Use regex for shortname matches.
        $filterconfig = <<<EOF
regex | exp | All but default | ^[a-zø]{2}
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        // This new rubric should exclude courses with a shortname like 'c_1'.
        // It does not begin with two lowercase letters.
        $this->courseunderrubric([
            'user1' => [
                'c_1' => 'All but default',
            ],
        ], 'not');

        // Courses under any other category should be listed.
        $this->courseunderrubric([
            'user1' => [
                'cc1_1' => 'All but default',
                'cc2_1' => 'All but default',
                'gc1_1' => 'All but default',
                'sc_1'  => 'All but default',
                'øthér' => 'All but default',
            ],
        ]);
    }

    /**
     * Test generic filters
     */
    public function test_generic_filters() {

        $this->create_rich_site();

        // Set up the generic filter.
        set_config('filters', 'generic | exp | Courses | Course categories', 'block_filtered_course_list');

        // Change the managerview setting to 'own'.
        set_config('managerview', 'own', 'block_filtered_course_list');

        // Hide the catch-all 'Other courses' rubric.
        set_config('hideothercourses', 1, 'block_filtered_course_list');

        // The block should offer top-level category links to all users including logged-in user enrolled in no courses.
        $this->courselistincludes([
            'admin' => [ 'Course categories' ],
            'user1' => [ 'Course categories' ],
            'user2' => [ 'Course categories' ],
            'none'  => [ 'Course categories' ],
            'guest' => [ 'Course categories' ],
            'user3' => [ 'Course categories' ],
        ]);
    }

    /**
     * Test a site with mixed filters
     */
    public function test_mixed_filters() {

        $this->create_rich_site();

        // Set up mixed filters.
        $cc2id = $this->categories['cc2']->id;
        $scid = $this->categories['sc']->id;
        $filterconfig = <<<EOF
shortname | expanded | Ones   | _1
category  | expanded | $cc2id | 0
category  | expanded | $scid  | 0
shortname | expanded | Twos   | _2
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        // Users should see relevant courses under all rubrics.
        $this->courseunderrubric([
            'user1' => [
                'c_1'   => 'Ones',
                'cc2_1' => 'Child category 2',
                'cc2_2' => 'Child category 2',
                'sc_1'  => 'Sibling category',
                'sc_2'  => 'Twos',
            ],
        ]);
    }

    /**
     * Test the ability to use the admin settings to hide the link to 'All courses'
     */
    public function test_setting_hideallcourseslink() {

        $this->create_rich_site();

        // Set up simple matching.
        $filterconfig = <<<EOF
shortname | e | Courses | _
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        // Any user who sees the block should also see the "All courses" link.
        $this->allcourseslink([
            'none'  => 'Search courses',
            'guest' => 'Search courses',
            'user1' => 'All courses',
            'admin' => 'Search courses',
            'user3' => false,
        ]);

        // Hide the All-courses link from all but admins.
        set_config('hideallcourseslink', 1, 'block_filtered_course_list');

        // Only an admin should see the "All courses" link.
        $this->allcourseslink([
            'none'  => false,
            'guest' => false,
            'user1' => false,
            'admin' => 'Search courses',
        ]);
    }

    /**
     * Test whether an admin can hide the block from guests
     */
    public function test_setting_hidefromguests() {

        $this->create_rich_site();

        // Set up simple matching.
        $filterconfig = <<<EOF
shortname | expanded | Courses | _
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        // All users should see the block.
        $this->noblock([
            'none'  => false,
            'guest' => false,
            'user1' => false,
            'admin' => false,
            'user3' => false,
        ]);

        // Change the setting to hide the block from guests and anonymous visitors.
        set_config('hidefromguests', 1, 'block_filtered_course_list');

        // Now only admins and logged-in users should see the block.
        $this->noblock([
            'none'  => true,
            'guest' => true,
            'user1' => false,
            'admin' => false,
            'user3' => false,
        ]);
    }

    /**
     * Test whether an admin can choose whether or not to display a link to 'Other courses'
     * not covered by the filter
     */
    public function test_setting_hideothercourses() {

        $this->create_rich_site();

        $filterconfig = <<<EOF
shortname | e | Current courses | gc
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        // Enrollments that do not match appear under 'Other courses'.
        $this->courseunderrubric([
            'user1' => [
                'sc_1' => 'Other courses',
            ],
        ]);

        // Hide the catch-all 'Other courses' rubric.
        set_config('hideothercourses', 1, 'block_filtered_course_list');

        // No other courses are listed.
        $this->courselistexcludes([
            'user1' => [ 'Other courses' ],
        ]);
    }

    /**
     * Test that aria attributes are added for collapsible rubrics
     */
    public function test_aria_attributes() {

        $this->create_rich_site();

        // Set up simple matching.
        $filterconfig = <<<EOF
shortname | exp | Courses | _
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        // For users enrolled in courses the various rubrics are collapsible.
        $this->courselistincludes([
            'user1' => [
                'collapsible',
                'aria-multiselectable',
                'aria-controls',
                'aria-expanded',
                'aria-selected',
                'aria-labelledby',
                'aria-hidden',
            ],
        ]);
    }

    /**
     * Test whether an admin can designate particular rubrics to be expanded automatically
     */
    public function test_setting_expanded_section() {

        $this->create_rich_site();

        // Set up some rubrics.
        $filterconfig = <<<EOF
shortname | collapsed | Current courses       | _1
shortname | collapsed | Future courses        | _2
shortname | collapsed | Child courses         | cc
shortname | collapsed | Unnumbered categories | c_
category  | collapsed | 0                     | 0
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        // All sections should be collapsed.
        $this->sectionexpanded([
            'Current courses'       => 'collapsed',
            'Future courses'        => 'collapsed',
            'Child courses'         => 'collapsed',
            'Unnumbered categories' => 'collapsed',
            'Category 1'         => 'collapsed',
            'Child category'        => 'collapsed',
        ]);

        // Now set a couple sections to be expanded by default.
        $filterconfig = <<<EOF
shortname | expanded  | Current courses       | _1
shortname | collapsed | Future courses        | _2
shortname | collapsed | Child courses         | cc
shortname | expanded  | Unnumbered categories | c_
category  | expanded  | 0                     | 0
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        // The corresponding sections should be expanded.
        $this->sectionexpanded([
            'Current courses'       => 'expanded',
            'Future courses'        => 'collapsed',
            'Child courses'         => 'collapsed',
            'Unnumbered categories' => 'expanded',
            'Category 1'         => 'expanded',
            'Child category'        => 'expanded',
        ]);

        // Now test config lines that do not set expansion state or use invalid values.
        $filterconfig = <<<EOF
shortname | | Current courses | _1
shortname | invalid value | Future courses | _2
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        // All sections should be collapsed.
        $this->sectionexpanded([
            'Current courses'       => 'collapsed',
            'Future courses'        => 'collapsed',
        ]);
    }

    /**
     * Test that rubric titles pass through htmlentities()
     */
    public function test_rubric_title_htmlentities() {

        $this->create_rich_site();

        // Set up a shortname rubrics.
        $filterconfig = <<<EOF
shortname | col | Current <br />courses | _1
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        // We should see the course under the original text.
        // This test would fail if the line break were interpreted.
        $this->courseunderrubric([
            'user1' => [
                'c_1' => 'Current <br />courses',
            ],
        ]);
    }

    /**
     * Test whether an admin can choose whether to see all courses or only her own
     */
    public function test_setting_managerview() {

        $this->create_rich_site();
        set_config('filters', '', 'block_filtered_course_list');

        // The block should not display links to categories below the top level.
        $this->courselistexcludes([
            'admin' => [ 'Course 1', 'Child', 'Grandchild' ],
        ]);

        // The block should offer top-level category links to anonymous, guest, and admin.
        $this->courselistincludes([
            'admin' => [ 'Category 1', 'Sibling' ],
        ]);

        // Change the managerview setting to 'own'.
        set_config('managerview', 'own', 'block_filtered_course_list');

        // An admin enrolled in no courses should still see only the top level.
        $this->courselistexcludes([
            'admin' => [ 'Course 1', 'Child', 'Grandchild' ],
        ]);

        $this->courselistincludes([
            'admin' => [ 'Category 1', 'Sibling' ],
        ]);

        // Put the admin in a course.
        $this->getDataGenerator()->enrol_user($this->adminid, $this->courses['gc1_1']->id);

        // Admin should see the course listing as a regular user would.
        $this->courselistexcludes([
            'admin' => [ 'Category 1', 'Sibling' ],
        ]);

        $this->courseunderrubric([
            'admin' => [ 'gc1_1' => 'Other courses' ],
        ]);
    }

    /**
     * Test some display template settings
     */
    public function test_setting_tpls() {
        $this->create_rich_site();
        set_config('coursenametpl', 'FULLNAME (SHORTNAME) : IDNUMBER < <b>CATEGORY</b>', 'block_filtered_course_list');
        set_config('catrubrictpl', 'NAME - IDNUMBER - <b>PARENT</b> - ANCESTRY', 'block_filtered_course_list');
        set_config('catseparator', ' :: ', 'block_filtered_course_list');

        // Any tags should be stripped.
        $longrubric = 'Grandchild category 1 - gc1 - Child category 2 - Category 1 :: Child category 2 :: Grandchild category 1';
        $htmlentities = '&uuml;&amp;: HTML Entities (&uuml;&amp;shortname) : &uuml;&amp;IDNUMBER &lt; Sibling category';
        $this->courselistincludes([
            'user1' => [ 'Non-ascii matching (øthér) : ØTHÉR &lt; Sibling category',
                'Category 1 -  - Top - Category 1',
                $longrubric,
                $htmlentities,
             ],
        ]);
        $this->courselistexcludes([
            'user1' => [ '&amp;uuml;', '&amp;amp;' ],
        ]);
    }

    /**
     * Ensure that the block honors a possible 'disablemycourses' setting
     */
    public function test_setting_cfg_disablemycourses() {

        global $CFG;
        $this->create_rich_site();
        set_config('filters', '', 'block_filtered_course_list');

        $CFG->disablemycourses = 1;

        // Enrolled users, like guests, should see a generic list of categories.
        $this->courselistincludes([
            'user1' => [ 'Category 1', 'Sibling' ],
        ]);

        // Enrolled users, like guests, should not see subcategories or specific courses.
        $this->courselistexcludes([
            'user1' => [ 'Course 1', 'Child', 'Grandchild' ],
        ]);

        // On the other hand, managerview = own trumps disablemycourses.
        set_config('managerview', 'own', 'block_filtered_course_list');

        // Enroll admin in 'hc_1'.
        $this->getDataGenerator()->enrol_user($this->adminid, $this->courses['hc_1']->id, 3);

        $this->courseunderrubric([
            'admin' => [
                'hc_1' => 'Other courses',
            ],
        ]);
    }

    /**
     * Test that we are applying the correct CSS completion classes
     */
    public function test_css_completion_classes() {
        global $CFG, $DB;

        // Create a course.
        $this->create_misc_courses(1, 1);

        // Enroll user1 as a student in course 1.
        $this->getDataGenerator()->enrol_user($this->user1->id, $this->courses['c_1']->id);

        // While completion is not enabled site-wide.
        $this->courselistexcludes([
            'user1' => [ 'complete' ],
        ]);

        // While completion is enabled site-wide, but not on the course.
        $CFG->enablecompletion = true;
        $this->courselistexcludes([
            'user1' => [ 'complete' ],
        ]);

        // Enable completion on the course.
        $record = new stdClass();
        $record->id = $this->courses['c_1']->id;
        $record->enablecompletion = 1;
        $DB->update_record('course', $record);
        $this->courselistincludes([
            'user1' => [ 'incomplete' ],
        ]);

        // Finally, mark the completion.
        $completion = new completion_completion([
            'course' => $this->courses['c_1']->id,
            'userid' => $this->user1->id,
        ]);
        $completion->mark_complete();
        $this->courselistincludes([
            'user1' => [ 'complete' ],
        ]);
        $this->courselistexcludes([
            'user1' => [ 'incomplete' ],
        ]);
    }

    /**
     * Test enrolment filter.
     */
    public function test_enrolment_filter() {
        $this->create_rich_site();

        // Include courses with guest access enabled.
        $filterconfig = <<<EOF
enrolment | c | guest | Open to guests
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        $this->courselistincludes([
            'user1' => ['Open to guests', 'Guest enrolment'],
            'user3' => ['Open to guests', 'Guest enrolment'],
            'none'  => ['Open to guests', 'Guest enrolment'],
        ]);

        $this->courselistexcludes([
            'user3' => ['Self enrolment'],
        ]);

        // Include courses with self enrolment enabled.
        $filterconfig = <<<EOF
enrolment | c | self | Allowing self enrolment
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        $this->courselistincludes([
            'user1' => ['Allowing self enrolment', 'Self enrolment'],
            'user3' => ['Allowing self enrolment', 'Self enrolment'],
            'none'  => ['Allowing self enrolment', 'Self enrolment'],
        ]);

        $this->courselistexcludes([
            'user3' => ['Guest enrolment'],
        ]);

        // Include courses with either self or guest enrolment enabled.
        $filterconfig = <<<EOF
enrolment | c | self, guest | Self-serve
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        $this->courselistincludes([
            'user1' => ['Self-serve', 'Self enrolment'],
            'user2' => ['Self-serve', 'Guest enrolment'],
            'user3' => ['Self-serve', 'Guest enrolment'],
            'none'  => ['Self-serve', 'Guest enrolment'],
        ]);
    }


    /**
     * Generate some users to test against
     */
    private function setupusers() {

        global $USER;

        // Get the admin id.
        $this->setAdminUser();
        $this->adminid = $USER->id;

        // Set up 3 regular users.
        $this->user1 = $this->getDataGenerator()->create_user([
            'username'  => 'user1',
            'firstname' => 'User',
            'lastname'  => 'One',
            'email'     => 'user1@unittest.com',
        ]);
        $this->user2 = $this->getDataGenerator()->create_user([
            'username'  => 'user2',
            'firstname' => 'User',
            'lastname'  => 'Two',
            'email'     => 'user2@unittest.com',
        ]);
        $this->user3 = $this->getDataGenerator()->create_user([
            'username'  => 'user3',
            'firstname' => 'User',
            'lastname'  => 'Three',
            'email'     => 'user3@unittest.com',
        ]);
    }

    /**
     * Copy the test filter so that settings will detect it.
     */
    private function setupfilter() {
        $frompath = __DIR__ . '/behat/data/external_filter.php';
        $topath = __DIR__ . '/behat/data/fcl_filter.php';
        if (file_exists($frompath)) {
            copy($frompath, $topath);
        }
    }

    /**
     * Generate some courses in the Category 1 category
     *
     * @param int $start A first value to apply incrementally to several courses
     * @param int $end The value at which to stop generating courses
     */
    private function create_misc_courses($start = 1, $end = 8) {

        for ($i = $start; $i <= $end; $i++) {
            $this->courses["c_$i"] = $this->getDataGenerator()->create_course([
                'fullname' => "Course $i in Misc",
                'shortname' => "c_$i",
                'idnumber' => strtoupper("c_$i"),
            ]);
        }
    }

    /**
     * Build a more complicated site for more intresting testing
     * Use the following structure
     *
     * Category 1
     *   Course 1, c_1
     *   ...
     *   Course 12, c_12
     *   Child category 1
     *     Course 1 in Child category 1, cc1_1
     *     ...
     *     Course 3 in Child category 1, cc1_3, hidden
     *   Child category 2
     *     Course 1 in Child category 2, cc2_1
     *     ...
     *     Course 3 in Child category 2, cc2_3, hidden
     *     Grandchild category 1
     *       Course 1 in Grandchild category, gc1_1
     *       ...
     *       Course 3 in Grandchild category, gc1_3, hidden
     *   Hidden category
     *     Course 1 in Hidden category, hc_1
     *     ...
     *     Course 3 in Hidden category, hc_3, hidden
     *     Hidden category child
     *       Course 1 in Hidden category child, hcc_1
     *       ...
     *       Course 3 in Hidden category child, hcc_3, hidden
     *     Hidden category visible Child
     *       Course 1 in Hidden category child, hcvc_1
     *       ...
     *       Course 3 in Hidden category child, hcvc_3, hidden
     * Sibling category
     *   Course 1 in Sibling category, sc_1
     *   ...
     *   Course 3 in Sibling category, sc_3, hidden
     *   Non-ascii matching, øthér
     */
    private function create_rich_site() {
        global $DB;

        // Add some courses under Category 1.
        $this->create_misc_courses(1, 3);

        // Create categories.
        $this->categories['cc1'] = $this->getDataGenerator()->create_category([
            'name'     => 'Child category 1',
            'parent'    => 1,
            'idnumber' => 'cc1',
        ]);
        $this->categories['cc2'] = $this->getDataGenerator()->create_category([
            'name'     => 'Child category 2',
            'parent'    => 1,
            'idnumber' => 'cc2',
        ]);
        $cc2id = $this->categories['cc2']->id;
        $this->categories['gc1'] = $this->getDataGenerator()->create_category([
            'name'     => 'Grandchild category 1',
            'parent'   => $cc2id,
            'idnumber' => 'gc1',
        ]);
        $this->categories['hc'] = $this->getDataGenerator()->create_category([
            'name'     => 'Hidden category',
            'parent'   => 1,
            'idnumber' => 'hc',
            'visible'  => 0,
        ]);
        $hcid = $this->categories['hc']->id;
        $this->categories['hcc'] = $this->getDataGenerator()->create_category([
            'name'     => 'Hidden category child',
            'parent'   => $hcid,
            'idnumber' => 'hcc',
        ]);
        $this->categories['hcvc'] = $this->getDataGenerator()->create_category([
            'name'     => 'Hidden category visible child',
            'parent'   => $hcid,
            'idnumber' => 'hcvc',
        ]);
        $this->categories['hcvc']->show();
        $this->categories['sc'] = $this->getDataGenerator()->create_category([
            'name'     => 'Sibling category',
            'idnumber' => 'sc',
        ]);

        // Create three courses in each category, the third of which is hidden.
        foreach ($this->categories as $id => $category) {
            for ($i = 1; $i <= 3; $i++) {
                $shortname = "{$id}_$i";
                $params = [
                    'fullname'  => "Course $i in $category->name",
                    'shortname' => $shortname,
                    'idnumber'  => strtoupper($shortname),
                    'category'  => $category->id,
                ];
                if ($i % 3 == 0) {
                    $params['visible'] = 0;
                } else {
                    $params['visible'] = 1;
                }
                $this->courses[$shortname] = $this->getDataGenerator()->create_course($params);
            }
        }

        // Create a course with a non-ascii shortname in the Sibling category.
        $params = [
            'fullname'  => 'Non-ascii matching',
            'shortname' => 'øthér',
            'idnumber'  => 'ØTHÉR',
            'category'  => $this->categories['sc']->id,
        ];
        $this->courses['øthér'] = $this->getDataGenerator()->create_course($params);

        // Create a course with HTML entities in the fullname and shortname.
        $params = [
            'fullname'  => '&uuml;&amp;: HTML Entities',
            'shortname' => '&uuml;&amp;shortname',
            'idnumber'  => '&uuml;&amp;IDNUMBER',
            'category'  => $this->categories['sc']->id,
        ];
        $this->courses['&uuml;&amp;shortname'] = $this->getDataGenerator()->create_course($params);

        // Create a course with guest enrolment enabled in the Sibling category.
        $params = [
            'fullname'  => 'Guest enrolment enabled',
            'shortname' => 'guestenrolment',
            'idnumber'  => 'GUESTENROLMENT',
            'category'  => $this->categories['sc']->id,
        ];
        $this->courses['guestenrolment'] = $this->getDataGenerator()->create_course($params);
        $guestplugin = enrol_get_plugin('guest');
        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $instance = $guestplugin->add_instance($this->courses['guestenrolment'], ['status' => ENROL_INSTANCE_ENABLED,
                                                                'name' => 'Test instance',
                                                                'customint6' => 1,
                                                                'roleid' => $studentrole->id]);

        // Create a course with self enrolment enabled in the Sibling category.
        $params = [
            'fullname'  => 'Self enrolment enabled',
            'shortname' => 'selfenrolment',
            'idnumber'  => 'SELFENROLMENT',
            'category'  => $this->categories['sc']->id,
        ];
        $this->courses['selfenrolment'] = $this->getDataGenerator()->create_course($params);
        $selfplugin = enrol_get_plugin('self');
        $instanceid1 = $selfplugin->add_instance($this->courses['selfenrolment'], ['status' => ENROL_INSTANCE_ENABLED,
                                                                'name' => 'Test instance',
                                                                'customint6' => 1,
                                                                'roleid' => $studentrole->id]);

        // Enroll user1 as a student in all courses.
        foreach ($this->courses as $course) {
            $this->getDataGenerator()->enrol_user($this->user1->id, $course->id);
        }

        // Enroll user2 as a teacher in all courses.
        foreach ($this->courses as $course) {
            $this->getDataGenerator()->enrol_user($this->user2->id, $course->id, 3);
        }
    }

    /**
     * Test the get_rubrics method that facilitates mobile rendering
     */
    public function test_get_rubrics() {

        // Create 8 courses in the default category: Category 1.
        $this->create_misc_courses(1, 8);
        $page = new moodle_page();
        $this->switchuser('admin');
        $bi = new block_filtered_course_list();
        $bi->instance = new StdClass();
        $bi->instance->id = 17;
        $bi->get_content($page);
        $this->assertEquals(8, count($bi->get_rubrics()[0]->courses));
    }

    /**
     * Test whether given users see any block at all
     *
     * @param array $expectations A list of users and whether or not they should see any block
     */
    private function noblock($expectations = []) {
        $page = new moodle_page();
        foreach ($expectations as $user => $result) {
            $this->switchuser($user);
            $bi = new block_filtered_course_list();
            $bi->instance = new StdClass();
            $bi->instance->id = 17;
            if ($result === true) {
                if (isset($bi->get_content($page)->text)) {
                    // In some cases the text exists but is empty.
                    $this->assertEmpty(
                        $bi->get_content($page)->text,
                        "$user should not see a block, but ... " . $bi->get_content($page)->text
                    );
                } else {
                    // In other cases the text will not have been set at all.
                    $this->assertFalse(isset($bi->get_content($page)->text));
                }
            } else {
                $this->assertNotEmpty($bi->get_content($page)->text, "$user should see a block.");
            }
        }
    }

    /**
     * Test whether given users can see a link to 'All courses'
     *
     * @param array $expectations A list of users and whether or not they should see the link
     */
    private function allcourseslink($expectations = []) {
        $page = new moodle_page();
        foreach ($expectations as $user => $result) {
            $this->switchuser($user);
            $bi = new block_filtered_course_list();
            $bi->instance = new StdClass();
            $bi->instance->id = 17;
            $footer = $bi->get_content($page)->footer;
            if ($result) {
                $this->assertStringContainsString($result, $footer, "$user should see the All-courses link.");
            } else {
                $this->assertStringNotContainsString('All courses', $footer, "$user should not see the All-courses link.");
            }
        }
    }

    /**
     * Test whether the course listing includes a particular course for a given user
     *
     * @param array $expectations A list of users and courses they should see
     */
    private function courselistincludes($expectations = []) {
        $page = new moodle_page();
        foreach ($expectations as $user => $courses) {
            $this->switchuser($user);
            $bi = new block_filtered_course_list();
            $bi->instance = new StdClass();
            $bi->instance->id = 17;
            foreach ($courses as $course) {
                $this->assertStringContainsString($course, $bi->get_content($page)->text, "$user should see $course.");
            }
        }
    }

    /**
     * Test whether the course listing includes a particular course for a given user
     *
     * @param array $expectations A list of users and courses they should not see
     */
    private function courselistexcludes($expectations = []) {
        $page = new moodle_page();
        foreach ($expectations as $user => $courses) {
            $this->switchuser($user);
            $bi = new block_filtered_course_list();
            $bi->instance = new StdClass();
            $bi->instance->id = 17;
            foreach ($courses as $course) {
                $this->assertStringNotContainsString($course, $bi->get_content($page)->text, "$user should not see $course.");
            }
        }
    }

    /**
     * Change the current user
     *
     * @param mixed $user Can be a user type or a specific user object
     */
    private function switchuser($user) {
        if ($user == 'none') {
            $this->setUser(null);
        } else if ($user == 'guest') {
            $this->setGuestUser();
        } else if ($user == 'admin') {
            $this->setAdminUser();
        } else {
            $this->setUser($this->$user);
        }
    }

    /**
     * Test whether the course listing includes a particular course for a given user under a particular heading
     *
     * @param array $expectations A list of users and courses they should or should not see
     * @param string $relation 'under' indicates that the user should see the course, otherwise not
     */
    private function courseunderrubric($expectations = [], $relation = 'under') {
        $page = new moodle_page();
        foreach ($expectations as $user => $courses) {
            $this->switchuser($user);
            $bi = new block_filtered_course_list();
            $bi->instance = new StdClass();
            $bi->instance->id = 17;
            $html = new DOMDocument();
            $html->loadHTML(htmlspecialchars_decode(htmlentities($bi->get_content($page)->text)));
            $rubrics = $html->getElementsByTagName('div');
            foreach ($courses as $course => $rubricmatch) {
                $hits = 0;
                foreach ($rubrics as $rubric) {
                    $rubrictitle = $rubric->nodeValue;
                    if (trim($rubrictitle) != $rubricmatch || $rubric->getAttribute('class') == 'tablist') {
                        continue;
                    }
                    $ul = $rubric->nextSibling;
                    while (!($ul instanceof DOMElement)) {
                        $ul = $ul->nextSibling;
                    }
                    $anchors = $ul->getElementsByTagName('a');
                    foreach ($anchors as $anchor) {
                        $anchorclass = $anchor->attributes->getNamedItem('class')->nodeValue;
                        if (strpos($anchorclass, 'block-fcl__list__link') !== false) {
                            $anchortitle = $anchor->attributes->getNamedItem('title')->nodeValue;
                            if ($anchortitle == $course) {
                                $hits++;
                            }
                        }
                    }
                }
                if ($relation == 'not') {
                    $this->assertEquals(0, $hits, "$user should not see $course under $rubricmatch");
                } else {
                    $this->assertGreaterThan(0, $hits, "$user should see $course under $rubricmatch");
                }
            }
        }
    }

    /**
     * Test whether a given rubric is expanded or not
     *
     * @param array $expectations A list of rubric titles
     * @param string $operator Indicates whether to expect expanded or collapsed
     */
    private function sectionexpanded($expectations = [], $operator = '') {
        $page = new moodle_page();
        $this->switchuser('user1');
        $bi = new block_filtered_course_list();
        $bi->instance = new stdClass();
        $bi->instance->id = 17;
        $html = new DOMDocument();
        $html->loadHTML($bi->get_content($page)->text);
        $rubrics = $html->getElementsByTagName('div');
        foreach ($rubrics as $rubric) {
            $title = $rubric->textContent;
            if (!(array_key_exists($title, $expectations))) {
                continue;
            }
            $state = $expectations[$title];
            $class = $rubric->getAttribute('class');
            if ($operator == 'not') {
                $this->assertNotContains($state, $class, "The class attribute of '$title' should not contain $state.");
            } else {
                $this->assertContains($state, $class, "The class attribute of '$title' should contain $state.");
            }
        }
    }
}
