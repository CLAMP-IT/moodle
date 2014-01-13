/* Modified version of the code by David Walsh found at:
   http://davidwalsh.name/mootools-context-menu
   Now converted to work with YUI3 instead
*/
/*global Y*/
function ContextMenu(options) {
    "use strict";
    var ret = {
        menu: null,
        targets: [],

        //options
        options: {
            actions: {},
            menu: '#contextmenu',
            stopEvent: true,
            targets: 'body',
            trigger: 'contextmenu',
            offsets: { x: 0, y: 0 }
        },

        //initialization
        initialize: function (options) {
            var targ;

            //set options
            this.setOptions(options);

            //option diffs menu
            this.menu = Y.one(this.options.menu);
            this.targets = [];
            if (typeof this.options.targets === 'object') {
                for (targ in this.options.targets) {
                    if (this.options.targets.hasOwnProperty(targ)) {
                        this.targets.push(Y.one(this.options.targets[targ]));
                    }
                }
            } else {
                this.targets.push(Y.one(this.options.targets));
            }

            //hide and begin the listener
            this.hide().startListener();

            //hide the menu
            this.menu.setStyles({ position: 'absolute', top: '-900000px', display: 'block' });
        },

        setOptions: function (options) {
            var opt;
            for (opt in options) {
                if (options.hasOwnProperty(opt)) {
                    this.options[opt] = options[opt];
                }
            }
        },

        //get things started
        startListener: function () {
            var targ;
            /* all elements */
            for (targ in this.targets) {
                if (this.targets.hasOwnProperty(targ)) {
                    this.addMenu(this.targets[targ]);
                }
            }

            /* menu items */
            this.menu.all('a').each(function (item) {
                item.on('click', function (e) {
                    if (!item.hasClass('disabled')) {
                        this.execute(item.get('href').split('#')[1], this.options.element);
                    }
                }, this);
            }, this);

            //hide on body click
            Y.one('body').on('click', function () {
                this.hide();
            }, this);
        },

        addmenu: function (target) {
            /* show the menu */
            target.on(this.options.trigger, function (e) {
                //enabled?
                if (!this.options.disabled) {
                    var offx, offy;
                    //prevent default, if told to
                    if (this.options.stopEvent) { e.preventDefault(); e.stopPropagation(); }
                    //record this as the trigger
                    this.options.element = e.currentTarget;
                    //position the menu
                    this.menu.setStyles({
                        position: 'absolute',
                        zIndex: '2000'
                    });
                    offx = this.options.offsets.x;
                    offy = this.options.offsets.y;
                    // Nasty hack to fix positioning problem in IE <= 7 (IE 8 seems fine)
                    if (Y.UA.ie === 6 || Y.UA.ie === 7) {
                        offx -= 10;
                    }
                    this.menu.setStyles({left: (e.pageX + offx) + 'px', top: (e.pageY + offy) + 'px' });
                    //show the menu
                    this.show();
                }
            }, this);
        },

        //show menu
        show: function () {
            this.shown = true;
            return this;
        },

        //hide the menu
        hide: function () {
            if (this.shown) {
                this.menu.setStyles({ top: '-900000px' });
                this.shown = false;
            }
            return this;
        },

        //disable an item
        disableItem: function (item) {
            this.menu.all('a').each(function (el) {
                if (el.get('href').split('#')[1] === item) {
                    el.addClass('disabled');
                }
            });
            return this;
        },

        //enable an item
        enableItem: function (item) {
            this.menu.all('a').each(function (el) {
                if (el.get('href').split('#')[1] === item) {
                    el.removeClass('disabled');
                }
            });
            return this;
        },

        addItem: function (item, text, deleteicon, func, titletext) {
            var newel, link, dellink, delico;

            newel = Y.Node.create('<li></li>');
            link = Y.Node.create('<a></a>');
            link.setContent(text);
            link.set('href', '#' + item);
            if (titletext) {
                link.set('title', titletext);
            }
            newel.appendChild(link);
            if (deleteicon) {
                dellink = Y.Node.create('<a></a>');
                delico = Y.Node.create('<img />');
                delico.set('src', deleteicon);
                dellink.set('href', '#del' + item);
                dellink.addClass('delete');
                dellink.appendChild(delico);
                dellink.on('click', function (e) {
                    this.execute('removeitem', item);
                }, this);
                newel.appendChild(dellink);
            }

            newel.set('id', this.options.menu.split('#')[1] + item);
            this.menu.appendChild(newel);
            link.on('click', function (e) {
                if (!link.hasClass('disabled')) {
                    func(item, this);
                }
            }, this);

            return this;
        },

        removeItem: function (item) {
            var itemid, el;
            itemid = this.options.menu + item;
            el = Y.one(itemid);
            if (el) {  el.remove(true); }
            return this;
        },

        //diable the entire menu
        disable: function () {
            this.options.disabled = true;
            return this;
        },

        //enable the entire menu
        enable: function () {
            this.options.disabled = false;
            return this;
        },

        //execute an action
        execute: function (action, element) {
            if (this.options.actions[action]) {
                this.options.actions[action](element, this);
            }
            return this;
        }
    };

    ret.initialize(options);
    return ret;
}
