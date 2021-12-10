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
                $('[data-use-flatpickr-fallback]').each(function() {
                    var options = {
                        appendTo: $container[0],
                        dateFormat: 'Y-m-d H:i:S',
                        enableTime: true,
                        enableSeconds: true
                    };

                    for (name in this.dataset) {
                        if (name.length > 9 && name.substr(0, 9) === 'flatpickr') {
                            var value = this.dataset[name];
                            if (value === '') {
                                value = true;
                            }

                            options[name.charAt(9).toLowerCase() + name.substr(10)] = value;
                        }
                    }

                    var element = this;
                    if (!! options.wrap) {
                        element = this.parentNode;
                    }

                    $(element).flatpickr(options);
                });
            });
        }
    };

    Icinga.availableModules.reporting = Reporting;

}(Icinga));
