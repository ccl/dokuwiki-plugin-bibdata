var myAjax = new sack();


function writeResult() {
	var publist = $('__publist');
	if(myAjax.response){
	    var list = null;
	    eval("list = " + myAjax.response);
		var html = "<ul>";
	    for(var i in list) {
			html += '<li class="level1"><div class="li">' + list[i] + '</div></li>';
		}
		html += "</ul>";
		publist.innerHTML = html; 
	}
}	

function updateList() {
    var publist = $('__publist');
    publist.innerHTML = "Loading...";
	myAjax.setVar("test", 1);
    myAjax.requestFile = DOKU_BASE+'lib/plugins/bibdata/ajax.php';
    myAjax.method = "POST";
    myAjax.onCompletion = writeResult;
    myAjax.runAJAX();
}
 
function pubListEvent()
{
    var publist = $('__publist');
    if(publist !== null)
    {
        addEvent(publist, 'click', updateList);
    }
}
 
addInitEvent(pubListEvent);