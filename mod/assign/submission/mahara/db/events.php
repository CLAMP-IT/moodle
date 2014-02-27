<?php

$mapper = function($event) {
  return array(
    'handlerfile' => '/mod/assign/submission/mahara/events.php',
    'handlerfunction' => array('assign_mahara_events', $event),
    'schedule' => 'instant',
  );
};

$events = array(
  'mahara_portfolio_deleted',
  'assign_mahara_grade_submitted',
);

$handlers = array_combine($events, array_map($mapper, $events));
