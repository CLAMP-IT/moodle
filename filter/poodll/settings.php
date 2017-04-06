<?php  //$Id: settings.php,v 0.0.0.1 2010/01/15 22:40:00 thomw Exp $

$settings = null;
defined('MOODLE_INTERNAL') || die;
if (is_siteadmin()) {

	//add folder in property tree for settings pages
	 $conf = get_config('filter_poodll');
     $poodll_category_name='filter_poodll_category';
	 $poodll_category = new admin_category($poodll_category_name, 'PoodLL');
     $ADMIN->add('filtersettings', $poodll_category);

     //add totally bogus page with a link to jump to poodll filter settings category
    //its hidden so it doesn't appear in nav, but will catch the link that moodle auto adds to managefilters
    $jumpcat_settings = new admin_settingpage('filtersettingpoodll',get_string('pluginname', 'filter_poodll'),'moodle/site:config',true);
    $jumpcat_items=\filter_poodll\settingstools::fetch_jumpcat_items($poodll_category_name);
    foreach ($jumpcat_items as $item) {
        $jumpcat_settings->add($item);
    }
    $ADMIN->add('filtersettings', $jumpcat_settings);

	//General Settings:
	$general_settings = new admin_settingpage('filter_poodll_general',get_string('generalsettings', 'filter_poodll'));
	$general_items = \filter_poodll\settingstools::fetch_general_items();
	foreach ($general_items as $gen_item) {
    	$general_settings->add($gen_item);
	}
	$ADMIN->add($poodll_category_name, $general_settings);
	
	///File Extension Settings:matching players/widgets with parsed file extensions
	$extension_settings = new admin_settingpage('filter_poodll_extensions',get_string('extensionsettings', 'filter_poodll'));
	$extension_items =  \filter_poodll\settingstools::fetch_extension_items($conf);
	foreach ($extension_items as $ext_item) {
    	$extension_settings->add($ext_item);
	}
	$ADMIN->add($poodll_category_name, $extension_settings);
	
	///diagnostics items
	//$diagnostics_settings = new admin_settingpage('filter_poodll_diagnostics',get_string('diagnosticssettings', 'filter_poodll'));
	
	$diagnostics_settings = new admin_externalpage('poodlldiagnostics', get_string('supportinfo', 'filter_poodll'),
          $CFG->wwwroot . '/filter/poodll/poodlldiagnostics.php' );
	
	$ADMIN->add($poodll_category_name, $diagnostics_settings);



	//Mobile app Settings 
	$mobile_settings = new admin_settingpage('filter_poodll_mobile',get_string('mobilesettings', 'filter_poodll'));
	$mobile_items =  \filter_poodll\settingstools::fetch_mobile_items($conf);
	foreach ($mobile_items as $mobile_item) {
    	$mobile_settings->add($mobile_item);
	}
	$ADMIN->add($poodll_category_name, $mobile_settings);
	
	//Widgets Settings: to show in poodll widgets 
	$widget_settings = new admin_settingpage('filter_poodll_widgets',get_string('widgetsettings', 'filter_poodll'));
	$widget_items =  \filter_poodll\settingstools::fetch_widget_items($conf);
	foreach ($widget_items as $widget_item) {
    	$widget_settings->add($widget_item);
	}
	$ADMIN->add($poodll_category_name, $widget_settings);

	//Templates Launch Page
    $templatetable_settings = new admin_settingpage('filter_poodll_templatetable',get_string('templates', 'filter_poodll'));
    $templatetable_items =  \filter_poodll\settingstools::fetch_template_table();
    foreach ($templatetable_items as $templatetable_item) {
        $templatetable_settings->add($templatetable_item);
    }
    $ADMIN->add($poodll_category_name, $templatetable_settings);

    //Original Templates
    //these are all "hidden" (4th param hidden=true) so display but don't show on nav
   // $template_category = new admin_category('filter_poodll_templatecat', get_string('templates', 'filter_poodll'),'moodle/site:config',true);
	//$ADMIN->add($poodll_category_name, $template_category);
	$template_pages =  \filter_poodll\settingstools::fetch_template_pages($conf);
	foreach ($template_pages as $template_page) {
    	//$ADMIN->add('filter_poodll_templatecat', $template_page);
        $ADMIN->add($poodll_category_name, $template_page);
	}

}