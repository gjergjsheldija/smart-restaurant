<?php

class functionDuplicate{
    var $dir_name = '';									//The directory to search
    var $allowed_file_types = array('php','phps','inc');//The file types that are searched
    var $foundFiles;									//Files that contain the search phrase will be stored here
	var $functionList = array(); 						//array with the list of functions
	var $functionDeclarationList = array();
	var $functionUsedList = ''; 						// array with the not used functions
    var $myfiles;
/*    var $forbiddenDirs = array (".",
							    "..",
    							".svn",
    							"system",
    							"administrator",
    							"lang",
    							"xtemplate"
							    );*/
    var $forbiddenDirs = array (".",
							    "..",
    							".svn",
    							"system",
    							"docs",
    							"help",
    							"installer",
    							"cache",
    							"gui",    							
    							"js",
								"logs"      							
							    );							    
    
    function search($directory){
        $this->dir_name = $directory;
        
        $this->myfiles = $this->GetDirContents($this->dir_name);
        $this->foundFiles = array();
        
        if ( empty($this->dir_name) ) die('You must select a directory to search');
        
        
		foreach( $this->myfiles as $fl) {
		     if ( in_array(array_pop(explode ( '.', $fl )),  $this->allowed_file_types) ){
                $this->functionList[] =  $this->parse_string($fl);
            }		
		}
		echo "build functions array..." . count($this->functionList) . " functions\n";
		//dumb dumb clean up
		foreach( $this->functionList as $key => $value) {
				if( count($value) == 0 ) {continue;}
				foreach($value as $val1 => $val2) {
					foreach($val2 as $val3 => $val4) {
						$temporaryArray [] = $val4;
					}
				}
		}
		echo "cleaned functions array... " . count($temporaryArray) . " functions\n";
			
		$this->functionList = array_unique( $temporaryArray );
		$this->functionDeclarationList =  array_flip(array_unique( $temporaryArray ));
		echo "looking into ... " . count($this->myfiles) . " files\n";
		foreach( $this->functionList as $nr => $functionName) {
			$i=1;
			foreach ( $this->myfiles as $f ){
	            if ( in_array(array_pop(explode ( '.', $f )),  $this->allowed_file_types) ){
	                $contents = file_get_contents($f);
	                if ( strpos($contents, $functionName) !== false ) {
	                	$this->functionUsedList[$functionName] = $i++;
	                    $this->foundFiles [] = $f;
	                }
	            }
	        }
		}
		echo "build functions list for selected files...\n";
        return $this->foundFiles;
    }
	function parse_string($myFile) {
		$temp = array();
		$phpFunctionsArray = get_defined_functions();
		$fh = fopen($myFile, 'r');
		$theData = fread($fh, filesize($myFile));
		$tokens = token_get_all($theData);
		fclose($fh);
		foreach($tokens as $arrNum => $tokenVal) {
			if(is_array($tokenVal)) {
				if( @token_name($tokenVal[0]) == 'T_FUNCTION' && !in_array($tokens[$arrNum+2][1], $phpFunctionsArray['internal'])) {
					if($tokens[$arrNum+2][1] != '') {
						$temp[$tokenVal[1]][] = $tokens[$arrNum+2][1];
					}
				}
			}
		}
		return $temp;
	}

    function GetDirContents($dir){
       if (!is_dir($dir)){die ("Function GetDirContents: Problem reading : $dir!");}
       if ($root=@opendir($dir)){
           while ($file=readdir($root)){
               if( in_array($file, $this->forbiddenDirs) ){continue;}
               if(is_dir($dir."/".$file) && is_array($files) && is_array($this->GetDirContents($dir."/".$file))){
                   	$files=array_merge($files,$this->GetDirContents($dir."/".$file));
               }else{
               		$files[]=$dir."/".$file;
               }
           }
       }
       return $files;
    }

}


$search = new functionDuplicate;
$search->search('/var/www/smartres/trunk/');
//$search->search('/var/www/iMed/branches/php5x/');
echo count($search->functionDeclarationList) . "--" . count($search->functionUsedList);
//var_dump($search->functionUsedList);
echo "<table><tr><td>";
echo "<pre>";
print_r($search->functionUsedList);
echo "</pre>";
echo "</td><td>";
echo "<pre>";
print_r($search->functionDeclarationList);
echo "</pre>";
echo "</td><td>";
echo "<pre>";
print_r(array_diff($search->functionUsedList,$search->functionDeclarationList));
echo "</pre>";
echo "</td></tr></table>";