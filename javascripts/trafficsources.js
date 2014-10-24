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
    var refreshTrafficSourcesWidget = function (element, refreshAfterXSecs) {
        // if the widget has been removed from the DOM, abort
        if ($(element).parent().length == 0) {
            return;
        }
        var lastMinutes = $(element).find('.dynameter').attr('data-last-minutes') || 30;

        var ajaxRequest = new ajaxHelper();
        ajaxRequest.addParams({
            module: 'API',
            method: 'TrafficSources.getTrafficSources',
            format: 'json',
            lastMinutes: lastMinutes
        }, 'get');
        ajaxRequest.setFormat('json');
        ajaxRequest.setCallback(function (data) {
        	data.sort(function(a, b){
        	    return b.value - a.value;
        	});
        	$.each( data, function( index, value ){
            	//$('#chart div').each(function() {
                	var pc = value['percentage'];
	        		pc = pc > 100 ? 100 : pc;
	        		$('#chart').find("div[index="+index+"]").children('.percent').html(pc+'%');
	        		var ww = $('#chart').find("div[index="+index+"]").width();
	        		var len = parseInt(ww, 10) * parseInt(pc, 10) / 100;
	        		$('#chart').find("div[index="+index+"]").children('.bar').animate({ 'width' : len+'px' }, 1500);
            	//});
        	});
            // schedule another request
            setTimeout(function () { refreshTrafficSourcesWidget(element, refreshAfterXSecs); }, refreshAfterXSecs * 1000);
        });
        ajaxRequest.send(true);
    };

    var exports = require("piwik/TrafficSources");
    exports.initSimpleRealtimeTrafficSourcesWidget = function (refreshInterval) {
        var ajaxRequest = new ajaxHelper();
        ajaxRequest.addParams({
            module: 'API',
            method: 'TrafficSources.getTrafficSources',
            format: 'json',
            lastMinutes: 30
        }, 'get');
        ajaxRequest.setFormat('json');
        ajaxRequest.setCallback(function (data) {
        	data.sort(function(a, b){
        	    return b.value - a.value;
        	});
        	$.each( data, function( index, value ){
                	var pc = value['percentage'];
	        		pc = pc > 100 ? 100 : pc;
	        		$('#chart').find("div[index="+index+"]").children('.percent').html(pc+'%');
	        		$('#chart').find("div[index="+index+"]").children('.title').text(value['name']);
	        		var ww = $('#chart').find("div[index="+index+"]").width();
	        		var len = parseInt(ww, 10) * parseInt(pc, 10) / 100;
	        		$('#chart').find("div[index="+index+"]").children('.bar').animate({ 'width' : len+'px' }, 1500);
        	});
            $('#chart').each(function() {
                var $this = $(this),
                   refreshAfterXSecs = refreshInterval;
                setTimeout(function() { refreshTrafficSourcesWidget($this, refreshAfterXSecs ); }, refreshAfterXSecs * 1000);
            });
        });
        ajaxRequest.send(true);
     };
});

