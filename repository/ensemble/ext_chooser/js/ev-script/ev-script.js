/**
 * ev-script 0.3.0 2014-05-07
 * Ensemble Video Integration Library
 * https://github.com/jmpease/ev-script
 * Copyright (c) 2014 Symphony Video, Inc.
 * Licensed MIT, GPL-2.0
 */
(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD.  Put jQuery plugins at the end since they don't return any values
        // that are passed to our factory.
        define(['jquery', 'underscore', 'backbone', 'jquery-ui', 'jquery.plupload.queue', 'jquery.cookie', 'plupload'], factory);
    } else {
        // Browser globals
        root.EV = factory(root.$, root._, root.Backbone);
    }
}(this, function ($, _, Backbone) {

/**
 * almond 0.2.5 Copyright (c) 2011-2012, The Dojo Foundation All Rights Reserved.
 * Available via the MIT or new BSD license.
 * see: http://github.com/jrburke/almond for details
 */
//Going sloppy to avoid 'use strict' string cost, but strict practices should
//be followed.
/*jslint sloppy: true */
/*global setTimeout: false */

var requirejs, require, define;
(function (undef) {
    var main, req, makeMap, handlers,
        defined = {},
        waiting = {},
        config = {},
        defining = {},
        hasOwn = Object.prototype.hasOwnProperty,
        aps = [].slice;

    function hasProp(obj, prop) {
        return hasOwn.call(obj, prop);
    }

    /**
     * Given a relative module name, like ./something, normalize it to
     * a real name that can be mapped to a path.
     * @param {String} name the relative name
     * @param {String} baseName a real name that the name arg is relative
     * to.
     * @returns {String} normalized name
     */
    function normalize(name, baseName) {
        var nameParts, nameSegment, mapValue, foundMap,
            foundI, foundStarMap, starI, i, j, part,
            baseParts = baseName && baseName.split("/"),
            map = config.map,
            starMap = (map && map['*']) || {};

        //Adjust any relative paths.
        if (name && name.charAt(0) === ".") {
            //If have a base name, try to normalize against it,
            //otherwise, assume it is a top-level require that will
            //be relative to baseUrl in the end.
            if (baseName) {
                //Convert baseName to array, and lop off the last part,
                //so that . matches that "directory" and not name of the baseName's
                //module. For instance, baseName of "one/two/three", maps to
                //"one/two/three.js", but we want the directory, "one/two" for
                //this normalization.
                baseParts = baseParts.slice(0, baseParts.length - 1);

                name = baseParts.concat(name.split("/"));

                //start trimDots
                for (i = 0; i < name.length; i += 1) {
                    part = name[i];
                    if (part === ".") {
                        name.splice(i, 1);
                        i -= 1;
                    } else if (part === "..") {
                        if (i === 1 && (name[2] === '..' || name[0] === '..')) {
                            //End of the line. Keep at least one non-dot
                            //path segment at the front so it can be mapped
                            //correctly to disk. Otherwise, there is likely
                            //no path mapping for a path starting with '..'.
                            //This can still fail, but catches the most reasonable
                            //uses of ..
                            break;
                        } else if (i > 0) {
                            name.splice(i - 1, 2);
                            i -= 2;
                        }
                    }
                }
                //end trimDots

                name = name.join("/");
            } else if (name.indexOf('./') === 0) {
                // No baseName, so this is ID is resolved relative
                // to baseUrl, pull off the leading dot.
                name = name.substring(2);
            }
        }

        //Apply map config if available.
        if ((baseParts || starMap) && map) {
            nameParts = name.split('/');

            for (i = nameParts.length; i > 0; i -= 1) {
                nameSegment = nameParts.slice(0, i).join("/");

                if (baseParts) {
                    //Find the longest baseName segment match in the config.
                    //So, do joins on the biggest to smallest lengths of baseParts.
                    for (j = baseParts.length; j > 0; j -= 1) {
                        mapValue = map[baseParts.slice(0, j).join('/')];

                        //baseName segment has  config, find if it has one for
                        //this name.
                        if (mapValue) {
                            mapValue = mapValue[nameSegment];
                            if (mapValue) {
                                //Match, update name to the new value.
                                foundMap = mapValue;
                                foundI = i;
                                break;
                            }
                        }
                    }
                }

                if (foundMap) {
                    break;
                }

                //Check for a star map match, but just hold on to it,
                //if there is a shorter segment match later in a matching
                //config, then favor over this star map.
                if (!foundStarMap && starMap && starMap[nameSegment]) {
                    foundStarMap = starMap[nameSegment];
                    starI = i;
                }
            }

            if (!foundMap && foundStarMap) {
                foundMap = foundStarMap;
                foundI = starI;
            }

            if (foundMap) {
                nameParts.splice(0, foundI, foundMap);
                name = nameParts.join('/');
            }
        }

        return name;
    }

    function makeRequire(relName, forceSync) {
        return function () {
            //A version of a require function that passes a moduleName
            //value for items that may need to
            //look up paths relative to the moduleName
            return req.apply(undef, aps.call(arguments, 0).concat([relName, forceSync]));
        };
    }

    function makeNormalize(relName) {
        return function (name) {
            return normalize(name, relName);
        };
    }

    function makeLoad(depName) {
        return function (value) {
            defined[depName] = value;
        };
    }

    function callDep(name) {
        if (hasProp(waiting, name)) {
            var args = waiting[name];
            delete waiting[name];
            defining[name] = true;
            main.apply(undef, args);
        }

        if (!hasProp(defined, name) && !hasProp(defining, name)) {
            throw new Error('No ' + name);
        }
        return defined[name];
    }

    //Turns a plugin!resource to [plugin, resource]
    //with the plugin being undefined if the name
    //did not have a plugin prefix.
    function splitPrefix(name) {
        var prefix,
            index = name ? name.indexOf('!') : -1;
        if (index > -1) {
            prefix = name.substring(0, index);
            name = name.substring(index + 1, name.length);
        }
        return [prefix, name];
    }

    /**
     * Makes a name map, normalizing the name, and using a plugin
     * for normalization if necessary. Grabs a ref to plugin
     * too, as an optimization.
     */
    makeMap = function (name, relName) {
        var plugin,
            parts = splitPrefix(name),
            prefix = parts[0];

        name = parts[1];

        if (prefix) {
            prefix = normalize(prefix, relName);
            plugin = callDep(prefix);
        }

        //Normalize according
        if (prefix) {
            if (plugin && plugin.normalize) {
                name = plugin.normalize(name, makeNormalize(relName));
            } else {
                name = normalize(name, relName);
            }
        } else {
            name = normalize(name, relName);
            parts = splitPrefix(name);
            prefix = parts[0];
            name = parts[1];
            if (prefix) {
                plugin = callDep(prefix);
            }
        }

        //Using ridiculous property names for space reasons
        return {
            f: prefix ? prefix + '!' + name : name, //fullName
            n: name,
            pr: prefix,
            p: plugin
        };
    };

    function makeConfig(name) {
        return function () {
            return (config && config.config && config.config[name]) || {};
        };
    }

    handlers = {
        require: function (name) {
            return makeRequire(name);
        },
        exports: function (name) {
            var e = defined[name];
            if (typeof e !== 'undefined') {
                return e;
            } else {
                return (defined[name] = {});
            }
        },
        module: function (name) {
            return {
                id: name,
                uri: '',
                exports: defined[name],
                config: makeConfig(name)
            };
        }
    };

    main = function (name, deps, callback, relName) {
        var cjsModule, depName, ret, map, i,
            args = [],
            usingExports;

        //Use name if no relName
        relName = relName || name;

        //Call the callback to define the module, if necessary.
        if (typeof callback === 'function') {

            //Pull out the defined dependencies and pass the ordered
            //values to the callback.
            //Default to [require, exports, module] if no deps
            deps = !deps.length && callback.length ? ['require', 'exports', 'module'] : deps;
            for (i = 0; i < deps.length; i += 1) {
                map = makeMap(deps[i], relName);
                depName = map.f;

                //Fast path CommonJS standard dependencies.
                if (depName === "require") {
                    args[i] = handlers.require(name);
                } else if (depName === "exports") {
                    //CommonJS module spec 1.1
                    args[i] = handlers.exports(name);
                    usingExports = true;
                } else if (depName === "module") {
                    //CommonJS module spec 1.1
                    cjsModule = args[i] = handlers.module(name);
                } else if (hasProp(defined, depName) ||
                           hasProp(waiting, depName) ||
                           hasProp(defining, depName)) {
                    args[i] = callDep(depName);
                } else if (map.p) {
                    map.p.load(map.n, makeRequire(relName, true), makeLoad(depName), {});
                    args[i] = defined[depName];
                } else {
                    throw new Error(name + ' missing ' + depName);
                }
            }

            ret = callback.apply(defined[name], args);

            if (name) {
                //If setting exports via "module" is in play,
                //favor that over return value and exports. After that,
                //favor a non-undefined return value over exports use.
                if (cjsModule && cjsModule.exports !== undef &&
                        cjsModule.exports !== defined[name]) {
                    defined[name] = cjsModule.exports;
                } else if (ret !== undef || !usingExports) {
                    //Use the return value from the function.
                    defined[name] = ret;
                }
            }
        } else if (name) {
            //May just be an object definition for the module. Only
            //worry about defining if have a module name.
            defined[name] = callback;
        }
    };

    requirejs = require = req = function (deps, callback, relName, forceSync, alt) {
        if (typeof deps === "string") {
            if (handlers[deps]) {
                //callback in this case is really relName
                return handlers[deps](callback);
            }
            //Just return the module wanted. In this scenario, the
            //deps arg is the module name, and second arg (if passed)
            //is just the relName.
            //Normalize module name, if it contains . or ..
            return callDep(makeMap(deps, callback).f);
        } else if (!deps.splice) {
            //deps is a config object, not an array.
            config = deps;
            if (callback.splice) {
                //callback is an array, which means it is a dependency list.
                //Adjust args if there are dependencies
                deps = callback;
                callback = relName;
                relName = null;
            } else {
                deps = undef;
            }
        }

        //Support require(['a'])
        callback = callback || function () {};

        //If relName is a function, it is an errback handler,
        //so remove it.
        if (typeof relName === 'function') {
            relName = forceSync;
            forceSync = alt;
        }

        //Simulate async callback;
        if (forceSync) {
            main(undef, deps, callback, relName);
        } else {
            //Using a non-zero value because of concern for what old browsers
            //do, and latest browsers "upgrade" to 4 if lower value is used:
            //http://www.whatwg.org/specs/web-apps/current-work/multipage/timers.html#dom-windowtimers-settimeout:
            //If want a value immediately, use require('id') instead -- something
            //that works in almond on the global level, but not guaranteed and
            //unlikely to work in other AMD implementations.
            setTimeout(function () {
                main(undef, deps, callback, relName);
            }, 4);
        }

        return req;
    };

    /**
     * Just drops the config on the floor, but returns req in case
     * the config return value is used.
     */
    req.config = function (cfg) {
        config = cfg;
        if (config.deps) {
            req(config.deps, config.callback);
        }
        return req;
    };

    define = function (name, deps, callback) {

        //This module may not have dependencies
        if (!deps.splice) {
            //deps is not an array, so probably means
            //an object literal or factory function for
            //the value. Adjust args.
            callback = deps;
            deps = [];
        }

        if (!hasProp(defined, name) && !hasProp(waiting, name)) {
            waiting[name] = [name, deps, callback];
        }
    };

    define.amd = {
        jQuery: true
    };
}());

define("almond", function(){});

define('ev-script/models/video-settings',['backbone'], function(Backbone) {

    

    return Backbone.Model.extend({
        defaults: {
            type: 'video',
            showtitle: false,
            autoplay: false,
            showcaptions: false,
            hidecontrols: false,
            search: '',
            sourceId: 'content'
        }
    });
});

define('ev-script/models/playlist-settings',['backbone'], function(Backbone) {

    

    return Backbone.Model.extend({
        defaults: {
            type: 'playlist',
            search: '',
        }
    });
});

define('ev-script/util/events',['require','underscore','backbone'],function(require) {

    

    var events = [],
        _ = require('underscore'),
        Backbone = require('backbone');

    events['global'] = _.extend({}, Backbone.Events);

    return {
        initEvents: function(index) {
            return events[index] = _.extend({}, Backbone.Events);
        },
        getEvents: function(index) {
            var es;
            if (!index) {
                es = events['global'];
            } else {
                es = events[index];
            }
            return es;
        }
    };

});

define('ev-script/util/cache',['require','jquery','underscore','backbone'],function(require) {

    

    var $ = require('jquery'),
        _ = require('underscore'),
        Backbone = require('backbone');

    var Cache = function() {
        this.cache = [];
        this.get = function(index) {
            return index ? this.cache[index] : null;
        };
        this.set = function(index, value) {
            return index ? this.cache[index] = value : null;
        };
        return this;
    };

    var caches = new Cache();

    var _getAppCache = function(appId) {
        var appCache = caches.get(appId);
        if (!appCache) {
            appCache = caches.set(appId, new Cache());
        }
        return appCache;
    };

    // Convenience method to initialize a cache for app-specific configuration
    var setAppConfig = function(appId, config) {
        return _getAppCache(appId).set('config', config);
    };

    var getAppConfig = function(appId) {
        return _getAppCache(appId).get('config');
    };

    // Convenience method to initialize a cache for app-specific authentication
    var setAppAuth = function(appId, auth) {
        return _getAppCache(appId).set('auth', auth);
    };

    var getAppAuth = function(appId) {
        return _getAppCache(appId).get('auth');
    };

    // Convenience method to initialize a cache for upstream application info
    var setAppInfo = function(appId, info) {
        return _getAppCache(appId).set('info', info);
    };

    var getAppInfo = function(appId) {
        return _getAppCache(appId).get('info');
    };

    var getUserCache = function(ensembleUrl, user) {
        var appCache = caches.get(ensembleUrl);
        if (!appCache) {
            appCache = caches.set(ensembleUrl, new Cache());
        }
        var userCache = appCache.get(user);
        if (!userCache) {
            userCache = appCache.set(user, new Cache());
        }
        return userCache;
    };

    return {
        Cache: Cache,
        caches: caches,
        setAppConfig: setAppConfig,
        getAppConfig: getAppConfig,
        setAppAuth: setAppAuth,
        getAppAuth: getAppAuth,
        setAppInfo: setAppInfo,
        getAppInfo: getAppInfo,
        getUserCache: getUserCache
    };

});

define('ev-script/views/base',['require','jquery','underscore','backbone','ev-script/util/events','ev-script/util/cache'],function(require) {

    

    var $ = require('jquery'),
        _ = require('underscore'),
        Backbone = require('backbone'),
        root = this,
        eventsUtil = require('ev-script/util/events'),
        cacheUtil = require('ev-script/util/cache');

    return Backbone.View.extend({
        initialize: function(options) {
            this.appId = options.appId;
            this.config = cacheUtil.getAppConfig(this.appId);
            this.auth = cacheUtil.getAppAuth(this.appId);
            this.info = cacheUtil.getAppInfo(this.appId);
            this.appEvents = eventsUtil.getEvents(this.appId);
            this.globalEvents = eventsUtil.getEvents('global');
        },
        ajaxError: function(xhr, authCallback) {
            if (xhr.status === 401) {
                this.auth.handleUnauthorized(this.el, authCallback);
            } else if (xhr.status === 500) {
                // Making an assumption that root is window here...
                root.alert('It appears there is an issue with the Ensemble Video installation.');
            } else if (xhr.status === 404) {
                root.alert('Could not find requested resource.  This is likely a problem with the configured Ensemble Video base url.');
            } else if (xhr.status !== 0) {
                root.alert('An unexpected error occurred.  Check the server log for more details.');
            }
        }
    });

});

/**
 * @license RequireJS text 2.0.10 Copyright (c) 2010-2012, The Dojo Foundation All Rights Reserved.
 * Available via the MIT or new BSD license.
 * see: http://github.com/requirejs/text for details
 */
/*jslint regexp: true */
/*global require, XMLHttpRequest, ActiveXObject,
  define, window, process, Packages,
  java, location, Components, FileUtils */

define('text',['module'], function (module) {
    

    var text, fs, Cc, Ci, xpcIsWindows,
        progIds = ['Msxml2.XMLHTTP', 'Microsoft.XMLHTTP', 'Msxml2.XMLHTTP.4.0'],
        xmlRegExp = /^\s*<\?xml(\s)+version=[\'\"](\d)*.(\d)*[\'\"](\s)*\?>/im,
        bodyRegExp = /<body[^>]*>\s*([\s\S]+)\s*<\/body>/im,
        hasLocation = typeof location !== 'undefined' && location.href,
        defaultProtocol = hasLocation && location.protocol && location.protocol.replace(/\:/, ''),
        defaultHostName = hasLocation && location.hostname,
        defaultPort = hasLocation && (location.port || undefined),
        buildMap = {},
        masterConfig = (module.config && module.config()) || {};

    text = {
        version: '2.0.10',

        strip: function (content) {
            //Strips <?xml ...?> declarations so that external SVG and XML
            //documents can be added to a document without worry. Also, if the string
            //is an HTML document, only the part inside the body tag is returned.
            if (content) {
                content = content.replace(xmlRegExp, "");
                var matches = content.match(bodyRegExp);
                if (matches) {
                    content = matches[1];
                }
            } else {
                content = "";
            }
            return content;
        },

        jsEscape: function (content) {
            return content.replace(/(['\\])/g, '\\$1')
                .replace(/[\f]/g, "\\f")
                .replace(/[\b]/g, "\\b")
                .replace(/[\n]/g, "\\n")
                .replace(/[\t]/g, "\\t")
                .replace(/[\r]/g, "\\r")
                .replace(/[\u2028]/g, "\\u2028")
                .replace(/[\u2029]/g, "\\u2029");
        },

        createXhr: masterConfig.createXhr || function () {
            //Would love to dump the ActiveX crap in here. Need IE 6 to die first.
            var xhr, i, progId;
            if (typeof XMLHttpRequest !== "undefined") {
                return new XMLHttpRequest();
            } else if (typeof ActiveXObject !== "undefined") {
                for (i = 0; i < 3; i += 1) {
                    progId = progIds[i];
                    try {
                        xhr = new ActiveXObject(progId);
                    } catch (e) {}

                    if (xhr) {
                        progIds = [progId];  // so faster next time
                        break;
                    }
                }
            }

            return xhr;
        },

        /**
         * Parses a resource name into its component parts. Resource names
         * look like: module/name.ext!strip, where the !strip part is
         * optional.
         * @param {String} name the resource name
         * @returns {Object} with properties "moduleName", "ext" and "strip"
         * where strip is a boolean.
         */
        parseName: function (name) {
            var modName, ext, temp,
                strip = false,
                index = name.indexOf("."),
                isRelative = name.indexOf('./') === 0 ||
                             name.indexOf('../') === 0;

            if (index !== -1 && (!isRelative || index > 1)) {
                modName = name.substring(0, index);
                ext = name.substring(index + 1, name.length);
            } else {
                modName = name;
            }

            temp = ext || modName;
            index = temp.indexOf("!");
            if (index !== -1) {
                //Pull off the strip arg.
                strip = temp.substring(index + 1) === "strip";
                temp = temp.substring(0, index);
                if (ext) {
                    ext = temp;
                } else {
                    modName = temp;
                }
            }

            return {
                moduleName: modName,
                ext: ext,
                strip: strip
            };
        },

        xdRegExp: /^((\w+)\:)?\/\/([^\/\\]+)/,

        /**
         * Is an URL on another domain. Only works for browser use, returns
         * false in non-browser environments. Only used to know if an
         * optimized .js version of a text resource should be loaded
         * instead.
         * @param {String} url
         * @returns Boolean
         */
        useXhr: function (url, protocol, hostname, port) {
            var uProtocol, uHostName, uPort,
                match = text.xdRegExp.exec(url);
            if (!match) {
                return true;
            }
            uProtocol = match[2];
            uHostName = match[3];

            uHostName = uHostName.split(':');
            uPort = uHostName[1];
            uHostName = uHostName[0];

            return (!uProtocol || uProtocol === protocol) &&
                   (!uHostName || uHostName.toLowerCase() === hostname.toLowerCase()) &&
                   ((!uPort && !uHostName) || uPort === port);
        },

        finishLoad: function (name, strip, content, onLoad) {
            content = strip ? text.strip(content) : content;
            if (masterConfig.isBuild) {
                buildMap[name] = content;
            }
            onLoad(content);
        },

        load: function (name, req, onLoad, config) {
            //Name has format: some.module.filext!strip
            //The strip part is optional.
            //if strip is present, then that means only get the string contents
            //inside a body tag in an HTML string. For XML/SVG content it means
            //removing the <?xml ...?> declarations so the content can be inserted
            //into the current doc without problems.

            // Do not bother with the work if a build and text will
            // not be inlined.
            if (config.isBuild && !config.inlineText) {
                onLoad();
                return;
            }

            masterConfig.isBuild = config.isBuild;

            var parsed = text.parseName(name),
                nonStripName = parsed.moduleName +
                    (parsed.ext ? '.' + parsed.ext : ''),
                url = req.toUrl(nonStripName),
                useXhr = (masterConfig.useXhr) ||
                         text.useXhr;

            // Do not load if it is an empty: url
            if (url.indexOf('empty:') === 0) {
                onLoad();
                return;
            }

            //Load the text. Use XHR if possible and in a browser.
            if (!hasLocation || useXhr(url, defaultProtocol, defaultHostName, defaultPort)) {
                text.get(url, function (content) {
                    text.finishLoad(name, parsed.strip, content, onLoad);
                }, function (err) {
                    if (onLoad.error) {
                        onLoad.error(err);
                    }
                });
            } else {
                //Need to fetch the resource across domains. Assume
                //the resource has been optimized into a JS module. Fetch
                //by the module name + extension, but do not include the
                //!strip part to avoid file system issues.
                req([nonStripName], function (content) {
                    text.finishLoad(parsed.moduleName + '.' + parsed.ext,
                                    parsed.strip, content, onLoad);
                });
            }
        },

        write: function (pluginName, moduleName, write, config) {
            if (buildMap.hasOwnProperty(moduleName)) {
                var content = text.jsEscape(buildMap[moduleName]);
                write.asModule(pluginName + "!" + moduleName,
                               "define(function () { return '" +
                                   content +
                               "';});\n");
            }
        },

        writeFile: function (pluginName, moduleName, req, write, config) {
            var parsed = text.parseName(moduleName),
                extPart = parsed.ext ? '.' + parsed.ext : '',
                nonStripName = parsed.moduleName + extPart,
                //Use a '.js' file name so that it indicates it is a
                //script that can be loaded across domains.
                fileName = req.toUrl(parsed.moduleName + extPart) + '.js';

            //Leverage own load() method to load plugin value, but only
            //write out values that do not have the strip argument,
            //to avoid any potential issues with ! in file names.
            text.load(nonStripName, req, function (value) {
                //Use own write() method to construct full module value.
                //But need to create shell that translates writeFile's
                //write() to the right interface.
                var textWrite = function (contents) {
                    return write(fileName, contents);
                };
                textWrite.asModule = function (moduleName, contents) {
                    return write.asModule(moduleName, fileName, contents);
                };

                text.write(pluginName, nonStripName, textWrite, config);
            }, config);
        }
    };

    if (masterConfig.env === 'node' || (!masterConfig.env &&
            typeof process !== "undefined" &&
            process.versions &&
            !!process.versions.node &&
            !process.versions['node-webkit'])) {
        //Using special require.nodeRequire, something added by r.js.
        fs = require.nodeRequire('fs');

        text.get = function (url, callback, errback) {
            try {
                var file = fs.readFileSync(url, 'utf8');
                //Remove BOM (Byte Mark Order) from utf8 files if it is there.
                if (file.indexOf('\uFEFF') === 0) {
                    file = file.substring(1);
                }
                callback(file);
            } catch (e) {
                errback(e);
            }
        };
    } else if (masterConfig.env === 'xhr' || (!masterConfig.env &&
            text.createXhr())) {
        text.get = function (url, callback, errback, headers) {
            var xhr = text.createXhr(), header;
            xhr.open('GET', url, true);

            //Allow plugins direct access to xhr headers
            if (headers) {
                for (header in headers) {
                    if (headers.hasOwnProperty(header)) {
                        xhr.setRequestHeader(header.toLowerCase(), headers[header]);
                    }
                }
            }

            //Allow overrides specified in config
            if (masterConfig.onXhr) {
                masterConfig.onXhr(xhr, url);
            }

            xhr.onreadystatechange = function (evt) {
                var status, err;
                //Do not explicitly handle errors, those should be
                //visible via console output in the browser.
                if (xhr.readyState === 4) {
                    status = xhr.status;
                    if (status > 399 && status < 600) {
                        //An http 4xx or 5xx error. Signal an error.
                        err = new Error(url + ' HTTP status: ' + status);
                        err.xhr = xhr;
                        errback(err);
                    } else {
                        callback(xhr.responseText);
                    }

                    if (masterConfig.onXhrComplete) {
                        masterConfig.onXhrComplete(xhr, url);
                    }
                }
            };
            xhr.send(null);
        };
    } else if (masterConfig.env === 'rhino' || (!masterConfig.env &&
            typeof Packages !== 'undefined' && typeof java !== 'undefined')) {
        //Why Java, why is this so awkward?
        text.get = function (url, callback) {
            var stringBuffer, line,
                encoding = "utf-8",
                file = new java.io.File(url),
                lineSeparator = java.lang.System.getProperty("line.separator"),
                input = new java.io.BufferedReader(new java.io.InputStreamReader(new java.io.FileInputStream(file), encoding)),
                content = '';
            try {
                stringBuffer = new java.lang.StringBuffer();
                line = input.readLine();

                // Byte Order Mark (BOM) - The Unicode Standard, version 3.0, page 324
                // http://www.unicode.org/faq/utf_bom.html

                // Note that when we use utf-8, the BOM should appear as "EF BB BF", but it doesn't due to this bug in the JDK:
                // http://bugs.sun.com/bugdatabase/view_bug.do?bug_id=4508058
                if (line && line.length() && line.charAt(0) === 0xfeff) {
                    // Eat the BOM, since we've already found the encoding on this file,
                    // and we plan to concatenating this buffer with others; the BOM should
                    // only appear at the top of a file.
                    line = line.substring(1);
                }

                if (line !== null) {
                    stringBuffer.append(line);
                }

                while ((line = input.readLine()) !== null) {
                    stringBuffer.append(lineSeparator);
                    stringBuffer.append(line);
                }
                //Make sure we return a JavaScript string and not a Java string.
                content = String(stringBuffer.toString()); //String
            } finally {
                input.close();
            }
            callback(content);
        };
    } else if (masterConfig.env === 'xpconnect' || (!masterConfig.env &&
            typeof Components !== 'undefined' && Components.classes &&
            Components.interfaces)) {
        //Avert your gaze!
        Cc = Components.classes,
        Ci = Components.interfaces;
        Components.utils['import']('resource://gre/modules/FileUtils.jsm');
        xpcIsWindows = ('@mozilla.org/windows-registry-key;1' in Cc);

        text.get = function (url, callback) {
            var inStream, convertStream, fileObj,
                readData = {};

            if (xpcIsWindows) {
                url = url.replace(/\//g, '\\');
            }

            fileObj = new FileUtils.File(url);

            //XPCOM, you so crazy
            try {
                inStream = Cc['@mozilla.org/network/file-input-stream;1']
                           .createInstance(Ci.nsIFileInputStream);
                inStream.init(fileObj, 1, 0, false);

                convertStream = Cc['@mozilla.org/intl/converter-input-stream;1']
                                .createInstance(Ci.nsIConverterInputStream);
                convertStream.init(inStream, "utf-8", inStream.available(),
                Ci.nsIConverterInputStream.DEFAULT_REPLACEMENT_CHARACTER);

                convertStream.readString(inStream.available(), readData);
                convertStream.close();
                inStream.close();
                callback(readData.value);
            } catch (e) {
                throw new Error((fileObj && fileObj.path || '') + ': ' + e);
            }
        };
    }
    return text;
});

define('text!ev-script/templates/hider.html',[],function () { return '<a class="action-hide" href="#" title="Hide Picker">Hide</a>\n<% if (showLogout) { %>\n    <a class="action-logout" href="#" title="Logout <%= username %>">Logout</a>\n<% } %>\n';});

define('ev-script/views/hider',['require','underscore','ev-script/views/base','text!ev-script/templates/hider.html'],function(require) {

    

    var _ = require('underscore'),
        BaseView = require('ev-script/views/base');

    return BaseView.extend({
        template: _.template(require('text!ev-script/templates/hider.html')),
        initialize: function(options) {
            BaseView.prototype.initialize.call(this, options);
            _.bindAll(this, 'hideHandler', 'logoutHandler', 'authHandler', 'render');
            this.globalEvents.on('loggedIn', this.authHandler);
            this.globalEvents.on('loggedOut', this.authHandler);
            this.field = options.field;
        },
        events: {
            'click a.action-hide': 'hideHandler',
            'click a.action-logout': 'logoutHandler'
        },
        authHandler: function(ensembleUrl) {
            if (ensembleUrl === this.config.ensembleUrl) {
                this.render();
            }
        },
        render: function() {
            var username = '';
            if (this.info.get('ApplicationVersion') && this.auth.isAuthenticated()) {
                username = this.auth.getUser().get('UserName');
            }
            this.$el.html(this.template({
                showLogout: this.auth.isAuthenticated() && this.config.authType !== 'none',
                username: username
            }));
        },
        hideHandler: function(e) {
            this.appEvents.trigger('hidePicker', this.field.id);
            e.preventDefault();
        },
        logoutHandler: function(e) {
            this.auth.logout().always(this.appEvents.trigger('hidePickers'));
            e.preventDefault();
        }
    });

});

define('text!ev-script/templates/picker.html',[],function () { return '<div id="<%= id %>-hider" class="ev-hider"></div>\n<div id="<%= id %>-filter-block" class="ev-filter-block">\n    <div class="loader"></div>\n    <div class="ev-poweredby">\n        <a tabindex="-1" target="_blank" href="http://ensemblevideo.com"><span>Powered by Ensemble</span></a>\n    </div>\n</div>\n<div id="<%= id %>-results" class="ev-results clearfix"></div>\n';});

define('ev-script/views/picker',['require','jquery','underscore','ev-script/views/base','ev-script/views/hider','text!ev-script/templates/picker.html'],function(require) {

    

    var $ = require('jquery'),
        _ = require('underscore'),
        BaseView = require('ev-script/views/base'),
        HiderView = require('ev-script/views/hider');

    /*
     * Encapsulates views to manage search, display and selection of Ensemble videos and playlists.
     */
    return BaseView.extend({
        template: _.template(require('text!ev-script/templates/picker.html')),
        initialize: function(options) {
            BaseView.prototype.initialize.call(this, options);
            _.bindAll(this, 'chooseItem', 'hidePicker', 'showPicker');
            this.$el.hide();
            this.$el.html(this.template({
                id: this.id
            }));
            this.field = options.field;
            this.hider = new HiderView({
                el: this.$('div.ev-hider'),
                field: this.field,
                appId: this.appId
            });
            var $loader = this.$('div.loader');
            $loader.on('ajaxSend', _.bind(function(e, xhr, settings) {
                if (this === settings.picker) {
                    $loader.addClass('loading');
                }
            }, this)).on('ajaxComplete', _.bind(function(e, xhr, settings) {
                if (this === settings.picker) {
                    $loader.removeClass('loading');
                }
            }, this));
            this.appEvents.on('hidePickers', function(fieldId) {
                if (!fieldId || (this.field.id !== fieldId)) {
                    this.hidePicker();
                }
            }, this);
            this.appEvents.on('showPicker', function(fieldId) {
                if (this.field.id === fieldId && this.$el.is(':hidden')) {
                    this.showPicker();
                }
            }, this);
            this.appEvents.on('hidePicker', function(fieldId) {
                if (this.field.id === fieldId) {
                    this.hidePicker();
                }
            }, this);
            this.hider.render();
        },
        chooseItem: function(e) {
            var id = $(e.target).attr('rel');
            var content = this.resultsView.collection.get(id);
            this.model.set({
                id: id,
                content: content.toJSON()
            });
            this.field.model.set(this.model.attributes);
            this.appEvents.trigger('hidePicker', this.field.id);
            e.preventDefault();
        },
        hidePicker: function() {
            this.$el.fadeOut('fast');
        },
        showPicker: function() {
            // In case our authentication status has changed...re-render our hider
            this.hider.render();
            this.$el.fadeIn('fast');
        }
    });

});

define('text!ev-script/templates/search.html',[],function () { return '<form>\n    <label for="<%= id %>">Search:</label>\n    <input id="<%= id %>" type="text" class="form-text search" value="<%- searchVal %>" title="Search Media" />\n    <input type="submit" value="Go" class="form-submit" />\n</form>\n';});

define('ev-script/views/search',['require','underscore','ev-script/views/base','text!ev-script/templates/search.html'],function(require) {

    

    var _ = require('underscore'),
        BaseView = require('ev-script/views/base');

    return BaseView.extend({
        template: _.template(require('text!ev-script/templates/search.html')),
        initialize: function(options) {
            BaseView.prototype.initialize.call(this, options);
            _.bindAll(this, 'searchHandler', 'doSearch', 'autoSearch');
            this.picker = options.picker;
            this.callback = options.callback || function() {};
        },
        events: {
            'submit form': 'searchHandler',
            'keyup .search': 'autoSearch'
        },
        render: function() {
            this.$el.html(this.template({
                id: this.id + '-input',
                searchVal: this.picker.model.get('search')
            }));
        },
        doSearch: function() {
            this.picker.model.set({
                search: this.$('.search').val()
            });
            this.callback();
        },
        searchHandler: function(e) {
            this.doSearch();
            e.preventDefault();
        },
        autoSearch: function(e) {
            var value = e.target.value;
            if (value !== this.lastValue) {
                this.lastValue = value;
                if (this.submitTimeout) {
                    clearTimeout(this.submitTimeout);
                }
                this.submitTimeout = setTimeout(_.bind(function() {
                    this.doSearch();
                }, this), 1000);
            }
        }
    });

});

define('text!ev-script/templates/library-type-select.html',[],function () { return '<form>\n    <label for="<%= id %>">Type:</label>\n    <select id="<%= id %>" class="form-select source" title="Select Library Type">\n      <option value="content" <% if (sourceId === \'content\') { print(\'selected="selected"\'); } %>>Media Library</option>\n      <option value="shared" <% if (sourceId === \'shared\') { print(\'selected="selected"\'); } %>>Shared Library</option>\n    </select>\n    <input type="submit" value="Go" class="form-submit" />\n</form>\n';});

define('ev-script/views/library-type-select',['require','underscore','ev-script/views/base','text!ev-script/templates/library-type-select.html'],function(require) {

    

    var _ = require('underscore'),
        BaseView = require('ev-script/views/base');

    return BaseView.extend({
        template: _.template(require('text!ev-script/templates/library-type-select.html')),
        initialize: function(options) {
            BaseView.prototype.initialize.call(this, options);
            _.bindAll(this, 'changeHandler');
            this.picker = options.picker;
            this.callback = options.callback || function() {};
        },
        events: {
            'change .source': 'changeHandler'
        },
        render: function() {
            this.$el.html(this.template({
                id: this.id + '-select',
                sourceId: this.picker.model.get('sourceId')
            }));
        },
        changeHandler: function(e) {
            this.picker.model.set({
                sourceId: this.$('.source').val()
            });
            this.callback();
            e.preventDefault();
        }
    });

});

define('text!ev-script/templates/options.html',[],function () { return '<% collection.each(function(item) { %>\n    <option value="<%= item.id %>" <% if (selectedId === item.id) { print(\'selected="selected"\'); } %>><%- item.get(\'Name\') %></option>\n<% }); %>\n';});

define('ev-script/views/organization-select',['require','underscore','ev-script/views/base','text!ev-script/templates/options.html'],function(require) {

    

    var _ = require('underscore'),
        BaseView = require('ev-script/views/base');

    return BaseView.extend({
        template: _.template(require('text!ev-script/templates/options.html')),
        initialize: function(options) {
            BaseView.prototype.initialize.call(this, options);
            _.bindAll(this, 'render');
            this.picker = options.picker;
            this.$el.html('<option value="-1">Loading...</option>');
            this.collection.on('reset', this.render);
        },
        render: function() {
            var selectedId = this.picker.model.get('organizationId') || this.auth.getUser().get('OrganizationID');
            this.$el.html(this.template({
                selectedId: selectedId,
                collection: this.collection
            }));
            this.$el.trigger('change');
        }
    });

});

define('ev-script/collections/base',['require','jquery','underscore','backbone','ev-script/util/cache'],function(require) {

    

    var $ = require('jquery'),
        _ = require('underscore'),
        Backbone = require('backbone'),
        cacheUtil = require('ev-script/util/cache');

    return Backbone.Collection.extend({
        initialize: function(collections, options) {
            this.requiresAuth = true;
            this.appId = options.appId;
            this.config = cacheUtil.getAppConfig(this.appId);
            this.auth = cacheUtil.getAppAuth(this.appId);
            this.info = cacheUtil.getAppInfo(this.appId);
        },
        model: Backbone.Model.extend({
            idAttribute: 'ID'
        }),
        getCached: function(key) {},
        setCached: function(key, resp) {},
        clearCache: function(key) {},
        parse: function(response) {
            return response.Data;
        },
        fetch: function(options) {
            if (options && options.success) {
                options.success = _.wrap(options.success, _.bind(function(success) {
                    // We've successfully queried the API for something that
                    // requires authentication but we're in an unauthenticated
                    // state.  Double-check our authentication and proceed.
                    var args = Array.prototype.slice.call(arguments, 1);
                    if (this.requiresAuth && !this.auth.isAuthenticated()) {
                        this.auth.fetchUser()
                        .always(function() {
                            success.apply(this, args);
                        });
                    } else {
                        success.apply(this, args);
                    }
                }, this));
                // TODO - maybe wrap error to handle 401?
            }
            return Backbone.Collection.prototype.fetch.call(this, options);
        },
        sync: function(method, collection, options) {
            _.defaults(options || (options = {}), {
                xhrFields: { withCredentials: true }
            });
            if (method === 'read') {
                var cached = this.getCached(options.cacheKey);
                if (cached) {
                    var deferred = $.Deferred();
                    if (options.success) {
                        deferred.done(options.success);
                    }
                    return deferred.resolve(cached).promise();
                } else {
                    // Grab the response and cache
                    options.success = options.success || function(collection, response, options) {};
                    options.success = _.wrap(options.success, _.bind(function(success) {
                        this.setCached(options.cacheKey, arguments[1]);
                        success.apply(this, Array.prototype.slice.call(arguments, 1));
                    }, this));
                    return Backbone.Collection.prototype.sync.call(this, method, collection, options);
                }
            } else {
                return Backbone.Collection.prototype.sync.call(this, method, collection, options);
            }
        }
    });

});

define('ev-script/collections/organizations',['require','ev-script/collections/base','ev-script/util/cache'],function(require) {

    

    var BaseCollection = require('ev-script/collections/base'),
        cacheUtil = require('ev-script/util/cache');

    return BaseCollection.extend({
        initialize: function(models, options) {
            BaseCollection.prototype.initialize.call(this, models, options);
        },
        _cache: function(key, resp) {
            var cachedValue = null,
                user = this.auth.getUser(),
                userCache = user ? cacheUtil.getUserCache(this.config.ensembleUrl, user.id) : null;
            return userCache ? userCache[resp ? 'set' : 'get'](key, resp) : null;
        },
        getCached: function(key) {
            return this._cache('orgs');
        },
        setCached: function(key, resp) {
            return this._cache('orgs', resp);
        },
        url: function() {
            var api_url = this.config.ensembleUrl + '/api/Organizations';
            // Make this arbitrarily large so we can retrieve ALL orgs in a single request
            var sizeParam = 'PageSize=9999';
            var indexParam = 'PageIndex=1';
            var url = api_url + '?' + sizeParam + '&' + indexParam;
            return this.config.urlCallback ? this.config.urlCallback(url) : url;
        }
    });

});

define('ev-script/views/library-select',['require','underscore','ev-script/views/base','text!ev-script/templates/options.html'],function(require) {

    

    var _ = require('underscore'),
        BaseView = require('ev-script/views/base');

    return BaseView.extend({
        template: _.template(require('text!ev-script/templates/options.html')),
        initialize: function(options) {
            BaseView.prototype.initialize.call(this, options);
            _.bindAll(this, 'render');
            this.picker = options.picker;
            this.$el.html('<option value="-1">Loading...</option>');
            this.collection.on('reset', this.render);
        },
        render: function() {
            var selectedId = this.picker.model.get('libraryId') || this.auth.getUser().get('LibraryID');
            this.$el.html(this.template({
                selectedId: selectedId,
                collection: this.collection
            }));
            this.$el.trigger('change');
        }
    });

});

define('ev-script/collections/libraries',['require','ev-script/collections/base','ev-script/util/cache'],function(require) {

    

    var BaseCollection = require('ev-script/collections/base'),
        cacheUtil = require('ev-script/util/cache');

    return BaseCollection.extend({
        initialize: function(models, options) {
            BaseCollection.prototype.initialize.call(this, models, options);
            this.filterValue = options.organizationId || '';
        },
        _cache: function(key, resp) {
            var cachedValue = null,
                user = this.auth.getUser(),
                userCache = user ? cacheUtil.getUserCache(this.config.ensembleUrl, user.id) : null;
            if (userCache) {
                var libsCache = userCache.get('libs');
                if (!libsCache) {
                    userCache.set('libs', libsCache = new cacheUtil.Cache());
                }
                cachedValue = libsCache[resp ? 'set' : 'get'](key, resp);
            }
            return cachedValue;
        },
        getCached: function(key) {
            return this._cache(key);
        },
        setCached: function(key, resp) {
            return this._cache(key, resp);
        },
        url: function() {
            var api_url = this.config.ensembleUrl + '/api/Libraries';
            // Make this arbitrarily large so we can retrieve ALL libraries under an org in a single request
            var sizeParam = 'PageSize=9999';
            var indexParam = 'PageIndex=1';
            var onParam = 'FilterOn=OrganizationId';
            var valueParam = 'FilterValue=' + encodeURIComponent(this.filterValue);
            var url = api_url + '?' + sizeParam + '&' + indexParam + '&' + onParam + '&' + valueParam;
            return this.config.urlCallback ? this.config.urlCallback(url) : url;
        }
    });

});

define('text!ev-script/templates/unit-selects.html',[],function () { return '<form id="<%= formId %>" class="unit-selects">\n    <label for="<%= orgSelectId %>">Organization:</label>\n    <select id="<%= orgSelectId %>" class="form-select organizations" title="Select Organization"></select>\n    <label for="<%= libSelectId %>">Library:</label>\n    <select id="<%= libSelectId %>" class="form-select libraries" title="Select Library"></select>\n    <input type="submit" value="Go" class="form-submit" />\n</form>\n';});

define('ev-script/views/unit-selects',['require','jquery','underscore','ev-script/views/base','ev-script/views/organization-select','ev-script/collections/organizations','ev-script/views/library-select','ev-script/collections/libraries','text!ev-script/templates/unit-selects.html'],function(require) {

    

    var $ = require('jquery'),
        _ = require('underscore'),
        BaseView = require('ev-script/views/base'),
        OrganizationSelectView = require('ev-script/views/organization-select'),
        Organizations = require('ev-script/collections/organizations'),
        LibrarySelectView = require('ev-script/views/library-select'),
        Libraries = require('ev-script/collections/libraries');

    return BaseView.extend({
        template: _.template(require('text!ev-script/templates/unit-selects.html')),
        initialize: function(options) {
            BaseView.prototype.initialize.call(this, options);
            _.bindAll(this, 'loadOrgs', 'loadLibraries', 'changeOrganization', 'changeLibrary');
            this.picker = options.picker;
            this.id = options.id;
            this.$el.html(this.template({
                formId: this.id + '-unit-selects',
                orgSelectId: this.id + '-org-select',
                libSelectId: this.id + '-lib-select'
            }));
            this.orgSelect = new OrganizationSelectView({
                el: this.$('.organizations'),
                picker: this.picker,
                appId: this.appId,
                collection: new Organizations({}, {
                    appId: this.appId
                })
            });
            this.libSelect = new LibrarySelectView({
                el: this.$('.libraries'),
                picker: this.picker,
                appId: this.appId,
                collection: new Libraries({}, {
                    appId: this.appId
                })
            });
        },
        events: {
            'change select.organizations': 'changeOrganization',
            'change select.libraries': 'changeLibrary'
        },
        changeOrganization: function(e) {
            this.picker.model.set({
                organizationId: e.target.value
            });
            this.loadLibraries();
        },
        changeLibrary: function(e) {
            this.picker.model.set({
                libraryId: e.target.value
            });
        },
        loadOrgs: function() {
            var orgs = new Organizations({}, {
                appId: this.appId
            });
            orgs.fetch({
                picker: this.picker,
                success: _.bind(function(collection, response, options) {
                    this.orgSelect.collection.reset(collection.models);
                }, this),
                error: _.bind(function(collection, xhr, options) {
                    this.ajaxError(xhr, _.bind(function() {
                        this.loadOrgs();
                    }, this));
                }, this)
            });
        },
        loadLibraries: function() {
            var orgId = this.picker.model.get('organizationId');
            var libs = new Libraries({}, {
                organizationId: orgId,
                appId: this.appId
            });
            libs.fetch({
                picker: this.picker,
                cacheKey: orgId,
                success: _.bind(function(collection, response, options) {
                    this.libSelect.collection.reset(collection.models);
                }, this),
                error: _.bind(function(collection, xhr, options) {
                    this.ajaxError(xhr, _.bind(function() {
                        this.loadLibraries();
                    }, this));
                }, this)
            });
        }
    });

});

/**
 * ev-scroll-loader 0.1.0 2013-01-31
 * Ensemble Video jQuery Scroll Loader Plugin
 * https://github.com/jmpease/ev-scroll-loader
 * Copyright (c) 2013 Symphony Video, Inc.
 * Licensed MIT, GPL-2.0
 */
/*global jQuery*/
(function($) {

    

    var defaults = {
        callback: function() {}
    };

    var methods = {
        init: function(options) {
            var settings = $.extend({}, defaults, options);
            return this.each(function() {
                var $this = $(this);
                $this.addClass('scroll-content');
                var $wrap = $this.wrap('<div class=\"scrollWrap\"/>').closest('.scrollWrap');
                $wrap.append('<div class="loader"></div>');
                var scrollHeight = this.scrollHeight;
                var setHeight = settings.height || scrollHeight;
                var wrapHeight = Math.min(setHeight, scrollHeight) - 10;
                $wrap.css({
                    'position': 'relative',
                    'height': wrapHeight + 'px',
                    'overflow-y': 'scroll'
                }).scroll(function() {
                    if ($wrap.scrollTop() === $wrap[0].scrollHeight - wrapHeight) {
                        settings.callback.apply($this[0]);
                    }
                });
            });
        },
        showLoader: function() {
            var $wrap = $(this).closest('.scrollWrap');
            $('.loader', $wrap).show();
            return this;
        },
        hideLoader: function() {
            var $wrap = $(this).closest('.scrollWrap');
            $('.loader', $wrap).hide();
            return this;
        }
    };

    $.fn.evScrollLoader = function(method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        }
    };

}(jQuery));

define("ev-scroll-loader", function(){});

define('text!ev-script/templates/results.html',[],function () { return '<div class="total">Search returned <%= totalResults %> results.</div>\n<div class="results">\n    <table class="content-list"></table>\n</div>\n';});

define('text!ev-script/templates/no-results.html',[],function () { return '<tr class="odd"><td colspan="2">No results available.</td></tr>\n';});

define('ev-script/views/results',['require','jquery','underscore','ev-script/views/base','ev-scroll-loader','text!ev-script/templates/results.html','text!ev-script/templates/no-results.html'],function(require) {

    

    var $ = require('jquery'),
        _ = require('underscore'),
        BaseView = require('ev-script/views/base');

    require('ev-scroll-loader');

    /*
     * Base object for result views since video and playlist results are rendered differently
     */
    return BaseView.extend({
        resultsTemplate: _.template(require('text!ev-script/templates/results.html')),
        emptyTemplate: _.template(require('text!ev-script/templates/no-results.html')),
        initialize: function(options) {
            BaseView.prototype.initialize.call(this, options);
            _.bindAll(this, 'render', 'loadMore', 'addHandler', 'previewItem');
            this.picker = options.picker;
            this.appId = options.appId;
            this.loadLock = false;
        },
        events: {
            'click a.action-preview': 'previewItem'
        },
        getItemHtml: function(item, index) {
            if (this.resultTemplate) {
                return this.resultTemplate({
                    item: item,
                    index: index

                });
            }
        },
        previewItem: function(e) {
            var element = e.currentTarget;
            var id = $(element).attr('rel');
            var item = this.collection.get(id);
            var settings = {
                id: id,
                content: item.toJSON(),
                appId: this.appId
            };
            var previewView = new this.previewClass({
                el: element,
                model: new this.modelClass(settings),
                appId: this.appId,
                picker: this.picker
            });
            // Stop event propagation so we don't trigger preview of stored field item as well
            e.stopPropagation();
            e.preventDefault();
        },
        loadMore: function() {
            if (this.collection.hasMore && !this.loadLock) {
                this.loadLock = true;
                this.collection.fetch({
                    remove: false,
                    picker: this.picker,
                    success: _.bind(function(collection, response, options) {
                        if (_.size(response.Data) < this.config.pageSize) {
                            collection.hasMore = false;
                            this.$scrollLoader.evScrollLoader('hideLoader');
                        } else {
                            collection.hasMore = true;
                            collection.pageIndex += 1;
                        }
                        this.loadLock = false;
                    }, this),
                    error: _.bind(function(collection, xhr, options) {
                        this.ajaxError(xhr, _.bind(function() {
                            this.loadMore();
                        }, this));
                        this.loadLock = false;
                    }, this)
                });
            }
        },
        addHandler: function(item, collection, options) {
            var $item = $(this.getItemHtml(item, options.index));
            this.decorate($item);
            this.$('.content-list').append($item);
        },
        // Override this in extending views to update the DOM when items are added
        decorate: function($item) {},
        render: function() {
            this.$el.html(this.resultsTemplate({
                totalResults: this.collection.totalResults
            }));
            var $contentList = this.$('.content-list');
            if (!this.collection.isEmpty()) {
                this.collection.each(function(item, index) {
                    var $item = $(this.getItemHtml(item, index));
                    this.decorate($item);
                    $contentList.append($item);
                }, this);
            } else {
                $contentList.append(this.emptyTemplate());
            }
            var scrollHeight = this.config.scrollHeight;
            if (this.collection.size() >= this.config.pageSize || $contentList[0].scrollHeight > scrollHeight) {
                this.$scrollLoader = $contentList.evScrollLoader({
                    height: scrollHeight,
                    callback: this.loadMore
                });
                if (!this.collection.hasMore) {
                    this.$scrollLoader.evScrollLoader('hideLoader');
                }
            }
            // Prevent multiple bindings if the collection hasn't changed between render calls
            this.collection.off('add', this.addHandler).on('add', this.addHandler);
        }
    });

});

/*global window*/
define('ev-script/views/preview',['require','jquery','underscore','ev-script/views/base','ev-script/models/video-settings','jquery-ui'],function(require) {

    

    var $ = require('jquery'),
        _ = require('underscore'),
        BaseView = require('ev-script/views/base'),
        VideoSettings = require('ev-script/models/video-settings');

    require('jquery-ui');

    return BaseView.extend({
        initialize: function(options) {
            BaseView.prototype.initialize.call(this, options);
            var $dialogWrap = $('<div class="dialogWrap"></div>'),
                content = this.model.get('content') || {
                    Title: this.model.get('id')
                },
                embedSettings = new this.model.constructor(this.model.toJSON()),
                // Desired media dimensions
                mediaDims = {
                    width: this.model.get('width') || (this.model instanceof VideoSettings ? 640 : 800),
                    height: this.model.get('height') || (this.model instanceof VideoSettings ? 360 : 850)
                },
                // Dialog dimensions TBD
                dialogDims = {},
                // Desired difference between media width and containing dialog width
                widthOffset = 50,
                // Desired difference between media height and containing dialog height
                heightOffset = 140,
                // Used for scaling media dimensions to fit within desired dialog size
                ratio,
                // Maximum width of media based on desired dialog width
                maxWidth,
                // Our dialog
                $dialog;
            this.$el.after($dialogWrap);
            dialogDims.width = Math.min(mediaDims.width + widthOffset, $(window).width() - this.config.dialogMargin);
            dialogDims.height = Math.min(mediaDims.height + heightOffset, $(window).height() - this.config.dialogMargin);
            maxWidth = dialogDims.width - widthOffset;
            // Only bother scaling if we're dealing with videos and if width is
            // too big
            if (this.model instanceof VideoSettings && mediaDims.width > maxWidth) {
                ratio = maxWidth / mediaDims.width;
                mediaDims.width = mediaDims.width * ratio;
                mediaDims.height = mediaDims.height * ratio;
            }
            embedSettings.set('width', mediaDims.width);
            embedSettings.set('height', mediaDims.height);
            $dialog = $dialogWrap.dialog({
                title: content.Title || content.Name,
                modal: true,
                width: dialogDims.width,
                height: dialogDims.height,
                draggable: false,
                resizable: false,
                dialogClass: 'ev-dialog',
                create: _.bind(function(event, ui) {
                    var embedView = new this.embedClass({
                        model: embedSettings,
                        appId: this.appId
                    });
                    $dialogWrap.html(embedView.$el);
                }, this),
                close: function(event, ui) {
                    $dialogWrap.dialog('destroy').remove();
                }
            });
        }
    });

});

define('text!ev-script/templates/video-embed.html',[],function () { return '<iframe src="<%= src %>"\n        frameborder="0"\n        style="width: <%= width %>px;height:<%= (parseInt(height, 10) + 56) %>px;"\n        allowfullscreen>\n</iframe>\n';});

define('ev-script/views/video-embed',['require','underscore','ev-script/views/base','text!ev-script/templates/video-embed.html'],function(require) {

    

    var _ = require('underscore'),
        BaseView = require('ev-script/views/base');

    return BaseView.extend({
        template: _.template(require('text!ev-script/templates/video-embed.html')),
        initialize: function(options) {
            BaseView.prototype.initialize.call(this, options);
            // Width and height really should be set by now...but use a reasonable default if not
            var width = (this.model.get('width') ? this.model.get('width') : '640');
            var height = (this.model.get('height') ? this.model.get('height') : '360');
            var src = this.config.ensembleUrl + '/app/plugin/embed.aspx?ID=' + this.model.get('id') +
                '&autoPlay=' + this.model.get('autoplay') +
                '&displayTitle=' + this.model.get('showtitle') +
                '&hideControls=' + this.model.get('hidecontrols') +
                '&showCaptions=' + this.model.get('showcaptions') +
                '&width=' + width +
                '&height=' + height;
            this.$el.html(this.template({
                src: src,
                width: width,
                height: height
            }));
        }
    });

});

define('ev-script/models/base',['require','jquery','underscore','backbone','ev-script/util/cache','ev-script/collections/base'],function(require) {

    

    var $ = require('jquery'),
        _ = require('underscore'),
        Backbone = require('backbone'),
        cacheUtil = require('ev-script/util/cache'),
        BaseCollection = require('ev-script/collections/base');

    return Backbone.Model.extend({
        initialize: function(attributes, options) {
            this.appId = options.appId;
            this.config = cacheUtil.getAppConfig(this.appId);
        },
        getCached: function() {},
        setCached: function() {},
        fetch: function(options) {
            if (options.success) {
                options.success = _.wrap(options.success, _.bind(function(success) {
                    // We've successfully queried the API for something that
                    // requires authentication but we're in an unauthenticated
                    // state.  Double-check our authentication and proceed.
                    var args = Array.prototype.slice.call(arguments, 1);
                    if (this.requiresAuth && !this.auth.isAuthenticated()) {
                        this.auth.fetchUser()
                        .always(function() {
                            success.apply(this, args);
                        });
                    } else {
                        success.apply(this, args);
                    }
                }, this));
                // TODO - maybe wrap error to handle 401?
            }
            return Backbone.Model.prototype.fetch.call(this, options);
        },
        sync: function(method, collection, options) {
            _.defaults(options || (options = {}), {
                xhrFields: { withCredentials: true }
            });
            if (method === 'read') {
                var cached = this.getCached(options.cacheKey);
                if (cached) {
                    var deferred = $.Deferred();
                    if (options.success) {
                        deferred.done(options.success);
                    }
                    return deferred.resolve(cached).promise();
                } else {
                    // Grab the response and cache
                    options.success = options.success || function(collection, response, options) {};
                    options.success = _.wrap(options.success, _.bind(function(success) {
                        this.setCached(options.cacheKey, arguments[1]);
                        success.apply(this, Array.prototype.slice.call(arguments, 1));
                    }, this));
                    return Backbone.Model.prototype.sync.call(this, method, collection, options);
                }
            } else {
                return Backbone.Model.prototype.sync.call(this, method, collection, options);
            }
        }
    });

});

define('ev-script/models/video-encoding',['require','backbone','ev-script/models/base','underscore','ev-script/util/cache'],function(require) {

    

    var Backbone = require('backbone'),
        BaseModel = require('ev-script/models/base'),
        _ = require('underscore'),
        cacheUtil = require('ev-script/util/cache');

    return BaseModel.extend({
        idAttribute: 'videoID',
        initialize: function(attributes, options) {
            BaseModel.prototype.initialize.call(this, attributes, options);
            this.requiresAuth = false;
        },
        // TODO - cache responses
        getCached: function(key) {},
        setCached: function(key, resp) {},
        url: function() {
            // Note the response is actually JSONP.  We'll strip the padding
            // below with our dataFilter.
            var url = this.config.ensembleUrl + '/app/api/content/show.json/' + this.get('fetchId');
            return this.config.urlCallback ? this.config.urlCallback(url) : url;
        },
        getDims: function() {
            var dimsRaw = this.get('dimensions') || "640x360",
                dimsStrs = dimsRaw.split('x'),
                dims = [];
            dims[0] = parseInt(dimsStrs[0], 10) || 640;
            dims[1] = parseInt(dimsStrs[1], 10) || 360;
            return dims;
        },
        getRatio: function() {
            var dims = this.getDims();
            return dims[0] / dims[1];
        },
        getWidth: function() {
            return this.getDims()[0];
        },
        getHeight: function() {
            return this.getDims()[1];
        },
        parse: function(response) {
            if (_.isArray(response.dataSet.encodings)) {
                // This is a collection, so return the highest bitrate encoding
                return _.max(response.dataSet.encodings, function(encoding, index, encodings) {
                    return parseInt(encoding.bitRate, 10);
                });
            } else {
                return response.dataSet.encodings;
            }
        },
        sync: function(method, model, options) {
            _.extend(options, {
                dataFilter: function(data) {
                    // Strip padding from JSONP response
                    var match = data.match(/\{[\s\S]*\}/);
                    return match ? match[0] : data;
                }
            });
            return Backbone.sync.call(this, method, model, options);
        }
    });

});

define('ev-script/views/video-preview',['require','underscore','ev-script/views/preview','ev-script/views/video-embed','ev-script/models/video-encoding'],function(require) {

    

    var _ = require('underscore'),
        PreviewView = require('ev-script/views/preview'),
        VideoEmbedView = require('ev-script/views/video-embed'),
        VideoEncoding = require('ev-script/models/video-encoding');

    return PreviewView.extend({
        embedClass: VideoEmbedView,
        initialize: function(options) {
            // Although our super sets this...we don't call our super init until
            // later so we should set appId here
            this.appId = options.appId;
            this.encoding = options.encoding || new VideoEncoding({
                fetchId: this.model.id
            }, {
                appId: this.appId
            });
            this.picker = options.picker;
            var success = _.bind(function() {
                if (!this.model.get('width') || !this.model.get('height')) {
                    this.model.set({
                        width: this.encoding.getWidth(),
                        height: this.encoding.getHeight()
                    });
                }
                PreviewView.prototype.initialize.call(this, options);
            }, this);
            if (this.encoding.isNew()) {
                this.encoding.fetch({
                    success: success,
                    // The loader indicator will show if it detects an AJAX
                    // request on our picker
                    picker: this.picker
                });
            } else {
                success();
            }
        }
    });

});

define('text!ev-script/templates/video-result.html',[],function () { return '<tr class="<%= (index % 2 ? \'odd\' : \'even\') %>">\n    <td class="content-actions">\n        <img src="<%= item.get(\'ThumbnailUrl\').replace(/width=100/, \'width=150\') %>" alt="<%- item.get(\'Title\') %> thumbnail image"/>\n        <div class="action-links">\n            <a class="action-add" href="#" title="Choose <%- item.get(\'Title\') %>" rel="<%= item.get(\'ID\') %>"><span>Choose</span></a>\n            <a class="action-preview" href="#" title="Preview: <%- item.get(\'Title\') %>" rel="<%= item.get(\'ID\') %>"><span>Preview:  item.get(\'Title\') %></span></a>\n        </div>\n    </td>\n    <td class="content-meta">\n        <table class="content-item">\n            <tbody>\n                <tr class="title">\n                    <td colspan="2">\n                        <a class="action-preview" title="Preview: <%-item.get(\'Title\') %>" href="#" rel="<%= item.get(\'ID\') %>"><%- item.get(\'Title\') %></a>\n                    </td>\n                </tr>\n                <tr class="desc"><td class="label">Description</td><td class="value"><%- item.get(\'Description\') %></td></tr>\n                <tr><td class="label">Date Added</td><td class="value"><%- new Date(item.get(\'AddedOn\')).toLocaleString() %></td></tr>\n                <tr><td class="label">Keywords</td><td class="value"><%- item.get(\'Keywords\') %></td></tr>\n                <tr><td class="label">Library</td><td class="value"><%- item.get(\'LibraryName\') %></td></tr>\n            </tbody>\n        </table>\n    </td>\n</tr>\n';});

define('ev-script/views/video-results',['require','jquery','underscore','ev-script/views/results','ev-script/models/video-settings','ev-script/views/video-preview','text!ev-script/templates/video-result.html'],function(require) {

    

    var $ = require('jquery'),
        _ = require('underscore'),
        ResultsView = require('ev-script/views/results'),
        VideoSettings = require('ev-script/models/video-settings'),
        VideoPreviewView = require('ev-script/views/video-preview');

    return ResultsView.extend({
        modelClass: VideoSettings,
        previewClass: VideoPreviewView,
        resultTemplate: _.template(require('text!ev-script/templates/video-result.html')),
        initialize: function(options) {
            ResultsView.prototype.initialize.call(this, options);
        },
        decorate: function($item) {
            // Handle truncation (more/less) of description text
            $('.desc .value', $item).each(function(element) {
                var $this = $(this), $full, $short, truncLen = 100, fullDesc = $(this).html();
                if (fullDesc.length > truncLen) {
                    $this.empty();
                    $full = $('<span>' + fullDesc + '</span>');
                    $short = $('<span>' + fullDesc.substring(0, truncLen) + '...</span>');
                    var $shorten = $('<a href="#">Less</a>').click(function(e) {
                        $full.hide();
                        $short.show();
                        e.preventDefault();
                    });
                    var $expand = $('<a href="#">More</a>').click(function(e) {
                        $short.hide();
                        $full.show();
                        e.preventDefault();
                    });
                    $full.hide().append($shorten);
                    $short.append($expand);
                    $this.append($short).append($full);
                }
            });
        }
    });

});

define('ev-script/collections/videos',['require','ev-script/collections/base','ev-script/util/cache'],function(require) {

    

    var BaseCollection = require('ev-script/collections/base'),
        cacheUtil = require('ev-script/util/cache');

    return BaseCollection.extend({
        initialize: function(models, options) {
            BaseCollection.prototype.initialize.call(this, models, options);
            this.libraryId = options.libraryId || '';
            this.filterOn = options.filterOn || '';
            this.filterValue = options.filterValue || '';
            this.sourceUrl = options.sourceId === 'shared' ? '/api/SharedContent' : '/api/Content';
            this.pageIndex = 1;
        },
        _cache: function(key, resp) {
            var cachedValue = null,
                user = this.auth.getUser(),
                userCache = user ? cacheUtil.getUserCache(this.config.ensembleUrl, user.id) : null;
            if (userCache) {
                var videosCache = userCache.get('videos');
                if (!videosCache) {
                    userCache.set('videos', videosCache = new cacheUtil.Cache());
                }
                cachedValue = videosCache[resp ? 'set' : 'get'](key, resp);
            }
            return cachedValue;
        },
        getCached: function(key) {
            return this._cache(key);
        },
        setCached: function(key, resp) {
            return this._cache(key, resp);
        },
        clearCache: function() {
            var user = this.auth.getUser(),
                userCache = user ? cacheUtil.getUserCache(this.config.ensembleUrl, user.id) : null;
            if (userCache) {
                userCache.set('videos', null);
            }
        },
        url: function() {
            var api_url = this.config.ensembleUrl + this.sourceUrl,
                sizeParam = 'PageSize=' + this.config.pageSize,
                indexParam = 'PageIndex=' + this.pageIndex,
                onParam = 'FilterOn=' + encodeURIComponent(this.filterOn),
                valueParam = 'FilterValue=' + encodeURIComponent(this.filterValue),
                url = api_url + '/' + this.libraryId + '?' + sizeParam + '&' + indexParam + '&' + onParam + '&' + valueParam;
            return this.config.urlCallback ? this.config.urlCallback(url) : url;
        }
    });

});

define('ev-script/collections/media-workflows',['require','ev-script/collections/base','ev-script/util/cache'],function(require) {

    

    var BaseCollection = require('ev-script/collections/base'),
        cacheUtil = require('ev-script/util/cache');

    return BaseCollection.extend({
        initialize: function(models, options) {
            BaseCollection.prototype.initialize.call(this, models, options);
            this.filterValue = options.libraryId || '';
        },
        _cache: function(key, resp) {
            var cachedValue = null,
                user = this.auth.getUser(),
                userCache = user ? cacheUtil.getUserCache(this.config.ensembleUrl, user.id) : null;
            if (userCache) {
                var workflowsCache = userCache.get('workflows');
                if (!workflowsCache) {
                    userCache.set('workflows', workflowsCache = new cacheUtil.Cache());
                }
                cachedValue = workflowsCache[resp ? 'set' : 'get'](key, resp);
            }
            return cachedValue;
        },
        getCached: function(key) {
            return this._cache(key);
        },
        setCached: function(key, resp) {
            return this._cache(key, resp);
        },
        url: function() {
            var api_url = this.config.ensembleUrl + '/api/MediaWorkflows';
            // Make this arbitrarily large so we can retrieve ALL workflows in a single request
            var sizeParam = 'PageSize=9999';
            var indexParam = 'PageIndex=1';
            var onParam = 'FilterOn=LibraryId';
            var valueParam = 'FilterValue=' + encodeURIComponent(this.filterValue);
            var url = api_url + '?' + sizeParam + '&' + indexParam + '&' + onParam + '&' + valueParam;
            return this.config.urlCallback ? this.config.urlCallback(url) : url;
        },
        // Override base parse in order to grab settings
        parse: function(response) {
            this.settings = response.Settings;
            return response.Data;
        }
    });

});


define('ev-script/views/workflow-select',['require','underscore','ev-script/views/base','text!ev-script/templates/options.html'],function(require) {

    

    var _ = require('underscore'),
        BaseView = require('ev-script/views/base');

    return BaseView.extend({
        template: _.template(require('text!ev-script/templates/options.html')),
        initialize: function(options) {
            BaseView.prototype.initialize.call(this, options);
            _.bindAll(this, 'render');
            this.$el.html('<option value="-1">Loading...</option>');
            this.render();
        },
        render: function() {
            var selected = this.collection.findWhere({
                'IsDefault': true
            }) || this.collection.at(0);
            this.$el.html(this.template({
                selectedId: selected.id,
                collection: this.collection
            }));
        },
        getSelected: function() {
            return this.collection.get(this.$('option:selected').val());
        }
    });

});

define('text!ev-script/templates/upload.html',[],function () { return '<form class="upload-form" method="POST" action="">\n    <select class="form-select" name="MediaWorkflowID"></select>\n    <div class="fieldWrap">\n        <label for="Title">Title *</label>\n        <input class="form-text" type="text" name="Title" id="Title" />\n    </div>\n    <div class="fieldWrap">\n        <label for="Description">Description</label>\n        <textarea class="form-text" name="Description" id="Description" />\n    </div>\n    <div class="upload"></div>\n</form>\n';});

/*global window,plupload,navigator*/
define('ev-script/views/upload',['require','jquery','underscore','ev-script/views/base','backbone','ev-script/views/workflow-select','ev-script/models/video-settings','plupload','jquery.plupload.queue','text!ev-script/templates/upload.html'],function(require) {

    

    var $ = require('jquery'),
        _ = require('underscore'),
        BaseView = require('ev-script/views/base'),
        Backbone = require('backbone'),
        WorkflowSelect = require('ev-script/views/workflow-select'),
        VideoSettings = require('ev-script/models/video-settings');

    // Explicit dependency declaration
    require('plupload');
    require('jquery.plupload.queue');

    return BaseView.extend({
        template: _.template(require('text!ev-script/templates/upload.html')),
        events: {
            'change select': 'handleSelect'
        },
        initialize: function(options) {
            BaseView.prototype.initialize.call(this, options);
            _.bindAll(this, 'render', 'decorateUploader', 'closeDialog', 'handleSelect');
            this.field = options.field;
            this.$anchor = this.$el;
            this.setElement(this.template());
            this.$upload = this.$('.upload');
            this.workflows = options.workflows;
            this.workflowSelect = new WorkflowSelect({
                appId: this.appId,
                el: this.$('select')[0],
                collection: this.workflows
            });
            this.render();
            this.decorateUploader();
            this.appEvents.on('hidePickers', this.closeDialog);
        },
        getWidth: function() {
            return Math.min(600, $(window).width() - this.config.dialogMargin);
        },
        getHeight: function() {
            return Math.min(400, $(window).height() - this.config.dialogMargin);
        },
        decorateUploader: function() {
            var extensions = this.workflows.settings.SupportedVideo.replace(/\*\./g, '').replace(/;/g, ',').replace(/\s/g, ''),
                selected = this.workflowSelect.getSelected(),
                maxUploadSize = parseInt(selected.get('MaxUploadSize'), 10); //,
                // runtimes = 'html5,html4',
                // iOS = (navigator.userAgent.indexOf('iPad') > -1) || (navigator.userAgent.indexOf('iPhone') > -1),
                // MSIE = (navigator.userAgent.indexOf('MSIE') > -1),
                // Android = (navigator.userAgent.indexOf('Android') > -1),
                // SafariVersion5 = (navigator.userAgent.match(/Version\/5.*Safari/i) != null) && (navigator.userAgent.indexOf('Chrome') === -1) && !iOS && !Android;

            // runtime selection based on browser
            // if (iOS) {
            //     runtimes = 'html5,html4';
            // } else if (MSIE) {
            //     runtimes = 'silverlight,html4';
            // } else if (Android) {
            //     runtimes = 'flash,html5,html4';
            // }

            if (this.$upload.pluploadQueue()) {
                this.$upload.pluploadQueue().destroy();
            }

            this.$upload.pluploadQueue({
                url: this.workflows.settings.SubmitUrl,
                runtimes: 'html5,html4,flash', //runtimes,
                max_file_size: maxUploadSize > 0 ? maxUploadSize + 'gb' : '12gb',
                max_file_count: 1,
                chunk_size: '2mb',
                unique_names: false,
                multiple_queues: false,
                multi_selection: false,
                drag_drop: true,
                multipart: true,
                flash_swf_url: this.config.pluploadFlashPath,
                // FIXME
                // silverlight_xap_url: 'FIXME',
                preinit: {
                    Init: _.bind(function(up, info) {
                        // Remove runtime tooltip
                        $('.plupload_container', this.$upload).removeAttr('title');
                        // Change text since we only allow single file upload
                        $('.plupload_add', this.$upload).text('Add file');
                        $('.plupload_droptext', this.$upload).text('Drag file here.');
                    }, this),
                    UploadFile: _.bind(function(up, file) {
                        up.settings.multipart_params = {
                            'Title': this.$('#Title').val(),
                            'Description': this.$('#Description').val(),
                            'MediaWorkflowID': this.$('select').val()
                        };
                    }, this)
                },
                init: {
                    StateChanged: _.bind(function(up) {
                        switch (up.state) {
                            case plupload.STARTED:
                                if (up.state === plupload.STARTED) {
                                    if ($('.plupload_cancel', this.$upload).length === 0) {
                                        // Add cancel button
                                        this.$cancel = $('<a class="plupload_button plupload_cancel" href="#">Cancel upload</a>')
                                        .insertBefore($('.plupload_filelist_footer .plupload_clearer', this.$upload))
                                        .click(_.bind(function() {
                                            up.stop();
                                            this.decorateUploader();
                                        }, this));
                                    }
                                    if (this.$cancel) {
                                        this.$cancel.show();
                                    }
                                }
                                break;
                            case plupload.STOPPED:
                                if (this.$cancel) {
                                    this.$cancel.hide();
                                }
                                break;
                        }
                    }, this),
                    BeforeUpload: _.bind(function(up, file) {
                        var $title = this.$('#Title'),
                            title = $title.val();
                        if (!title || title.trim() === '') {
                            $title.focus();
                            up.stop();
                            $('.plupload_upload_status', this.$upload).hide();
                            $('.plupload_buttons', this.$upload).show();
                        }
                    }, this),
                    FilesAdded: _.bind(function(up, files) {
                        var validExtensions = extensions.split(',');
                        _.each(files, function(file) {
                            var parts = file.name.split('.'),
                                extension = parts[parts.length - 1];
                            if (!_.contains(validExtensions, extension.toLowerCase())) {
                                up.removeFile(file);
                                up.trigger('Error', {
                                    code : plupload.FILE_EXTENSION_ERROR,
                                    message : plupload.translate('File extension error.'),
                                    file : file
                                });
                            }
                        });
                        // Keep the last file in the queue
                        if (up.files.length > 1) {
                            up.splice(0, up.files.length - 1);
                        }
                    }, this),
                    UploadComplete: _.bind(function() {
                        this.closeDialog();
                    }, this),
                    FileUploaded: _.bind(function(up, file, info) {
                        this.appEvents.trigger('fileUploaded');
                    }, this)
                }
            });
            // Hacks to deal with z-index issue in dialog
            // see https://github.com/moxiecode/plupload/issues/468
            this.$upload.pluploadQueue().bind('refresh', function() {
                $('div.upload > div.plupload').css({ 'z-index': '0' });
                $('.plupload_button').css({ 'z-index': '1' });
            });
            this.$upload.pluploadQueue().refresh();
        },
        closeDialog: function() {
            if (this.$dialog) {
                this.$dialog.dialog('close');
            }
        },
        handleSelect: function(e) {
            this.decorateUploader();
        },
        render: function() {
            var $dialogWrap = $('<div class="dialogWrap"></div>'),
                $dialog;
            this.$anchor.after($dialogWrap);
            this.$dialog = $dialogWrap.dialog({
                title: 'Upload Media to Ensemble',
                modal: true,
                width: this.getWidth(),
                height: this.getHeight(),
                draggable: false,
                resizable: false,
                dialogClass: 'ev-dialog',
                create: _.bind(function(event, ui) {
                    $dialogWrap.html(this.$el);
                }, this),
                close: _.bind(function(event, ui) {
                    this.$upload.pluploadQueue().destroy();
                    $dialogWrap.dialog('destroy').remove();
                    this.appEvents.off('hidePickers', this.closeDialog);
                    this.$dialog = null;
                }, this)
            });
        }
    });

});

define('ev-script/views/video-picker',['require','jquery','underscore','ev-script/views/picker','ev-script/views/search','ev-script/views/library-type-select','ev-script/views/unit-selects','ev-script/views/video-results','ev-script/collections/videos','ev-script/collections/media-workflows','ev-script/views/upload'],function(require) {

    

    var $ = require('jquery'),
        _ = require('underscore'),
        PickerView = require('ev-script/views/picker'),
        SearchView = require('ev-script/views/search'),
        TypeSelectView = require('ev-script/views/library-type-select'),
        UnitSelectsView = require('ev-script/views/unit-selects'),
        VideoResultsView = require('ev-script/views/video-results'),
        Videos = require('ev-script/collections/videos'),
        MediaWorkflows = require('ev-script/collections/media-workflows'),
        UploadView = require('ev-script/views/upload');

    return PickerView.extend({
        initialize: function(options) {
            PickerView.prototype.initialize.call(this, options);
            _.bindAll(this, 'loadVideos', 'loadWorkflows', 'changeLibrary', 'handleSubmit', 'uploadHandler');
            var callback = _.bind(function() {
                this.loadVideos();
            }, this);
            if (this.info.get('ApplicationVersion')) {
                this.$upload = $('<div class="ev-field-actions"><div class="action-upload"><div class="upload-icon"></div><a href="#" class="upload-link" title="Upload"><span>Upload<span></a></div></div>').css('display', 'none');
                this.$('div.ev-filter-block').prepend(this.$upload);
            }
            this.searchView = new SearchView({
                id: this.id + '-search',
                tagName: 'div',
                className: 'ev-search',
                picker: this,
                appId: this.appId,
                callback: callback
            });
            this.$('div.ev-filter-block').prepend(this.searchView.$el);
            this.searchView.render();
            this.typeSelectView = new TypeSelectView({
                id: this.id + '-type-select',
                tagName: 'div',
                className: 'ev-type-select',
                picker: this,
                appId: this.appId,
                callback: callback
            });
            this.$('div.ev-filter-block').prepend(this.typeSelectView.$el);
            this.typeSelectView.render();
            if (this.info.get('ApplicationVersion')) {
                this.unitSelects = new UnitSelectsView({
                    id: this.id + '-unit-selects',
                    tagName: 'div',
                    className: 'ev-unit-selects',
                    picker: this,
                    appId: this.appId
                });
                this.$('div.ev-filter-block').prepend(this.unitSelects.$el);
            }
            this.resultsView = new VideoResultsView({
                el: this.$('div.ev-results'),
                picker: this,
                appId: this.appId
            });
            this.$el.append(this.resultsView.$el);
        },
        events: {
            'click .action-add': 'chooseItem',
            'click .action-upload': 'uploadHandler',
            'change form.unit-selects select.libraries': 'changeLibrary',
            'submit form.unit-selects': 'handleSubmit'
        },
        changeLibrary: function(e) {
            this.loadVideos();
            this.loadWorkflows();
        },
        handleSubmit: function(e) {
            this.loadVideos();
            e.preventDefault();
        },
        uploadHandler: function(e) {
            var uploadView = new UploadView({
                appId: this.appId,
                field: this.field,
                workflows: this.workflows
            });
            e.preventDefault();
        },
        showPicker: function() {
            PickerView.prototype.showPicker.call(this);
            if (this.info.get('ApplicationVersion')) {
                this.unitSelects.loadOrgs();
                this.unitSelects.$('select').filter(':visible').first().focus();
            } else {
                this.searchView.$('input[type="text"]').focus();
                this.loadVideos();
            }
        },
        loadVideos: function() {
            var searchVal = $.trim(this.model.get('search').toLowerCase()),
                sourceId = this.model.get('sourceId'),
                libraryId = this.model.get('libraryId'),
                cacheKey = sourceId + libraryId + searchVal,
                videos = new Videos({}, {
                    sourceId: sourceId,
                    libraryId: libraryId,
                    filterOn: '',
                    filterValue: searchVal,
                    appId: this.appId
                }),
                clearVideosCache = _.bind(function() {
                    videos.clearCache();
                    this.loadVideos();
                }, this);
            videos.fetch({
                picker: this,
                cacheKey: cacheKey,
                success: _.bind(function(collection, response, options) {
                    var totalRecords = collection.totalResults = parseInt(response.Pager.TotalRecords, 10);
                    var size = _.size(response.Data);
                    if (size === totalRecords) {
                        collection.hasMore = false;
                    } else {
                        collection.hasMore = true;
                        collection.pageIndex += 1;
                    }
                    this.resultsView.collection = collection;
                    this.resultsView.render();
                }, this),
                error: _.bind(function(collection, xhr, options) {
                    this.ajaxError(xhr, _.bind(function() {
                        this.loadVideos();
                    }, this));
                }, this)
            });
            this.appEvents.off('fileUploaded').on('fileUploaded', clearVideosCache);
        },
        loadWorkflows: function() {
            this.workflows = new MediaWorkflows({}, {
                appId: this.appId
            });
            // FIXME - add libraryId (as with playlists)
            this.workflows.filterValue = this.model.get('libraryId');
            this.workflows.fetch({
                cacheKey: this.workflows.filterValue,
                success: _.bind(function(collection, response, options) {
                    if (!collection.isEmpty()) {
                        this.$upload.css('display', 'inline-block');
                    } else {
                        this.$upload.css('display', 'none');
                    }
                }, this),
                error: _.bind(function(collection, xhr, options) {
                    this.ajaxError(xhr, _.bind(function() {
                        this.loadWorkflows();
                    }, this));
                }, this),
                reset: true
            });
        }
    });

});

define('ev-script/views/settings',['require','underscore','ev-script/views/base','jquery-ui'],function(require) {

    

    var _ = require('underscore'),
        BaseView = require('ev-script/views/base');

    require('jquery-ui');

    return BaseView.extend({
        initialize: function(options) {
            BaseView.prototype.initialize.call(this, options);
            _.bindAll(this, 'show', 'cancelHandler', 'submitHandler');
            this.field = options.field;
        },
        events: {
            'submit': 'submitHandler',
            'click input.action-cancel': 'cancelHandler'
        },
        show: function() {
            this.render();
            this.$el.dialog('open');
        },
        cancelHandler: function(e) {
            this.$el.dialog('close');
            e.preventDefault();
        },
        submitHandler: function(e) {
            this.updateModel();
            this.$el.dialog('close');
            e.preventDefault();
        },
        // Override me
        updateModel: function() {}
    });

});

define('text!ev-script/templates/video-settings.html',[],function () { return '<form>\n    <fieldset>\n        <div class="fieldWrap">\n            <label for="size">Size</label>\n            <select class="form-select size" id="size" name="size">\n                <option value="original">Original</option>\n            </select>\n        </div>\n        <div class="fieldWrap">\n            <label for="showtitle">Show Title</label>\n            <input id="showtitle" class="form-checkbox" <% if (model.get(\'showtitle\')) { print(\'checked="checked"\'); } %> name="showtitle" type="checkbox"/>\n        </div>\n        <div class="fieldWrap">\n            <label for="autoplay">Auto Play</label>\n            <input id="autoplay" class="form-checkbox" <% if (model.get(\'autoplay\')) { print(\'checked="checked"\'); } %>  name="autoplay" type="checkbox"/>\n        </div>\n        <div class="fieldWrap">\n            <label for="showcaptions">Show Captions</label>\n            <input id="showcaptions" class="form-checkbox" <% if (model.get(\'showcaptions\')) { print(\'checked="checked"\'); } %>  name="showcaptions" type="checkbox"/>\n        </div>\n        <div class="fieldWrap">\n            <label for="hidecontrols">Hide Controls</label>\n            <input id="hidecontrols" class="form-checkbox" <% if (model.get(\'hidecontrols\')) { print(\'checked="checked"\'); } %>  name="hidecontrols" type="checkbox"/>\n        </div>\n        <div class="form-actions">\n            <input type="button" class="form-submit action-cancel" value="Cancel"/>\n            <input type="submit" class="form-submit action-submit" value="Submit"/>\n        </div>\n    </fieldset>\n</form>\n';});

define('text!ev-script/templates/sizes.html',[],function () { return '<% _.each(sizes, function(size) { %>\n    <option value="<%= size %>" <% if (size === target) { print(\'selected="selected"\'); } %>><%= size %></option>\n<% }); %>\n';});

/*global window*/
define('ev-script/views/video-settings',['require','jquery','underscore','ev-script/views/settings','jquery-ui','text!ev-script/templates/video-settings.html','text!ev-script/templates/sizes.html'],function(require) {

    

    var $ = require('jquery'),
        _ = require('underscore'),
        SettingsView = require('ev-script/views/settings');

    require('jquery-ui');

    return SettingsView.extend({
        template: _.template(require('text!ev-script/templates/video-settings.html')),
        sizesTemplate: _.template(require('text!ev-script/templates/sizes.html')),
        initialize: function(options) {
            SettingsView.prototype.initialize.call(this, options);
            this.encoding = options.encoding;
            this.encoding.on('change:id', _.bind(function() {
                this.render();
            }, this));
        },
        updateModel: function() {
            var attrs = {
                'showtitle': this.$('#showtitle').is(':checked'),
                'autoplay': this.$('#autoplay').is(':checked'),
                'showcaptions': this.$('#showcaptions').is(':checked'),
                'hidecontrols': this.$('#hidecontrols').is(':checked')
            };
            var sizeVal = this.$('#size').val();
            if (sizeVal === 'original') {
                // isNew signifies that the encoding hasn't been fetched yet
                if (this.encoding && !this.encoding.isNew()) {
                    _.extend(attrs, {
                        width: this.encoding.getWidth(),
                        height: this.encoding.getHeight()
                    });
                }
            } else {
                var dims = sizeVal.split('x');
                _.extend(attrs, {
                    width: parseInt(dims[0], 10),
                    height: parseInt(dims[1], 10)
                });
            }
            this.field.model.set(attrs);
        },
        renderSize: function() {
            var width = this.field.model.get('width');
            var height = this.field.model.get('height');
            var ratio = 16 / 9;
            var options = ['1280x720', '1024x576', '848x480', '720x405', '640x360', '610x344', '560x315', '480x270', '400x225', '320x180', '240x135', '160x90'];
            if (width && height) {
                ratio = width / height;
            } else if (this.encoding.id) {
                width = this.encoding.getWidth();
                height = this.encoding.getHeight();
                ratio = this.encoding.getRatio();
            }
            // Use a fuzz factor to determine ratio equality since our sizes are not always accurate
            if (Math.ceil(ratio * 10) / 10 === Math.ceil((4 / 3) * 10) / 10) {
                options = ['1280x960', '1024x770', '848x636', '720x540', '640x480', '610x460', '560x420', '480x360', '400x300', '320x240', '240x180', '160x120'];
            }
            var size = width + 'x' + height;
            this.$('.size').append(this.sizesTemplate({
                sizes: options,
                target: size
            }));
        },
        render: function() {
            this.$el.html(this.template({
                model: this.field.model
            }));
            this.renderSize();
            var content = this.field.model.get('content');
            this.$el.dialog({
                title: (content ? content.Title : this.field.model.get('id')),
                modal: true,
                autoOpen: false,
                draggable: false,
                resizable: false,
                dialogClass: 'ev-dialog',
                width: Math.min(340, $(window).width() - this.config.dialogMargin),
                height: Math.min(320, $(window).height() - this.config.dialogMargin)
            });
        }
    });

});

define('text!ev-script/templates/playlist-embed.html',[],function () { return '<iframe src="<%= ensembleUrl %>/app/plugin/embed.aspx?DestinationID=<%= modelId %>"\n        frameborder="0"\n        style="width:800px;height:850px;"\n        allowfullscreen>\n</iframe>\n';});

define('ev-script/views/playlist-embed',['require','underscore','ev-script/views/base','text!ev-script/templates/playlist-embed.html'],function(require) {

    

    var _ = require('underscore'),
        BaseView = require('ev-script/views/base');

    return BaseView.extend({
        template: _.template(require('text!ev-script/templates/playlist-embed.html')),
        initialize: function(options) {
            BaseView.prototype.initialize.call(this, options);
            this.$el.html(this.template({
                modelId: this.model.get('id'),
                ensembleUrl: this.config.ensembleUrl
            }));
        }
    });

});

define('ev-script/views/playlist-preview',['require','ev-script/views/preview','ev-script/views/playlist-embed'],function(require) {

    

    var PreviewView = require('ev-script/views/preview'),
        PlaylistEmbedView = require('ev-script/views/playlist-embed');

    return PreviewView.extend({
        initialize: function(options) {
            PreviewView.prototype.initialize.call(this, options);
        },
        embedClass: PlaylistEmbedView
    });

});

define('text!ev-script/templates/playlist-result.html',[],function () { return '<tr class="<%= (index % 2 ? \'odd\' : \'even\') %>">\n    <td class="content-actions">\n        <div class="action-links">\n            <a class="action-add" href="#" title="Choose <%- item.get(\'Name\') %>" rel="<%= item.get(\'ID\') %>">\n                <span>Choose</span>\n            </a>\n            <a class="action-preview" href="#" title="Preview: <%- item.get(\'Name\') %>" rel="<%= item.get(\'ID\') %>">\n                <span>Preview: <%- item.get(\'Name\') %></span>\n            </a>\n        </div>\n    </td>\n    <td class="content-meta">\n        <span><%- item.get(\'Name\') %></span>\n    </td>\n</tr>\n';});

define('ev-script/views/playlist-results',['require','underscore','jquery','ev-script/views/results','ev-script/models/playlist-settings','ev-script/views/playlist-preview','text!ev-script/templates/playlist-result.html'],function(require) {

    

    var _ = require('underscore'),
        $ = require('jquery'),
        ResultsView = require('ev-script/views/results'),
        PlaylistSettings = require('ev-script/models/playlist-settings'),
        PlaylistPreviewView = require('ev-script/views/playlist-preview');

    return ResultsView.extend({
        modelClass: PlaylistSettings,
        previewClass: PlaylistPreviewView,
        resultTemplate: _.template(require('text!ev-script/templates/playlist-result.html')),
        initialize: function(options) {
            ResultsView.prototype.initialize.call(this, options);
        }
    });

});

define('ev-script/collections/playlists',['require','ev-script/collections/base','ev-script/util/cache'],function(require) {

    

    var BaseCollection = require('ev-script/collections/base'),
        cacheUtil = require('ev-script/util/cache');

    return BaseCollection.extend({
        initialize: function(models, options) {
            BaseCollection.prototype.initialize.call(this, models, options);
            this.libraryId = options.libraryId || '';
            this.filterValue = options.filterValue || '';
            this.pageIndex = 1;
        },
        _cache: function(key, resp) {
            var cachedValue = null,
                user = this.auth.getUser(),
                userCache = user ? cacheUtil.getUserCache(this.config.ensembleUrl, user.id) : null;
            if (userCache) {
                var playlistsCache = userCache.get('playlists');
                if (!playlistsCache) {
                    userCache.set('playlists', playlistsCache = new cacheUtil.Cache());
                }
                cachedValue = playlistsCache[resp ? 'set' : 'get'](key, resp);
            }
            return cachedValue;
        },
        getCached: function(key) {
            return this._cache(key);
        },
        setCached: function(key, resp) {
            return this._cache(key, resp);
        },
        url: function() {
            var api_url = this.config.ensembleUrl + '/api/Playlists',
                sizeParam = 'PageSize=' + this.config.pageSize,
                indexParam = 'PageIndex=' + this.pageIndex,
                url, onParam, valueParam;
            if (this.info.get('ApplicationVersion')) {
                onParam = 'FilterOn=Name';
                valueParam = 'FilterValue=' + encodeURIComponent(this.filterValue);
                url = api_url + '/' + encodeURIComponent(this.libraryId) + '?' + sizeParam + '&' + indexParam + (this.filterValue ? '&' + onParam + '&' + valueParam : '');
            } else {
                onParam = 'FilterOn=LibraryId';
                valueParam = 'FilterValue=' + encodeURIComponent(this.libraryId);
                url = api_url + '?' + sizeParam + '&' + indexParam + '&' + onParam + '&' + valueParam;
            }
            return this.config.urlCallback ? this.config.urlCallback(url) : url;
        }
    });

});

define('ev-script/views/playlist-picker',['require','jquery','underscore','ev-script/views/picker','ev-script/views/unit-selects','ev-script/views/search','ev-script/views/playlist-results','ev-script/collections/playlists'],function(require) {

    

    var $ = require('jquery'),
        _ = require('underscore'),
        PickerView = require('ev-script/views/picker'),
        UnitSelectsView = require('ev-script/views/unit-selects'),
        SearchView = require('ev-script/views/search'),
        PlaylistResultsView = require('ev-script/views/playlist-results'),
        Playlists = require('ev-script/collections/playlists');

    return PickerView.extend({
        initialize: function(options) {
            PickerView.prototype.initialize.call(this, options);
            _.bindAll(this, 'loadPlaylists', 'changeLibrary', 'handleSubmit');
            if (this.info.get('ApplicationVersion')) {
                this.searchView = new SearchView({
                    id: this.id + '-search',
                    tagName: 'div',
                    className: 'ev-search',
                    picker: this,
                    appId: this.appId,
                    callback: _.bind(function() {
                        this.loadPlaylists();
                    }, this)
                });
                this.$('div.ev-filter-block').prepend(this.searchView.$el);
                this.searchView.render();
            }
            this.unitSelects = new UnitSelectsView({
                id: this.id + '-unit-selects',
                tagName: 'div',
                className: 'ev-unit-selects',
                picker: this,
                appId: this.appId
            });
            this.$('div.ev-filter-block').prepend(this.unitSelects.$el);
            this.resultsView = new PlaylistResultsView({
                el: this.$('div.ev-results'),
                picker: this,
                appId: this.appId
            });
            this.$el.append(this.resultsView.$el);
        },
        events: {
            'click a.action-add': 'chooseItem',
            'change form.unit-selects select.libraries': 'changeLibrary',
            'submit form.unit-selects': 'handleSubmit'
        },
        changeLibrary: function(e) {
            this.loadPlaylists();
        },
        handleSubmit: function(e) {
            this.loadPlaylists();
            e.preventDefault();
        },
        showPicker: function() {
            PickerView.prototype.showPicker.call(this);
            this.unitSelects.loadOrgs();
            this.unitSelects.$('select').filter(':visible').first().focus();
        },
        loadPlaylists: function() {
            var searchVal = $.trim(this.model.get('search').toLowerCase()),
                libraryId = this.model.get('libraryId'),
                playlists = new Playlists({}, {
                    libraryId: libraryId,
                    filterValue: searchVal,
                    appId: this.appId
                });
            playlists.fetch({
                picker: this,
                cacheKey: libraryId + searchVal,
                success: _.bind(function(collection, response, options) {
                    var totalRecords = collection.totalResults = parseInt(response.Pager.TotalRecords, 10);
                    var size = _.size(response.Data);
                    if(size === totalRecords) {
                        collection.hasMore = false;
                    } else {
                        collection.hasMore = true;
                        collection.pageIndex += 1;
                    }
                    this.resultsView.collection = collection;
                    this.resultsView.render();
                }, this),
                error: _.bind(function(collection, xhr, options) {
                    this.ajaxError(xhr, _.bind(function() {
                        this.loadPlaylists();
                    }, this));
                }, this)
            });
        }
    });

});

define('text!ev-script/templates/playlist-settings.html',[],function () { return '<h3>TODO</h3>\n<p>\n    <%- json %>\n</p>\n';});

define('ev-script/views/playlist-settings',['require','underscore','ev-script/views/settings','jquery-ui','text!ev-script/templates/playlist-settings.html'],function(require) {

    

    var _ = require('underscore'),
        SettingsView = require('ev-script/views/settings');

    require('jquery-ui');

    return SettingsView.extend({
        template: _.template(require('text!ev-script/templates/playlist-settings.html')),
        initialize: function(options) {
            SettingsView.prototype.initialize.call(this, options);
        },
        render: function() {
            // TODO - fix this template when we have playlist settings implemented
            this.$el.html(this.template({
                json: JSON.stringify(this.field.model.toJSON())
            }));
            this.$el.dialog({
                title: 'Playlist Embed Settings',
                modal: true,
                autoOpen: false,
                dialogClass: 'ev-dialog'
            });
        }
    });

});

define('text!ev-script/templates/field.html',[],function () { return '<div class="logo">\n    <a target="_blank" href="<%= ensembleUrl %>"><span>Ensemble Logo</span></a>\n</div>\n<% if (modelId) { %>\n    <% if (thumbnailUrl) { %>\n        <div class="thumbnail">\n            <img alt="Media thumbnail" src="<%= thumbnailUrl %>"/>\n        </div>\n    <% } %>\n    <div class="title"><%- name %></div>\n    <div class="ev-field-actions">\n        <a href="#" class="action-choose" title="Change <%= label %>"><span>Change <%= label %><span></a>\n        <a href="#" class="action-preview" title="Preview: <%- name %>"><span>Preview: <%- name %><span></a>\n        <!-- TODO - temporarily disabled playlist settings until it is implemented -->\n        <% if (type === \'video\') { %>\n            <a href="#" class="action-options" title="<%= label %> Embed Options"><span><%= label %> Embed Options<span></a>\n        <% } %>\n        <a href="#" class="action-remove" title="Remove <%= label %>"><span>Remove <%= label %><span></a>\n    </div>\n<% } else { %>\n    <div class="title"><em>Add <%= label %></em></div>\n    <div class="ev-field-actions">\n        <a href="#" class="action-choose" title="Choose <%= label %>"><span>Choose <%= label %><span></a>\n    </div>\n<% } %>\n';});

define('ev-script/views/field',['require','jquery','underscore','ev-script/views/base','ev-script/models/video-settings','ev-script/models/playlist-settings','ev-script/views/video-picker','ev-script/views/video-settings','ev-script/views/video-preview','ev-script/models/video-encoding','ev-script/views/playlist-picker','ev-script/views/playlist-settings','ev-script/views/playlist-preview','text!ev-script/templates/field.html'],function(require) {

    

    var $ = require('jquery'),
        _ = require('underscore'),
        BaseView = require('ev-script/views/base'),
        VideoSettings = require('ev-script/models/video-settings'),
        PlaylistSettings = require('ev-script/models/playlist-settings'),
        VideoPickerView = require('ev-script/views/video-picker'),
        VideoSettingsView = require('ev-script/views/video-settings'),
        VideoPreviewView = require('ev-script/views/video-preview'),
        VideoEncoding = require('ev-script/models/video-encoding'),
        PlaylistPickerView = require('ev-script/views/playlist-picker'),
        PlaylistSettingsView = require('ev-script/views/playlist-settings'),
        PlaylistPreviewView = require('ev-script/views/playlist-preview');

    /*
     * View for our field (element that we set with the selected content identifier)
     */
    return BaseView.extend({
        template: _.template(require('text!ev-script/templates/field.html')),
        initialize: function(options) {
            BaseView.prototype.initialize.call(this, options);
            _.bindAll(this, 'chooseHandler', 'optionsHandler', 'removeHandler', 'previewHandler');
            this.$field = options.$field;
            this.showChoose = true;
            var pickerOptions = {
                id: this.id + '-picker',
                tagName: 'div',
                className: 'ev-' + this.model.get('type') + '-picker',
                field: this,
                appId: this.appId
            };
            var settingsOptions = {
                id: this.id + '-settings',
                tagName: 'div',
                className: 'ev-settings',
                field: this,
                appId: this.appId
            };
            if (this.model instanceof VideoSettings) {
                this.modelClass = VideoSettings;
                this.pickerClass = VideoPickerView;
                this.settingsClass = VideoSettingsView;
                this.previewClass = VideoPreviewView;
                this.encoding = new VideoEncoding({}, {
                    appId: this.appId
                });
                if (!this.model.isNew()) {
                    this.encoding.set({
                        fetchId: this.model.id
                    });
                    this.encoding.fetch();
                }
                this.model.on('change:id', _.bind(function() {
                    // Only fetch encoding if identifier is set
                    if (this.model.id) {
                        this.encoding.set({
                            fetchId: this.model.id
                        });
                        this.encoding.fetch({
                            success: _.bind(function(response) {
                                this.model.set({
                                    width: this.encoding.getWidth(),
                                    height: this.encoding.getHeight()
                                });
                            }, this)
                        });
                    } else {
                        this.encoding.clear();
                    }
                }, this));
                _.extend(settingsOptions, {
                    encoding: this.encoding
                });
            } else if (this.model instanceof PlaylistSettings) {
                this.modelClass = PlaylistSettings;
                this.pickerClass = PlaylistPickerView;
                this.settingsClass = PlaylistSettingsView;
                this.previewClass = PlaylistPreviewView;
            }
            this.picker = new this.pickerClass(_.extend({}, pickerOptions, {
                // We don't want to modify field model until we actually pick a new video...so use a copy as our current model
                model: new this.modelClass(this.model.toJSON()),
            }));
            this.settings = new this.settingsClass(settingsOptions);
            this.$field.after(this.picker.$el);
            this.renderActions();
            this.model.on('change', _.bind(function() {
                if (!this.model.isNew()) {
                    var json = this.model.toJSON();
                    this.$field.val(JSON.stringify(json));
                    this.appEvents.trigger('fieldUpdated', this.$field, json);
                    this.renderActions();
                }
            }, this));
            this.appEvents.on('showPicker', function(fieldId) {
                if (this.id === fieldId) {
                    this.$('.action-choose').hide();
                    this.showChoose = false;
                    // We only want one picker showing at a time so notify all fields to hide them (unless it's ours)
                    if (this.config.hidePickers) {
                        this.appEvents.trigger('hidePickers', this.id);
                    }
                }
            }, this);
            this.appEvents.on('hidePicker', function(fieldId) {
                if (this.id === fieldId) {
                    this.$('.action-choose').show();
                    this.showChoose = true;
                }
            }, this);
            this.appEvents.on('hidePickers', function(fieldId) {
                // When the picker for our field is hidden we need need to show our 'Choose' button
                if (!fieldId || (this.id !== fieldId)) {
                    this.$('.action-choose').show();
                    this.showChoose = true;
                }
            }, this);
        },
        events: {
            'click .action-choose': 'chooseHandler',
            'click .action-preview': 'previewHandler',
            'click .action-options': 'optionsHandler',
            'click .action-remove': 'removeHandler'
        },
        chooseHandler: function(e) {
            this.appEvents.trigger('showPicker', this.id);
            e.preventDefault();
        },
        optionsHandler: function(e) {
            this.settings.show();
            e.preventDefault();
        },
        removeHandler: function(e) {
            this.model.clear();
            this.$field.val('');
            // Silent here because we don't want to trigger our change handler above
            // (which would set the field value to our model defaults)
            this.model.set(this.model.defaults, {
                silent: true
            });
            this.appEvents.trigger('fieldUpdated', this.$field);
            this.renderActions();
            e.preventDefault();
        },
        previewHandler: function(e) {
            var element = e.currentTarget;
            var previewView = new this.previewClass({
                el: element,
                encoding: this.encoding,
                model: this.model,
                appId: this.appId
            });
            e.preventDefault();
        },
        renderActions: function() {
            var ensembleUrl = this.config.ensembleUrl, name, label, type, thumbnailUrl;
            if (this.model instanceof VideoSettings) {
                label = 'Media';
                type = 'video';
            } else {
                label = 'Playlist';
                type = 'playlist';
            }
            if (this.model.id) {
                name = this.model.id;
                var content = this.model.get('content');
                if (content) {
                    name = content.Name || content.Title;
                    // Validate thumbnailUrl as it could potentially have been modified and we want to protect against XSRF
                    // (a GET shouldn't have side effects...but make sure we actually have a thumbnail url just in case)
                    var re = new RegExp('^' + ensembleUrl.toLocaleLowerCase() + '\/app\/assets\/');
                    if (content.ThumbnailUrl && re.test(content.ThumbnailUrl.toLocaleLowerCase())) {
                        thumbnailUrl = content.ThumbnailUrl;
                    }
                }
            }
            if (!this.$actions) {
                this.$actions = $('<div class="ev-field"/>');
                this.$field.after(this.$actions);
            }
            this.$actions.html(this.template({
                ensembleUrl: ensembleUrl,
                modelId: this.model.id,
                label: label,
                type: type,
                name: name,
                thumbnailUrl: thumbnailUrl
            }));
            // If our picker is shown, hide our 'Choose' button
            if (!this.showChoose) {
                this.$('.action-choose').hide();
            }
        }
    });

});

define('ev-script/models/app-info',['require','underscore','ev-script/models/base'],function(require) {

    

    var _ = require('underscore'),
        BaseModel = require('ev-script/models/base');

    return BaseModel.extend({
        initialize: function(attributes, options) {
            BaseModel.prototype.initialize.call(this, attributes, options);
            this.requiresAuth = false;
        },
        url: function() {
            var url = this.config.ensembleUrl + '/api/Info';
            return this.config.urlCallback ? this.config.urlCallback(url) : url;
        },
        parse: function(response) {
            return response;
        }
    });

});

define('ev-script/models/current-user',['require','underscore','ev-script/models/base'],function(require) {

    

    var _ = require('underscore'),
        BaseModel = require('ev-script/models/base');

    return BaseModel.extend({
        idAttribute: 'ID',
        initialize: function(attributes, options) {
            BaseModel.prototype.initialize.call(this, attributes, options);
            // The API actually does require authentication...but we don't want
            // special handling
            this.requiresAuth = false;
        },
        url: function() {
            var url = this.config.ensembleUrl + '/api/CurrentUser';
            return this.config.urlCallback ? this.config.urlCallback(url) : url;
        },
        parse: function(response) {
            return response.Data[0];
        }
    });

});

define('ev-script/auth/base/auth',['require','jquery','underscore','backbone','ev-script/util/events','ev-script/util/cache','ev-script/models/current-user'],function(require) {

    

    var $ = require('jquery'),
        _ = require('underscore'),
        Backbone = require('backbone'),
        eventsUtil = require('ev-script/util/events'),
        cacheUtil = require('ev-script/util/cache'),
        CurrentUser = require('ev-script/models/current-user'),
        BaseAuth = function(appId) {
            _.bindAll(this, 'getUser', 'login', 'logout', 'isAuthenticated', 'handleUnauthorized');
            this.appId = appId;
            this.config = cacheUtil.getAppConfig(appId);
            this.info = cacheUtil.getAppInfo(appId);
            this.globalEvents = eventsUtil.getEvents('global');
            this.appEvents = eventsUtil.getEvents(appId);
            this.user = null;
            this.appEvents.on('appLoaded', function() {
                this.fetchUser();
            }, this);
        };

    // Reusing Backbone's object model for extension
    BaseAuth.extend = Backbone.Model.extend;

    _.extend(BaseAuth.prototype, {
        fetchUser: function() {
            var currentUser = new CurrentUser({}, {
                appId: this.appId
            });
            return currentUser.fetch({
                success: _.bind(function(model, response, options) {
                    this.user = model;
                    this.globalEvents.trigger('loggedIn', this.config.ensembleUrl);
                }, this),
                error: _.bind(function(model, response, options) {
                    this.user = null;
                    this.globalEvents.trigger('loggedOut', this.config.ensembleUrl);
                }, this)
            }).promise();
        },
        getUser: function() {
            return this.user;
        },
        // Return failed promise...subclasses should override
        login: function(loginInfo) {
            return $.Deferred().reject().promise();
        },
        // Return failed promise...subclasses should override
        logout: function() {
            return $.Deferred().reject().promise();
        },
        isAuthenticated: function() {
            return this.user != null;
        },
        handleUnauthorized: function(element, authCallback) {}
    });

    return BaseAuth;

});

define('text!ev-script/auth/basic/template.html',[],function () { return '<div class="logo"></div>\n<form>\n    <fieldset>\n        <div class="fieldWrap">\n            <label for="username">Username</label>\n            <input id="username" name="username" class="form-text"type="text"/>\n        </div>\n        <div class="fieldWrap">\n            <label for="password">Password</label>\n            <input id="password" name="password" class="form-text"type="password"/>\n        </div>\n        <div class="form-actions">\n            <label></label>\n            <input type="submit" class="form-submit action-submit" value="Submit"/>\n        </div>\n    </fieldset>\n</form>\n';});

/*global window*/
define('ev-script/auth/basic/view',['require','exports','module','jquery','underscore','backbone','ev-script/util/cache','ev-script/util/events','jquery.cookie','jquery-ui','text!ev-script/auth/basic/template.html'],function(require, template) {

    

    var $ = require('jquery'),
        _ = require('underscore'),
        Backbone = require('backbone'),
        cacheUtil = require('ev-script/util/cache'),
        eventsUtil = require('ev-script/util/events');

    require('jquery.cookie');
    require('jquery-ui');

    return Backbone.View.extend({
        template: _.template(require('text!ev-script/auth/basic/template.html')),
        initialize: function(options) {
            this.appId = options.appId;
            this.config = cacheUtil.getAppConfig(this.appId);
            this.appEvents = eventsUtil.getEvents(this.appId);
            this.submitCallback = options.submitCallback || function() {};
            this.auth = options.auth;
        },
        render: function() {
            var html = this.template();
            this.$dialog = $('<div class="ev-auth"></div>');
            this.$el.after(this.$dialog);
            this.$dialog.dialog({
                title: 'Ensemble Video Login - ' + this.config.ensembleUrl,
                modal: true,
                draggable: false,
                resizable: false,
                width: Math.min(540, $(window).width() - this.config.dialogMargin),
                height: Math.min(250, $(window).height() - this.config.dialogMargin),
                dialogClass: 'ev-dialog',
                create: _.bind(function(event, ui) {
                    this.$dialog.html(html);
                }, this),
                close: _.bind(function(event, ui) {
                    this.$dialog.dialog('destroy').remove();
                    this.appEvents.trigger('hidePickers');
                }, this)
            });
            $('form', this.$dialog).submit(_.bind(function(e) {
                var $form = $(e.target);
                var username = $('#username', $form).val();
                var password = $('#password', $form).val();
                if (username && password) {
                    this.auth.login({
                        username: username,
                        password: password
                    })
                    .always(this.submitCallback);
                    this.$dialog.dialog('destroy').remove();
                }
                e.preventDefault();
            }, this));
        }
    });

});

define('ev-script/auth/basic/auth',['require','jquery','underscore','backbone','ev-script/auth/base/auth','ev-script/auth/basic/view','ev-script/collections/organizations'],function(require) {

    

    var $ = require('jquery'),
        _ = require('underscore'),
        Backbone = require('backbone'),
        BaseAuth = require('ev-script/auth/base/auth'),
        AuthView = require('ev-script/auth/basic/view'),
        Organizations = require('ev-script/collections/organizations'),
        // Note: This isn't really basic authentication at all...we just set
        // cookies containing credentials to be handled by a proxy.  The proxy
        // uses these to forward our request with a basic auth header.
        BasicAuth = BaseAuth.extend({
            constructor: function(appId) {
                BasicAuth.__super__.constructor.call(this, appId);
            },
            fetchUser: function() {
                // Hack to handle legacy (pre-3.6) API which doesn't have a
                // currentUser endpoint.  See if we can successfully query orgs
                // instead (probably least expensive due to minimal data) to see
                // if valid credentials are set, then use a randomly generated
                // user id
                if (this.info.get('ApplicationVersion')) {
                    return BasicAuth.__super__.fetchUser.call(this);
                } else {
                    var orgs = new Organizations({}, {
                        appId: this.appId
                    });
                    // Don't want special treatment of failure due to
                    // authentication in this case
                    orgs.requiresAuth = false;
                    return orgs.fetch({
                        success: _.bind(function(collection, response, options) {
                            this.user = new Backbone.Model({
                                id: Math.floor(Math.random() * 10000000000000001).toString(16)
                            });
                            this.globalEvents.trigger('loggedIn', this.config.ensembleUrl);
                        }, this),
                        error: _.bind(function(collection, xhr, options) {
                            this.user = null;
                            this.globalEvents.trigger('loggedOut', this.config.ensembleUrl);
                        }, this)
                    }).promise();
                }
            },
            login: function(loginInfo) {
                var cookieOptions = { path: this.config.authPath };
                $.cookie(this.config.ensembleUrl + '-user', loginInfo.username, _.extend({}, cookieOptions));
                $.cookie(this.config.ensembleUrl + '-pass', loginInfo.password, _.extend({}, cookieOptions));
                return this.fetchUser();
            },
            logout: function() {
                var deferred = $.Deferred();
                var cookieOptions = { path: this.config.authPath };
                $.cookie(this.config.ensembleUrl + '-user', null, _.extend({}, cookieOptions));
                $.cookie(this.config.ensembleUrl + '-pass', null, _.extend({}, cookieOptions));
                this.user = null;
                this.globalEvents.trigger('loggedOut', this.config.ensembleUrl);
                deferred.resolve();
                return deferred.promise();
            },
            handleUnauthorized: function(element, authCallback) {
                this.logout();
                var authView = new AuthView({
                    el: element,
                    submitCallback: authCallback,
                    appId: this.appId,
                    auth: this
                });
                authView.render();
            }
        });

    return BasicAuth;
});

define('text!ev-script/auth/forms/template.html',[],function () { return '<div class="logo"></div>\n<form>\n    <fieldset>\n        <div class="fieldWrap">\n            <label for="username">Username</label>\n            <input id="username" name="username" class="form-text" type="text"/>\n        </div>\n        <div class="fieldWrap">\n            <label for="password">Password</label>\n            <input id="password" name="password" class="form-text" type="password"/>\n        </div>\n        <div class="fieldWrap">\n            <label for="domain">Domain</label>\n            <select id="domain" name="domain" class="form-select"></select>\n        </div>\n        <div class="fieldWrap">\n            <label for="remember">Remember Me</label>\n            <input id="remember" name="remember" type="checkbox"></input>\n        </div>\n        <div class="form-actions">\n            <label></label>\n            <input type="submit" class="form-submit action-submit" value="Submit"/>\n        </div>\n    </fieldset>\n</form>\n';});

/*global window*/
define('ev-script/auth/forms/view',['require','exports','module','jquery','underscore','backbone','ev-script/util/cache','ev-script/util/events','jquery.cookie','jquery-ui','text!ev-script/auth/forms/template.html','text!ev-script/templates/options.html'],function(require, template) {

    

    var $ = require('jquery'),
        _ = require('underscore'),
        Backbone = require('backbone'),
        cacheUtil = require('ev-script/util/cache'),
        eventsUtil = require('ev-script/util/events');

    require('jquery.cookie');
    require('jquery-ui');

    return Backbone.View.extend({
        template: _.template(require('text!ev-script/auth/forms/template.html')),
        optionsTemplate: _.template(require('text!ev-script/templates/options.html')),
        initialize: function(options) {
            this.appId = options.appId;
            this.config = cacheUtil.getAppConfig(this.appId);
            this.appEvents = eventsUtil.getEvents(this.appId);
            this.submitCallback = options.submitCallback || function() {};
            this.auth = options.auth;
        },
        render: function() {
            var $html = $(this.template());
            var $select = $('#domain', $html).append(this.optionsTemplate({
                collection: this.collection,
                // FIXME - need to know the default to select here
                selectedId: null
            }));
            this.$dialog = $('<div class="ev-auth"></div>');
            this.$el.after(this.$dialog);
            this.$dialog.dialog({
                title: 'Ensemble Video Login - ' + this.config.ensembleUrl,
                modal: true,
                draggable: false,
                resizable: false,
                width: Math.min(540, $(window).width() - this.config.dialogMargin),
                height: Math.min(250, $(window).height() - this.config.dialogMargin),
                dialogClass: 'ev-dialog',
                create: _.bind(function(event, ui) {
                    this.$dialog.html($html);
                }, this),
                close: _.bind(function(event, ui) {
                    this.$dialog.dialog('destroy').remove();
                    this.appEvents.trigger('hidePickers');
                }, this)
            });
            $('form', this.$dialog).submit(_.bind(function(e) {
                var $form = $(e.target);
                var username = $('#username', $form).val();
                var password = $('#password', $form).val();
                if (username && password) {
                    this.auth.login({
                        username: username,
                        password: password,
                        authSourceId: $('#domain :selected', $form).val(),
                        persist: $('#remember', $form).is(':checked')
                    }).then(_.bind(function() {
                        this.$dialog.dialog('destroy').remove();
                        this.submitCallback();
                    }, this));
                }
                e.preventDefault();
            }, this));
        }
    });

});

define('ev-script/collections/identity-providers',['require','ev-script/collections/base','ev-script/util/cache'],function(require) {

    

    var BaseCollection = require('ev-script/collections/base'),
        cacheUtil = require('ev-script/util/cache'),
        cached = new cacheUtil.Cache();

    return BaseCollection.extend({
        initialize: function(models, options) {
            BaseCollection.prototype.initialize.call(this, models, options);
            this.requiresAuth = false;
        },
        getCached: function(key) {
            return cached.get(this.config.ensembleUrl);
        },
        setCached: function(key, resp) {
            return cached.set(this.config.ensembleUrl, resp);
        },
        url: function() {
            var api_url = this.config.ensembleUrl + '/api/IdentityProviders';
            return this.config.urlCallback ? this.config.urlCallback(api_url) : api_url;
        }
    });

});

define('ev-script/auth/forms/auth',['require','jquery','underscore','ev-script/auth/base/auth','ev-script/models/current-user','ev-script/auth/forms/view','ev-script/collections/identity-providers'],function(require) {

    

    var $ = require('jquery'),
        _ = require('underscore'),
        BaseAuth = require('ev-script/auth/base/auth'),
        CurrentUser = require('ev-script/models/current-user'),
        AuthView = require('ev-script/auth/forms/view'),
        IdentityProviders = require('ev-script/collections/identity-providers'),
        FormsAuth = BaseAuth.extend({
            constructor: function(appId) {
                BaseAuth.prototype.constructor.call(this, appId);
                this.identityProviders = new IdentityProviders({}, {
                    appId: appId
                });
                this.asPromise = this.identityProviders.fetch();
            },
            login: function(loginInfo) {
                var url = this.config.ensembleUrl + '/api/Login';
                return $.ajax({
                    url: this.config.urlCallback ? this.config.urlCallback(url) : url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        user: loginInfo.username,
                        password: loginInfo.password,
                        identityProviderId: loginInfo.authSourceId,
                        persist: loginInfo.persist
                    },
                    xhrFields: {
                        withCredentials: true
                    },
                    success: _.bind(function(data, status, xhr) {
                        this.user = new CurrentUser(data.Data[0], {
                            appId: this.appId
                        });
                        this.globalEvents.trigger('loggedIn', this.config.ensembleUrl);
                    }, this)
                }).promise();
            },
            logout: function() {
                var url = this.config.ensembleUrl + '/api/Logout';
                return $.ajax({
                    url: this.config.urlCallback ? this.config.urlCallback(url) : url,
                    type: 'POST',
                    xhrFields: {
                        withCredentials: true
                    },
                    success: _.bind(function(data, status, xhr) {
                        this.user = null;
                        this.globalEvents.trigger('loggedOut', this.config.ensembleUrl);
                    }, this)
                }).promise();
            },
            handleUnauthorized: function(element, authCallback) {
                this.user = null;
                this.globalEvents.trigger('loggedOut', this.config.ensembleUrl);
                this.asPromise.done(_.bind(function() {
                    var authView = new AuthView({
                        el: element,
                        submitCallback: authCallback,
                        appId: this.appId,
                        auth: this,
                        collection: this.identityProviders
                    });
                    authView.render();
                }, this));
            }
        });

    return FormsAuth;

});

define('text!ev-script/auth/none/template.html',[],function () { return '<div class="logo"></div>\n<form>\n    <h3>You are unauthorized to access this content.</h3>\n</form>\n';});

/*global window*/
define('ev-script/auth/none/view',['require','exports','module','jquery','underscore','backbone','ev-script/util/cache','ev-script/util/events','jquery-ui','text!ev-script/auth/none/template.html'],function(require, template) {

    

    var $ = require('jquery'),
        _ = require('underscore'),
        Backbone = require('backbone'),
        cacheUtil = require('ev-script/util/cache'),
        eventsUtil = require('ev-script/util/events');

    require('jquery-ui');

    return Backbone.View.extend({
        template: _.template(require('text!ev-script/auth/none/template.html')),
        initialize: function(options) {
            this.appId = options.appId;
            this.config = cacheUtil.getAppConfig(this.appId);
            this.appEvents = eventsUtil.getEvents(this.appId);
        },
        render: function() {
            var $html = $(this.template());
            this.$dialog = $('<div class="ev-auth"></div>');
            this.$el.after(this.$dialog);
            this.$dialog.dialog({
                title: 'Ensemble Video Login - ' + this.config.ensembleUrl,
                modal: true,
                draggable: false,
                resizable: false,
                width: Math.min(540, $(window).width() - this.config.dialogMargin),
                height: Math.min(250, $(window).height() - this.config.dialogMargin),
                dialogClass: 'ev-dialog',
                create: _.bind(function(event, ui) {
                    this.$dialog.html($html);
                }, this),
                close: _.bind(function(event, ui) {
                    this.$dialog.dialog('destroy').remove();
                    this.appEvents.trigger('hidePickers');
                }, this)
            });
        }
    });

});

define('ev-script/auth/none/auth',['require','jquery','underscore','ev-script/auth/base/auth','ev-script/auth/none/view'],function(require) {

    

    var $ = require('jquery'),
        _ = require('underscore'),
        BaseAuth = require('ev-script/auth/base/auth'),
        AuthView = require('ev-script/auth/none/view'),
        // This auth type doesn't actually prompt to authenticate.  Rather, it
        // displays an authentication warning.
        NoneAuth = BaseAuth.extend({
            constructor: function(appId) {
                BaseAuth.prototype.constructor.call(this, appId);
            },
            handleUnauthorized: function(element, authCallback) {
                this.user = null;
                this.globalEvents.trigger('loggedOut', this.config.ensembleUrl);
                var authView = new AuthView({
                    el: element,
                    submitCallback: authCallback,
                    appId: this.appId,
                    auth: this,
                    collection: this.identityProviders
                });
                authView.render();
            }
        });

    return NoneAuth;

});

define('ev-script',['require','backbone','underscore','jquery','ev-script/models/video-settings','ev-script/models/playlist-settings','ev-script/views/field','ev-script/views/video-embed','ev-script/views/playlist-embed','ev-script/models/app-info','ev-script/auth/basic/auth','ev-script/auth/forms/auth','ev-script/auth/none/auth','ev-script/util/events','ev-script/util/cache'],function(require) {

    

    var Backbone = require('backbone'),
        _ = require('underscore'),
        $ = require('jquery'),
        VideoSettings = require('ev-script/models/video-settings'),
        PlaylistSettings = require('ev-script/models/playlist-settings'),
        FieldView = require('ev-script/views/field'),
        VideoEmbedView = require('ev-script/views/video-embed'),
        PlaylistEmbedView = require('ev-script/views/playlist-embed'),
        AppInfo = require('ev-script/models/app-info'),
        BasicAuth = require('ev-script/auth/basic/auth'),
        FormsAuth = require('ev-script/auth/forms/auth'),
        NoneAuth = require('ev-script/auth/none/auth'),
        eventsUtil = require('ev-script/util/events'),
        cacheUtil = require('ev-script/util/cache');

    var EnsembleApp = function(appOptions) {

        // Lame unique id generator
        var appId = Math.floor(Math.random() * 10000000000000001).toString(16);

        // Get or create a new cache to store objects specific to EV
        // installation but common across 'app' instances (e.g. videos
        // accessible by a given user).
        var evCache = cacheUtil.caches.get(appOptions.ensembleUrl);
        if (!evCache) {
            evCache = cacheUtil.caches.set(appOptions.ensembleUrl, new cacheUtil.Cache());
        }

        var defaults = {
            // Application root of the EV installation.
            ensembleUrl: '',
            // Cookie path.
            authPath: '',
            // Models/collections will typically fetch directly from the API,
            // but this method is called in case that needs to be overridden
            // (e.g. in cross-domain scenarios where we're using a proxy).
            urlCallback: function(url) { return url; },
            // Number of results to fetch at a time from the server (page size).
            pageSize: 100,
            // The height of our scroll loader.
            scrollHeight: 600,
            // In scenarios where we have multiple fields on a page we want to
            // automatically hide inactive pickers to preserve screen real
            // estate.  Set to false to disable.
            hidePickers: true,
            // The difference between window dimensions and maximum dialog size.
            dialogMargin: 40,
            // This can be 'forms', 'basic' (default) or 'none' (in which case
            // an access denied message is displayed and user is not prompted
            // to authenticate).
            // authType: 'forms'
            // Location for plupload flash runtime
            pluploadFlashPath: ''
        };

        // Add our configuration to the app cache...this is specific to this
        // 'app' instance.  There may be multiple instances on a single page w/
        // unique settings.
        var config = cacheUtil.setAppConfig(appId, _.extend({}, defaults, appOptions));

        // Create an event aggregator specific to our app
        eventsUtil.initEvents(appId);
        this.appEvents = eventsUtil.getEvents(appId);
        // eventsUtil also provides us with a global event aggregator for events
        // that span app instances
        this.globalEvents = eventsUtil.getEvents();

        // Features depend on info asynchronously retreived below...so leverage
        // promises to coordinate loading
        var loading = $.Deferred();
        _.extend(this, loading.promise());

        var info = new AppInfo({}, {
            appId: appId
        });
        cacheUtil.setAppInfo(appId, info);
        info.fetch({})
        .always(_.bind(function() {
            // This is kinda lazy...but this will only be set in 3.6+ versions
            // so we don't actually need to check the version number
            if (!info.get('ApplicationVersion') && config.authType === 'forms') {
                loading.reject('Configured to use forms authentication against a pre-3.6 API.');
            } else {
                // This will initialize and cache an auth object for our app
                var auth;
                switch (config.authType) {
                    case 'forms':
                        auth = new FormsAuth(appId);
                        break;
                    case 'none':
                        auth = new NoneAuth(appId);
                        break;
                    default:
                        auth = new BasicAuth(appId);
                        break;
                }
                cacheUtil.setAppAuth(appId, auth);

                // TODO - document and add some flexibility to params (e.g. in addition
                // to selector allow element or object).
                this.handleField = function(fieldWrap, settingsModel, fieldSelector) {
                    var $field = $(fieldSelector, fieldWrap);
                    var fieldView = new FieldView({
                        id: fieldWrap.id || appId,
                        el: fieldWrap,
                        model: settingsModel,
                        $field: $field,
                        appId: appId
                    });
                };

                // TODO - document.  See handleField comment too.
                this.handleEmbed = function(embedWrap, settingsModel) {
                    if (settingsModel instanceof VideoSettings) {
                        var videoEmbed = new VideoEmbedView({
                            el: embedWrap,
                            model: settingsModel,
                            appId: appId
                        });
                    } else {
                        var playlistEmbed = new PlaylistEmbedView({
                            el: embedWrap,
                            model: settingsModel,
                            appId: appId
                        });
                    }
                };

                this.appEvents.trigger('appLoaded');
                loading.resolve();
            }
        }, this));
    };

    return {
        VideoSettings: VideoSettings,
        PlaylistSettings: PlaylistSettings,
        EnsembleApp: EnsembleApp
    };

});
    // Register in the values from the outer closure for common dependencies
    // as local almond modules
    define('jquery', function () {
        return $ || jQuery;
    });
    define('underscore', function () {
        return _;
    });
    define('backbone', ['jquery', 'underscore'], function () {
        return Backbone;
    });
    define('jquery-ui', ['jquery'], function() {});
    define('jquery.cookie', ['jquery'], function() {});
    define('plupload', function() {});
    define('jquery.plupload.queue', ['jquery', 'plupload'], function() {});

    // Use almond's special top-level, synchronous require to trigger factory
    // functions, get the final module value, and export it as the public
    // value.
    return require('ev-script');
}));
