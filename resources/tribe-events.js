// tribe local storage

var tribe_storage, t_fail, t_uid;
try {
    t_uid = new Date;
    (tribe_storage = window.localStorage).setItem(t_uid, t_uid);
    t_fail = tribe_storage.getItem(t_uid) != t_uid;
    tribe_storage.removeItem(t_uid);
    t_fail && (tribe_storage = false);
} catch (e) {
}

// live ajax timer

var tribe_ajax_timer;

// jquery functions

(function ($) {
    $.fn.tribe_clear_form = function () {
        return this.each(function () {
            var type = this.type, tag = this.tagName.toLowerCase();
            if (tag == 'form')
                return jQuery(':input', this).tribe_clear_form();
            if (type == 'text' || type == 'password' || tag == 'textarea')
                this.value = '';
            else if (type == 'checkbox' || type == 'radio')
                this.checked = false;
            else if (tag == 'select')
                this.selectedIndex = 0;
        });
    };
    $.fn.tribe_has_attr = function (name) {
        return this.attr(name) !== undefined;
    };
})(jQuery);

// tribe events object

var tribe_ev = {};

tribe_ev.fn = {
    current_date: function () {
        var today = new Date();
        var dd = today.getDate();
        var mm = today.getMonth() + 1;
        var yyyy = today.getFullYear();
        if (dd < 10) {
            dd = '0' + dd
        }
        if (mm < 10) {
            mm = '0' + mm
        }
        return yyyy + '-' + mm + '-' + dd;
    },
    disable_inputs: function (parent, type) {
        jQuery(parent).find(type).prop('disabled', true);
        if (jQuery(parent).find('.select2-container').length) {
            jQuery(parent).find('.select2-container').each(function () {
                var s2_id = jQuery(this).attr('id');
                var $this = jQuery('#' + s2_id);
                $this.select2("disable");
            });
        }
    },
    disable_empty: function (parent, type) {
        jQuery(parent).find(type).each(function () {
            if (jQuery(this).val() === '') {
                jQuery(this).prop('disabled', true);
            }
        });
    },
    enable_inputs: function (parent, type) {
        jQuery(parent).find(type).prop('disabled', false);
        if (jQuery(parent).find('.select2-container').length) {
            jQuery(parent).find('.select2-container').each(function () {
                var s2_id = jQuery(this).attr('id');
                var $this = jQuery('#' + s2_id);
                $this.select2("enable");
            });
        }
    },
    get_base_url: function () {
        var base_url = '';
        if (jQuery('#tribe-events-header').length){
            base_url = jQuery('#tribe-events-header').attr('data-baseurl');
        }
        return base_url;
    },
    get_category: function () {
        if (tribe_ev.fn.is_category())
            return jQuery('#tribe-events').attr('data-category');
        else
            return '';
    },
    get_day: function () {
        var dp_day = '';
        if (jQuery('#tribe-bar-date').length) {
            dp_day = jQuery('#tribe-bar-date-day').val();
        }
        return dp_day;
    },
    get_params: function () {
        return location.search.substr(1);
    },
    get_url_param: function (tribe_param_name) {
        return decodeURIComponent((new RegExp('[?|&]' + tribe_param_name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search) || [, ""])[1].replace(/\+/g, '%20')) || null;
    },
    hide_settings: function () {
        jQuery('#tribe-events-bar [class^="tribe-bar-button-"]')
            .removeClass('open')
            .next('.tribe-bar-drop-content')
            .hide();
    },
    in_params: function (params, term) {
        return params.toLowerCase().indexOf(term);
    },
    is_category: function () {
        return (jQuery('#tribe-events').length && jQuery('#tribe-events').tribe_has_attr('data-category') && jQuery('#tribe-events').attr('data-category') !== '') ? true : false;
    },
    make_slug: function (string) {
        var string_h = string.replace(/\s/g, '-');
        var slug = string_h.replace(/[^a-zA-Z0-9\-]/g, '');
        return slug.toLowerCase();
    },
    parse_string: function (string) {
        var map = {};
        string.replace(/([^&=]+)=?([^&]*)(?:&+|$)/g, function (match, key, value) {
            (map[key] = map[key] || []).push(value);
        });
        return map;
    },
    pre_ajax: function (tribe_ajax_callback) {

        if (jQuery('#tribe-bar-geoloc').length) {
            var tribe_map_val = jQuery('#tribe-bar-geoloc').val();
            if (tribe_map_val.length) {
                tribe_process_geocoding(tribe_map_val, function (tribe_geoloc_results) {

                    var tribe_geoloc_lat = tribe_geoloc_results[0].geometry.location.lat();
                    var tribe_geoloc_lng = tribe_geoloc_results[0].geometry.location.lng();
                    if (tribe_geoloc_lat)
                        jQuery('#tribe-bar-geoloc-lat').val(tribe_geoloc_lat);

                    if (tribe_geoloc_lng)
                        jQuery('#tribe-bar-geoloc-lng').val(tribe_geoloc_lng);

                    if (tribe_ajax_callback && typeof( tribe_ajax_callback ) === "function") {
                        if (jQuery("#tribe_events_filter_item_geofence").length)
                            jQuery("#tribe_events_filter_item_geofence").show();
                        tribe_ajax_callback();
                    }
                });
            } else {
                jQuery('#tribe-bar-geoloc-lat, #tribe-bar-geoloc-lng').val('');
                if (tribe_ajax_callback && typeof( tribe_ajax_callback ) === "function") {
                    if (jQuery("#tribe_events_filter_item_geofence").length) {
                        jQuery('#tribe_events_filter_item_geofence input').prop('checked', false);
                        jQuery("#tribe_events_filter_item_geofence").hide().find('select').prop('selectedIndex', 0);
                    }
                    tribe_ajax_callback();
                }
            }
        } else {

            if (tribe_ajax_callback && typeof( tribe_ajax_callback ) === "function") {
                tribe_ajax_callback();
            }
        }
    },
    serialize: function (form, type) {
        tribe_ev.fn.enable_inputs(form, type);
        tribe_ev.fn.disable_empty(form, type);
        var params = jQuery(form).serialize();
        tribe_ev.fn.disable_inputs(form, type);
        return params;
    },
    set_form: function (params) {
        jQuery('body').addClass('tribe-reset-on');

        var has_sliders = false;
        var has_select2 = false;

        if (jQuery('#tribe_events_filters_form').length) {
            var $form = jQuery('#tribe_events_filters_form');

            $form.tribe_clear_form();

            if ($form.find('.select2-container').length) {

                has_select2 = true;

                jQuery('#tribe_events_filters_form .select2-container').select2("val", {});
            }

            if ($form.find('.ui-slider').length) {

                has_sliders = true;

                jQuery('#tribe_events_filters_form .ui-slider').each(function () {
                    var s_id = jQuery(this).attr('id');
                    var $this = jQuery('#' + s_id);
                    var $input = $this.prev();
                    var $display = $input.prev();
                    var settings = $this.slider("option");

                    $this.slider("values", 0, settings.min);
                    $this.slider("values", 1, settings.max);
                    $display.text(settings.min + " - " + settings.max);
                    $input.val('');
                });
            }
        }

        if (jQuery('#tribe-bar-form').length) {
            jQuery('#tribe-bar-form').tribe_clear_form();
        }

        params = tribe_ev.fn.parse_string(params);

        jQuery.each(params, function (key, value) {
            if (key !== 'action') {
                var name = decodeURI(key);
                var $target = '';
                if (value.length === 1) {
                    if (jQuery('[name="' + name + '"]').is('input[type="text"], input[type="hidden"]')) {
                        jQuery('[name="' + name + '"]').val(value);
                    } else if (jQuery('[name="' + name + '"][value="' + value + '"]').is(':checkbox, :radio')) {
                        jQuery('[name="' + name + '"][value="' + value + '"]').prop("checked", true);
                    } else if (jQuery('[name="' + name + '"]').is('select')) {
                        jQuery('select[name="' + name + '"] option[value="' + value + '"]').attr('selected', true);
                    }
                } else {
                    for (var i = 0; i < value.length; i++) {
                        $target = jQuery('[name="' + name + '"][value="' + value[i] + '"]');
                        if ($target.is(':checkbox, :radio')) {
                            $target.prop("checked", true);
                        } else {
                            jQuery('select[name="' + name + '"] option[value="' + value[i] + '"]').attr('selected', true);
                        }
                    }
                }
            }
        });

        if (has_sliders) {
            jQuery('#tribe_events_filters_form .ui-slider').each(function () {
                var s_id = jQuery(this).attr('id');
                var $this = jQuery('#' + s_id);
                var $input = $this.prev();
                var range = $input.val().split('-');

                if (range[0] !== '') {
                    var $display = $input.prev();

                    $this.slider("values", 0, range[0]);
                    $this.slider("values", 1, range[1]);
                    $display.text(range[0] + " - " + range[1]);
                    $this.slider('refresh');
                }
            });
        }

        if (has_select2) {
            jQuery('#tribe_events_filters_form .select2-container').each(function () {
                var s2_id = jQuery(this).attr('id');
                var $this = jQuery('#' + s2_id);
                $this.next().trigger("change");
            });
        }

        jQuery('body').removeClass('tribe-reset-on');
    },
    setup_ajax_timer: function (callback) {
        clearTimeout(tribe_ajax_timer);
        if (!tribe_ev.tests.reset_on()) {
            tribe_ajax_timer = setTimeout(function () {
                callback();
            }, 500);
        }
    },
    snap: function (container, trigger_parent, trigger) {
        jQuery(trigger_parent).on('click', trigger, function (e) {
            jQuery('html, body').animate({scrollTop: jQuery(container).offset().top - 120}, {duration: 0});
        });
    },
    spin_hide: function () {
        jQuery('#tribe-events-footer, #tribe-events-header').find('.tribe-events-ajax-loading').hide();
    },
    spin_show: function () {
        jQuery('#tribe-events-footer, #tribe-events-header').find('.tribe-events-ajax-loading').show();
    },
    tooltips: function () {

        jQuery('body').on('mouseenter', '.tribe-events-calendar-full div[id*="tribe-events-event-"], .tribe-events-calendar-full div[id*="tribe-events-daynum-"]:has(a), div.event-is-recurring',function () {

            var bottomPad = 0;
            var $this = jQuery(this);

            if (jQuery('body').hasClass('tribe-events-week')) {

                var $tip = $this.find('.tribe-events-tooltip');

                if (!$this.parents('.tribe-grid-allday').length) {

                    var $wrapper = jQuery('.tribe-week-grid-wrapper');
                    var $parent = $this.parent();
                    var $container = $parent.parent();

                    var pwidth = Math.ceil($container.width());
                    var cwidth = Math.ceil($this.width());
                    var twidth = Math.ceil($tip.outerWidth());
                    var gheight = $wrapper.height();

                    var scroll = $wrapper.scrollTop();
                    var coffset = $parent.position();
                    var poffset = $this.position();
                    var ptop = Math.ceil(poffset.top);
                    var toffset = scroll - ptop;

                    var isright = $parent.hasClass('tribe-events-right');
                    var wcheck;
                    var theight;
                    var available;
                    var cssmap = {};

                    if (!$tip.hasClass('hovered')) {
                        $tip.attr('data-ow', twidth).addClass('hovered');
                    }

                    if (isright)
                        wcheck = Math.ceil(coffset.left) - 20;
                    else
                        wcheck = pwidth - cwidth - Math.ceil(coffset.left);

                    if (twidth >= wcheck)
                        twidth = wcheck;
                    else if ($tip.attr('data-ow') > wcheck)
                        twidth = wcheck;
                    else
                        twidth = $tip.attr('data-ow');

                    if (isright)
                        cssmap = { "right": cwidth + 20, "bottom": "auto", "width": twidth + "px"};
                    else
                        cssmap = { "left": cwidth + 20, "bottom": "auto", "width": twidth + "px"};

                    $tip.css(cssmap);

                    theight = $tip.height();

                    if (toffset >= 0) {
                        toffset = toffset + 5;
                    } else {
                        available = toffset + gheight;
                        if (theight > available)
                            toffset = available - theight - 8;
                        else
                            toffset = 5;
                    }

                    $tip.css("top", toffset).show();

                } else {
                    bottomPad = $this.outerHeight() + 6;
                    $tip.css('bottom', bottomPad).show();
                }


            } else if (jQuery('body').hasClass('events-gridview')) { // Cal View Tooltips
                bottomPad = $this.find('a').outerHeight() + 18;
            } else if (jQuery('body').is('.single-tribe_events, .events-list')) { // Single/List View Recurring Tooltips
                bottomPad = $this.outerHeight() + 12;
            } else if (jQuery('body').is('.tribe-events-photo')) { // Photo View
                bottomPad = $this.outerHeight() + 10;
            }

            // Widget Tooltips
            if ($this.parents('.tribe-events-calendar-widget').length) {
                bottomPad = $this.outerHeight() - 6;
            }
            if (!jQuery('body').hasClass('tribe-events-week')) {
                $this.find('.tribe-events-tooltip').css('bottom', bottomPad).show();
            }

        }).on('mouseleave', 'div[id*="tribe-events-event-"], div[id*="tribe-events-daynum-"]:has(a), div.event-is-recurring', function () {
                jQuery(this).find('.tribe-events-tooltip').stop(true, false).fadeOut(200);
            });
    },
    update_picker: function (date) {
        if (jQuery().bootstrapDatepicker && jQuery("#tribe-bar-date").length) {
            jQuery("#tribe-bar-date").bootstrapDatepicker("setValue", date);
        } else if (jQuery("#tribe-bar-date").length) {
            jQuery("#tribe-bar-date").val(date);
        }
    },
    url_path: function (url) {
        return url.split("?")[0];
    }
};

tribe_ev.tests = {
    live_ajax: function () {
        return (jQuery('#tribe-events').length && jQuery('#tribe-events').tribe_has_attr('data-live_ajax') && jQuery('#tribe-events').attr('data-live_ajax') == '1') ? true : false;
    },
    map_view: function () {
        return ( typeof GeoLoc !== 'undefined' && GeoLoc.map_view ) ? true : false;
    },
    pushstate: !!(window.history && history.pushState),
    reset_on: function () {
        return jQuery('body').is('.tribe-reset-on');
    },
    starting_delim: function () {
        return tribe_ev.state.cur_url.indexOf('?') != -1 ? '&' : '?';
    }
};

tribe_ev.data = {
    ajax_response: {},
    base_url: '',
    cur_url: tribe_ev.fn.url_path(document.URL),
    cur_date: tribe_ev.fn.current_date(),
    initial_url: tribe_ev.fn.url_path(document.URL),
    params: tribe_ev.fn.get_params()
};

tribe_ev.events = {};

tribe_ev.state = {
    ajax_running: false,
    category: '',
    date: '',
    do_string: false,
    filters: false,
    filter_cats: false,
    initial_load: true,
    paged: 1,
    params: {},
    popping: false,
    pushstate: true,
    pushcount: 0,
    recurrence: false,
    url_params: {},
    view: '',
    view_target: ''
};

jQuery(document).ready(function ($) {
    var touchAction = "click";
    if ('ontouchstart' in document.documentElement || navigator.msMaxTouchPoints > 0 ) {
           touchAction = window.navigator.msPointerEnabled ? "MSPointerDown" : "touchstart";
    }

    var $tribecalendar = $('table.tribe-events-calendar');

    // Check width of events bar
    function calendarWidth($tribegrid) {

        var tribeCalendarWidth = $tribecalendar.width();

        if (tribeCalendarWidth < 670) {
            $tribecalendar.removeClass('tribe-events-calendar-full').addClass('tribe-events-calendar-collapse');
        } else {
            $tribecalendar.removeClass('tribe-events-calendar-collapse').addClass('tribe-events-calendar-full');
             $('.tribe-events-calendar-collapse-list').remove();
        }
    }

    calendarWidth($tribecalendar);

    $tribecalendar.resize(function () {
        calendarWidth($tribecalendar);
    });

    $('#tribe-events').on( touchAction, '.tribe-events-has-events', function() {

        var $this = $(this);

        if ( $('.tribe-events-calendar').is('.tribe-events-calendar-collapse') ) {
            console.log('the calendar is responsive');

            var $eventsToAdd = $this.find('.tribe-events-tooltip').clone();
            var $viewMore = $this.find('.tribe-events-viewmore').clone();


            if ( !$('.tribe-events-calendar-collapse-list').length ) {
                console.log('the list wrapper does not exist');

                $('#tribe-events-content').append('<div class="tribe-events-calendar-collapse-list" />');
                var $eventsListWrap = $('.tribe-events-calendar-collapse-list');

                $eventsListWrap.html( $eventsToAdd );

                $eventsListWrap.find('.tribe-events-tooltip')
                    .removeClass('tribe-events-tooltip')
                    .addClass('tribe-events-mini-event');
                
                if(typeof $viewMore != 'undefined') {
                    console.log($viewMore);
                   $eventsListWrap.append( $viewMore );
                }
                $('html, body').stop().animate( {
                  scrollTop:$eventsListWrap.offset().top - 100
                 }, {
                  duration: 500
                });                

            } else {
                console.log('the list wrapper exists');                
                var $eventsListWrap = $('.tribe-events-calendar-collapse-list');
                
                $eventsListWrap.html( $eventsToAdd );
                
                $eventsListWrap.find('.tribe-events-tooltip')
                    .removeClass('tribe-events-tooltip')
                    .addClass('tribe-events-mini-event');

                if(typeof $viewMore != 'undefined') {
                    console.log($viewMore);
                    $eventsListWrap.append( $viewMore );
                }               
                $('html, body').stop().animate( {
                  scrollTop:$eventsListWrap.offset().top - 100
                 }, {
                  duration: 500                
                });                 
            }

        }

    });

    tribe_ev.state.category = tribe_ev.fn.get_category();
    tribe_ev.data.base_url = tribe_ev.fn.get_base_url();

    var tribe_display = tribe_ev.fn.get_url_param('tribe_event_display');

    if (tribe_display) {
        tribe_ev.state.view = tribe_display;
    } else if ($('#tribe-events-header').length && $('#tribe-events-header').tribe_has_attr('data-view')) {
        tribe_ev.state.view = $('#tribe-events-header').attr('data-view');
    }

    /* Let's hide the widget calendar if we find more than one instance */
    $(".tribe-events-calendar-widget").not(":eq(0)").hide();

    // Global Tooltips
    tribe_ev.fn.tooltips();

    //remove border on list view event before month divider
    if ($('.tribe-events-list').length) {
        $('.tribe-events-list-separator-month').prev('.vevent').addClass('tribe-event-end-month');
    }
});
