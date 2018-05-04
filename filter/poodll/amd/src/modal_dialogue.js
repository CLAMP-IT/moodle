define(['jquery','core/log','filter_poodll/dlg_devicesettings'], function($, log,dlg_setting) {
	"use strict"; // jshint ;_;

    log.debug('Modal Progress: initialising');
	
	return{
		init: function(){       
		
			
        },
		
		fetch_icon: function(controlbarid){
			
			return '<div class="setting" id="setting_'+controlbarid+'"><button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal"><i class="fa fa-cogs" aria-hidden="true"></i></button></div>';
		},	
		test: function(){
			console.log(dlg_setting.init());
		},
		fetch_dialogue_box: function(visibility){
			return '<div class="poodll_dialogue_box '+visibility+'"><div class="poodll_close_modal"><i class="fa fa-window-close" aria-hidden="true"></div></i><div class="poodll_dialogue_content"><p>Poodll Dialogue box</p></div></div>';
		}
	}
});