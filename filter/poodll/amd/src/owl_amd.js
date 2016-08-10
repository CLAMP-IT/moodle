/* jshint ignore:start */
define(['jquery','core/log', 'filter_poodll/owl.carousel', 'filter_poodll/jquery.flip'], function($, log, owl, jqueryflip) {

  "use strict"; // jshint ;_;

  log.debug('Filter PoodLL: owl-carousel initialising');

  return {
	  
		injectcss: function(csslink){
			var link = document.createElement("link");
			link.href = csslink;
			if(csslink.toLowerCase().lastIndexOf('.html')==csslink.length-5){
				link.rel = 'import';
			}else{
				link.type = "text/css";
				link.rel = "stylesheet";	
			}
			document.getElementsByTagName("head")[0].appendChild(link);	
		},
		
		// load and stash all our variables
		loadowl: function(opts) {
			log.debug(opts);	
			//load our css in head if required
			if(opts['CSS_INJECT']){
					this.injectcss(opts['CSS_OWL']);
					this.injectcss(opts['CSS_THEME']);
			}
			
			var owl = $("#" + opts['FLASHCARDS_ID'] + "  .owl-carousel");
			owl.owlCarousel({
				  navigation : true, // Show next and prev buttons
				  slideSpeed : 300,
				  pagination: false,
				  autoHeight: opts['AUTOHEIGHT'],
				  paginationSpeed : 400,
				  singleItem: opts['SINGLEITEM']
			});
						
			//initialise buttons
			$('#' + opts['FLASHCARDS_ID'] + ' .filter_poodll_flashcards_owl_previous').click(
				function(){
					owl.trigger('owl.prev');
					//owl.prev();
					log.debug('right');
				}
			);
			$('#' + opts['FLASHCARDS_ID'] + ' .filter_poodll_flashcards_owl_next').click(
				function(){
					owl.trigger('owl.next');
					//owl.next();
					log.debug('left');
				}
			);
			
			//init all the cards themselves with a flip animation
			$('#' + opts['FLASHCARDS_ID'] + ' .filter_poodll_flashcards_owl_onecard').flip();
			
				
		}//end of function
	}
});
/* jshint ignore:end */