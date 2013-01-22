<?php
include_once('../../config.php');
require_once($CFG->dirroot . '/lib/adminlib.php');
require_once($CFG->dirroot . '/mod/turningtech/lib.php');
require_once($CFG->dirroot . '/mod/turningtech/lib/forms/turningtech_admin_purge_form.php');

admin_externalpage_setup('managemodules');

$form         = new turningtech_admin_purge_form();
$redirect_url = "{$CFG->wwwroot}/admin/settings.php?section=modsettingturningtech";

if ($form->is_cancelled()) {
    redirect($redirect_url);
} else if ($data = $form->get_data()) {
    $purged = TurningTechDeviceMap::purgeGlobal();
    if ($purged === FALSE) {
        turningtech_set_message(get_string('admincouldnotpurge', 'turningtech'));
        redirect($redirect_url);
    } else {
        turningtech_set_message(get_string('adminalldevicespurged', 'turningtech'));
        turningtech_set_message(get_string('numberdevicespurged', 'turningtech', $purged));
        redirect($redirect_url);
    }
}
// --------- page output -----------
echo $OUTPUT->header();

echo turningtech_show_messages();

$form->display();

echo $OUTPUT->footer();
?>