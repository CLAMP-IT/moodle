<?php

// Get settings.
$hasheading = ($PAGE->heading);
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());
$hasfooter = (empty($PAGE->layout_options['nofooter']));
$hassidepre = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-pre', $OUTPUT));
$hassidepost = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-post', $OUTPUT));
$haslogininfo = (empty($PAGE->layout_options['nologininfo']));

// Get custom region settings.
$hassearch = ($PAGE->blocks->region_has_content('search', $OUTPUT));
$hastoplinks = ($PAGE->blocks->region_has_content('toplinks', $OUTPUT));
$hashomeblock = ($PAGE->blocks->region_has_content('homeblock', $OUTPUT));
$showsidepre = ($hassidepre && !$PAGE->blocks->region_completely_docked('side-pre', $OUTPUT));
$showsidepost = ($hassidepost && !$PAGE->blocks->region_completely_docked('side-post', $OUTPUT));

// Get menu settings.
$custommenu = $OUTPUT->custom_menu();
$hascustommenu = (empty($PAGE->layout_options['nocustommenu']) && !empty($custommenu));

// Get footnote setting.
$hasfootnote = (!empty($PAGE->theme->settings->footnote));

// Get language string.
$editingmode = get_string('editingmode', 'theme_cosmic');

$bodyclasses = array();
if ($showsidepre && !$showsidepost) {
    $bodyclasses[] = 'side-pre-only';
} else if ($showsidepost && !$showsidepre) {
    $bodyclasses[] = 'side-post-only';
} else if (!$showsidepost && !$showsidepre) {
    $bodyclasses[] = 'content-only';
}
if ($hascustommenu) {
    $bodyclasses[] = 'has_custom_menu';
}

echo $OUTPUT->doctype();
?>
<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
    <title><?php echo $PAGE->title ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />

<!-- START AUTOHIDE STATUS CHECK -->
<?php
if (!empty($PAGE->theme->settings->autohide) && cosmic_allow_autohide($this->page->devicetypeinuse)) {
    $autohide = $PAGE->theme->settings->autohide;
} else {
    $autohide = 'disable';
}

if ($autohide == 'enable') {
    ?><link rel="stylesheet" type="text/css" href="<?php echo $CFG->wwwroot; ?>/theme/cosmic/style/autohide.css" />
<?php
}
?>
<!-- END AUTOHIDE STATUS CHECK -->

        <?php echo $OUTPUT->standard_head_html() ?>
</head>

<body id="<?php p($PAGE->bodyid) ?>" class="<?php p($PAGE->bodyclasses.' '.join(' ', $bodyclasses)) ?>">
<?php echo $OUTPUT->standard_top_of_body_html() ?>
<div id="page-wrapper">
    <div id="page">
<?php
if ($hasheading || $hasnavbar) {
?>
        <div id="page-header">
<?php
    if ($hasheading) {
?>
            <table style="width:100%; height:133px; margin: 0px;">
                <tr>
                    <td width="250px" style="margin:0px; padding:0px;">
                        <a class="logo" href="<?php echo $CFG->wwwroot; ?>" title="<?php print_string('home'); ?>"></a>
                    </td>
                    <td valign="bottom" style="margin:0px; padding:0px;">
                        <div class="headermenu">
<?php
        if ($haslogininfo) {
            echo $OUTPUT->login_info();
        }
        if (!empty($PAGE->layout_options['langmenu'])) {
            echo $OUTPUT->lang_menu();
        }
        echo $PAGE->headingmenu
?>
                        </div>
<?php
    }
?>
                        <!-- START CUSTOMMENU -->
                        <div id="navcontainer">
<?php
    if ($hascustommenu) {
?>
                            <div id="menuwrap">
                                <div id="custommenu" class="javascript-disabled"><?php echo $custommenu; ?></div>
                            </div>
<?php
    }
?>
<!-- END OF CUSTOMMENU -->
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
<?php
}
?>
<!-- END OF HEADER -->
    <div id="editingmode"><?php echo $editingmode ?></div>
    <div id="page-content">
        <div id="hometopblocks">
            <div id="toplinks">
<?php
if ($hastoplinks) {
?>
                <div class="region-content">
<?php
    echo $OUTPUT->blocks_for_region('toplinks')
?>
                </div>
<?php
}
?>
            </div>
            <div id="homeblock">
<?php
if ($hashomeblock) {
?>
                <div class="region-content">
<?php
    echo $OUTPUT->blocks_for_region('homeblock');
?>
                </div>
<?php
}
?>
            </div>
        </div>
        <div id="headerstrip">
            <div id="search">
<?php
if ($hassearch) {
?>
                <div class="region-content">
<?php
    echo $OUTPUT->blocks_for_region('search');
?>
                </div>
<?php
}
?>
            </div>
            <div id="sitename"><?php echo $PAGE->theme->settings->sitename; ?></div>
        </div>
        <div id="region-main-box">
            <div id="region-post-box">
                <div id="region-main-wrap">
                    <div id="region-main-pad">
                        <div id="region-main">
                            <div class="region-content">
                                <?php echo $OUTPUT->main_content() ?>
                            </div>
                        </div>
                    </div>
                </div>
<?php
if ($hassidepre) {
?>
                <div id="region-pre" class="block-region">
                    <div class="region-content">
                        <?php echo $OUTPUT->blocks_for_region('side-pre') ?>
                   </div>
                </div>
<?php
}
if ($hassidepost) {
?>
                <div id="region-post" class="block-region">
                    <div class="region-content">
                        <?php echo $OUTPUT->blocks_for_region('side-post') ?>
                    </div>
                </div>
<?php
}
?>
            </div>
        </div>
    </div>
    <!-- START OF FOOTER -->
<?php
if ($hasfooter) {
?>
    <div id="page-footer" class="clearfix">
        <div class="footer-left">
<?php
    if ($hasfootnote) {
?>
            <div id="footnote"><?php echo $PAGE->theme->settings->footnote;?></div>
<?php
    }
?>

            <a href="http://moodle.org" title="Moodle">
                <div id="logo"></div>
            </a>
        </div>
        <div class="footer-right">
            <div class="copyright"><?php echo $PAGE->theme->settings->copyright; ?></div>
            <?php echo $OUTPUT->login_info();?>
        </div>
        <?php echo $OUTPUT->standard_footer_html(); ?>
    </div>
<?php
}
?>
    <div class="clearfix"></div>
</div>
<?php echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>
