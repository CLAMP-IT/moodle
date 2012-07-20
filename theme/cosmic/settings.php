<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

// Institution Name
$name = 'theme_cosmic/sitename';
$title = get_string('sitename','theme_cosmic');
$description = get_string('sitenamedesc', 'theme_cosmic');
$default = 'Welcome to my site';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$settings->add($setting);

// Set status of Autohide functionality
$name = 'theme_cosmic/autohide';
$title = get_string('autohide','theme_cosmic');
$description = get_string('autohidedesc', 'theme_cosmic');
$default = 'enable';
$choices = array(
	'enable' => get_string('enable', 'theme_cosmic'),
	'disable' => get_string('disable', 'theme_cosmic')
);
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$settings->add($setting);

// Set status of Edit Toggle functionality
$name = 'theme_cosmic/edittoggle';
$title = get_string('editmodetoggle','theme_cosmic');
$description = get_string('edittoggledesc', 'theme_cosmic');
$default = 'enable';
$choices = array(
	'enable' => get_string('enable', 'theme_cosmic'),
	'disable' => get_string('disable', 'theme_cosmic')
);
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$settings->add($setting);

// Logo file setting
$name = 'theme_cosmic/logo';
$title = get_string('logo','theme_cosmic');
$description = get_string('logodesc', 'theme_cosmic');
$default = 'cosmic/pix/logo/lafayette-logo.png';
$setting = new admin_setting_configfile($name, $title, $description, $default);
$settings->add($setting);

// Banner file setting
$name = 'theme_cosmic/banner';
$title = get_string('banner','theme_cosmic');
$description = get_string('bannerdesc', 'theme_cosmic');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$settings->add($setting);

// Banner Height
$name = 'theme_cosmic/bannerheight';
$title = get_string('bannerheight','theme_cosmic');
$description = get_string('bannerheightdesc', 'theme_cosmic');
$default = 255;
$choices = array(5=>get_string('nobanner', 'theme_cosmic'), 55=>'50px', 105=>'100px',155=>'150px', 205=>'200px', 255=>'250px',  305=>'300px',355=>'350px');
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$settings->add($setting);

// Fullscreen Toggle
$name = 'theme_cosmic/screenwidth';
$title = get_string('screenwidth','theme_cosmic');
$description = get_string('screenwidthdesc', 'theme_cosmic');
$default = 1000;
$choices = array(1000=>get_string('fixedwidth','theme_cosmic'), 97=>get_string('variablewidth','theme_cosmic'));
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$settings->add($setting);

// Main theme background colour setting
$name = 'theme_cosmic/themecolor';
$title = get_string('themecolor','theme_cosmic');
$description = get_string('themecolordesc', 'theme_cosmic');
$default = '#a8213a';
$previewconfig = NULL;
$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
$settings->add($setting);

// Main theme trim colour setting
$name = 'theme_cosmic/themetrimcolor';
$title = get_string('themetrimcolor','theme_cosmic');
$description = get_string('themetrimcolordesc', 'theme_cosmic');
$default = '#660000';
$previewconfig = NULL;
$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
$settings->add($setting);

// Menu colour setting
$name = 'theme_cosmic/menucolor';
$title = get_string('menucolor','theme_cosmic');
$description = get_string('menucolordesc', 'theme_cosmic');
$default = '#76777c';
$previewconfig = NULL;
$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
$settings->add($setting);

// Menu hover colour setting
$name = 'theme_cosmic/menuhovercolor';
$title = get_string('menuhovercolor','theme_cosmic');
$description = get_string('menuhovercolordesc', 'theme_cosmic');
$default = '#8a8a8a';
$previewconfig = NULL;
$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
$settings->add($setting);

// Menu trim colour setting
$name = 'theme_cosmic/menutrimcolor';
$title = get_string('menutrimcolor','theme_cosmic');
$description = get_string('menutrimcolordesc', 'theme_cosmic');
$default = '#4c4c4c';
$previewconfig = NULL;
$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
$settings->add($setting);

// Content link colour setting
$name = 'theme_cosmic/contentlinkcolor';
$title = get_string('contentlinkcolor','theme_cosmic');
$description = get_string('contentlinkcolordesc', 'theme_cosmic');
$default = '#006699';
$previewconfig = NULL;
$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
$settings->add($setting);

// Block link colour setting
$name = 'theme_cosmic/blocklinkcolor';
$title = get_string('blocklinkcolor','theme_cosmic');
$description = get_string('blocklinkcolordesc', 'theme_cosmic');
$default = '#333333';
$previewconfig = NULL;
$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
$settings->add($setting);

// Menu link colour setting
$name = 'theme_cosmic/menulinkcolor';
$title = get_string('menulinkcolor','theme_cosmic');
$description = get_string('menulinkcolordesc', 'theme_cosmic');
$default = '#ffffff';
$previewconfig = NULL;
$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
$settings->add($setting);

// Footer text or link
$name = 'theme_cosmic/footnote';
$title = get_string('footnote','theme_cosmic');
$description = get_string('footnotedesc', 'theme_cosmic');
$default = '';
$setting = new admin_setting_confightmleditor($name, $title, $description, $default);
$settings->add($setting);


// Copyright Notice
$name = 'theme_cosmic/copyright';
$title = get_string('copyright','theme_cosmic');
$description = get_string('copyrightdesc', 'theme_cosmic');
$default = '';
$setting = new admin_setting_confightmleditor($name, $title, $description, $default);
$settings->add($setting);

// Custom CSS file
$name = 'theme_cosmic/customcss';
$title = get_string('customcss','theme_cosmic');
$description = get_string('customcssdesc', 'theme_cosmic');
$setting = new admin_setting_configtextarea($name, $title, $description, '');
$settings->add($setting);

}
