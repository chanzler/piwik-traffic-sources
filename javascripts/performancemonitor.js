/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * jQueryUI widget for Live visitors widget
 */
$(function() {
    var refreshWidget = function (element, refreshAfterXSecs) {
        // if the widget has been removed from the DOM, abort
        if ($(element).parent().length == 0) {
            return;
        }
        var lastMinutes = $(element).find('.dynameter').attr('data-last-minutes') || 30;

        var ajaxRequest = new ajaxHelper();
        ajaxRequest.addParams({
            module: 'API',
            method: 'ConcurrentsByTrafficSource.getVisitorCounter',
            format: 'json',
            lastMinutes: lastMinutes
        }, 'get');
        ajaxRequest.setFormat('json');
        ajaxRequest.setCallback(function (data) {
            data = data[0];

            var visitors = data['visits'];
            var maxvisitors = data['maxvisits'];
            $(element).find('.dynameter').removeClass('dm-wrapperDiv');
            var $myMeter = null;
            $myMeter = $(element).find('.dynameter').dynameter({
                label: '',
                min: 0,
                max: maxvisitors,
                regions: {
                  error: 0,
                  warn: maxvisitors/5,
                  normal: maxvisitors/2
                },
                value: visitors,
                unit: 'Performance Index'
            });
            $myMeter.changeValue(visitors, maxvisitors, 0);
            myOdometer = new Odometer({ el: $('.odometer')[0], theme: 'default', value: visitors });
            myOdometer.render()
            myOdometer.update(visitors);
            $(element).find('.legend-max', element).text(maxvisitors);
            // schedule another request
            setTimeout(function () { refreshWidget(element, refreshAfterXSecs); }, refreshAfterXSecs * 1000);
        });
        ajaxRequest.send(true);
    };

    var exports = require("piwik/ConcurrentsByTrafficSource");
    exports.initSimpleRealtimeVisitorWidget = function (refreshInterval) {
        var ajaxRequest = new ajaxHelper();
        ajaxRequest.addParams({
            module: 'API',
            method: 'ConcurrentsByTrafficSource.getVisitorCounter',
            format: 'json',
            lastMinutes: 30
        }, 'get');
        ajaxRequest.setFormat('json');
        ajaxRequest.setCallback(function (data) {
            data = data[0];

            var visitors = data['visits'];
            var maxvisitors = data['maxvisits'];
            $('.dynameter').dynameter({
                label: '',
                min: 0,
                max: maxvisitors,
                regions: {
                  error: 0,
                  warn: maxvisitors/5,
                  normal: maxvisitors/2
                },
                value: visitors,
                unit: 'Performance Index'
            });
            myOdometer = new Odometer({ el: $('.odometer')[0], theme: 'default', value: visitors });
            myOdometer.render()
            $('.dynameter-widget').each(function() {
                var $this = $(this),
                   refreshAfterXSecs = refreshInterval;
                setTimeout(function() { refreshWidget($this, refreshAfterXSecs ); }, refreshAfterXSecs * 1000);
            });
        });
        ajaxRequest.send(true);
     };
});

