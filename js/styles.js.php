<?php 
$root  = "http://".$_SERVER['HTTP_HOST'];
$root .= str_replace(basename($_SERVER['SCRIPT_NAME']),"",$_SERVER['SCRIPT_NAME']);
$baseUrl = $root;
$str = <<<JS_CODE

	/* Theme changer - set cookie */

    $(function() {
		$("link[title='style']").attr("href","$baseUrl../css/styles/default/ui.css");
        $('a.set_theme').click(function() {
           	var theme_name = $(this).attr("id");
			$("link[title='style']").attr("href","$baseUrl../css/styles/" + theme_name + "/ui.css");
			$.cookie('theme', theme_name );
			$('a.set_theme').css("fontWeight","normal");
			$(this).css("fontWeight","bold");
        });

		var theme = $.cookie('theme');
	    
		if (theme == 'default') {
	        $("link[title='style']").attr("href","$baseUrl../css/styles/default/ui.css");
	    };
	    
		if (theme == 'light_blue') {
	        $("link[title='style']").attr("href","$baseUrl../css/styles/light_blue/ui.css");
	    };
	

	/* Layout option - Change layout from fluid to fixed with set cookie */
       
       $("#fluid_layout").click (function(){
			$("#fluid_layout").hide();
			$("#fixed_layout").show();
			$("#page-wrapper").removeClass('fixed');
			$.cookie('layout', 'fluid' );
       });

       $("#fixed_layout").click (function(){
			$("#fixed_layout").hide();
			$("#fluid_layout").show();
			$("#page-wrapper").addClass('fixed');
			$.cookie('layout', 'fixed' );
       });

	    var layout = $.cookie('layout');
	    
		if (layout == 'fixed') {
			$("#fixed_layout").hide();
			$("#fluid_layout").show();
	        $("#page-wrapper").addClass('fixed');
	    };

		if (layout == 'fluid') {
			$("#fixed_layout").show();
			$("#fluid_layout").hide();
	        $("#page-wrapper").addClass('fluid');
	    };
	    
	    if (layout != 'fluid' && layout != 'fixed') {
			$("#fixed_layout").show();
			$("#fluid_layout").hide();
	        $("#page-wrapper").addClass('fluid');
	    };
	
    });
    
JS_CODE;
echo $str;