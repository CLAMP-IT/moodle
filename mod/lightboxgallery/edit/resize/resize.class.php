<?php

class edit_resize extends edit_base {

    var $strresize;
    var $strscale;
    var $resizeoptions;

    function __construct($gallery, $cm, $image, $tab) {
        parent::edit_base($gallery, $cm, $image, $tab, true);
        $this->strresize = get_string('edit_resize', 'lightboxgallery');
        $this->strscale = get_string('edit_resizescale', 'lightboxgallery');
        $this->resizeoptions = lightboxgallery_resize_options();
    }

    function output() {
        $fs = get_file_storage();
        $stored_file = $fs->get_file($this->context->id, 'mod_lightboxgallery', 'gallery_images', '0', '/', $this->image);
        $image = new lightboxgallery_image($stored_file, $this->gallery, $this->cm);

        $currentsize = sprintf('%s: %dx%d', get_string('currentsize', 'lightboxgallery'), $image->width, $image->height).'<br /><br />';

        $sizeselect = '<select name="size">';
        foreach ($this->resizeoptions as $index => $option) {
            $sizeselect .= '<option value="' . $index . '">' . $option . '</option>';
        }
        $sizeselect .= '</select>&nbsp;<input type="submit" name="button" value="' . $this->strresize . '" /><br /><br />';

        $scaleselect = '<select name="scale">'.
                       '  <option value="200">200&#37;</option>'.
                       '  <option value="150">150&#37;</option>'.
                       '  <option value="125">125&#37;</option>'.
                       '  <option value="75">75&#37;</option>'.
                       '  <option value="50">50&#37;</option>'.
                       '  <option value="25">25&#37;</option>'.
                       '</select>&nbsp;<input type="submit" name="button" value="' . $this->strscale . '" />';

        return $this->enclose_in_form($currentsize . $sizeselect . $scaleselect);        
    }

    function process_form() {
        $button = required_param('button', PARAM_TEXT);

        $fs = get_file_storage();
        $stored_file = $fs->get_file($this->context->id, 'mod_lightboxgallery', 'gallery_images', '0', '/', $this->image);
        $image = new lightboxgallery_image($stored_file, $this->gallery, $this->cm);

        switch ($button) {
            case $this->strresize:
                $size = required_param('size', PARAM_INT);
                list($width, $height) = explode('x', $this->resizeoptions[$size]);
            break;
            case $this->strscale:
                $scale = required_param('scale', PARAM_INT);
                $width = $image->width * ($scale / 100);
                $height = $image->height * ($scale / 100);
            break;
        }

        $this->image = $image->resize_image($width, $height);
    }

}

?>
