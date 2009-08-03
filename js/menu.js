$(document).ready(function() {
	
$("ul#topnav li").hover(function() { 
	$(this).css({ 'background' : '#1376c9 '}); 
	$(this).find("span").show(); 
} , function() { 
	$(this).css({ 'background' : 'none'}); 
	$(this).find("span").hide(); 
});
	
});
