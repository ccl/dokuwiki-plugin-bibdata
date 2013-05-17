var bibdata_warn_color = 'darkorange';

function checkSourceCompleted(response) {
    var status_div = jQuery('#bibdataform__status');
    var source_input = jQuery('#bibdataform__source');
    var pageid_input = jQuery('#bibdataform__pageid');
    status_div.css('color', 'green');
    status_div.html('BibTeX seems ok!');
    eval('var result = ' + response);
    if(result.msg.length > 0) {
        status_div.css('color', bibdata_warn_color);
        status_div.html(result.msg);
        return;
    }
    pageid_input.val(result.pageid);
    if(!result.has_doi) {
        status_div.css('color', bibdata_warn_color);
        status_div.html('BibTeX source does not seem to contain the doi!');
    }
    if(!result.has_abstract) {
        if(!result.has_doi) {
            status_div.html(status_div.html().substr(0, status_div.html().length-1)
			    + ', nor the abstract!');
        } else {
            status_div.css('color', bibdata_warn_color);
            status_div.html('BibTeX source does not seem to contain the abstract!');
        }
    }
}

function checkPageIdCompleted(response) {
    var pageid_input = jQuery('#bibdataform__pageid');
    if(response == "true") {
        pageid_input.css('color', 'green');
    } else {
        pageid_input.css('color', 'red');
    }
}

function checkSourceAjax() {
    var status_div = jQuery('#bibdataform__status');
    var source_input = jQuery('#bibdataform__source');
    var targetns = jQuery('[name="targetns"]');

    status_div.html('Please wait while parsing BibTeX...');
    jQuery.post(DOKU_BASE + 'lib/plugins/bibdata/ajax/checkBibtex.php',
		{source: encodeURIComponent(source_input.val()),
		 targetns: encodeURIComponent(targetns.val())},
		checkSourceCompleted);
}

function checkPageIdAjax() {
    var pageid_input = jQuery('#bibdataform__pageid');
    var targetns = jQuery('[name="targetns"]');

    jQuery.post(DOKU_BASE + 'lib/plugins/bibdata/ajax/checkPageId.php',
		{pageid: encodeURIComponent(pageid_input.val()),
		 targetns: encodeURIComponent(targetns.val())},
		checkPageIdCompleted);
}

function installFormCheck()
{
    var source_input = jQuery('#bibdataform__source');
    var pageid_input = jQuery('#bibdataform__pageid');
    if(source_input.length > 0) {
        jQuery(source_input).change(checkSourceAjax);
    }
    if(pageid_input.length > 0) {
        jQuery(pageid_input).keyup(checkPageIdAjax);
    }
}

function redirectOnSuccess() {
    var success_div = jQuery('#bibdataform__success');
    if(success_div.length > 0) {
        var dest = success_div.attr('href');
        setTimeout(function() {window.location =  dest;}, 3000);
    }
}

jQuery(installFormCheck);
jQuery(redirectOnSuccess);