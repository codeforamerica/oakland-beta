/*
 * Venti Modal
 */
class VentiModal {
    constructor (options) {
        this._options = options;
        this._container = document.getElementById(options.id + "-venti-modal");
        this._input = document.getElementById(options.id + "-venti-field");
        this._overlay = this._container.parentNode;
        this._schedule = new VentiSchedule(options);

        this.onShow = new CustomEvent("onShow",{
            'bubbles': true,
            'cancelable': true
        });

        this.onHide = new CustomEvent("onHide",{
            'bubbles': true,
            'cancelable': true
        });

        this.initEvents();
    }

    get container () {
        return this._container;
    }

    get overlay () {
        return this._overlay;
    }

    get options () {
        return this._options;
    }

    get schedule () {
        return this._schedule;
    }

    set container (container) {
        this._container = container;
    }

    set overlay (overlay) {
        this._overlay = overlay;
    }

    set options (options) {
        this._options = options;
    }

    set schedule (schedule) {
        this._schedule = schedule;
    }

    show () {
        $(this._overlay).fadeIn('fast');
        this._container.dispatchEvent(this.onShow);
    }

    hide () {
        $(this._overlay).fadeOut('fast');
        this._container.dispatchEvent(this.onHide);
    }


    initEvents () {
        var $this = this,
            mdl = $this._container,
            sch = $this._schedule,
            done = mdl.querySelector('button.submit'),
            cancel = mdl.querySelector('button.cancel'),
            tabContainer = mdl.querySelector('.venti_modal_tabs');

        // Modal done button click event
        done.addEventListener('click', function (evt) {
            evt.preventDefault();
            sch.getRuleString(mdl, function(data) {
                $this.setInputValues(data);
                $this.hide();
            });
            
        }, false);

        cancel.addEventListener('click', function (evt){
            evt.preventDefault();
            $this.hide();
        }, false);

        $(tabContainer).on('click', 'a', function (evt) {
            evt.preventDefault();
            $this.toggleTab( 
                evt.delegateTarget, 
                $(this)
            );
        });
    }

    //[jQ]
    toggleTab (container, tab) {
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
    
    clearSummary () {
        var $this = this,
            mdl = $this._container;
        mdl.querySelectorAll('.venti-summary')[0].innerHTML = "";
    }

    setInputValues (values) {
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
}