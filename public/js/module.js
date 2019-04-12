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
                $(".flatpickr").flatpickr({
                    enableTime: true,
                    dateFormat: 'Y-m-d H:i',
                    appendTo: $container[0]
                });
            });
        }
    };

    Icinga.availableModules.reporting = Reporting;

}(Icinga));
