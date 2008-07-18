function redir(url) {
	document.location.href=url;
	return false;
}

function order_select($dishid,form_name){
	frm=eval("document."+form_name);
	frm.dishid.value=$dishid;
	frm.submit();
	return(false);
}