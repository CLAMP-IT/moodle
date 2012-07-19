<?php

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/comment_form.php');

$id      = required_param('id', PARAM_INT);
$delete  = optional_param('delete', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);

if (! $gallery = $DB->get_record('lightboxgallery', array('id' => $id))) {
    print_error('Course module is incorrect');
}
if (! $course = $DB->get_record('course', array('id' => $gallery->course))) {
    print_error('Course is misconfigured');
}
if (! $cm = get_coursemodule_from_instance('lightboxgallery', $gallery->id, $course->id)) {
   print_error('Course Module ID was incorrect');
}

if ($delete && ! $comment = $DB->get_record('lightboxgallery_comments', array('gallery' => $gallery->id, 'id' => $delete))) {
   print_error('Invalid comment ID');
}

require_login($course->id);

$PAGE->set_url('/mod/lightboxgallery/view.php', array('id' => $id));
$PAGE->set_title($gallery->name);
$PAGE->set_heading($course->shortname);
$PAGE->set_button(update_module_button($cm->id, $course->id, get_string('modulename', 'lightboxgallery')));


$context = get_context_instance(CONTEXT_MODULE, $cm->id);

$galleryurl = $CFG->wwwroot.'/mod/lightboxgallery/view.php?id='.$cm->id;

if ($delete && has_capability('mod/lightboxgallery:edit', $context)) {
    if ($confirm && confirm_sesskey()) {
        $DB->delete_records('lightboxgallery_comments', array('id' => $comment->id));
        redirect($galleryurl);
    } else {
        echo $OUTPUT->header();
        lightboxgallery_print_comment($comment, $context);
        echo('<br />');
        notice_yesno(get_string('commentdelete', 'lightboxgallery'),
                     $CFG->wwwroot . '/mod/lightboxgallery/comment.php', $CFG->wwwroot . '/mod/lightboxgallery/view.php',
                     array('id' => $gallery->id, 'delete' => $comment->id, 'sesskey' => sesskey(), 'confirm' => 1), array('id' => $cm->id),
                     'post', 'get');
        echo $OUTPUT->footer();
        die();
    }
}

require_capability('mod/lightboxgallery:addcomment', $context);

if (! $gallery->comments) {
    print_error('Comments disabled', $galleryurl);
}

$mform = new mod_lightboxgallery_comment_form(null, $gallery);

if ($mform->is_cancelled()) {
    redirect($galleryurl);
} else if ($formadata = $mform->get_data()) {
    $newcomment = new object;
    $newcomment->gallery = $gallery->id;
    $newcomment->userid = $USER->id;
    $newcomment->comment = $formadata->comment['text'];
    $newcomment->timemodified = time();
    if ($DB->insert_record('lightboxgallery_comments', $newcomment)) {
        add_to_log($course->id, 'lightboxgallery', 'comment', 'view.php?id='.$cm->id, $gallery->id, $cm->id, $USER->id);
        redirect($galleryurl, get_string('commentadded', 'lightboxgallery'));
    } else {
        print_error('Comment creation failed');
    }
}


echo $OUTPUT->header();

$mform->display();

echo $OUTPUT->footer();

?>
