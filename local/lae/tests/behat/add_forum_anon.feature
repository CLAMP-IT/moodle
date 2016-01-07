@anon_forum @local_lae
Feature: Add an anonymous forum
  In order to create an anonymous forum
  As a teacher
  I need to add anonymous forum activities to moodle courses

  Background:
    Given the following "users" exist:
      | username | firstname  | lastname  | email                 |
      | teacher1 | Teacher    | 1         | teacher1@example.com  |
      | student1 | Student    | 1         | student1@example.com  |
      | student2 | Student    | 2         | student2@example.com  |
    And the following "courses" exist:
      | fullname | shortname  | category  |
      | Course 1 | C1         | 0         |
    And the following "course enrolments" exist:
      | user      | course  | role            |
      | teacher1  | C1      | editingteacher  |
      | student1  | C1      | student         |
      | student2  | C1      | student         |
    And I log in as "admin"
    And I am on site homepage
    And I expand "Site administration" node
    And I expand "Plugins" node
    And I expand "Activity modules" node
    And I follow "Forum"
    And I set the following fields to these values:
      | Post anonymously | 1 |
    And I press "Save changes"
    And I am on site homepage
    And I log out
    And I log in as "teacher1"
    And I follow "Course 1"
    And I turn editing mode on

  Scenario: Set anonymous forums option to always and create one
    Given I add a "Forum" to section "1" and I fill the form with:
      | Forum name | Test forum name |
      | Description | Test forum description |
      | Anonymize posts? | Yes, always |
    And I log out
    And I log in as "student1"
    And I follow "Course 1"
    And I follow "Test forum name"
    And I add a new discussion to "Test forum name" forum with:
      | Subject | Post 1 subject |
      | Message | Body 1 content |
    Then I should see "Anonymous User"
    And I follow "Post 1 subject"
    Then I should see "by Anonymous User"
    And I log out
    And I log in as "student2"
    And I am on site homepage
    And I follow "Course 1"
    And I follow "Test forum name"
    Then I should see "Anonymous User"
    And I follow "Post 1 subject"
    Then I should see "Anonymous User"

  Scenario: Set anonymous forums option to optional and create one
    Given I add a "Forum" to section "1" and I fill the form with:
      | Forum name | Test forum 2 name |
      | Description | Test forum 2 description |
      | Anonymize posts? | Yes, let the user decide |
    And I log out
    And I log in as "student1"
    And I follow "Course 1"
    And I follow "Test forum 2 name"
    And I add a new discussion to "Test forum 2 name" forum with:
      | Subject | Post 2 subject |
      | Message | Body 2 content |
      | Post anonymously | 1 |
    Then I should see "Anonymous User"
    And I log out
    And I log in as "student2"
    And I am on site homepage
    And I follow "Course 1"
    And I follow "Test forum 2 name"
    Then I should see "Anonymous User"
    And I follow "Post 2 subject"
    Then I should see "by Anonymous User"
    And I reply "Post 2 subject" post from "Test forum 2 name" forum with:
      | Subject | This is the post content |
      | Message | This is the body |
      | Post anonymously | 0 |
    Then I should see "by Student 2"
    And I should see "Anonymous User"
