@block @block_clampmail
Feature: Send email to groups
  In order to communicate effectively
  As a student or a teacher
  I need the ability to send email to various groups

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode | groupmodeforce |
      | Test Course Separate | CF101 | 0 | 1 | 1 |
      | Test Course Visible  | CF102 | 0 | 2 | 1 |
      | Test Course NoGroups | CF100 | 0 | 0 | 1 |
      | Test Course Separate NoGroups | CF103 | 0 | 1 | 1 |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | student1 | Student | 1 | student1@example.com |
      | student2 | Student | 2 | student2@example.com |
      | student3 | Student | 3 | student3@example.com |
      | student4 | Student | 4 | student4@example.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | CF101 | editingteacher |
      | teacher1 | CF102 | editingteacher |
      | teacher1 | CF100 | editingteacher |
      | teacher1 | CF103 | editingteacher |
      | student1 | CF101 | student |
      | student2 | CF101 | student |
      | student3 | CF101 | student |
      | student4 | CF101 | student |
      | student1 | CF102 | student |
      | student2 | CF102 | student |
      | student3 | CF102 | student |
      | student4 | CF102 | student |
      | student1 | CF100 | student |
      | student2 | CF100 | student |
      | student3 | CF100 | student |
      | student4 | CF100 | student |
      | student1 | CF103 | student |
      | student2 | CF103 | student |
      | student3 | CF103 | student |
      | student4 | CF103 | student |
    And the following "groups" exist:
      | name    | description | course | idnumber |
      | Group A | Group A     | CF101  | GROUPA   |
      | Group B | Group B     | CF101  | GROUPB   |
      | Group C | Group C     | CF102  | GROUPC   |
      | Group D | Group D     | CF102  | GROUPD   |
      | Group E | Group E     | CF100  | GROUPE   |
    And the following "group members" exist:
      | user     | group  |
      | student1 | GROUPA |
      | student2 | GROUPA |
      | student3 | GROUPB |
      | student4 | GROUPB |
      | student1 | GROUPC |
      | student2 | GROUPC |
      | student3 | GROUPD |
      | student4 | GROUPD |
      | student2 | GROUPE |

  @javascript
  Scenario: Teacher composes to a single group
    Given I log in as "teacher1"
    And I am on "Test Course Separate" course homepage
    And I navigate to "CLAMPMail" in current page administration
    And I set the following fields to these values:
      | groups | Group B |
    And I press "Add"
    And I set the following fields to these values:
      | Subject | Doom At 11 |
      | Message | Salvation At Noon |
    When I press "Send email"
    Then I should see "View history"
    And I follow "Open email"
    Then I should see "Student 3" in the "#mail_users" "css_element"
    And I should see "Student 4" in the "#mail_users" "css_element"

  @javascript
  Scenario: Student uses separate groups
    Given I log in as "teacher1"
    And I am on "Test Course Separate" course homepage
    And I navigate to "CLAMPMail" in current page administration
    And I follow "Configuration"
    And I set the following fields to these values:
      | Roles that can send email | manager,coursecreator,editingteacher,teacher,student |
    And I press "Save changes"
    And I should see "Changes saved"
    And I log out
    And I log in as "student1"
    And I am on "Test Course Separate" course homepage
    And I navigate to "CLAMPMail" in current page administration
    And I should not see "Student 3" in the "#from_users" "css_element"
    And I set the following fields to these values:
      | groups | Group A |
    And I press "Add"
    And I set the following fields to these values:
      | Subject | Doom At 11 |
      | Message | Salvation At Noon |
    When I press "Send email"
    Then I should see "View history"
    And I follow "Open email"
    Then I should see "Student 2" in the "#mail_users" "css_element"

  @javascript
  Scenario: Student uses visible groups
    Given I log in as "teacher1"
    And I am on "Test Course Visible" course homepage
    And I navigate to "CLAMPMail" in current page administration
    And I follow "Configuration"
    And I set the following fields to these values:
      | Roles that can send email | manager,coursecreator,editingteacher,teacher,student |
    And I press "Save changes"
    And I should see "Changes saved"
    And I log out
    Given I log in as "student1"
    And I am on "Test Course Visible" course homepage
    And I navigate to "CLAMPMail" in current page administration
    And I set the following fields to these values:
      | groups | Group C |
    And I press "Add"
    And I set the following fields to these values:
      | groups | Group D |
    And I press "Add"
    And I set the following fields to these values:
      | Subject | Doom At 11 |
      | Message | Salvation At Noon |
    When I press "Send email"
    Then I should see "View history"
    And I follow "Open email"
    And I should see "Student 2" in the "#mail_users" "css_element"
    And I should see "Student 3" in the "#mail_users" "css_element"

  @javascript
  Scenario: Student uses no groups
    Given I log in as "teacher1"
    And I am on "Test Course NoGroups" course homepage
    And I navigate to "CLAMPMail" in current page administration
    And I follow "Configuration"
    And I set the following fields to these values:
      | Roles that can send email | manager,coursecreator,editingteacher,teacher,student |
    And I press "Save changes"
    And I should see "Changes saved"
    And I log out
    Given I log in as "student1"
    And I am on "Test Course NoGroups" course homepage
    And I navigate to "CLAMPMail" in current page administration
    And I set the following fields to these values:
      | groups | Not in a group |
    And I press "Add"
    And I set the following fields to these values:
      | Subject | Doom At 11 |
      | Message | Salvation At Noon |
    When I press "Send email"
    Then I should see "View history"
    And I follow "Open email"
    And I should see "Student 2" in the "#mail_users" "css_element"
    And I should see "Teacher 1" in the "#mail_users" "css_element"

  @javascript
  Scenario: Teacher allows students to email all users in separate groups
    Given I log in as "teacher1"
    And I am on "Test Course Separate" course homepage
    And I navigate to "CLAMPMail" in current page administration
    And I follow "Configuration"
    And I set the following fields to these values:
      | Roles that can send email | manager,coursecreator,editingteacher,teacher,student |
    And I press "Save changes"
    And I should see "Changes saved"
    And I am on the "Test Course Separate" "permissions" page
    And I override the system permissions of "Student" role with:
      | block/clampmail:cansendtoall | Allow |
    And I log out
    And I log in as "student1"
    And I am on "Test Course Separate" course homepage
    And I navigate to "CLAMPMail" in current page administration
    And I set the following fields to these values:
      | groups | Group A,Group B |
    And I press "Add"
    And I set the following fields to these values:
      | Subject | Doom At 11 |
      | Message | Salvation At Noon |
    When I press "Send email"
    Then I should see "View history"
    And I follow "Open email"
    And I should see "Student 2" in the "#mail_users" "css_element"
    And I should see "Student 3" in the "#mail_users" "css_element"

  @javascript
  Scenario: Student emails when separate groups are set but no groups are defined
    Given I log in as "teacher1"
    And I am on "Test Course Separate NoGroups" course homepage
    And I navigate to "CLAMPMail" in current page administration
    And I follow "Configuration"
    And I set the following fields to these values:
      | Roles that can send email | manager,coursecreator,editingteacher,teacher,student |
    And I press "Save changes"
    And I should see "Changes saved"
    And I log out
    And I log in as "student1"
    And I am on "Test Course Separate NoGroups" course homepage
    And I navigate to "CLAMPMail" in current page administration
    And I press "Add all"
    And I set the following fields to these values:
      | Subject | Doom At 11 |
      | Message | Salvation At Noon |
    When I press "Send email"
    Then I should see "View history"
    And I follow "Open email"
    And I should see "Student 2" in the "#mail_users" "css_element"
    And I should see "Student 3" in the "#mail_users" "css_element"
