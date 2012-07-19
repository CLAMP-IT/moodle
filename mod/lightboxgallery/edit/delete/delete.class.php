<?php

class edit_delete extends edit_base {

    function edit_delete($gallery, $cm, $image, $tab) {
        parent::edit_base($gallery, $cm, $image, $tab, true);
    }

    function output() {
        global $page;
        $result = get_string('deletecheck', '', $this->image).'<br /><br />';
        $result .= '<input type="hidden" name="page" value="'.$page.'" />';
        $result .= '<input type="submit" value="'.get_string('yes').'" />';
        return $this->enclose_in_form($result);
    }

    function process_form() {
        global $CFG, $DB, $page;
        $fs = get_file_storage();
        $stored_file = $fs->get_file($this->context->id, 'mod_lightboxgallery', 'gallery_images', '0', '/', $this->image);
        $stored_file->delete();
        redirect($CFG->wwwroot.'/mod/lightboxgallery/view.php?id='.$this->cm->id.'&page='.$page.'&editing=1');
    }

}

?>
