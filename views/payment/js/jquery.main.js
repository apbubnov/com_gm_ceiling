var bindingSelector;
var selectdItem;
function initMain() {
    $('.code-wrap .opener-tooltip, .tooltip-wrap .close').click(function () {
        $(this).closest('.code-wrap').toggleClass('active-tooltip');
        return false;
    });
    $(document).click(function (event) {
        if ($(event.target).closest('.code-wrap').length) return;
        $('.code-wrap').removeClass('active-tooltip');
        event.stopPropagation();
    });
    $('.code-wrap .opener-code a').click(function () {
        if ($('.code-wrap .code').hasClass('text')) {
            $(this).text('Показать код').parent().removeClass('active').closest('.code-wrap').find('.code').removeClass('text').attr('type', 'password');
        } else {
            $(this).text('Скрыть код').parent().addClass('active').closest('.code-wrap').find('.code').addClass('text').attr('type', 'text');
        }
        return false;
    });
    alignLoad();
    var oc_timer;
    $(window).on("orientationchange", function (event) {
        clearTimeout(oc_timer);
        oc_timer = setTimeout(function () {
            alignLoad();
        }, 500);
    });
    function alignLoad() {
        var _w = window.innerWidth,
            bg = $('.load-wrapper .bg'),
            popup = $('.load-wrapper .clock'),
            _h = $(document).height();
        bg.css({"height": _h, "width": _w + $(window).scrollLeft()});
        if ((($(window).height() / 2) - (popup.outerHeight() / 2)) + $(window).scrollTop() < 0) {
            popup.css({'top': 0, 'left': ((window.innerWidth - popup.outerWidth()) / 2) + $(window).scrollLeft()});
            return false;
        }
        popup.css({
            'top': (($(window).height() - popup.outerHeight()) / 2) + $(window).scrollTop(),
            'left': ((window.innerWidth - popup.outerWidth()) / 2) + $(window).scrollLeft()
        });
    }

    (function ($) {
        $.widget("custom.combobox", {
            _create: function () {
                this.wrapper = $("<span>")
                    .addClass("custom-combobox")
                    .insertAfter(this.element);
                this.element.hide();
                this._createAutocomplete();
                //this._createShowAllButton();
            },
            _createAutocomplete: function () {
                bindingSelector = this;
                var selected = this.element.children(":selected"),
                    value = selected.val() ? selected.text() : "";
                this.input = $("<input  maxlength='23' type='tel'>")
                    .appendTo(this.wrapper)
                    .val(value)
                    .attr("title", "")
                    .addClass("custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-all")
                    .attr('id', 'pan_visible')
					.attr('tabindex', "0")
                    .autocomplete({
                        delay: 0,
                        minLength: 0,
                        source: $.proxy(this, "_source"),
                        open: function () {
                            $(this).parent().find('.ui-button').addClass('active');
                        }
                    })
                    .tooltip({
                        tooltipClass: "ui-state-highlight"
                    })
                    ;
                this.input.keyup(function (event){
                    $("#iPAN").val(event.target.value.split(" ").join(""));
                    $("#iPAN").trigger('keyup.payment');
                });
                this.input.mousedown(function (event){
                    event.target.value = event.target.value.replace(/ *$/,'');
                });
                this.input.mask("?9999 9999 9999 9999 999",{placeholder:" "});
                this._on(this.input, {
                    autocompleteselect: function (event, ui) {
                        $(this.input).parent().find('.ui-button').removeClass('active');
                        ui.item.option.selected = true;
                        this._trigger("select", event, {
                            item: ui.item.option
                        });
                        var input = $(this.input);
                        setTimeout(function () {
                            input.blur();
                            input.focus();
                        }, 10);
                        selectdItem = ui.item;
                        $("#combobox").change();
                    }/*
                     ,
                     autocompletechange: "_removeIfInvalid"
                     */
                });
            },
            _source: function (request, response) {
                var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), "i");
                response(this.element.children("option").map(function () {
                    var text = $(this).text();
                    if (this.value && ( !request.term || matcher.test(text) ))
                        return {
                            label: text,
                            value: text,
                            option: this
                        };
                }));
            },
            _destroy: function () {
                this.wrapper.remove();
                this.element.show();
            }
        });
    })(jQuery);
    $(function () {
        $("#combobox").combobox();
        $("#toggle").click(function () {
            $("#combobox").toggle();
        });
    });
	
	window.onload = function(e) {
		$('#pan_visible').focus();
		$('#pan_visible').prop('type', 'tel');
		$('#pan_visible').prop('inputmode', 'numeric');
		$('#pan_visible').prop('pattern', '[0-9]*');
	}
};


/*! device.js 0.1.58 */
(function () {
    var a, b, c, d, e, f, g, h, i, j;
    a = window.device, window.device = {}, c = window.document.documentElement, j = window.navigator.userAgent.toLowerCase(), device.ios = function () {
        return device.iphone() || device.ipod() || device.ipad()
    }, device.iphone = function () {
        return d("iphone")
    }, device.ipod = function () {
        return d("ipod")
    }, device.ipad = function () {
        return d("ipad")
    }, device.android = function () {
        return d("android")
    }, device.androidPhone = function () {
        return device.android() && d("mobile")
    }, device.androidTablet = function () {
        return device.android() && !d("mobile")
    }, device.blackberry = function () {
        return d("blackberry") || d("bb10") || d("rim")
    }, device.blackberryPhone = function () {
        return device.blackberry() && !d("tablet")
    }, device.blackberryTablet = function () {
        return device.blackberry() && d("tablet")
    }, device.windows = function () {
        return d("windows")
    }, device.windowsPhone = function () {
        return device.windows() && d("phone")
    }, device.windowsTablet = function () {
        return device.windows() && d("touch")
    }, device.fxos = function () {
        return d("(mobile; rv:") || d("(tablet; rv:")
    }, device.fxosPhone = function () {
        return device.fxos() && d("mobile")
    }, device.fxosTablet = function () {
        return device.fxos() && d("tablet")
    }, device.mobile = function () {
        return device.androidPhone() || device.iphone() || device.ipod() || device.windowsPhone() || device.blackberryPhone() || device.fxosPhone()
    }, device.tablet = function () {
        return device.ipad() || device.androidTablet() || device.blackberryTablet() || device.windowsTablet() || device.fxosTablet()
    }, device.portrait = function () {
        return 90 !== Math.abs(window.orientation)
    }, device.landscape = function () {
        return 90 === Math.abs(window.orientation)
    }, device.noConflict = function () {
        return window.device = a, this
    }, d = function (a) {
        return-1 !== j.indexOf(a)
    }, f = function (a) {
        var b;
        return b = new RegExp(a, "i"), c.className.match(b)
    }, b = function (a) {
        return f(a) ? void 0 : c.className += " " + a
    }, h = function (a) {
        return f(a) ? c.className = c.className.replace(a, "") : void 0
    }, device.ios() ? device.ipad() ? b("ios ipad tablet") : device.iphone() ? b("ios iphone mobile") : device.ipod() && b("ios ipod mobile") : device.android() ? device.androidTablet() ? b("android tablet") : b("android mobile") : device.blackberry() ? device.blackberryTablet() ? b("blackberry tablet") : b("blackberry mobile") : device.windows() ? device.windowsTablet() ? b("windows tablet") : device.windowsPhone() ? b("windows mobile") : b("desktop") : device.fxos() ? device.fxosTablet() ? b("fxos tablet") : b("fxos mobile") : b("desktop"), e = function () {
        return device.landscape() ? (h("portrait"), b("landscape")) : (h("landscape"), b("portrait"))
    }, i = "onorientationchange"in window, g = i ? "orientationchange" : "resize", window.addEventListener ? window.addEventListener(g, e, !1) : window.attachEvent ? window.attachEvent(g, e) : window[g] = e, e()
}).call(this);