(function (factory) {
    if (typeof define === "function" && define.amd) {

        // AMD. Register as an anonymous module.
        define(["jquery"], factory);
    } else {

        // Browser globals
        factory(jQuery);
    }
}(function ($) {

    $.ui = $.ui || {};

    var version = $.ui.version = "1.12.1";


    /*!
     * jQuery UI Widget 1.12.1
     * http://jqueryui.com
     *
     * Copyright jQuery Foundation and other contributors
     * Released under the MIT license.
     * http://jquery.org/license
     */

//>>label: Widget
//>>group: Core
//>>description: Provides a factory for creating stateful widgets with a common API.
//>>docs: http://api.jqueryui.com/jQuery.widget/
//>>demos: http://jqueryui.com/widget/


    var widgetUuid = 0;
    var widgetSlice = Array.prototype.slice;

    $.cleanData = (function (orig) {
        return function (elems) {
            var events, elem, i;
            for (i = 0; (elem = elems[i]) != null; i++) {
                try {

                    // Only trigger remove when necessary to save time
                    events = $._data(elem, "events");
                    if (events && events.remove) {
                        $(elem).triggerHandler("remove");
                    }

                    // Http://bugs.jquery.com/ticket/8235
                } catch (e) {
                }
            }
            orig(elems);
        };
    })($.cleanData);

    $.widget = function (name, base, prototype) {
        var existingConstructor, constructor, basePrototype;

        // ProxiedPrototype allows the provided prototype to remain unmodified
        // so that it can be used as a mixin for multiple widgets (#8876)
        var proxiedPrototype = {};

        var namespace = name.split(".")[0];
        name = name.split(".")[1];
        var fullName = namespace + "-" + name;

        if (!prototype) {
            prototype = base;
            base = $.Widget;
        }

        if ($.isArray(prototype)) {
            prototype = $.extend.apply(null, [{}].concat(prototype));
        }

        // Create selector for plugin
        $.expr[":"][fullName.toLowerCase()] = function (elem) {
            return !!$.data(elem, fullName);
        };

        $[namespace] = $[namespace] || {};
        existingConstructor = $[namespace][name];
        constructor = $[namespace][name] = function (options, element) {

            // Allow instantiation without "new" keyword
            if (!this._createWidget) {
                return new constructor(options, element);
            }

            // Allow instantiation without initializing for simple inheritance
            // must use "new" keyword (the code above always passes args)
            if (arguments.length) {
                this._createWidget(options, element);
            }
        };

        // Extend with the existing constructor to carry over any static properties
        $.extend(constructor, existingConstructor, {
            version: prototype.version,

            // Copy the object used to create the prototype in case we need to
            // redefine the widget later
            _proto: $.extend({}, prototype),

            // Track widgets that inherit from this widget in case this widget is
            // redefined after a widget inherits from it
            _childConstructors: []
        });

        basePrototype = new base();

        // We need to make the options hash a property directly on the new instance
        // otherwise we'll modify the options hash on the prototype that we're
        // inheriting from
        basePrototype.options = $.widget.extend({}, basePrototype.options);
        $.each(prototype, function (prop, value) {
            if (!$.isFunction(value)) {
                proxiedPrototype[prop] = value;
                return;
            }
            proxiedPrototype[prop] = (function () {
                function _super() {
                    return base.prototype[prop].apply(this, arguments);
                }

                function _superApply(args) {
                    return base.prototype[prop].apply(this, args);
                }

                return function () {
                    var __super = this._super;
                    var __superApply = this._superApply;
                    var returnValue;

                    this._super = _super;
                    this._superApply = _superApply;

                    returnValue = value.apply(this, arguments);

                    this._super = __super;
                    this._superApply = __superApply;

                    return returnValue;
                };
            })();
        });
        constructor.prototype = $.widget.extend(basePrototype, {

            // TODO: remove support for widgetEventPrefix
            // always use the name + a colon as the prefix, e.g., draggable:start
            // don't prefix for widgets that aren't DOM-based
            widgetEventPrefix: existingConstructor ? (basePrototype.widgetEventPrefix || name) : name
        }, proxiedPrototype, {
            constructor: constructor,
            namespace: namespace,
            widgetName: name,
            widgetFullName: fullName
        });

        // If this widget is being redefined then we need to find all widgets that
        // are inheriting from it and redefine all of them so that they inherit from
        // the new version of this widget. We're essentially trying to replace one
        // level in the prototype chain.
        if (existingConstructor) {
            $.each(existingConstructor._childConstructors, function (i, child) {
                var childPrototype = child.prototype;

                // Redefine the child widget using the same prototype that was
                // originally used, but inherit from the new version of the base
                $.widget(childPrototype.namespace + "." + childPrototype.widgetName, constructor,
                    child._proto);
            });

            // Remove the list of existing child constructors from the old constructor
            // so the old child constructors can be garbage collected
            delete existingConstructor._childConstructors;
        } else {
            base._childConstructors.push(constructor);
        }

        $.widget.bridge(name, constructor);

        return constructor;
    };

    $.widget.extend = function (target) {
        var input = widgetSlice.call(arguments, 1);
        var inputIndex = 0;
        var inputLength = input.length;
        var key;
        var value;

        for (; inputIndex < inputLength; inputIndex++) {
            for (key in input[inputIndex]) {
                value = input[inputIndex][key];
                if (input[inputIndex].hasOwnProperty(key) && value !== undefined) {

                    // Clone objects
                    if ($.isPlainObject(value)) {
                        target[key] = $.isPlainObject(target[key]) ?
                            $.widget.extend({}, target[key], value) :

                            // Don't extend strings, arrays, etc. with objects
                            $.widget.extend({}, value);

                        // Copy everything else by reference
                    } else {
                        target[key] = value;
                    }
                }
            }
        }
        return target;
    };

    $.widget.bridge = function (name, object) {
        var fullName = object.prototype.widgetFullName || name;
        $.fn[name] = function (options) {
            var isMethodCall = typeof options === "string";
            var args = widgetSlice.call(arguments, 1);
            var returnValue = this;

            if (isMethodCall) {

                // If this is an empty collection, we need to have the instance method
                // return undefined instead of the jQuery instance
                if (!this.length && options === "instance") {
                    returnValue = undefined;
                } else {
                    this.each(function () {
                        var methodValue;
                        var instance = $.data(this, fullName);

                        if (options === "instance") {
                            returnValue = instance;
                            return false;
                        }

                        if (!instance) {
                            return $.error("cannot call methods on " + name +
                                " prior to initialization; " +
                                "attempted to call method '" + options + "'");
                        }

                        if (!$.isFunction(instance[options]) || options.charAt(0) === "_") {
                            return $.error("no such method '" + options + "' for " + name +
                                " widget instance");
                        }

                        methodValue = instance[options].apply(instance, args);

                        if (methodValue !== instance && methodValue !== undefined) {
                            returnValue = methodValue && methodValue.jquery ?
                                returnValue.pushStack(methodValue.get()) :
                                methodValue;
                            return false;
                        }
                    });
                }
            } else {

                // Allow multiple hashes to be passed on init
                if (args.length) {
                    options = $.widget.extend.apply(null, [options].concat(args));
                }

                this.each(function () {
                    var instance = $.data(this, fullName);
                    if (instance) {
                        instance.option(options || {});
                        if (instance._init) {
                            instance._init();
                        }
                    } else {
                        $.data(this, fullName, new object(options, this));
                    }
                });
            }

            return returnValue;
        };
    };

    $.Widget = function ( /* options, element */) {
    };
    $.Widget._childConstructors = [];

    $.Widget.prototype = {
        widgetName: "widget",
        widgetEventPrefix: "",
        defaultElement: "<div>",

        options: {
            classes: {},
            disabled: false,

            // Callbacks
            create: null
        },

        _createWidget: function (options, element) {
            element = $(element || this.defaultElement || this)[0];
            this.element = $(element);
            this.uuid = widgetUuid++;
            this.eventNamespace = "." + this.widgetName + this.uuid;

            this.bindings = $();
            this.hoverable = $();
            this.focusable = $();
            this.classesElementLookup = {};

            if (element !== this) {
                $.data(element, this.widgetFullName, this);
                this._on(true, this.element, {
                    remove: function (event) {
                        if (event.target === element) {
                            this.destroy();
                        }
                    }
                });
                this.document = $(element.style ?

                    // Element within the document
                    element.ownerDocument :

                    // Element is window or document
                    element.document || element);
                this.window = $(this.document[0].defaultView || this.document[0].parentWindow);
            }

            this.options = $.widget.extend({},
                this.options,
                this._getCreateOptions(),
                options);

            this._create();

            if (this.options.disabled) {
                this._setOptionDisabled(this.options.disabled);
            }

            this._trigger("create", null, this._getCreateEventData());
            this._init();
        },

        _getCreateOptions: function () {
            return {};
        },

        _getCreateEventData: $.noop,

        _create: $.noop,

        _init: $.noop,

        destroy: function () {
            var that = this;

            this._destroy();
            $.each(this.classesElementLookup, function (key, value) {
                that._removeClass(value, key);
            });

            // We can probably remove the unbind calls in 2.0
            // all event bindings should go through this._on()
            this.element
                .off(this.eventNamespace)
                .removeData(this.widgetFullName);
            this.widget()
                .off(this.eventNamespace)
                .removeAttr("aria-disabled");

            // Clean up events and states
            this.bindings.off(this.eventNamespace);
        },

        _destroy: $.noop,

        widget: function () {
            return this.element;
        },

        option: function (key, value) {
            var options = key;
            var parts;
            var curOption;
            var i;

            if (arguments.length === 0) {

                // Don't return a reference to the internal hash
                return $.widget.extend({}, this.options);
            }

            if (typeof key === "string") {

                // Handle nested keys, e.g., "foo.bar" => { foo: { bar: ___ } }
                options = {};
                parts = key.split(".");
                key = parts.shift();
                if (parts.length) {
                    curOption = options[key] = $.widget.extend({}, this.options[key]);
                    for (i = 0; i < parts.length - 1; i++) {
                        curOption[parts[i]] = curOption[parts[i]] || {};
                        curOption = curOption[parts[i]];
                    }
                    key = parts.pop();
                    if (arguments.length === 1) {
                        return curOption[key] === undefined ? null : curOption[key];
                    }
                    curOption[key] = value;
                } else {
                    if (arguments.length === 1) {
                        return this.options[key] === undefined ? null : this.options[key];
                    }
                    options[key] = value;
                }
            }

            this._setOptions(options);

            return this;
        },

        _setOptions: function (options) {
            var key;

            for (key in options) {
                this._setOption(key, options[key]);
            }

            return this;
        },

        _setOption: function (key, value) {
            if (key === "classes") {
                this._setOptionClasses(value);
            }

            this.options[key] = value;

            if (key === "disabled") {
                this._setOptionDisabled(value);
            }

            return this;
        },

        _setOptionClasses: function (value) {
            var classKey, elements, currentElements;

            for (classKey in value) {
                currentElements = this.classesElementLookup[classKey];
                if (value[classKey] === this.options.classes[classKey] ||
                    !currentElements ||
                    !currentElements.length) {
                    continue;
                }

                // We are doing this to create a new jQuery object because the _removeClass() call
                // on the next line is going to destroy the reference to the current elements being
                // tracked. We need to save a copy of this collection so that we can add the new classes
                // below.
                elements = $(currentElements.get());
                this._removeClass(currentElements, classKey);

                // We don't use _addClass() here, because that uses this.options.classes
                // for generating the string of classes. We want to use the value passed in from
                // _setOption(), this is the new value of the classes option which was passed to
                // _setOption(). We pass this value directly to _classes().
                elements.addClass(this._classes({
                    element: elements,
                    keys: classKey,
                    classes: value,
                    add: true
                }));
            }
        },

        _setOptionDisabled: function (value) {
            this._toggleClass(this.widget(), this.widgetFullName + "-disabled", null, !!value);

            // If the widget is becoming disabled, then nothing is interactive
            if (value) {
                this._removeClass(this.hoverable, null, "ui-state-hover");
                this._removeClass(this.focusable, null, "ui-state-focus");
            }
        },

        enable: function () {
            return this._setOptions({disabled: false});
        },

        disable: function () {
            return this._setOptions({disabled: true});
        },

        _classes: function (options) {
            var full = [];
            var that = this;

            options = $.extend({
                element: this.element,
                classes: this.options.classes || {}
            }, options);

            function processClassString(classes, checkOption) {
                var current, i;
                for (i = 0; i < classes.length; i++) {
                    current = that.classesElementLookup[classes[i]] || $();
                    if (options.add) {
                        current = $($.unique(current.get().concat(options.element.get())));
                    } else {
                        current = $(current.not(options.element).get());
                    }
                    that.classesElementLookup[classes[i]] = current;
                    full.push(classes[i]);
                    if (checkOption && options.classes[classes[i]]) {
                        full.push(options.classes[classes[i]]);
                    }
                }
            }

            this._on(options.element, {
                "remove": "_untrackClassesElement"
            });

            if (options.keys) {
                processClassString(options.keys.match(/\S+/g) || [], true);
            }
            if (options.extra) {
                processClassString(options.extra.match(/\S+/g) || []);
            }

            return full.join(" ");
        },

        _untrackClassesElement: function (event) {
            var that = this;
            $.each(that.classesElementLookup, function (key, value) {
                if ($.inArray(event.target, value) !== -1) {
                    that.classesElementLookup[key] = $(value.not(event.target).get());
                }
            });
        },

        _removeClass: function (element, keys, extra) {
            return this._toggleClass(element, keys, extra, false);
        },

        _addClass: function (element, keys, extra) {
            return this._toggleClass(element, keys, extra, true);
        },

        _toggleClass: function (element, keys, extra, add) {
            add = (typeof add === "boolean") ? add : extra;
            var shift = (typeof element === "string" || element === null),
                options = {
                    extra: shift ? keys : extra,
                    keys: shift ? element : keys,
                    element: shift ? this.element : element,
                    add: add
                };
            options.element.toggleClass(this._classes(options), add);
            return this;
        },

        _on: function (suppressDisabledCheck, element, handlers) {
            var delegateElement;
            var instance = this;

            // No suppressDisabledCheck flag, shuffle arguments
            if (typeof suppressDisabledCheck !== "boolean") {
                handlers = element;
                element = suppressDisabledCheck;
                suppressDisabledCheck = false;
            }

            // No element argument, shuffle and use this.element
            if (!handlers) {
                handlers = element;
                element = this.element;
                delegateElement = this.widget();
            } else {
                element = delegateElement = $(element);
                this.bindings = this.bindings.add(element);
            }

            $.each(handlers, function (event, handler) {
                function handlerProxy() {

                    // Allow widgets to customize the disabled handling
                    // - disabled as an array instead of boolean
                    // - disabled class as method for disabling individual parts
                    if (!suppressDisabledCheck &&
                        (instance.options.disabled === true ||
                            $(this).hasClass("ui-state-disabled"))) {
                        return;
                    }
                    return (typeof handler === "string" ? instance[handler] : handler)
                        .apply(instance, arguments);
                }

                // Copy the guid so direct unbinding works
                if (typeof handler !== "string") {
                    handlerProxy.guid = handler.guid =
                        handler.guid || handlerProxy.guid || $.guid++;
                }

                var match = event.match(/^([\w:-]*)\s*(.*)$/);
                var eventName = match[1] + instance.eventNamespace;
                var selector = match[2];

                if (selector) {
                    delegateElement.on(eventName, selector, handlerProxy);
                } else {
                    element.on(eventName, handlerProxy);
                }
            });
        },

        _off: function (element, eventName) {
            eventName = (eventName || "").split(" ").join(this.eventNamespace + " ") +
                this.eventNamespace;
            element.off(eventName).off(eventName);

            // Clear the stack to avoid memory leaks (#10056)
            this.bindings = $(this.bindings.not(element).get());
            this.focusable = $(this.focusable.not(element).get());
            this.hoverable = $(this.hoverable.not(element).get());
        },

        _delay: function (handler, delay) {
            function handlerProxy() {
                return (typeof handler === "string" ? instance[handler] : handler)
                    .apply(instance, arguments);
            }

            var instance = this;
            return setTimeout(handlerProxy, delay || 0);
        },

        _hoverable: function (element) {
            this.hoverable = this.hoverable.add(element);
            this._on(element, {
                mouseenter: function (event) {
                    this._addClass($(event.currentTarget), null, "ui-state-hover");
                },
                mouseleave: function (event) {
                    this._removeClass($(event.currentTarget), null, "ui-state-hover");
                }
            });
        },

        _focusable: function (element) {
            this.focusable = this.focusable.add(element);
            this._on(element, {
                focusin: function (event) {
                    this._addClass($(event.currentTarget), null, "ui-state-focus");
                },
                focusout: function (event) {
                    this._removeClass($(event.currentTarget), null, "ui-state-focus");
                }
            });
        },

        _trigger: function (type, event, data) {
            var prop, orig;
            var callback = this.options[type];

            data = data || {};
            event = $.Event(event);
            event.type = (type === this.widgetEventPrefix ?
                type :
                this.widgetEventPrefix + type).toLowerCase();

            // The original event may come from any element
            // so we need to reset the target on the new event
            event.target = this.element[0];

            // Copy original event properties over to the new event
            orig = event.originalEvent;
            if (orig) {
                for (prop in orig) {
                    if (!(prop in event)) {
                        event[prop] = orig[prop];
                    }
                }
            }

            this.element.trigger(event, data);
            return !($.isFunction(callback) &&
                callback.apply(this.element[0], [event].concat(data)) === false ||
                event.isDefaultPrevented());
        }
    };

    $.each({show: "fadeIn", hide: "fadeOut"}, function (method, defaultEffect) {
        $.Widget.prototype["_" + method] = function (element, options, callback) {
            if (typeof options === "string") {
                options = {effect: options};
            }

            var hasOptions;
            var effectName = !options ?
                method :
                options === true || typeof options === "number" ?
                    defaultEffect :
                    options.effect || defaultEffect;

            options = options || {};
            if (typeof options === "number") {
                options = {duration: options};
            }

            hasOptions = !$.isEmptyObject(options);
            options.complete = callback;

            if (options.delay) {
                element.delay(options.delay);
            }

            if (hasOptions && $.effects && $.effects.effect[effectName]) {
                element[method](options);
            } else if (effectName !== method && element[effectName]) {
                element[effectName](options.duration, options.easing, callback);
            } else {
                element.queue(function (next) {
                    $(this)[method]();
                    if (callback) {
                        callback.call(element[0]);
                    }
                    next();
                });
            }
        };
    });

    var widget = $.widget;


    /*!
     * jQuery UI :data 1.12.1
     * http://jqueryui.com
     *
     * Copyright jQuery Foundation and other contributors
     * Released under the MIT license.
     * http://jquery.org/license
     */

//>>label: :data Selector
//>>group: Core
//>>description: Selects elements which have data stored under the specified key.
//>>docs: http://api.jqueryui.com/data-selector/


    var data = $.extend($.expr[":"], {
        data: $.expr.createPseudo ?
            $.expr.createPseudo(function (dataName) {
                return function (elem) {
                    return !!$.data(elem, dataName);
                };
            }) :

            // Support: jQuery <1.8
            function (elem, i, match) {
                return !!$.data(elem, match[3]);
            }
    });

    /*!
     * jQuery UI Scroll Parent 1.12.1
     * http://jqueryui.com
     *
     * Copyright jQuery Foundation and other contributors
     * Released under the MIT license.
     * http://jquery.org/license
     */

//>>label: scrollParent
//>>group: Core
//>>description: Get the closest ancestor element that is scrollable.
//>>docs: http://api.jqueryui.com/scrollParent/


    var scrollParent = $.fn.scrollParent = function (includeHidden) {
        var position = this.css("position"),
            excludeStaticParent = position === "absolute",
            overflowRegex = includeHidden ? /(auto|scroll|hidden)/ : /(auto|scroll)/,
            scrollParent = this.parents().filter(function () {
                var parent = $(this);
                if (excludeStaticParent && parent.css("position") === "static") {
                    return false;
                }
                return overflowRegex.test(parent.css("overflow") + parent.css("overflow-y") +
                    parent.css("overflow-x"));
            }).eq(0);

        return position === "fixed" || !scrollParent.length ?
            $(this[0].ownerDocument || document) :
            scrollParent;
    };


// This file is deprecated
    var ie = $.ui.ie = !!/msie [\w.]+/.exec(navigator.userAgent.toLowerCase());

    /*!
     * jQuery UI Mouse 1.12.1
     * http://jqueryui.com
     *
     * Copyright jQuery Foundation and other contributors
     * Released under the MIT license.
     * http://jquery.org/license
     */

//>>label: Mouse
//>>group: Widgets
//>>description: Abstracts mouse-based interactions to assist in creating certain widgets.
//>>docs: http://api.jqueryui.com/mouse/


    var mouseHandled = false;
    $(document).on("mouseup", function () {
        mouseHandled = false;
    });

    var widgetsMouse = $.widget("ui.mouse", {
        version: "1.12.1",
        options: {
            cancel: "input, textarea, button, select, option",
            distance: 1,
            delay: 0
        },
        _mouseInit: function () {
            var that = this;

            this.element
                .on("mousedown." + this.widgetName, function (event) {
                    return that._mouseDown(event);
                })
                .on("click." + this.widgetName, function (event) {
                    if (true === $.data(event.target, that.widgetName + ".preventClickEvent")) {
                        $.removeData(event.target, that.widgetName + ".preventClickEvent");
                        event.stopImmediatePropagation();
                        return false;
                    }
                });

            this.started = false;
        },

        // TODO: make sure destroying one instance of mouse doesn't mess with
        // other instances of mouse
        _mouseDestroy: function () {
            this.element.off("." + this.widgetName);
            if (this._mouseMoveDelegate) {
                this.document
                    .off("mousemove." + this.widgetName, this._mouseMoveDelegate)
                    .off("mouseup." + this.widgetName, this._mouseUpDelegate);
            }
        },

        _mouseDown: function (event) {

            // don't let more than one widget handle mouseStart
            if (mouseHandled) {
                return;
            }

            this._mouseMoved = false;

            // We may have missed mouseup (out of window)
            (this._mouseStarted && this._mouseUp(event));

            this._mouseDownEvent = event;

            var that = this,
                btnIsLeft = (event.which === 1),

                // event.target.nodeName works around a bug in IE 8 with
                // disabled inputs (#7620)
                elIsCancel = (typeof this.options.cancel === "string" && event.target.nodeName ?
                    $(event.target).closest(this.options.cancel).length : false);
            if (!btnIsLeft || elIsCancel || !this._mouseCapture(event)) {
                return true;
            }

            this.mouseDelayMet = !this.options.delay;
            if (!this.mouseDelayMet) {
                this._mouseDelayTimer = setTimeout(function () {
                    that.mouseDelayMet = true;
                }, this.options.delay);
            }

            if (this._mouseDistanceMet(event) && this._mouseDelayMet(event)) {
                this._mouseStarted = (this._mouseStart(event) !== false);
                if (!this._mouseStarted) {
                    event.preventDefault();
                    return true;
                }
            }

            // Click event may never have fired (Gecko & Opera)
            if (true === $.data(event.target, this.widgetName + ".preventClickEvent")) {
                $.removeData(event.target, this.widgetName + ".preventClickEvent");
            }

            // These delegates are required to keep context
            this._mouseMoveDelegate = function (event) {
                return that._mouseMove(event);
            };
            this._mouseUpDelegate = function (event) {
                return that._mouseUp(event);
            };

            this.document
                .on("mousemove." + this.widgetName, this._mouseMoveDelegate)
                .on("mouseup." + this.widgetName, this._mouseUpDelegate);

            event.preventDefault();

            mouseHandled = true;
            return true;
        },

        _mouseMove: function (event) {

            // Only check for mouseups outside the document if you've moved inside the document
            // at least once. This prevents the firing of mouseup in the case of IE<9, which will
            // fire a mousemove event if content is placed under the cursor. See #7778
            // Support: IE <9
            if (this._mouseMoved) {

                // IE mouseup check - mouseup happened when mouse was out of window
                if ($.ui.ie && (!document.documentMode || document.documentMode < 9) &&
                    !event.button) {
                    return this._mouseUp(event);

                    // Iframe mouseup check - mouseup occurred in another document
                } else if (!event.which) {

                    // Support: Safari <=8 - 9
                    // Safari sets which to 0 if you press any of the following keys
                    // during a drag (#14461)
                    if (event.originalEvent.altKey || event.originalEvent.ctrlKey ||
                        event.originalEvent.metaKey || event.originalEvent.shiftKey) {
                        this.ignoreMissingWhich = true;
                    } else if (!this.ignoreMissingWhich) {
                        return this._mouseUp(event);
                    }
                }
            }

            if (event.which || event.button) {
                this._mouseMoved = true;
            }

            if (this._mouseStarted) {
                this._mouseDrag(event);
                return event.preventDefault();
            }

            if (this._mouseDistanceMet(event) && this._mouseDelayMet(event)) {
                this._mouseStarted =
                    (this._mouseStart(this._mouseDownEvent, event) !== false);
                (this._mouseStarted ? this._mouseDrag(event) : this._mouseUp(event));
            }

            return !this._mouseStarted;
        },

        _mouseUp: function (event) {
            this.document
                .off("mousemove." + this.widgetName, this._mouseMoveDelegate)
                .off("mouseup." + this.widgetName, this._mouseUpDelegate);

            if (this._mouseStarted) {
                this._mouseStarted = false;

                if (event.target === this._mouseDownEvent.target) {
                    $.data(event.target, this.widgetName + ".preventClickEvent", true);
                }

                this._mouseStop(event);
            }

            if (this._mouseDelayTimer) {
                clearTimeout(this._mouseDelayTimer);
                delete this._mouseDelayTimer;
            }

            this.ignoreMissingWhich = false;
            mouseHandled = false;
            event.preventDefault();
        },

        _mouseDistanceMet: function (event) {
            return (Math.max(
                    Math.abs(this._mouseDownEvent.pageX - event.pageX),
                    Math.abs(this._mouseDownEvent.pageY - event.pageY)
                ) >= this.options.distance
            );
        },

        _mouseDelayMet: function ( /* event */) {
            return this.mouseDelayMet;
        },

        // These are placeholder methods, to be overriden by extending plugin
        _mouseStart: function ( /* event */) {
        },
        _mouseDrag: function ( /* event */) {
        },
        _mouseStop: function ( /* event */) {
        },
        _mouseCapture: function ( /* event */) {
            return true;
        }
    });


// $.ui.plugin is deprecated. Use $.widget() extensions instead.
    var plugin = $.ui.plugin = {
        add: function (module, option, set) {
            var i,
                proto = $.ui[module].prototype;
            for (i in set) {
                proto.plugins[i] = proto.plugins[i] || [];
                proto.plugins[i].push([option, set[i]]);
            }
        },
        call: function (instance, name, args, allowDisconnected) {
            var i,
                set = instance.plugins[name];

            if (!set) {
                return;
            }

            if (!allowDisconnected && (!instance.element[0].parentNode ||
                instance.element[0].parentNode.nodeType === 11)) {
                return;
            }

            for (i = 0; i < set.length; i++) {
                if (instance.options[set[i][0]]) {
                    set[i][1].apply(instance.element, args);
                }
            }
        }
    };


    var safeActiveElement = $.ui.safeActiveElement = function (document) {
        var activeElement;

        // Support: IE 9 only
        // IE9 throws an "Unspecified error" accessing document.activeElement from an <iframe>
        try {
            activeElement = document.activeElement;
        } catch (error) {
            activeElement = document.body;
        }

        // Support: IE 9 - 11 only
        // IE may return null instead of an element
        // Interestingly, this only seems to occur when NOT in an iframe
        if (!activeElement) {
            activeElement = document.body;
        }

        // Support: IE 11 only
        // IE11 returns a seemingly empty object in some cases when accessing
        // document.activeElement from an <iframe>
        if (!activeElement.nodeName) {
            activeElement = document.body;
        }

        return activeElement;
    };


    var safeBlur = $.ui.safeBlur = function (element) {

        // Support: IE9 - 10 only
        // If the <body> is blurred, IE will switch windows, see #9420
        if (element && element.nodeName.toLowerCase() !== "body") {
            $(element).trigger("blur");
        }
    };


    /*!
     * jQuery UI Draggable 1.12.1
     * http://jqueryui.com
     *
     * Copyright jQuery Foundation and other contributors
     * Released under the MIT license.
     * http://jquery.org/license
     */

//>>label: Draggable
//>>group: Interactions
//>>description: Enables dragging functionality for any element.
//>>docs: http://api.jqueryui.com/draggable/
//>>demos: http://jqueryui.com/draggable/
//>>css.structure: ../../themes/base/draggable.css


    $.widget("ui.draggable", $.ui.mouse, {
        version: "1.12.1",
        widgetEventPrefix: "drag",
        options: {
            addClasses: true,
            appendTo: "parent",
            axis: false,
            connectToSortable: false,
            containment: false,
            cursor: "auto",
            cursorAt: false,
            grid: false,
            handle: false,
            helper: "original",
            iframeFix: false,
            opacity: false,
            refreshPositions: false,
            revert: false,
            revertDuration: 500,
            scope: "default",
            scroll: true,
            scrollSensitivity: 20,
            scrollSpeed: 20,
            snap: false,
            snapMode: "both",
            snapTolerance: 20,
            stack: false,
            zIndex: false,

            // Callbacks
            drag: null,
            start: null,
            stop: null
        },
        _create: function () {

            if (this.options.helper === "original") {
                this._setPositionRelative();
            }
            if (this.options.addClasses) {
                this._addClass("ui-draggable");
            }
            this._setHandleClassName();

            this._mouseInit();
        },

        _setOption: function (key, value) {
            this._super(key, value);
            if (key === "handle") {
                this._removeHandleClassName();
                this._setHandleClassName();
            }
        },

        _destroy: function () {
            if ((this.helper || this.element).is(".ui-draggable-dragging")) {
                this.destroyOnClear = true;
                return;
            }
            this._removeHandleClassName();
            this._mouseDestroy();
        },

        _mouseCapture: function (event) {
            var o = this.options;

            // Among others, prevent a drag on a resizable-handle
            if (this.helper || o.disabled ||
                $(event.target).closest(".ui-resizable-handle").length > 0) {
                return false;
            }

            //Quit if we're not on a valid handle
            this.handle = this._getHandle(event);
            if (!this.handle) {
                return false;
            }

            this._blurActiveElement(event);

            this._blockFrames(o.iframeFix === true ? "iframe" : o.iframeFix);

            return true;

        },

        _blockFrames: function (selector) {
            this.iframeBlocks = this.document.find(selector).map(function () {
                var iframe = $(this);

                return $("<div>")
                    .css("position", "absolute")
                    .appendTo(iframe.parent())
                    .outerWidth(iframe.outerWidth())
                    .outerHeight(iframe.outerHeight())
                    .offset(iframe.offset())[0];
            });
        },

        _unblockFrames: function () {
            if (this.iframeBlocks) {
                this.iframeBlocks.remove();
                delete this.iframeBlocks;
            }
        },

        _blurActiveElement: function (event) {
            var activeElement = $.ui.safeActiveElement(this.document[0]),
                target = $(event.target);

            // Don't blur if the event occurred on an element that is within
            // the currently focused element
            // See #10527, #12472
            if (target.closest(activeElement).length) {
                return;
            }

            // Blur any element that currently has focus, see #4261
            $.ui.safeBlur(activeElement);
        },

        _mouseStart: function (event) {

            var o = this.options;

            //Create and append the visible helper
            this.helper = this._createHelper(event);

            this._addClass(this.helper, "ui-draggable-dragging");

            //Cache the helper size
            this._cacheHelperProportions();

            //If ddmanager is used for droppables, set the global draggable
            if ($.ui.ddmanager) {
                $.ui.ddmanager.current = this;
            }

            /*
             * - Position generation -
             * This block generates everything position related - it's the core of draggables.
             */

            //Cache the margins of the original element
            this._cacheMargins();

            //Store the helper's css position
            this.cssPosition = this.helper.css("position");
            this.scrollParent = this.helper.scrollParent(true);
            this.offsetParent = this.helper.offsetParent();
            this.hasFixedAncestor = this.helper.parents().filter(function () {
                return $(this).css("position") === "fixed";
            }).length > 0;

            //The element's absolute position on the page minus margins
            this.positionAbs = this.element.offset();
            this._refreshOffsets(event);

            //Generate the original position
            this.originalPosition = this.position = this._generatePosition(event, false);
            this.originalPageX = event.pageX;
            this.originalPageY = event.pageY;

            //Adjust the mouse offset relative to the helper if "cursorAt" is supplied
            (o.cursorAt && this._adjustOffsetFromHelper(o.cursorAt));

            //Set a containment if given in the options
            this._setContainment();

            //Trigger event + callbacks
            if (this._trigger("start", event) === false) {
                this._clear();
                return false;
            }

            //Recache the helper size
            this._cacheHelperProportions();

            //Prepare the droppable offsets
            if ($.ui.ddmanager && !o.dropBehaviour) {
                $.ui.ddmanager.prepareOffsets(this, event);
            }

            // Execute the drag once - this causes the helper not to be visible before getting its
            // correct position
            this._mouseDrag(event, true);

            // If the ddmanager is used for droppables, inform the manager that dragging has started
            // (see #5003)
            if ($.ui.ddmanager) {
                $.ui.ddmanager.dragStart(this, event);
            }

            return true;
        },

        _refreshOffsets: function (event) {
            this.offset = {
                top: this.positionAbs.top - this.margins.top,
                left: this.positionAbs.left - this.margins.left,
                scroll: false,
                parent: this._getParentOffset(),
                relative: this._getRelativeOffset()
            };

            this.offset.click = {
                left: event.pageX - this.offset.left,
                top: event.pageY - this.offset.top
            };
        },

        _mouseDrag: function (event, noPropagation) {

            // reset any necessary cached properties (see #5009)
            if (this.hasFixedAncestor) {
                this.offset.parent = this._getParentOffset();
            }

            //Compute the helpers position
            this.position = this._generatePosition(event, true);
            this.positionAbs = this._convertPositionTo("absolute");

            //Call plugins and callbacks and use the resulting position if something is returned
            if (!noPropagation) {
                var ui = this._uiHash();
                if (this._trigger("drag", event, ui) === false) {
                    this._mouseUp(new $.Event("mouseup", event));
                    return false;
                }
                this.position = ui.position;
            }

            this.helper[0].style.left = this.position.left + "px";
            this.helper[0].style.top = this.position.top + "px";

            if ($.ui.ddmanager) {
                $.ui.ddmanager.drag(this, event);
            }

            return false;
        },

        _mouseStop: function (event) {

            //If we are using droppables, inform the manager about the drop
            var that = this,
                dropped = false;
            if ($.ui.ddmanager && !this.options.dropBehaviour) {
                dropped = $.ui.ddmanager.drop(this, event);
            }

            //if a drop comes from outside (a sortable)
            if (this.dropped) {
                dropped = this.dropped;
                this.dropped = false;
            }

            if ((this.options.revert === "invalid" && !dropped) ||
                (this.options.revert === "valid" && dropped) ||
                this.options.revert === true || ($.isFunction(this.options.revert) &&
                    this.options.revert.call(this.element, dropped))
            ) {
                $(this.helper).animate(
                    this.originalPosition,
                    parseInt(this.options.revertDuration, 10),
                    function () {
                        if (that._trigger("stop", event) !== false) {
                            that._clear();
                        }
                    }
                );
            } else {
                if (this._trigger("stop", event) !== false) {
                    this._clear();
                }
            }

            return false;
        },

        _mouseUp: function (event) {
            this._unblockFrames();

            // If the ddmanager is used for droppables, inform the manager that dragging has stopped
            // (see #5003)
            if ($.ui.ddmanager) {
                $.ui.ddmanager.dragStop(this, event);
            }

            // Only need to focus if the event occurred on the draggable itself, see #10527
            if (this.handleElement.is(event.target)) {

                // The interaction is over; whether or not the click resulted in a drag,
                // focus the element
                this.element.trigger("focus");
            }

            return $.ui.mouse.prototype._mouseUp.call(this, event);
        },

        cancel: function () {

            if (this.helper.is(".ui-draggable-dragging")) {
                this._mouseUp(new $.Event("mouseup", {target: this.element[0]}));
            } else {
                this._clear();
            }

            return this;

        },

        _getHandle: function (event) {
            return this.options.handle ?
                !!$(event.target).closest(this.element.find(this.options.handle)).length :
                true;
        },

        _setHandleClassName: function () {
            this.handleElement = this.options.handle ?
                this.element.find(this.options.handle) : this.element;
            this._addClass(this.handleElement, "ui-draggable-handle");
        },

        _removeHandleClassName: function () {
            this._removeClass(this.handleElement, "ui-draggable-handle");
        },

        _createHelper: function (event) {

            var o = this.options,
                helperIsFunction = $.isFunction(o.helper),
                helper = helperIsFunction ?
                    $(o.helper.apply(this.element[0], [event])) :
                    (o.helper === "clone" ?
                        this.element.clone().removeAttr("id") :
                        this.element);

            if (!helper.parents("body").length) {
                helper.appendTo((o.appendTo === "parent" ?
                    this.element[0].parentNode :
                    o.appendTo));
            }

            // Http://bugs.jqueryui.com/ticket/9446
            // a helper function can return the original element
            // which wouldn't have been set to relative in _create
            if (helperIsFunction && helper[0] === this.element[0]) {
                this._setPositionRelative();
            }

            if (helper[0] !== this.element[0] &&
                !(/(fixed|absolute)/).test(helper.css("position"))) {
                helper.css("position", "absolute");
            }

            return helper;

        },

        _setPositionRelative: function () {
            if (!(/^(?:r|a|f)/).test(this.element.css("position"))) {
                this.element[0].style.position = "relative";
            }
        },

        _adjustOffsetFromHelper: function (obj) {
            if (typeof obj === "string") {
                obj = obj.split(" ");
            }
            if ($.isArray(obj)) {
                obj = {left: +obj[0], top: +obj[1] || 0};
            }
            if ("left" in obj) {
                this.offset.click.left = obj.left + this.margins.left;
            }
            if ("right" in obj) {
                this.offset.click.left = this.helperProportions.width - obj.right + this.margins.left;
            }
            if ("top" in obj) {
                this.offset.click.top = obj.top + this.margins.top;
            }
            if ("bottom" in obj) {
                this.offset.click.top = this.helperProportions.height - obj.bottom + this.margins.top;
            }
        },

        _isRootNode: function (element) {
            return (/(html|body)/i).test(element.tagName) || element === this.document[0];
        },

        _getParentOffset: function () {

            //Get the offsetParent and cache its position
            var po = this.offsetParent.offset(),
                document = this.document[0];

            // This is a special case where we need to modify a offset calculated on start, since the
            // following happened:
            // 1. The position of the helper is absolute, so it's position is calculated based on the
            // next positioned parent
            // 2. The actual offset parent is a child of the scroll parent, and the scroll parent isn't
            // the document, which means that the scroll is included in the initial calculation of the
            // offset of the parent, and never recalculated upon drag
            if (this.cssPosition === "absolute" && this.scrollParent[0] !== document &&
                $.contains(this.scrollParent[0], this.offsetParent[0])) {
                po.left += this.scrollParent.scrollLeft();
                po.top += this.scrollParent.scrollTop();
            }

            if (this._isRootNode(this.offsetParent[0])) {
                po = {top: 0, left: 0};
            }

            return {
                top: po.top + (parseInt(this.offsetParent.css("borderTopWidth"), 10) || 0),
                left: po.left + (parseInt(this.offsetParent.css("borderLeftWidth"), 10) || 0)
            };

        },

        _getRelativeOffset: function () {
            if (this.cssPosition !== "relative") {
                return {top: 0, left: 0};
            }

            var p = this.element.position(),
                scrollIsRootNode = this._isRootNode(this.scrollParent[0]);

            return {
                top: p.top - (parseInt(this.helper.css("top"), 10) || 0) +
                    (!scrollIsRootNode ? this.scrollParent.scrollTop() : 0),
                left: p.left - (parseInt(this.helper.css("left"), 10) || 0) +
                    (!scrollIsRootNode ? this.scrollParent.scrollLeft() : 0)
            };

        },

        _cacheMargins: function () {
            this.margins = {
                left: (parseInt(this.element.css("marginLeft"), 10) || 0),
                top: (parseInt(this.element.css("marginTop"), 10) || 0),
                right: (parseInt(this.element.css("marginRight"), 10) || 0),
                bottom: (parseInt(this.element.css("marginBottom"), 10) || 0)
            };
        },

        _cacheHelperProportions: function () {
            this.helperProportions = {
                width: this.helper.outerWidth(),
                height: this.helper.outerHeight()
            };
        },

        _setContainment: function () {

            var isUserScrollable, c, ce,
                o = this.options,
                document = this.document[0];

            this.relativeContainer = null;

            if (!o.containment) {
                this.containment = null;
                return;
            }

            if (o.containment === "window") {
                this.containment = [
                    $(window).scrollLeft() - this.offset.relative.left - this.offset.parent.left,
                    $(window).scrollTop() - this.offset.relative.top - this.offset.parent.top,
                    $(window).scrollLeft() + $(window).width() -
                    this.helperProportions.width - this.margins.left,
                    $(window).scrollTop() +
                    ($(window).height() || document.body.parentNode.scrollHeight) -
                    this.helperProportions.height - this.margins.top
                ];
                return;
            }

            if (o.containment === "document") {
                this.containment = [
                    0,
                    0,
                    $(document).width() - this.helperProportions.width - this.margins.left,
                    ($(document).height() || document.body.parentNode.scrollHeight) -
                    this.helperProportions.height - this.margins.top
                ];
                return;
            }

            if (o.containment.constructor === Array) {
                this.containment = o.containment;
                return;
            }

            if (o.containment === "parent") {
                o.containment = this.helper[0].parentNode;
            }

            c = $(o.containment);
            ce = c[0];

            if (!ce) {
                return;
            }

            isUserScrollable = /(scroll|auto)/.test(c.css("overflow"));

            this.containment = [
                (parseInt(c.css("borderLeftWidth"), 10) || 0) +
                (parseInt(c.css("paddingLeft"), 10) || 0),
                (parseInt(c.css("borderTopWidth"), 10) || 0) +
                (parseInt(c.css("paddingTop"), 10) || 0),
                (isUserScrollable ? Math.max(ce.scrollWidth, ce.offsetWidth) : ce.offsetWidth) -
                (parseInt(c.css("borderRightWidth"), 10) || 0) -
                (parseInt(c.css("paddingRight"), 10) || 0) -
                this.helperProportions.width -
                this.margins.left -
                this.margins.right,
                (isUserScrollable ? Math.max(ce.scrollHeight, ce.offsetHeight) : ce.offsetHeight) -
                (parseInt(c.css("borderBottomWidth"), 10) || 0) -
                (parseInt(c.css("paddingBottom"), 10) || 0) -
                this.helperProportions.height -
                this.margins.top -
                this.margins.bottom
            ];
            this.relativeContainer = c;
        },

        _convertPositionTo: function (d, pos) {

            if (!pos) {
                pos = this.position;
            }

            var mod = d === "absolute" ? 1 : -1,
                scrollIsRootNode = this._isRootNode(this.scrollParent[0]);

            return {
                top: (

                    // The absolute mouse position
                    pos.top +

                    // Only for relative positioned nodes: Relative offset from element to offset parent
                    this.offset.relative.top * mod +

                    // The offsetParent's offset without borders (offset + border)
                    this.offset.parent.top * mod -
                    ((this.cssPosition === "fixed" ?
                        -this.offset.scroll.top :
                        (scrollIsRootNode ? 0 : this.offset.scroll.top)) * mod)
                ),
                left: (

                    // The absolute mouse position
                    pos.left +

                    // Only for relative positioned nodes: Relative offset from element to offset parent
                    this.offset.relative.left * mod +

                    // The offsetParent's offset without borders (offset + border)
                    this.offset.parent.left * mod -
                    ((this.cssPosition === "fixed" ?
                        -this.offset.scroll.left :
                        (scrollIsRootNode ? 0 : this.offset.scroll.left)) * mod)
                )
            };

        },

        _generatePosition: function (event, constrainPosition) {

            var containment, co, top, left,
                o = this.options,
                scrollIsRootNode = this._isRootNode(this.scrollParent[0]),
                pageX = event.pageX,
                pageY = event.pageY;

            // Cache the scroll
            if (!scrollIsRootNode || !this.offset.scroll) {
                this.offset.scroll = {
                    top: this.scrollParent.scrollTop(),
                    left: this.scrollParent.scrollLeft()
                };
            }

            /*
             * - Position constraining -
             * Constrain the position to a mix of grid, containment.
             */

            // If we are not dragging yet, we won't check for options
            if (constrainPosition) {
                if (this.containment) {
                    if (this.relativeContainer) {
                        co = this.relativeContainer.offset();
                        containment = [
                            this.containment[0] + co.left,
                            this.containment[1] + co.top,
                            this.containment[2] + co.left,
                            this.containment[3] + co.top
                        ];
                    } else {
                        containment = this.containment;
                    }

                    if (event.pageX - this.offset.click.left < containment[0]) {
                        pageX = containment[0] + this.offset.click.left;
                    }
                    if (event.pageY - this.offset.click.top < containment[1]) {
                        pageY = containment[1] + this.offset.click.top;
                    }
                    if (event.pageX - this.offset.click.left > containment[2]) {
                        pageX = containment[2] + this.offset.click.left;
                    }
                    if (event.pageY - this.offset.click.top > containment[3]) {
                        pageY = containment[3] + this.offset.click.top;
                    }
                }

                if (o.grid) {

                    //Check for grid elements set to 0 to prevent divide by 0 error causing invalid
                    // argument errors in IE (see ticket #6950)
                    top = o.grid[1] ? this.originalPageY + Math.round((pageY -
                        this.originalPageY) / o.grid[1]) * o.grid[1] : this.originalPageY;
                    pageY = containment ? ((top - this.offset.click.top >= containment[1] ||
                        top - this.offset.click.top > containment[3]) ?
                        top :
                        ((top - this.offset.click.top >= containment[1]) ?
                            top - o.grid[1] : top + o.grid[1])) : top;

                    left = o.grid[0] ? this.originalPageX +
                        Math.round((pageX - this.originalPageX) / o.grid[0]) * o.grid[0] :
                        this.originalPageX;
                    pageX = containment ? ((left - this.offset.click.left >= containment[0] ||
                        left - this.offset.click.left > containment[2]) ?
                        left :
                        ((left - this.offset.click.left >= containment[0]) ?
                            left - o.grid[0] : left + o.grid[0])) : left;
                }

                if (o.axis === "y") {
                    pageX = this.originalPageX;
                }

                if (o.axis === "x") {
                    pageY = this.originalPageY;
                }
            }

            return {
                top: (

                    // The absolute mouse position
                    pageY -

                    // Click offset (relative to the element)
                    this.offset.click.top -

                    // Only for relative positioned nodes: Relative offset from element to offset parent
                    this.offset.relative.top -

                    // The offsetParent's offset without borders (offset + border)
                    this.offset.parent.top +
                    (this.cssPosition === "fixed" ?
                        -this.offset.scroll.top :
                        (scrollIsRootNode ? 0 : this.offset.scroll.top))
                ),
                left: (

                    // The absolute mouse position
                    pageX -

                    // Click offset (relative to the element)
                    this.offset.click.left -

                    // Only for relative positioned nodes: Relative offset from element to offset parent
                    this.offset.relative.left -

                    // The offsetParent's offset without borders (offset + border)
                    this.offset.parent.left +
                    (this.cssPosition === "fixed" ?
                        -this.offset.scroll.left :
                        (scrollIsRootNode ? 0 : this.offset.scroll.left))
                )
            };

        },

        _clear: function () {
            this._removeClass(this.helper, "ui-draggable-dragging");
            if (this.helper[0] !== this.element[0] && !this.cancelHelperRemoval) {
                this.helper.remove();
            }
            this.helper = null;
            this.cancelHelperRemoval = false;
            if (this.destroyOnClear) {
                this.destroy();
            }
        },

        // From now on bulk stuff - mainly helpers

        _trigger: function (type, event, ui) {
            ui = ui || this._uiHash();
            $.ui.plugin.call(this, type, [event, ui, this], true);

            // Absolute position and offset (see #6884 ) have to be recalculated after plugins
            if (/^(drag|start|stop)/.test(type)) {
                this.positionAbs = this._convertPositionTo("absolute");
                ui.offset = this.positionAbs;
            }
            return $.Widget.prototype._trigger.call(this, type, event, ui);
        },

        plugins: {},

        _uiHash: function () {
            return {
                helper: this.helper,
                position: this.position,
                originalPosition: this.originalPosition,
                offset: this.positionAbs
            };
        }

    });

    $.ui.plugin.add("draggable", "connectToSortable", {
        start: function (event, ui, draggable) {
            var uiSortable = $.extend({}, ui, {
                item: draggable.element
            });

            draggable.sortables = [];
            $(draggable.options.connectToSortable).each(function () {
                var sortable = $(this).sortable("instance");

                if (sortable && !sortable.options.disabled) {
                    draggable.sortables.push(sortable);

                    // RefreshPositions is called at drag start to refresh the containerCache
                    // which is used in drag. This ensures it's initialized and synchronized
                    // with any changes that might have happened on the page since initialization.
                    sortable.refreshPositions();
                    sortable._trigger("activate", event, uiSortable);
                }
            });
        },
        stop: function (event, ui, draggable) {
            var uiSortable = $.extend({}, ui, {
                item: draggable.element
            });

            draggable.cancelHelperRemoval = false;

            $.each(draggable.sortables, function () {
                var sortable = this;

                if (sortable.isOver) {
                    sortable.isOver = 0;

                    // Allow this sortable to handle removing the helper
                    draggable.cancelHelperRemoval = true;
                    sortable.cancelHelperRemoval = false;

                    // Use _storedCSS To restore properties in the sortable,
                    // as this also handles revert (#9675) since the draggable
                    // may have modified them in unexpected ways (#8809)
                    sortable._storedCSS = {
                        position: sortable.placeholder.css("position"),
                        top: sortable.placeholder.css("top"),
                        left: sortable.placeholder.css("left")
                    };

                    sortable._mouseStop(event);

                    // Once drag has ended, the sortable should return to using
                    // its original helper, not the shared helper from draggable
                    sortable.options.helper = sortable.options._helper;
                } else {

                    // Prevent this Sortable from removing the helper.
                    // However, don't set the draggable to remove the helper
                    // either as another connected Sortable may yet handle the removal.
                    sortable.cancelHelperRemoval = true;

                    sortable._trigger("deactivate", event, uiSortable);
                }
            });
        },
        drag: function (event, ui, draggable) {
            $.each(draggable.sortables, function () {
                var innermostIntersecting = false,
                    sortable = this;

                // Copy over variables that sortable's _intersectsWith uses
                sortable.positionAbs = draggable.positionAbs;
                sortable.helperProportions = draggable.helperProportions;
                sortable.offset.click = draggable.offset.click;

                if (sortable._intersectsWith(sortable.containerCache)) {
                    innermostIntersecting = true;

                    $.each(draggable.sortables, function () {

                        // Copy over variables that sortable's _intersectsWith uses
                        this.positionAbs = draggable.positionAbs;
                        this.helperProportions = draggable.helperProportions;
                        this.offset.click = draggable.offset.click;

                        if (this !== sortable &&
                            this._intersectsWith(this.containerCache) &&
                            $.contains(sortable.element[0], this.element[0])) {
                            innermostIntersecting = false;
                        }

                        return innermostIntersecting;
                    });
                }

                if (innermostIntersecting) {

                    // If it intersects, we use a little isOver variable and set it once,
                    // so that the move-in stuff gets fired only once.
                    if (!sortable.isOver) {
                        sortable.isOver = 1;

                        // Store draggable's parent in case we need to reappend to it later.
                        draggable._parent = ui.helper.parent();

                        sortable.currentItem = ui.helper
                            .appendTo(sortable.element)
                            .data("ui-sortable-item", true);

                        // Store helper option to later restore it
                        sortable.options._helper = sortable.options.helper;

                        sortable.options.helper = function () {
                            return ui.helper[0];
                        };

                        // Fire the start events of the sortable with our passed browser event,
                        // and our own helper (so it doesn't create a new one)
                        event.target = sortable.currentItem[0];
                        sortable._mouseCapture(event, true);
                        sortable._mouseStart(event, true, true);

                        // Because the browser event is way off the new appended portlet,
                        // modify necessary variables to reflect the changes
                        sortable.offset.click.top = draggable.offset.click.top;
                        sortable.offset.click.left = draggable.offset.click.left;
                        sortable.offset.parent.left -= draggable.offset.parent.left -
                            sortable.offset.parent.left;
                        sortable.offset.parent.top -= draggable.offset.parent.top -
                            sortable.offset.parent.top;

                        draggable._trigger("toSortable", event);

                        // Inform draggable that the helper is in a valid drop zone,
                        // used solely in the revert option to handle "valid/invalid".
                        draggable.dropped = sortable.element;

                        // Need to refreshPositions of all sortables in the case that
                        // adding to one sortable changes the location of the other sortables (#9675)
                        $.each(draggable.sortables, function () {
                            this.refreshPositions();
                        });

                        // Hack so receive/update callbacks work (mostly)
                        draggable.currentItem = draggable.element;
                        sortable.fromOutside = draggable;
                    }

                    if (sortable.currentItem) {
                        sortable._mouseDrag(event);

                        // Copy the sortable's position because the draggable's can potentially reflect
                        // a relative position, while sortable is always absolute, which the dragged
                        // element has now become. (#8809)
                        ui.position = sortable.position;
                    }
                } else {

                    // If it doesn't intersect with the sortable, and it intersected before,
                    // we fake the drag stop of the sortable, but make sure it doesn't remove
                    // the helper by using cancelHelperRemoval.
                    if (sortable.isOver) {

                        sortable.isOver = 0;
                        sortable.cancelHelperRemoval = true;

                        // Calling sortable's mouseStop would trigger a revert,
                        // so revert must be temporarily false until after mouseStop is called.
                        sortable.options._revert = sortable.options.revert;
                        sortable.options.revert = false;

                        sortable._trigger("out", event, sortable._uiHash(sortable));
                        sortable._mouseStop(event, true);

                        // Restore sortable behaviors that were modfied
                        // when the draggable entered the sortable area (#9481)
                        sortable.options.revert = sortable.options._revert;
                        sortable.options.helper = sortable.options._helper;

                        if (sortable.placeholder) {
                            sortable.placeholder.remove();
                        }

                        // Restore and recalculate the draggable's offset considering the sortable
                        // may have modified them in unexpected ways. (#8809, #10669)
                        ui.helper.appendTo(draggable._parent);
                        draggable._refreshOffsets(event);
                        ui.position = draggable._generatePosition(event, true);

                        draggable._trigger("fromSortable", event);

                        // Inform draggable that the helper is no longer in a valid drop zone
                        draggable.dropped = false;

                        // Need to refreshPositions of all sortables just in case removing
                        // from one sortable changes the location of other sortables (#9675)
                        $.each(draggable.sortables, function () {
                            this.refreshPositions();
                        });
                    }
                }
            });
        }
    });

    $.ui.plugin.add("draggable", "cursor", {
        start: function (event, ui, instance) {
            var t = $("body"),
                o = instance.options;

            if (t.css("cursor")) {
                o._cursor = t.css("cursor");
            }
            t.css("cursor", o.cursor);
        },
        stop: function (event, ui, instance) {
            var o = instance.options;
            if (o._cursor) {
                $("body").css("cursor", o._cursor);
            }
        }
    });

    $.ui.plugin.add("draggable", "opacity", {
        start: function (event, ui, instance) {
            var t = $(ui.helper),
                o = instance.options;
            if (t.css("opacity")) {
                o._opacity = t.css("opacity");
            }
            t.css("opacity", o.opacity);
        },
        stop: function (event, ui, instance) {
            var o = instance.options;
            if (o._opacity) {
                $(ui.helper).css("opacity", o._opacity);
            }
        }
    });

    $.ui.plugin.add("draggable", "scroll", {
        start: function (event, ui, i) {
            if (!i.scrollParentNotHidden) {
                i.scrollParentNotHidden = i.helper.scrollParent(false);
            }

            if (i.scrollParentNotHidden[0] !== i.document[0] &&
                i.scrollParentNotHidden[0].tagName !== "HTML") {
                i.overflowOffset = i.scrollParentNotHidden.offset();
            }
        },
        drag: function (event, ui, i) {

            var o = i.options,
                scrolled = false,
                scrollParent = i.scrollParentNotHidden[0],
                document = i.document[0];

            if (scrollParent !== document && scrollParent.tagName !== "HTML") {
                if (!o.axis || o.axis !== "x") {
                    if ((i.overflowOffset.top + scrollParent.offsetHeight) - event.pageY <
                        o.scrollSensitivity) {
                        scrollParent.scrollTop = scrolled = scrollParent.scrollTop + o.scrollSpeed;
                    } else if (event.pageY - i.overflowOffset.top < o.scrollSensitivity) {
                        scrollParent.scrollTop = scrolled = scrollParent.scrollTop - o.scrollSpeed;
                    }
                }

                if (!o.axis || o.axis !== "y") {
                    if ((i.overflowOffset.left + scrollParent.offsetWidth) - event.pageX <
                        o.scrollSensitivity) {
                        scrollParent.scrollLeft = scrolled = scrollParent.scrollLeft + o.scrollSpeed;
                    } else if (event.pageX - i.overflowOffset.left < o.scrollSensitivity) {
                        scrollParent.scrollLeft = scrolled = scrollParent.scrollLeft - o.scrollSpeed;
                    }
                }

            } else {

                if (!o.axis || o.axis !== "x") {
                    if (event.pageY - $(document).scrollTop() < o.scrollSensitivity) {
                        scrolled = $(document).scrollTop($(document).scrollTop() - o.scrollSpeed);
                    } else if ($(window).height() - (event.pageY - $(document).scrollTop()) <
                        o.scrollSensitivity) {
                        scrolled = $(document).scrollTop($(document).scrollTop() + o.scrollSpeed);
                    }
                }

                if (!o.axis || o.axis !== "y") {
                    if (event.pageX - $(document).scrollLeft() < o.scrollSensitivity) {
                        scrolled = $(document).scrollLeft(
                            $(document).scrollLeft() - o.scrollSpeed
                        );
                    } else if ($(window).width() - (event.pageX - $(document).scrollLeft()) <
                        o.scrollSensitivity) {
                        scrolled = $(document).scrollLeft(
                            $(document).scrollLeft() + o.scrollSpeed
                        );
                    }
                }

            }

            if (scrolled !== false && $.ui.ddmanager && !o.dropBehaviour) {
                $.ui.ddmanager.prepareOffsets(i, event);
            }

        }
    });

    $.ui.plugin.add("draggable", "snap", {
        start: function (event, ui, i) {

            var o = i.options;

            i.snapElements = [];

            $(o.snap.constructor !== String ? (o.snap.items || ":data(ui-draggable)") : o.snap)
                .each(function () {
                    var $t = $(this),
                        $o = $t.offset();
                    if (this !== i.element[0]) {
                        i.snapElements.push({
                            item: this,
                            width: $t.outerWidth(), height: $t.outerHeight(),
                            top: $o.top, left: $o.left
                        });
                    }
                });

        },
        drag: function (event, ui, inst) {

            var ts, bs, ls, rs, l, r, t, b, i, first,
                o = inst.options,
                d = o.snapTolerance,
                x1 = ui.offset.left, x2 = x1 + inst.helperProportions.width,
                y1 = ui.offset.top, y2 = y1 + inst.helperProportions.height;

            for (i = inst.snapElements.length - 1; i >= 0; i--) {

                l = inst.snapElements[i].left - inst.margins.left;
                r = l + inst.snapElements[i].width;
                t = inst.snapElements[i].top - inst.margins.top;
                b = t + inst.snapElements[i].height;

                if (x2 < l - d || x1 > r + d || y2 < t - d || y1 > b + d ||
                    !$.contains(inst.snapElements[i].item.ownerDocument,
                        inst.snapElements[i].item)) {
                    if (inst.snapElements[i].snapping) {
                        (inst.options.snap.release &&
                            inst.options.snap.release.call(
                                inst.element,
                                event,
                                $.extend(inst._uiHash(), {snapItem: inst.snapElements[i].item})
                            ));
                    }
                    inst.snapElements[i].snapping = false;
                    continue;
                }

                if (o.snapMode !== "inner") {
                    ts = Math.abs(t - y2) <= d;
                    bs = Math.abs(b - y1) <= d;
                    ls = Math.abs(l - x2) <= d;
                    rs = Math.abs(r - x1) <= d;
                    if (ts) {
                        ui.position.top = inst._convertPositionTo("relative", {
                            top: t - inst.helperProportions.height,
                            left: 0
                        }).top;
                    }
                    if (bs) {
                        ui.position.top = inst._convertPositionTo("relative", {
                            top: b,
                            left: 0
                        }).top;
                    }
                    if (ls) {
                        ui.position.left = inst._convertPositionTo("relative", {
                            top: 0,
                            left: l - inst.helperProportions.width
                        }).left;
                    }
                    if (rs) {
                        ui.position.left = inst._convertPositionTo("relative", {
                            top: 0,
                            left: r
                        }).left;
                    }
                }

                first = (ts || bs || ls || rs);

                if (o.snapMode !== "outer") {
                    ts = Math.abs(t - y1) <= d;
                    bs = Math.abs(b - y2) <= d;
                    ls = Math.abs(l - x1) <= d;
                    rs = Math.abs(r - x2) <= d;
                    if (ts) {
                        ui.position.top = inst._convertPositionTo("relative", {
                            top: t,
                            left: 0
                        }).top;
                    }
                    if (bs) {
                        ui.position.top = inst._convertPositionTo("relative", {
                            top: b - inst.helperProportions.height,
                            left: 0
                        }).top;
                    }
                    if (ls) {
                        ui.position.left = inst._convertPositionTo("relative", {
                            top: 0,
                            left: l
                        }).left;
                    }
                    if (rs) {
                        ui.position.left = inst._convertPositionTo("relative", {
                            top: 0,
                            left: r - inst.helperProportions.width
                        }).left;
                    }
                }

                if (!inst.snapElements[i].snapping && (ts || bs || ls || rs || first)) {
                    (inst.options.snap.snap &&
                        inst.options.snap.snap.call(
                            inst.element,
                            event,
                            $.extend(inst._uiHash(), {
                                snapItem: inst.snapElements[i].item
                            })));
                }
                inst.snapElements[i].snapping = (ts || bs || ls || rs || first);

            }

        }
    });

    $.ui.plugin.add("draggable", "stack", {
        start: function (event, ui, instance) {
            var min,
                o = instance.options,
                group = $.makeArray($(o.stack)).sort(function (a, b) {
                    return (parseInt($(a).css("zIndex"), 10) || 0) -
                        (parseInt($(b).css("zIndex"), 10) || 0);
                });

            if (!group.length) {
                return;
            }

            min = parseInt($(group[0]).css("zIndex"), 10) || 0;
            $(group).each(function (i) {
                $(this).css("zIndex", min + i);
            });
            this.css("zIndex", (min + group.length));
        }
    });

    $.ui.plugin.add("draggable", "zIndex", {
        start: function (event, ui, instance) {
            var t = $(ui.helper),
                o = instance.options;

            if (t.css("zIndex")) {
                o._zIndex = t.css("zIndex");
            }
            t.css("zIndex", o.zIndex);
        },
        stop: function (event, ui, instance) {
            var o = instance.options;

            if (o._zIndex) {
                $(ui.helper).css("zIndex", o._zIndex);
            }
        }
    });

    var widgetsDraggable = $.ui.draggable;


    /*!
     * jQuery UI Droppable 1.12.1
     * http://jqueryui.com
     *
     * Copyright jQuery Foundation and other contributors
     * Released under the MIT license.
     * http://jquery.org/license
     */

//>>label: Droppable
//>>group: Interactions
//>>description: Enables drop targets for draggable elements.
//>>docs: http://api.jqueryui.com/droppable/
//>>demos: http://jqueryui.com/droppable/


    $.widget("ui.droppable", {
        version: "1.12.1",
        widgetEventPrefix: "drop",
        options: {
            accept: "*",
            addClasses: true,
            greedy: false,
            scope: "default",
            tolerance: "intersect",

            // Callbacks
            activate: null,
            deactivate: null,
            drop: null,
            out: null,
            over: null
        },
        _create: function () {

            var proportions,
                o = this.options,
                accept = o.accept;

            this.isover = false;
            this.isout = true;

            this.accept = $.isFunction(accept) ? accept : function (d) {
                return d.is(accept);
            };

            this.proportions = function ( /* valueToWrite */) {
                if (arguments.length) {

                    // Store the droppable's proportions
                    proportions = arguments[0];
                } else {

                    // Retrieve or derive the droppable's proportions
                    return proportions ?
                        proportions :
                        proportions = {
                            width: this.element[0].offsetWidth,
                            height: this.element[0].offsetHeight
                        };
                }
            };

            this._addToManager(o.scope);

            o.addClasses && this._addClass("ui-droppable");

        },

        _addToManager: function (scope) {

            // Add the reference and positions to the manager
            $.ui.ddmanager.droppables[scope] = $.ui.ddmanager.droppables[scope] || [];
            $.ui.ddmanager.droppables[scope].push(this);
        },

        _splice: function (drop) {
            var i = 0;
            for (; i < drop.length; i++) {
                if (drop[i] === this) {
                    drop.splice(i, 1);
                }
            }
        },

        _destroy: function () {
            var drop = $.ui.ddmanager.droppables[this.options.scope];

            this._splice(drop);
        },

        _setOption: function (key, value) {

            if (key === "accept") {
                this.accept = $.isFunction(value) ? value : function (d) {
                    return d.is(value);
                };
            } else if (key === "scope") {
                var drop = $.ui.ddmanager.droppables[this.options.scope];

                this._splice(drop);
                this._addToManager(value);
            }

            this._super(key, value);
        },

        _activate: function (event) {
            var draggable = $.ui.ddmanager.current;

            this._addActiveClass();
            if (draggable) {
                this._trigger("activate", event, this.ui(draggable));
            }
        },

        _deactivate: function (event) {
            var draggable = $.ui.ddmanager.current;

            this._removeActiveClass();
            if (draggable) {
                this._trigger("deactivate", event, this.ui(draggable));
            }
        },

        _over: function (event) {

            var draggable = $.ui.ddmanager.current;

            // Bail if draggable and droppable are same element
            if (!draggable || (draggable.currentItem ||
                draggable.element)[0] === this.element[0]) {
                return;
            }

            if (this.accept.call(this.element[0], (draggable.currentItem ||
                draggable.element))) {
                this._addHoverClass();
                this._trigger("over", event, this.ui(draggable));
            }

        },

        _out: function (event) {

            var draggable = $.ui.ddmanager.current;

            // Bail if draggable and droppable are same element
            if (!draggable || (draggable.currentItem ||
                draggable.element)[0] === this.element[0]) {
                return;
            }

            if (this.accept.call(this.element[0], (draggable.currentItem ||
                draggable.element))) {
                this._removeHoverClass();
                this._trigger("out", event, this.ui(draggable));
            }

        },

        _drop: function (event, custom) {

            var draggable = custom || $.ui.ddmanager.current,
                childrenIntersection = false;

            // Bail if draggable and droppable are same element
            if (!draggable || (draggable.currentItem ||
                draggable.element)[0] === this.element[0]) {
                return false;
            }

            this.element
                .find(":data(ui-droppable)")
                .not(".ui-draggable-dragging")
                .each(function () {
                    var inst = $(this).droppable("instance");
                    if (
                        inst.options.greedy &&
                        !inst.options.disabled &&
                        inst.options.scope === draggable.options.scope &&
                        inst.accept.call(
                            inst.element[0], (draggable.currentItem || draggable.element)
                        ) &&
                        intersect(
                            draggable,
                            $.extend(inst, {offset: inst.element.offset()}),
                            inst.options.tolerance, event
                        )
                    ) {
                        childrenIntersection = true;
                        return false;
                    }
                });
            if (childrenIntersection) {
                return false;
            }

            if (this.accept.call(this.element[0],
                (draggable.currentItem || draggable.element))) {
                this._removeActiveClass();
                this._removeHoverClass();

                this._trigger("drop", event, this.ui(draggable));
                return this.element;
            }

            return false;

        },

        ui: function (c) {
            return {
                draggable: (c.currentItem || c.element),
                helper: c.helper,
                position: c.position,
                offset: c.positionAbs
            };
        },

        // Extension points just to make backcompat sane and avoid duplicating logic
        // TODO: Remove in 1.13 along with call to it below
        _addHoverClass: function () {
            this._addClass("ui-droppable-hover");
        },

        _removeHoverClass: function () {
            this._removeClass("ui-droppable-hover");
        },

        _addActiveClass: function () {
            this._addClass("ui-droppable-active");
        },

        _removeActiveClass: function () {
            this._removeClass("ui-droppable-active");
        }
    });

    var intersect = $.ui.intersect = (function () {
        function isOverAxis(x, reference, size) {
            return (x >= reference) && (x < (reference + size));
        }

        return function (draggable, droppable, toleranceMode, event) {

            if (!droppable.offset) {
                return false;
            }

            var x1 = (draggable.positionAbs ||
                    draggable.position.absolute).left + draggable.margins.left,
                y1 = (draggable.positionAbs ||
                    draggable.position.absolute).top + draggable.margins.top,
                x2 = x1 + draggable.helperProportions.width,
                y2 = y1 + draggable.helperProportions.height,
                l = droppable.offset.left,
                t = droppable.offset.top,
                r = l + droppable.proportions().width,
                b = t + droppable.proportions().height;

            switch (toleranceMode) {
                case "fit":
                    return (l <= x1 && x2 <= r && t <= y1 && y2 <= b);
                case "intersect":
                    return (l < x1 + (draggable.helperProportions.width / 2) && // Right Half
                        x2 - (draggable.helperProportions.width / 2) < r && // Left Half
                        t < y1 + (draggable.helperProportions.height / 2) && // Bottom Half
                        y2 - (draggable.helperProportions.height / 2) < b); // Top Half
                case "pointer":
                    return isOverAxis(event.pageY, t, droppable.proportions().height) &&
                        isOverAxis(event.pageX, l, droppable.proportions().width);
                case "touch":
                    return (
                        (y1 >= t && y1 <= b) || // Top edge touching
                        (y2 >= t && y2 <= b) || // Bottom edge touching
                        (y1 < t && y2 > b) // Surrounded vertically
                    ) && (
                        (x1 >= l && x1 <= r) || // Left edge touching
                        (x2 >= l && x2 <= r) || // Right edge touching
                        (x1 < l && x2 > r) // Surrounded horizontally
                    );
                default:
                    return false;
            }
        };
    })();

    /*
     This manager tracks offsets of draggables and droppables
     */
    $.ui.ddmanager = {
        current: null,
        droppables: {"default": []},
        prepareOffsets: function (t, event) {

            var i, j,
                m = $.ui.ddmanager.droppables[t.options.scope] || [],
                type = event ? event.type : null, // workaround for #2317
                list = (t.currentItem || t.element).find(":data(ui-droppable)").addBack();

            droppablesLoop: for (i = 0; i < m.length; i++) {

                // No disabled and non-accepted
                if (m[i].options.disabled || (t && !m[i].accept.call(m[i].element[0],
                    (t.currentItem || t.element)))) {
                    continue;
                }

                // Filter out elements in the current dragged item
                for (j = 0; j < list.length; j++) {
                    if (list[j] === m[i].element[0]) {
                        m[i].proportions().height = 0;
                        continue droppablesLoop;
                    }
                }

                m[i].visible = m[i].element.css("display") !== "none";
                if (!m[i].visible) {
                    continue;
                }

                // Activate the droppable if used directly from draggables
                if (type === "mousedown") {
                    m[i]._activate.call(m[i], event);
                }

                m[i].offset = m[i].element.offset();
                m[i].proportions({
                    width: m[i].element[0].offsetWidth,
                    height: m[i].element[0].offsetHeight
                });

            }

        },
        drop: function (draggable, event) {

            var dropped = false;

            // Create a copy of the droppables in case the list changes during the drop (#9116)
            $.each(($.ui.ddmanager.droppables[draggable.options.scope] || []).slice(), function () {

                if (!this.options) {
                    return;
                }
                if (!this.options.disabled && this.visible &&
                    intersect(draggable, this, this.options.tolerance, event)) {
                    dropped = this._drop.call(this, event) || dropped;
                }

                if (!this.options.disabled && this.visible && this.accept.call(this.element[0],
                    (draggable.currentItem || draggable.element))) {
                    this.isout = true;
                    this.isover = false;
                    this._deactivate.call(this, event);
                }

            });
            return dropped;

        },
        dragStart: function (draggable, event) {

            // Listen for scrolling so that if the dragging causes scrolling the position of the
            // droppables can be recalculated (see #5003)
            draggable.element.parentsUntil("body").on("scroll.droppable", function () {
                if (!draggable.options.refreshPositions) {
                    $.ui.ddmanager.prepareOffsets(draggable, event);
                }
            });
        },
        drag: function (draggable, event) {

            // If you have a highly dynamic page, you might try this option. It renders positions
            // every time you move the mouse.
            if (draggable.options.refreshPositions) {
                $.ui.ddmanager.prepareOffsets(draggable, event);
            }

            // Run through all droppables and check their positions based on specific tolerance options
            $.each($.ui.ddmanager.droppables[draggable.options.scope] || [], function () {

                if (this.options.disabled || this.greedyChild || !this.visible) {
                    return;
                }

                var parentInstance, scope, parent,
                    intersects = intersect(draggable, this, this.options.tolerance, event),
                    c = !intersects && this.isover ?
                        "isout" :
                        (intersects && !this.isover ? "isover" : null);
                if (!c) {
                    return;
                }

                if (this.options.greedy) {

                    // find droppable parents with same scope
                    scope = this.options.scope;
                    parent = this.element.parents(":data(ui-droppable)").filter(function () {
                        return $(this).droppable("instance").options.scope === scope;
                    });

                    if (parent.length) {
                        parentInstance = $(parent[0]).droppable("instance");
                        parentInstance.greedyChild = (c === "isover");
                    }
                }

                // We just moved into a greedy child
                if (parentInstance && c === "isover") {
                    parentInstance.isover = false;
                    parentInstance.isout = true;
                    parentInstance._out.call(parentInstance, event);
                }

                this[c] = true;
                this[c === "isout" ? "isover" : "isout"] = false;
                this[c === "isover" ? "_over" : "_out"].call(this, event);

                // We just moved out of a greedy child
                if (parentInstance && c === "isout") {
                    parentInstance.isout = false;
                    parentInstance.isover = true;
                    parentInstance._over.call(parentInstance, event);
                }
            });

        },
        dragStop: function (draggable, event) {
            draggable.element.parentsUntil("body").off("scroll.droppable");

            // Call prepareOffsets one final time since IE does not fire return scroll events when
            // overflow was caused by drag (see #5003)
            if (!draggable.options.refreshPositions) {
                $.ui.ddmanager.prepareOffsets(draggable, event);
            }
        }
    };

// DEPRECATED
// TODO: switch return back to widget declaration at top of file when this is removed
    if ($.uiBackCompat !== false) {

        // Backcompat for activeClass and hoverClass options
        $.widget("ui.droppable", $.ui.droppable, {
            options: {
                hoverClass: false,
                activeClass: false
            },
            _addActiveClass: function () {
                this._super();
                if (this.options.activeClass) {
                    this.element.addClass(this.options.activeClass);
                }
            },
            _removeActiveClass: function () {
                this._super();
                if (this.options.activeClass) {
                    this.element.removeClass(this.options.activeClass);
                }
            },
            _addHoverClass: function () {
                this._super();
                if (this.options.hoverClass) {
                    this.element.addClass(this.options.hoverClass);
                }
            },
            _removeHoverClass: function () {
                this._super();
                if (this.options.hoverClass) {
                    this.element.removeClass(this.options.hoverClass);
                }
            }
        });
    }

    var widgetsDroppable = $.ui.droppable;


    /*!
     * jQuery UI Sortable 1.12.1
     * http://jqueryui.com
     *
     * Copyright jQuery Foundation and other contributors
     * Released under the MIT license.
     * http://jquery.org/license
     */

//>>label: Sortable
//>>group: Interactions
//>>description: Enables items in a list to be sorted using the mouse.
//>>docs: http://api.jqueryui.com/sortable/
//>>demos: http://jqueryui.com/sortable/
//>>css.structure: ../../themes/base/sortable.css


    var widgetsSortable = $.widget("ui.sortable", $.ui.mouse, {
        version: "1.12.1",
        widgetEventPrefix: "sort",
        ready: false,
        options: {
            appendTo: "parent",
            axis: false,
            connectWith: false,
            containment: false,
            cursor: "auto",
            cursorAt: false,
            dropOnEmpty: true,
            forcePlaceholderSize: false,
            forceHelperSize: false,
            grid: false,
            handle: false,
            helper: "original",
            items: "> *",
            opacity: false,
            placeholder: false,
            revert: false,
            scroll: true,
            scrollSensitivity: 20,
            scrollSpeed: 20,
            scope: "default",
            tolerance: "intersect",
            zIndex: 1000,

            // Callbacks
            activate: null,
            beforeStop: null,
            change: null,
            deactivate: null,
            out: null,
            over: null,
            receive: null,
            remove: null,
            sort: null,
            start: null,
            stop: null,
            update: null
        },

        _isOverAxis: function (x, reference, size) {
            return (x >= reference) && (x < (reference + size));
        },

        _isFloating: function (item) {
            return (/left|right/).test(item.css("float")) ||
                (/inline|table-cell/).test(item.css("display"));
        },

        _create: function () {
            this.containerCache = {};
            this._addClass("ui-sortable");

            //Get the items
            this.refresh();

            //Let's determine the parent's offset
            this.offset = this.element.offset();

            //Initialize mouse events for interaction
            this._mouseInit();

            this._setHandleClassName();

            //We're ready to go
            this.ready = true;

        },

        _setOption: function (key, value) {
            this._super(key, value);

            if (key === "handle") {
                this._setHandleClassName();
            }
        },

        _setHandleClassName: function () {
            var that = this;
            this._removeClass(this.element.find(".ui-sortable-handle"), "ui-sortable-handle");
            $.each(this.items, function () {
                that._addClass(
                    this.instance.options.handle ?
                        this.item.find(this.instance.options.handle) :
                        this.item,
                    "ui-sortable-handle"
                );
            });
        },

        _destroy: function () {
            this._mouseDestroy();

            for (var i = this.items.length - 1; i >= 0; i--) {
                this.items[i].item.removeData(this.widgetName + "-item");
            }

            return this;
        },

        _mouseCapture: function (event, overrideHandle) {
            var currentItem = null,
                validHandle = false,
                that = this;

            if (this.reverting) {
                return false;
            }

            if (this.options.disabled || this.options.type === "static") {
                return false;
            }

            //We have to refresh the items data once first
            this._refreshItems(event);

            //Find out if the clicked node (or one of its parents) is a actual item in this.items
            $(event.target).parents().each(function () {
                if ($.data(this, that.widgetName + "-item") === that) {
                    currentItem = $(this);
                    return false;
                }
            });
            if ($.data(event.target, that.widgetName + "-item") === that) {
                currentItem = $(event.target);
            }

            if (!currentItem) {
                return false;
            }
            if (this.options.handle && !overrideHandle) {
                $(this.options.handle, currentItem).find("*").addBack().each(function () {
                    if (this === event.target) {
                        validHandle = true;
                    }
                });
                if (!validHandle) {
                    return false;
                }
            }

            this.currentItem = currentItem;
            this._removeCurrentsFromItems();
            return true;

        },

        _mouseStart: function (event, overrideHandle, noActivation) {

            var i, body,
                o = this.options;

            this.currentContainer = this;

            //We only need to call refreshPositions, because the refreshItems call has been moved to
            // mouseCapture
            this.refreshPositions();

            //Create and append the visible helper
            this.helper = this._createHelper(event);

            //Cache the helper size
            this._cacheHelperProportions();

            /*
             * - Position generation -
             * This block generates everything position related - it's the core of draggables.
             */

            //Cache the margins of the original element
            this._cacheMargins();

            //Get the next scrolling parent
            this.scrollParent = this.helper.scrollParent();

            //The element's absolute position on the page minus margins
            this.offset = this.currentItem.offset();
            this.offset = {
                top: this.offset.top - this.margins.top,
                left: this.offset.left - this.margins.left
            };

            $.extend(this.offset, {
                click: { //Where the click happened, relative to the element
                    left: event.pageX - this.offset.left,
                    top: event.pageY - this.offset.top
                },
                parent: this._getParentOffset(),

                // This is a relative to absolute position minus the actual position calculation -
                // only used for relative positioned helper
                relative: this._getRelativeOffset()
            });

            // Only after we got the offset, we can change the helper's position to absolute
            // TODO: Still need to figure out a way to make relative sorting possible
            this.helper.css("position", "absolute");
            this.cssPosition = this.helper.css("position");

            //Generate the original position
            this.originalPosition = this._generatePosition(event);
            this.originalPageX = event.pageX;
            this.originalPageY = event.pageY;

            //Adjust the mouse offset relative to the helper if "cursorAt" is supplied
            (o.cursorAt && this._adjustOffsetFromHelper(o.cursorAt));

            //Cache the former DOM position
            this.domPosition = {
                prev: this.currentItem.prev()[0],
                parent: this.currentItem.parent()[0]
            };

            // If the helper is not the original, hide the original so it's not playing any role during
            // the drag, won't cause anything bad this way
            if (this.helper[0] !== this.currentItem[0]) {
                this.currentItem.hide();
            }

            //Create the placeholder
            this._createPlaceholder();

            //Set a containment if given in the options
            if (o.containment) {
                this._setContainment();
            }

            if (o.cursor && o.cursor !== "auto") { // cursor option
                body = this.document.find("body");

                // Support: IE
                this.storedCursor = body.css("cursor");
                body.css("cursor", o.cursor);

                this.storedStylesheet =
                    $("<style>*{ cursor: " + o.cursor + " !important; }</style>").appendTo(body);
            }

            if (o.opacity) { // opacity option
                if (this.helper.css("opacity")) {
                    this._storedOpacity = this.helper.css("opacity");
                }
                this.helper.css("opacity", o.opacity);
            }

            if (o.zIndex) { // zIndex option
                if (this.helper.css("zIndex")) {
                    this._storedZIndex = this.helper.css("zIndex");
                }
                this.helper.css("zIndex", o.zIndex);
            }

            //Prepare scrolling
            if (this.scrollParent[0] !== this.document[0] &&
                this.scrollParent[0].tagName !== "HTML") {
                this.overflowOffset = this.scrollParent.offset();
            }

            //Call callbacks
            this._trigger("start", event, this._uiHash());

            //Recache the helper size
            if (!this._preserveHelperProportions) {
                this._cacheHelperProportions();
            }

            //Post "activate" events to possible containers
            if (!noActivation) {
                for (i = this.containers.length - 1; i >= 0; i--) {
                    this.containers[i]._trigger("activate", event, this._uiHash(this));
                }
            }

            //Prepare possible droppables
            if ($.ui.ddmanager) {
                $.ui.ddmanager.current = this;
            }

            if ($.ui.ddmanager && !o.dropBehaviour) {
                $.ui.ddmanager.prepareOffsets(this, event);
            }

            this.dragging = true;

            this._addClass(this.helper, "ui-sortable-helper");

            // Execute the drag once - this causes the helper not to be visiblebefore getting its
            // correct position
            this._mouseDrag(event);
            return true;

        },

        _mouseDrag: function (event) {
            var i, item, itemElement, intersection,
                o = this.options,
                scrolled = false;

            //Compute the helpers position
            this.position = this._generatePosition(event);
            this.positionAbs = this._convertPositionTo("absolute");

            if (!this.lastPositionAbs) {
                this.lastPositionAbs = this.positionAbs;
            }

            //Do scrolling
            if (this.options.scroll) {
                if (this.scrollParent[0] !== this.document[0] &&
                    this.scrollParent[0].tagName !== "HTML") {

                    if ((this.overflowOffset.top + this.scrollParent[0].offsetHeight) -
                        event.pageY < o.scrollSensitivity) {
                        this.scrollParent[0].scrollTop =
                            scrolled = this.scrollParent[0].scrollTop + o.scrollSpeed;
                    } else if (event.pageY - this.overflowOffset.top < o.scrollSensitivity) {
                        this.scrollParent[0].scrollTop =
                            scrolled = this.scrollParent[0].scrollTop - o.scrollSpeed;
                    }

                    if ((this.overflowOffset.left + this.scrollParent[0].offsetWidth) -
                        event.pageX < o.scrollSensitivity) {
                        this.scrollParent[0].scrollLeft = scrolled =
                            this.scrollParent[0].scrollLeft + o.scrollSpeed;
                    } else if (event.pageX - this.overflowOffset.left < o.scrollSensitivity) {
                        this.scrollParent[0].scrollLeft = scrolled =
                            this.scrollParent[0].scrollLeft - o.scrollSpeed;
                    }

                } else {

                    if (event.pageY - this.document.scrollTop() < o.scrollSensitivity) {
                        scrolled = this.document.scrollTop(this.document.scrollTop() - o.scrollSpeed);
                    } else if (this.window.height() - (event.pageY - this.document.scrollTop()) <
                        o.scrollSensitivity) {
                        scrolled = this.document.scrollTop(this.document.scrollTop() + o.scrollSpeed);
                    }

                    if (event.pageX - this.document.scrollLeft() < o.scrollSensitivity) {
                        scrolled = this.document.scrollLeft(
                            this.document.scrollLeft() - o.scrollSpeed
                        );
                    } else if (this.window.width() - (event.pageX - this.document.scrollLeft()) <
                        o.scrollSensitivity) {
                        scrolled = this.document.scrollLeft(
                            this.document.scrollLeft() + o.scrollSpeed
                        );
                    }

                }

                if (scrolled !== false && $.ui.ddmanager && !o.dropBehaviour) {
                    $.ui.ddmanager.prepareOffsets(this, event);
                }
            }

            //Regenerate the absolute position used for position checks
            this.positionAbs = this._convertPositionTo("absolute");

            //Set the helper position
            if (!this.options.axis || this.options.axis !== "y") {
                this.helper[0].style.left = this.position.left + "px";
            }
            if (!this.options.axis || this.options.axis !== "x") {
                this.helper[0].style.top = this.position.top + "px";
            }

            //Rearrange
            for (i = this.items.length - 1; i >= 0; i--) {

                //Cache variables and intersection, continue if no intersection
                item = this.items[i];
                itemElement = item.item[0];
                intersection = this._intersectsWithPointer(item);
                if (!intersection) {
                    continue;
                }

                // Only put the placeholder inside the current Container, skip all
                // items from other containers. This works because when moving
                // an item from one container to another the
                // currentContainer is switched before the placeholder is moved.
                //
                // Without this, moving items in "sub-sortables" can cause
                // the placeholder to jitter between the outer and inner container.
                if (item.instance !== this.currentContainer) {
                    continue;
                }

                // Cannot intersect with itself
                // no useless actions that have been done before
                // no action if the item moved is the parent of the item checked
                if (itemElement !== this.currentItem[0] &&
                    this.placeholder[intersection === 1 ? "next" : "prev"]()[0] !== itemElement &&
                    !$.contains(this.placeholder[0], itemElement) &&
                    (this.options.type === "semi-dynamic" ?
                            !$.contains(this.element[0], itemElement) :
                            true
                    )
                ) {

                    this.direction = intersection === 1 ? "down" : "up";

                    if (this.options.tolerance === "pointer" || this._intersectsWithSides(item)) {
                        this._rearrange(event, item);
                    } else {
                        break;
                    }

                    this._trigger("change", event, this._uiHash());
                    break;
                }
            }

            //Post events to containers
            this._contactContainers(event);

            //Interconnect with droppables
            if ($.ui.ddmanager) {
                $.ui.ddmanager.drag(this, event);
            }

            //Call callbacks
            this._trigger("sort", event, this._uiHash());

            this.lastPositionAbs = this.positionAbs;
            return false;

        },

        _mouseStop: function (event, noPropagation) {

            if (!event) {
                return;
            }

            //If we are using droppables, inform the manager about the drop
            if ($.ui.ddmanager && !this.options.dropBehaviour) {
                $.ui.ddmanager.drop(this, event);
            }

            if (this.options.revert) {
                var that = this,
                    cur = this.placeholder.offset(),
                    axis = this.options.axis,
                    animation = {};

                if (!axis || axis === "x") {
                    animation.left = cur.left - this.offset.parent.left - this.margins.left +
                        (this.offsetParent[0] === this.document[0].body ?
                                0 :
                                this.offsetParent[0].scrollLeft
                        );
                }
                if (!axis || axis === "y") {
                    animation.top = cur.top - this.offset.parent.top - this.margins.top +
                        (this.offsetParent[0] === this.document[0].body ?
                                0 :
                                this.offsetParent[0].scrollTop
                        );
                }
                this.reverting = true;
                $(this.helper).animate(
                    animation,
                    parseInt(this.options.revert, 10) || 500,
                    function () {
                        that._clear(event);
                    }
                );
            } else {
                this._clear(event, noPropagation);
            }

            return false;

        },

        cancel: function () {

            if (this.dragging) {

                this._mouseUp(new $.Event("mouseup", {target: null}));

                if (this.options.helper === "original") {
                    this.currentItem.css(this._storedCSS);
                    this._removeClass(this.currentItem, "ui-sortable-helper");
                } else {
                    this.currentItem.show();
                }

                //Post deactivating events to containers
                for (var i = this.containers.length - 1; i >= 0; i--) {
                    this.containers[i]._trigger("deactivate", null, this._uiHash(this));
                    if (this.containers[i].containerCache.over) {
                        this.containers[i]._trigger("out", null, this._uiHash(this));
                        this.containers[i].containerCache.over = 0;
                    }
                }

            }

            if (this.placeholder) {

                //$(this.placeholder[0]).remove(); would have been the jQuery way - unfortunately,
                // it unbinds ALL events from the original node!
                if (this.placeholder[0].parentNode) {
                    this.placeholder[0].parentNode.removeChild(this.placeholder[0]);
                }
                if (this.options.helper !== "original" && this.helper &&
                    this.helper[0].parentNode) {
                    this.helper.remove();
                }

                $.extend(this, {
                    helper: null,
                    dragging: false,
                    reverting: false,
                    _noFinalSort: null
                });

                if (this.domPosition.prev) {
                    $(this.domPosition.prev).after(this.currentItem);
                } else {
                    $(this.domPosition.parent).prepend(this.currentItem);
                }
            }

            return this;

        },

        serialize: function (o) {

            var items = this._getItemsAsjQuery(o && o.connected),
                str = [];
            o = o || {};

            $(items).each(function () {
                var res = ($(o.item || this).attr(o.attribute || "id") || "")
                    .match(o.expression || (/(.+)[\-=_](.+)/));
                if (res) {
                    str.push(
                        (o.key || res[1] + "[]") +
                        "=" + (o.key && o.expression ? res[1] : res[2]));
                }
            });

            if (!str.length && o.key) {
                str.push(o.key + "=");
            }

            return str.join("&");

        },

        toArray: function (o) {

            var items = this._getItemsAsjQuery(o && o.connected),
                ret = [];

            o = o || {};

            items.each(function () {
                ret.push($(o.item || this).attr(o.attribute || "id") || "");
            });
            return ret;

        },

        /* Be careful with the following core functions */
        _intersectsWith: function (item) {

            var x1 = this.positionAbs.left,
                x2 = x1 + this.helperProportions.width,
                y1 = this.positionAbs.top,
                y2 = y1 + this.helperProportions.height,
                l = item.left,
                r = l + item.width,
                t = item.top,
                b = t + item.height,
                dyClick = this.offset.click.top,
                dxClick = this.offset.click.left,
                isOverElementHeight = (this.options.axis === "x") || ((y1 + dyClick) > t &&
                    (y1 + dyClick) < b),
                isOverElementWidth = (this.options.axis === "y") || ((x1 + dxClick) > l &&
                    (x1 + dxClick) < r),
                isOverElement = isOverElementHeight && isOverElementWidth;

            if (this.options.tolerance === "pointer" ||
                this.options.forcePointerForContainers ||
                (this.options.tolerance !== "pointer" &&
                    this.helperProportions[this.floating ? "width" : "height"] >
                    item[this.floating ? "width" : "height"])
            ) {
                return isOverElement;
            } else {

                return (l < x1 + (this.helperProportions.width / 2) && // Right Half
                    x2 - (this.helperProportions.width / 2) < r && // Left Half
                    t < y1 + (this.helperProportions.height / 2) && // Bottom Half
                    y2 - (this.helperProportions.height / 2) < b); // Top Half

            }
        },

        _intersectsWithPointer: function (item) {
            var verticalDirection, horizontalDirection,
                isOverElementHeight = (this.options.axis === "x") ||
                    this._isOverAxis(
                        this.positionAbs.top + this.offset.click.top, item.top, item.height),
                isOverElementWidth = (this.options.axis === "y") ||
                    this._isOverAxis(
                        this.positionAbs.left + this.offset.click.left, item.left, item.width),
                isOverElement = isOverElementHeight && isOverElementWidth;

            if (!isOverElement) {
                return false;
            }

            verticalDirection = this._getDragVerticalDirection();
            horizontalDirection = this._getDragHorizontalDirection();

            return this.floating ?
                ((horizontalDirection === "right" || verticalDirection === "down") ? 2 : 1)
                : (verticalDirection && (verticalDirection === "down" ? 2 : 1));

        },

        _intersectsWithSides: function (item) {

            var isOverBottomHalf = this._isOverAxis(this.positionAbs.top +
                    this.offset.click.top, item.top + (item.height / 2), item.height),
                isOverRightHalf = this._isOverAxis(this.positionAbs.left +
                    this.offset.click.left, item.left + (item.width / 2), item.width),
                verticalDirection = this._getDragVerticalDirection(),
                horizontalDirection = this._getDragHorizontalDirection();

            if (this.floating && horizontalDirection) {
                return ((horizontalDirection === "right" && isOverRightHalf) ||
                    (horizontalDirection === "left" && !isOverRightHalf));
            } else {
                return verticalDirection && ((verticalDirection === "down" && isOverBottomHalf) ||
                    (verticalDirection === "up" && !isOverBottomHalf));
            }

        },

        _getDragVerticalDirection: function () {
            var delta = this.positionAbs.top - this.lastPositionAbs.top;
            return delta !== 0 && (delta > 0 ? "down" : "up");
        },

        _getDragHorizontalDirection: function () {
            var delta = this.positionAbs.left - this.lastPositionAbs.left;
            return delta !== 0 && (delta > 0 ? "right" : "left");
        },

        refresh: function (event) {
            this._refreshItems(event);
            this._setHandleClassName();
            this.refreshPositions();
            return this;
        },

        _connectWith: function () {
            var options = this.options;
            return options.connectWith.constructor === String ?
                [options.connectWith] :
                options.connectWith;
        },

        _getItemsAsjQuery: function (connected) {

            var i, j, cur, inst,
                items = [],
                queries = [],
                connectWith = this._connectWith();

            if (connectWith && connected) {
                for (i = connectWith.length - 1; i >= 0; i--) {
                    cur = $(connectWith[i], this.document[0]);
                    for (j = cur.length - 1; j >= 0; j--) {
                        inst = $.data(cur[j], this.widgetFullName);
                        if (inst && inst !== this && !inst.options.disabled) {
                            queries.push([$.isFunction(inst.options.items) ?
                                inst.options.items.call(inst.element) :
                                $(inst.options.items, inst.element)
                                    .not(".ui-sortable-helper")
                                    .not(".ui-sortable-placeholder"), inst]);
                        }
                    }
                }
            }

            queries.push([$.isFunction(this.options.items) ?
                this.options.items
                    .call(this.element, null, {options: this.options, item: this.currentItem}) :
                $(this.options.items, this.element)
                    .not(".ui-sortable-helper")
                    .not(".ui-sortable-placeholder"), this]);

            function addItems() {
                items.push(this);
            }

            for (i = queries.length - 1; i >= 0; i--) {
                queries[i][0].each(addItems);
            }

            return $(items);

        },

        _removeCurrentsFromItems: function () {

            var list = this.currentItem.find(":data(" + this.widgetName + "-item)");

            this.items = $.grep(this.items, function (item) {
                for (var j = 0; j < list.length; j++) {
                    if (list[j] === item.item[0]) {
                        return false;
                    }
                }
                return true;
            });

        },

        _refreshItems: function (event) {

            this.items = [];
            this.containers = [this];

            var i, j, cur, inst, targetData, _queries, item, queriesLength,
                items = this.items,
                queries = [[$.isFunction(this.options.items) ?
                    this.options.items.call(this.element[0], event, {item: this.currentItem}) :
                    $(this.options.items, this.element), this]],
                connectWith = this._connectWith();

            //Shouldn't be run the first time through due to massive slow-down
            if (connectWith && this.ready) {
                for (i = connectWith.length - 1; i >= 0; i--) {
                    cur = $(connectWith[i], this.document[0]);
                    for (j = cur.length - 1; j >= 0; j--) {
                        inst = $.data(cur[j], this.widgetFullName);
                        if (inst && inst !== this && !inst.options.disabled) {
                            queries.push([$.isFunction(inst.options.items) ?
                                inst.options.items
                                    .call(inst.element[0], event, {item: this.currentItem}) :
                                $(inst.options.items, inst.element), inst]);
                            this.containers.push(inst);
                        }
                    }
                }
            }

            for (i = queries.length - 1; i >= 0; i--) {
                targetData = queries[i][1];
                _queries = queries[i][0];

                for (j = 0, queriesLength = _queries.length; j < queriesLength; j++) {
                    item = $(_queries[j]);

                    // Data for target checking (mouse manager)
                    item.data(this.widgetName + "-item", targetData);

                    items.push({
                        item: item,
                        instance: targetData,
                        width: 0, height: 0,
                        left: 0, top: 0
                    });
                }
            }

        },

        refreshPositions: function (fast) {

            // Determine whether items are being displayed horizontally
            this.floating = this.items.length ?
                this.options.axis === "x" || this._isFloating(this.items[0].item) :
                false;

            //This has to be redone because due to the item being moved out/into the offsetParent,
            // the offsetParent's position will change
            if (this.offsetParent && this.helper) {
                this.offset.parent = this._getParentOffset();
            }

            var i, item, t, p;

            for (i = this.items.length - 1; i >= 0; i--) {
                item = this.items[i];

                //We ignore calculating positions of all connected containers when we're not over them
                if (item.instance !== this.currentContainer && this.currentContainer &&
                    item.item[0] !== this.currentItem[0]) {
                    continue;
                }

                t = this.options.toleranceElement ?
                    $(this.options.toleranceElement, item.item) :
                    item.item;

                if (!fast) {
                    item.width = t.outerWidth();
                    item.height = t.outerHeight();
                }

                p = t.offset();
                item.left = p.left;
                item.top = p.top;
            }

            if (this.options.custom && this.options.custom.refreshContainers) {
                this.options.custom.refreshContainers.call(this);
            } else {
                for (i = this.containers.length - 1; i >= 0; i--) {
                    p = this.containers[i].element.offset();
                    this.containers[i].containerCache.left = p.left;
                    this.containers[i].containerCache.top = p.top;
                    this.containers[i].containerCache.width =
                        this.containers[i].element.outerWidth();
                    this.containers[i].containerCache.height =
                        this.containers[i].element.outerHeight();
                }
            }

            return this;
        },

        _createPlaceholder: function (that) {
            that = that || this;
            var className,
                o = that.options;

            if (!o.placeholder || o.placeholder.constructor === String) {
                className = o.placeholder;
                o.placeholder = {
                    element: function () {

                        var nodeName = that.currentItem[0].nodeName.toLowerCase(),
                            element = $("<" + nodeName + ">", that.document[0]);

                        that._addClass(element, "ui-sortable-placeholder",
                            className || that.currentItem[0].className)
                            ._removeClass(element, "ui-sortable-helper");

                        if (nodeName === "tbody") {
                            that._createTrPlaceholder(
                                that.currentItem.find("tr").eq(0),
                                $("<tr>", that.document[0]).appendTo(element)
                            );
                        } else if (nodeName === "tr") {
                            that._createTrPlaceholder(that.currentItem, element);
                        } else if (nodeName === "img") {
                            element.attr("src", that.currentItem.attr("src"));
                        }

                        if (!className) {
                            element.css("visibility", "hidden");
                        }

                        return element;
                    },
                    update: function (container, p) {

                        // 1. If a className is set as 'placeholder option, we don't force sizes -
                        // the class is responsible for that
                        // 2. The option 'forcePlaceholderSize can be enabled to force it even if a
                        // class name is specified
                        if (className && !o.forcePlaceholderSize) {
                            return;
                        }

                        //If the element doesn't have a actual height by itself (without styles coming
                        // from a stylesheet), it receives the inline height from the dragged item
                        if (!p.height()) {
                            p.height(
                                that.currentItem.innerHeight() -
                                parseInt(that.currentItem.css("paddingTop") || 0, 10) -
                                parseInt(that.currentItem.css("paddingBottom") || 0, 10));
                        }
                        if (!p.width()) {
                            p.width(
                                that.currentItem.innerWidth() -
                                parseInt(that.currentItem.css("paddingLeft") || 0, 10) -
                                parseInt(that.currentItem.css("paddingRight") || 0, 10));
                        }
                    }
                };
            }

            //Create the placeholder
            that.placeholder = $(o.placeholder.element.call(that.element, that.currentItem));

            //Append it after the actual current item
            that.currentItem.after(that.placeholder);

            //Update the size of the placeholder (TODO: Logic to fuzzy, see line 316/317)
            o.placeholder.update(that, that.placeholder);

        },

        _createTrPlaceholder: function (sourceTr, targetTr) {
            var that = this;

            sourceTr.children().each(function () {
                $("<td>&#160;</td>", that.document[0])
                    .attr("colspan", $(this).attr("colspan") || 1)
                    .appendTo(targetTr);
            });
        },

        _contactContainers: function (event) {
            var i, j, dist, itemWithLeastDistance, posProperty, sizeProperty, cur, nearBottom,
                floating, axis,
                innermostContainer = null,
                innermostIndex = null;

            // Get innermost container that intersects with item
            for (i = this.containers.length - 1; i >= 0; i--) {

                // Never consider a container that's located within the item itself
                if ($.contains(this.currentItem[0], this.containers[i].element[0])) {
                    continue;
                }

                if (this._intersectsWith(this.containers[i].containerCache)) {

                    // If we've already found a container and it's more "inner" than this, then continue
                    if (innermostContainer &&
                        $.contains(
                            this.containers[i].element[0],
                            innermostContainer.element[0])) {
                        continue;
                    }

                    innermostContainer = this.containers[i];
                    innermostIndex = i;

                } else {

                    // container doesn't intersect. trigger "out" event if necessary
                    if (this.containers[i].containerCache.over) {
                        this.containers[i]._trigger("out", event, this._uiHash(this));
                        this.containers[i].containerCache.over = 0;
                    }
                }

            }

            // If no intersecting containers found, return
            if (!innermostContainer) {
                return;
            }

            // Move the item into the container if it's not there already
            if (this.containers.length === 1) {
                if (!this.containers[innermostIndex].containerCache.over) {
                    this.containers[innermostIndex]._trigger("over", event, this._uiHash(this));
                    this.containers[innermostIndex].containerCache.over = 1;
                }
            } else {

                // When entering a new container, we will find the item with the least distance and
                // append our item near it
                dist = 10000;
                itemWithLeastDistance = null;
                floating = innermostContainer.floating || this._isFloating(this.currentItem);
                posProperty = floating ? "left" : "top";
                sizeProperty = floating ? "width" : "height";
                axis = floating ? "pageX" : "pageY";

                for (j = this.items.length - 1; j >= 0; j--) {
                    if (!$.contains(
                        this.containers[innermostIndex].element[0], this.items[j].item[0])
                    ) {
                        continue;
                    }
                    if (this.items[j].item[0] === this.currentItem[0]) {
                        continue;
                    }

                    cur = this.items[j].item.offset()[posProperty];
                    nearBottom = false;
                    if (event[axis] - cur > this.items[j][sizeProperty] / 2) {
                        nearBottom = true;
                    }

                    if (Math.abs(event[axis] - cur) < dist) {
                        dist = Math.abs(event[axis] - cur);
                        itemWithLeastDistance = this.items[j];
                        this.direction = nearBottom ? "up" : "down";
                    }
                }

                //Check if dropOnEmpty is enabled
                if (!itemWithLeastDistance && !this.options.dropOnEmpty) {
                    return;
                }

                if (this.currentContainer === this.containers[innermostIndex]) {
                    if (!this.currentContainer.containerCache.over) {
                        this.containers[innermostIndex]._trigger("over", event, this._uiHash());
                        this.currentContainer.containerCache.over = 1;
                    }
                    return;
                }

                itemWithLeastDistance ?
                    this._rearrange(event, itemWithLeastDistance, null, true) :
                    this._rearrange(event, null, this.containers[innermostIndex].element, true);
                this._trigger("change", event, this._uiHash());
                this.containers[innermostIndex]._trigger("change", event, this._uiHash(this));
                this.currentContainer = this.containers[innermostIndex];

                //Update the placeholder
                this.options.placeholder.update(this.currentContainer, this.placeholder);

                this.containers[innermostIndex]._trigger("over", event, this._uiHash(this));
                this.containers[innermostIndex].containerCache.over = 1;
            }

        },

        _createHelper: function (event) {

            var o = this.options,
                helper = $.isFunction(o.helper) ?
                    $(o.helper.apply(this.element[0], [event, this.currentItem])) :
                    (o.helper === "clone" ? this.currentItem.clone() : this.currentItem);

            //Add the helper to the DOM if that didn't happen already
            if (!helper.parents("body").length) {
                $(o.appendTo !== "parent" ?
                    o.appendTo :
                    this.currentItem[0].parentNode)[0].appendChild(helper[0]);
            }

            if (helper[0] === this.currentItem[0]) {
                this._storedCSS = {
                    width: this.currentItem[0].style.width,
                    height: this.currentItem[0].style.height,
                    position: this.currentItem.css("position"),
                    top: this.currentItem.css("top"),
                    left: this.currentItem.css("left")
                };
            }

            if (!helper[0].style.width || o.forceHelperSize) {
                helper.width(this.currentItem.width());
            }
            if (!helper[0].style.height || o.forceHelperSize) {
                helper.height(this.currentItem.height());
            }

            return helper;

        },

        _adjustOffsetFromHelper: function (obj) {
            if (typeof obj === "string") {
                obj = obj.split(" ");
            }
            if ($.isArray(obj)) {
                obj = {left: +obj[0], top: +obj[1] || 0};
            }
            if ("left" in obj) {
                this.offset.click.left = obj.left + this.margins.left;
            }
            if ("right" in obj) {
                this.offset.click.left = this.helperProportions.width - obj.right + this.margins.left;
            }
            if ("top" in obj) {
                this.offset.click.top = obj.top + this.margins.top;
            }
            if ("bottom" in obj) {
                this.offset.click.top = this.helperProportions.height - obj.bottom + this.margins.top;
            }
        },

        _getParentOffset: function () {

            //Get the offsetParent and cache its position
            this.offsetParent = this.helper.offsetParent();
            var po = this.offsetParent.offset();

            // This is a special case where we need to modify a offset calculated on start, since the
            // following happened:
            // 1. The position of the helper is absolute, so it's position is calculated based on the
            // next positioned parent
            // 2. The actual offset parent is a child of the scroll parent, and the scroll parent isn't
            // the document, which means that the scroll is included in the initial calculation of the
            // offset of the parent, and never recalculated upon drag
            if (this.cssPosition === "absolute" && this.scrollParent[0] !== this.document[0] &&
                $.contains(this.scrollParent[0], this.offsetParent[0])) {
                po.left += this.scrollParent.scrollLeft();
                po.top += this.scrollParent.scrollTop();
            }

            // This needs to be actually done for all browsers, since pageX/pageY includes this
            // information with an ugly IE fix
            if (this.offsetParent[0] === this.document[0].body ||
                (this.offsetParent[0].tagName &&
                    this.offsetParent[0].tagName.toLowerCase() === "html" && $.ui.ie)) {
                po = {top: 0, left: 0};
            }

            return {
                top: po.top + (parseInt(this.offsetParent.css("borderTopWidth"), 10) || 0),
                left: po.left + (parseInt(this.offsetParent.css("borderLeftWidth"), 10) || 0)
            };

        },

        _getRelativeOffset: function () {

            if (this.cssPosition === "relative") {
                var p = this.currentItem.position();
                return {
                    top: p.top - (parseInt(this.helper.css("top"), 10) || 0) +
                        this.scrollParent.scrollTop(),
                    left: p.left - (parseInt(this.helper.css("left"), 10) || 0) +
                        this.scrollParent.scrollLeft()
                };
            } else {
                return {top: 0, left: 0};
            }

        },

        _cacheMargins: function () {
            this.margins = {
                left: (parseInt(this.currentItem.css("marginLeft"), 10) || 0),
                top: (parseInt(this.currentItem.css("marginTop"), 10) || 0)
            };
        },

        _cacheHelperProportions: function () {
            this.helperProportions = {
                width: this.helper.outerWidth(),
                height: this.helper.outerHeight()
            };
        },

        _setContainment: function () {

            var ce, co, over,
                o = this.options;
            if (o.containment === "parent") {
                o.containment = this.helper[0].parentNode;
            }
            if (o.containment === "document" || o.containment === "window") {
                this.containment = [
                    0 - this.offset.relative.left - this.offset.parent.left,
                    0 - this.offset.relative.top - this.offset.parent.top,
                    o.containment === "document" ?
                        this.document.width() :
                        this.window.width() - this.helperProportions.width - this.margins.left,
                    (o.containment === "document" ?
                            (this.document.height() || document.body.parentNode.scrollHeight) :
                            this.window.height() || this.document[0].body.parentNode.scrollHeight
                    ) - this.helperProportions.height - this.margins.top
                ];
            }

            if (!(/^(document|window|parent)$/).test(o.containment)) {
                ce = $(o.containment)[0];
                co = $(o.containment).offset();
                over = ($(ce).css("overflow") !== "hidden");

                this.containment = [
                    co.left + (parseInt($(ce).css("borderLeftWidth"), 10) || 0) +
                    (parseInt($(ce).css("paddingLeft"), 10) || 0) - this.margins.left,
                    co.top + (parseInt($(ce).css("borderTopWidth"), 10) || 0) +
                    (parseInt($(ce).css("paddingTop"), 10) || 0) - this.margins.top,
                    co.left + (over ? Math.max(ce.scrollWidth, ce.offsetWidth) : ce.offsetWidth) -
                    (parseInt($(ce).css("borderLeftWidth"), 10) || 0) -
                    (parseInt($(ce).css("paddingRight"), 10) || 0) -
                    this.helperProportions.width - this.margins.left,
                    co.top + (over ? Math.max(ce.scrollHeight, ce.offsetHeight) : ce.offsetHeight) -
                    (parseInt($(ce).css("borderTopWidth"), 10) || 0) -
                    (parseInt($(ce).css("paddingBottom"), 10) || 0) -
                    this.helperProportions.height - this.margins.top
                ];
            }

        },

        _convertPositionTo: function (d, pos) {

            if (!pos) {
                pos = this.position;
            }
            var mod = d === "absolute" ? 1 : -1,
                scroll = this.cssPosition === "absolute" &&
                !(this.scrollParent[0] !== this.document[0] &&
                    $.contains(this.scrollParent[0], this.offsetParent[0])) ?
                    this.offsetParent :
                    this.scrollParent,
                scrollIsRootNode = (/(html|body)/i).test(scroll[0].tagName);

            return {
                top: (

                    // The absolute mouse position
                    pos.top +

                    // Only for relative positioned nodes: Relative offset from element to offset parent
                    this.offset.relative.top * mod +

                    // The offsetParent's offset without borders (offset + border)
                    this.offset.parent.top * mod -
                    ((this.cssPosition === "fixed" ?
                        -this.scrollParent.scrollTop() :
                        (scrollIsRootNode ? 0 : scroll.scrollTop())) * mod)
                ),
                left: (

                    // The absolute mouse position
                    pos.left +

                    // Only for relative positioned nodes: Relative offset from element to offset parent
                    this.offset.relative.left * mod +

                    // The offsetParent's offset without borders (offset + border)
                    this.offset.parent.left * mod -
                    ((this.cssPosition === "fixed" ?
                        -this.scrollParent.scrollLeft() : scrollIsRootNode ? 0 :
                            scroll.scrollLeft()) * mod)
                )
            };

        },

        _generatePosition: function (event) {

            var top, left,
                o = this.options,
                pageX = event.pageX,
                pageY = event.pageY,
                scroll = this.cssPosition === "absolute" &&
                !(this.scrollParent[0] !== this.document[0] &&
                    $.contains(this.scrollParent[0], this.offsetParent[0])) ?
                    this.offsetParent :
                    this.scrollParent,
                scrollIsRootNode = (/(html|body)/i).test(scroll[0].tagName);

            // This is another very weird special case that only happens for relative elements:
            // 1. If the css position is relative
            // 2. and the scroll parent is the document or similar to the offset parent
            // we have to refresh the relative offset during the scroll so there are no jumps
            if (this.cssPosition === "relative" && !(this.scrollParent[0] !== this.document[0] &&
                this.scrollParent[0] !== this.offsetParent[0])) {
                this.offset.relative = this._getRelativeOffset();
            }

            /*
             * - Position constraining -
             * Constrain the position to a mix of grid, containment.
             */

            if (this.originalPosition) { //If we are not dragging yet, we won't check for options

                if (this.containment) {
                    if (event.pageX - this.offset.click.left < this.containment[0]) {
                        pageX = this.containment[0] + this.offset.click.left;
                    }
                    if (event.pageY - this.offset.click.top < this.containment[1]) {
                        pageY = this.containment[1] + this.offset.click.top;
                    }
                    if (event.pageX - this.offset.click.left > this.containment[2]) {
                        pageX = this.containment[2] + this.offset.click.left;
                    }
                    if (event.pageY - this.offset.click.top > this.containment[3]) {
                        pageY = this.containment[3] + this.offset.click.top;
                    }
                }

                if (o.grid) {
                    top = this.originalPageY + Math.round((pageY - this.originalPageY) /
                        o.grid[1]) * o.grid[1];
                    pageY = this.containment ?
                        ((top - this.offset.click.top >= this.containment[1] &&
                            top - this.offset.click.top <= this.containment[3]) ?
                            top :
                            ((top - this.offset.click.top >= this.containment[1]) ?
                                top - o.grid[1] : top + o.grid[1])) :
                        top;

                    left = this.originalPageX + Math.round((pageX - this.originalPageX) /
                        o.grid[0]) * o.grid[0];
                    pageX = this.containment ?
                        ((left - this.offset.click.left >= this.containment[0] &&
                            left - this.offset.click.left <= this.containment[2]) ?
                            left :
                            ((left - this.offset.click.left >= this.containment[0]) ?
                                left - o.grid[0] : left + o.grid[0])) :
                        left;
                }

            }

            return {
                top: (

                    // The absolute mouse position
                    pageY -

                    // Click offset (relative to the element)
                    this.offset.click.top -

                    // Only for relative positioned nodes: Relative offset from element to offset parent
                    this.offset.relative.top -

                    // The offsetParent's offset without borders (offset + border)
                    this.offset.parent.top +
                    ((this.cssPosition === "fixed" ?
                        -this.scrollParent.scrollTop() :
                        (scrollIsRootNode ? 0 : scroll.scrollTop())))
                ),
                left: (

                    // The absolute mouse position
                    pageX -

                    // Click offset (relative to the element)
                    this.offset.click.left -

                    // Only for relative positioned nodes: Relative offset from element to offset parent
                    this.offset.relative.left -

                    // The offsetParent's offset without borders (offset + border)
                    this.offset.parent.left +
                    ((this.cssPosition === "fixed" ?
                        -this.scrollParent.scrollLeft() :
                        scrollIsRootNode ? 0 : scroll.scrollLeft()))
                )
            };

        },

        _rearrange: function (event, i, a, hardRefresh) {

            a ? a[0].appendChild(this.placeholder[0]) :
                i.item[0].parentNode.insertBefore(this.placeholder[0],
                    (this.direction === "down" ? i.item[0] : i.item[0].nextSibling));

            //Various things done here to improve the performance:
            // 1. we create a setTimeout, that calls refreshPositions
            // 2. on the instance, we have a counter variable, that get's higher after every append
            // 3. on the local scope, we copy the counter variable, and check in the timeout,
            // if it's still the same
            // 4. this lets only the last addition to the timeout stack through
            this.counter = this.counter ? ++this.counter : 1;
            var counter = this.counter;

            this._delay(function () {
                if (counter === this.counter) {

                    //Precompute after each DOM insertion, NOT on mousemove
                    this.refreshPositions(!hardRefresh);
                }
            });

        },

        _clear: function (event, noPropagation) {

            this.reverting = false;

            // We delay all events that have to be triggered to after the point where the placeholder
            // has been removed and everything else normalized again
            var i,
                delayedTriggers = [];

            // We first have to update the dom position of the actual currentItem
            // Note: don't do it if the current item is already removed (by a user), or it gets
            // reappended (see #4088)
            if (!this._noFinalSort && this.currentItem.parent().length) {
                this.placeholder.before(this.currentItem);
            }
            this._noFinalSort = null;

            if (this.helper[0] === this.currentItem[0]) {
                for (i in this._storedCSS) {
                    if (this._storedCSS[i] === "auto" || this._storedCSS[i] === "static") {
                        this._storedCSS[i] = "";
                    }
                }
                this.currentItem.css(this._storedCSS);
                this._removeClass(this.currentItem, "ui-sortable-helper");
            } else {
                this.currentItem.show();
            }

            if (this.fromOutside && !noPropagation) {
                delayedTriggers.push(function (event) {
                    this._trigger("receive", event, this._uiHash(this.fromOutside));
                });
            }
            if ((this.fromOutside ||
                this.domPosition.prev !==
                this.currentItem.prev().not(".ui-sortable-helper")[0] ||
                this.domPosition.parent !== this.currentItem.parent()[0]) && !noPropagation) {

                // Trigger update callback if the DOM position has changed
                delayedTriggers.push(function (event) {
                    this._trigger("update", event, this._uiHash());
                });
            }

            // Check if the items Container has Changed and trigger appropriate
            // events.
            if (this !== this.currentContainer) {
                if (!noPropagation) {
                    delayedTriggers.push(function (event) {
                        this._trigger("remove", event, this._uiHash());
                    });
                    delayedTriggers.push((function (c) {
                        return function (event) {
                            c._trigger("receive", event, this._uiHash(this));
                        };
                    }).call(this, this.currentContainer));
                    delayedTriggers.push((function (c) {
                        return function (event) {
                            c._trigger("update", event, this._uiHash(this));
                        };
                    }).call(this, this.currentContainer));
                }
            }

            //Post events to containers
            function delayEvent(type, instance, container) {
                return function (event) {
                    container._trigger(type, event, instance._uiHash(instance));
                };
            }

            for (i = this.containers.length - 1; i >= 0; i--) {
                if (!noPropagation) {
                    delayedTriggers.push(delayEvent("deactivate", this, this.containers[i]));
                }
                if (this.containers[i].containerCache.over) {
                    delayedTriggers.push(delayEvent("out", this, this.containers[i]));
                    this.containers[i].containerCache.over = 0;
                }
            }

            //Do what was originally in plugins
            if (this.storedCursor) {
                this.document.find("body").css("cursor", this.storedCursor);
                this.storedStylesheet.remove();
            }
            if (this._storedOpacity) {
                this.helper.css("opacity", this._storedOpacity);
            }
            if (this._storedZIndex) {
                this.helper.css("zIndex", this._storedZIndex === "auto" ? "" : this._storedZIndex);
            }

            this.dragging = false;

            if (!noPropagation) {
                this._trigger("beforeStop", event, this._uiHash());
            }

            //$(this.placeholder[0]).remove(); would have been the jQuery way - unfortunately,
            // it unbinds ALL events from the original node!
            this.placeholder[0].parentNode.removeChild(this.placeholder[0]);

            if (!this.cancelHelperRemoval) {
                if (this.helper[0] !== this.currentItem[0]) {
                    this.helper.remove();
                }
                this.helper = null;
            }

            if (!noPropagation) {
                for (i = 0; i < delayedTriggers.length; i++) {

                    // Trigger all delayed events
                    delayedTriggers[i].call(this, event);
                }
                this._trigger("stop", event, this._uiHash());
            }

            this.fromOutside = false;
            return !this.cancelHelperRemoval;

        },

        _trigger: function () {
            if ($.Widget.prototype._trigger.apply(this, arguments) === false) {
                this.cancel();
            }
        },

        _uiHash: function (_inst) {
            var inst = _inst || this;
            return {
                helper: inst.helper,
                placeholder: inst.placeholder || $([]),
                position: inst.position,
                originalPosition: inst.originalPosition,
                offset: inst.positionAbs,
                item: inst.currentItem,
                sender: _inst ? _inst.element : null
            };
        }

    });


    /*!
     * jQuery UI Effects 1.12.1
     * http://jqueryui.com
     *
     * Copyright jQuery Foundation and other contributors
     * Released under the MIT license.
     * http://jquery.org/license
     */

//>>label: Effects Core
//>>group: Effects
// jscs:disable maximumLineLength
//>>description: Extends the internal jQuery effects. Includes morphing and easing. Required by all other effects.
// jscs:enable maximumLineLength
//>>docs: http://api.jqueryui.com/category/effects-core/
//>>demos: http://jqueryui.com/effect/


    var dataSpace = "ui-effects-",
        dataSpaceStyle = "ui-effects-style",
        dataSpaceAnimated = "ui-effects-animated",

        // Create a local jQuery because jQuery Color relies on it and the
        // global may not exist with AMD and a custom build (#10199)
        jQuery = $;

    $.effects = {
        effect: {}
    };

    /*!
     * jQuery Color Animations v2.1.2
     * https://github.com/jquery/jquery-color
     *
     * Copyright 2014 jQuery Foundation and other contributors
     * Released under the MIT license.
     * http://jquery.org/license
     *
     * Date: Wed Jan 16 08:47:09 2013 -0600
     */
    (function (jQuery, undefined) {

        var stepHooks = "backgroundColor borderBottomColor borderLeftColor borderRightColor " +
                "borderTopColor color columnRuleColor outlineColor textDecorationColor textEmphasisColor",

            // Plusequals test for += 100 -= 100
            rplusequals = /^([\-+])=\s*(\d+\.?\d*)/,

            // A set of RE's that can match strings and generate color tuples.
            stringParsers = [{
                re: /rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*(?:,\s*(\d?(?:\.\d+)?)\s*)?\)/,
                parse: function (execResult) {
                    return [
                        execResult[1],
                        execResult[2],
                        execResult[3],
                        execResult[4]
                    ];
                }
            }, {
                re: /rgba?\(\s*(\d+(?:\.\d+)?)\%\s*,\s*(\d+(?:\.\d+)?)\%\s*,\s*(\d+(?:\.\d+)?)\%\s*(?:,\s*(\d?(?:\.\d+)?)\s*)?\)/,
                parse: function (execResult) {
                    return [
                        execResult[1] * 2.55,
                        execResult[2] * 2.55,
                        execResult[3] * 2.55,
                        execResult[4]
                    ];
                }
            }, {

                // This regex ignores A-F because it's compared against an already lowercased string
                re: /#([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})/,
                parse: function (execResult) {
                    return [
                        parseInt(execResult[1], 16),
                        parseInt(execResult[2], 16),
                        parseInt(execResult[3], 16)
                    ];
                }
            }, {

                // This regex ignores A-F because it's compared against an already lowercased string
                re: /#([a-f0-9])([a-f0-9])([a-f0-9])/,
                parse: function (execResult) {
                    return [
                        parseInt(execResult[1] + execResult[1], 16),
                        parseInt(execResult[2] + execResult[2], 16),
                        parseInt(execResult[3] + execResult[3], 16)
                    ];
                }
            }, {
                re: /hsla?\(\s*(\d+(?:\.\d+)?)\s*,\s*(\d+(?:\.\d+)?)\%\s*,\s*(\d+(?:\.\d+)?)\%\s*(?:,\s*(\d?(?:\.\d+)?)\s*)?\)/,
                space: "hsla",
                parse: function (execResult) {
                    return [
                        execResult[1],
                        execResult[2] / 100,
                        execResult[3] / 100,
                        execResult[4]
                    ];
                }
            }],

            // JQuery.Color( )
            color = jQuery.Color = function (color, green, blue, alpha) {
                return new jQuery.Color.fn.parse(color, green, blue, alpha);
            },
            spaces = {
                rgba: {
                    props: {
                        red: {
                            idx: 0,
                            type: "byte"
                        },
                        green: {
                            idx: 1,
                            type: "byte"
                        },
                        blue: {
                            idx: 2,
                            type: "byte"
                        }
                    }
                },

                hsla: {
                    props: {
                        hue: {
                            idx: 0,
                            type: "degrees"
                        },
                        saturation: {
                            idx: 1,
                            type: "percent"
                        },
                        lightness: {
                            idx: 2,
                            type: "percent"
                        }
                    }
                }
            },
            propTypes = {
                "byte": {
                    floor: true,
                    max: 255
                },
                "percent": {
                    max: 1
                },
                "degrees": {
                    mod: 360,
                    floor: true
                }
            },
            support = color.support = {},

            // Element for support tests
            supportElem = jQuery("<p>")[0],

            // Colors = jQuery.Color.names
            colors,

            // Local aliases of functions called often
            each = jQuery.each;

// Determine rgba support immediately
        supportElem.style.cssText = "background-color:rgba(1,1,1,.5)";
        support.rgba = supportElem.style.backgroundColor.indexOf("rgba") > -1;

// Define cache name and alpha properties
// for rgba and hsla spaces
        each(spaces, function (spaceName, space) {
            space.cache = "_" + spaceName;
            space.props.alpha = {
                idx: 3,
                type: "percent",
                def: 1
            };
        });

        function clamp(value, prop, allowEmpty) {
            var type = propTypes[prop.type] || {};

            if (value == null) {
                return (allowEmpty || !prop.def) ? null : prop.def;
            }

            // ~~ is an short way of doing floor for positive numbers
            value = type.floor ? ~~value : parseFloat(value);

            // IE will pass in empty strings as value for alpha,
            // which will hit this case
            if (isNaN(value)) {
                return prop.def;
            }

            if (type.mod) {

                // We add mod before modding to make sure that negatives values
                // get converted properly: -10 -> 350
                return (value + type.mod) % type.mod;
            }

            // For now all property types without mod have min and max
            return 0 > value ? 0 : type.max < value ? type.max : value;
        }

        function stringParse(string) {
            var inst = color(),
                rgba = inst._rgba = [];

            string = string.toLowerCase();

            each(stringParsers, function (i, parser) {
                var parsed,
                    match = parser.re.exec(string),
                    values = match && parser.parse(match),
                    spaceName = parser.space || "rgba";

                if (values) {
                    parsed = inst[spaceName](values);

                    // If this was an rgba parse the assignment might happen twice
                    // oh well....
                    inst[spaces[spaceName].cache] = parsed[spaces[spaceName].cache];
                    rgba = inst._rgba = parsed._rgba;

                    // Exit each( stringParsers ) here because we matched
                    return false;
                }
            });

            // Found a stringParser that handled it
            if (rgba.length) {

                // If this came from a parsed string, force "transparent" when alpha is 0
                // chrome, (and maybe others) return "transparent" as rgba(0,0,0,0)
                if (rgba.join() === "0,0,0,0") {
                    jQuery.extend(rgba, colors.transparent);
                }
                return inst;
            }

            // Named colors
            return colors[string];
        }

        color.fn = jQuery.extend(color.prototype, {
            parse: function (red, green, blue, alpha) {
                if (red === undefined) {
                    this._rgba = [null, null, null, null];
                    return this;
                }
                if (red.jquery || red.nodeType) {
                    red = jQuery(red).css(green);
                    green = undefined;
                }

                var inst = this,
                    type = jQuery.type(red),
                    rgba = this._rgba = [];

                // More than 1 argument specified - assume ( red, green, blue, alpha )
                if (green !== undefined) {
                    red = [red, green, blue, alpha];
                    type = "array";
                }

                if (type === "string") {
                    return this.parse(stringParse(red) || colors._default);
                }

                if (type === "array") {
                    each(spaces.rgba.props, function (key, prop) {
                        rgba[prop.idx] = clamp(red[prop.idx], prop);
                    });
                    return this;
                }

                if (type === "object") {
                    if (red instanceof color) {
                        each(spaces, function (spaceName, space) {
                            if (red[space.cache]) {
                                inst[space.cache] = red[space.cache].slice();
                            }
                        });
                    } else {
                        each(spaces, function (spaceName, space) {
                            var cache = space.cache;
                            each(space.props, function (key, prop) {

                                // If the cache doesn't exist, and we know how to convert
                                if (!inst[cache] && space.to) {

                                    // If the value was null, we don't need to copy it
                                    // if the key was alpha, we don't need to copy it either
                                    if (key === "alpha" || red[key] == null) {
                                        return;
                                    }
                                    inst[cache] = space.to(inst._rgba);
                                }

                                // This is the only case where we allow nulls for ALL properties.
                                // call clamp with alwaysAllowEmpty
                                inst[cache][prop.idx] = clamp(red[key], prop, true);
                            });

                            // Everything defined but alpha?
                            if (inst[cache] &&
                                jQuery.inArray(null, inst[cache].slice(0, 3)) < 0) {

                                // Use the default of 1
                                inst[cache][3] = 1;
                                if (space.from) {
                                    inst._rgba = space.from(inst[cache]);
                                }
                            }
                        });
                    }
                    return this;
                }
            },
            is: function (compare) {
                var is = color(compare),
                    same = true,
                    inst = this;

                each(spaces, function (_, space) {
                    var localCache,
                        isCache = is[space.cache];
                    if (isCache) {
                        localCache = inst[space.cache] || space.to && space.to(inst._rgba) || [];
                        each(space.props, function (_, prop) {
                            if (isCache[prop.idx] != null) {
                                same = (isCache[prop.idx] === localCache[prop.idx]);
                                return same;
                            }
                        });
                    }
                    return same;
                });
                return same;
            },
            _space: function () {
                var used = [],
                    inst = this;
                each(spaces, function (spaceName, space) {
                    if (inst[space.cache]) {
                        used.push(spaceName);
                    }
                });
                return used.pop();
            },
            transition: function (other, distance) {
                var end = color(other),
                    spaceName = end._space(),
                    space = spaces[spaceName],
                    startColor = this.alpha() === 0 ? color("transparent") : this,
                    start = startColor[space.cache] || space.to(startColor._rgba),
                    result = start.slice();

                end = end[space.cache];
                each(space.props, function (key, prop) {
                    var index = prop.idx,
                        startValue = start[index],
                        endValue = end[index],
                        type = propTypes[prop.type] || {};

                    // If null, don't override start value
                    if (endValue === null) {
                        return;
                    }

                    // If null - use end
                    if (startValue === null) {
                        result[index] = endValue;
                    } else {
                        if (type.mod) {
                            if (endValue - startValue > type.mod / 2) {
                                startValue += type.mod;
                            } else if (startValue - endValue > type.mod / 2) {
                                startValue -= type.mod;
                            }
                        }
                        result[index] = clamp((endValue - startValue) * distance + startValue, prop);
                    }
                });
                return this[spaceName](result);
            },
            blend: function (opaque) {

                // If we are already opaque - return ourself
                if (this._rgba[3] === 1) {
                    return this;
                }

                var rgb = this._rgba.slice(),
                    a = rgb.pop(),
                    blend = color(opaque)._rgba;

                return color(jQuery.map(rgb, function (v, i) {
                    return (1 - a) * blend[i] + a * v;
                }));
            },
            toRgbaString: function () {
                var prefix = "rgba(",
                    rgba = jQuery.map(this._rgba, function (v, i) {
                        return v == null ? (i > 2 ? 1 : 0) : v;
                    });

                if (rgba[3] === 1) {
                    rgba.pop();
                    prefix = "rgb(";
                }

                return prefix + rgba.join() + ")";
            },
            toHslaString: function () {
                var prefix = "hsla(",
                    hsla = jQuery.map(this.hsla(), function (v, i) {
                        if (v == null) {
                            v = i > 2 ? 1 : 0;
                        }

                        // Catch 1 and 2
                        if (i && i < 3) {
                            v = Math.round(v * 100) + "%";
                        }
                        return v;
                    });

                if (hsla[3] === 1) {
                    hsla.pop();
                    prefix = "hsl(";
                }
                return prefix + hsla.join() + ")";
            },
            toHexString: function (includeAlpha) {
                var rgba = this._rgba.slice(),
                    alpha = rgba.pop();

                if (includeAlpha) {
                    rgba.push(~~(alpha * 255));
                }

                return "#" + jQuery.map(rgba, function (v) {

                    // Default to 0 when nulls exist
                    v = (v || 0).toString(16);
                    return v.length === 1 ? "0" + v : v;
                }).join("");
            },
            toString: function () {
                return this._rgba[3] === 0 ? "transparent" : this.toRgbaString();
            }
        });
        color.fn.parse.prototype = color.fn;

// Hsla conversions adapted from:
// https://code.google.com/p/maashaack/source/browse/packages/graphics/trunk/src/graphics/colors/HUE2RGB.as?r=5021

        function hue2rgb(p, q, h) {
            h = (h + 1) % 1;
            if (h * 6 < 1) {
                return p + (q - p) * h * 6;
            }
            if (h * 2 < 1) {
                return q;
            }
            if (h * 3 < 2) {
                return p + (q - p) * ((2 / 3) - h) * 6;
            }
            return p;
        }

        spaces.hsla.to = function (rgba) {
            if (rgba[0] == null || rgba[1] == null || rgba[2] == null) {
                return [null, null, null, rgba[3]];
            }
            var r = rgba[0] / 255,
                g = rgba[1] / 255,
                b = rgba[2] / 255,
                a = rgba[3],
                max = Math.max(r, g, b),
                min = Math.min(r, g, b),
                diff = max - min,
                add = max + min,
                l = add * 0.5,
                h, s;

            if (min === max) {
                h = 0;
            } else if (r === max) {
                h = (60 * (g - b) / diff) + 360;
            } else if (g === max) {
                h = (60 * (b - r) / diff) + 120;
            } else {
                h = (60 * (r - g) / diff) + 240;
            }

            // Chroma (diff) == 0 means greyscale which, by definition, saturation = 0%
            // otherwise, saturation is based on the ratio of chroma (diff) to lightness (add)
            if (diff === 0) {
                s = 0;
            } else if (l <= 0.5) {
                s = diff / add;
            } else {
                s = diff / (2 - add);
            }
            return [Math.round(h) % 360, s, l, a == null ? 1 : a];
        };

        spaces.hsla.from = function (hsla) {
            if (hsla[0] == null || hsla[1] == null || hsla[2] == null) {
                return [null, null, null, hsla[3]];
            }
            var h = hsla[0] / 360,
                s = hsla[1],
                l = hsla[2],
                a = hsla[3],
                q = l <= 0.5 ? l * (1 + s) : l + s - l * s,
                p = 2 * l - q;

            return [
                Math.round(hue2rgb(p, q, h + (1 / 3)) * 255),
                Math.round(hue2rgb(p, q, h) * 255),
                Math.round(hue2rgb(p, q, h - (1 / 3)) * 255),
                a
            ];
        };

        each(spaces, function (spaceName, space) {
            var props = space.props,
                cache = space.cache,
                to = space.to,
                from = space.from;

            // Makes rgba() and hsla()
            color.fn[spaceName] = function (value) {

                // Generate a cache for this space if it doesn't exist
                if (to && !this[cache]) {
                    this[cache] = to(this._rgba);
                }
                if (value === undefined) {
                    return this[cache].slice();
                }

                var ret,
                    type = jQuery.type(value),
                    arr = (type === "array" || type === "object") ? value : arguments,
                    local = this[cache].slice();

                each(props, function (key, prop) {
                    var val = arr[type === "object" ? key : prop.idx];
                    if (val == null) {
                        val = local[prop.idx];
                    }
                    local[prop.idx] = clamp(val, prop);
                });

                if (from) {
                    ret = color(from(local));
                    ret[cache] = local;
                    return ret;
                } else {
                    return color(local);
                }
            };

            // Makes red() green() blue() alpha() hue() saturation() lightness()
            each(props, function (key, prop) {

                // Alpha is included in more than one space
                if (color.fn[key]) {
                    return;
                }
                color.fn[key] = function (value) {
                    var vtype = jQuery.type(value),
                        fn = (key === "alpha" ? (this._hsla ? "hsla" : "rgba") : spaceName),
                        local = this[fn](),
                        cur = local[prop.idx],
                        match;

                    if (vtype === "undefined") {
                        return cur;
                    }

                    if (vtype === "function") {
                        value = value.call(this, cur);
                        vtype = jQuery.type(value);
                    }
                    if (value == null && prop.empty) {
                        return this;
                    }
                    if (vtype === "string") {
                        match = rplusequals.exec(value);
                        if (match) {
                            value = cur + parseFloat(match[2]) * (match[1] === "+" ? 1 : -1);
                        }
                    }
                    local[prop.idx] = value;
                    return this[fn](local);
                };
            });
        });

// Add cssHook and .fx.step function for each named hook.
// accept a space separated string of properties
        color.hook = function (hook) {
            var hooks = hook.split(" ");
            each(hooks, function (i, hook) {
                jQuery.cssHooks[hook] = {
                    set: function (elem, value) {
                        var parsed, curElem,
                            backgroundColor = "";

                        if (value !== "transparent" && (jQuery.type(value) !== "string" ||
                            (parsed = stringParse(value)))) {
                            value = color(parsed || value);
                            if (!support.rgba && value._rgba[3] !== 1) {
                                curElem = hook === "backgroundColor" ? elem.parentNode : elem;
                                while (
                                    (backgroundColor === "" || backgroundColor === "transparent") &&
                                    curElem && curElem.style
                                    ) {
                                    try {
                                        backgroundColor = jQuery.css(curElem, "backgroundColor");
                                        curElem = curElem.parentNode;
                                    } catch (e) {
                                    }
                                }

                                value = value.blend(backgroundColor && backgroundColor !== "transparent" ?
                                    backgroundColor :
                                    "_default");
                            }

                            value = value.toRgbaString();
                        }
                        try {
                            elem.style[hook] = value;
                        } catch (e) {

                            // Wrapped to prevent IE from throwing errors on "invalid" values like
                            // 'auto' or 'inherit'
                        }
                    }
                };
                jQuery.fx.step[hook] = function (fx) {
                    if (!fx.colorInit) {
                        fx.start = color(fx.elem, hook);
                        fx.end = color(fx.end);
                        fx.colorInit = true;
                    }
                    jQuery.cssHooks[hook].set(fx.elem, fx.start.transition(fx.end, fx.pos));
                };
            });

        };

        color.hook(stepHooks);

        jQuery.cssHooks.borderColor = {
            expand: function (value) {
                var expanded = {};

                each(["Top", "Right", "Bottom", "Left"], function (i, part) {
                    expanded["border" + part + "Color"] = value;
                });
                return expanded;
            }
        };

// Basic color names only.
// Usage of any of the other color names requires adding yourself or including
// jquery.color.svg-names.js.
        colors = jQuery.Color.names = {

            // 4.1. Basic color keywords
            aqua: "#00ffff",
            black: "#000000",
            blue: "#0000ff",
            fuchsia: "#ff00ff",
            gray: "#808080",
            green: "#008000",
            lime: "#00ff00",
            maroon: "#800000",
            navy: "#000080",
            olive: "#808000",
            purple: "#800080",
            red: "#ff0000",
            silver: "#c0c0c0",
            teal: "#008080",
            white: "#ffffff",
            yellow: "#ffff00",

            // 4.2.3. "transparent" color keyword
            transparent: [null, null, null, 0],

            _default: "#ffffff"
        };

    })(jQuery);

    /******************************************************************************/
    /****************************** CLASS ANIMATIONS ******************************/
    /******************************************************************************/
    (function () {

        var classAnimationActions = ["add", "remove", "toggle"],
            shorthandStyles = {
                border: 1,
                borderBottom: 1,
                borderColor: 1,
                borderLeft: 1,
                borderRight: 1,
                borderTop: 1,
                borderWidth: 1,
                margin: 1,
                padding: 1
            };

        $.each(
            ["borderLeftStyle", "borderRightStyle", "borderBottomStyle", "borderTopStyle"],
            function (_, prop) {
                $.fx.step[prop] = function (fx) {
                    if (fx.end !== "none" && !fx.setAttr || fx.pos === 1 && !fx.setAttr) {
                        jQuery.style(fx.elem, prop, fx.end);
                        fx.setAttr = true;
                    }
                };
            }
        );

        function getElementStyles(elem) {
            var key, len,
                style = elem.ownerDocument.defaultView ?
                    elem.ownerDocument.defaultView.getComputedStyle(elem, null) :
                    elem.currentStyle,
                styles = {};

            if (style && style.length && style[0] && style[style[0]]) {
                len = style.length;
                while (len--) {
                    key = style[len];
                    if (typeof style[key] === "string") {
                        styles[$.camelCase(key)] = style[key];
                    }
                }

                // Support: Opera, IE <9
            } else {
                for (key in style) {
                    if (typeof style[key] === "string") {
                        styles[key] = style[key];
                    }
                }
            }

            return styles;
        }

        function styleDifference(oldStyle, newStyle) {
            var diff = {},
                name, value;

            for (name in newStyle) {
                value = newStyle[name];
                if (oldStyle[name] !== value) {
                    if (!shorthandStyles[name]) {
                        if ($.fx.step[name] || !isNaN(parseFloat(value))) {
                            diff[name] = value;
                        }
                    }
                }
            }

            return diff;
        }

// Support: jQuery <1.8
        if (!$.fn.addBack) {
            $.fn.addBack = function (selector) {
                return this.add(selector == null ?
                    this.prevObject : this.prevObject.filter(selector)
                );
            };
        }

        $.effects.animateClass = function (value, duration, easing, callback) {
            var o = $.speed(duration, easing, callback);

            return this.queue(function () {
                var animated = $(this),
                    baseClass = animated.attr("class") || "",
                    applyClassChange,
                    allAnimations = o.children ? animated.find("*").addBack() : animated;

                // Map the animated objects to store the original styles.
                allAnimations = allAnimations.map(function () {
                    var el = $(this);
                    return {
                        el: el,
                        start: getElementStyles(this)
                    };
                });

                // Apply class change
                applyClassChange = function () {
                    $.each(classAnimationActions, function (i, action) {
                        if (value[action]) {
                            animated[action + "Class"](value[action]);
                        }
                    });
                };
                applyClassChange();

                // Map all animated objects again - calculate new styles and diff
                allAnimations = allAnimations.map(function () {
                    this.end = getElementStyles(this.el[0]);
                    this.diff = styleDifference(this.start, this.end);
                    return this;
                });

                // Apply original class
                animated.attr("class", baseClass);

                // Map all animated objects again - this time collecting a promise
                allAnimations = allAnimations.map(function () {
                    var styleInfo = this,
                        dfd = $.Deferred(),
                        opts = $.extend({}, o, {
                            queue: false,
                            complete: function () {
                                dfd.resolve(styleInfo);
                            }
                        });

                    this.el.animate(this.diff, opts);
                    return dfd.promise();
                });

                // Once all animations have completed:
                $.when.apply($, allAnimations.get()).done(function () {

                    // Set the final class
                    applyClassChange();

                    // For each animated element,
                    // clear all css properties that were animated
                    $.each(arguments, function () {
                        var el = this.el;
                        $.each(this.diff, function (key) {
                            el.css(key, "");
                        });
                    });

                    // This is guarnteed to be there if you use jQuery.speed()
                    // it also handles dequeuing the next anim...
                    o.complete.call(animated[0]);
                });
            });
        };

        $.fn.extend({
            addClass: (function (orig) {
                return function (classNames, speed, easing, callback) {
                    return speed ?
                        $.effects.animateClass.call(this,
                            {add: classNames}, speed, easing, callback) :
                        orig.apply(this, arguments);
                };
            })($.fn.addClass),

            removeClass: (function (orig) {
                return function (classNames, speed, easing, callback) {
                    return arguments.length > 1 ?
                        $.effects.animateClass.call(this,
                            {remove: classNames}, speed, easing, callback) :
                        orig.apply(this, arguments);
                };
            })($.fn.removeClass),

            toggleClass: (function (orig) {
                return function (classNames, force, speed, easing, callback) {
                    if (typeof force === "boolean" || force === undefined) {
                        if (!speed) {

                            // Without speed parameter
                            return orig.apply(this, arguments);
                        } else {
                            return $.effects.animateClass.call(this,
                                (force ? {add: classNames} : {remove: classNames}),
                                speed, easing, callback);
                        }
                    } else {

                        // Without force parameter
                        return $.effects.animateClass.call(this,
                            {toggle: classNames}, force, speed, easing);
                    }
                };
            })($.fn.toggleClass),

            switchClass: function (remove, add, speed, easing, callback) {
                return $.effects.animateClass.call(this, {
                    add: add,
                    remove: remove
                }, speed, easing, callback);
            }
        });

    })();

    /******************************************************************************/
    /*********************************** EFFECTS **********************************/
    /******************************************************************************/

    (function () {

        if ($.expr && $.expr.filters && $.expr.filters.animated) {
            $.expr.filters.animated = (function (orig) {
                return function (elem) {
                    return !!$(elem).data(dataSpaceAnimated) || orig(elem);
                };
            })($.expr.filters.animated);
        }

        if ($.uiBackCompat !== false) {
            $.extend($.effects, {

                // Saves a set of properties in a data storage
                save: function (element, set) {
                    var i = 0, length = set.length;
                    for (; i < length; i++) {
                        if (set[i] !== null) {
                            element.data(dataSpace + set[i], element[0].style[set[i]]);
                        }
                    }
                },

                // Restores a set of previously saved properties from a data storage
                restore: function (element, set) {
                    var val, i = 0, length = set.length;
                    for (; i < length; i++) {
                        if (set[i] !== null) {
                            val = element.data(dataSpace + set[i]);
                            element.css(set[i], val);
                        }
                    }
                },

                setMode: function (el, mode) {
                    if (mode === "toggle") {
                        mode = el.is(":hidden") ? "show" : "hide";
                    }
                    return mode;
                },

                // Wraps the element around a wrapper that copies position properties
                createWrapper: function (element) {

                    // If the element is already wrapped, return it
                    if (element.parent().is(".ui-effects-wrapper")) {
                        return element.parent();
                    }

                    // Wrap the element
                    var props = {
                            width: element.outerWidth(true),
                            height: element.outerHeight(true),
                            "float": element.css("float")
                        },
                        wrapper = $("<div></div>")
                            .addClass("ui-effects-wrapper")
                            .css({
                                fontSize: "100%",
                                background: "transparent",
                                border: "none",
                                margin: 0,
                                padding: 0
                            }),

                        // Store the size in case width/height are defined in % - Fixes #5245
                        size = {
                            width: element.width(),
                            height: element.height()
                        },
                        active = document.activeElement;

                    // Support: Firefox
                    // Firefox incorrectly exposes anonymous content
                    // https://bugzilla.mozilla.org/show_bug.cgi?id=561664
                    try {
                        active.id;
                    } catch (e) {
                        active = document.body;
                    }

                    element.wrap(wrapper);

                    // Fixes #7595 - Elements lose focus when wrapped.
                    if (element[0] === active || $.contains(element[0], active)) {
                        $(active).trigger("focus");
                    }

                    // Hotfix for jQuery 1.4 since some change in wrap() seems to actually
                    // lose the reference to the wrapped element
                    wrapper = element.parent();

                    // Transfer positioning properties to the wrapper
                    if (element.css("position") === "static") {
                        wrapper.css({position: "relative"});
                        element.css({position: "relative"});
                    } else {
                        $.extend(props, {
                            position: element.css("position"),
                            zIndex: element.css("z-index")
                        });
                        $.each(["top", "left", "bottom", "right"], function (i, pos) {
                            props[pos] = element.css(pos);
                            if (isNaN(parseInt(props[pos], 10))) {
                                props[pos] = "auto";
                            }
                        });
                        element.css({
                            position: "relative",
                            top: 0,
                            left: 0,
                            right: "auto",
                            bottom: "auto"
                        });
                    }
                    element.css(size);

                    return wrapper.css(props).show();
                },

                removeWrapper: function (element) {
                    var active = document.activeElement;

                    if (element.parent().is(".ui-effects-wrapper")) {
                        element.parent().replaceWith(element);

                        // Fixes #7595 - Elements lose focus when wrapped.
                        if (element[0] === active || $.contains(element[0], active)) {
                            $(active).trigger("focus");
                        }
                    }

                    return element;
                }
            });
        }

        $.extend($.effects, {
            version: "1.12.1",

            define: function (name, mode, effect) {
                if (!effect) {
                    effect = mode;
                    mode = "effect";
                }

                $.effects.effect[name] = effect;
                $.effects.effect[name].mode = mode;

                return effect;
            },

            scaledDimensions: function (element, percent, direction) {
                if (percent === 0) {
                    return {
                        height: 0,
                        width: 0,
                        outerHeight: 0,
                        outerWidth: 0
                    };
                }

                var x = direction !== "horizontal" ? ((percent || 100) / 100) : 1,
                    y = direction !== "vertical" ? ((percent || 100) / 100) : 1;

                return {
                    height: element.height() * y,
                    width: element.width() * x,
                    outerHeight: element.outerHeight() * y,
                    outerWidth: element.outerWidth() * x
                };

            },

            clipToBox: function (animation) {
                return {
                    width: animation.clip.right - animation.clip.left,
                    height: animation.clip.bottom - animation.clip.top,
                    left: animation.clip.left,
                    top: animation.clip.top
                };
            },

            // Injects recently queued functions to be first in line (after "inprogress")
            unshift: function (element, queueLength, count) {
                var queue = element.queue();

                if (queueLength > 1) {
                    queue.splice.apply(queue,
                        [1, 0].concat(queue.splice(queueLength, count)));
                }
                element.dequeue();
            },

            saveStyle: function (element) {
                element.data(dataSpaceStyle, element[0].style.cssText);
            },

            restoreStyle: function (element) {
                element[0].style.cssText = element.data(dataSpaceStyle) || "";
                element.removeData(dataSpaceStyle);
            },

            mode: function (element, mode) {
                var hidden = element.is(":hidden");

                if (mode === "toggle") {
                    mode = hidden ? "show" : "hide";
                }
                if (hidden ? mode === "hide" : mode === "show") {
                    mode = "none";
                }
                return mode;
            },

            // Translates a [top,left] array into a baseline value
            getBaseline: function (origin, original) {
                var y, x;

                switch (origin[0]) {
                    case "top":
                        y = 0;
                        break;
                    case "middle":
                        y = 0.5;
                        break;
                    case "bottom":
                        y = 1;
                        break;
                    default:
                        y = origin[0] / original.height;
                }

                switch (origin[1]) {
                    case "left":
                        x = 0;
                        break;
                    case "center":
                        x = 0.5;
                        break;
                    case "right":
                        x = 1;
                        break;
                    default:
                        x = origin[1] / original.width;
                }

                return {
                    x: x,
                    y: y
                };
            },

            // Creates a placeholder element so that the original element can be made absolute
            createPlaceholder: function (element) {
                var placeholder,
                    cssPosition = element.css("position"),
                    position = element.position();

                // Lock in margins first to account for form elements, which
                // will change margin if you explicitly set height
                // see: http://jsfiddle.net/JZSMt/3/ https://bugs.webkit.org/show_bug.cgi?id=107380
                // Support: Safari
                element.css({
                    marginTop: element.css("marginTop"),
                    marginBottom: element.css("marginBottom"),
                    marginLeft: element.css("marginLeft"),
                    marginRight: element.css("marginRight")
                })
                    .outerWidth(element.outerWidth())
                    .outerHeight(element.outerHeight());

                if (/^(static|relative)/.test(cssPosition)) {
                    cssPosition = "absolute";

                    placeholder = $("<" + element[0].nodeName + ">").insertAfter(element).css({

                        // Convert inline to inline block to account for inline elements
                        // that turn to inline block based on content (like img)
                        display: /^(inline|ruby)/.test(element.css("display")) ?
                            "inline-block" :
                            "block",
                        visibility: "hidden",

                        // Margins need to be set to account for margin collapse
                        marginTop: element.css("marginTop"),
                        marginBottom: element.css("marginBottom"),
                        marginLeft: element.css("marginLeft"),
                        marginRight: element.css("marginRight"),
                        "float": element.css("float")
                    })
                        .outerWidth(element.outerWidth())
                        .outerHeight(element.outerHeight())
                        .addClass("ui-effects-placeholder");

                    element.data(dataSpace + "placeholder", placeholder);
                }

                element.css({
                    position: cssPosition,
                    left: position.left,
                    top: position.top
                });

                return placeholder;
            },

            removePlaceholder: function (element) {
                var dataKey = dataSpace + "placeholder",
                    placeholder = element.data(dataKey);

                if (placeholder) {
                    placeholder.remove();
                    element.removeData(dataKey);
                }
            },

            // Removes a placeholder if it exists and restores
            // properties that were modified during placeholder creation
            cleanUp: function (element) {
                $.effects.restoreStyle(element);
                $.effects.removePlaceholder(element);
            },

            setTransition: function (element, list, factor, value) {
                value = value || {};
                $.each(list, function (i, x) {
                    var unit = element.cssUnit(x);
                    if (unit[0] > 0) {
                        value[x] = unit[0] * factor + unit[1];
                    }
                });
                return value;
            }
        });

// Return an effect options object for the given parameters:
        function _normalizeArguments(effect, options, speed, callback) {

            // Allow passing all options as the first parameter
            if ($.isPlainObject(effect)) {
                options = effect;
                effect = effect.effect;
            }

            // Convert to an object
            effect = {effect: effect};

            // Catch (effect, null, ...)
            if (options == null) {
                options = {};
            }

            // Catch (effect, callback)
            if ($.isFunction(options)) {
                callback = options;
                speed = null;
                options = {};
            }

            // Catch (effect, speed, ?)
            if (typeof options === "number" || $.fx.speeds[options]) {
                callback = speed;
                speed = options;
                options = {};
            }

            // Catch (effect, options, callback)
            if ($.isFunction(speed)) {
                callback = speed;
                speed = null;
            }

            // Add options to effect
            if (options) {
                $.extend(effect, options);
            }

            speed = speed || options.duration;
            effect.duration = $.fx.off ? 0 :
                typeof speed === "number" ? speed :
                    speed in $.fx.speeds ? $.fx.speeds[speed] :
                        $.fx.speeds._default;

            effect.complete = callback || options.complete;

            return effect;
        }

        function standardAnimationOption(option) {

            // Valid standard speeds (nothing, number, named speed)
            if (!option || typeof option === "number" || $.fx.speeds[option]) {
                return true;
            }

            // Invalid strings - treat as "normal" speed
            if (typeof option === "string" && !$.effects.effect[option]) {
                return true;
            }

            // Complete callback
            if ($.isFunction(option)) {
                return true;
            }

            // Options hash (but not naming an effect)
            if (typeof option === "object" && !option.effect) {
                return true;
            }

            // Didn't match any standard API
            return false;
        }

        $.fn.extend({
            effect: function ( /* effect, options, speed, callback */) {
                var args = _normalizeArguments.apply(this, arguments),
                    effectMethod = $.effects.effect[args.effect],
                    defaultMode = effectMethod.mode,
                    queue = args.queue,
                    queueName = queue || "fx",
                    complete = args.complete,
                    mode = args.mode,
                    modes = [],
                    prefilter = function (next) {
                        var el = $(this),
                            normalizedMode = $.effects.mode(el, mode) || defaultMode;

                        // Sentinel for duck-punching the :animated psuedo-selector
                        el.data(dataSpaceAnimated, true);

                        // Save effect mode for later use,
                        // we can't just call $.effects.mode again later,
                        // as the .show() below destroys the initial state
                        modes.push(normalizedMode);

                        // See $.uiBackCompat inside of run() for removal of defaultMode in 1.13
                        if (defaultMode && (normalizedMode === "show" ||
                            (normalizedMode === defaultMode && normalizedMode === "hide"))) {
                            el.show();
                        }

                        if (!defaultMode || normalizedMode !== "none") {
                            $.effects.saveStyle(el);
                        }

                        if ($.isFunction(next)) {
                            next();
                        }
                    };

                if ($.fx.off || !effectMethod) {

                    // Delegate to the original method (e.g., .show()) if possible
                    if (mode) {
                        return this[mode](args.duration, complete);
                    } else {
                        return this.each(function () {
                            if (complete) {
                                complete.call(this);
                            }
                        });
                    }
                }

                function run(next) {
                    var elem = $(this);

                    function cleanup() {
                        elem.removeData(dataSpaceAnimated);

                        $.effects.cleanUp(elem);

                        if (args.mode === "hide") {
                            elem.hide();
                        }

                        done();
                    }

                    function done() {
                        if ($.isFunction(complete)) {
                            complete.call(elem[0]);
                        }

                        if ($.isFunction(next)) {
                            next();
                        }
                    }

                    // Override mode option on a per element basis,
                    // as toggle can be either show or hide depending on element state
                    args.mode = modes.shift();

                    if ($.uiBackCompat !== false && !defaultMode) {
                        if (elem.is(":hidden") ? mode === "hide" : mode === "show") {

                            // Call the core method to track "olddisplay" properly
                            elem[mode]();
                            done();
                        } else {
                            effectMethod.call(elem[0], args, done);
                        }
                    } else {
                        if (args.mode === "none") {

                            // Call the core method to track "olddisplay" properly
                            elem[mode]();
                            done();
                        } else {
                            effectMethod.call(elem[0], args, cleanup);
                        }
                    }
                }

                // Run prefilter on all elements first to ensure that
                // any showing or hiding happens before placeholder creation,
                // which ensures that any layout changes are correctly captured.
                return queue === false ?
                    this.each(prefilter).each(run) :
                    this.queue(queueName, prefilter).queue(queueName, run);
            },

            show: (function (orig) {
                return function (option) {
                    if (standardAnimationOption(option)) {
                        return orig.apply(this, arguments);
                    } else {
                        var args = _normalizeArguments.apply(this, arguments);
                        args.mode = "show";
                        return this.effect.call(this, args);
                    }
                };
            })($.fn.show),

            hide: (function (orig) {
                return function (option) {
                    if (standardAnimationOption(option)) {
                        return orig.apply(this, arguments);
                    } else {
                        var args = _normalizeArguments.apply(this, arguments);
                        args.mode = "hide";
                        return this.effect.call(this, args);
                    }
                };
            })($.fn.hide),

            toggle: (function (orig) {
                return function (option) {
                    if (standardAnimationOption(option) || typeof option === "boolean") {
                        return orig.apply(this, arguments);
                    } else {
                        var args = _normalizeArguments.apply(this, arguments);
                        args.mode = "toggle";
                        return this.effect.call(this, args);
                    }
                };
            })($.fn.toggle),

            cssUnit: function (key) {
                var style = this.css(key),
                    val = [];

                $.each(["em", "px", "%", "pt"], function (i, unit) {
                    if (style.indexOf(unit) > 0) {
                        val = [parseFloat(style), unit];
                    }
                });
                return val;
            },

            cssClip: function (clipObj) {
                if (clipObj) {
                    return this.css("clip", "rect(" + clipObj.top + "px " + clipObj.right + "px " +
                        clipObj.bottom + "px " + clipObj.left + "px)");
                }
                return parseClip(this.css("clip"), this);
            },

            transfer: function (options, done) {
                var element = $(this),
                    target = $(options.to),
                    targetFixed = target.css("position") === "fixed",
                    body = $("body"),
                    fixTop = targetFixed ? body.scrollTop() : 0,
                    fixLeft = targetFixed ? body.scrollLeft() : 0,
                    endPosition = target.offset(),
                    animation = {
                        top: endPosition.top - fixTop,
                        left: endPosition.left - fixLeft,
                        height: target.innerHeight(),
                        width: target.innerWidth()
                    },
                    startPosition = element.offset(),
                    transfer = $("<div class='ui-effects-transfer'></div>")
                        .appendTo("body")
                        .addClass(options.className)
                        .css({
                            top: startPosition.top - fixTop,
                            left: startPosition.left - fixLeft,
                            height: element.innerHeight(),
                            width: element.innerWidth(),
                            position: targetFixed ? "fixed" : "absolute"
                        })
                        .animate(animation, options.duration, options.easing, function () {
                            transfer.remove();
                            if ($.isFunction(done)) {
                                done();
                            }
                        });
            }
        });

        function parseClip(str, element) {
            var outerWidth = element.outerWidth(),
                outerHeight = element.outerHeight(),
                clipRegex = /^rect\((-?\d*\.?\d*px|-?\d+%|auto),?\s*(-?\d*\.?\d*px|-?\d+%|auto),?\s*(-?\d*\.?\d*px|-?\d+%|auto),?\s*(-?\d*\.?\d*px|-?\d+%|auto)\)$/,
                values = clipRegex.exec(str) || ["", 0, outerWidth, outerHeight, 0];

            return {
                top: parseFloat(values[1]) || 0,
                right: values[2] === "auto" ? outerWidth : parseFloat(values[2]),
                bottom: values[3] === "auto" ? outerHeight : parseFloat(values[3]),
                left: parseFloat(values[4]) || 0
            };
        }

        $.fx.step.clip = function (fx) {
            if (!fx.clipInit) {
                fx.start = $(fx.elem).cssClip();
                if (typeof fx.end === "string") {
                    fx.end = parseClip(fx.end, fx.elem);
                }
                fx.clipInit = true;
            }

            $(fx.elem).cssClip({
                top: fx.pos * (fx.end.top - fx.start.top) + fx.start.top,
                right: fx.pos * (fx.end.right - fx.start.right) + fx.start.right,
                bottom: fx.pos * (fx.end.bottom - fx.start.bottom) + fx.start.bottom,
                left: fx.pos * (fx.end.left - fx.start.left) + fx.start.left
            });
        };

    })();

    /******************************************************************************/
    /*********************************** EASING ***********************************/
    /******************************************************************************/

    (function () {

// Based on easing equations from Robert Penner (http://www.robertpenner.com/easing)

        var baseEasings = {};

        $.each(["Quad", "Cubic", "Quart", "Quint", "Expo"], function (i, name) {
            baseEasings[name] = function (p) {
                return Math.pow(p, i + 2);
            };
        });

        $.extend(baseEasings, {
            Sine: function (p) {
                return 1 - Math.cos(p * Math.PI / 2);
            },
            Circ: function (p) {
                return 1 - Math.sqrt(1 - p * p);
            },
            Elastic: function (p) {
                return p === 0 || p === 1 ? p :
                    -Math.pow(2, 8 * (p - 1)) * Math.sin(((p - 1) * 80 - 7.5) * Math.PI / 15);
            },
            Back: function (p) {
                return p * p * (3 * p - 2);
            },
            Bounce: function (p) {
                var pow2,
                    bounce = 4;

                while (p < ((pow2 = Math.pow(2, --bounce)) - 1) / 11) {
                }
                return 1 / Math.pow(4, 3 - bounce) - 7.5625 * Math.pow((pow2 * 3 - 2) / 22 - p, 2);
            }
        });

        $.each(baseEasings, function (name, easeIn) {
            $.easing["easeIn" + name] = easeIn;
            $.easing["easeOut" + name] = function (p) {
                return 1 - easeIn(1 - p);
            };
            $.easing["easeInOut" + name] = function (p) {
                return p < 0.5 ?
                    easeIn(p * 2) / 2 :
                    1 - easeIn(p * -2 + 2) / 2;
            };
        });

    })();

    var effect = $.effects;


    /*!
     * jQuery UI Effects Highlight 1.12.1
     * http://jqueryui.com
     *
     * Copyright jQuery Foundation and other contributors
     * Released under the MIT license.
     * http://jquery.org/license
     */

//>>label: Highlight Effect
//>>group: Effects
//>>description: Highlights the background of an element in a defined color for a custom duration.
//>>docs: http://api.jqueryui.com/highlight-effect/
//>>demos: http://jqueryui.com/effect/


    var effectsEffectHighlight = $.effects.define("highlight", "show", function (options, done) {
        var element = $(this),
            animation = {
                backgroundColor: element.css("backgroundColor")
            };

        if (options.mode === "hide") {
            animation.opacity = 0;
        }

        $.effects.saveStyle(element);

        element
            .css({
                backgroundImage: "none",
                backgroundColor: options.color || "#ffff99"
            })
            .animate(animation, {
                queue: false,
                duration: options.duration,
                easing: options.easing,
                complete: done
            });
    });


}));