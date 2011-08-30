function checkSource() {
    var warn_color = 'darkorange';
    var source_input = document.getElementById('bibdataform__source');
    var status_div = document.getElementById('bibdataform__status');
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

function installSourceCheck()
{
    var source_input = document.getElementById('bibdataform__source');
    if(source_input !== null)
    {
        addEvent(source_input, 'change', checkSource);
        addEvent(source_input, 'keyup', checkSource);
    }
}

addInitEvent(installSourceCheck);