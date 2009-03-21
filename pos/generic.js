function redir(url) { 
	document.location.href=url; 
	return false; 
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

function check_all(form,elem) {
	fromlist=eval("document."+form+".elements['"+elem+"']");
	
	fromlistlength=fromlist.length;

	what = eval("document."+form+".all_checker.checked");
	
	for(i=0;i<fromlistlength;i++){
		fromlist[i].checked=what;
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


function select_all(form,elem) {
	fromlist=eval("document."+form+".elements['"+elem+"']");
	
	fromlistlength=fromlist.length;

	for(i=0;i<fromlistlength;i++){
		fromlist[i].selected=true;
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

// gjergj : print the actual page

function printPage() { 
	print(document); 
}