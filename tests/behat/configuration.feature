@block @block_clampmail
Feature: Block configuration
  In order to communicate effectively
  As someone who can send email
  I need the ability to configure the block

  Background:
    Given the following "courses" exist:
      | fullname     | shortname | category | groupmode |
      | Test Course  | CF101     | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
      | student2 | Student   | 2        | student2@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | CF101  | editingteacher |
      | student1 | CF101  | student        |
      | student2 | CF101  | student        |

  @javascript
  Scenario: Reset system defaults
    Given I log in as "teacher1"
    And I am on "Test Course" course homepage
    And I navigate to "CLAMPMail" in current page administration
    And I follow "Configuration"
    And I follow "Restore system defaults"
    And I should see "Changes saved"
    And the following fields match these values:
      | Roles to filter by  | editingteacher,teacher,student |
      | Receive a copy      | No                             |
      | Prepend course name | None                           |
      | Group mode          | Separate groups                |

  @javascript
  Scenario: Filter roles
    Given I log in as "teacher1"
    And I am on "Test Course" course homepage
    And I navigate to "CLAMPMail" in current page administration
    And I follow "Configuration"
    And I set the following fields to these values:
      | Roles to filter by | student |
    And I press "Save changes"
    Then I should see "Changes saved"
    And I am on "Test Course" course homepage
    And I navigate to "CLAMPMail" in current page administration
    Then the "roles" select box should contain "Student"
    And the "roles" select box should not contain "Teacher"

  @javascript
  Scenario: Prepend course name
    Given I log in as "teacher1"
    And I am on "Test Course" course homepage
    And I navigate to "CLAMPMail" in current page administration
    And I follow "Configuration"
    And I set the following fields to these values:
      | Prepend course name | Short name |
    And I press "Save changes"
    And I should see "Changes saved"
    Then I should see "Short name"

  @javascript
  Scenario: Receive a copy
    Given I log in as "teacher1"
    And I am on "Test Course" course homepage
    And I navigate to "CLAMPMail" in current page administration
    And I follow "Configuration"
    And I set the following fields to these values:
      | Receive a copy | Yes |
    And I press "Save changes"
    And I should see "Changes saved"
    Then I should see "Yes"
    And I am on "Test Course" course homepage
    And I navigate to "CLAMPMail" in current page administration
    Then the field "receipt" matches value "1"

  @javascript
  Scenario: Group mode
    Given I log in as "teacher1"
    And I am on "Test Course" course homepage
    And I navigate to "CLAMPMail" in current page administration
    And I follow "Configuration"
    And I set the following fields to these values:
      | Group mode | Visible groups |
    And I press "Save changes"
    And I should see "Changes saved"
    And I should see "Visible groups"

  @javascript
  Scenario: Configure send email roles
    Given I log in as "student1"
    And I am on "Test Course" course homepage
    And "CLAMPMail" "link" should not exist in current page administration
    And I log out
    And I log in as "teacher1"
    And I am on "Test Course" course homepage
    And I navigate to "CLAMPMail" in current page administration
    And I follow "Configuration"
    And I set the following fields to these values:
      | Roles that can send email | manager,coursecreator,editingteacher,teacher,student |
    And I press "Save changes"
    And I should see "Changes saved"
    And I log out
    And I log in as "student1"
    And I am on "Test Course" course homepage
    And I navigate to "CLAMPMail" in current page administration
