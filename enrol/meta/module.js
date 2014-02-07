/**
 * JavaScript for course selector.
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package courseselector
 */
// Define the core_enrol namespace if it has not already been defined
M.core_enrol = M.core_enrol || {};
// Define a selectors array for against namespace
M.core_enrol.course_selectors = [];
/**
 * Retrieves an instantiated course selector or null if there isn't one by the requested name
 * @param {string} name The name of the selector to retrieve
 * @return bool
 */
M.core_enrol.get_course_selector = function (name) {
    return this.course_selectors[name] || null;
};

/**
 * Initialise a new course selector.
 *
 * @param {YUI} Y The YUI3 instance
 * @param {string} name the control name/id.
 * @param {string} courseid the courseid.
 * @param {string} lastsearch The last search that took place
 */
M.core_enrol.init_course_selector = function (Y, name, courseid, lastsearch) {
    // Creates a new course_selector object
    var course_selector = {
        courseid : courseid,
        /** This id/name used for this control in the HTML. */
        name : name,
        /** Number of seconds to delay before submitting a query request */
        querydelay : 0.5,
        /** The input element that contains the search term. */
        searchfield : Y.one('#id_'+name + '_searchtext'),
        /** The clear button. */
        clearbutton : null,
        /** The select element that contains the list. */
        listbox : Y.one('#id_'+name),
        /** Used to hold the timeout id of the timeout that waits before doing a search. */
        timeoutid : null,
        /** The last string that we searched for, so we can avoid unnecessary repeat searches. */
        lastsearch : lastsearch,
        /** Whether any options where selected last time we checked. Used by
         *  handle_selection_change to track when this status changes. */
        selectionempty : true,
        /**
         * Initialises the course selector object
         * @constructor
         */
        init : function() {
            // Hide the search button and replace it with a label.

            var searchbutton = Y.one('#id_'+this.name + '_searchbutton');
            // remove search button
            searchbutton.remove();
            var clearbutton = Y.one('#id_'+this.name + '_clearbutton');
            // remove clear button
            clearbutton.remove();
            // Hook up the event handler for when the search text changes.
            this.searchfield.on('keyup', this.handle_keyup, this);
            // Hook up the event handler for when the selection changes.
            this.listbox.on('keyup', this.handle_selection_change, this);
            this.listbox.on('click', this.handle_selection_change, this);
            this.listbox.on('change', this.handle_selection_change, this);


            this.send_query(false);
        },
        /**
         * Key up hander for the search text box.
         * @param {Y.Event} e the keyup event.
         */
        handle_keyup : function(e) {
            //  Trigger an ajax search after a delay.
            this.cancel_timeout();
            this.timeoutid = Y.later(this.querydelay*1000, e, function(obj){obj.send_query(false)}, this);

            // If enter was pressed, prevent a form submission from happening.
            if (e.keyCode == 13) {
                e.halt();
            }
        },
        /**
         * Handles when the selection has changed. If the selection has changed from
         * empty to not-empty, or vice versa, then fire the event handlers.
         */
        handle_selection_change : function() {
            var isselectionempty = this.is_selection_empty();
            if (isselectionempty !== this.selectionempty) {
                this.fire('course_selector:selectionchanged', isselectionempty);
            }
            this.selectionempty = isselectionempty;
        },

        /**
         * Click handler for the clear button..
         */
        handle_clear : function() {
            this.searchfield.set('value', '');
            //this.clearbutton.set('disabled',true);
            this.send_query(false);
        },
        /**
         * Fires off the ajax search request.
         */
        send_query : function(forceresearch) {
            // Cancel any pending timeout.
            this.cancel_timeout();

            var value = this.get_search_text();

            this.searchfield.removeClass('error');
            if (this.lastsearch == value && !forceresearch) {
                return;
            }

            Y.io(M.cfg.wwwroot + '/enrol/meta/search.php', {
                method: 'POST',
                data: 'sesskey='+M.cfg.sesskey+'&searchtext='+value+'&id='+this.courseid,
                on: {
                    success:this.handle_response,
                    failure:this.handle_failure
                },
                context:this
            });

            this.lastsearch = value;
            this.listbox.setStyle('background','url(' + M.util.image_url('i/loading', 'moodle') + ') no-repeat center center');
        },
        /**
         * Handle what happens when we get some data back from the search.
         * @param {int} requestid not used.
         * @param {object} response the list of courses that was returned.
         */
        handle_response : function(requestid, response) {
            try {
                this.listbox.setStyle('background','');
                var data = Y.JSON.parse(response.responseText);
                this.output_list(data);
            } catch (e) {
                this.handle_failure();
            }
        },
        /**
         * Handles what happens when the ajax request fails.
         */
        handle_failure : function() {
            this.listbox.setStyle('background','');
            this.searchfield.addClass('error');
            // If we are in developer debug mode, output a link to help debug the failure.
            if (M.cfg.developerdebug) {
                this.searchfield.insert(Y.Node.create('<a href="'+M.cfg.wwwroot +'/enrol/meta/search.php?sesskey='+M.cfg.sesskey+'&search='+this.get_search_text()+'&debug=1">Ajax call failed. Click here to try the search call directly.</a>'));
            }
        },
        output_list : function(data) {
            var courses = {};
            var optgroup = Y.Node.create('<optgroup></optgroup>');
            this.listbox.all('optgroup').each(function(optgroup){
                optgroup.all('option').each(function(option){
                    if (option.get('selected')) {
                        courses[option.get('value')] = {
                            id : option.get('value'),
                            name : option.get('innerText') || option.get('textContent'),
                            disabled: option.get('disabled')
                        }
                    }
                    option.remove();
                }, this);
                optgroup.remove();
            }, this);

            count = 0;
            for (var id in data.result.results.display) {
                var display = data.result.results.display[id];
                var option = Y.Node.create('<option value="'+display.courseid+'">'+display.name+'</option>');
                optgroup.append(option);
                count++;
            }
            optgroup.set('label', data.result.results.label);
            this.listbox.append(optgroup);
            this.handle_selection_change();

        },
        /**
         * Replace
         * @param {string} str
         * @param {string} search The search term
         * @return string
         */
        insert_search_into_str : function(str, search) {
            return str.replace("%%SEARCHTERM%%", search);
        },
        /**
         * Gets the search text
         * @return String the value to search for, with leading and trailing whitespace trimmed.
         */
        get_search_text : function() {
            return this.searchfield.get('value').toString().replace(/^ +| +$/, '');
        },
        /**
         * Returns true if the selection is empty (nothing is selected)
         * @return Boolean check all the options and return whether any are selected.
         */
        is_selection_empty : function() {
            var selection = false;
            this.listbox.all('option').each(function(){
                if (this.get('selected')) {
                    selection = true;
                }
            });
            return !(selection);
        },
        /**
         * Cancel the search delay timeout, if there is one.
         */
        cancel_timeout : function() {
            if (this.timeoutid) {
                clearTimeout(this.timeoutid);
                this.timeoutid = null;
            }
        }
    };
    // Augment the course selector with the EventTarget class so that we can use
    // custom events
    Y.augment(course_selector, Y.EventTarget, null, null, {});
    // Initialise the course selector
    course_selector.init();
    // Store the course selector so that it can be retrieved
    this.course_selectors[name] = course_selector;
    // Return the course selector
    return course_selector;
};
