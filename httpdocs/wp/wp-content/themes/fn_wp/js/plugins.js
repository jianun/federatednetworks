/*
 * jQuery UI 1.7.1
 *
 * Copyright (c) 2009 AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://docs.jquery.com/UI
 */
;jQuery.ui || (function($) {

var _remove = $.fn.remove,
	isFF2 = $.browser.mozilla && (parseFloat($.browser.version) < 1.9);

//Helper functions and ui object
$.ui = {
	version: "1.7.1",

	// $.ui.plugin is deprecated.  Use the proxy pattern instead.
	plugin: {
		add: function(module, option, set) {
			var proto = $.ui[module].prototype;
			for(var i in set) {
				proto.plugins[i] = proto.plugins[i] || [];
				proto.plugins[i].push([option, set[i]]);
			}
		},
		call: function(instance, name, args) {
			var set = instance.plugins[name];
			if(!set || !instance.element[0].parentNode) { return; }

			for (var i = 0; i < set.length; i++) {
				if (instance.options[set[i][0]]) {
					set[i][1].apply(instance.element, args);
				}
			}
		}
	},

	contains: function(a, b) {
		return document.compareDocumentPosition
			? a.compareDocumentPosition(b) & 16
			: a !== b && a.contains(b);
	},

	hasScroll: function(el, a) {

		//If overflow is hidden, the element might have extra content, but the user wants to hide it
		if ($(el).css('overflow') == 'hidden') { return false; }

		var scroll = (a && a == 'left') ? 'scrollLeft' : 'scrollTop',
			has = false;

		if (el[scroll] > 0) { return true; }

		// TODO: determine which cases actually cause this to happen
		// if the element doesn't have the scroll set, see if it's possible to
		// set the scroll
		el[scroll] = 1;
		has = (el[scroll] > 0);
		el[scroll] = 0;
		return has;
	},

	isOverAxis: function(x, reference, size) {
		//Determines when x coordinate is over "b" element axis
		return (x > reference) && (x < (reference + size));
	},

	isOver: function(y, x, top, left, height, width) {
		//Determines when x, y coordinates is over "b" element
		return $.ui.isOverAxis(y, top, height) && $.ui.isOverAxis(x, left, width);
	},

	keyCode: {
		BACKSPACE: 8,
		CAPS_LOCK: 20,
		COMMA: 188,
		CONTROL: 17,
		DELETE: 46,
		DOWN: 40,
		END: 35,
		ENTER: 13,
		ESCAPE: 27,
		HOME: 36,
		INSERT: 45,
		LEFT: 37,
		NUMPAD_ADD: 107,
		NUMPAD_DECIMAL: 110,
		NUMPAD_DIVIDE: 111,
		NUMPAD_ENTER: 108,
		NUMPAD_MULTIPLY: 106,
		NUMPAD_SUBTRACT: 109,
		PAGE_DOWN: 34,
		PAGE_UP: 33,
		PERIOD: 190,
		RIGHT: 39,
		SHIFT: 16,
		SPACE: 32,
		TAB: 9,
		UP: 38
	}
};

// WAI-ARIA normalization
if (isFF2) {
	var attr = $.attr,
		removeAttr = $.fn.removeAttr,
		ariaNS = "http://www.w3.org/2005/07/aaa",
		ariaState = /^aria-/,
		ariaRole = /^wairole:/;

	$.attr = function(elem, name, value) {
		var set = value !== undefined;

		return (name == 'role'
			? (set
				? attr.call(this, elem, name, "wairole:" + value)
				: (attr.apply(this, arguments) || "").replace(ariaRole, ""))
			: (ariaState.test(name)
				? (set
					? elem.setAttributeNS(ariaNS,
						name.replace(ariaState, "aaa:"), value)
					: attr.call(this, elem, name.replace(ariaState, "aaa:")))
				: attr.apply(this, arguments)));
	};

	$.fn.removeAttr = function(name) {
		return (ariaState.test(name)
			? this.each(function() {
				this.removeAttributeNS(ariaNS, name.replace(ariaState, ""));
			}) : removeAttr.call(this, name));
	};
}

//jQuery plugins
$.fn.extend({
	remove: function() {
		// Safari has a native remove event which actually removes DOM elements,
		// so we have to use triggerHandler instead of trigger (#3037).
		$("*", this).add(this).each(function() {
			$(this).triggerHandler("remove");
		});
		return _remove.apply(this, arguments );
	},

	enableSelection: function() {
		return this
			.attr('unselectable', 'off')
			.css('MozUserSelect', '')
			.unbind('selectstart.ui');
	},

	disableSelection: function() {
		return this
			.attr('unselectable', 'on')
			.css('MozUserSelect', 'none')
			.bind('selectstart.ui', function() { return false; });
	},

	scrollParent: function() {
		var scrollParent;
		if(($.browser.msie && (/(static|relative)/).test(this.css('position'))) || (/absolute/).test(this.css('position'))) {
			scrollParent = this.parents().filter(function() {
				return (/(relative|absolute|fixed)/).test($.curCSS(this,'position',1)) && (/(auto|scroll)/).test($.curCSS(this,'overflow',1)+$.curCSS(this,'overflow-y',1)+$.curCSS(this,'overflow-x',1));
			}).eq(0);
		} else {
			scrollParent = this.parents().filter(function() {
				return (/(auto|scroll)/).test($.curCSS(this,'overflow',1)+$.curCSS(this,'overflow-y',1)+$.curCSS(this,'overflow-x',1));
			}).eq(0);
		}

		return (/fixed/).test(this.css('position')) || !scrollParent.length ? $(document) : scrollParent;
	}
});


//Additional selectors
$.extend($.expr[':'], {
	data: function(elem, i, match) {
		return !!$.data(elem, match[3]);
	},

	focusable: function(element) {
		var nodeName = element.nodeName.toLowerCase(),
			tabIndex = $.attr(element, 'tabindex');
		return (/input|select|textarea|button|object/.test(nodeName)
			? !element.disabled
			: 'a' == nodeName || 'area' == nodeName
				? element.href || !isNaN(tabIndex)
				: !isNaN(tabIndex))
			// the element and all of its ancestors must be visible
			// the browser may report that the area is hidden
			&& !$(element)['area' == nodeName ? 'parents' : 'closest'](':hidden').length;
	},

	tabbable: function(element) {
		var tabIndex = $.attr(element, 'tabindex');
		return (isNaN(tabIndex) || tabIndex >= 0) && $(element).is(':focusable');
	}
});


// $.widget is a factory to create jQuery plugins
// taking some boilerplate code out of the plugin code
function getter(namespace, plugin, method, args) {
	function getMethods(type) {
		var methods = $[namespace][plugin][type] || [];
		return (typeof methods == 'string' ? methods.split(/,?\s+/) : methods);
	}

	var methods = getMethods('getter');
	if (args.length == 1 && typeof args[0] == 'string') {
		methods = methods.concat(getMethods('getterSetter'));
	}
	return ($.inArray(method, methods) != -1);
}

$.widget = function(name, prototype) {
	var namespace = name.split(".")[0];
	name = name.split(".")[1];

	// create plugin method
	$.fn[name] = function(options) {
		var isMethodCall = (typeof options == 'string'),
			args = Array.prototype.slice.call(arguments, 1);

		// prevent calls to internal methods
		if (isMethodCall && options.substring(0, 1) == '_') {
			return this;
		}

		// handle getter methods
		if (isMethodCall && getter(namespace, name, options, args)) {
			var instance = $.data(this[0], name);
			return (instance ? instance[options].apply(instance, args)
				: undefined);
		}

		// handle initialization and non-getter methods
		return this.each(function() {
			var instance = $.data(this, name);

			// constructor
			(!instance && !isMethodCall &&
				$.data(this, name, new $[namespace][name](this, options))._init());

			// method call
			(instance && isMethodCall && $.isFunction(instance[options]) &&
				instance[options].apply(instance, args));
		});
	};

	// create widget constructor
	$[namespace] = $[namespace] || {};
	$[namespace][name] = function(element, options) {
		var self = this;

		this.namespace = namespace;
		this.widgetName = name;
		this.widgetEventPrefix = $[namespace][name].eventPrefix || name;
		this.widgetBaseClass = namespace + '-' + name;

		this.options = $.extend({},
			$.widget.defaults,
			$[namespace][name].defaults,
			$.metadata && $.metadata.get(element)[name],
			options);

		this.element = $(element)
			.bind('setData.' + name, function(event, key, value) {
				if (event.target == element) {
					return self._setData(key, value);
				}
			})
			.bind('getData.' + name, function(event, key) {
				if (event.target == element) {
					return self._getData(key);
				}
			})
			.bind('remove', function() {
				return self.destroy();
			});
	};

	// add widget prototype
	$[namespace][name].prototype = $.extend({}, $.widget.prototype, prototype);

	// TODO: merge getter and getterSetter properties from widget prototype
	// and plugin prototype
	$[namespace][name].getterSetter = 'option';
};

$.widget.prototype = {
	_init: function() {},
	destroy: function() {
		this.element.removeData(this.widgetName)
			.removeClass(this.widgetBaseClass + '-disabled' + ' ' + this.namespace + '-state-disabled')
			.removeAttr('aria-disabled');
	},

	option: function(key, value) {
		var options = key,
			self = this;

		if (typeof key == "string") {
			if (value === undefined) {
				return this._getData(key);
			}
			options = {};
			options[key] = value;
		}

		$.each(options, function(key, value) {
			self._setData(key, value);
		});
	},
	_getData: function(key) {
		return this.options[key];
	},
	_setData: function(key, value) {
		this.options[key] = value;

		if (key == 'disabled') {
			this.element
				[value ? 'addClass' : 'removeClass'](
					this.widgetBaseClass + '-disabled' + ' ' +
					this.namespace + '-state-disabled')
				.attr("aria-disabled", value);
		}
	},

	enable: function() {
		this._setData('disabled', false);
	},
	disable: function() {
		this._setData('disabled', true);
	},

	_trigger: function(type, event, data) {
		var callback = this.options[type],
			eventName = (type == this.widgetEventPrefix
				? type : this.widgetEventPrefix + type);

		event = $.Event(event);
		event.type = eventName;

		// copy original event properties over to the new event
		// this would happen if we could call $.event.fix instead of $.Event
		// but we don't have a way to force an event to be fixed multiple times
		if (event.originalEvent) {
			for (var i = $.event.props.length, prop; i;) {
				prop = $.event.props[--i];
				event[prop] = event.originalEvent[prop];
			}
		}

		this.element.trigger(event, data);

		return !($.isFunction(callback) && callback.call(this.element[0], event, data) === false
			|| event.isDefaultPrevented());
	}
};

$.widget.defaults = {
	disabled: false
};


/** Mouse Interaction Plugin **/

$.ui.mouse = {
	_mouseInit: function() {
		var self = this;

		this.element
			.bind('mousedown.'+this.widgetName, function(event) {
				return self._mouseDown(event);
			})
			.bind('click.'+this.widgetName, function(event) {
				if(self._preventClickEvent) {
					self._preventClickEvent = false;
					event.stopImmediatePropagation();
					return false;
				}
			});

		// Prevent text selection in IE
		if ($.browser.msie) {
			this._mouseUnselectable = this.element.attr('unselectable');
			this.element.attr('unselectable', 'on');
		}

		this.started = false;
	},

	// TODO: make sure destroying one instance of mouse doesn't mess with
	// other instances of mouse
	_mouseDestroy: function() {
		this.element.unbind('.'+this.widgetName);

		// Restore text selection in IE
		($.browser.msie
			&& this.element.attr('unselectable', this._mouseUnselectable));
	},

	_mouseDown: function(event) {
		// don't let more than one widget handle mouseStart
		// TODO: figure out why we have to use originalEvent
		event.originalEvent = event.originalEvent || {};
		if (event.originalEvent.mouseHandled) { return; }

		// we may have missed mouseup (out of window)
		(this._mouseStarted && this._mouseUp(event));

		this._mouseDownEvent = event;

		var self = this,
			btnIsLeft = (event.which == 1),
			elIsCancel = (typeof this.options.cancel == "string" ? $(event.target).parents().add(event.target).filter(this.options.cancel).length : false);
		if (!btnIsLeft || elIsCancel || !this._mouseCapture(event)) {
			return true;
		}

		this.mouseDelayMet = !this.options.delay;
		if (!this.mouseDelayMet) {
			this._mouseDelayTimer = setTimeout(function() {
				self.mouseDelayMet = true;
			}, this.options.delay);
		}

		if (this._mouseDistanceMet(event) && this._mouseDelayMet(event)) {
			this._mouseStarted = (this._mouseStart(event) !== false);
			if (!this._mouseStarted) {
				event.preventDefault();
				return true;
			}
		}

		// these delegates are required to keep context
		this._mouseMoveDelegate = function(event) {
			return self._mouseMove(event);
		};
		this._mouseUpDelegate = function(event) {
			return self._mouseUp(event);
		};
		$(document)
			.bind('mousemove.'+this.widgetName, this._mouseMoveDelegate)
			.bind('mouseup.'+this.widgetName, this._mouseUpDelegate);

		// preventDefault() is used to prevent the selection of text here -
		// however, in Safari, this causes select boxes not to be selectable
		// anymore, so this fix is needed
		($.browser.safari || event.preventDefault());

		event.originalEvent.mouseHandled = true;
		return true;
	},

	_mouseMove: function(event) {
		// IE mouseup check - mouseup happened when mouse was out of window
		if ($.browser.msie && !event.button) {
			return this._mouseUp(event);
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

	_mouseUp: function(event) {
		$(document)
			.unbind('mousemove.'+this.widgetName, this._mouseMoveDelegate)
			.unbind('mouseup.'+this.widgetName, this._mouseUpDelegate);

		if (this._mouseStarted) {
			this._mouseStarted = false;
			this._preventClickEvent = (event.target == this._mouseDownEvent.target);
			this._mouseStop(event);
		}

		return false;
	},

	_mouseDistanceMet: function(event) {
		return (Math.max(
				Math.abs(this._mouseDownEvent.pageX - event.pageX),
				Math.abs(this._mouseDownEvent.pageY - event.pageY)
			) >= this.options.distance
		);
	},

	_mouseDelayMet: function(event) {
		return this.mouseDelayMet;
	},

	// These are placeholder methods, to be overriden by extending plugin
	_mouseStart: function(event) {},
	_mouseDrag: function(event) {},
	_mouseStop: function(event) {},
	_mouseCapture: function(event) { return true; }
};

$.ui.mouse.defaults = {
	cancel: null,
	distance: 1,
	delay: 0
};

})(jQuery);



/*
 * jQuery UI Tabs 1.7.1
 *
 * Copyright (c) 2009 AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://docs.jquery.com/UI/Tabs
 *
 * Depends:
 *	ui.core.js
 */
(function($) {

$.widget("ui.tabs", {

	_init: function() {
		if (this.options.deselectable !== undefined) {
			this.options.collapsible = this.options.deselectable;
		}
		this._tabify(true);
	},

	_setData: function(key, value) {
		if (key == 'selected') {
			if (this.options.collapsible && value == this.options.selected) {
				return;
			}
			this.select(value);
		}
		else {
			this.options[key] = value;
			if (key == 'deselectable') {
				this.options.collapsible = value;
			}
			this._tabify();
		}
	},

	_tabId: function(a) {
		return a.title && a.title.replace(/\s/g, '_').replace(/[^A-Za-z0-9\-_:\.]/g, '') ||
			this.options.idPrefix + $.data(a);
	},

	_sanitizeSelector: function(hash) {
		return hash.replace(/:/g, '\\:'); // we need this because an id may contain a ":"
	},

	_cookie: function() {
		var cookie = this.cookie || (this.cookie = this.options.cookie.name || 'ui-tabs-' + $.data(this.list[0]));
		return $.cookie.apply(null, [cookie].concat($.makeArray(arguments)));
	},

	_ui: function(tab, panel) {
		return {
			tab: tab,
			panel: panel,
			index: this.anchors.index(tab)
		};
	},

	_cleanup: function() {
		// restore all former loading tabs labels
		this.lis.filter('.ui-state-processing').removeClass('ui-state-processing')
				.find('span:data(label.tabs)')
				.each(function() {
					var el = $(this);
					el.html(el.data('label.tabs')).removeData('label.tabs');
				});
	},

	_tabify: function(init) {

		this.list = this.element.children('ul:first');
		this.lis = $('li:has(a[href])', this.list);
		this.anchors = this.lis.map(function() { return $('a', this)[0]; });
		this.panels = $([]);

		var self = this, o = this.options;

		var fragmentId = /^#.+/; // Safari 2 reports '#' for an empty hash
		this.anchors.each(function(i, a) {
			var href = $(a).attr('href');

			// For dynamically created HTML that contains a hash as href IE < 8 expands
			// such href to the full page url with hash and then misinterprets tab as ajax.
			// Same consideration applies for an added tab with a fragment identifier
			// since a[href=#fragment-identifier] does unexpectedly not match.
			// Thus normalize href attribute...
			var hrefBase = href.split('#')[0], baseEl;
			if (hrefBase && (hrefBase === location.toString().split('#')[0] ||
					(baseEl = $('base')[0]) && hrefBase === baseEl.href)) {
				href = a.hash;
				a.href = href;
			}

			// inline tab
			if (fragmentId.test(href)) {
				self.panels = self.panels.add(self._sanitizeSelector(href));
			}

			// remote tab
			else if (href != '#') { // prevent loading the page itself if href is just "#"
				$.data(a, 'href.tabs', href); // required for restore on destroy

				// TODO until #3808 is fixed strip fragment identifier from url
				// (IE fails to load from such url)
				$.data(a, 'load.tabs', href.replace(/#.*$/, '')); // mutable data

				var id = self._tabId(a);
				a.href = '#' + id;
				var $panel = $('#' + id);
				if (!$panel.length) {
					$panel = $(o.panelTemplate).attr('id', id).addClass('ui-tabs-panel ui-widget-content ui-corner-bottom')
						.insertAfter(self.panels[i - 1] || self.list);
					$panel.data('destroy.tabs', true);
				}
				self.panels = self.panels.add($panel);
			}

			// invalid tab href
			else {
				o.disabled.push(i);
			}
		});

		// initialization from scratch
		if (init) {

			// attach necessary classes for styling
			this.element.addClass('ui-tabs ui-widget ui-widget-content ui-corner-all');
			this.list.addClass('ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all');
			this.lis.addClass('ui-state-default ui-corner-top');
			this.panels.addClass('ui-tabs-panel ui-widget-content ui-corner-bottom');

			// Selected tab
			// use "selected" option or try to retrieve:
			// 1. from fragment identifier in url
			// 2. from cookie
			// 3. from selected class attribute on <li>
			if (o.selected === undefined) {
				if (location.hash) {
					this.anchors.each(function(i, a) {
						if (a.hash == location.hash) {
							o.selected = i;
							return false; // break
						}
					});
				}
				if (typeof o.selected != 'number' && o.cookie) {
					o.selected = parseInt(self._cookie(), 10);
				}
				if (typeof o.selected != 'number' && this.lis.filter('.ui-tabs-selected').length) {
					o.selected = this.lis.index(this.lis.filter('.ui-tabs-selected'));
				}
				o.selected = o.selected || 0;
			}
			else if (o.selected === null) { // usage of null is deprecated, TODO remove in next release
				o.selected = -1;
			}

			// sanity check - default to first tab...
			o.selected = ((o.selected >= 0 && this.anchors[o.selected]) || o.selected < 0) ? o.selected : 0;

			// Take disabling tabs via class attribute from HTML
			// into account and update option properly.
			// A selected tab cannot become disabled.
			o.disabled = $.unique(o.disabled.concat(
				$.map(this.lis.filter('.ui-state-disabled'),
					function(n, i) { return self.lis.index(n); } )
			)).sort();

			if ($.inArray(o.selected, o.disabled) != -1) {
				o.disabled.splice($.inArray(o.selected, o.disabled), 1);
			}

			// highlight selected tab
			this.panels.addClass('ui-tabs-hide');
			this.lis.removeClass('ui-tabs-selected ui-state-active');
			if (o.selected >= 0 && this.anchors.length) { // check for length avoids error when initializing empty list
				this.panels.eq(o.selected).removeClass('ui-tabs-hide');
				this.lis.eq(o.selected).addClass('ui-tabs-selected ui-state-active');

				// seems to be expected behavior that the show callback is fired
				self.element.queue("tabs", function() {
					self._trigger('show', null, self._ui(self.anchors[o.selected], self.panels[o.selected]));
				});
				
				this.load(o.selected);
			}

			// clean up to avoid memory leaks in certain versions of IE 6
			$(window).bind('unload', function() {
				self.lis.add(self.anchors).unbind('.tabs');
				self.lis = self.anchors = self.panels = null;
			});

		}
		// update selected after add/remove
		else {
			o.selected = this.lis.index(this.lis.filter('.ui-tabs-selected'));
		}

		// update collapsible
		this.element[o.collapsible ? 'addClass' : 'removeClass']('ui-tabs-collapsible');

		// set or update cookie after init and add/remove respectively
		if (o.cookie) {
			this._cookie(o.selected, o.cookie);
		}

		// disable tabs
		for (var i = 0, li; (li = this.lis[i]); i++) {
			$(li)[$.inArray(i, o.disabled) != -1 &&
				!$(li).hasClass('ui-tabs-selected') ? 'addClass' : 'removeClass']('ui-state-disabled');
		}

		// reset cache if switching from cached to not cached
		if (o.cache === false) {
			this.anchors.removeData('cache.tabs');
		}

		// remove all handlers before, tabify may run on existing tabs after add or option change
		this.lis.add(this.anchors).unbind('.tabs');

		if (o.event != 'mouseover') {
			var addState = function(state, el) {
				if (el.is(':not(.ui-state-disabled)')) {
					el.addClass('ui-state-' + state);
				}
			};
			var removeState = function(state, el) {
				el.removeClass('ui-state-' + state);
			};
			this.lis.bind('mouseover.tabs', function() {
				addState('hover', $(this));
			});
			this.lis.bind('mouseout.tabs', function() {
				removeState('hover', $(this));
			});
			this.anchors.bind('focus.tabs', function() {
				addState('focus', $(this).closest('li'));
			});
			this.anchors.bind('blur.tabs', function() {
				removeState('focus', $(this).closest('li'));
			});
		}

		// set up animations
		var hideFx, showFx;
		if (o.fx) {
			if ($.isArray(o.fx)) {
				hideFx = o.fx[0];
				showFx = o.fx[1];
			}
			else {
				hideFx = showFx = o.fx;
			}
		}

		// Reset certain styles left over from animation
		// and prevent IE's ClearType bug...
		function resetStyle($el, fx) {
			$el.css({ display: '' });
			if ($.browser.msie && fx.opacity) {
				$el[0].style.removeAttribute('filter');
			}
		}

		// Show a tab...
		var showTab = showFx ?
			function(clicked, $show) {
				$(clicked).closest('li').removeClass('ui-state-default').addClass('ui-tabs-selected ui-state-active');
				$show.hide().removeClass('ui-tabs-hide') // avoid flicker that way
					.animate(showFx, showFx.duration || 'normal', function() {
						resetStyle($show, showFx);
						self._trigger('show', null, self._ui(clicked, $show[0]));
					});
			} :
			function(clicked, $show) {
				$(clicked).closest('li').removeClass('ui-state-default').addClass('ui-tabs-selected ui-state-active');
				$show.removeClass('ui-tabs-hide');
				self._trigger('show', null, self._ui(clicked, $show[0]));
			};

		// Hide a tab, $show is optional...
		var hideTab = hideFx ?
			function(clicked, $hide) {
				$hide.animate(hideFx, hideFx.duration || 'normal', function() {
					self.lis.removeClass('ui-tabs-selected ui-state-active').addClass('ui-state-default');
					$hide.addClass('ui-tabs-hide');
					resetStyle($hide, hideFx);
					self.element.dequeue("tabs");
				});
			} :
			function(clicked, $hide, $show) {
				self.lis.removeClass('ui-tabs-selected ui-state-active').addClass('ui-state-default');
				$hide.addClass('ui-tabs-hide');
				self.element.dequeue("tabs");
			};

		// attach tab event handler, unbind to avoid duplicates from former tabifying...
		this.anchors.bind(o.event + '.tabs', function() {
			var el = this, $li = $(this).closest('li'), $hide = self.panels.filter(':not(.ui-tabs-hide)'),
					$show = $(self._sanitizeSelector(this.hash));

			// If tab is already selected and not collapsible or tab disabled or
			// or is already loading or click callback returns false stop here.
			// Check if click handler returns false last so that it is not executed
			// for a disabled or loading tab!
			if (($li.hasClass('ui-tabs-selected') && !o.collapsible) ||
				$li.hasClass('ui-state-disabled') ||
				$li.hasClass('ui-state-processing') ||
				self._trigger('select', null, self._ui(this, $show[0])) === false) {
				this.blur();
				return false;
			}

			o.selected = self.anchors.index(this);

			self.abort();

			// if tab may be closed
			if (o.collapsible) {
				if ($li.hasClass('ui-tabs-selected')) {
					o.selected = -1;

					if (o.cookie) {
						self._cookie(o.selected, o.cookie);
					}

					self.element.queue("tabs", function() {
						hideTab(el, $hide);
					}).dequeue("tabs");
					
					this.blur();
					return false;
				}
				else if (!$hide.length) {
					if (o.cookie) {
						self._cookie(o.selected, o.cookie);
					}
					
					self.element.queue("tabs", function() {
						showTab(el, $show);
					});

					self.load(self.anchors.index(this)); // TODO make passing in node possible, see also http://dev.jqueryui.com/ticket/3171
					
					this.blur();
					return false;
				}
			}

			if (o.cookie) {
				self._cookie(o.selected, o.cookie);
			}

			// show new tab
			if ($show.length) {
				if ($hide.length) {
					self.element.queue("tabs", function() {
						hideTab(el, $hide);
					});
				}
				self.element.queue("tabs", function() {
					showTab(el, $show);
				});
				
				self.load(self.anchors.index(this));
			}
			else {
				throw 'jQuery UI Tabs: Mismatching fragment identifier.';
			}

			// Prevent IE from keeping other link focussed when using the back button
			// and remove dotted border from clicked link. This is controlled via CSS
			// in modern browsers; blur() removes focus from address bar in Firefox
			// which can become a usability and annoying problem with tabs('rotate').
			if ($.browser.msie) {
				this.blur();
			}

		});

		// disable click in any case
		this.anchors.bind('click.tabs', function(){return false;});

	},

	destroy: function() {
		var o = this.options;

		this.abort();
		
		this.element.unbind('.tabs')
			.removeClass('ui-tabs ui-widget ui-widget-content ui-corner-all ui-tabs-collapsible')
			.removeData('tabs');

		this.list.removeClass('ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all');

		this.anchors.each(function() {
			var href = $.data(this, 'href.tabs');
			if (href) {
				this.href = href;
			}
			var $this = $(this).unbind('.tabs');
			$.each(['href', 'load', 'cache'], function(i, prefix) {
				$this.removeData(prefix + '.tabs');
			});
		});

		this.lis.unbind('.tabs').add(this.panels).each(function() {
			if ($.data(this, 'destroy.tabs')) {
				$(this).remove();
			}
			else {
				$(this).removeClass([
					'ui-state-default',
					'ui-corner-top',
					'ui-tabs-selected',
					'ui-state-active',
					'ui-state-hover',
					'ui-state-focus',
					'ui-state-disabled',
					'ui-tabs-panel',
					'ui-widget-content',
					'ui-corner-bottom',
					'ui-tabs-hide'
				].join(' '));
			}
		});

		if (o.cookie) {
			this._cookie(null, o.cookie);
		}
	},

	add: function(url, label, index) {
		if (index === undefined) {
			index = this.anchors.length; // append by default
		}

		var self = this, o = this.options,
			$li = $(o.tabTemplate.replace(/#\{href\}/g, url).replace(/#\{label\}/g, label)),
			id = !url.indexOf('#') ? url.replace('#', '') : this._tabId($('a', $li)[0]);

		$li.addClass('ui-state-default ui-corner-top').data('destroy.tabs', true);

		// try to find an existing element before creating a new one
		var $panel = $('#' + id);
		if (!$panel.length) {
			$panel = $(o.panelTemplate).attr('id', id).data('destroy.tabs', true);
		}
		$panel.addClass('ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide');

		if (index >= this.lis.length) {
			$li.appendTo(this.list);
			$panel.appendTo(this.list[0].parentNode);
		}
		else {
			$li.insertBefore(this.lis[index]);
			$panel.insertBefore(this.panels[index]);
		}

		o.disabled = $.map(o.disabled,
			function(n, i) { return n >= index ? ++n : n; });

		this._tabify();

		if (this.anchors.length == 1) { // after tabify
			$li.addClass('ui-tabs-selected ui-state-active');
			$panel.removeClass('ui-tabs-hide');
			this.element.queue("tabs", function() {
				self._trigger('show', null, self._ui(self.anchors[0], self.panels[0]));
			});
				
			this.load(0);
		}

		// callback
		this._trigger('add', null, this._ui(this.anchors[index], this.panels[index]));
	},

	remove: function(index) {
		var o = this.options, $li = this.lis.eq(index).remove(),
			$panel = this.panels.eq(index).remove();

		// If selected tab was removed focus tab to the right or
		// in case the last tab was removed the tab to the left.
		if ($li.hasClass('ui-tabs-selected') && this.anchors.length > 1) {
			this.select(index + (index + 1 < this.anchors.length ? 1 : -1));
		}

		o.disabled = $.map($.grep(o.disabled, function(n, i) { return n != index; }),
			function(n, i) { return n >= index ? --n : n; });

		this._tabify();

		// callback
		this._trigger('remove', null, this._ui($li.find('a')[0], $panel[0]));
	},

	enable: function(index) {
		var o = this.options;
		if ($.inArray(index, o.disabled) == -1) {
			return;
		}

		this.lis.eq(index).removeClass('ui-state-disabled');
		o.disabled = $.grep(o.disabled, function(n, i) { return n != index; });

		// callback
		this._trigger('enable', null, this._ui(this.anchors[index], this.panels[index]));
	},

	disable: function(index) {
		var self = this, o = this.options;
		if (index != o.selected) { // cannot disable already selected tab
			this.lis.eq(index).addClass('ui-state-disabled');

			o.disabled.push(index);
			o.disabled.sort();

			// callback
			this._trigger('disable', null, this._ui(this.anchors[index], this.panels[index]));
		}
	},

	select: function(index) {
		if (typeof index == 'string') {
			index = this.anchors.index(this.anchors.filter('[href$=' + index + ']'));
		}
		else if (index === null) { // usage of null is deprecated, TODO remove in next release
			index = -1;
		}
		if (index == -1 && this.options.collapsible) {
			index = this.options.selected;
		}

		this.anchors.eq(index).trigger(this.options.event + '.tabs');
	},

	load: function(index) {
		var self = this, o = this.options, a = this.anchors.eq(index)[0], url = $.data(a, 'load.tabs');

		this.abort();

		// not remote or from cache
		if (!url || this.element.queue("tabs").length !== 0 && $.data(a, 'cache.tabs')) {
			this.element.dequeue("tabs");
			return;
		}

		// load remote from here on
		this.lis.eq(index).addClass('ui-state-processing');

		if (o.spinner) {
			var span = $('span', a);
			span.data('label.tabs', span.html()).html(o.spinner);
		}

		this.xhr = $.ajax($.extend({}, o.ajaxOptions, {
			url: url,
			success: function(r, s) {
				$(self._sanitizeSelector(a.hash)).html(r);

				// take care of tab labels
				self._cleanup();

				if (o.cache) {
					$.data(a, 'cache.tabs', true); // if loaded once do not load them again
				}

				// callbacks
				self._trigger('load', null, self._ui(self.anchors[index], self.panels[index]));
				try {
					o.ajaxOptions.success(r, s);
				}
				catch (e) {}

				// last, so that load event is fired before show...
				self.element.dequeue("tabs");
			}
		}));
	},

	abort: function() {
		// stop possibly running animations
		this.element.queue([]);
		this.panels.stop(false, true);

		// terminate pending requests from other tabs
		if (this.xhr) {
			this.xhr.abort();
			delete this.xhr;
		}

		// take care of tab labels
		this._cleanup();

	},

	url: function(index, url) {
		this.anchors.eq(index).removeData('cache.tabs').data('load.tabs', url);
	},

	length: function() {
		return this.anchors.length;
	}

});

$.extend($.ui.tabs, {
	version: '1.7.1',
	getter: 'length',
	defaults: {
		ajaxOptions: null,
		cache: false,
		cookie: null, // e.g. { expires: 7, path: '/', domain: 'jquery.com', secure: true }
		collapsible: false,
		disabled: [],
		event: 'click',
		fx: null, // e.g. { height: 'toggle', opacity: 'toggle', duration: 200 }
		idPrefix: 'ui-tabs-',
		panelTemplate: '<div></div>',
		spinner: '<em>Loading&#8230;</em>',
		tabTemplate: '<li><a href="#{href}"><span>#{label}</span></a></li>'
	}
});

/*
 * Tabs Extensions
 */

/*
 * Rotate
 */
$.extend($.ui.tabs.prototype, {
	rotation: null,
	rotate: function(ms, continuing) {

		var self = this, o = this.options;
		
		var rotate = self._rotate || (self._rotate = function(e) {
			clearTimeout(self.rotation);
			self.rotation = setTimeout(function() {
				var t = o.selected;
				self.select( ++t < self.anchors.length ? t : 0 );
			}, ms);
			
			if (e) {
				e.stopPropagation();
			}
		});
		
		var stop = self._unrotate || (self._unrotate = !continuing ?
			function(e) {
				if (e.clientX) { // in case of a true click
					self.rotate(null);
				}
			} :
			function(e) {
				t = o.selected;
				rotate();
			});

		// start rotation
		if (ms) {
			this.element.bind('tabsshow', rotate);
			this.anchors.bind(o.event + '.tabs', stop);
			rotate();
		}
		// stop rotation
		else {
			clearTimeout(self.rotation);
			this.element.unbind('tabsshow', rotate);
			this.anchors.unbind(o.event + '.tabs', stop);
			delete this._rotate;
			delete this._unrotate;
		}
	}
});

})(jQuery);



/**
 * jCarouselLite - jQuery plugin to navigate images/any content in a carousel style widget.
 * @requires jQuery v1.2 or above
 *
 * http://gmarwaha.com/jquery/jcarousellite/
 *
 * Copyright (c) 2007 Ganeshji Marwaha (gmarwaha.com)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 * Version: 1.0.1
 * Note: Requires jquery 1.2 or above from version 1.0.1
 */

/**
 * Creates a carousel-style navigation widget for images/any-content from a simple HTML markup.
 *
 * The HTML markup that is used to build the carousel can be as simple as...
 *
 *  <div class="carousel">
 *      <ul>
 *          <li><img src="image/1.jpg" alt="1"></li>
 *          <li><img src="image/2.jpg" alt="2"></li>
 *          <li><img src="image/3.jpg" alt="3"></li>
 *      </ul>
 *  </div>
 *
 * As you can see, this snippet is nothing but a simple div containing an unordered list of images.
 * You don't need any special "class" attribute, or a special "css" file for this plugin.
 * I am using a class attribute just for the sake of explanation here.
 *
 * To navigate the elements of the carousel, you need some kind of navigation buttons.
 * For example, you will need a "previous" button to go backward, and a "next" button to go forward.
 * This need not be part of the carousel "div" itself. It can be any element in your page.
 * Lets assume that the following elements in your document can be used as next, and prev buttons...
 *
 * <button class="prev">&lt;&lt;</button>
 * <button class="next">&gt;&gt;</button>
 *
 * Now, all you need to do is call the carousel component on the div element that represents it, and pass in the
 * navigation buttons as options.
 *
 * $(".carousel").jCarouselLite({
 *      btnNext: ".next",
 *      btnPrev: ".prev"
 * });
 *
 * That's it, you would have now converted your raw div, into a magnificient carousel.
 *
 * There are quite a few other options that you can use to customize it though.
 * Each will be explained with an example below.
 *
 * @param an options object - You can specify all the options shown below as an options object param.
 *
 * @option btnPrev, btnNext : string - no defaults
 * @example
 * $(".carousel").jCarouselLite({
 *      btnNext: ".next",
 *      btnPrev: ".prev"
 * });
 * @desc Creates a basic carousel. Clicking "btnPrev" navigates backwards and "btnNext" navigates forward.
 *
 * @option btnGo - array - no defaults
 * @example
 * $(".carousel").jCarouselLite({
 *      btnNext: ".next",
 *      btnPrev: ".prev",
 *      btnGo: [".0", ".1", ".2"]
 * });
 * @desc If you don't want next and previous buttons for navigation, instead you prefer custom navigation based on
 * the item number within the carousel, you can use this option. Just supply an array of selectors for each element
 * in the carousel. The index of the array represents the index of the element. What i mean is, if the
 * first element in the array is ".0", it means that when the element represented by ".0" is clicked, the carousel
 * will slide to the first element and so on and so forth. This feature is very powerful. For example, i made a tabbed
 * interface out of it by making my navigation elements styled like tabs in css. As the carousel is capable of holding
 * any content, not just images, you can have a very simple tabbed navigation in minutes without using any other plugin.
 * The best part is that, the tab will "slide" based on the provided effect. :-)
 *
 * @option mouseWheel : boolean - default is false
 * @example
 * $(".carousel").jCarouselLite({
 *      mouseWheel: true
 * });
 * @desc The carousel can also be navigated using the mouse wheel interface of a scroll mouse instead of using buttons.
 * To get this feature working, you have to do 2 things. First, you have to include the mouse-wheel plugin from brandon.
 * Second, you will have to set the option "mouseWheel" to true. That's it, now you will be able to navigate your carousel
 * using the mouse wheel. Using buttons and mouseWheel or not mutually exclusive. You can still have buttons for navigation
 * as well. They complement each other. To use both together, just supply the options required for both as shown below.
 * @example
 * $(".carousel").jCarouselLite({
 *      btnNext: ".next",
 *      btnPrev: ".prev",
 *      mouseWheel: true
 * });
 *
 * @option auto : number - default is null, meaning autoscroll is disabled by default
 * @example
 * $(".carousel").jCarouselLite({
 *      auto: 800,
 *      speed: 500
 * });
 * @desc You can make your carousel auto-navigate itself by specfying a millisecond value in this option.
 * The value you specify is the amount of time between 2 slides. The default is null, and that disables auto scrolling.
 * Specify this value and magically your carousel will start auto scrolling.
 *
 * @option speed : number - 200 is default
 * @example
 * $(".carousel").jCarouselLite({
 *      btnNext: ".next",
 *      btnPrev: ".prev",
 *      speed: 800
 * });
 * @desc Specifying a speed will slow-down or speed-up the sliding speed of your carousel. Try it out with
 * different speeds like 800, 600, 1500 etc. Providing 0, will remove the slide effect.
 *
 * @option easing : string - no easing effects by default.
 * @example
 * $(".carousel").jCarouselLite({
 *      btnNext: ".next",
 *      btnPrev: ".prev",
 *      easing: "bounceout"
 * });
 * @desc You can specify any easing effect. Note: You need easing plugin for that. Once specified,
 * the carousel will slide based on the provided easing effect.
 *
 * @option vertical : boolean - default is false
 * @example
 * $(".carousel").jCarouselLite({
 *      btnNext: ".next",
 *      btnPrev: ".prev",
 *      vertical: true
 * });
 * @desc Determines the direction of the carousel. true, means the carousel will display vertically. The next and
 * prev buttons will slide the items vertically as well. The default is false, which means that the carousel will
 * display horizontally. The next and prev items will slide the items from left-right in this case.
 *
 * @option circular : boolean - default is true
 * @example
 * $(".carousel").jCarouselLite({
 *      btnNext: ".next",
 *      btnPrev: ".prev",
 *      circular: false
 * });
 * @desc Setting it to true enables circular navigation. This means, if you click "next" after you reach the last
 * element, you will automatically slide to the first element and vice versa. If you set circular to false, then
 * if you click on the "next" button after you reach the last element, you will stay in the last element itself
 * and similarly for "previous" button and first element.
 *
 * @option visible : number - default is 3
 * @example
 * $(".carousel").jCarouselLite({
 *      btnNext: ".next",
 *      btnPrev: ".prev",
 *      visible: 4
 * });
 * @desc This specifies the number of items visible at all times within the carousel. The default is 3.
 * You are even free to experiment with real numbers. Eg: "3.5" will have 3 items fully visible and the
 * last item half visible. This gives you the effect of showing the user that there are more images to the right.
 *
 * @option start : number - default is 0
 * @example
 * $(".carousel").jCarouselLite({
 *      btnNext: ".next",
 *      btnPrev: ".prev",
 *      start: 2
 * });
 * @desc You can specify from which item the carousel should start. Remember, the first item in the carousel
 * has a start of 0, and so on.
 *
 * @option scrool : number - default is 1
 * @example
 * $(".carousel").jCarouselLite({
 *      btnNext: ".next",
 *      btnPrev: ".prev",
 *      scroll: 2
 * });
 * @desc The number of items that should scroll/slide when you click the next/prev navigation buttons. By
 * default, only one item is scrolled, but you may set it to any number. Eg: setting it to "2" will scroll
 * 2 items when you click the next or previous buttons.
 *
 * @option beforeStart, afterEnd : function - callbacks
 * @example
 * $(".carousel").jCarouselLite({
 *      btnNext: ".next",
 *      btnPrev: ".prev",
 *      beforeStart: function(a) {
 *          alert("Before animation starts:" + a);
 *      },
 *      afterEnd: function(a) {
 *          alert("After animation ends:" + a);
 *      }
 * });
 * @desc If you wanted to do some logic in your page before the slide starts and after the slide ends, you can
 * register these 2 callbacks. The functions will be passed an argument that represents an array of elements that
 * are visible at the time of callback.
 *
 *
 * @cat Plugins/Image Gallery
 * @author Ganeshji Marwaha/ganeshread@gmail.com
 */

(function($) {                                          // Compliant with jquery.noConflict()
$.fn.jCarouselLite = function(o) {
    o = $.extend({
        btnPrev: null,
        btnNext: null,
        btnGo: null,
        mouseWheel: false,
        auto: null,

        speed: 200,
        easing: null,

        vertical: false,
        circular: true,
        visible: 3,
        start: 0,
        scroll: 1,

        beforeStart: null,
        afterEnd: null
    }, o || {});

    return this.each(function() {                           // Returns the element collection. Chainable.

        var running = false, animCss=o.vertical?"top":"left", sizeCss=o.vertical?"height":"width";
        var div = $(this), ul = $("ul", div), tLi = $("li", ul), tl = tLi.size(), v = o.visible;

        if(o.circular) {
            ul.prepend(tLi.slice(tl-v-1+1).clone())
              .append(tLi.slice(0,v).clone());
            o.start += v;
        }

        var li = $("li", ul), itemLength = li.size(), curr = o.start;
        div.css("visibility", "visible");

        li.css({overflow: "hidden", float: o.vertical ? "none" : "left"});
        ul.css({margin: "0", padding: "0", position: "relative", "list-style-type": "none", "z-index": "1"});
        div.css({overflow: "hidden", position: "relative", "z-index": "2", left: "0px"});

        var liSize = o.vertical ? height(li) : width(li);   // Full li size(incl margin)-Used for animation
        var ulSize = liSize * itemLength;                   // size of full ul(total length, not just for the visible items)
        var divSize = liSize * v;                           // size of entire div(total length for just the visible items)

        li.css({width: li.width(), height: li.height()});
        ul.css(sizeCss, ulSize+"px").css(animCss, -(curr*liSize));

        div.css(sizeCss, divSize+"px");                     // Width of the DIV. length of visible images

        if(o.btnPrev)
            $(o.btnPrev).click(function() {
                return go(curr-o.scroll);
            });

        if(o.btnNext)
            $(o.btnNext).click(function() {
                return go(curr+o.scroll);
            });

        if(o.btnGo)
            $.each(o.btnGo, function(i, val) {
                $(val).click(function() {
                    return go(o.circular ? o.visible+i : i);
                });
            });

        if(o.mouseWheel && div.mousewheel)
            div.mousewheel(function(e, d) {
                return d>0 ? go(curr-o.scroll) : go(curr+o.scroll);
            });

        if(o.auto)
            setInterval(function() {
                go(curr+o.scroll);
            }, o.auto+o.speed);

        function vis() {
            return li.slice(curr).slice(0,v);
        };

        function go(to) {
            if(!running) {

                if(o.beforeStart)
                    o.beforeStart.call(this, vis());

                if(o.circular) {            // If circular we are in first or last, then goto the other end
                    if(to<=o.start-v-1) {           // If first, then goto last
                        ul.css(animCss, -((itemLength-(v*2))*liSize)+"px");
                        // If "scroll" > 1, then the "to" might not be equal to the condition; it can be lesser depending on the number of elements.
                        curr = to==o.start-v-1 ? itemLength-(v*2)-1 : itemLength-(v*2)-o.scroll;
                    } else if(to>=itemLength-v+1) { // If last, then goto first
                        ul.css(animCss, -( (v) * liSize ) + "px" );
                        // If "scroll" > 1, then the "to" might not be equal to the condition; it can be greater depending on the number of elements.
                        curr = to==itemLength-v+1 ? v+1 : v+o.scroll;
                    } else curr = to;
                } else {                    // If non-circular and to points to first or last, we just return.
                    if(to<0 || to>itemLength-v) return;
                    else curr = to;
                }                           // If neither overrides it, the curr will still be "to" and we can proceed.

                running = true;

                ul.animate(
                    animCss == "left" ? { left: -(curr*liSize) } : { top: -(curr*liSize) } , o.speed, o.easing,
                    function() {
                        if(o.afterEnd)
                            o.afterEnd.call(this, vis());
                        running = false;
                    }
                );
                // Disable buttons when the carousel reaches the last/first, and enable when not
                if(!o.circular) {
                    $(o.btnPrev + "," + o.btnNext).removeClass("disabled");
                    $( (curr-o.scroll<0 && o.btnPrev)
                        ||
                       (curr+o.scroll > itemLength-v && o.btnNext)
                        ||
                       []
                     ).addClass("disabled");
                }

            }
            return false;
        };
    });
};

function css(el, prop) {
    return parseInt($.css(el[0], prop)) || 0;
};
function width(el) {
    return  el[0].offsetWidth + css(el, 'marginLeft') + css(el, 'marginRight');
};
function height(el) {
    return el[0].offsetHeight + css(el, 'marginTop') + css(el, 'marginBottom');
};

})(jQuery);

/*
    anythingSlider v1.2
    
    By Chris Coyier: http://css-tricks.com
    with major improvements by Doug Neiner: http://pixelgraphics.us/
    based on work by Remy Sharp: http://jqueryfordesigners.com/


	To use the navigationFormatter function, you must have a function that
	accepts two paramaters, and returns a string of HTML text.
	
	index = integer index (1 based);
	panel = jQuery wrapped LI item this tab references
	@return = Must return a string of HTML/Text
	
	navigationFormatter: function(index, panel){
		return index + " Panel"; // This would have each tab with the text 'X Panel' where X = index
	}
*/

(function($){
	
    $.anythingSlider = function(el, options){
        // To avoid scope issues, use 'base' instead of 'this'
        // to reference this class from internal events and functions.
        var base = this;
        
        // Access to jQuery and DOM versions of element
        base.$el = $(el);
        base.el = el; 

		// Set up a few defaults
        base.currentPage = 1;
		base.timer = null;
		base.playing = false;

        // Add a reverse reference to the DOM object
        base.$el.data("AnythingSlider", base);
        
        base.init = function(){
            base.options = $.extend({},$.anythingSlider.defaults, options);
			
			// Cache existing DOM elements for later 
			base.$wrapper = base.$el.find('> div').css('overflow', 'hidden');
            base.$slider  = base.$wrapper.find('> ul');
            base.$items   = base.$slider.find('> li');
            base.$single  = base.$items.filter(':first');

			// Build the navigation if needed
			if(base.options.buildNavigation) base.buildNavigation();
        
        	// Get the details
            base.singleWidth = base.$single.outerWidth();
            base.pages = base.$items.length;

            // Top and tail the list with 'visible' number of items, top has the last section, and tail has the first
			// This supports the "infinite" scrolling
			base.$items.filter(':first').before(base.$items.filter(':last').clone().addClass('cloned'));
            base.$items.filter(':last' ).after(base.$items.filter(':first').clone().addClass('cloned'));

			// We just added two items, time to re-cache the list
            base.$items = base.$slider.find('> li'); // reselect
            
			// Setup our forward/backward navigation
			base.buildNextBackButtons();
		
			// If autoPlay functionality is included, then initialize the settings
			if(base.options.autoPlay) {
				base.playing = !base.options.startStopped; // Sets the playing variable to false if startStopped is true
				base.buildAutoPlay();
			};
			
			// If pauseOnHover then add hover effects
			if(base.options.pauseOnHover) {
				base.$el.hover(function(){
					base.clearTimer();
				}, function(){
					base.startStop(base.playing);
				});
			}
			
			// If a hash can not be used to trigger the plugin, then go to page 1
			if((base.options.hashTags == true && !base.gotoHash()) || base.options.hashTags == false){
				base.setCurrentPage(1);
			};
        };

		base.gotoPage = function(page, autoplay){
			// When autoplay isn't passed, we stop the timer
			if(autoplay !== true) autoplay = false;
			if(!autoplay) base.startStop(false);
			
			if(typeof(page) == "undefined" || page == null) {
				page = 1;
				base.setCurrentPage(1);
			};
			
			// Just check for bounds
			if(page > base.pages + 1) page = base.pages;
			if(page < 0 ) page = 1;

			var dir = page < base.currentPage ? -1 : 1,
                n = Math.abs(base.currentPage - page),
                left = base.singleWidth * dir * n;
			
			base.$wrapper.filter(':not(:animated)').animate({
                scrollLeft : '+=' + left
            }, base.options.animationTime, base.options.easing, function () {
                if (page == 0) {
                    base.$wrapper.scrollLeft(base.singleWidth * base.pages);
					page = base.pages;
                } else if (page > base.pages) {
                    base.$wrapper.scrollLeft(base.singleWidth);
                    // reset back to start position
                    page = 1;
                };
				base.setCurrentPage(page);
				
            });
		};
		
		base.setCurrentPage = function(page, move){
			// Set visual
			if(base.options.buildNavigation){
				base.$nav.find('.cur').removeClass('cur');
				$(base.$navLinks[page - 1]).addClass('cur');	
			};
			
			// Only change left if move does not equal false
			if(move !== false) base.$wrapper.scrollLeft(base.singleWidth * page);

			// Update local variable
			base.currentPage = page;
		};
		
		base.goForward = function(autoplay){
			if(autoplay !== true) autoplay = false;
			base.gotoPage(base.currentPage + 1, autoplay);
		};
		
		base.goBack = function(){
			base.gotoPage(base.currentPage - 1);
		};
		
		// This method tries to find a hash that matches panel-X
		// If found, it tries to find a matching item
		// If that is found as well, then that item starts visible
		base.gotoHash = function(){
			if(/^#?panel-\d+$/.test(window.location.hash)){
				var index = parseInt(window.location.hash.substr(7));
				var $item = base.$items.filter(':eq(' + index + ')');
				if($item.length != 0){
					base.setCurrentPage(index);
					return true;
				};
			};
			return false; // A item wasn't found;
		};
        
		// Creates the numbered navigation links
		base.buildNavigation = function(){
			base.$nav = $("<div id='thumbNav'></div>").appendTo(base.$el);
			base.$items.each(function(i,el){
				var index = i + 1;
				var $a = $("<a href='#'></a>");
				
				// If a formatter function is present, use it
				if( typeof(base.options.navigationFormatter) == "function"){
					$a.html(base.options.navigationFormatter(index, $(this)));
				} else {
					$a.text(index);
				}
				$a.click(function(e){
                    base.gotoPage(index);
                    
                    if (base.options.hashTags)
						base.setHash('panel-' + index);
						
                    e.preventDefault();
				});
				base.$nav.append($a);
			});
			base.$navLinks = base.$nav.find('> a');
		};
		
		
		// Creates the Forward/Backward buttons
		base.buildNextBackButtons = function(){
			var $forward = $('<a class="arrow forward">&gt;</a>'),
				$back    = $('<a class="arrow back">&lt;</a>');
				
            // Bind to the forward and back buttons
            $back.click(function(e){
                base.goBack();
				e.preventDefault();
            });

            $forward.click(function(e){
                base.goForward();
				e.preventDefault();
            });

			// Append elements to page
			base.$wrapper.after($back).after($forward);
		};
		
		// Creates the Start/Stop button
		base.buildAutoPlay = function(){

			base.$startStop = $("<a href='#' id='start-stop'></a>").html(base.playing ? base.options.stopText :  base.options.startText);
			base.$el.append(base.$startStop);            
            base.$startStop.click(function(e){
				base.startStop(!base.playing);
				e.preventDefault();
            });

			// Use the same setting, but trigger the start;
			base.startStop(base.playing);
		};
		
		// Handles stopping and playing the slideshow
		// Pass startStop(false) to stop and startStop(true) to play
		base.startStop = function(playing){
			if(playing !== true) playing = false; // Default if not supplied is false
			
			// Update variable
			base.playing = playing;
			
			// Toggle playing and text
			if(base.options.autoPlay) base.$startStop.toggleClass("playing", playing).html( playing ? base.options.stopText : base.options.startText );
			
			if(playing){
				base.clearTimer(); // Just in case this was triggered twice in a row
				base.timer = window.setInterval(function(){
					base.goForward(true);
				}, base.options.delay);
			} else {
				base.clearTimer();
			};
		};
		
		base.clearTimer = function(){
			// Clear the timer only if it is set
			if(base.timer) window.clearInterval(base.timer);
		};
		
		// Taken from AJAXY jquery.history Plugin
		base.setHash = function ( hash ) {
			// Write hash
			if ( typeof window.location.hash !== 'undefined' ) {
				if ( window.location.hash !== hash ) {
					window.location.hash = hash;
				};
			} else if ( location.hash !== hash ) {
				location.hash = hash;
			};
			
			// Done
			return hash;
		};
		// <-- End AJAXY code


		// Trigger the initialization
        base.init();
    };

	
    $.anythingSlider.defaults = {
        easing: "swing",                // Anything other than "linear" or "swing" requires the easing plugin
        autoPlay: true,                 // This turns off the entire FUNCTIONALY, not just if it starts running or not
        startStopped: false,            // If autoPlay is on, this can force it to start stopped
        delay: 3000,                    // How long between slide transitions in AutoPlay mode
        animationTime: 600,             // How long the slide transition takes
        hashTags: true,                 // Should links change the hashtag in the URL?
        buildNavigation: true,          // If true, builds and list of anchor links to link to each slide
        pauseOnHover: true,             // If true, and autoPlay is enabled, the show will pause on hover
		startText: "Start",             // Start text
		stopText: "Stop",               // Stop text
		navigationFormatter: null       // Details at the top of the file on this use (advanced use)
    };
	

    $.fn.anythingSlider = function(options){
		if(typeof(options) == "object"){
		    return this.each(function(i){			
				(new $.anythingSlider(this, options));

	            // This plugin supports multiple instances, but only one can support hash-tag support
				// This disables hash-tags on all items but the first one
				options.hashTags = false;
	        });	
		} else if (typeof(options) == "number") {

			return this.each(function(i){
				var anySlide = $(this).data('AnythingSlider');
				if(anySlide){
					anySlide.gotoPage(options);
				}
			});
		}
    };

	
})(jQuery);


/*
 * FancyBox - jQuery Plugin
 * Simple and fancy lightbox alternative
 *
 * Examples and documentation at: http://fancybox.net
 * 
 * Copyright (c) 2008 - 2010 Janis Skarnelis
 *
 * Version: 1.3.1 (05/03/2010)
 * Requires: jQuery v1.3+
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 */

(function($) {

	var tmp, loading, overlay, wrap, outer, inner, close, nav_left, nav_right,

		selectedIndex = 0, selectedOpts = {}, selectedArray = [], currentIndex = 0, currentOpts = {}, currentArray = [],

		ajaxLoader = null, imgPreloader = new Image(), imgRegExp = /\.(jpg|gif|png|bmp|jpeg)(.*)?$/i, swfRegExp = /[^\.]\.(swf)\s*$/i,

		loadingTimer, loadingFrame = 1,

		start_pos, final_pos, busy = false, shadow = 20, fx = $.extend($('<div/>')[0], { prop: 0 }), titleh = 0, 

		isIE6 = !$.support.opacity && !window.XMLHttpRequest,

		/*
		 * Private methods 
		 */

		fancybox_abort = function() {
			loading.hide();

			imgPreloader.onerror = imgPreloader.onload = null;

			if (ajaxLoader) {
				ajaxLoader.abort();
			}

			tmp.empty();
		},

		fancybox_error = function() {
			$.fancybox('<p id="fancybox_error">The requested content cannot be loaded.<br />Please try again later.</p>', {
				'scrolling'		: 'no',
				'padding'		: 20,
				'transitionIn'	: 'none',
				'transitionOut'	: 'none'
			});
		},

		fancybox_get_viewport = function() {
			return [ $(window).width(), $(window).height(), $(document).scrollLeft(), $(document).scrollTop() ];
		},

		fancybox_get_zoom_to = function () {
			var view	= fancybox_get_viewport(),
				to		= {},

				margin = currentOpts.margin,
				resize = currentOpts.autoScale,

				horizontal_space	= (shadow + margin) * 2,
				vertical_space		= (shadow + margin) * 2,
				double_padding		= (currentOpts.padding * 2),
				
				ratio;

			if (currentOpts.width.toString().indexOf('%') > -1) {
				to.width = ((view[0] * parseFloat(currentOpts.width)) / 100) - (shadow * 2) ;
				resize = false;

			} else {
				to.width = currentOpts.width + double_padding;
			}

			if (currentOpts.height.toString().indexOf('%') > -1) {
				to.height = ((view[1] * parseFloat(currentOpts.height)) / 100) - (shadow * 2);
				resize = false;

			} else {
				to.height = currentOpts.height + double_padding;
			}

			if (resize && (to.width > (view[0] - horizontal_space) || to.height > (view[1] - vertical_space))) {
				if (selectedOpts.type == 'image' || selectedOpts.type == 'swf') {
					horizontal_space	+= double_padding;
					vertical_space		+= double_padding;

					ratio = Math.min(Math.min( view[0] - horizontal_space, currentOpts.width) / currentOpts.width, Math.min( view[1] - vertical_space, currentOpts.height) / currentOpts.height);

					to.width	= Math.round(ratio * (to.width	- double_padding)) + double_padding;
					to.height	= Math.round(ratio * (to.height	- double_padding)) + double_padding;

				} else {
					to.width	= Math.min(to.width,	(view[0] - horizontal_space));
					to.height	= Math.min(to.height,	(view[1] - vertical_space));
				}
			}

			to.top	= view[3] + ((view[1] - (to.height	+ (shadow * 2 ))) * 0.5);
			to.left	= view[2] + ((view[0] - (to.width	+ (shadow * 2 ))) * 0.5);

			if (currentOpts.autoScale === false) {
				to.top	= Math.max(view[3] + margin, to.top);
				to.left	= Math.max(view[2] + margin, to.left);
			}

			return to;
		},

		fancybox_format_title = function(title) {
			if (title && title.length) {
				switch (currentOpts.titlePosition) {
					case 'inside':
						return title;
					case 'over':
						return '<span id="fancybox-title-over">' + title + '</span>';
					default:
						return '<span id="fancybox-title-wrap"><span id="fancybox-title-left"></span><span id="fancybox-title-main">' + title + '</span><span id="fancybox-title-right"></span></span>';
				}
			}

			return false;
		},

		fancybox_process_title = function() {
			var title	= currentOpts.title,
				width	= final_pos.width - (currentOpts.padding * 2),
				titlec	= 'fancybox-title-' + currentOpts.titlePosition;
				
			$('#fancybox-title').remove();

			titleh = 0;

			if (currentOpts.titleShow === false) {
				return;
			}

			title = $.isFunction(currentOpts.titleFormat) ? currentOpts.titleFormat(title, currentArray, currentIndex, currentOpts) : fancybox_format_title(title);

			if (!title || title === '') {
				return;
			}

			$('<div id="fancybox-title" class="' + titlec + '" />').css({
				'width'			: width,
				'paddingLeft'	: currentOpts.padding,
				'paddingRight'	: currentOpts.padding
			}).html(title).appendTo('body');

			switch (currentOpts.titlePosition) {
				case 'inside':
					titleh = $("#fancybox-title").outerHeight(true) - currentOpts.padding;
					final_pos.height += titleh;
				break;

				case 'over':
					$('#fancybox-title').css('bottom', currentOpts.padding);
				break;

				default:
					$('#fancybox-title').css('bottom', $("#fancybox-title").outerHeight(true) * -1);
				break;
			}

			$('#fancybox-title').appendTo( outer ).hide();
		},

		fancybox_set_navigation = function() {
			$(document).unbind('keydown.fb').bind('keydown.fb', function(e) {
				if (e.keyCode == 27 && currentOpts.enableEscapeButton) {
					e.preventDefault();
					$.fancybox.close();

				} else if (e.keyCode == 37) {
					e.preventDefault();
					$.fancybox.prev();

				} else if (e.keyCode == 39) {
					e.preventDefault();
					$.fancybox.next();
				}
			});

			if ($.fn.mousewheel) {
				wrap.unbind('mousewheel.fb');

				if (currentArray.length > 1) {
					wrap.bind('mousewheel.fb', function(e, delta) {
						e.preventDefault();

						if (busy || delta === 0) {
							return;
						}

						if (delta > 0) {
							$.fancybox.prev();
						} else {
							$.fancybox.next();
						}
					});
				}
			}

			if (!currentOpts.showNavArrows) { return; }

			if ((currentOpts.cyclic && currentArray.length > 1) || currentIndex !== 0) {
				nav_left.show();
			}

			if ((currentOpts.cyclic && currentArray.length > 1) || currentIndex != (currentArray.length -1)) {
				nav_right.show();
			}
		},

		fancybox_preload_images = function() {
			var href, 
				objNext;
				
			if ((currentArray.length -1) > currentIndex) {
				href = currentArray[ currentIndex + 1 ].href;

				if (typeof href !== 'undefined' && href.match(imgRegExp)) {
					objNext = new Image();
					objNext.src = href;
				}
			}

			if (currentIndex > 0) {
				href = currentArray[ currentIndex - 1 ].href;

				if (typeof href !== 'undefined' && href.match(imgRegExp)) {
					objNext = new Image();
					objNext.src = href;
				}
			}
		},

		_finish = function () {
			inner.css('overflow', (currentOpts.scrolling == 'auto' ? (currentOpts.type == 'image' || currentOpts.type == 'iframe' || currentOpts.type == 'swf' ? 'hidden' : 'auto') : (currentOpts.scrolling == 'yes' ? 'auto' : 'visible')));

			if (!$.support.opacity) {
				inner.get(0).style.removeAttribute('filter');
				wrap.get(0).style.removeAttribute('filter');
			}

			$('#fancybox-title').show();

			if (currentOpts.hideOnContentClick)	{
				inner.one('click', $.fancybox.close);
			}
			if (currentOpts.hideOnOverlayClick)	{
				overlay.one('click', $.fancybox.close);
			}

			if (currentOpts.showCloseButton) {
				close.show();
			}

			fancybox_set_navigation();

			$(window).bind("resize.fb", $.fancybox.center);

			if (currentOpts.centerOnScroll) {
				$(window).bind("scroll.fb", $.fancybox.center);
			} else {
				$(window).unbind("scroll.fb");
			}

			if ($.isFunction(currentOpts.onComplete)) {
				currentOpts.onComplete(currentArray, currentIndex, currentOpts);
			}

			busy = false;

			fancybox_preload_images();
		},

		fancybox_draw = function(pos) {
			var width	= Math.round(start_pos.width	+ (final_pos.width	- start_pos.width)	* pos),
				height	= Math.round(start_pos.height	+ (final_pos.height	- start_pos.height)	* pos),

				top		= Math.round(start_pos.top	+ (final_pos.top	- start_pos.top)	* pos),
				left	= Math.round(start_pos.left	+ (final_pos.left	- start_pos.left)	* pos);

			wrap.css({
				'width'		: width		+ 'px',
				'height'	: height	+ 'px',
				'top'		: top		+ 'px',
				'left'		: left		+ 'px'
			});

			width	= Math.max(width - currentOpts.padding * 2, 0);
			height	= Math.max(height - (currentOpts.padding * 2 + (titleh * pos)), 0);

			inner.css({
				'width'		: width		+ 'px',
				'height'	: height	+ 'px'
			});

			if (typeof final_pos.opacity !== 'undefined') {
				wrap.css('opacity', (pos < 0.5 ? 0.5 : pos));
			}
		},

		fancybox_get_obj_pos = function(obj) {
			var pos		= obj.offset();

			pos.top		+= parseFloat( obj.css('paddingTop') )	|| 0;
			pos.left	+= parseFloat( obj.css('paddingLeft') )	|| 0;

			pos.top		+= parseFloat( obj.css('border-top-width') )	|| 0;
			pos.left	+= parseFloat( obj.css('border-left-width') )	|| 0;

			pos.width	= obj.width();
			pos.height	= obj.height();

			return pos;
		},

		fancybox_get_zoom_from = function() {
			var orig = selectedOpts.orig ? $(selectedOpts.orig) : false,
				from = {},
				pos,
				view;

			if (orig && orig.length) {
				pos = fancybox_get_obj_pos(orig);

				from = {
					width	: (pos.width	+ (currentOpts.padding * 2)),
					height	: (pos.height	+ (currentOpts.padding * 2)),
					top		: (pos.top		- currentOpts.padding - shadow),
					left	: (pos.left		- currentOpts.padding - shadow)
				};
				
			} else {
				view = fancybox_get_viewport();

				from = {
					width	: 1,
					height	: 1,
					top		: view[3] + view[1] * 0.5,
					left	: view[2] + view[0] * 0.5
				};
			}

			return from;
		},

		fancybox_show = function() {
			loading.hide();

			if (wrap.is(":visible") && $.isFunction(currentOpts.onCleanup)) {
				if (currentOpts.onCleanup(currentArray, currentIndex, currentOpts) === false) {
					$.event.trigger('fancybox-cancel');

					busy = false;
					return;
				}
			}

			currentArray	= selectedArray;
			currentIndex	= selectedIndex;
			currentOpts		= selectedOpts;

			inner.get(0).scrollTop	= 0;
			inner.get(0).scrollLeft	= 0;

			if (currentOpts.overlayShow) {
				if (isIE6) {
					$('select:not(#fancybox-tmp select)').filter(function() {
						return this.style.visibility !== 'hidden';
					}).css({'visibility':'hidden'}).one('fancybox-cleanup', function() {
						this.style.visibility = 'inherit';
					});
				}

				overlay.css({
					'background-color'	: currentOpts.overlayColor,
					'opacity'			: currentOpts.overlayOpacity
				}).unbind().show();
			}

			final_pos = fancybox_get_zoom_to();

			fancybox_process_title();

			if (wrap.is(":visible")) {
				$( close.add( nav_left ).add( nav_right ) ).hide();

				var pos = wrap.position(),
					equal;

				start_pos = {
					top		:	pos.top ,
					left	:	pos.left,
					width	:	wrap.width(),
					height	:	wrap.height()
				};

				equal = (start_pos.width == final_pos.width && start_pos.height == final_pos.height);

				inner.fadeOut(currentOpts.changeFade, function() {
					var finish_resizing = function() {
						inner.html( tmp.contents() ).fadeIn(currentOpts.changeFade, _finish);
					};
					
					$.event.trigger('fancybox-change');

					inner.empty().css('overflow', 'hidden');

					if (equal) {
						inner.css({
							top			: currentOpts.padding,
							left		: currentOpts.padding,
							width		: Math.max(final_pos.width	- (currentOpts.padding * 2), 1),
							height		: Math.max(final_pos.height	- (currentOpts.padding * 2) - titleh, 1)
						});
						
						finish_resizing();

					} else {
						inner.css({
							top			: currentOpts.padding,
							left		: currentOpts.padding,
							width		: Math.max(start_pos.width	- (currentOpts.padding * 2), 1),
							height		: Math.max(start_pos.height	- (currentOpts.padding * 2), 1)
						});
						
						fx.prop = 0;

						$(fx).animate({ prop: 1 }, {
							 duration	: currentOpts.changeSpeed,
							 easing		: currentOpts.easingChange,
							 step		: fancybox_draw,
							 complete	: finish_resizing
						});
					}
				});

				return;
			}

			wrap.css('opacity', 1);

			if (currentOpts.transitionIn == 'elastic') {
				start_pos = fancybox_get_zoom_from();

				inner.css({
						top			: currentOpts.padding,
						left		: currentOpts.padding,
						width		: Math.max(start_pos.width	- (currentOpts.padding * 2), 1),
						height		: Math.max(start_pos.height	- (currentOpts.padding * 2), 1)
					})
					.html( tmp.contents() );

				wrap.css(start_pos).show();

				if (currentOpts.opacity) {
					final_pos.opacity = 0;
				}

				fx.prop = 0;

				$(fx).animate({ prop: 1 }, {
					 duration	: currentOpts.speedIn,
					 easing		: currentOpts.easingIn,
					 step		: fancybox_draw,
					 complete	: _finish
				});

			} else {
				inner.css({
						top			: currentOpts.padding,
						left		: currentOpts.padding,
						width		: Math.max(final_pos.width	- (currentOpts.padding * 2), 1),
						height		: Math.max(final_pos.height	- (currentOpts.padding * 2) - titleh, 1)
					})
					.html( tmp.contents() );

				wrap.css( final_pos ).fadeIn( currentOpts.transitionIn == 'none' ? 0 : currentOpts.speedIn, _finish );
			}
		},

		fancybox_process_inline = function() {
			tmp.width(	selectedOpts.width );
			tmp.height(	selectedOpts.height );

			if (selectedOpts.width	== 'auto') {
				selectedOpts.width = tmp.width();
			}
			if (selectedOpts.height	== 'auto') {
				selectedOpts.height	= tmp.height();
			}

			fancybox_show();
		},
		
		fancybox_process_image = function() {
			busy = true;

			selectedOpts.width	= imgPreloader.width;
			selectedOpts.height	= imgPreloader.height;

			$("<img />").attr({
				'id'	: 'fancybox-img',
				'src'	: imgPreloader.src,
				'alt'	: selectedOpts.title
			}).appendTo( tmp );

			fancybox_show();
		},

		fancybox_start = function() {
			fancybox_abort();

			var obj	= selectedArray[ selectedIndex ],
				href, 
				type, 
				title,
				str,
				emb,
				selector,
				data;

			selectedOpts = $.extend({}, $.fn.fancybox.defaults, (typeof $(obj).data('fancybox') == 'undefined' ? selectedOpts : $(obj).data('fancybox')));
			title = obj.title || $(obj).title || selectedOpts.title || '';
			
			if (obj.nodeName && !selectedOpts.orig) {
				selectedOpts.orig = $(obj).children("img:first").length ? $(obj).children("img:first") : $(obj);
			}

			if (title === '' && selectedOpts.orig) {
				title = selectedOpts.orig.attr('alt');
			}

			if (obj.nodeName && (/^(?:javascript|#)/i).test(obj.href)) {
				href = selectedOpts.href || null;
			} else {
				href = selectedOpts.href || obj.href || null;
			}

			if (selectedOpts.type) {
				type = selectedOpts.type;

				if (!href) {
					href = selectedOpts.content;
				}
				
			} else if (selectedOpts.content) {
				type	= 'html';

			} else if (href) {
				if (href.match(imgRegExp)) {
					type = 'image';

				} else if (href.match(swfRegExp)) {
					type = 'swf';

				} else if ($(obj).hasClass("iframe")) {
					type = 'iframe';

				} else if (href.match(/#/)) {
					obj = href.substr(href.indexOf("#"));

					type = $(obj).length > 0 ? 'inline' : 'ajax';
				} else {
					type = 'ajax';
				}
			} else {
				type = 'inline';
			}

			selectedOpts.type	= type;
			selectedOpts.href	= href;
			selectedOpts.title	= title;

			if (selectedOpts.autoDimensions && selectedOpts.type !== 'iframe' && selectedOpts.type !== 'swf') {
				selectedOpts.width		= 'auto';
				selectedOpts.height		= 'auto';
			}

			if (selectedOpts.modal) {
				selectedOpts.overlayShow		= true;
				selectedOpts.hideOnOverlayClick	= false;
				selectedOpts.hideOnContentClick	= false;
				selectedOpts.enableEscapeButton	= false;
				selectedOpts.showCloseButton	= false;
			}

			if ($.isFunction(selectedOpts.onStart)) {
				if (selectedOpts.onStart(selectedArray, selectedIndex, selectedOpts) === false) {
					busy = false;
					return;
				}
			}

			tmp.css('padding', (shadow + selectedOpts.padding + selectedOpts.margin));

			$('.fancybox-inline-tmp').unbind('fancybox-cancel').bind('fancybox-change', function() {
				$(this).replaceWith(inner.children());
			});

			switch (type) {
				case 'html' :
					tmp.html( selectedOpts.content );
					fancybox_process_inline();
				break;

				case 'inline' :
					$('<div class="fancybox-inline-tmp" />').hide().insertBefore( $(obj) ).bind('fancybox-cleanup', function() {
						$(this).replaceWith(inner.children());
					}).bind('fancybox-cancel', function() {
						$(this).replaceWith(tmp.children());
					});

					$(obj).appendTo(tmp);

					fancybox_process_inline();
				break;

				case 'image':
					busy = false;

					$.fancybox.showActivity();

					imgPreloader = new Image();

					imgPreloader.onerror = function() {
						fancybox_error();
					};

					imgPreloader.onload = function() {
						imgPreloader.onerror = null;
						imgPreloader.onload = null;
						fancybox_process_image();
					};

					imgPreloader.src = href;
		
				break;

				case 'swf':
					str = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="' + selectedOpts.width + '" height="' + selectedOpts.height + '"><param name="movie" value="' + href + '"></param>';
					emb = '';
					
					$.each(selectedOpts.swf, function(name, val) {
						str += '<param name="' + name + '" value="' + val + '"></param>';
						emb += ' ' + name + '="' + val + '"';
					});

					str += '<embed src="' + href + '" type="application/x-shockwave-flash" width="' + selectedOpts.width + '" height="' + selectedOpts.height + '"' + emb + '></embed></object>';

					tmp.html(str);

					fancybox_process_inline();
				break;

				case 'ajax':
					selector	= href.split('#', 2);
					data		= selectedOpts.ajax.data || {};

					if (selector.length > 1) {
						href = selector[0];

						if (typeof data == "string") {
							data += '&selector=' + selector[1];
						} else {
							data.selector = selector[1];
						}
					}

					busy = false;
					$.fancybox.showActivity();

					ajaxLoader = $.ajax($.extend(selectedOpts.ajax, {
						url		: href,
						data	: data,
						error	: fancybox_error,
						success : function(data, textStatus, XMLHttpRequest) {
							if (ajaxLoader.status == 200) {
								tmp.html( data );
								fancybox_process_inline();
							}
						}
					}));

				break;

				case 'iframe' :
					$('<iframe id="fancybox-frame" name="fancybox-frame' + new Date().getTime() + '" frameborder="0" hspace="0" scrolling="' + selectedOpts.scrolling + '" src="' + selectedOpts.href + '"></iframe>').appendTo(tmp);
					fancybox_show();
				break;
			}
		},

		fancybox_animate_loading = function() {
			if (!loading.is(':visible')){
				clearInterval(loadingTimer);
				return;
			}

			$('div', loading).css('top', (loadingFrame * -40) + 'px');

			loadingFrame = (loadingFrame + 1) % 12;
		},

		fancybox_init = function() {
			if ($("#fancybox-wrap").length) {
				return;
			}

			$('body').append(
				tmp			= $('<div id="fancybox-tmp"></div>'),
				loading		= $('<div id="fancybox-loading"><div></div></div>'),
				overlay		= $('<div id="fancybox-overlay"></div>'),
				wrap		= $('<div id="fancybox-wrap"></div>')
			);

			if (!$.support.opacity) {
				wrap.addClass('fancybox-ie');
				loading.addClass('fancybox-ie');
			}

			outer = $('<div id="fancybox-outer"></div>')
				.append('<div class="fancy-bg" id="fancy-bg-n"></div><div class="fancy-bg" id="fancy-bg-ne"></div><div class="fancy-bg" id="fancy-bg-e"></div><div class="fancy-bg" id="fancy-bg-se"></div><div class="fancy-bg" id="fancy-bg-s"></div><div class="fancy-bg" id="fancy-bg-sw"></div><div class="fancy-bg" id="fancy-bg-w"></div><div class="fancy-bg" id="fancy-bg-nw"></div>')
				.appendTo( wrap );

			outer.append(
				inner		= $('<div id="fancybox-inner"></div>'),
				close		= $('<a id="fancybox-close"></a>'),

				nav_left	= $('<a href="javascript:;" id="fancybox-left"><span class="fancy-ico" id="fancybox-left-ico"></span></a>'),
				nav_right	= $('<a href="javascript:;" id="fancybox-right"><span class="fancy-ico" id="fancybox-right-ico"></span></a>')
			);

			close.click($.fancybox.close);
			loading.click($.fancybox.cancel);

			nav_left.click(function(e) {
				e.preventDefault();
				$.fancybox.prev();
			});

			nav_right.click(function(e) {
				e.preventDefault();
				$.fancybox.next();
			});

			if (isIE6) {
				overlay.get(0).style.setExpression('height',	"document.body.scrollHeight > document.body.offsetHeight ? document.body.scrollHeight : document.body.offsetHeight + 'px'");
				loading.get(0).style.setExpression('top',		"(-20 + (document.documentElement.clientHeight ? document.documentElement.clientHeight/2 : document.body.clientHeight/2 ) + ( ignoreMe = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop )) + 'px'");

				outer.prepend('<iframe id="fancybox-hide-sel-frame" src="javascript:\'\';" scrolling="no" frameborder="0" ></iframe>');
			}
		};

	/*
	 * Public methods 
	 */

	$.fn.fancybox = function(options) {
		$(this)
			.data('fancybox', $.extend({}, options, ($.metadata ? $(this).metadata() : {})))
			.unbind('click.fb').bind('click.fb', function(e) {
				e.preventDefault();

				if (busy) {
					return;
				}

				busy = true;

				$(this).blur();

				selectedArray	= [];
				selectedIndex	= 0;

				var rel = $(this).attr('rel') || '';

				if (!rel || rel == '' || rel === 'nofollow') {
					selectedArray.push(this);

				} else {
					selectedArray	= $("a[rel=" + rel + "], area[rel=" + rel + "]");
					selectedIndex	= selectedArray.index( this );
				}

				fancybox_start();

				return false;
			});

		return this;
	};

	$.fancybox = function(obj) {
		if (busy) {
			return;
		}

		busy = true;

		var opts = typeof arguments[1] !== 'undefined' ? arguments[1] : {};

		selectedArray	= [];
		selectedIndex	= opts.index || 0;

		if ($.isArray(obj)) {
			for (var i = 0, j = obj.length; i < j; i++) {
				if (typeof obj[i] == 'object') {
					$(obj[i]).data('fancybox', $.extend({}, opts, obj[i]));
				} else {
					obj[i] = $({}).data('fancybox', $.extend({content : obj[i]}, opts));
				}
			}

			selectedArray = jQuery.merge(selectedArray, obj);

		} else {
			if (typeof obj == 'object') {
				$(obj).data('fancybox', $.extend({}, opts, obj));
			} else {
				obj = $({}).data('fancybox', $.extend({content : obj}, opts));
			}

			selectedArray.push(obj);
		}

		if (selectedIndex > selectedArray.length || selectedIndex < 0) {
			selectedIndex = 0;
		}

		fancybox_start();
	};

	$.fancybox.showActivity = function() {
		clearInterval(loadingTimer);

		loading.show();
		loadingTimer = setInterval(fancybox_animate_loading, 66);
	};

	$.fancybox.hideActivity = function() {
		loading.hide();
	};

	$.fancybox.next = function() {
		return $.fancybox.pos( currentIndex + 1);
	};
	
	$.fancybox.prev = function() {
		return $.fancybox.pos( currentIndex - 1);
	};

	$.fancybox.pos = function(pos) {
		if (busy) {
			return;
		}

		pos = parseInt(pos, 10);

		if (pos > -1 && currentArray.length > pos) {
			selectedIndex = pos;
			fancybox_start();
		}

		if (currentOpts.cyclic && currentArray.length > 1 && pos < 0) {
			selectedIndex = currentArray.length - 1;
			fancybox_start();
		}

		if (currentOpts.cyclic && currentArray.length > 1 && pos >= currentArray.length) {
			selectedIndex = 0;
			fancybox_start();
		}

		return;
	};

	$.fancybox.cancel = function() {
		if (busy) {
			return;
		}

		busy = true;

		$.event.trigger('fancybox-cancel');

		fancybox_abort();

		if (selectedOpts && $.isFunction(selectedOpts.onCancel)) {
			selectedOpts.onCancel(selectedArray, selectedIndex, selectedOpts);
		}

		busy = false;
	};

	// Note: within an iframe use - parent.$.fancybox.close();
	$.fancybox.close = function() {
		if (busy || wrap.is(':hidden')) {
			return;
		}

		busy = true;

		if (currentOpts && $.isFunction(currentOpts.onCleanup)) {
			if (currentOpts.onCleanup(currentArray, currentIndex, currentOpts) === false) {
				busy = false;
				return;
			}
		}

		fancybox_abort();

		$(close.add( nav_left ).add( nav_right )).hide();

		$('#fancybox-title').remove();

		wrap.add(inner).add(overlay).unbind();

		$(window).unbind("resize.fb scroll.fb");
		$(document).unbind('keydown.fb');

		function _cleanup() {
			overlay.fadeOut('fast');

			wrap.hide();

			$.event.trigger('fancybox-cleanup');

			inner.empty();

			if ($.isFunction(currentOpts.onClosed)) {
				currentOpts.onClosed(currentArray, currentIndex, currentOpts);
			}

			currentArray	= selectedOpts	= [];
			currentIndex	= selectedIndex	= 0;
			currentOpts		= selectedOpts	= {};

			busy = false;
		}

		inner.css('overflow', 'hidden');

		if (currentOpts.transitionOut == 'elastic') {
			start_pos = fancybox_get_zoom_from();

			var pos = wrap.position();

			final_pos = {
				top		:	pos.top ,
				left	:	pos.left,
				width	:	wrap.width(),
				height	:	wrap.height()
			};

			if (currentOpts.opacity) {
				final_pos.opacity = 1;
			}

			fx.prop = 1;

			$(fx).animate({ prop: 0 }, {
				 duration	: currentOpts.speedOut,
				 easing		: currentOpts.easingOut,
				 step		: fancybox_draw,
				 complete	: _cleanup
			});

		} else {
			wrap.fadeOut( currentOpts.transitionOut == 'none' ? 0 : currentOpts.speedOut, _cleanup);
		}
	};

	$.fancybox.resize = function() {
		var c, h;
		
		if (busy || wrap.is(':hidden')) {
			return;
		}

		busy = true;

		c = inner.wrapInner("<div style='overflow:auto'></div>").children();
		h = c.height();

		wrap.css({height:	h + (currentOpts.padding * 2) + titleh});
		inner.css({height:	h});

		c.replaceWith(c.children());

		$.fancybox.center();
	};

	$.fancybox.center = function() {
		busy = true;

		var view	= fancybox_get_viewport(),
			margin	= currentOpts.margin,
			to		= {};

		to.top	= view[3] + ((view[1] - ((wrap.height() - titleh) + (shadow * 2 ))) * 0.5);
		to.left	= view[2] + ((view[0] - (wrap.width() + (shadow * 2 ))) * 0.5);

		to.top	= Math.max(view[3] + margin, to.top);
		to.left	= Math.max(view[2] + margin, to.left);

		wrap.css(to);

		busy = false;
	};

	$.fn.fancybox.defaults = {
		padding				:	10,
		margin				:	20,
		opacity				:	false,
		modal				:	false,
		cyclic				:	false,
		scrolling			:	'auto',	// 'auto', 'yes' or 'no'

		width				:	560,
		height				:	340,

		autoScale			:	true,
		autoDimensions		:	true,
		centerOnScroll		:	false,

		ajax				:	{},
		swf					:	{ wmode: 'transparent' },

		hideOnOverlayClick	:	true,
		hideOnContentClick	:	false,

		overlayShow			:	true,
		overlayOpacity		:	0.3,
		overlayColor		:	'#666',

		titleShow			:	true,
		titlePosition		:	'outside',	// 'outside', 'inside' or 'over'
		titleFormat			:	null,

		transitionIn		:	'fade',	// 'elastic', 'fade' or 'none'
		transitionOut		:	'fade',	// 'elastic', 'fade' or 'none'

		speedIn				:	300,
		speedOut			:	300,

		changeSpeed			:	300,
		changeFade			:	'fast',

		easingIn			:	'swing',
		easingOut			:	'swing',

		showCloseButton		:	true,
		showNavArrows		:	true,
		enableEscapeButton	:	true,

		onStart				:	null,
		onCancel			:	null,
		onComplete			:	null,
		onCleanup			:	null,
		onClosed			:	null
	};

	$(document).ready(function() {
		fancybox_init();
	});

})(jQuery);

/* Copyright (c) 2007 Paul Bakaus (paul.bakaus@googlemail.com) and Brandon Aaron (brandon.aaron@gmail.com || http://brandonaaron.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 *
 * $LastChangedDate: 2007-09-29 01:08:25 +0100 (Sat, 29 Sep 2007) $
 * $Rev: 3493 $
 *
 * Version: @VERSION
 *
 * Requires: jQuery 1.2+
 */

(function($){
	
$.dimensions = {
	version: '@VERSION'
};

// Create innerHeight, innerWidth, outerHeight and outerWidth methods
$.each( [ 'Height', 'Width' ], function(i, name){
	
	// innerHeight and innerWidth
	$.fn[ 'inner' + name ] = function() {
		if (!this[0]) return;
		
		var torl = name == 'Height' ? 'Top'    : 'Left',  // top or left
		    borr = name == 'Height' ? 'Bottom' : 'Right'; // bottom or right

		return num(this, name.toLowerCase()) + num(this, 'padding' + torl) + num(this, 'padding' + borr);
	};
	
	// outerHeight and outerWidth
	$.fn[ 'outer' + name ] = function(options) {
		if (!this[0]) return;
		
		var torl = name == 'Height' ? 'Top'    : 'Left',  // top or left
		    borr = name == 'Height' ? 'Bottom' : 'Right'; // bottom or right
		
		options = $.extend({ margin: false }, options || {});
		
		return num(this, name.toLowerCase())
				+ num(this, 'border' + torl + 'Width') + num(this, 'border' + borr + 'Width')
				+ num(this, 'padding' + torl) + num(this, 'padding' + borr)
				+ (options.margin ? (num(this, 'margin' + torl) + num(this, 'margin' + borr)) : 0);
	};
});

// Create scrollLeft and scrollTop methods
$.each( ['Left', 'Top'], function(i, name) {
	$.fn[ 'scroll' + name ] = function(val) {
		if (!this[0]) return;
		
		return val != undefined ?
		
			// Set the scroll offset
			this.each(function() {
				this == window || this == document ?
					window.scrollTo( 
						name == 'Left' ? val : $(window)[ 'scrollLeft' ](),
						name == 'Top'  ? val : $(window)[ 'scrollTop'  ]()
					) :
					this[ 'scroll' + name ] = val;
			}) :
			
			// Return the scroll offset
			this[0] == window || this[0] == document ?
				self[ (name == 'Left' ? 'pageXOffset' : 'pageYOffset') ] ||
					$.boxModel && document.documentElement[ 'scroll' + name ] ||
					document.body[ 'scroll' + name ] :
				this[0][ 'scroll' + name ];
	};
});

$.fn.extend({
	position: function() {
		var left = 0, top = 0, elem = this[0], offset, parentOffset, offsetParent, results;
		
		if (elem) {
			// Get *real* offsetParent
			offsetParent = this.offsetParent();
			
			// Get correct offsets
			offset       = this.offset();
			parentOffset = offsetParent.offset();
			
			// Subtract element margins
			offset.top  -= num(elem, 'marginTop');
			offset.left -= num(elem, 'marginLeft');
			
			// Add offsetParent borders
			parentOffset.top  += num(offsetParent, 'borderTopWidth');
			parentOffset.left += num(offsetParent, 'borderLeftWidth');
			
			// Subtract the two offsets
			results = {
				top:  offset.top  - parentOffset.top,
				left: offset.left - parentOffset.left
			};
		}
		
		return results;
	},
	
	offsetParent: function() {
		var offsetParent = this[0].offsetParent;
		while ( offsetParent && (!/^body|html$/i.test(offsetParent.tagName) && $.css(offsetParent, 'position') == 'static') )
			offsetParent = offsetParent.offsetParent;
		return $(offsetParent);
	}
});

function num(el, prop) {
	return parseInt($.css(el.jquery?el[0]:el,prop))||0;
};

})(jQuery);


/* Copyright (c) 2006 Brandon Aaron (brandon.aaron@gmail.com || http://brandonaaron.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 * Thanks to: http://adomas.org/javascript-mouse-wheel/ for some pointers.
 * Thanks to: Mathias Bank(http://www.mathias-bank.de) for a scope bug fix.
 *
 * $LastChangedDate: 2007-06-20 16:25:35 -0500 (Wed, 20 Jun 2007) $
 * $Rev: 2125 $
 *
 * Version: 2.2
 */
(function($){$.fn.extend({mousewheel:function(f){if(!f.guid)f.guid=$.event.guid++;if(!$.event._mwCache)$.event._mwCache=[];return this.each(function(){if(this._mwHandlers)return this._mwHandlers.push(f);else this._mwHandlers=[];this._mwHandlers.push(f);var s=this;this._mwHandler=function(e){e=$.event.fix(e||window.event);$.extend(e,this._mwCursorPos||{});var delta=0,returnValue=true;if(e.wheelDelta)delta=e.wheelDelta/120;if(e.detail)delta=-e.detail/3;if(window.opera)delta=-e.wheelDelta;for(var i=0;i<s._mwHandlers.length;i++)if(s._mwHandlers[i])if(s._mwHandlers[i].call(s,e,delta)===false){returnValue=false;e.preventDefault();e.stopPropagation();}return returnValue;};if($.browser.mozilla&&!this._mwFixCursorPos){this._mwFixCursorPos=function(e){this._mwCursorPos={pageX:e.pageX,pageY:e.pageY,clientX:e.clientX,clientY:e.clientY};};$(this).bind('mousemove',this._mwFixCursorPos);}if(this.addEventListener)if($.browser.mozilla)this.addEventListener('DOMMouseScroll',this._mwHandler,false);else this.addEventListener('mousewheel',this._mwHandler,false);else
this.onmousewheel=this._mwHandler;$.event._mwCache.push($(this));});},unmousewheel:function(f){return this.each(function(){if(f&&this._mwHandlers){for(var i=0;i<this._mwHandlers.length;i++)if(this._mwHandlers[i]&&this._mwHandlers[i].guid==f.guid)delete this._mwHandlers[i];}else{if($.browser.mozilla&&!this._mwFixCursorPos)$(this).unbind('mousemove',this._mwFixCursorPos);if(this.addEventListener)if($.browser.mozilla)this.removeEventListener('DOMMouseScroll',this._mwHandler,false);else this.removeEventListener('mousewheel',this._mwHandler,false);else
this.onmousewheel=null;this._mwHandlers=this._mwHandler=this._mwFixCursorPos=this._mwCursorPos=null;}});}});$(window).one('unload',function(){var els=$.event._mwCache||[];for(var i=0;i<els.length;i++)els[i].unmousewheel();});})(jQuery);

/*
 * Copyright (c) 2008 Threeformed Media (http://www.threeformed.com)
 * This is licensed under GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 * 
 * 
 * *******
 * 
 * This plugin is derived in part from JScrollPane created by Kevin Luck(http://www.kelvinluck.com)
 * 
 * Copyright (c) 2006 Kelvin Luck (kelvin AT kelvinluck DOT com || http://www.kelvinluck.com)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) 
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 * 
 * See http://kelvinluck.com/assets/jquery/jScrollPane/
 * $Id: jScrollPane.js 3125 2007-09-06 20:39:42Z kelvin.luck $
 */

/**
 * Replace the default horizontal scroll bars on matched 
 * elements with a CSS styled veresion.  Very similar to the JScrollPane 
 * which does vertical scrolling, 2 features in particular have been added. 
 * 
 * 1) Intervals
 * 2) Resizing
 * 
 * 1) Intervals can be added by attaching a class type of "scroll-interval" to any 
 * element wrapped within the jscrollhorizontalpane context.  This provides
 * the following abilitiies: 
 * 			a) When dragging, it will snap to the closest element on release of dragger.
 * 			b) Mousewheel motions jump between intervals
 * 			c) Notches appear by default on the scrollbar, but can be overriden by css.
 * 
 * 2) Resizing Also occurs. When turned on, all widths are dealt in percentages, so on a 
 * screen refresh, the scroller will resize itself based on it's initial percentage.
 * There are a ton of different circumstances that need to be accounted for, and i'm sure 
 * it's not meeting some people's expected behaviour so let me know about any problems or 
 * feature requests for that!  The resizing is done through the WResize plugin.
 * 
 *
 * @example jQuery(".scroll-pane").jScrollHorizontalPane();
 *
 * @name jScrollHorizontalPane
 * @type jQuery
 * @param Object	settings	hash with options, described below.
 *								scrollbarHeight	-	The height of the generated scrollbar in pixels
 *								scrollbarMargin	-	The amount of space to leave on the side of the scrollbar in pixels
 *								wheelSpeed		-	The speed the pane will scroll in response to the mouse wheel in pixels
 *								showArrows		-	Whether to display arrows for the user to scroll with
 *								arrowSize		-	The height of the arrow buttons if showArrows=true
 *								animateTo		-	Whether to animate when calling scrollTo and scrollBy
 *								dragMinWidth	-	The minimum width to allow the drag bar to be
 *								dragMaxWidth	-	The maximum width to allow the drag bar to be
 *								animateInterval	-	The interval in milliseconds to update an animating scrollHorizontalPane (default 100)
 *								animateStep		-	The amount to divide the remaining scroll distance by when animating (default 3)
 *								maintainPosition-	Whether you want the contents of the scroll pane to maintain it's position when you re-initialise it - so it doesn't scroll as you add more content (default true)
 *								resize			- 	Whether or not to have resizing turned on or not.
 * 								minimumWidth    - 	The minimum width to allow the jScrollHorizontalPane to be resized to.  Only effective when resize is on.
 * 								reset			-	When set to 'true' all the global properties will be reset.  This is useful for dynamic refreshes on the page.
 */

var _jscr_originalSizes = new Array();
var _jscr_differenceSizes = new Array();
var _jscr_previousWindowSize = new Array();
var _jscr_originalPercentages = new Array();
var _jscr_intervals = new Array();
var _jscr_trackInt = new Array();
var _jscr_originalPos = new Array();
var _jscr_globalProperties = new Array();

jQuery.jScrollHorizontalPane = {
	active : []
};

jQuery.fn.jScrollHorizontalPane = function(settings)
{
	settings = jQuery.extend(
		{
			scrollbarHeight : 10,
			scrollbarMargin : 5,
			wheelSpeed : 18,
			showArrows : false,
			arrowSize : 10,
			animateTo : false,
			dragMinWidth : 1,
			dragMaxWidth : 99999,
			animateInterval : 100,
			animateStep: 3,
			maintainPosition: true, 
			resize: true,
			minimumWidth: 200,
			reset: false
		}, settings
	);
	
	return this.each(
		function()
		{
			if(settings.reset == true) {
				jQuery.fn.jScrollHorizontalPane.reset();
			}
			
			//This holds each one of the intervals, defaulting with one at the beginning.
			var $this = jQuery(this);			
			var mouseWheelNext = 0;
			var mouseWheelMove = false;
			var currentId = $this.attr('id');
			
			if(currentId == undefined) {
				currentId = $this.attr('class');
			}
			
			var previousWindow = _jscr_previousWindowSize[currentId];
			_jscr_originalPos[currentId] = -1;
			_jscr_globalProperties[currentId] = settings;
			_jscr_previousWindowSize[currentId] = $(window).width();

			//On initial load, set values needed for percentage resizing.
			if(_jscr_originalSizes[currentId] == undefined) {		

				//ie6 hack, since jquery width doesnt get the right value on an inproper refresh
				if((jQuery.browser.msie) && (parseInt(jQuery.browser.version) == 6)) {
					var outerWidth = parseInt($this.outerWidth()) - parseInt($this.offset().left);
					_jscr_differenceSizes[currentId] = $this.offset().left / $(window).width();
				} else {
					var outerWidth = $this.outerWidth();
					_jscr_differenceSizes[currentId] = $this.position().left / $(window).width();
				}

				percentageWidth = (outerWidth / $(window).width());			
				_jscr_originalPercentages[currentId] = percentageWidth;
				_jscr_originalSizes[currentId] = $(window).width();
			} else { 
				percentageWidth = _jscr_originalPercentages[currentId];
				diff = _jscr_differenceSizes[currentId] - (($this.offset().left + _jscr_originalPos[currentId])/ $(window).width());
				percentageWidth = percentageWidth + diff;
			}

			var halfIntervals = new Array();
			_jscr_intervals = new Array();
			halfIntervals[0] = 0;
			_jscr_intervals[0] = 0;
			margin = $this.position().left;
			offset = 1;
			
			if(margin < 0) {
				margin = 0;
			}
			
			//Handles interval code
			$(".scroll-interval", $this).each(
				function(i, elem) {
					pos = $(elem).position().left - margin;
					if(pos != 0) {
						_jscr_intervals[i+offset] = pos;
					} else { 
						offset--;
					}
				}
			);
				
			if(_jscr_intervals.length <= 1) { 
				_jscr_intervals = new Array();
			}

			if (jQuery(this).parent().is('.jScrollPaneContainer')) {
				var currentScrollPosition = settings.maintainPosition ? $this.offset({relativeTo:jQuery(this).parent()[0]}).left : 0;
				var $c = jQuery(this).parent();
				var paneWidth = $c.outerWidth();
				var paneHeight = $c.innerHeight();
				var rightPos = $this.offset().left + _jscr_originalPos[currentId] + paneWidth;
				
				if((previousWindow != $(window).width()) && ((rightPos > $(window).width()) || (previousWindow < $(window).width())) && (settings.resize == true)) {

					if($(window).width() >= _jscr_originalSizes[currentId]) {
						paneWidth = ($(window).width() *  percentageWidth);
					} else {
						//Give the outside edge a 10 px buffer margin
						paneWidth = $(window).width() - ($this.offset().left + _jscr_originalPos[currentId]) - 10;
					}

					if(paneWidth < settings.minimumWidth){
						paneWidth = settings.minimumWidth;
					}

					jQuery(this).parent().css(
									{ 
										'height':paneHeight+'px', 
										'width': paneWidth + 'px'
									}
								);
				}
				
				var trackWidth = paneWidth;

				if($c.unmousewheel) {
						
					if($.browser.opera) {
						$c.unbind("mousewheel", fn = function() { });	
					} else {
						$c.unmousewheel();
					}				
				}

				jQuery('>.jScrollPaneTrack, >.jScrollArrowLeft, >.jScrollArrowRight', $c).remove();
				$this.css({'left':0});
				_jscr_originalPos[currentId] = -1;
			} else {
				var currentScrollPosition = 0;
				this.originalPadding = $this.css('paddingTop') + ' ' + $this.css('paddingRight') + ' ' + $this.css('paddingBottom') + ' ' + $this.css('paddingLeft');
				this.originalSidePaddingTotal = (parseInt($this.css('paddingLeft')) || 0) + (parseInt($this.css('paddingRight')) || 0);
				var paneWidth = $this.outerWidth();
				var rightPos = $this.offset().left + _jscr_originalPos[currentId] + paneWidth;

				if((rightPos) > $(window).width()) { 
					paneWidth = $(window).width() *  percentageWidth; 
				}
				
				if(paneWidth < settings.minimumWidth){
					paneWidth = settings.minimumWidth;
				}
					
				var paneHeight = $this.innerHeight();
				var trackWidth = paneWidth;
				
				$this.wrap(
					jQuery('<div></div>').attr(
						{'className':'jScrollPaneContainer'}
					).css(
						{
							'height':paneHeight+'px', 
							'width':paneWidth+'px'
						}
					)
				);
				// deal with text size changes (if the jquery.em plugin is included)
				// and re-initialise the scrollPane so the track maintains the
				// correct size
				jQuery(document).bind(
					'emchange', 
					function(e, cur, prev)
					{
						$this.jScrollHorizontalPane(settings);
					}
				);
			}
			var p = this.originalSidePaddingTotal;
			
			$this.css(
				{
					'height': paneHeight - settings.scrollbarHeight - p + 'px',
					'width': 'auto',
					/*'paddingRight':settings.scrollbarMargin + 'px'*/
				}
			);

			var contentWidth = $this.outerWidth();

			//ie6 and 7, outside width does not always guarantee the full size of the div
			//is returned for outerWidth
			if($.browser.msie || $.browser.opera || $.browser.safari) {
				var ieWidth = 0;
				$this.children().each(function(i, elem) { if($(elem).outerWidth() > ieWidth) { ieWidth = $(elem).outerWidth();}});
				if(ieWidth > contentWidth) {
					contentWidth = ieWidth;
				}				
			}

			var percentInView = paneWidth / contentWidth;
			var trackIntervals = new Array();

			if (percentInView < 0.99) {
				var $container = $this.parent();

				$container.append(
					jQuery('<div></div>').attr({'className':'jScrollPaneTrack'}).css({'height':settings.scrollbarHeight+'px'}).append(
						jQuery('<div></div>').attr({'className':'jScrollPaneDrag'}).css({'height':settings.scrollbarHeight+'px'}).append(
							jQuery('<div></div>').attr({'className':'jScrollPaneDragLeft'}).css({'height':settings.scrollbarHeight+'px'}),
							jQuery('<div></div>').attr({'className':'jScrollPaneDragRight'}).css({'height':settings.scrollbarHeight+'px'})
						)
					)
				);
				
				var $track = jQuery('>.jScrollPaneTrack', $container);
			
				//Attach the intervals to the track
				for(inter in _jscr_intervals) { 
					
					if(settings.showArrows == true) { 
						scrollOffset = settings.arrowSize;
					} else { 
						scrollOffset = 0;
					}

					intervalTrackPos = _jscr_intervals[inter] / contentWidth * $track.width() - (scrollOffset);
					trackIntervals[inter] = intervalTrackPos;
					
					if(trackIntervals[inter - 1] != undefined) {
						halfIntervals[inter-1] = (trackIntervals[inter] + trackIntervals[inter-1]) / 2;
					}
				
					if(inter != 0) { 
						interObj = jQuery('<div>|</div>').attr({'className':'jScrollIntervalTrack'}).css({'left':intervalTrackPos + 'px'})
						$track.append(interObj);
					}
				}

				var $drag = jQuery('>.jScrollPaneTrack .jScrollPaneDrag', $container);
				
				if (settings.showArrows) {
					
					var currentArrowButton;
					var currentArrowDirection;
					var currentArrowInterval;
					var currentArrowInc;
					var whileArrowButtonDown = function()
					{
						if (currentArrowInc > 4 || currentArrowInc%4==0) {
							positionDrag(dragPosition + currentArrowDirection * mouseWheelMultiplier);
						}
						currentArrowInc ++;
					};
					var onArrowMouseUp = function(event)
					{
						jQuery('body').unbind('mouseup', onArrowMouseUp);
						currentArrowButton.removeClass('jScrollActiveArrowButton');
						clearInterval(currentArrowInterval);
						arrowUp = true;
						moveIntervals();
					};
					var onArrowMouseDown = function() {
						jQuery('body').bind('mouseup', onArrowMouseUp);
						currentArrowButton.addClass('jScrollActiveArrowButton');
						currentArrowInc = 0;
						whileArrowButtonDown();
						currentArrowInterval = setInterval(whileArrowButtonDown, 100);
					};
					$container
						.append(
							jQuery('<a></a>')
								.attr({'href':'javascript:;', 'className':'jScrollArrowLeft'})
								.css({'width':settings.arrowSize+'px'})
								.html('Scroll Left')
								.bind('mousedown', function()
								{
									currentArrowButton = jQuery(this);
									currentArrowDirection = -1;
									onArrowMouseDown();
									this.blur();
									return false;
								}),
							jQuery('<a></a>')
								.attr({'href':'javascript:;', 'className':'jScrollArrowRight'})
								.css({'width':settings.arrowSize+'px'})
								.html('Scroll Right')
								.bind('mousedown', function()
								{
									currentArrowButton = jQuery(this);
									currentArrowDirection = 1;
									onArrowMouseDown();
									this.blur();
									return false;
								})
						);
					if (settings.arrowSize) {
						trackWidth = paneWidth - settings.arrowSize - settings.arrowSize;
						$track
							.css({'width': trackWidth+'px', left:settings.arrowSize+'px'})
					} else {
						var leftArrowWidth = jQuery('>.jScrollArrowLeft', $container).width();
						settings.arrowSize = leftArrowWidth;
						trackWidth = paneWidth - leftArrowWidth - jQuery('>.jScrollArrowRight', $container).width();
						$track
							.css({'width': trackWidth +'px', left: leftArrowWidth+'px'})
					}
				}
				
				var $pane = jQuery(this).css({'position':'absolute', 'overflow':'visible'});
				
				var currentOffset;
				var maxX;
				var mouseWheelMultiplier;
				
				// store this in a seperate variable so we can keep track more accurately than just updating the css property..
				var dragPosition = 0;
				var dragMiddle = percentInView*paneWidth/2;
				
				// pos function borrowed from tooltip plugin and adapted...
				var getPos = function (event, c) {
					var p = c == 'X' ? 'Left' : 'Bottom';
					return event['page' + c] || (event['client' + c] + (document.documentElement['scroll' + p] || document.body['scroll' + p])) || 0;
				};
				
				var ignoreNativeDrag = function() {	return false; };
				var currentInterval = 0;
				var direction = 1;
				var arrowUp = false;
				var intervalMove = false;
				_jscr_trackInt[currentId] = -1;;
						
				var initDrag = function()
				{
					ceaseAnimation();
					currentOffset = $drag.offset(false);
					currentOffset.left -= dragPosition;
					maxX = trackWidth - $drag[0].offsetWidth;
					mouseWheelMultiplier = 2 * settings.wheelSpeed * maxX / contentWidth;
				};
				
				var onStartDrag = function(event)
				{
					initDrag();
					dragMiddle = getPos(event, 'X') - dragPosition - currentOffset.left;
					jQuery('body').bind('mouseup', onStopDrag).bind('mousemove', updateScroll);
					if (jQuery.browser.msie) {
						jQuery('body').bind('dragstart', ignoreNativeDrag).bind('selectstart', ignoreNativeDrag);
					}
					return false;
				};
				var onStopDrag = function()
				{
					jQuery('body').unbind('mouseup', onStopDrag).unbind('mousemove', updateScroll);
					dragMiddle = percentInView*paneWidth/2;
					moveIntervals();
					
					if (jQuery.browser.msie) {
						jQuery('body').unbind('dragstart', ignoreNativeDrag).unbind('selectstart', ignoreNativeDrag);
					}
				};
				var positionDrag = function(destX)
				{
					//Figure out if we need to adjust because of intervals.
					evaluateIntervals(dragPosition, destX);
					destX = destX < 0 ? 0 : (destX > maxX ? maxX : destX);
					dragPosition = destX;

					$drag.css({'left':destX +'px'});
					var p = destX / maxX;
					_jscr_originalPos[currentId] = (paneWidth-contentWidth) * p * -1;
					$pane.css({'left':((paneWidth-contentWidth)*p) + 'px'});
					$this.trigger('scroll');
				};
				
				var updateScroll = function(e)
				{
					positionDrag(getPos(e, 'X') - currentOffset.left - dragMiddle);
				};
				
				var evaluateIntervals = function(position, destX) { 
	
					if((intervalMove == false) && (mouseWheelMove != true)) { 
							_jscr_trackInt[currentId] = -1;
							halfInter = -1;
							
							smallInter = -1;
							bigInter = -1;
							
							endDragPos = destX + $drag.width();
							fullTrackWidth = $('.jScrollPaneTrack').width();

							for(inter in trackIntervals) { 
								if((endDragPos >= fullTrackWidth) && (endDragPos >= trackIntervals[inter])) {
									_jscr_trackInt[currentId] = inter;
								} else if(destX >= trackIntervals[inter]) {
									smallInter = inter;
								}	else { 
									bigInter = inter;
									break;
								}
								
							}

							if(_jscr_trackInt[currentId] == -1) {
								smallDistance = destX - trackIntervals[smallInter];
								largeDistance = trackIntervals[bigInter] - destX;
								
								if(smallDistance <= largeDistance) {
									_jscr_trackInt[currentId] = smallInter;
								} else { 
									_jscr_trackInt[currentId] = bigInter;
								}
							}
					} else { 
						intervalMove = false;
					}
	
				}
				
				var moveIntervals = function() { 
					if(_jscr_trackInt[currentId] != -1) { 
						//Catching arrow clicks
						if(arrowUp == true) { 
							if((direction == -1) && (_jscr_trackInt[currentId] != 0)) { 
								_jscr_trackInt[currentId] = currentInterval - 1;
							} else if((direction == 1) && (_jscr_trackInt[currentId] != (_jscr_intervals.length -1))) {
								_jscr_trackInt[currentId] = parseInt(currentInterval) + 1;
							}
							arrowUp = false;
						} 
						
						intervalMove = true;
						positionDrag(trackIntervals[_jscr_trackInt[currentId]]);
						currentInterval = _jscr_trackInt[currentId];
					}
				}
				
				var arrowSize = 0;
				
				if(settings.showArrows == true) {
					arrowSize = settings.arrowSize;
				} 
				
				var dragH = Math.max(Math.min(percentInView*(paneWidth-arrowSize*2), settings.dragMaxWidth), settings.dragMinWidth);
				
				$drag.css(
					{'width':dragH+'px'}
				).bind('mousedown', onStartDrag);
				
				var trackScrollInterval;
				var trackScrollInc;
				var trackScrollMousePos;
				var doTrackScroll = function()
				{
					if (trackScrollInc > 8 || trackScrollInc%4==0) {
						positionDrag((dragPosition - ((dragPosition - trackScrollMousePos) / 2)));
					}
					trackScrollInc ++;
				};
				var onStopTrackClick = function()
				{
					clearInterval(trackScrollInterval);
					moveIntervals();
					jQuery('body').unbind('mouseup', onStopTrackClick).unbind('mousemove', onTrackMouseMove);
				};
				var onTrackMouseMove = function(event)
				{
					trackScrollMousePos = getPos(event, 'X') - currentOffset.left - dragMiddle;
				};
				var onTrackClick = function(event)
				{
					initDrag();
					onTrackMouseMove(event);
					trackScrollInc = 0;
					jQuery('body').bind('mouseup', onStopTrackClick).bind('mousemove', onTrackMouseMove);
					trackScrollInterval = setInterval(doTrackScroll, 100);
					doTrackScroll();
				};
				
				$track.bind('mousedown', onTrackClick);
				
				// if the mousewheel plugin has been included then also react to the mousewheel
				if ($container.mousewheel) {

					$container.mousewheel (
						function (event, delta) {
							var movePos = -1;
							
							if($.browser.opera) {
								delta = event.wheelDelta / 120;	
							}

							//The following handles intervals with the mouse wheel
							if(trackIntervals.length > 1) {
								mouseWheelMove = true;
										
								//increase or decrease the interval we are currently on, depending
								//on the direction of the mouse wheel
								if(delta < 0) {
									_jscr_trackInt[currentId] = parseInt(_jscr_trackInt[currentId]) + 1;

									if((_jscr_trackInt[currentId]) >= trackIntervals.length - 1) {
										_jscr_trackInt[currentId] = trackIntervals.length - 1;
									}
									
									//If the next interval is beyond the dragWidth then recalculate.
									if((parseInt($drag.width())+ parseInt(trackIntervals[_jscr_trackInt[currentId]])) > parseInt($('.jScrollPaneTrack').width())) {
										movePos = parseInt($('.jScrollPaneTrack').width()) - $drag.width();
									}
									
								} else { 
									_jscr_trackInt[currentId] = parseInt(_jscr_trackInt[currentId]) - 1;
									if(_jscr_trackInt[currentId] < 0) {
										_jscr_trackInt[currentId] = 0;
									}
								}
							}

							initDrag();
							ceaseAnimation();
							var d = dragPosition;
							
							//when intervals are in use, mouseWheelMove is set to true
							if(mouseWheelMove == true) {
								if(movePos == -1) {
									positionDrag(trackIntervals[_jscr_trackInt[currentId]]);
								} else {
									positionDrag(movePos);
								}
							} else {
								positionDrag(dragPosition - delta * mouseWheelMultiplier);
							}
							
							moveIntervals();
							var dragOccured = d != dragPosition;
							mouseWheelMove = false;
							return !dragOccured;
						},
						false
					);					
				}
				var _animateToPosition;
				var _animateToInterval;
				function animateToPosition()
				{

					var diff = (_animateToPosition - dragPosition) / settings.animateStep;
					
					if ((diff > 1 || diff < -1) && ((dragPosition + diff + $drag.width()) < (paneWidth))) {
						positionDrag(dragPosition + diff);
					} else {
						positionDrag(_animateToPosition);
						ceaseAnimation();
					}
				}
				var ceaseAnimation = function()
				{
					if (_animateToInterval) {
						clearInterval(_animateToInterval);
						delete _animateToPosition;
					}
				};
				var scrollTo = function(pos, preventAni)
				{
					if (typeof pos == "string") {
						$e = jQuery(pos, this);

						if (!$e.length) return;
						pos = $e.position().left;
					}

					ceaseAnimation();
					var destDragPosition = -pos/(paneWidth-contentWidth) * maxX;
					if (!preventAni || settings.animateTo) {
						_animateToPosition = destDragPosition;
						_animateToInterval = setInterval(animateToPosition, settings.animateInterval);
		
					} else {
						positionDrag(destDragPosition);
					}	
				};
				$this[0].scrollTo = scrollTo;
				
				$this[0].scrollBy = function(delta)
				{
					var currentPos = -parseInt($pane.css('left')) || 0;
					scrollTo(currentPos + delta);
				};
				
				initDrag();
				
				scrollTo(-currentScrollPosition, true);
				
				jQuery.jScrollHorizontalPane.active.push($this[0]);

			} else {
				var scrollTo = function(pos, preventAni) {}
				$this[0].scrollTo = scrollTo;
				
				$this.css(
					{
						'height':paneHeight-this.originalSidePaddingTotal+'px',
						'width': paneWidth+'px',
						'padding':this.originalPadding
					}
				);
				// remove from active list?
			}
			
		}
	)
};

jQuery.fn.jScrollHorizontalPane.reset = function() {
	_jscr_originalSizes = new Array();
	_jscr_differenceSizes = new Array();
	_jscr_previousWindowSize = new Array();
	_jscr_originalPercentages = new Array();
	_jscr_intervals = new Array();
	_jscr_trackInt = new Array();
	_jscr_originalPos = new Array();
	_jscr_globalProperties = new Array();
}

// clean up the scrollTo expandos
jQuery(window)
	.bind('unload', function() {
		var els = jQuery.jScrollHorizontalPane.active; 
		for (var i=0; i<els.length; i++) {
			els[i].scrollTo = els[i].scrollBy = null;
		}
	}
);
	
	
/* Copyright (c) 2009 Kelvin Luck (kelvin AT kelvinluck DOT com || http://www.kelvinluck.com)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) 
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 * 
 * See http://kelvinluck.com/assets/jquery/jScrollPane/
 * $Id: jScrollPane.js 86 2009-08-30 21:45:11Z kelvin.luck@gmail.com $
 */

/**
 * Replace the vertical scroll bars on any matched elements with a fancy
 * styleable (via CSS) version. With JS disabled the elements will
 * gracefully degrade to the browsers own implementation of overflow:auto.
 * If the mousewheel plugin has been included on the page then the scrollable areas will also
 * respond to the mouse wheel.
 *
 * @example jQuery(".scroll-pane").jScrollPane();
 *
 * @name jScrollPane
 * @type jQuery
 * @param Object	settings	hash with options, described below.
 *								scrollbarWidth	-	The width of the generated scrollbar in pixels
 *								scrollbarMargin	-	The amount of space to leave on the side of the scrollbar in pixels
 *								wheelSpeed		-	The speed the pane will scroll in response to the mouse wheel in pixels
 *								showArrows		-	Whether to display arrows for the user to scroll with
 *								arrowSize		-	The height of the arrow buttons if showArrows=true
 *								animateTo		-	Whether to animate when calling scrollTo and scrollBy
 *								dragMinHeight	-	The minimum height to allow the drag bar to be
 *								dragMaxHeight	-	The maximum height to allow the drag bar to be
 *								animateInterval	-	The interval in milliseconds to update an animating scrollPane (default 100)
 *								animateStep		-	The amount to divide the remaining scroll distance by when animating (default 3)
 *								maintainPosition-	Whether you want the contents of the scroll pane to maintain it's position when you re-initialise it - so it doesn't scroll as you add more content (default true)
 *								tabIndex		-	The tabindex for this jScrollPane to control when it is tabbed to when navigating via keyboard (default 0)
 *								enableKeyboardNavigation - Whether to allow keyboard scrolling of this jScrollPane when it is focused (default true)
 *								animateToInternalLinks - Whether the move to an internal link (e.g. when it's focused by tabbing or by a hash change in the URL) should be animated or instant (default false)
 *								scrollbarOnLeft	-	Display the scrollbar on the left side?  (needs stylesheet changes, see examples.html)
 *								reinitialiseOnImageLoad - Whether the jScrollPane should automatically re-initialise itself when any contained images are loaded (default false)
 *								topCapHeight	-	The height of the "cap" area between the top of the jScrollPane and the top of the track/ buttons
 *								bottomCapHeight	-	The height of the "cap" area between the bottom of the jScrollPane and the bottom of the track/ buttons
 * @return jQuery
 * @cat Plugins/jScrollPane
 * @author Kelvin Luck (kelvin AT kelvinluck DOT com || http://www.kelvinluck.com)
 */

(function($) {

$.jScrollPane = {
	active : []
};
$.fn.jScrollPane = function(settings)
{
	settings = $.extend({}, $.fn.jScrollPane.defaults, settings);

	var rf = function() { return false; };
	
	return this.each(
		function()
		{
			var $this = $(this);
			var paneEle = this;
			var currentScrollPosition = 0;
			var paneWidth;
			var paneHeight;
			var trackHeight;
			var trackOffset = settings.topCapHeight;
			
			if ($(this).parent().is('.jScrollPaneContainer')) {
				currentScrollPosition = settings.maintainPosition ? $this.position().top : 0;
				var $c = $(this).parent();
				paneWidth = $c.innerWidth();
				paneHeight = $c.outerHeight();
				$('>.jScrollPaneTrack, >.jScrollArrowUp, >.jScrollArrowDown, >.jScollCap', $c).remove();
				$this.css({'top':0});
			} else {
				$this.data('originalStyleTag', $this.attr('style'));
				// Switch the element's overflow to hidden to ensure we get the size of the element without the scrollbars [http://plugins.jquery.com/node/1208]
				$this.css('overflow', 'hidden');
				this.originalPadding = $this.css('paddingTop') + ' ' + $this.css('paddingRight') + ' ' + $this.css('paddingBottom') + ' ' + $this.css('paddingLeft');
				this.originalSidePaddingTotal = (parseInt($this.css('paddingLeft')) || 0) + (parseInt($this.css('paddingRight')) || 0);
				paneWidth = $this.innerWidth();
				paneHeight = $this.innerHeight();
				var $container = $('<div></div>')
					.attr({'className':'jScrollPaneContainer'})
					.css(
						{
							'height':paneHeight+'px', 
							'width':paneWidth+'px'
						}
					);
				if (settings.enableKeyboardNavigation) {
					$container.attr(
						'tabindex', 
						settings.tabIndex
					);
				}
				$this.wrap($container);
				// deal with text size changes (if the jquery.em plugin is included)
				// and re-initialise the scrollPane so the track maintains the
				// correct size
				$(document).bind(
					'emchange', 
					function(e, cur, prev)
					{
						$this.jScrollPane(settings);
					}
				);
				
			}
			trackHeight = paneHeight;
			
			if (settings.reinitialiseOnImageLoad) {
				// code inspired by jquery.onImagesLoad: http://plugins.jquery.com/project/onImagesLoad
				// except we re-initialise the scroll pane when each image loads so that the scroll pane is always up to size...
				// TODO: Do I even need to store it in $.data? Is a local variable here the same since I don't pass the reinitialiseOnImageLoad when I re-initialise?
				var $imagesToLoad = $.data(paneEle, 'jScrollPaneImagesToLoad') || $('img', $this);
				var loadedImages = [];
				
				if ($imagesToLoad.length) {
					$imagesToLoad.each(function(i, val)	{
						$(this).bind('load readystatechange', function() {
							if($.inArray(i, loadedImages) == -1){ //don't double count images
								loadedImages.push(val); //keep a record of images we've seen
								$imagesToLoad = $.grep($imagesToLoad, function(n, i) {
									return n != val;
								});
								$.data(paneEle, 'jScrollPaneImagesToLoad', $imagesToLoad);
								var s2 = $.extend(settings, {reinitialiseOnImageLoad:false});
								$this.jScrollPane(s2); // re-initialise
							}
						}).each(function(i, val) {
							if(this.complete || this.complete===undefined) { 
								//needed for potential cached images
								this.src = this.src; 
							} 
						});
					});
				};
			}

			var p = this.originalSidePaddingTotal;
			var realPaneWidth = paneWidth - settings.scrollbarWidth - settings.scrollbarMargin - p;

			var cssToApply = {
				'height':'auto',
				'width': realPaneWidth + 'px'
			}

			if(settings.scrollbarOnLeft) {
				cssToApply.paddingLeft = settings.scrollbarMargin + settings.scrollbarWidth + 'px';
			} else {
				cssToApply.paddingRight = settings.scrollbarMargin + 'px';
			}

			$this.css(cssToApply);

			var contentHeight = $this.outerHeight();
			var percentInView = paneHeight / contentHeight;

			if (percentInView < .99) {
				var $container = $this.parent();
				$container.append(
					$('<div></div>').addClass('jScrollCap jScrollCapTop').css({height:settings.topCapHeight}),
					$('<div></div>').attr({'className':'jScrollPaneTrack'}).css({'width':settings.scrollbarWidth+'px'}).append(
						$('<div></div>').attr({'className':'jScrollPaneDrag'}).css({'width':settings.scrollbarWidth+'px'}).append(
							$('<div></div>').attr({'className':'jScrollPaneDragTop'}).css({'width':settings.scrollbarWidth+'px'}),
							$('<div></div>').attr({'className':'jScrollPaneDragBottom'}).css({'width':settings.scrollbarWidth+'px'})
						)
					),
					$('<div></div>').addClass('jScrollCap jScrollCapBottom').css({height:settings.bottomCapHeight})
				);
				
				var $track = $('>.jScrollPaneTrack', $container);
				var $drag = $('>.jScrollPaneTrack .jScrollPaneDrag', $container);
				
				
				var currentArrowDirection;
				var currentArrowTimerArr = [];// Array is used to store timers since they can stack up when dealing with keyboard events. This ensures all timers are cleaned up in the end, preventing an acceleration bug.
				var currentArrowInc;
				var whileArrowButtonDown = function() 
				{
					if (currentArrowInc > 4 || currentArrowInc % 4 == 0) {
						positionDrag(dragPosition + currentArrowDirection * mouseWheelMultiplier);
					}
					currentArrowInc++;
				};

				if (settings.enableKeyboardNavigation) {
					$container.bind(
						'keydown.jscrollpane',
						function(e) 
						{
							switch (e.keyCode) {
								case 38: //up
									currentArrowDirection = -1;
									currentArrowInc = 0;
									whileArrowButtonDown();
									currentArrowTimerArr[currentArrowTimerArr.length] = setInterval(whileArrowButtonDown, 100);
									return false;
								case 40: //down
									currentArrowDirection = 1;
									currentArrowInc = 0;
									whileArrowButtonDown();
									currentArrowTimerArr[currentArrowTimerArr.length] = setInterval(whileArrowButtonDown, 100);
									return false;
								case 33: // page up
								case 34: // page down
									// TODO
									return false;
								default:
							}
						}
					).bind(
						'keyup.jscrollpane',
						function(e) 
						{
							if (e.keyCode == 38 || e.keyCode == 40) {
								for (var i = 0; i < currentArrowTimerArr.length; i++) {
									clearInterval(currentArrowTimerArr[i]);
								}
								return false;
							}
						}
					);
				}

				if (settings.showArrows) {
					
					var currentArrowButton;
					var currentArrowInterval;

					var onArrowMouseUp = function(event)
					{
						$('html').unbind('mouseup', onArrowMouseUp);
						currentArrowButton.removeClass('jScrollActiveArrowButton');
						clearInterval(currentArrowInterval);
					};
					var onArrowMouseDown = function() {
						$('html').bind('mouseup', onArrowMouseUp);
						currentArrowButton.addClass('jScrollActiveArrowButton');
						currentArrowInc = 0;
						whileArrowButtonDown();
						currentArrowInterval = setInterval(whileArrowButtonDown, 100);
					};
					$container
						.append(
							$('<a></a>')
								.attr(
									{
										'href':'javascript:;', 
										'className':'jScrollArrowUp', 
										'tabindex':-1
									}
								)
								.css(
									{
										'width':settings.scrollbarWidth+'px',
										'top':settings.topCapHeight + 'px'
									}
								)
								.html('Scroll up')
								.bind('mousedown', function()
								{
									currentArrowButton = $(this);
									currentArrowDirection = -1;
									onArrowMouseDown();
									this.blur();
									return false;
								})
								.bind('click', rf),
							$('<a></a>')
								.attr(
									{
										'href':'javascript:;', 
										'className':'jScrollArrowDown', 
										'tabindex':-1
									}
								)
								.css(
									{
										'width':settings.scrollbarWidth+'px',
										'bottom':settings.bottomCapHeight + 'px'
									}
								)
								.html('Scroll down')
								.bind('mousedown', function()
								{
									currentArrowButton = $(this);
									currentArrowDirection = 1;
									onArrowMouseDown();
									this.blur();
									return false;
								})
								.bind('click', rf)
						);
					var $upArrow = $('>.jScrollArrowUp', $container);
					var $downArrow = $('>.jScrollArrowDown', $container);
				}
				
				if (settings.arrowSize) {
					trackHeight = paneHeight - settings.arrowSize - settings.arrowSize;
					trackOffset += settings.arrowSize;
				} else if ($upArrow) {
					var topArrowHeight = $upArrow.height();
					settings.arrowSize = topArrowHeight;
					trackHeight = paneHeight - topArrowHeight - $downArrow.height();
					trackOffset += topArrowHeight;
				}
				trackHeight -= settings.topCapHeight + settings.bottomCapHeight;
				$track.css({'height': trackHeight+'px', top:trackOffset+'px'})
				
				var $pane = $(this).css({'position':'absolute', 'overflow':'visible'});
				
				var currentOffset;
				var maxY;
				var mouseWheelMultiplier;
				// store this in a seperate variable so we can keep track more accurately than just updating the css property..
				var dragPosition = 0;
				var dragMiddle = percentInView*paneHeight/2;
				
				// pos function borrowed from tooltip plugin and adapted...
				var getPos = function (event, c) {
					var p = c == 'X' ? 'Left' : 'Top';
					return event['page' + c] || (event['client' + c] + (document.documentElement['scroll' + p] || document.body['scroll' + p])) || 0;
				};
				
				var ignoreNativeDrag = function() {	return false; };
				
				var initDrag = function()
				{
					ceaseAnimation();
					currentOffset = $drag.offset(false);
					currentOffset.top -= dragPosition;
					maxY = trackHeight - $drag[0].offsetHeight;
					mouseWheelMultiplier = 2 * settings.wheelSpeed * maxY / contentHeight;
				};
				
				var onStartDrag = function(event)
				{
					initDrag();
					dragMiddle = getPos(event, 'Y') - dragPosition - currentOffset.top;
					$('html').bind('mouseup', onStopDrag).bind('mousemove', updateScroll);
					if ($.browser.msie) {
						$('html').bind('dragstart', ignoreNativeDrag).bind('selectstart', ignoreNativeDrag);
					}
					return false;
				};
				var onStopDrag = function()
				{
					$('html').unbind('mouseup', onStopDrag).unbind('mousemove', updateScroll);
					dragMiddle = percentInView*paneHeight/2;
					if ($.browser.msie) {
						$('html').unbind('dragstart', ignoreNativeDrag).unbind('selectstart', ignoreNativeDrag);
					}
				};
				var positionDrag = function(destY)
				{
					$container.scrollTop(0);
					destY = destY < 0 ? 0 : (destY > maxY ? maxY : destY);
					dragPosition = destY;
					$drag.css({'top':destY+'px'});
					var p = destY / maxY;
					$this.data('jScrollPanePosition', (paneHeight-contentHeight)*-p);
					$pane.css({'top':((paneHeight-contentHeight)*p) + 'px'});
					$this.trigger('scroll');
					if (settings.showArrows) {
						$upArrow[destY == 0 ? 'addClass' : 'removeClass']('disabled');
						$downArrow[destY == maxY ? 'addClass' : 'removeClass']('disabled');
					}
				};
				var updateScroll = function(e)
				{
					positionDrag(getPos(e, 'Y') - currentOffset.top - dragMiddle);
				};
				
				var dragH = Math.max(Math.min(percentInView*(paneHeight-settings.arrowSize*2), settings.dragMaxHeight), settings.dragMinHeight);
				
				$drag.css(
					{'height':dragH+'px'}
				).bind('mousedown', onStartDrag);
				
				var trackScrollInterval;
				var trackScrollInc;
				var trackScrollMousePos;
				var doTrackScroll = function()
				{
					if (trackScrollInc > 8 || trackScrollInc%4==0) {
						positionDrag((dragPosition - ((dragPosition - trackScrollMousePos) / 2)));
					}
					trackScrollInc ++;
				};
				var onStopTrackClick = function()
				{
					clearInterval(trackScrollInterval);
					$('html').unbind('mouseup', onStopTrackClick).unbind('mousemove', onTrackMouseMove);
				};
				var onTrackMouseMove = function(event)
				{
					trackScrollMousePos = getPos(event, 'Y') - currentOffset.top - dragMiddle;
				};
				var onTrackClick = function(event)
				{
					initDrag();
					onTrackMouseMove(event);
					trackScrollInc = 0;
					$('html').bind('mouseup', onStopTrackClick).bind('mousemove', onTrackMouseMove);
					trackScrollInterval = setInterval(doTrackScroll, 100);
					doTrackScroll();
					return false;
				};
				
				$track.bind('mousedown', onTrackClick);
				
				$container.bind(
					'mousewheel',
					function (event, delta) {
						delta = delta || (event.wheelDelta ? event.wheelDelta / 120 : (event.detail) ?
-event.detail/3 : 0);
						initDrag();
						ceaseAnimation();
						var d = dragPosition;
						positionDrag(dragPosition - delta * mouseWheelMultiplier);
						var dragOccured = d != dragPosition;
						return !dragOccured;
					}
				);

				var _animateToPosition;
				var _animateToInterval;
				function animateToPosition()
				{
					var diff = (_animateToPosition - dragPosition) / settings.animateStep;
					if (diff > 1 || diff < -1) {
						positionDrag(dragPosition + diff);
					} else {
						positionDrag(_animateToPosition);
						ceaseAnimation();
					}
				}
				var ceaseAnimation = function()
				{
					if (_animateToInterval) {
						clearInterval(_animateToInterval);
						delete _animateToPosition;
					}
				};
				var scrollTo = function(pos, preventAni)
				{
					if (typeof pos == "string") {
						$e = $(pos, $this);
						if (!$e.length) return;
						pos = $e.offset().top - $this.offset().top;
					}
					ceaseAnimation();
					var maxScroll = contentHeight - paneHeight;
					pos = pos > maxScroll ? maxScroll : pos;
					$this.data('jScrollPaneMaxScroll', maxScroll);
					var destDragPosition = pos/maxScroll * maxY;
					if (preventAni || !settings.animateTo) {
						positionDrag(destDragPosition);
					} else {
						$container.scrollTop(0);
						_animateToPosition = destDragPosition;
						_animateToInterval = setInterval(animateToPosition, settings.animateInterval);
					}
				};
				$this[0].scrollTo = scrollTo;
				
				$this[0].scrollBy = function(delta)
				{
					var currentPos = -parseInt($pane.css('top')) || 0;
					scrollTo(currentPos + delta);
				};
				
				initDrag();
				
				scrollTo(-currentScrollPosition, true);
			
				// Deal with it when the user tabs to a link or form element within this scrollpane
				$('*', this).bind(
					'focus',
					function(event)
					{
						var $e = $(this);
						
						// loop through parents adding the offset top of any elements that are relatively positioned between
						// the focused element and the jScrollPaneContainer so we can get the true distance from the top
						// of the focused element to the top of the scrollpane...
						var eleTop = 0;
						
						while ($e[0] != $this[0]) {
							eleTop += $e.position().top;
							$e = $e.offsetParent();
						}
						
						var viewportTop = -parseInt($pane.css('top')) || 0;
						var maxVisibleEleTop = viewportTop + paneHeight;
						var eleInView = eleTop > viewportTop && eleTop < maxVisibleEleTop;
						if (!eleInView) {
							var destPos = eleTop - settings.scrollbarMargin;
							if (eleTop > viewportTop) { // element is below viewport - scroll so it is at bottom.
								destPos += $(this).height() + 15 + settings.scrollbarMargin - paneHeight;
							}
							scrollTo(destPos);
						}
					}
				)
				
				
				if (location.hash && location.hash.length > 1) {
					setTimeout(function() {scrollTo(location.hash);}, $.browser.safari ? 100 : 0);
				}
				
				// use event delegation to listen for all clicks on links and hijack them if they are links to
				// anchors within our content...
				$(document).bind(
					'click',
					function(e)
					{
						$target = $(e.target);
						if ($target.is('a')) {
							var h = $target.attr('href');
							if (h && h.substr(0, 1) == '#' && h.length > 1) {
								setTimeout(function() {scrollTo(h, !settings.animateToInternalLinks);}, $.browser.safari ? 100 : 0);
							}
						}
					}
				); 
				
				// Deal with dragging and selecting text to make the scrollpane scroll...
				function onSelectScrollMouseDown(e)
				{
				   $(document).bind('mousemove.jScrollPaneDragging', onTextSelectionScrollMouseMove);
				   $(document).bind('mouseup.jScrollPaneDragging',   onSelectScrollMouseUp);
				  
				}
				
				var textDragDistanceAway;
				var textSelectionInterval;
				
				function onTextSelectionInterval()
				{
					direction = textDragDistanceAway < 0 ? -1 : 1;
					$this[0].scrollBy(textDragDistanceAway / 2);
				}

				function clearTextSelectionInterval()
				{
					if (textSelectionInterval) {
						clearInterval(textSelectionInterval);
						textSelectionInterval = undefined;
					}
				}
				
				function onTextSelectionScrollMouseMove(e)
				{
					var offset = $this.parent().offset().top;
					var maxOffset = offset + paneHeight;
					var mouseOffset = getPos(e, 'Y');
					textDragDistanceAway = mouseOffset < offset ? mouseOffset - offset : (mouseOffset > maxOffset ? mouseOffset - maxOffset : 0);
					if (textDragDistanceAway == 0) {
						clearTextSelectionInterval();
					} else {
						if (!textSelectionInterval) {
							textSelectionInterval  = setInterval(onTextSelectionInterval, 100);
						}
					}
				}

				function onSelectScrollMouseUp(e)
				{
				   $(document)
					  .unbind('mousemove.jScrollPaneDragging')
					  .unbind('mouseup.jScrollPaneDragging');
				   clearTextSelectionInterval();
				}

				$container.bind('mousedown.jScrollPane', onSelectScrollMouseDown);

				
				$.jScrollPane.active.push($this[0]);
				
			} else {
				$this.css(
					{
						'height':paneHeight+'px',
						'width':paneWidth-this.originalSidePaddingTotal+'px',
						'padding':this.originalPadding
					}
				);
				$this[0].scrollTo = $this[0].scrollBy = function() {};
				// clean up listeners
				$this.parent().unbind('mousewheel').unbind('mousedown.jScrollPane').unbind('keydown.jscrollpane').unbind('keyup.jscrollpane');
			}
			
		}
	)
};

$.fn.jScrollPaneRemove = function()
{
	$(this).each(function()
	{
		$this = $(this);
		var $c = $this.parent();
		if ($c.is('.jScrollPaneContainer')) {
			$this.css(
				{
					'top':'',
					'height':'',
					'width':'',
					'padding':'',
					'overflow':'',
					'position':''
				}
			);
			$this.attr('style', $this.data('originalStyleTag'));
			$c.after($this).remove();
		}
	});
}

$.fn.jScrollPane.defaults = {
	scrollbarWidth : 10,
	scrollbarMargin : 5,
	wheelSpeed : 18,
	showArrows : false,
	arrowSize : 0,
	animateTo : false,
	dragMinHeight : 1,
	dragMaxHeight : 99999,
	animateInterval : 100,
	animateStep: 3,
	maintainPosition: true,
	scrollbarOnLeft: false,
	reinitialiseOnImageLoad: false,
	tabIndex : 0,
	enableKeyboardNavigation: true,
	animateToInternalLinks: false,
	topCapHeight: 0,
	bottomCapHeight: 0
};

// clean up the scrollTo expandos
$(window)
	.bind('unload', function() {
		var els = $.jScrollPane.active; 
		for (var i=0; i<els.length; i++) {
			els[i].scrollTo = els[i].scrollBy = null;
		}
	}
);

})(jQuery);
	
	
	
/*   
=============================================================================== 
WResize is the jQuery plugin for fixing the IE window resize bug 
............................................................................... 
                                               Copyright 2007 / Andrea Ercolino 
------------------------------------------------------------------------------- 
LICENSE: http://www.opensource.org/licenses/mit-license.php 
WEBSITE: http://noteslog.com/ 

------------------------------------------------------------------------------- 
USAGE: (div is automatically resized when the window is resized)
------------------------------------------------------------------------------- 
*/ 
 
( function( $ )  
{ 
 
	jQuery(function($) {
		$( window ).wresize(resizeScroller);
		
		function resizeScroller() {
		$('.scroll-pane').each(function(i, elem) { 	
			
			if($(elem).attr('id') == undefined) {
				id = $(elem).attr('class');
			} else {
				id = $(elem).attr('id');
			}
			$(elem).jScrollHorizontalPane(_jscr_globalProperties[$(elem).attr('id')]);
		});
		}	
	});


    $.fn.wresize = function( f )  
    { 
        version = '1.1'; 
        wresize = {fired: false, width: 0}; 
 
        function resizeOnce()  
        { 
            if ( $.browser.msie ) 
            { 
                if ( ! wresize.fired ) 
                { 
                    wresize.fired = true; 
                } 
                else  
                { 
                    var version = parseInt( $.browser.version, 10 ); 
                    wresize.fired = false; 
                    if ( version < 7 ) 
                    { 
                        return false; 
                    } 
                    else if ( version == 7 ) 
                    { 
                        //a vertical resize is fired once, an horizontal resize twice 
                        var width = $( window ).width(); 
                        if ( width != wresize.width ) 
                        { 
                            wresize.width = width; 
                            return false; 
                        } 
                    } 
                } 
            }
 
            return true; 
        } 
 
        function handleWResize( e )  
        { 
			if ( resizeOnce() ) 
            { 
                return f.apply(this, [e]); 
            } 
        } 
 
        this.each( function()  
        { 
            if ( this == window ) 
            { 
                $( this ).resize( handleWResize ); 
            } 
            else 
            { 
                $( this ).resize( f ); 
            } 
        } ); 
 
        return this; 
    }; 
 
} ) ( jQuery );


/*
 *  jQuery selectBox (version 1.0.7)
 *
 *  Copyright 2011 Cory LaViska for A Beautiful Site, LLC.
 *
 *  http://abeautifulsite.net/blog/2011/01/jquery-selectbox-plugin/
 *
 */
if(jQuery)(function($){$.extend($.fn,{selectBox:function(method,data){var typeTimer,typeSearch='';var init=function(select,data){if(navigator.userAgent.match(/iPad|iPhone|Android/i))return false;if(select.tagName.toLowerCase()!=='select')return false;select=$(select);if(select.data('selectBox-control'))return false;var control=$('<a class="selectBox" />'),inline=select.attr('multiple')||parseInt(select.attr('size'))>1;var settings=data||{};if(settings.autoWidth===undefined)settings.autoWidth=true;control.addClass(select.attr('class')).attr('style',select.attr('style')||'').attr('title',select.attr('title')||'').attr('tabindex',parseInt(select.attr('tabindex'))).css('display','inline-block').bind('focus.selectBox',function(){if(this!==document.activeElement)$(document.activeElement).blur();if(control.hasClass('selectBox-active'))return;control.addClass('selectBox-active');select.trigger('focus')}).bind('blur.selectBox',function(){if(!control.hasClass('selectBox-active'))return;control.removeClass('selectBox-active');select.trigger('blur')});if(select.attr('disabled'))control.addClass('selectBox-disabled');if(inline){var options=getOptions(select,'inline');control.append(options).data('selectBox-options',options).addClass('selectBox-inline').addClass('selectBox-menuShowing').bind('keydown.selectBox',function(event){handleKeyDown(select,event)}).bind('keypress.selectBox',function(event){handleKeyPress(select,event)}).bind('mousedown.selectBox',function(event){if($(event.target).is('A.selectBox-inline'))event.preventDefault();if(!control.hasClass('selectBox-focus'))control.focus()}).insertAfter(select);if(!select[0].style.height){var size=select.attr('size')?parseInt(select.attr('size')):5;var tmp=control.clone().removeAttr('id').css({position:'absolute',top:'-9999em'}).show().appendTo('body');tmp.find('.selectBox-options').html('<li><a>\u00A0</a></li>');optionHeight=parseInt(tmp.find('.selectBox-options A:first').html('&nbsp;').outerHeight());tmp.remove();control.height(optionHeight*size)}disableSelection(control)}else{var label=$('<span class="selectBox-label" />'),arrow=$('<span class="selectBox-arrow" />');label.text($(select).find('OPTION:selected').text()||'\u00A0');var options=getOptions(select,'dropdown');options.appendTo('BODY');control.data('selectBox-options',options).addClass('selectBox-dropdown').append(label).append(arrow).bind('mousedown.selectBox',function(event){if(control.hasClass('selectBox-menuShowing')){hideMenus()}else{event.stopPropagation();options.data('selectBox-down-at-x',event.screenX).data('selectBox-down-at-y',event.screenY);showMenu(select)}}).bind('keydown.selectBox',function(event){handleKeyDown(select,event)}).bind('keypress.selectBox',function(event){handleKeyPress(select,event)}).insertAfter(select);disableSelection(control)}select.addClass('selectBox').data('selectBox-control',control).data('selectBox-settings',settings).hide()};var getOptions=function(select,type){var options;switch(type){case'inline':options=$('<ul class="selectBox-options" />');if(select.find('OPTGROUP').length){select.find('OPTGROUP').each(function(){var optgroup=$('<li class="selectBox-optgroup" />');optgroup.text($(this).attr('label'));options.append(optgroup);$(this).find('OPTION').each(function(){var li=$('<li />'),a=$('<a />');li.addClass($(this).attr('class'));a.attr('rel',$(this).val()).text($(this).text());li.append(a);if($(this).attr('disabled'))li.addClass('selectBox-disabled');if($(this).attr('selected'))li.addClass('selectBox-selected');options.append(li)})})}else{select.find('OPTION').each(function(){var li=$('<li />'),a=$('<a />');li.addClass($(this).attr('class'));a.attr('rel',$(this).val()).text($(this).text());li.append(a);if($(this).attr('disabled'))li.addClass('selectBox-disabled');if($(this).attr('selected'))li.addClass('selectBox-selected');options.append(li)})}options.find('A').bind('mouseover.selectBox',function(event){addHover(select,$(this).parent())}).bind('mouseout.selectBox',function(event){removeHover(select,$(this).parent())}).bind('mousedown.selectBox',function(event){event.preventDefault();if(!select.selectBox('control').hasClass('selectBox-active'))select.selectBox('control').focus()}).bind('mouseup.selectBox',function(event){hideMenus();selectOption(select,$(this).parent(),event)});disableSelection(options);return options;case'dropdown':options=$('<ul class="selectBox-dropdown-menu selectBox-options" />');if(select.find('OPTGROUP').length){select.find('OPTGROUP').each(function(){var optgroup=$('<li class="selectBox-optgroup" />');optgroup.text($(this).attr('label'));options.append(optgroup);$(this).find('OPTION').each(function(){var li=$('<li />'),a=$('<a />');li.addClass($(this).attr('class'));a.attr('rel',$(this).val()).text($(this).text());li.append(a);if($(this).attr('disabled'))li.addClass('selectBox-disabled');if($(this).attr('selected'))li.addClass('selectBox-selected');options.append(li)})})}else{if(select.find('OPTION').length>0){select.find('OPTION').each(function(){var li=$('<li />'),a=$('<a />');li.addClass($(this).attr('class'));a.attr('rel',$(this).val()).text($(this).text());li.append(a);if($(this).attr('disabled'))li.addClass('selectBox-disabled');if($(this).attr('selected'))li.addClass('selectBox-selected');options.append(li)})}else{options.append('<li>\u00A0</li>')}}options.data('selectBox-select',select).css('display','none').appendTo('BODY').find('A').bind('mousedown.selectBox',function(event){event.preventDefault();if(event.screenX===options.data('selectBox-down-at-x')&&event.screenY===options.data('selectBox-down-at-y')){options.removeData('selectBox-down-at-x').removeData('selectBox-down-at-y');hideMenus()}}).bind('mouseup.selectBox',function(event){if(event.screenX===options.data('selectBox-down-at-x')&&event.screenY===options.data('selectBox-down-at-y')){return}else{options.removeData('selectBox-down-at-x').removeData('selectBox-down-at-y')}selectOption(select,$(this).parent());hideMenus()}).bind('mouseover.selectBox',function(event){addHover(select,$(this).parent())}).bind('mouseout.selectBox',function(event){removeHover(select,$(this).parent())});disableSelection(options);return options}};var destroy=function(select){select=$(select);var control=select.data('selectBox-control');if(!control)return;var options=control.data('selectBox-options');options.remove();control.remove();select.removeClass('selectBox').removeData('selectBox-control').removeData('selectBox-settings').show()};var showMenu=function(select){select=$(select);var control=select.data('selectBox-control'),settings=select.data('selectBox-settings'),options=control.data('selectBox-options');if(control.hasClass('selectBox-disabled'))return false;hideMenus();if(settings.autoWidth)options.css('width',control.outerWidth()-(parseInt(control.css('borderLeftWidth'))+parseInt(control.css('borderLeftWidth'))));options.css({top:control.offset().top+control.outerHeight()-(parseInt(control.css('borderBottomWidth'))),left:control.offset().left});switch(settings.menuTransition){case'fade':options.fadeIn(settings.menuSpeed);break;case'slide':options.slideDown(settings.menuSpeed);break;default:options.show(settings.menuSpeed);break}var li=options.find('.selectBox-selected:first');keepOptionInView(select,li,true);addHover(select,li);control.addClass('selectBox-menuShowing');$(document).bind('mousedown.selectBox',function(event){if($(event.target).parents().andSelf().hasClass('selectBox-options'))return;hideMenus()})};var hideMenus=function(){if($(".selectBox-dropdown-menu").length===0)return;$(document).unbind('mousedown.selectBox');$(".selectBox-dropdown-menu").each(function(){var options=$(this),select=options.data('selectBox-select'),control=select.data('selectBox-control'),settings=select.data('selectBox-settings');switch(settings.menuTransition){case'fade':options.fadeOut(settings.menuSpeed);break;case'slide':options.slideUp(settings.menuSpeed);break;default:options.hide(settings.menuSpeed);break}control.removeClass('selectBox-menuShowing')})};var selectOption=function(select,li,event){select=$(select);li=$(li);var control=select.data('selectBox-control'),settings=select.data('selectBox-settings');if(control.hasClass('selectBox-disabled'))return false;if(li.length===0||li.hasClass('selectBox-disabled'))return false;if(select.attr('multiple')){if(event.shiftKey&&control.data('selectBox-last-selected')){li.toggleClass('selectBox-selected');var affectedOptions;if(li.index()>control.data('selectBox-last-selected').index()){affectedOptions=li.siblings().slice(control.data('selectBox-last-selected').index(),li.index())}else{affectedOptions=li.siblings().slice(li.index(),control.data('selectBox-last-selected').index())}affectedOptions=affectedOptions.not('.selectBox-optgroup, .selectBox-disabled');if(li.hasClass('selectBox-selected')){affectedOptions.addClass('selectBox-selected')}else{affectedOptions.removeClass('selectBox-selected')}}else if(event.metaKey){li.toggleClass('selectBox-selected')}else{li.siblings().removeClass('selectBox-selected');li.addClass('selectBox-selected')}}else{li.siblings().removeClass('selectBox-selected');li.addClass('selectBox-selected')}if(control.hasClass('selectBox-dropdown')){control.find('.selectBox-label').text(li.text())}var i=0,selection=[];if(select.attr('multiple')){control.find('.selectBox-selected A').each(function(){selection[i++]=$(this).attr('rel')})}else{selection=li.find('A').attr('rel')}control.data('selectBox-last-selected',li);if(select.val()!==selection){select.val(selection);select.trigger('change')}return true};var addHover=function(select,li){select=$(select);li=$(li);var control=select.data('selectBox-control'),options=control.data('selectBox-options');options.find('.selectBox-hover').removeClass('selectBox-hover');li.addClass('selectBox-hover')};var removeHover=function(select,li){select=$(select);li=$(li);var control=select.data('selectBox-control'),options=control.data('selectBox-options');options.find('.selectBox-hover').removeClass('selectBox-hover')};var keepOptionInView=function(select,li,center){if(!li||li.length===0)return;select=$(select);var control=select.data('selectBox-control'),options=control.data('selectBox-options'),scrollBox=control.hasClass('selectBox-dropdown')?options:options.parent(),top=parseInt(li.offset().top-scrollBox.position().top),bottom=parseInt(top+li.outerHeight());if(center){scrollBox.scrollTop(li.offset().top-scrollBox.offset().top+scrollBox.scrollTop()-(scrollBox.height()/2))}else{if(top<0){scrollBox.scrollTop(li.offset().top-scrollBox.offset().top+scrollBox.scrollTop())}if(bottom>scrollBox.height()){scrollBox.scrollTop((li.offset().top+li.outerHeight())-scrollBox.offset().top+scrollBox.scrollTop()-scrollBox.height())}}};var handleKeyDown=function(select,event){select=$(select);var control=select.data('selectBox-control'),options=control.data('selectBox-options'),totalOptions=0,i=0;if(control.hasClass('selectBox-disabled'))return;switch(event.keyCode){case 8:event.preventDefault();typeSearch='';break;case 9:case 27:hideMenus();removeHover(select);break;case 13:if(control.hasClass('selectBox-menuShowing')){selectOption(select,options.find('LI.selectBox-hover:first'),event);if(control.hasClass('selectBox-dropdown'))hideMenus()}else{showMenu(select)}break;case 38:case 37:event.preventDefault();if(control.hasClass('selectBox-menuShowing')){var prev=options.find('.selectBox-hover').prev('LI');totalOptions=options.find('LI:not(.selectBox-optgroup)').length;i=0;while(prev.length===0||prev.hasClass('selectBox-disabled')||prev.hasClass('selectBox-optgroup')){prev=prev.prev('LI');if(prev.length===0)prev=options.find('LI:last');if(++i>=totalOptions)break}addHover(select,prev);keepOptionInView(select,prev)}else{showMenu(select)}break;case 40:case 39:event.preventDefault();if(control.hasClass('selectBox-menuShowing')){var next=options.find('.selectBox-hover').next('LI');totalOptions=options.find('LI:not(.selectBox-optgroup)').length;i=0;while(next.length===0||next.hasClass('selectBox-disabled')||next.hasClass('selectBox-optgroup')){next=next.next('LI');if(next.length===0)next=options.find('LI:first');if(++i>=totalOptions)break}addHover(select,next);keepOptionInView(select,next)}else{showMenu(select)}break}};var handleKeyPress=function(select,event){select=$(select);var control=select.data('selectBox-control'),options=control.data('selectBox-options');if(control.hasClass('selectBox-disabled'))return;switch(event.keyCode){case 9:case 27:case 13:case 38:case 37:case 40:case 39:break;default:if(!control.hasClass('selectBox-menuShowing'))showMenu(select);event.preventDefault();clearTimeout(typeTimer);typeSearch+=String.fromCharCode(event.charCode||event.keyCode);options.find('A').each(function(){if($(this).text().substr(0,typeSearch.length).toLowerCase()===typeSearch.toLowerCase()){addHover(select,$(this).parent());keepOptionInView(select,$(this).parent());return false}});typeTimer=setTimeout(function(){typeSearch=''},1000);break}};var enable=function(select){select=$(select);select.attr('disabled',false);var control=select.data('selectBox-control');if(!control)return;control.removeClass('selectBox-disabled')};var disable=function(select){select=$(select);select.attr('disabled',true);var control=select.data('selectBox-control');if(!control)return;control.addClass('selectBox-disabled')};var setValue=function(select,value){select=$(select);select.val(value);value=select.val();var control=select.data('selectBox-control');if(!control)return;var settings=select.data('selectBox-settings'),options=control.data('selectBox-options');control.find('.selectBox-label').text($(select).find('OPTION:selected').text()||'\u00A0');options.find('.selectBox-selected').removeClass('selectBox-selected');options.find('A').each(function(){if(typeof(value)==='object'){for(var i=0;i<value.length;i++){if($(this).attr('rel')==value[i]){$(this).parent().addClass('selectBox-selected')}}}else{if($(this).attr('rel')==value){$(this).parent().addClass('selectBox-selected')}}});if(settings.change)settings.change.call(select)};var setOptions=function(select,options){select=$(select);var control=select.data('selectBox-control'),settings=select.data('selectBox-settings');switch(typeof(data)){case'string':select.html(data);break;case'object':select.html('');for(var i in data){if(data[i]===null)continue;if(typeof(data[i])==='object'){var optgroup=$('<optgroup label="'+i+'" />');for(var j in data[i]){optgroup.append('<option value="'+j+'">'+data[i][j]+'</option>')}select.append(optgroup)}else{var option=$('<option value="'+i+'">'+data[i]+'</option>');select.append(option)}}break}if(!control)return;control.data('selectBox-options').remove();var type=control.hasClass('selectBox-dropdown')?'dropdown':'inline',options=getOptions(select,type);control.data('selectBox-options',options);switch(type){case'inline':control.append(options);break;case'dropdown':control.find('.selectBox-label').text($(select).find('OPTION:selected').text()||'\u00A0');$("BODY").append(options);break}};var disableSelection=function(selector){$(selector).css('MozUserSelect','none').bind('selectstart',function(event){event.preventDefault()})};switch(method){case'control':return $(this).data('selectBox-control');break;case'settings':if(!data)return $(this).data('selectBox-settings');$(this).each(function(){$(this).data('selectBox-settings',$.extend(true,$(this).data('selectBox-settings'),data))});break;case'options':$(this).each(function(){setOptions(this,data)});break;case'value':if(!data)return $(this).val();$(this).each(function(){setValue(this,data)});break;case'enable':$(this).each(function(){enable(this)});break;case'disable':$(this).each(function(){disable(this)});break;case'destroy':$(this).each(function(){destroy(this)});break;default:$(this).each(function(){init(this,method)});break}return $(this)}})})(jQuery);