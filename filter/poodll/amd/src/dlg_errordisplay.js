define(['jquery','core/log','filter_poodll/dlg_poodll'], function($, log,dialog) {
	
	 log.debug('Error display dialog: initialising');
	
	
	
	return{
		dlg: null,
		instanceprops: null,
		dlgbox: null,
		
		init: function(instanceprops){
			this.dlg=dialog.clone();	
			this.dlg.setHeader('Error');
			this.instanceprops= instanceprops;
        },
        //for making multiple instances
        clone: function(){
            return $.extend(true,{},this);
        },

		fetch_dialogue_box: function(){
			//this returns html that will be set to the DOM
			return this.dlg.fetch_dialogue_box('errors');
		},
		set_dialogue_box: function(dlgbox){
			//this is the jquery object that is the dlgbox in the DOM
			this.dlgbox = dlgbox;
			this.dlg.set_dialogue_box(dlgbox);

		},
		open: function(message){
			var self = this;
			var ip = this.instanceprops;
			var content = '<div class="filter_poodll_errordisplay">';
		
			  content += '<span>' + message + '</span>';
			  content += '</div>';
			  //set the html to the dialog and DOM
			  self.dlg.setContent(content);
			  //register events for the select boxes
			  self.registerEvents();

			  //open the dialog
			  self.dlg.open();
		},
		
		registerEvents: function(){
			//we have no events
		}
	}
	
});