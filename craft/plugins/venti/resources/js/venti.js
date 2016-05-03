"use strict";

var _createClass = (function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; })();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

(function () {
    function CustomEvent(event, params) {
        params = params || { bubbles: false, cancelable: false, detail: undefined };
        var evt = document.createEvent('CustomEvent');
        evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);
        return evt;
    }

    CustomEvent.prototype = window.Event.prototype;
    window.CustomEvent = CustomEvent;

    Object.defineProperty(Object.prototype, "indexOfKey", {
        value: function value(_value) {
            var i = 1;
            for (var key in this) {
                if (key == _value) {
                    return i;
                }
                i++;
            }
            return undefined;
        }
    });

    String.prototype.capitalize = function () {
        return this.charAt(0).toUpperCase() + this.slice(1);
    };
})();
/**
 * Venti Input Class
 */

var VentiInput = (function () {
    function VentiInput(options) {
        _classCallCheck(this, VentiInput);

        this._id = options.id;
        this._input = document.getElementById(options.id + "-venti-field");
        this._storedDates = {
            state: false,
            startTime: '',
            endTime: ''
        };

        this.loadModal(options);
    }

    /*
     * Venti Modal
     */

    _createClass(VentiInput, [{
        key: "initEvents",
        value: function initEvents() {
            var $this = this,
                input = $this._input,
                modal = $this._modal,
                startDateInput = input.querySelectorAll('.venti-startdate--input')[0],
                endDateInput = input.querySelectorAll('.venti-enddate--input')[0],
                rrule = input.querySelectorAll('.venti-rrule')[0];

            input.addEventListener('click', function (evt) {
                var elm = evt.target;
                if (elm.className === 'venti-eventRepeat-edit') {
                    modal.show();
                } else if (elm.className === 'venti-allday--input') {
                    $this.toggleAllDay(evt);
                } else if (elm.className === 'venti-eventRepeat') {
                    if (elm.checked) {
                        $this.endDateValidate(endDateInput);
                        modal.show();
                    }
                    $this.endDateFauxDisable(elm);
                    if (rrule.value !== "") {
                        $this.clearSummary();
                    }
                }
            }, false);

            startDateInput.addEventListener('focusout', function (evt) {
                var thisSDI = this;
                setTimeout(function () {
                    $this.startDateValidate(thisSDI);
                }, 200);
            }, false);

            endDateInput.addEventListener('focusout', function (evt) {
                var thisEDI = this;
                setTimeout(function () {
                    $this.endDateValidate(thisEDI);
                }, 200);
            }, false);

            document.body.addEventListener('onHide', function (evt) {
                if (evt.srcElement.id === modal.container.id) {
                    var rrule = input.querySelectorAll('.venti-rrule')[0],
                        repeatCbBtn = input.querySelectorAll('.venti-eventRepeat')[0];
                    if (rrule.value === "") {
                        repeatCbBtn.checked = false;
                        $this.endDateFauxDisable(repeatCbBtn);
                    }
                }
            }, false);

            document.body.addEventListener('onShow', function (evt) {
                if (evt.srcElement.id === modal.container.id) {
                    modal.schedule.setStartOn();
                }
            }, false);
        }
    }, {
        key: "loadModal",
        value: function loadModal(options) {
            var $this = this;
            Craft.postActionRequest('venti/ajax/modal', {
                name: options.id,
                rrule: options.values !== null ? options.values.rRule : "",
                locale: options.locale
            }, function (data) {
                // Append modal content
                //[jQ]
                $('body').append(data);
                $this._modal = new VentiModal(options);
                $this.initEvents();
            });
        }
    }, {
        key: "toggleAllDay",
        value: function toggleAllDay(evt) {
            var $this = this,
                input = $this._input,
                startDateTime = input.querySelectorAll('.venti-startdate--input')[1],
                endDateTime = input.querySelectorAll('.venti-enddate--input')[1];

            if (evt.target.checked) {
                input.classList.add('allDay');
                if (!$this._storedDates.state) {
                    $this._storedDates.startTime = startDateTime.value;
                    $this._storedDates.endTime = endDateTime.value;
                    startDateTime.value = "12:00 AM";
                    endDateTime.value = "12:59 PM";
                    $this._storedDates.state = true;
                } else {
                    startDateTime.value = "12:00 AM";
                    endDateTime.value = "12:59 PM";
                }
            } else {
                input.classList.remove('allDay');
                if ($this._storedDates.state) {
                    startDateTime.value = $this._storedDates.startTime;
                    endDateTime.value = $this._storedDates.endTime;
                }
            }
        }
    }, {
        key: "clearSummary",
        value: function clearSummary() {
            var $this = this,
                input = $this._input,
                modal = $this._modal,

            //[jQ]
            edit = $('.venti-eventRepeat-edit');

            input.querySelectorAll('.venti-summary')[0].value = "";
            input.querySelectorAll('.venti-rrule')[0].value = "";
            input.querySelectorAll('.venti-summary--human')[0].innerHTML = "";

            //[jQ] if visible
            if (edit.is(":visible")) {
                edit.hide();
            }

            modal.clearSummary();
        }
    }, {
        key: "startDateValidate",
        value: function startDateValidate(elm) {
            var $this = this,
                input = $this._input,
                startDateInput = elm,
                sdValue = startDateInput.value,
                endDateInput = input.querySelectorAll('.venti-enddate--input')[0],
                edValue = endDateInput.value,
                repeatChbx = input.querySelectorAll('.venti-eventRepeat')[0];

            /*
             * When StartDate focusout if no EndDate make same as StartDate
             * If EndDate is populate && repeat checkbox is checked always make EndDate = StartDate
             * Else make sure EndDate is >= StartDate
             */
            if (edValue === "") {
                endDateInput.value = sdValue;
            } else {
                var sD_Date = new Date(sdValue),
                    eD_Date = new Date(edValue);

                if (repeatChbx.checked) {
                    endDateInput.value = sdValue;
                } else {
                    if (sD_Date > eD_Date) {
                        endDateInput.value = sdValue;
                    }
                }
            }
            //console.log("START: " + sdValue);
            //console.log("END: " + edValue);
        }
    }, {
        key: "endDateFauxDisable",
        value: function endDateFauxDisable(elm) {
            var $this = this,
                input = $this._input;

            if (elm.checked) {
                input.classList.add('repeats');
            } else {
                input.classList.remove('repeats');
            }
        }
    }, {
        key: "endDateValidate",
        value: function endDateValidate(elm) {
            var $this = this,
                input = $this._input,
                endDateInput = elm,
                edValue = endDateInput.value,
                startDateInput = input.querySelectorAll('.venti-startdate--input')[0],
                sdValue = startDateInput.value,
                repeatChbx = input.querySelectorAll('.venti-eventRepeat')[0];

            if (sdValue !== "") {
                var sD_Date = new Date(sdValue),
                    eD_Date = new Date(edValue);
                if (eD_Date < sD_Date) {
                    endDateInput.value = sdValue;
                }
                if (repeatChbx.checked) {
                    endDateInput.value = sdValue;
                }
            }
        }
    }, {
        key: "input",
        get: function get() {
            return this._input;
        },
        set: function set(input) {
            this._input = input;
        }
    }, {
        key: "modal",
        get: function get() {
            return this._modal;
        },
        set: function set(modal) {
            this._modal = modal;
        }
    }, {
        key: "id",
        get: function get() {
            return this._id;
        },
        set: function set(id) {
            this._id = id;
        }
    }, {
        key: "storedDates",
        get: function get() {
            return this._storedDates;
        },
        set: function set(storedDates) {
            this._storedDates = storedDates;
        }
    }]);

    return VentiInput;
})();

var VentiSchedule = (function () {
    function VentiSchedule(options) {
        _classCallCheck(this, VentiSchedule);

        this._options = options;
        this._container = document.getElementById(options.id + "-venti-modal");
        this._input = document.getElementById(options.id + "-venti-field");
        this._freqSelect = this._container.querySelectorAll('.venti-frequency--select')[0];

        this.initEvents();
    }

    /*
     * Venti Modal
     */

    _createClass(VentiSchedule, [{
        key: "initEvents",
        value: function initEvents() {
            var $this = this,
                mdl = $this._container,
                dp = mdl.querySelectorAll('.venti-endson-datefield')[0],
                excludeDp = mdl.querySelectorAll('.venti-exclude-datefield')[0],
                includeDp = mdl.querySelectorAll('.venti-include-datefield')[0],
                ends = mdl.querySelectorAll('.venti_endson')[0];

            // Update scheduler state
            this.freqSelect.addEventListener('change', function () {
                var sel = this,
                    idx = sel.selectedIndex;
                $this.updateState(sel.options[idx].value);
            }, false);

            dp.addEventListener('focusout', function (evt) {
                //console.log(evt);
            }, false);

            // Action after exclude datepicker focusout event
            excludeDp.addEventListener('focusout', function (evt) {
                var thisDP = this;
                setTimeout(function () {
                    $this.setDateElement(thisDP, evt);
                }, 200);
            }, false);

            // Action after incude datepicker focusout event
            includeDp.addEventListener('focusout', function (evt) {
                var thisDP = this;
                setTimeout(function () {
                    $this.setDateElement(thisDP, evt);
                }, 200);
            }, false);

            $("#" + mdl.id).on('click', '.delete', function () {
                $(this).parent().fadeOut(function () {
                    $(this).remove();
                });
            });

            ends.addEventListener('click', function (evt) {
                var parent = this,
                    elm = evt.target,
                    textInputs = parent.querySelectorAll('input[type=text]');

                if (elm.className === "venti-endson__after" && elm.checked) {

                    textInputs[0].disabled = false;
                    textInputs[0].classList.remove('disabled');
                    textInputs[1].disabled = true;
                    textInputs[1].classList.add('disabled');
                    textInputs[1].value = "";
                } else if (elm.className === "venti-endson__date" && elm.checked) {

                    textInputs[1].disabled = false;
                    textInputs[1].classList.remove('disabled');
                    textInputs[0].disabled = true;
                    textInputs[0].classList.add('disabled');
                    textInputs[0].value = "";
                } else if (elm.className === "venti-endson__never" && elm.checked) {
                    for (var i = 0; i < textInputs.length; i++) {
                        textInputs[i].disabled = true;
                        textInputs[i].classList.add('disabled');
                        textInputs[i].value = "";
                    }
                }
            }, false);
        }
    }, {
        key: "clearSummary",
        value: function clearSummary() {
            var $this = this,
                mdl = $this._container;
            mdl.querySelectorAll('.venti-summary')[0].innerHTML = "";
        }
    }, {
        key: "updateState",
        value: function updateState(value) {
            var stateID = parseInt(value) + 1;
            this._container.dataset.state = stateID;
        }
    }, {
        key: "setStartOn",
        value: function setStartOn() {
            var $this = this,
                input = $this._input,
                mdl = $this._container,
                startOnInput = mdl.querySelectorAll('.venti-starts-on')[0],
                startDate = input.querySelectorAll('.venti-startdate--input')[0];

            if (startDate.value !== "") {
                startOnInput.value = startDate.value;
            }
        }

        //[jQ]
    }, {
        key: "setDateElement",
        value: function setDateElement(obj, evt) {
            var input = obj,
                value = input.value,
                tab = this.getNthParent(input, 4),
                elmList = $("#" + tab.id).find('.venti_elements'),
                tempName = tab.dataset.template,
                temp = $(tempName).text(),
                elm = $(temp);

            if (value.trim() !== "") {
                elm.find('input').attr('value', value);
                elm.find('.title').append(value);
                elmList.append(elm);
                input.value = "";
            }
        }
    }, {
        key: "getRuleString",
        value: function getRuleString(elm, callback) {
            var $this = this,
                mdl = $this._container,
                formData = $("#" + mdl.id).find(".venti_modal-form").serialize();

            Craft.postActionRequest('venti/ajax/getRuleString', formData, function (data) {
                if (typeof callback == 'function') {
                    callback(data);
                }
            });
        }
    }, {
        key: "getNthParent",
        value: function getNthParent(elm, idx) {
            var el = elm,
                i = idx;
            while (i-- && (el = el.parentNode));
            return el;
        }
    }, {
        key: "container",
        get: function get() {
            return this._container;
        },
        set: function set(container) {
            this._container = container;
        }
    }, {
        key: "overlay",
        get: function get() {
            return this._overlay;
        }
    }, {
        key: "options",
        get: function get() {
            return this._options;
        },
        set: function set(options) {
            this._options = options;
        }
    }, {
        key: "freqSelect",
        get: function get() {
            return this._freqSelect;
        },
        set: function set(freqsel) {
            this._freqSelect = freqsel;
        }
    }]);

    return VentiSchedule;
})();

var VentiModal = (function () {
    function VentiModal(options) {
        _classCallCheck(this, VentiModal);

        this._options = options;
        this._container = document.getElementById(options.id + "-venti-modal");
        this._input = document.getElementById(options.id + "-venti-field");
        this._overlay = this._container.parentNode;
        this._schedule = new VentiSchedule(options);

        this.onShow = new CustomEvent("onShow", {
            'bubbles': true,
            'cancelable': true
        });

        this.onHide = new CustomEvent("onHide", {
            'bubbles': true,
            'cancelable': true
        });

        this.initEvents();
    }

    _createClass(VentiModal, [{
        key: "show",
        value: function show() {
            $(this._overlay).fadeIn('fast');
            this._container.dispatchEvent(this.onShow);
        }
    }, {
        key: "hide",
        value: function hide() {
            $(this._overlay).fadeOut('fast');
            this._container.dispatchEvent(this.onHide);
        }
    }, {
        key: "initEvents",
        value: function initEvents() {
            var $this = this,
                mdl = $this._container,
                sch = $this._schedule,
                done = mdl.querySelector('button.submit'),
                cancel = mdl.querySelector('button.cancel'),
                tabContainer = mdl.querySelector('.venti_modal_tabs');

            // Modal done button click event
            done.addEventListener('click', function (evt) {
                evt.preventDefault();
                sch.getRuleString(mdl, function (data) {
                    $this.setInputValues(data);
                    $this.hide();
                });
            }, false);

            cancel.addEventListener('click', function (evt) {
                evt.preventDefault();
                $this.hide();
            }, false);

            $(tabContainer).on('click', 'a', function (evt) {
                evt.preventDefault();
                $this.toggleTab(evt.delegateTarget, $(this));
            });
        }

        //[jQ]
    }, {
        key: "toggleTab",
        value: function toggleTab(container, tab) {
            var container = $(container).find("ul"),
                id = tab.attr("href");

            container.find(".sel").removeClass('sel');
            tab.addClass('sel');
            $(id).siblings().hide();
            $(id).show();
        }

        /* 
         * Clears rRule & summary hidden input as well as text holders next
         * too repeat checkbox and event modal summary box.
         */

    }, {
        key: "clearSummary",
        value: function clearSummary() {
            var $this = this,
                mdl = $this._container;
            mdl.querySelectorAll('.venti-summary')[0].innerHTML = "";
        }
    }, {
        key: "setInputValues",
        value: function setInputValues(values) {
            var $this = this,
                input = $this._input,
                mdl = $this._container,
                rruleInput = input.querySelectorAll('.venti-rrule')[0],
                summaryInput = input.querySelectorAll('.venti-summary')[0],
                summaryOutput = input.querySelectorAll('.venti-summary--human')[0],
                mdlSummaryOutput = mdl.querySelectorAll('.venti-summary')[0],
                readable = values.readable ? values.readable.capitalize() : values.readable;

            rruleInput.value = values.rrule;
            summaryOutput.innerHTML = readable;
            summaryInput.value = readable;
            mdlSummaryOutput.innerHTML = readable;
        }
    }, {
        key: "container",
        get: function get() {
            return this._container;
        },
        set: function set(container) {
            this._container = container;
        }
    }, {
        key: "overlay",
        get: function get() {
            return this._overlay;
        },
        set: function set(overlay) {
            this._overlay = overlay;
        }
    }, {
        key: "options",
        get: function get() {
            return this._options;
        },
        set: function set(options) {
            this._options = options;
        }
    }, {
        key: "schedule",
        get: function get() {
            return this._schedule;
        },
        set: function set(schedule) {
            this._schedule = schedule;
        }
    }]);

    return VentiModal;
})();