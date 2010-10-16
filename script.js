var myAjax = new sack();
var data = null;

function parseTemplate(data) {
	var out = '<li class="level1"><div class="li">';
	out += '<strong><a href="/gap/optics/wiki/' + data['id'] + '" class="wikilink1" title="' + data['id'] + '">';
	out += data['title'] + '</a></strong>, <br />';
	out += data['authors'] + ', <em>' + data['journal'] + ' ' + data['volume'] + ', ' + data['page'] + ' (' + data['year'] + ')';
	out += '</em></div></li>';
	return out;
}

function showList(start) {
	var publist = $('__publist');
	var html = "<ul>";
	for(var i=start; i<data.length && i < start+20; i++) {
		html += parseTemplate(data[i]);
	}
	html += "</ul>";
	if(start > 0 || start+20 < data.length) {
		html += '<div class="prevnext">';
		if(start > 0) html += '<a onclick="showList(' + (start-20) + ');" title="Previous" class="previous">&larr; Previous Page</a>';
		html += '&nbsp;';
		if(start + 20 < data.length) html += '<a onclick="showList(' + (start+20) + ');" title="Next" class="next">Next Page &rarr;</a>';		
		html += '</div>';
	}
	publist.innerHTML = html;	
}

function loadCompleted() {
	var publist = $('__publist');
	if(myAjax.response){
	    publist.innerHTML = 'Building list...';
	    eval("data = " + myAjax.response);
		showList(0);
	}
}	

function loadList() {
    var publist = $('__publist');
    publist.innerHTML = "Loading list of publications...";
	myAjax.setVar("test", 1);
    myAjax.requestFile = DOKU_BASE+'lib/plugins/bibdata/ajax.php';
    myAjax.method = "POST";
    myAjax.onCompletion = loadCompleted;
    myAjax.runAJAX();
}
 
function pubListEvent()
{
    var publist = $('__publist');
    if(publist !== null)
    {
        loadList();
    }
}
 
addInitEvent(pubListEvent);