/**
 * Venti Input Class
 */

class VentiInput {
    constructor (options) {

        this._id = options.id;
        this._input  = document.getElementById(options.id + "-venti-field");
        this._storedDates = {
            state: false,
            startTime: '',
            endTime: ''
        };
        
        this.loadModal( options );
    }


    get input () {
        return this._input;
    }

    get modal () {
        return this._modal;
    }

    get id () {
        return this._id;
    }

    get storedDates () {
        return this._storedDates;
    }

    set input (input) {
        this._input = input;
    }

    set modal (modal) {
        this._modal = modal;
    }

    set id (id) {
        this._id = id;
    }

    set storedDates (storedDates) {
        this._storedDates = storedDates;
    }

    initEvents () {
        var $this = this,
            input = $this._input,
            modal = $this._modal,
            startDateInput = input.querySelectorAll('.venti-startdate--input')[0],
            endDateInput = input.querySelectorAll('.venti-enddate--input')[0],
            rrule = input.querySelectorAll('.venti-rrule')[0];

        input.addEventListener('click', function (evt) {
            var elm = evt.target;
            if ( elm.className === 'venti-eventRepeat-edit' ) {
                modal.show();
            } else if ( elm.className === 'venti-allday--input' ) {
                $this.toggleAllDay(evt);
            } else if ( elm.className === 'venti-eventRepeat' ) {
                if ( elm.checked ) {
                    $this.endDateValidate(endDateInput);
                    modal.show();
                }
                $this.endDateFauxDisable(elm);
                if ( rrule.value !== "" ) {
                    $this.clearSummary();
                }
            }
        },false);

        startDateInput.addEventListener('focusout', function (evt) {
            var thisSDI = this;
            setTimeout(function(){
                $this.startDateValidate(thisSDI);
            },200);
        },false);

        endDateInput.addEventListener('focusout', function (evt) {
            var thisEDI = this;
            setTimeout(function(){
                $this.endDateValidate(thisEDI);
            },200);
        },false);

        document.body.addEventListener('onHide', function (evt) {
            if(evt.srcElement.id === modal.container.id){
                var rrule = input.querySelectorAll('.venti-rrule')[0],
                    repeatCbBtn = input.querySelectorAll('.venti-eventRepeat')[0];
                if(rrule.value === ""){
                    repeatCbBtn.checked = false;
                    $this.endDateFauxDisable(repeatCbBtn);
                }
            }
        },false);

        document.body.addEventListener('onShow', function (evt) {
            if(evt.srcElement.id === modal.container.id){
                modal.schedule.setStartOn();
            }
        },false);
    }

    loadModal ( options ) {
        var $this = this;
        Craft.postActionRequest(
            'venti/ajax/modal', 
            {
                name: options.id,
                rrule: options.values !== null ? options.values.rRule : "",
                locale: options.locale
            }, 
            function (data) {
                // Append modal content
                //[jQ]
                $('body').append(data);
                $this._modal = new VentiModal(options);
                $this.initEvents();
        } );
    }


    toggleAllDay (evt) {
        var $this = this,
            input = $this._input,
            startDateTime = input.querySelectorAll('.venti-startdate--input')[1],
            endDateTime = input.querySelectorAll('.venti-enddate--input')[1];

        if(evt.target.checked){
            input.classList.add('allDay');
            if (!$this._storedDates.state) {
                $this._storedDates.startTime = startDateTime.value;
                $this._storedDates.endTime = endDateTime.value;
                startDateTime.value = "12:00 AM";
                endDateTime.value = "12:59 PM";
                $this._storedDates.state = true;
            }else{
                startDateTime.value = "12:00 AM";
                endDateTime.value = "12:59 PM";
            }
        }else{
            input.classList.remove('allDay');
            if ($this._storedDates.state) {
                startDateTime.value = $this._storedDates.startTime;
                endDateTime.value = $this._storedDates.endTime;
            }
        }
    }

    clearSummary () {
        var $this = this,
            input = $this._input,
            modal = $this._modal,
            //[jQ]
            edit = $('.venti-eventRepeat-edit');

        input.querySelectorAll('.venti-summary')[0].value = "";
        input.querySelectorAll('.venti-rrule')[0].value = "";
        input.querySelectorAll('.venti-summary--human')[0].innerHTML = "";

        //[jQ] if visible
        if ( edit.is(":visible") ) {
            edit.hide();
        }

        modal.clearSummary();
    }

    startDateValidate (elm) {
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
        if(edValue === "") {
            endDateInput.value = sdValue;
        } else {
            var sD_Date = new Date(sdValue),
                eD_Date = new Date(edValue);

            if(repeatChbx.checked){
                endDateInput.value = sdValue;
            }else{
                if(sD_Date > eD_Date){
                    endDateInput.value = sdValue;
                }
            }
        }
        //console.log("START: " + sdValue);
        //console.log("END: " + edValue);
    }

    endDateFauxDisable (elm) {
        var $this = this,
            input = $this._input;

        if(elm.checked){
            input.classList.add('repeats');
        }else{
            input.classList.remove('repeats');
        }
    }

    endDateValidate (elm) {
        var $this = this,
            input = $this._input,
            endDateInput = elm,
            edValue = endDateInput.value,
            startDateInput = input.querySelectorAll('.venti-startdate--input')[0],
            sdValue = startDateInput.value,
            repeatChbx = input.querySelectorAll('.venti-eventRepeat')[0];

        if(sdValue !== ""){
            var sD_Date = new Date(sdValue),
                eD_Date = new Date(edValue);
            if(eD_Date < sD_Date){
                endDateInput.value = sdValue;
            }
            if(repeatChbx.checked){
                endDateInput.value = sdValue;
            }
        }
    }

}