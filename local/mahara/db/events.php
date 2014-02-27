<?php

$mapper = function($event) {
  return array(
    'handlerfile' => '/local/mahara/events.php',
    'handlerfunction' => array('mahara_local_events', $event),
    'schedule' => 'instant',
  );
};

$events = array('user_deleted');

$handlers = array_combine($events, array_map($mapper, $events));
