<?php

$get_hosts = function() {
  global $DB;
  $sql = "
    SELECT DISTINCT
      h.id,
      h.name
    FROM
      {mnet_host} h,
      {mnet_application} a
    WHERE
      h.id != :local_id AND
      h.deleted = 0 AND
      h.applicationid = a.id AND
      a.name = 'mahara'
    ORDER BY h.name";

  $local = get_config('moodle', 'mnet_localhost_id');
  try {
    return $DB->get_records_sql_menu($sql, array('local_id' => $local));
  } catch (Exception $e) {
    return array();
  }
};


$settings->add(new admin_setting_configcheckbox(
  'assignsubmission_mahara/default',
  new lang_string('default', 'assignsubmission_mahara'),
  new lang_string('default_help', 'assignsubmission_mahara'),
  0
));

$hosts = $get_hosts();

if (!empty($hosts)) {
  $name = new lang_string('site', 'assignsubmission_mahara');
  $description = new lang_string('site_help', 'assignsubmission_mahara');

  $settings->add(new admin_setting_configselect(
    'assignsubmission_mahara/host',
    $name,
    $description,
    !empty($hosts) ? key($hosts) : '',
    $hosts ?: array()
  ));
}
