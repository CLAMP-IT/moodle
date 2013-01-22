<?php
require_once('../../config.php');
require_once($CFG->dirroot . '/lib/adminlib.php');
require_once($CFG->dirroot . '/mod/turningtech/lib.php');
require_once($CFG->dirroot . '/mod/turningtech/lib/forms/turningtech_admin_search_form.php');
// verify admin user

/*
require_login(0, FALSE);
$context = get_context_instance(CONTEXT_SYSTEM);
require_capability('moodle/site:config', $context);
*/
admin_externalpage_setup('editusers');

$searchform = new turningtech_admin_search_form();
if ($data = $searchform->get_data()) {
    // TODO: get search results
    $table = turningtech_admin_search_results($data);
}

// -------- page output --------------
echo $OUTPUT->header();
echo turningtech_show_messages();
$searchform->display();
if (isset($data)) {
    if (!empty($table)) {
        echo html_writer::table($table);
    } else {
        echo "<p class='empty-search'>" . get_string('nostudentsfound', 'turningtech') . "</p>\n";
    }
}
echo $OUTPUT->footer();
?>