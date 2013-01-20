(function(window, $) {
    "use strict";

    var titleOk = 'Speichern',
        titleCancel = 'Abbrechen',


        // CONSTANTS
        CONFIRM = 421,
        ALERT   = 422,
        FORM    = 423,
        WAIT    = 424,
        WARNING = 425,


        globalIdCounter = 0,
        getNextGlobalId = function() {
            globalIdCounter++;
            return 'FormWindowId' + globalIdCounter;
        };

    window.FormWindow = function(items, title, description, config) {

        var self = this,
            id = getNextGlobalId(),
            $div = $('<div id="' + id + '" class="FormWindow"></div>'),
            $darkroom = $('<div class="FormWindowDarkroom"></div>'),
            submitCallbacks = [],
            type = FORM,



            constructor = function() {
                var i, l, $footer, $b;

                if (!items) {
                    type = WAIT;
                } else if (typeof (items) == 'number') {
                    type = items;
                } else {
                    type = FORM;
                }

                // waiting window
                if (type == WAIT) {
                    title = 'Bitte warten';
                }

                if (!title) title = '';

                $div.append('<div class="FormWindowTitle">' + title + '</div>');

                if (description) {
                    $div.append('<div class="FormElement">' + description + '</div>');
                    $div.append('<hr/>');
                }


                if (type == WAIT) {
                    $div.append('<div class="FormWindowWait"></div>');
                    $div.append('<hr/>');
                } else {

                    if (type == FORM) {

                        for (i = 0, l = items.length; i < l; i++) {
                            if (Array.isArray(items[i])) {
                                items[i] = window.FormElement.apply({}, items[i]);
                            }
                            $div.append(items[i].getElement());

                            $div.append('<hr/>');
                        }

                    } else if (type == WARNING) {
                        $div.append('<div class="FormWindowWarning"></div>');
                        $div.append('<hr/>');
                    }


                    $footer = $('<div class="FormWindowFooter"></div>');
                    $div.append($footer);

                    if (config && config.titleOk) {
                        titleOk = config.titleOk;
                    }

                    $b = $('<button>' + titleOk + '</button>');

                    if (type == ALERT || type == CONFIRM || type == WARNING) {
                        $b.text('Ok');
                        $b.click(function() {
                            self.close();
                        });
                    }

                    $b.click(function() {
                        $.each(submitCallbacks, function(i, elem) {
                            elem.apply(self, [self.getData()]);
                        });
                    });
                    $footer.append($b);

                    if (type != ALERT && type != WARNING) {
                        $b = $('<button>' + titleCancel + '</button>');
                        $b.click(function() {
                            self.close();
                        });
                        $footer.append($b);
                    }
                }
            };


        this.getData = function() {
            var i, l,
                data = {};

            for (i = 0, l = items.length; i < l; i++) {
                data[items[i].getKey()] = items[i].getValue();
            }

            return data;
        };

        this.getElement = function() {
            return $div;
        };

        this.open = function() {
            var top, left;

            $('body').append($darkroom).append($div);

            left = (window.innerWidth/2 - parseInt($div.css('width'))/2);
            top = (window.innerHeight/2 - parseInt($div.css('height'))/2);

            if (left < 10) left = 10;
            if (top < 10) top = 10;

            top += parseInt($(document).scrollTop());

            $div.css('top', top).css('left', left);



            if (items && items.length > 0) {
                items[0].focus();
            }

            return this;
        };

        this.submit = function( cb ){
            if (cb) {
                submitCallbacks.push(cb);
            }
            return this;
        };

        this.close = function() {
            $div.remove();
            $darkroom.remove();

            return this;
        };

        // call constructor
        constructor.apply(this, arguments);
    };



    window.FormWindow.create = function(items, title, description, config) {
        return new window.FormWindow(items, title, description, config);
    };

    window.FormWindow.confirm = function(title, text, callback) {
        return window.FormWindow
            .create(CONFIRM, title, text)
            .submit(callback)
            .open();
    };

    window.FormWindow.alert = function(title, text, callback) {
        return window.FormWindow
            .create(ALERT, title, text)
            .submit(callback)
            .open();
    };

    window.FormWindow.warning = function(title, text, callback) {
        return window.FormWindow
            .create(WARNING, title, text)
            .submit(callback)
            .open();
    };



    window.FormElement = function( type, key, label, value, description, config ) {
        var id = getNextGlobalId(),
            element = new window['Form'+type](value, id, key, config),

            $div = $('<div class="FormElement"></div>'),
            constructor = function() {

                if (label) {
                    $div.append('<label class="FormElementLabel" for="' + id + '">' + label + '</label>');
                }

                $div.append(element.getElement());


                if (description) {
                    $div.append('<div class="FormElementDescription">' + description + '</div>');
                }
            };

        this.getElement = function() {
            return $div;
        };

        this.getValue = function() {
            return element.getValue();
        };

        this.getKey = function() {
            return key;
        };
        this.focus = function() {
            element.focus();
        };

        constructor.apply(this, arguments);
        return this;
    };


    window.FormText = function( value, id, key, config ) {
        var $input = $('<input type="text" id="' + id + '" value="' + value + '" name="' + key + '"/>'),
            $div = $('<div class="FormElementText"></div>').append($input);
        this.getElement = function() {
            return $div;
        };
        this.focus = function() {
            $input.focus();
        };
        this.getValue = function() {
            return $input.val();
        };
    };


    window.FormLabel = function( value, id, key, config ) {
        var $div = $('<div class="FormElementText">' + value + '</div>');
        this.getElement = function() {
            return $div;
        };
        this.focus = function() {
        };
        this.getValue = function() {
            return value;
        };
    };


    window.FormTextarea = function( value, id, key, config ) {
        var $input = $('<textarea id="' + id + '" name="' + key + '">' + value + '</textarea>'),
            $div = $('<div class="FormElementText"></div>').append($input);
        this.getElement = function() {
            return $div;
        };
        this.focus = function() {
            $input.focus();
        };
        this.getValue = function() {
            return $input.val();
        };
    };


    window.FormCheckbox = function( value, id, key, config ) {
        var $input = $('<input type="checkbox" id="' + id + '" value="checked" name="' + key + '"/>'),
            $label,
            $div = $('<div class="FormElementText"></div>').append($input);

        if (value) {
            $input.attr('checked', 'checked');
        }

        if (config && config.label) {
            $div.append($('<label for="' + id + '">' + config.label + '</label>'));
        }

        this.getElement = function() {
            return $div;
        };
        this.focus = function() {
            $input.focus();
        };
        this.getValue = function() {
            return $input.attr('checked') == 'checked';
        };
    };


    window.FormRadio = function( value, id, key, config ) {
        var inputs = [],
            $div = $('<div class="FormElementRadio"></div>'),

            constructor = function() {
                var i, l, $help,
                    options = [],
                    val, dis, id2;
                if (config.options) options = config.options;

                for (i = 0, l = options.length; i < l; i++) {
                    id2 = getNextGlobalId();

                    if (typeof options[i] == 'object') {
                        val = options[i].value;
                        dis = options[i].display;
                    } else {
                        val = dis = options[i];
                    }

                    $help = $('<input type="radio" name="' + key + '" id="' + id2 + '" value="' + val + '"/>');
                    if (val == value) {
                        $help.attr('checked', 'checked');
                    }
                    inputs.push($help);
                    $div.append($help).append('<label for="' + id2 + '">' + dis + '</label>');

                    if (i != l-1) {
                        $div.append('<br/>');
                    }
                }
            };

        this.getElement = function() {
            return $div;
        };

        this.focus = function() {
            $div.find('input:first').focus();
        };
        this.getValue = function() {
            var i, l;
            for ( i = 0, l = inputs.length; i < l; i++) {
                if (inputs[i].attr('checked')) {
                    return inputs[i].val();
                }
            }

            return false;
        };

        constructor.apply(this, []);
    };


    window.FormSelect = function( value, id, key, config ) {
        var $select = $('<select name="' + key + '" id="' + id + '"></select>'),
            $div = $('<div class="FormElementSelect"></div>').append($select),

            constructor = function() {
                var i, l, $help,
                    options = [],
                    val, dis;
                if (config.options) options = config.options;

                for (i = 0, l = options.length; i < l; i++) {
                    if (typeof options[i] == 'object') {
                        val = options[i].value;
                        dis = options[i].display;
                    } else {
                        val = dis = options[i];
                    }

                    $help = $('<option value="' + val + '">' + dis + '</option>');
                    if (val == value) {
                        $help.attr('selected', 'selected');
                    }
                    $select.append($help);
                }
            };

        this.getElement = function() {
            return $div;
        };

        this.focus = function() {
            $select.focus();
        };
        this.getValue = function() {
            return $select.val();
        };

        constructor.apply(this, []);
    };


    window.FormDate = function( value, id, key, config ) {
        var $day = $('<select name="' + key + '_day" id="' + id + '" style="width:50px"></select>'),
            $month = $('<select name="' + key + '_month" style="width:50px"></select>'),
            $year = $('<select name="' + key + '_year" style="width:100px"></select>'),
            $div = $('<div class="FormElementText"></div>').append($day).append($month).append($year),
            constructor = function() {
                var i, day, month, year, help,
                    res = value.match(/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/);

                if (res) {
                    day = res[3];
                    month = res[2];
                    year = res[1];
                }


                for (i = 1; i < 32; i++) {
                    help = $('<option>' + i + '</option>');
                    if (day && parseInt(day, 10) == i) {
                        help.attr('selected', 'selected');
                    }
                    $day.append(help);
                }
                for (i = 1; i < 13; i++) {
                    help = $('<option>' + i + '</option>');
                    if (month && parseInt(month, 10) == i) {
                        help.attr('selected', 'selected');
                    }
                    $month.append(help);
                }
                for (i = 1990; i < (new Date()).getFullYear()+5; i++) {
                    help = $('<option>' + i + '</option>');
                    if (year && parseInt(year, 10) == i) {
                        help.attr('selected', 'selected');
                    }
                    $year.append(help);
                }
            };

        this.getElement = function() {
            return $div;
        };
        this.focus = function() {
            $day.focus();
        };
        this.getValue = function() {
            var day = String($day.val()),
                month = String($month.val()),
                year = String($year.val());

            if (day.length == 1) day = '0' + day;
            if (month.length == 1) month = '0' + month;

            return year + '-' + month + '-' + day;
        };

        constructor.apply(this, []);
    };
})(window, $);
