@assignfeedback @assignfeedback_pdf @_only_local
Feature: Teachers can generate a response to a PDF submitted by a group of students
  In order to provide feedback to the students
  As a teacher
  I need to be able to generate a PDF

  @javascript
  Scenario: Student submits a PDF, the teacher generates a response and all students in the group can then download the response
    Given the following "courses" exists:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1 | 0 | 1 |
    And the following "users" exists:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@asd.com |
      | student1 | Student | 1 | student1@asd.com |
      | student2 | Student | 2 | student2@asd.com |
    And the following "course enrolments" exists:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
      | student2 | C1 | student |
    And the following "groups" exists:
      | name | course | idnumber |
      | Group 1 | C1 | G1 |
    And I log in as "teacher1"
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Assignment" to section "1" and I fill the form with:
      | Assignment name               | Test assignment name |
      | Description                   | Submit your PDF |
      | assignsubmission_pdf_enabled  | 1 |
      | assignsubmission_file_enabled | 0 |
      | assignfeedback_pdf_enabled    | 1 |
      | Students submit in groups     | Yes |
      | Group mode                    | Separate groups |
    And I expand "Users" node
    And I follow "Groups"
    And I add "student1" user to "Group 1" group
    And I add "student2" user to "Group 1" group
    And I log out
    And I log in as "student1"
    And I follow "Course 1"
    And I follow "Test assignment name"
    When I press "Add submission"
    And I upload "mod/assign/feedback/pdf/tests/pdf_test1.pdf" file to "PDF submissions" filepicker
    And I press "Save changes"
    Then I should see "Submitted for grading"
    And I should see "Download final submission"
    And I should see "Not graded"
    And I log out
    And I log in as "student2"
    And I follow "Course 1"
    And I follow "Test assignment name"
    And I should see "Download final submission"
    And I should see "Not graded"
    And I log out
    And I log in as "teacher1"
    And I follow "Course 1"
    And I follow "Test assignment name"
    And I follow "View/grade all submissions"
    And I should see "Download final submission"
    And I click on "//img[@alt='Grade Student 2']/parent::a" "xpath_element"
    And I should see "Download final submission"
    And I should see "Annotate submission"
    And I follow "Annotate submission"
    And "div#everythingspinner" "css_element" should not exists
    And "div#everything" "css_element" should exists
    And "div#everything.hidden" "css_element" should not exists
    And I click on "input#generateresponse" "css_element"
    And I should see "Download response"
    And I should see "View response online"
    And I log out
    And I log in as "student1"
    And I follow "Course 1"
    And I follow "Test assignment name"
    And I should see "Download response"
    And I should see "View response online"
    And I log out
    And I log in as "student2"
    And I follow "Course 1"
    And I follow "Test assignment name"
    And I should see "Download response"
    And I should see "View response online"
