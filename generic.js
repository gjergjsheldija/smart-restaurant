function doit(tID, isOver)
{
  var theRow = document.getElementById(tID)

 theRow.style.backgroundColor = (isOver) ? '#0000ff' : '#ffffff';
}


function changeto(highlightcolor){
source=event.srcElement
if (source.tagName=="TD"||source.tagName=="TABLE")
return
while(source.tagName!="TR")
source=source.parentElement
if (source.style.backgroundColor!=highlightcolor&&source.id!="ignore")
source.style.backgroundColor=highlightcolor
}

function changeback(originalcolor){
if (event.fromElement.contains(event.toElement)||source.contains(event.toElement)||source.id=="ignore")
return
if (event.toElement!=source)
source.style.backgroundColor=originalcolor
}

function setPointer(theRow, theRowNum, theAction, theDefaultColor, thePointerColor, theMarkColor)
{
    var theCells = null;

    // 1. Pointer and mark feature are disabled or the browser can't get the
    //    row -> exits
    if ((thePointerColor == '' && theMarkColor == '')
        || typeof(theRow.style) == 'undefined') {
        return false;
    }

    // 2. Gets the current row and exits if the browser can't get it
    if (typeof(document.getElementsByTagName) != 'undefined') {
        theCells = theRow.getElementsByTagName('td');
    }
    else if (typeof(theRow.cells) != 'undefined') {
        theCells = theRow.cells;
    }
    else {
        return false;
    }

    // 3. Gets the current color...
    var rowCellsCnt  = theCells.length;
    var domDetect    = null;
    var currentColor = null;
    var newColor     = null;
    // 3.1 ... with DOM compatible browsers except Opera that does not return
    //         valid values with "getAttribute"
    if (typeof(window.opera) == 'undefined'
        && typeof(theCells[0].getAttribute) != 'undefined') {
        currentColor = theCells[0].getAttribute('bgcolor');
        domDetect    = true;
    }
    // 3.2 ... with other browsers
    else {
        currentColor = theCells[0].style.backgroundColor;
        domDetect    = false;
    } // end 3

    // 3.3 ... Opera changes colors set via HTML to rgb(r,g,b) format so fix it
    if (currentColor.indexOf("rgb") >= 0) 
    {
        var rgbStr = currentColor.slice(currentColor.indexOf('(') + 1,
                                     currentColor.indexOf(')'));
        var rgbValues = rgbStr.split(",");
        currentColor = "#";
        var hexChars = "0123456789ABCDEF";
        for (var i = 0; i < 3; i++)
        {
            var v = rgbValues[i].valueOf();
            currentColor += hexChars.charAt(v/16) + hexChars.charAt(v%16);
        }
    }

    // 4. Defines the new color
    // 4.1 Current color is the default one
    if (currentColor == ''
        || currentColor.toLowerCase() == theDefaultColor.toLowerCase()) {
        if (theAction == 'over' && thePointerColor != '') {
            newColor              = thePointerColor;
        }
        else if (theAction == 'click' && theMarkColor != '') {
            newColor              = theMarkColor;
            marked_row[theRowNum] = true;
            // Garvin: deactivated onclick marking of the checkbox because it's also executed
            // when an action (like edit/delete) on a single item is performed. Then the checkbox
            // would get deactived, even though we need it activated. Maybe there is a way
            // to detect if the row was clicked, and not an item therein...
            // document.getElementById('id_rows_to_delete' + theRowNum).checked = true;
        }
    }
    // 4.1.2 Current color is the pointer one
    else if (currentColor.toLowerCase() == thePointerColor.toLowerCase()
             && (typeof(marked_row[theRowNum]) == 'undefined' || !marked_row[theRowNum])) {
        if (theAction == 'out') {
            newColor              = theDefaultColor;
        }
        else if (theAction == 'click' && theMarkColor != '') {
            newColor              = theMarkColor;
            marked_row[theRowNum] = true;
            // document.getElementById('id_rows_to_delete' + theRowNum).checked = true;
        }
    }
    // 4.1.3 Current color is the marker one
    else if (currentColor.toLowerCase() == theMarkColor.toLowerCase()) {
        if (theAction == 'click') {
            newColor              = (thePointerColor != '')
                                  ? thePointerColor
                                  : theDefaultColor;
            marked_row[theRowNum] = (typeof(marked_row[theRowNum]) == 'undefined' || !marked_row[theRowNum])
                                  ? true
                                  : null;
            // document.getElementById('id_rows_to_delete' + theRowNum).checked = false;
        }
    } // end 4

    // 5. Sets the new color...
    if (newColor) {
        var c = null;
        // 5.1 ... with DOM compatible browsers except Opera
        if (domDetect) {
            for (c = 0; c < rowCellsCnt; c++) {
                theCells[c].setAttribute('bgcolor', newColor, 0);
            } // end for
        }
        // 5.2 ... with other browsers
        else {
            for (c = 0; c < rowCellsCnt; c++) {
                theCells[c].style.backgroundColor = newColor;
            }
        }
    } // end 5

    return true;
} // end of the 'setPointer()' function

function redir(url) {
	document.location.href=url;
	return false;
}
 
 function click(e) {
 if (document.all) {
 if (event.button == 2) {
 //var messagel="PC's Rule ha! :)\n"
 //alert(navigator.appName+" \nver: "+navigator.appVersion);
  return false;
   }
 }
 if (document.layers) {
 if (e.which == 3) {
 //var messagel="PC's Rule ha! :)\n"
 //alert(navigator.appName+" \nver: "+navigator.appVersion);
 return false;
    }
   }
 }

if (navigator.appName!="Microsoft Pocket Internet Explorer") {
 if (document.layers) {
 document.captureEvents(Event.MOUSEDOWN);
 }
 document.onmousedown=click;
}

function type_insert_check(form_name,elem_name,id){
	doc_form=eval("document."+form_name+'.'+elem_name);
	doc_form[id].checked=true;
}

function order_select($dishid,form_name){
	frm=eval("document."+form_name);
	frm.dishid.value=$dishid;
	frm.submit();
	return(false);
}

function color_select(color){
	document.edit_form_category.htmlcolor.value=color;
	tabcolor.tbodies[0].trows[0].cells[0].innerText=color;
	//tabcolor.tbodies[0].trows[0].cells[0].style.backgroundColor=color;
	tdcolor.style.backgroundColor=color;
	return(false);
}

function mod_set($letter){
	document.form1.letter.value=$letter;
	document.form1.last.value=0;
	document.form1.submit();
	return(false);
}

function discount_switch(){

	if(document.form_discount.discount_type[0].checked==true){
		document.form_discount.percent.disabled=true;
		document.form_discount.amount.disabled=true;
	} else if (document.form_discount.discount_type[1].checked==true){
		document.form_discount.percent.disabled=false;
		document.form_discount.amount.disabled=true;
	} else if (document.form_discount.discount_type[2].checked==true){
		document.form_discount.percent.disabled=true;
		document.form_discount.amount.disabled=false;
	}

	return(false);
}

function payment_activation(){
	//alert("Funzione BEGIN");
	//list1=eval("document.form1.payment_data_date_day")

	document.form1.payment_data_date_day.disabled=!document.form1.payment_data_date_day.disabled
	document.form1.payment_data_date_month.disabled=!document.form1.payment_data_date_month.disabled
	document.form1.payment_data_date_year.disabled=!document.form1.payment_data_date_year.disabled
	document.form1.payment_data_type[0].disabled=!document.form1.payment_data_type[0].disabled
	document.form1.payment_data_type[1].disabled=!document.form1.payment_data_type[1].disabled
	document.form1.payment_data_type[2].disabled=!document.form1.payment_data_type[2].disabled
	document.form1.payment_data_account_id.disabled=!document.form1.payment_data_account_id.disabled

	if(document.form1.payment_data_type[0].disabled==true){
		document.form1.payment_data_type[0].checked=false;
		document.form1.payment_data_type[1].checked=false;
		document.form1.payment_data_type[2].checked=false;
	} else {
		document.form1.payment_data_type[0].checked=true;
		document.form1.payment_data_type[1].checked=false;
		document.form1.payment_data_type[2].checked=false;
	}
}

function invia(aformtosend,alist1,alist2){
	formtosend=eval("document."+aformtosend);
	list1=eval("document."+aformtosend+"."+alist1);
	list2=eval("document."+aformtosend+"."+alist2);

	list1length=list1.length;
	list2length=list2.length;

	for(i=0;i<list1length;i++){
		list1[i].selected=true;
	}
	for(i=0;i<list2length;i++){
		list2[i].selected=true;
	}
	//alert(list2.length);
	formtosend.submit();
}

function quantity(form,elem,operation,massimo){
	elemento=eval("document."+form+"."+elem);
	//(int) elemento.value=(int) elemento.value + 1;
	//(int) elemento.text=(int) elemento.text + 1;
//	document.form1.elements[1].value="22";

	//alert(elemento.value + " " + massimo);

	if(operation=="1" && elemento.value < massimo){
			elemento.value++;
	} else if (operation=="-1" && elemento.value > 0) {
			elemento.value--;
	}
			//	elemento.value++;

}

function check_all(form,elem) {
	fromlist=eval("document."+form+".elements['"+elem+"']");
	
	fromlistlength=fromlist.length;

	what = eval("document."+form+".all_checker.checked");
	
	for(i=0;i<fromlistlength;i++){
		fromlist[i].checked=what;
	}
}

function check_elem_in_list(form,elem,value) {
	fromlist=eval("document."+form+".elements['"+elem+"']");
	
	fromlistlength=fromlist.length;

	for(i=0;i<fromlistlength;i++){
		if(fromlist[i].value==value) fromlist[i].checked=!fromlist[i].checked;
	}
}

function check_prio(form,elem) {
	fromlist=eval("document."+form+".elements['data[priority]']");
	fromlist[elem].checked=true;
}

function check_ingredqty(ingredid,id) {
	elem="data[ingred_qty]["+ingredid+"]";
	fromlist=eval("document.form1.elements['"+elem+"']");
	fromlist[id].checked=true;
}

function check_elem(form,elem,id) {
	elem=elem+"["+id+"]";
	fromlist=eval("document."+form+".elements['"+elem+"']");
	fromlist.checked=!fromlist.checked;
}

function allarme() {
	alert('poppo');
}

function move(form,from,to){
	fromlist=eval("document."+form+".elements['"+from+"']");
	tolist=eval("document."+form+".elements['"+to+"']");
	
	fromlistlength=fromlist.length;
	tolistlength=tolist.length;

	for(i=0;i<fromlistlength;i++){

		if(fromlist[i].selected==true){
			tolist.length=tolistlength+1;
			tolistlength=tolist.length;

			last2=tolistlength-1;
			tolist[last2].value=fromlist[i].value;
			tolist[last2].text=fromlist[i].text;
			tolist[last2].selected=fromlist[i].selected;


			for(i=i;i<fromlistlength-1;i++){

				j=i+1;
				fromlist[i].value=fromlist[j].value;
				fromlist[i].text=fromlist[j].text;
				fromlist[i].selected=fromlist[j].selected;
			}
			fromlist.length=fromlistlength-1;

		i=-1;
		fromlistlength=fromlist.length;
		tolistlength=tolist.length;
		}
	}
}

<!--
//TODO : rishkrue help screen
var tl=new Array(
"My Handy Restaurant is a free software created to help restaurant workers in their job",
"",
"Developed by Fabio 'Kilyerd' De Pascale",
"Created by Fabio 'Kilyerd' De Pascale and Ivan 'Ivanoez' Anochin",
"",
"Kindly supported by:",
"Ristorante Arsenale (Forlì - Italy)- http://www.ristorantearsenale.it",
"Alt-F4 (Italy) - http://www.alt-f4.it",
"Aviano Inn (Aviano - Italy)",
"",
"Developers:",
"Fabio 'Kilyerd' De Pascale - Main developer",
"Rogelio Triviño González - Optimization",
"",
"Translated by:",
"Fabio 'Kilyerd' De Pascale - Italian and English",
"Ivan 'Ivanoez' Anochin - Russian",
"Pablo Hugo 'Pabloha' Acevedo - Spanish (Argentinian)",
"Fadjar Tandabawana - Indonesian",
"Dorian Mladin - Romanian",
"",
"Thanks to:",
"Stefania, my girlfriend, for her love",
"Ivan, for his enthusiasm and the howtos",
"the forum writers, for their suggestions and bugs reporting",
"the people at Ristorante Arsenale, in particular Nando and Maurizio, for their support and their tasty meals!",
"Christian, for his surprising desserts",
"EliBus from Alt-F4, for the webserver and for believing in the project",
"Fadjar, for his suggestions and testing",
"Pabloha, for his hard work in doing the first translation"
 );

var speed=50;
var index=0; text_pos=0;
var str_length=tl[0].length;
var contents, row;

function type_text()
{
  contents='';
  row=Math.max(0,index-20);
  //row=0;
  while(row<index)
    contents += tl[row++] + '\r\n';
  document.forms[0].elements[0].value = contents + tl[index].substring(0,text_pos) + "_";
  if(text_pos++==str_length)
  {
    text_pos=0;
    index++;
    if(index!=tl.length)
    {
      str_length=tl[index].length;
      setTimeout("type_text()",500);
    }
  } else
    setTimeout("type_text()",speed);
 
}

function change_class (obj, classnam) {
	obj.className = classnam;
}

function select_all(form,elem) {
	fromlist=eval("document."+form+".elements['"+elem+"']");
	
	fromlistlength=fromlist.length;

	for(i=0;i<fromlistlength;i++){
		fromlist[i].selected=true;
	}
}

function deselect_all(form,elem) {
	fromlist=eval("document."+form+".elements['"+elem+"']");
	
	fromlistlength=fromlist.length;

	for(i=0;i<fromlistlength;i++){
		fromlist[i].selected=false;
	}
}

function select_one(form,elem,idx) {
	fromlist=eval("document."+form+".elements['"+elem+"']");
	fromlistlength=fromlist.length;
	for(i=0;i<fromlistlength;i++){
		fromlist[i].selected=false;
	}
	fromlist[idx].selected=true;
}

function password_form(){
	p0=eval("document.edit_form_user.elements[password_action]");
	p1=eval("document.edit_form_user.elements['data[password1]']");
	p2=eval("document.edit_form_user.elements['data[password2]']");
	p1.disabled=p0.checked;
	p2.disabled=p0.checked;
}

//mizuko : Printoj faqen aktuale

function printPage() { 
	print(document); 
}