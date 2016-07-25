<?php
/**
 * Returns general link or file embedding html.
 * @param string $fullurl
 * @param string $title
 * @param string $clicktoopen
 * @param string $mimetype
 * @return string html
 */
function reed_resourcelib_embed_general($fullurl, $title, $clicktoopen, $mimetype) {
    global $CFG, $PAGE;

    if ($fullurl instanceof moodle_url) {
        $fullurl = $fullurl->out();
    }

    $iframe = false;

    $param = '<param name="src" value="'.$fullurl.'" />';

    if ($iframe) {
        $code = <<<EOT
<div class="resourcecontent resourcegeneral">
  <iframe id="resourceobject" src="$fullurl">
    $clicktoopen
  </iframe>
</div>
EOT;
    } else {
        $code = <<<EOT
<div class="resourcecontent resourcegeneral">
  <object id="resourceobject" data="$fullurl" type="$mimetype"  width="800" height="100">
    $param
    $clicktoopen
  </object>
</div>
EOT;
    }

    return $code;
}
?>