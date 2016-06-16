/********
 * TurningTechnologies functions
 * @author jacob
 */
YUI().use("yui2-dom","yui2-event", function(Y) {
  var YAHOO = Y.YUI2;
var TurningTech = function() {
 
  // shortcuts for YUI libs 
  this.dom = YAHOO.util.Dom;
  this.event = YAHOO.util.Event;
  
  // main setup function
  this.setup = function() {
    this.hideElements();
    this.setEventhandlers();
  };
  
  // hide responsecard form (if necessary) and responsecard form (always)
  this.hideElements = function() {
    if(!leaveOpen) {
      this.dom.replaceClass(
        this.dom.getElementsByClassName('uncollapsed',null,'responsecard-collapse-group'),
        'uncollapsed',
        'collapsed'
      );
    }
    
    this.dom.replaceClass(
      this.dom.getElementsByClassName('uncollapsed', null, 'responseware-collapse-group'),
      'uncollapsed',
      'collapsed'
    );
    
    if(leaveResCardFrmOpen)
    {
    	this.toggleResponseCard();
    }
    
    if(leaveResWareFrmOpen)
    {
    	this.toggleResponseWare();
    }
  };
  
  // set click events for links
  this.setEventhandlers = function() {
    var l = this.dom.getElementsByClassName('rw-image-container');
    // set links at top of page so they only show the form, never hide it
    this.event.addListener(
      this.dom.getElementsByClassName('responsecard-form-link','a',l[0]),
      'click',
      function() { TurnTech.toggleResponseCard(); }
    );
    this.event.addListener(
      this.dom.getElementsByClassName('responseware-form-link','a',l[0]),
      'click',
      function() { TurnTech.toggleResponseWare(); }
    );
    
    // set links at the bottom to toggle
    l = this.dom.getElementsByClassName('form-container');
    this.event.addListener(
      this.dom.getElementsByClassName('responsecard-form-link','a', l[0]),
      'click',
      function() { TurnTech.toggleResponseCard(); }
    );
    
    this.event.addListener(
      this.dom.getElementsByClassName('responseware-form-link','a', l[0]),
      'click',
      function() { TurnTech.toggleResponseWare(); }
    );
    
  };
  
  // show the responsecard form
  this.showResponseCard = function() {
    this.showElements(this.dom.get('responsecard-collapse-group'));
  };
  
  // show the responseware form
  this.showResponseWare = function() {
    this.showElements(this.dom.get('responseware-collapse-group'));
  };
  
  // helper function for showing elements
  this.showElements = function(node) {
    var list = this.dom.getElementsByClassName('collapsed',null,node);
    alert(list);
    if(list.length > 0) {
      this.dom.replaceClass(list, 'collapsed', 'uncollapsed');
    }
  }
  
  // toggles responsecard form
  this.toggleResponseCard = function() {
    this.toggleElements(this.dom.get('responsecard-collapse-group'));
  };

  // toggles responseware form
  this.toggleResponseWare = function() {

    this.toggleElements(this.dom.get('responseware-collapse-group'));
  };
  
  // helper function for toggling elements
  this.toggleElements = function(node) {
    var list = this.dom.getElementsByClassName('uncollapsed',null,node);
          if(list.length > 0) {
      this.dom.replaceClass(list, 'uncollapsed','collapsed');
    }
    else {
      list = this.dom.getElementsByClassName('collapsed',null,node);
      this.dom.replaceClass(list, 'collapsed','uncollapsed');
    }
  };
  
}

var TurnTech = new TurningTech();

if(YAHOO.util.Event)
{
    YAHOO.util.Event.onDOMReady(function() { TurnTech.setup(); });
}

});