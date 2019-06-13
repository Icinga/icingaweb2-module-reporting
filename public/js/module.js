// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

;(function (Icinga) {

    'use strict';

    var Reporting = function(module) {
        this.module = module;

        this.initialize();
    };

    Reporting.prototype.initialize = function () {
        if (typeof $().flatpickr === 'function') {
            this.module.on('rendered', function (event) {
                var $container = $('<div>');
                event.target.insertAdjacentElement('beforeend', $container[0]);
                $('.reporting-flatpickr').each(function() {
                    var $el = $(this);
                    var data = $el.find('input').data();
                    var options = {
                        appendTo: $container[0],
                        dateFormat: 'Y-m-d',
                        wrap: true
                    };

                    if (data.hasOwnProperty('enableTime')) {
                        options.enableTime = true;
                        options.dateFormat += ' H:i';
                        options.defaultHour = data.defaultHour || 12;
                        options.defaultMinute = data.defaultMinute || 0;
                    }

                    if (data.hasOwnProperty('enableSeconds')) {
                        options.enableSeconds = true;
                        options.dateFormat += ':S';
                        options.defaultSeconds = data.defaultSeconds || 0;
                    }

                    if (data.hasOwnProperty('allowInput')) {
                        options.allowInput = true;
                        options.clickOpens = false;
                        options.parseDate = function() {
                            // Accept any date string but don't update the value of the input
                            // If the dev console is open this will issue a warning.
                            return true;
                        };
                    }

                    console.log(options);

                    $el.flatpickr(options);
                });
            });
        }
    };

    Icinga.availableModules.reporting = Reporting;

}(Icinga));
