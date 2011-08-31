var myAjax = new sack();
var warn_color = 'darkorange';

function checkSource() {
    var source_input = $('bibdataform__source');
    var status_div = $('bibdataform__status');
    var bibsource = source_input.value.toLowerCase();
    var doi_pos = bibsource.indexOf('doi');
    var abstract_pos = bibsource.indexOf('abstract');
    status_div.innerHTML = 'BibTeX source seems ok!';
    status_div.style.color = 'green';
    if(doi_pos == -1) {
	status_div.style.color = warn_color;
	status_div.innerHTML = 'BibTeX source does not seem to contain the doi!';
    }
    if(abstract_pos == -1) {
	if(status_div.style.color == warn_color) {
	    status_div.innerHTML = status_div.innerHTML.substr(0, status_div.innerHTML.length-1)
		+ ', nor the abstract!';
	} else {
	    status_div.style.color = warn_color;
	    status_div.innerHTML = 'BibTeX source does not seem to contain the abstract!';
	}
    }
}

function checkCompleted() {
    var status_div = $('bibdataform__status');
    var source_input = $('bibdataform__source');
    var pageid_input = $('bibdataform__pageid');
    status_div.style.color = 'green';
    status_div.innerHTML = 'BibTeX seems ok!';
    eval('var result = ' + myAjax.response);
    if(result.msg.length > 0) {
	status_div.style.color = warn_color;
	status_div.innerHTML = result.msg;
	return;
    }
    pageid_input.value = result.pageid;
    if(!result.has_doi) {
	status_div.style.color = warn_color;
	status_div.innerHTML = 'BibTeX source does not seem to contain the doi!';
    }
    if(!result.has_abstract) {
	if(status_div.style.color == warn_color) {
	    status_div.innerHTML = status_div.innerHTML.substr(0, status_div.innerHTML.length-1)
		+ ', nor the abstract!';
	} else {
	    status_div.style.color = warn_color;
	    status_div.innerHTML = 'BibTeX source does not seem to contain the abstract!';
	}
    }
}

function checkSourceAjax() {
    var status_div = $('bibdataform__status');
    var source_input = $('bibdataform__source');
    var targetns = document.getElementsByName('targetns')[0];

    status_div.innerHTML = 'Please wait while parsing BibTeX...';
    myAjax.requestFile = DOKU_BASE + 'lib/plugins/bibdata/ajax/checkBibtex.php';
    myAjax.method = "POST";
    myAjax.setVar('source', encodeURIComponent(source_input.value));
    myAjax.setVar('targetns', encodeURIComponent(targetns.value));
    console.log(myAjax.URLString);
    myAjax.onCompletion = checkCompleted;
    myAjax.runAJAX();
}

function installSourceCheck()
{
    var source_input = $('bibdataform__source');
    if(source_input !== null)
    {
        addEvent(source_input, 'change', checkSourceAjax);
        //addEvent(source_input, 'keyup', checkSource);
    }
}

addInitEvent(installSourceCheck);