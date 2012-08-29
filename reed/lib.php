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

    // IE can not embed stuff properly, that is why we use iframe instead.
    // Unfortunately this tag does not validate in xhtml strict mode,
    // but in any case it is undeprecated in HTML 5 - we will use it everywhere soon!
    if ($mimetype === 'text/html' and check_browser_version('MSIE', 5)) {
        $iframe = true;
    }

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