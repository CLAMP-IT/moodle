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

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/blocks/moodleblock.class.php');
require_once(dirname(__FILE__) . '/../block_filtered_course_list.php');

/**
 * PHPUnit tests
 *
 * @package    block_filtered_course_list
 * @copyright  2016 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_filtered_course_list_block_testcase extends advanced_testcase {

    /** @var int The admin user's id number */
    private $adminid;
    /** @var object A test user */
    private $user1;
    /** @var object A test user */
    private $user2;
    /** @var array A list of course objects to be used in several tests */
    private $courses = array();
    /** @var array A list of categories into which the test courses can be grouped */
    private $categories = array();

    /**
     * General setup for PHPUnit testing
     */
    protected function setUp() {

        global $CFG;
        unset ( $CFG->maxcategorydepth );
        $this->resetAfterTest(true);
        $this->_setupusers();

    }

    /**
     * Test that the default settings are applying as expected
     */
    public function test_default_settings() {

        // Confirm expected defaults.
        // We omit the multiline default for the 'filters' setting.

        $fclconfig = get_config('block_filtered_course_list');

        $configdefaults = array (
            'hideallcourseslink' => 0,
            'hidefromguests'     => 0,
            'hideothercourses'   => 0,
            'maxallcourse'       => 10,
            'managerview'        => 'all',
            'primarysort'        => 'fullname',
            'primaryvector'      => 'ASC',
            'secondarysort'      => 'none',
            'secondaryvector'    => 'ASC',
        );

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
        $this->_noblock ( array (
            'none'  => true,
            'guest' => true,
            'user1' => true,
            'admin' => true
        ));

    }

    /**
     * Test a site that has only one category and no enrollments
     */
    public function test_single_category_site_with_no_enrollments() {

        // Create 8 courses in the default category: Miscellaneous.
        $this->_create_misc_courses( 1, 8 );

        // Anonymous, Guest and Admin should see all courses.
        $this->_courselistincludes ( array (
            'none'  => array ( 'Course 1' , 'Course 8' ),
            'guest' => array ( 'Course 1' , 'Course 8' ),
            'admin' => array ( 'Course 1' , 'Course 8' )
        ));

        // Regular users should not see a block (because there are as yet no enrollments).
        $this->_noblock ( array (
            'user1' => true,
        ));

    }

    /**
     * Test a small site with one category and some enrollments
     */
    public function test_small_single_category_site_with_enrollments() {

        // Create 8 courses in the default category: Miscellaneous.
        $this->_create_misc_courses( 1, 8 );

        // Enroll user1 as a teacher in course 1 and a student in courses 3 and 5.
        $this->getDataGenerator()->enrol_user( $this->user1->id , $this->courses['c_1']->id , 3 );
        $this->getDataGenerator()->enrol_user( $this->user1->id , $this->courses['c_3']->id );
        $this->getDataGenerator()->enrol_user( $this->user1->id , $this->courses['c_5']->id );

        // Anonymous, Guest and Admin should see all courses.
        $this->_courselistincludes ( array (
            'none'  => array ( 'Course 1' , 'Course 8' ),
            'guest' => array ( 'Course 1' , 'Course 8' ),
            'admin' => array ( 'Course 1' , 'Course 8' )
        ));

        // User1 should see courses 1, 3 and 5.
        $this->_courselistincludes ( array (
            'user1' => array ( 'Course 1' , 'Course 3' , 'Course 5' )
        ));

        // User1 should not see course 2 or course 6.
        $this->_courselistexcludes ( array (
            'user1' => array ( 'Course 2' , 'Course 6' )
        ));
    }

    /**
     * Test a larger site with enrollments in one category
     */
    public function test_larger_single_category_site_with_enrollments() {

        // Create 12 courses in the default category: Miscellaneous.
        $this->_create_misc_courses( 1, 12 );

        // Enroll user1 as a teacher in course 1 and a student in courses 3 and 5.
        $this->getDataGenerator()->enrol_user( $this->user1->id , $this->courses['c_1']->id , 3 );
        $this->getDataGenerator()->enrol_user( $this->user1->id , $this->courses['c_3']->id );
        $this->getDataGenerator()->enrol_user( $this->user1->id , $this->courses['c_5']->id );

        // The block should not display individual courses to anonymous, guest, or admin.
        $this->_courselistexcludes ( array (
            'none'  => array ( 'Course 1' ),
            'guest' => array ( 'Course 1' ),
            'admin' => array ( 'Course 1' )
        ));

        // The block should offer a category link to anonymous, guest, and admin.
        $this->_courselistincludes ( array (
            'none'  => array ( 'Miscellaneous' ),
            'guest' => array ( 'Miscellaneous' ),
            'admin' => array ( 'Miscellaneous' )
        ));

        // User1 should still see courses 1, 3 and 5.
        $this->_courselistincludes ( array (
            'user1' => array ( 'Course 1' , 'Course 3' , 'Course 5' )
        ));

        // User1 should not see course 2 or course 6.
        $this->_courselistexcludes ( array (
            'user1' => array ( 'Course 2' , 'Course 6' )
        ));
    }

    /**
     * Test a rich site with several courses in various categories
     */
    public function test_rich_site_with_defaults() {

        $this->_create_rich_site();

        // With no special settings, the behavior should be as for a larger single-category site.
        set_config('filters', '', 'block_filtered_course_list');

        // The block should not display individual courses to anonymous, guest, or admin.
        // The block should not display links to categories below the top level.
        $this->_courselistexcludes ( array (
            'none'  => array ( 'Course', 'Child', 'Grandchild' ),
            'guest' => array ( 'Course', 'Child', 'Grandchild' ),
            'admin' => array ( 'Course', 'Child', 'Grandchild' )
        ));

        // The block should offer top-level category links to anonymous, guest, and admin.
        $this->_courselistincludes ( array (
            'none'  => array ( 'Miscellaneous', 'Sibling' ),
            'guest' => array ( 'Miscellaneous', 'Sibling' ),
            'admin' => array ( 'Miscellaneous', 'Sibling' )
        ));

        // Regular users should see links to visible courses in visible categories under 'Other courses'.
        // Teachers should see links to all their courses, visible or hidden, and under hidden categories.
        $this->_courseunderrubric( array(
            'user1' => array(
                'c_1'   => 'Other courses',
                'cc1_2' => 'Other courses',
                'gc1_1' => 'Other courses',
                'sc_2'  => 'Other courses',
                'øthér' => 'Other courses',
            ),
            'user2' => array(
                'c_1'   => 'Other courses',
                'cc1_3' => 'Other courses',
                'gc1_1' => 'Other courses',
                'hc_1'  => 'Other courses',
                'hcc_3' => 'Other courses',
                'sc_2'  => 'Other courses',
            )
        ));

        // Students should not see links to hidden courses or visible courses under hidden categories.
        $this->_courselistexcludes( array(
            'user1' => array( 'cc1_3', 'gc1_3', 'hc_1', 'hcc_2' )
        ));

        // The block should not appear for a user who is not enrolled in any visible courses.
        $this->_noblock( array(
            'user3' => true
        ));

    }

    /**
     * Test a complicated site filtered by category
     */
    public function test_rich_site_filtered_by_category() {

        $this->_create_rich_site();

        // Change setting to filter by categories.
        $filterconfig = <<<EOF
category | c | 0
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        // The block should not display individual courses to anonymous, guest, or admin.
        // The block should not display links to categories below the top level.
        $this->_courselistexcludes ( array (
            'none'  => array ( 'Course', 'Child', 'Grandchild' ),
            'guest' => array ( 'Course', 'Child', 'Grandchild' ),
            'admin' => array ( 'Course', 'Child', 'Grandchild' )
        ));

        // The block should offer top-level category links to anonymous, guest, and admin.
        $this->_courselistincludes ( array (
            'none'  => array ( 'Miscellaneous', 'Sibling' ),
            'guest' => array ( 'Miscellaneous', 'Sibling' ),
            'admin' => array ( 'Miscellaneous', 'Sibling' )
        ));

        // Regular users should see links to visible courses under corresponding visible categories.
        // Teachers should see links to all their courses, visible or hidden, and under hidden categories.
        // Courses under a hidden category will appear under "Other courses" to those who can't see the category.
        // With admins_sees_own an admin should see hidden courses under hidden categories.

        // Change the managerview setting to 'own'.
        set_config('managerview', 'own', 'block_filtered_course_list');

        // Enroll admin in 'hc_1'.
        $this->getDataGenerator()->enrol_user( $this->adminid, $this->courses['hc_1']->id, 3 );

        $this->_courseunderrubric( array(
            'user1' => array(
                'c_1'   => 'Miscellaneous',
                'cc1_2' => 'Child category 1',
                'gc1_1' => 'Grandchild category 1',
                'sc_2'  => 'Sibling category',
            ),
            'user2' => array(
                'c_1'   => 'Miscellaneous',
                'cc1_3' => 'Child category 1',
                'gc1_1' => 'Grandchild category 1',
                'hc_1'  => 'Other courses',
                'hcc_3' => 'Other courses',
                'sc_2'  => 'Sibling category',
                'øthér' => 'Sibling category',
            ),
            'admin' => array(
                'hc_1' => 'Hidden category'
            )
        ));

        // Courses should appear only under immediate parents.
        $this->_courseunderrubric ( array (
            'user1' => array ( 'gc1_1' => 'Miscellaneous' ),
            'user2' => array ( 'gc1_1' => 'Child category 1' )
        ) , 'not' );

        // Students should not see links to hidden courses or visible courses under hidden categories.
        $this->_courselistexcludes( array(
            'user1' => array( 'cc1_3', 'gc1_3', 'hc_1', 'hcc_2' )
        ));

        // The block should not appear for a user who is not enrolled in any visible courses.
        $this->_noblock( array(
            'user3' => true
        ));

        // Now try switching the root category setting.
        $cc2id = $this->categories['cc2']->id;
        $filterconfig = <<<EOF
category | collapsed | $cc2id
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        // There should be no rubric for Miscellaneous.
        $this->_courselistexcludes( array (
            'user1' => array ( 'Miscellaneous' )
        ));

        // Courses directly under Miscellaneous should continue to appear in 'Other courses'.
        $this->_courseunderrubric( array(
            'user1' => array(
                'c_1'   => 'Other courses',
                'cc1_2' => 'Other courses',
                'cc2_1' => 'Child category 2',
                'gc1_1' => 'Grandchild category 1',
                'sc_2'  => 'Other courses',
            )
        ));

        // Test the ability to set recursion depth on top-level category filter.
        // Confirm also that comments in the category and depth fields will be ignored.
        $filterconfig = <<<EOF
category | collapsed | 0 (Top level) | 1 deep
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        $this->_courseunderrubric( array(
            'user1' => array(
                'c_1'   => 'Miscellaneous',
                'cc1_2' => 'Other courses',
                'gc1_1' => 'Other courses',
                'sc_2'  => 'Sibling category',
            ),
        ));

        $this->_sectionexpanded ( array(
            'Miscellaneous'    => 'collapsed',
            'Sibling category' => 'collapsed',
        ));

        // Test the ability to set recursion depth on specific categories.
        $cc1id = $this->categories['cc1']->id;
        $scid = $this->categories['sc']->id;
        $filterconfig = <<<EOF
category | expanded | $cc1id | 1
category | collapsed | $scid  | 1
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        $this->_courseunderrubric( array(
            'user1' => array(
                'c_1'   => 'Other courses',
                'cc1_2' => 'Child category 1',
                'gc1_1' => 'Other courses',
                'sc_2'  => 'Sibling category',
            ),
        ));

        $this->_sectionexpanded ( array(
            'Child category 1' => 'expanded',
            'Sibling category' => 'collapsed',
        ));
    }

    /**
     * Test shortname filtering
     */
    public function test_shortnames() {

        $this->_create_rich_site();

        // Set a current and future shortname.
        $filterconfig = <<<EOF
shortname | expanded | Current courses       | _1
shortname | expanded | Future courses        | _2
shortname | expanded | Non-ascii             | ø
shortname | expanded | Child courses         | cc
shortname | expanded | Unnumbered categories | c_
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        // The block should not display individual courses to anonymous, guest, or admin.
        // The block should not display links to categories below the top level.
        $this->_courselistexcludes ( array (
            'none'  => array ( 'Course', 'Child', 'Grandchild' ),
            'guest' => array ( 'Course', 'Child', 'Grandchild' ),
            'admin' => array ( 'Course', 'Child', 'Grandchild' )
        ));

        // The block should offer top-level category links to anonymous, guest, and admin.
        $this->_courselistincludes ( array (
            'none'  => array ( 'Miscellaneous', 'Sibling' ),
            'guest' => array ( 'Miscellaneous', 'Sibling' ),
            'admin' => array ( 'Miscellaneous', 'Sibling' )
        ));

        // The block should list'Current', 'Future', and 'Other courses'.
        $this->_courseunderrubric( array(
            'user1' => array(
                'c_1'   => 'Current courses',
                'cc1_1' => 'Current courses',
                'cc2_1' => 'Current courses',
                'gc1_1' => 'Current courses',
                'sc_1'  => 'Current courses',
                'øthér' => 'Non-ascii',
                'cc1_2' => 'Child courses',
                'cc2_1' => 'Child courses',
                'sc_2'  => 'Unnumbered categories',
            ),
            'user2' => array(
                'c_2'   => 'Future courses',
                'cc1_2' => 'Future courses',
                'cc2_2' => 'Future courses',
                'gc1_2' => 'Future courses',
                'sc_2'  => 'Future courses',
                'hc_2'  => 'Future courses',
                'hc_3'  => 'Unnumbered categories',
                'c_3'   => 'Unnumbered categories',
                'cc2_2' => 'Child courses',
                'cc2_2' => 'Future courses',
                'gc1_3' => 'Other courses'
            )
        ));
    }

    /**
     * Test shortnames with regex
     */
    public function test_regex_shortnames() {

        $this->_create_rich_site();

        // Use regex for shortname matches.
        $filterconfig = <<<EOF
regex | exp | All but default | ^[a-zø]{2}
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        // This new rubric should exclude courses with a shortname like 'c_1'.
        // It does not begin with two lowercase letters.
        $this->_courseunderrubric ( array (
            'user1' => array (
                'c_1' => 'All but default',
            )
        ), 'not');

        // Courses under any other category should be listed.
        $this->_courseunderrubric ( array (
            'user1' => array (
                'cc1_1' => 'All but default',
                'cc2_1' => 'All but default',
                'gc1_1' => 'All but default',
                'sc_1'  => 'All but default',
                'øthér' => 'All but default',
            ),
        ));
    }

    /**
     * Test a site with mixed filters
     */
    public function test_mixed_filters() {

        $this->_create_rich_site();

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
        $this->_courseunderrubric( array(
            'user1' => array(
                'c_1'   => 'Ones',
                'sc_1'  => 'Ones',
                'cc2_1' => 'Ones',
                'cc2_1' => 'Child category 2',
                'cc2_2' => 'Child category 2',
                'sc_1'  => 'Sibling category',
                'sc_2'  => 'Sibling category',
                'sc_2'  => 'Twos',
            ),
        ));
    }

    /**
     * Test the ability to use the admin settings to hide the link to 'All courses'
     */
    public function test_setting_hideallcourseslink() {

        $this->_create_rich_site();

        // Set up simple matching.
        $filterconfig = <<<EOF
shortname | e | Courses | _
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        // Any user who sees the should also see the "All courses" link.
        $this->_allcourseslink ( array (
            'none'  => true,
            'guest' => true,
            'user1' => true,
            'admin' => true,
            'user3' => false
        ));

        // Hide the All-courses link from all but admins.
        set_config('hideallcourseslink', 1, 'block_filtered_course_list');

        // Only an admin should see the "All courses" link.
        $this->_allcourseslink ( array (
            'none'  => false,
            'guest' => false,
            'user1' => false,
            'admin' => true
        ));
    }

    /**
     * Test whether an admin can hide the block from guests
     */
    public function test_setting_hidefromguests() {

        $this->_create_rich_site();

        // Set up simple matching.
        $filterconfig = <<<EOF
shortname | expanded | Courses | _
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        // All users (except a regular user enrolled in no courses) should see the block.
        $this->_noblock ( array (
            'none'  => false,
            'guest' => false,
            'user1' => false,
            'admin' => false,
            'user3' => true
        ));

        // Change the setting to hide the block from guests and anonymous visitors.
        set_config('hidefromguests', 1, 'block_filtered_course_list');

        // Now only admins and regular enrolled users should see the block.
        $this->_noblock ( array (
            'none'  => true,
            'guest' => true,
            'user1' => false,
            'admin' => false,
            'user3' => true
        ));
    }

    /**
     * Test whether an admin can choose whether or not to display a link to 'Other courses'
     * not covered by the filter
     */
    public function test_setting_hideothercourses() {

        $this->_create_rich_site();

        $filterconfig = <<<EOF
shortname | e | Current courses | gc
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        // Enrollments that do not match appear under 'Other courses'.
        $this->_courseunderrubric ( array (
            'user1' => array (
                'sc_1' => 'Other courses',
            ),
        ));

        // Hide the catch-all 'Other courses' rubric.
        set_config('hideothercourses', 1, 'block_filtered_course_list');

        // No other courses are listed.
        $this->_courselistexcludes ( array (
            'user1' => array( 'Other courses' ),
        ));

    }

    /**
     * Test that aria attributes are added for collapsible rubrics
     */
    public function test_aria_attributes() {

        $this->_create_rich_site();

        // Set up simple matching.
        $filterconfig = <<<EOF
shortname | exp | Courses | _
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        // For users enrolled in courses the various rubrics are collapsible.
        $this->_courselistincludes ( array (
            'user1' => array (
                'collapsible',
                'aria-multiselectable',
                'aria-controls',
                'aria-expanded',
                'aria-selected',
                'aria-labelledby',
                'aria-hidden',
            ),
        ));
    }

    /**
     * Test whether an admin can designate particular rubrics to be expanded automatically
     */
    public function test_setting_expanded_section() {

        $this->_create_rich_site();

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
        $this->_sectionexpanded ( array(
            'Current courses'       => 'collapsed',
            'Future courses'        => 'collapsed',
            'Child courses'         => 'collapsed',
            'Unnumbered categories' => 'collapsed',
            'Miscellaneous'         => 'collapsed',
            'Child category'        => 'collapsed',
        ));

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
        $this->_sectionexpanded ( array(
            'Current courses'       => 'expanded',
            'Future courses'        => 'collapsed',
            'Child courses'         => 'collapsed',
            'Unnumbered categories' => 'expanded',
            'Miscellaneous'         => 'expanded',
            'Child category'        => 'expanded',
        ));

        // Now test config lines that do not set expansion state or use invalid values.
        $filterconfig = <<<EOF
shortname | | Current courses | _1
shortname | invalid value | Future courses | _2
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        // All sections should be collapsed.
        $this->_sectionexpanded ( array(
            'Current courses'       => 'collapsed',
            'Future courses'        => 'collapsed',
        ));
    }

    /**
     * Test that rubric titles pass through htmlentities()
     */
    public function test_rubric_title_htmlentities() {

        $this->_create_rich_site();

        // Set up a shortname rubrics.
        $filterconfig = <<<EOF
shortname | col | Current <br />courses | _1
EOF;
        set_config('filters', $filterconfig, 'block_filtered_course_list');

        // We should see the course under the original text.
        // This test would fail if the line break were interpreted.
        $this->_courseunderrubric ( array (
            'user1' => array (
                'c_1' => 'Current <br />courses',
            )
        ));
    }

    /**
     * Test whether an admin can choose whether to see all courses or only her own
     */
    public function test_setting_managerview() {

        $this->_create_rich_site();
        set_config('filters', '', 'block_filtered_course_list');

        // The block should not display links to categories below the top level.
        $this->_courselistexcludes ( array (
            'admin' => array ( 'Course', 'Child', 'Grandchild' )
        ));

        // The block should offer top-level category links to anonymous, guest, and admin.
        $this->_courselistincludes ( array (
            'admin' => array ( 'Miscellaneous', 'Sibling' )
        ));

        // Change the managerview setting to 'own'.
        set_config('managerview', 'own', 'block_filtered_course_list');

        // An admin enrolled in no courses should still see only the top level.
        $this->_courselistexcludes ( array (
            'admin' => array ( 'Course', 'Child', 'Grandchild' )
        ));

        $this->_courselistincludes ( array (
            'admin' => array ( 'Miscellaneous', 'Sibling' )
        ));

        // Put the admin in a course.
        $this->getDataGenerator()->enrol_user( $this->adminid, $this->courses['gc1_1']->id );

        // Admin should see the course listing as a regular user would.
        $this->_courselistexcludes ( array (
            'admin' => array ( 'Miscellaneous', 'Sibling' )
        ));

        $this->_courseunderrubric ( array (
            'admin' => array ( 'gc1_1' => 'Other courses' )
        ));

    }

    /**
     * Ensure that the block honors a possible 'disablemycourses' setting
     */
    public function test_setting_cfg_disablemycourses() {

        global $CFG;
        $this->_create_rich_site();
        set_config('filters', '', 'block_filtered_course_list');

        $CFG->disablemycourses = 1;

        // Enrolled users, like guests, should see a generic list of categories.
        $this->_courselistincludes ( array (
            'user1' => array ( 'Miscellaneous', 'Sibling' )
        ));

        // Enrolled users, like guests, should not see subcategories or specific courses.
        $this->_courselistexcludes ( array (
            'user1' => array ( 'Course', 'Child', 'Grandchild' )
        ));

        // On the other hand, managerview = own trumps disablemycourses.
        set_config('managerview', 'own', 'block_filtered_course_list');

        // Enroll admin in 'hc_1'.
        $this->getDataGenerator()->enrol_user( $this->adminid, $this->courses['hc_1']->id, 3 );

        $this->_courseunderrubric ( array (
            'admin' => array (
                'hc_1' => 'Other courses'
            )
        ));
    }

    /**
     * Generate some users to test against
     */
    private function _setupusers() {

        global $USER;

        // Get the admin id.
        $this->setAdminUser();
        $this->adminid = $USER->id;

        // Set up 3 regular users.
        $this->user1 = $this->getDataGenerator()->create_user(array(
            'username'  => 'user1',
            'firstname' => 'User',
            'lastname'  => 'One',
            'email'     => 'user1@unittest.com'
        ));
        $this->user2 = $this->getDataGenerator()->create_user(array(
            'username'  => 'user2',
            'firstname' => 'User',
            'lastname'  => 'Two',
            'email'     => 'user2@unittest.com'
        ));
        $this->user3 = $this->getDataGenerator()->create_user(array(
            'username'  => 'user3',
            'firstname' => 'User',
            'lastname'  => 'Three',
            'email'     => 'user3@unittest.com'
        ));

    }

    /**
     * Generate some courses in the Miscellaneous category
     *
     * @param int $start A first value to apply incrementally to several courses
     * @param int $end The value at which to stop generating courses
     */
    private function _create_misc_courses( $start=1, $end=8 ) {

        for ($i = $start; $i <= $end; $i++) {
            $this->courses["c_$i"] = $this->getDataGenerator()->create_course(array(
                'fullname' => "Course $i in Misc",
                'shortname' => "c_$i",
                'idnumber' => "c_$i"
            ));
        }
    }

    /**
     * Build a more complicated site for more intresting testing
     * Use the following structure
     *
     * Miscellaneous
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
     * Sibling category
     *   Course 1 in Sibling category, sc_1
     *   ...
     *   Course 3 in Sibling category, sc_3, hidden
     *   Non-ascii matching, øthér
     */
    private function _create_rich_site() {

        // Add some courses under Miscellaneous.
        $this->_create_misc_courses ( 1, 3 );

        // Create categories.
        $this->categories['cc1'] = $this->getDataGenerator()->create_category( array(
            'name'     => 'Child category 1',
            'parent'    => 1,
            'idnumber' => 'cc1'
        ));
        $this->categories['cc2'] = $this->getDataGenerator()->create_category( array(
            'name'     => 'Child category 2',
            'parent'    => 1,
            'idnumber' => 'cc2'
        ));
        $cc2id = $this->categories['cc2']->id;
        $this->categories['gc1'] = $this->getDataGenerator()->create_category( array(
            'name'     => 'Grandchild category 1',
            'parent'   => $cc2id,
            'idnumber' => 'gc1'
        ));
        $this->categories['hc'] = $this->getDataGenerator()->create_category( array(
            'name'     => 'Hidden category',
            'parent'   => 1,
            'idnumber' => 'hc',
            'visible'  => 0
        ));
        $hcid = $this->categories['hc']->id;
        $this->categories['hcc'] = $this->getDataGenerator()->create_category( array(
            'name'     => 'Hidden category child',
            'parent'   => $hcid,
            'idnumber' => 'hcc'
        ));
        $this->categories['sc'] = $this->getDataGenerator()->create_category( array(
            'name'     => 'Sibling category',
            'idnumber' => 'sc'
        ));

        // Create three courses in each category, the third of which is hidden.
        foreach ($this->categories as $id => $category) {
            for ($i = 1; $i <= 3; $i++) {
                $shortname = "${id}_$i";
                $params = array (
                    'fullname' => "Course $i in $category->name",
                    'shortname' => $shortname,
                    'idnumber'  => $shortname,
                    'category'  => $category->id
                );
                if ( $i % 3 == 0 ) {
                    $params['visible'] = 0;
                }
                $this->courses[$shortname] = $this->getDataGenerator()->create_course( $params );
            }
        }

        // Create a course with a non-ascii shortname in the Sibling category.
        $params = array (
            'fullname' => 'Non-ascii matching',
            'shortname' => 'øthér',
            'idnumber'  => 'øthér',
            'category'  => $this->categories['sc']->id
        );
        $this->courses['øthér'] = $this->getDataGenerator()->create_course( $params );

        // Enroll user1 as a student in all courses.
        foreach ($this->courses as $course) {
            $this->getDataGenerator()->enrol_user( $this->user1->id, $course->id );
        }

        // Enroll user2 as a teacher in all courses.
        foreach ($this->courses as $course) {
            $this->getDataGenerator()->enrol_user( $this->user2->id, $course->id, 3 );
        }
    }

    /**
     * Test whether given users see any block at all
     *
     * @param array $expectations A list of users and whether or not they should see any block
     */
    private function _noblock ( $expectations=array() ) {
        foreach ($expectations as $user => $result) {
            $this->_switchuser ( $user );
            $bi = new block_filtered_course_list;
            $bi->instance = new StdClass;
            $bi->instance->id = 17;
            if ( $result === true ) {
                if ( isset ( $bi->get_content()->text ) ) {
                    // In some cases the text exists but is empty.
                    $this->assertEmpty ( $bi->get_content()->text , "$user should not see a block." );
                } else {
                    // In other cases the text will not have been set at all.
                    $this->assertFalse ( isset ( $bi->get_content()->text ) );
                }
            } else {
                $this->assertNotEmpty ( $bi->get_content()->text , "$user should see a block." );
            }
        }
    }

    /**
     * Test whether given users can see a link to 'All courses'
     *
     * @param array $expectations A list of users and whether or not they should see the link
     */
    private function _allcourseslink ( $expectations=array() ) {
        foreach ($expectations as $user => $result) {
            $this->_switchuser ( $user );
            $bi = new block_filtered_course_list;
            $bi->instance = new StdClass;
            $bi->instance->id = 17;
            $footer = $bi->get_content()->footer;
            if ( $result === true ) {
                $this->assertContains ( 'All courses' , $footer , "$user should see the All-courses link." );
            } else {
                $this->assertNotContains ( 'All courses' , $footer , "$user should not see the All-courses link." );
            }
        }
    }

    /**
     * Test whether the course listing includes a particular course for a given user
     *
     * @param array $expectations A list of users and courses they should see
     */
    private function _courselistincludes ( $expectations=array() ) {
        foreach ($expectations as $user => $courses) {
            $this->_switchuser ( $user );
            $bi = new block_filtered_course_list;
            $bi->instance = new StdClass;
            $bi->instance->id = 17;
            foreach ($courses as $course) {
                $this->assertContains ( $course , $bi->get_content()->text , "$user should see $course." );
            }
        }
    }

    /**
     * Test whether the course listing includes a particular course for a given user
     *
     * @param array $expectations A list of users and courses they should not see
     */
    private function _courselistexcludes ( $expectations=array() ) {
        foreach ($expectations as $user => $courses) {
            $this->_switchuser ( $user );
            $bi = new block_filtered_course_list;
            $bi->instance = new StdClass;
            $bi->instance->id = 17;
            foreach ($courses as $course) {
                $this->assertNotContains ( $course , $bi->get_content()->text , "$user should not see $course." );
            }
        }
    }

    /**
     * Change the current user
     *
     * @param mixed $user Can be a user type or a specific user object
     */
    private function _switchuser ( $user ) {
        if ( $user == 'none' ) {
            $this->setUser( null );
        } else if ( $user == 'guest' ) {
            $this->setGuestUser();
        } else if ( $user == 'admin' ) {
            $this->setAdminUser();
        } else {
            $this->setUser( $this->$user );
        }
    }

    /**
     * Test whether the course listing includes a particular course for a given user under a particular heading
     *
     * @param array $expectations A list of users and courses they should or should not see
     * @param string $relation 'under' indicates that the user should see the course, otherwise not
     */
    private function _courseunderrubric ( $expectations=array() , $relation='under' ) {
        foreach ($expectations as $user => $courses) {
            $this->_switchuser ( $user );
            $bi = new block_filtered_course_list;
            $bi->instance = new StdClass;
            $bi->instance->id = 17;
            $html = new DOMDocument;
            $html->loadHTML( mb_convert_encoding( $bi->get_content()->text, 'HTML-ENTITIES', 'UTF-8' ));
            $rubrics = $html->getElementsByTagName('div');
            foreach ($courses as $course => $rubricmatch) {
                $hits = 0;
                foreach ($rubrics as $rubric) {
                    $rubrictitle = $rubric->nodeValue;
                    if ( $rubrictitle != $rubricmatch || $rubric->getAttribute('class') == 'tablist') {
                        continue;
                    }
                    $ul = $rubric->nextSibling;
                    $anchors = $ul->getElementsByTagName('a');
                    foreach ($anchors as $anchor) {
                        $anchorclass = $anchor->attributes->getNamedItem('class')->nodeValue;
                        if ( strpos( $anchorclass, 'fcl-course-link' ) !== false ) {
                            $anchortitle = $anchor->attributes->getNamedItem('title')->nodeValue;
                            if ( $anchortitle == $course ) {
                                $hits++;
                            }
                        }
                    }
                }
                if ( $relation == 'not' ) {
                    $this->assertEquals( 0, $hits, "$user should not see $course under $rubricmatch" );
                } else {
                    $this->assertGreaterThan( 0, $hits, "$user should see $course under $rubricmatch" );
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
    private function _sectionexpanded ( $expectations=array(), $operator='' ) {
        $this->_switchuser('user1');
        $bi = new block_filtered_course_list;
        $bi->instance = new StdClass;
        $bi->instance->id = 17;
        $html = new DOMDocument;
        $html->loadHTML( $bi->get_content()->text );
        $rubrics = $html->getElementsByTagName('div');
        foreach ($rubrics as $rubric) {
            $title = $rubric->textContent;
            if (!(array_key_exists($title, $expectations))) {
                continue;
            }
            $state = $expectations[$title];
            $class = $rubric->getAttribute('class');
            if ($operator == 'not') {
                $this->assertNotContains ( $state , $class, "The class attribute of '$title' should not contain $state.");
            } else {
                $this->assertContains ( $state , $class, "The class attribute of '$title' should contain $state.");
            }
        }
    }
}
