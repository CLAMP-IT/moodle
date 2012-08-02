<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

// Institution Name
$name = 'theme_reed/sitename';
$title = get_string('sitename','theme_reed');
$description = get_string('sitenamedesc', 'theme_reed');
$default = 'hello.';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$settings->add($setting);

// Set status of Autohide functionality
$name = 'theme_reed/autohide';
$title = get_string('autohide','theme_reed');
$description = get_string('autohidedesc', 'theme_reed');
$default = 'enable';
$choices = array(
	'enable' => get_string('enable', 'theme_reed'),
	'disable' => get_string('disable', 'theme_reed')
);
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$settings->add($setting);

// Set status of Edit Toggle functionality
$name = 'theme_reed/edittoggle';
$title = get_string('editmodetoggle','theme_reed');
$description = get_string('edittoggledesc', 'theme_reed');
$default = 'enable';
$choices = array(
	'enable' => get_string('enable', 'theme_reed'),
	'disable' => get_string('disable', 'theme_reed')
);
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$settings->add($setting);

// Logo file setting
$name = 'theme_reed/logo';
$title = get_string('logo','theme_reed');
$description = get_string('logodesc', 'theme_reed');
$default = 'reed/pix/logo/reedmoodle2.badge.2.png';
$setting = new admin_setting_configfile($name, $title, $description, $default);
$settings->add($setting);

// Banner file setting
$name = 'theme_reed/banner';
$title = get_string('banner','theme_reed');
$description = get_string('bannerdesc', 'theme_reed');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$settings->add($setting);

// Banner Height
$name = 'theme_reed/bannerheight';
$title = get_string('bannerheight','theme_reed');
$description = get_string('bannerheightdesc', 'theme_reed');
$default = 5;
$choices = array(5=>get_string('nobanner', 'theme_reed'), 55=>'50px', 105=>'100px',155=>'150px', 205=>'200px', 255=>'250px',  305=>'300px',355=>'350px');
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$settings->add($setting);

// Fullscreen Toggle
$name = 'theme_reed/screenwidth';
$title = get_string('screenwidth','theme_reed');
$description = get_string('screenwidthdesc', 'theme_reed');
$default = 97;
$choices = array(1000=>get_string('fixedwidth','theme_reed'), 97=>get_string('variablewidth','theme_reed'));
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$settings->add($setting);

// Main theme background colour setting
$name = 'theme_reed/themecolor';
$title = get_string('themecolor','theme_reed');
$description = get_string('themecolordesc', 'theme_reed');
$default = '#444444';
$previewconfig = NULL;
$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
$settings->add($setting);

// Main theme trim colour setting
$name = 'theme_reed/themetrimcolor';
$title = get_string('themetrimcolor','theme_reed');
$description = get_string('themetrimcolordesc', 'theme_reed');
$default = '#660000';
$previewconfig = NULL;
$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
$settings->add($setting);

// Menu colour setting
$name = 'theme_reed/menucolor';
$title = get_string('menucolor','theme_reed');
$description = get_string('menucolordesc', 'theme_reed');
$default = '#76777c';
$previewconfig = NULL;
$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
$settings->add($setting);

// Menu hover colour setting
$name = 'theme_reed/menuhovercolor';
$title = get_string('menuhovercolor','theme_reed');
$description = get_string('menuhovercolordesc', 'theme_reed');
$default = '#8a8a8a';
$previewconfig = NULL;
$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
$settings->add($setting);

// Menu trim colour setting
$name = 'theme_reed/menutrimcolor';
$title = get_string('menutrimcolor','theme_reed');
$description = get_string('menutrimcolordesc', 'theme_reed');
$default = '#4c4c4c';
$previewconfig = NULL;
$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
$settings->add($setting);

// Content link colour setting
$name = 'theme_reed/contentlinkcolor';
$title = get_string('contentlinkcolor','theme_reed');
$description = get_string('contentlinkcolordesc', 'theme_reed');
$default = '#006699';
$previewconfig = NULL;
$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
$settings->add($setting);

// Block link colour setting
$name = 'theme_reed/blocklinkcolor';
$title = get_string('blocklinkcolor','theme_reed');
$description = get_string('blocklinkcolordesc', 'theme_reed');
$default = '#333333';
$previewconfig = NULL;
$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
$settings->add($setting);

// Menu link colour setting
$name = 'theme_reed/menulinkcolor';
$title = get_string('menulinkcolor','theme_reed');
$description = get_string('menulinkcolordesc', 'theme_reed');
$default = '#ffffff';
$previewconfig = NULL;
$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
$settings->add($setting);

// Footer text or link
$name = 'theme_reed/footnote';
$title = get_string('footnote','theme_reed');
$description = get_string('footnotedesc', 'theme_reed');
$default = '';
$setting = new admin_setting_confightmleditor($name, $title, $description, $default);
$settings->add($setting);


// Copyright Notice
$name = 'theme_reed/copyright';
$title = get_string('copyright','theme_reed');
$description = get_string('copyrightdesc', 'theme_reed');
$default = '';
$setting = new admin_setting_confightmleditor($name, $title, $description, $default);
$settings->add($setting);

// Custom CSS file
$name = 'theme_reed/customcss';
$title = get_string('customcss','theme_reed');
$description = get_string('customcssdesc', 'theme_reed');
$setting = new admin_setting_configtextarea($name, $title, $description, '');
$settings->add($setting);

}
