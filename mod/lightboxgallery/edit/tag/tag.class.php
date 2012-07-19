<?php

class edit_tag extends edit_base {

    function __construct($gallery, $cm, $image, $tab) {
        parent::edit_base($gallery, $cm, $image, $tab, true);
    }

    function output() {
        global $CFG;

        $fs = get_file_storage();
        $stored_file = $fs->get_file($this->context->id, 'mod_lightboxgallery', 'gallery_images', '0', '/', $this->image);
        $image = new lightboxgallery_image($stored_file, $this->gallery, $this->cm);

        $manualform = '<input type="text" name="tag" /><input type="submit" value="'.get_string('add').'" />';
        $manualform = $this->enclose_in_form($manualform);

        $deleteform = '';

        if ($tags = $image->get_tags()) {
            $textlib = textlib_get_instance();
            $deleteform = '<input type="hidden" name="delete" value="1" />';
            foreach ($tags as $tag) {
                $deleteform .= '<label><input type="checkbox" name="deletetags[]" value="'.$tag->id.'" /> '.htmlentities(utf8_decode($tag->description)).'</label><br />';
            }
            $deleteform .= '<input type="submit" value="' . get_string('remove') . '" />';
            $deleteform = '<span class="tag-head"> ' . get_string('tagscurrent', 'lightboxgallery') . '</span>' . $this->enclose_in_form($deleteform);
        }

        return $manualform . $deleteform;
    }

    function process_form() {
        $tag = optional_param('tag', '', PARAM_TAG);

        $fs = get_file_storage();
        $stored_file = $fs->get_file($this->context->id, 'mod_lightboxgallery', 'gallery_images', '0', '/', $this->image);
        $image = new lightboxgallery_image($stored_file, $this->gallery, $this->cm);

        if ($tag) {
            $image->add_tag($tag);
        } else if (optional_param('delete', 0, PARAM_INT)) {
            if ($deletes = optional_param('deletetags', array(), PARAM_RAW)) {
                foreach ($deletes as $delete) {
                    $image->delete_tag(clean_param($delete, PARAM_INT));
                }
            }
        }
    }

}

?>
