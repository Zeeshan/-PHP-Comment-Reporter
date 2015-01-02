<?php
// +----------------------------------------------------------------------+
// | comment_reporter                                                     |
// +----------------------------------------------------------------------+
// | Authors: Syed Zeeshan Haider Shah                                	  |
// +----------------------------------------------------------------------+

//set time limit to infinite for parsing large number of files
set_time_limit(0);
//display warnings only
error_reporting(E_ALL ^ E_NOTICE);
//set error reporting to on 
ini_set('display_errors','On');
/**
* Read a file or recursively read a directory
*
* @param string $str Path to file or directory
*/
function recursive_read($str)
{
	//add extension of type of files you need to scan in this array
    $allowed_ext = array("php","module","inc");
	//if this is a file
    if(is_file($str))
    {
		//check if file extension is allowed to report
        $ext = pathinfo($str, PATHINFO_EXTENSION);
		//if file is allowed to report
        if( in_array($ext, $allowed_ext))
        {
			//initialize reporting variable
            $rep ="";
			//add heading for reporting file
            $rep .=  "<b>Reporting:$str </b><br>";
			//pass this file to function for finding uncommented lines and print it
            echo $rep .= comment_report($str);            
        }
    }
	//if this is a folder , not a file
    elseif(is_dir($str))
	{
		//trim slashes from paths
        $scan = glob(rtrim($str,'/').'/*');
		//traverse through folders
        foreach($scan as $index=>$path)
        {
			//recursion; call same function again for each folder/file.
            recursive_read($path);
        }
    }
}
/**
* This function will find the previous value of an array from provided location
* As prev function will set pointer to a different  location, I have to code this function so that 
* location of pointer dont get lost 
*
* @param array &$array location of array
* @param int | string  $curr_key location of current index
* @return int | string $prev , location of last index
*/
function getPrev(&$array, $curr_key)
{
	//Set pointer to array end
    end($array);
	//hold index in var
    $prev = key($array);
	//jump to previous index and hold current in tem var
    do
    {
		//save key in temp
        $tmp_key = key($array);
		//set array to previous key
        $res = prev($array);
    } 
	//keep jumping till you get the current key(passed to this function)
	while ( ($tmp_key != $curr_key) && $res );
	//if found during looping 
    if( $res )
    {
		//get to previous location from current key
        $prev = key($array);
    }
	//return previous index
    return $prev;
}
/**
* This function will parse provided code and report if line is commented 
* @param file $file file having some code to test
* @return string $msg return formatted  report for all lines that are uncommented
*/
function comment_report($file)
{
	//set threshold value of percentage of code you want to be treated as red flag
    $per_red = 60;
    //get code from file
    $src= file_get_contents($file);
	//count total lines
    $lines = count(file($file));
    //create tokens
    $tokens = token_get_all($src);        
    //initialize array for storing number of lines 
    $line_num = array();
	//initialize array to save token in desired format(line and token name pair)
    $my_token = array();
	//name of tokens which needs to be excluded from original array of tokens
    $excluded_tokens = array("T_INLINE_HTML","T_OPEN_TAG","T_CLOSE_TAG","T_WHITESPACE");        
    //del all tokens in excluded list
    foreach($tokens as $tid=>$token)
    {
		//verify if its token is array , not string like  {,},(,) etc
        if(is_array($token))
        {
			//verify if token is in excuded list
            if( in_array( token_name($token[0]),$excluded_tokens) )
            {
				//delete it from array
                unset($tokens[$tid]);
            }
        }
		//if its a string delete it, we dont want comments for {,},(,) etc
        else
        {
			//delete it from array
            unset($tokens[$tid]);
        }
    }

	//Now we will delete repeated lines as we only need one occurrence for reporting
    //initialize array
    $line = array();
	//set line number against every token id
    foreach($tokens as $tid=>$token)
    {
		//save line as index and token id as value
        $line[$token[2]][]  = $tid;
    }
	//traverse each line for multiple tokens
    foreach($line as $lid=>$line_arr)
    {
		//if line has more the one token
        if(count($line_arr)>1)
        {
			//leave first occurrence of array by saving all remaining , which will be deleted latter
            array_shift($line[$lid]);
        }
    }
	//now traverse these arrays and delete them from original array
    foreach($line as $line_arr)
    {
		//if line has more the one token
        if(count($line_arr)>1)
        {
			//go to each token in line
            foreach($line_arr as $line_arr_ele)
            {
				//and delete them
                 unset($tokens[$line_arr_ele]);
            }
        }
    }
	
	//Now create array holding line number and token name
	//initilized a new array for tokens
    $my_token = array();
	//traverse old token
    foreach ($tokens as $token)
    {
		//save token line number and token name in new array
		$my_token[$token[2]] = token_name($token[0]);
    }
	
	//Now we need to count actual number of php code for more accurate reporting.
	//We will not count whitespace,tags, comments as PHP code
    //initialize int for recording php code lines by zero
    $php_code_lines = 0;
	//traverse tokens
    foreach ($my_token as $token)
    {
		//check if this is a real php code
        if($token <>"T_COMMENT" and $token <>"T_DOC_COMMENT")
        {
			//keep incrementing counter
            ++$php_code_lines ;
        }    
    }
   
    //Now our token are clean and ready to check against comments, we will proceed for actual reporting
    $uncommented_lines= array();
	//for reporting we will explode array as string
    $uncommented_lines_str ="";
	//percentage of uncommented code will be saved in this var
    $per_uncomment_coode ="";
	//traverse every line of php code 
    foreach($my_token as $line_no=>$newtoken)
    {
		//if this line is not any type of comment
        if(($newtoken != "T_COMMENT" and $newtoken != "T_DOC_COMMENT") and $line_no >1)
        {
			//check previous line of current line to verify if line is commented
            if( $my_token[getPrev($my_token,$line_no)]!="T_COMMENT" and  $my_token[getPrev($my_token,$line_no)]!="T_DOC_COMMENT")
            {
				//the line in uncomment, report it....
                $uncommented_lines[] =  $line_no;                
            }
        }
    }
   
	//Now create a formatted  report for uncommented line
    $uncommented_lines_str = implode(",", $uncommented_lines);
	//if code is fully commented
	if(trim($uncommented_lines_str)=="")
	{
		//set $uncommented_lines_str to 0 
		$uncommented_lines_str = 0;
		//appreciate dev by comments
		$bravo = "<b>EXCELENT CODE</b><br>";
	}
	//calculate percentage of uncommented code
    $per_uncomment_coode = floor((count($uncommented_lines)/$php_code_lines)*100);
	//now format report
    $style ="";
	//Mark as red flag  if exceeding threshold value
    if($per_uncomment_coode > $per_red)
    {
		//show in red color
        $style = "color='red'";
    }
	//set message to blank
    $msg = "";
	//prepare reporting string
    $msg = "<font $style>$per_uncomment_coode% code is uncomment.";
	//add stats
    $msg .= "<br>Tot lines: $lines, PHP lines :$php_code_lines, uncommented lines: $uncommented_lines_str. </font><br><br>$bravo";
	//return report
    return $msg;
}

//provide name of file or folder to scan and report comments
//you can pass it directly here or in request variable
$scaning_file_name="";
//if file name is provided
if($scaning_file_name)
{
	//Pass file/folder name to function to traverse all files in folder
    recursive_read($scaning_file_name);
}    
?>