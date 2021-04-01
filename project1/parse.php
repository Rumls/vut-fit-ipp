<?php

# DEFINITIONS

ini_set('display_errors', 'stderr');
$headerCheck = true;
$instruction = [
    'order' => 0,
    'instr' => '',
    'arg1' => ['',''],
    'arg2' => ['',''],
    'arg3' => ['',''],
];

# FUNCTIONS

// simple function that counts on every call
function order(){
    static $ord = 0;
    ++$ord;
    return $ord;
}

function syntax_check($instruction){
    switch($instruction['instr']){
        case 'CREATEFRAME':
        case 'PUSHFRAME':
        case 'POPFRAME':
        case 'RETURN':
        case 'BREAK':
            if(!empty($instruction['arg1'][1])){
                exit(23);
            }
            break;

        case 'DEFVAR':
        case 'POPS':
            if(empty($instruction['arg1'][1]) || !empty($instruction['arg2'][1])){
                exit(23);
            }
            if($instruction['arg1'][1] != 'var'){
                exit(23);
            }
            break;
        
        case 'CALL':
        case 'LABEL':
        case 'JUMP':
            if(empty($instruction['arg1'][1]) || !empty($instruction['arg2'][1])){
                exit(23);
            }
            if($instruction['arg1'][1] != 'label'){
                exit(23);
            }
            break;

        case 'PUSHS':
        case 'WRITE':
        case 'EXIT':
        case 'DPRINT':
            if(empty($instruction['arg1'][1]) || !empty($instruction['arg2'][1])){
                exit(23);
            }
            if(($instruction['arg1'][1] != 'string') && ($instruction['arg1'][1] != 'int') && 
                ($instruction['arg1'][1] != 'bool') && ($instruction['arg1'][1] != 'nil') &&
                ($instruction['arg1'][1] != 'var')){
                //echo $instruction['instr'].$instruction['arg1'][1]."\n";
                exit(23);
            }
            break;

        case 'READ':
            if(empty($instruction['arg1'][1]) || empty($instruction['arg2'][1]) || !empty($instruction['arg3'][1])){
                exit(23);
            }
            if((($instruction['arg1'][1] != 'string') && ($instruction['arg1'][1] != 'int') && 
                ($instruction['arg1'][1] != 'bool') && ($instruction['arg1'][1] != 'nil') &&
                ($instruction['arg1'][1] != 'var')) || ($instruction['arg2'][1] != 'type')){
                //echo $instruction['instr'].$instruction['arg1'][1]."\n";
                exit(23);
            }
            break;

        case 'MOVE':
        case 'INT2CHAR':
        case 'STRLEN':
        case 'TYPE':
        case 'NOT':
            if(empty($instruction['arg1'][1]) || empty($instruction['arg2'][1]) || !empty($instruction['arg3'][1])){
                exit(23);
            }
            if((($instruction['arg2'][1] != 'string') && ($instruction['arg2'][1] != 'int') && 
                ($instruction['arg2'][1] != 'bool') && ($instruction['arg2'][1] != 'nil') &&
                ($instruction['arg2'][1] != 'var')) || ($instruction['arg1'][1] != 'var')){
                //echo $instruction['instr'].$instruction['arg1'][1]."\n";
                exit(23);
            }
            break;

        case 'JUMPIFEQ':
        case 'JUMPIFNEQ':
            if(empty($instruction['arg1'][1]) || empty($instruction['arg2'][1]) || empty($instruction['arg3'][1])){
                exit(23);
            }
            if((($instruction['arg2'][1] != 'string') && ($instruction['arg2'][1] != 'int') && 
                ($instruction['arg2'][1] != 'bool') && ($instruction['arg2'][1] != 'nil') &&
                ($instruction['arg2'][1] != 'var')) || ($instruction['arg1'][1] != 'label') ||
                (($instruction['arg3'][1] != 'string') && ($instruction['arg3'][1] != 'int') && 
                ($instruction['arg3'][1] != 'bool') && ($instruction['arg3'][1] != 'nil') &&
                ($instruction['arg3'][1] != 'var'))
                ){
                //echo $instruction['instr'].$instruction['arg1'][1]."\n";
                exit(23);
            }
            break;

        case 'ADD':
        case 'SUB':
        case 'MUL':
        case 'IDIV':
        case 'LT':
        case 'GT':
        case 'EQ':
        case 'AND':
        case 'OR':
        case 'STR2INT':
        case 'CONCAT':
        case 'GETCHAR':
        case 'SETCHAR':
            if(empty($instruction['arg1'][1]) || empty($instruction['arg2'][1]) || empty($instruction['arg3'][1])){
                exit(23);
            }
            if((($instruction['arg2'][1] != 'string') && ($instruction['arg2'][1] != 'int') && 
                ($instruction['arg2'][1] != 'bool') && ($instruction['arg2'][1] != 'nil') &&
                ($instruction['arg2'][1] != 'var')) || ($instruction['arg1'][1] != 'var') ||
                (($instruction['arg3'][1] != 'string') && ($instruction['arg3'][1] != 'int') && 
                ($instruction['arg3'][1] != 'bool') && ($instruction['arg3'][1] != 'nil') &&
                ($instruction['arg3'][1] != 'var'))
                ){
                //echo $instruction['instr'].$instruction['arg1'][1]."\n";
                exit(23);
            }
            break;

        default:
            exit(22);
    }
    for($k = 1; $k < 4; $k++){
        if( ($instruction['arg'.$k][1] == 'string' && 
            !(preg_match('/^(?!.*(\\\\\d(\D|$)|\\\\\d\d(\D|$)|\\\\\d\d\d\d|\\\\(\D|$)|\s)).*$/', $instruction['arg'.$k][0])))
            || ($instruction['arg'.$k][1] == 'int' && 
            !(preg_match('/^[-]*\d$/', $instruction['arg'.$k][0])))
            || ($instruction['arg'.$k][1] == 'label' && 
            !(preg_match('/^[_\-$&%*!?a-zA-Z][_\-$&%*!?a-zA-Z0-9]*$/', $instruction['arg'.$k][0])))
            || ($instruction['arg'.$k][1] == 'var' && 
            !(preg_match('/^(GF|LF|TF)@[_\-$&%*!?a-zA-Z][_\-$&%*!?a-zA-Z0-9]*$/', $instruction['arg'.$k][0])))
            ){
            exit(23);
        }
    }
}

// function to fill up instruction object and construct its xml counterpart (calls syntax_check)
function get_instr(&$headerCheck, $xw){
    while($line = fgets(STDIN)){

        // ignore whitespaces and comments
        $line = trim($line);
        if(($line == "") || ($line[0] == '#')){
            continue;
        }
    
        // strip the remaining non-newline comments
        if($filtered = strstr($line, '#', true)){
            $line = $filtered;
        }
    
        // check the header
        if($headerCheck){
            if($line == ".IPPcode21"){
                $headerCheck = false;
                continue;
            } else {
                echo("error");
                exit(21);
            }
        }
    
        // split filtered line into tokens
        $tokens = preg_split("/\s+/", $line, -1, PREG_SPLIT_NO_EMPTY);
        
        // fill up instruction object, start with order and opcode
        $instruction['order'] = order();
        $instruction['instr'] = strtoupper($tokens[0]);
    
        // continue filling up instruction object with arguments
        for($i = 1; $i < 4; $i++){
            if(empty($tokens[$i])){
                $instruction['arg'.$i][0] = '';
                $instruction['arg'.$i][1] = '';
    
            } else {

                // stripping type from its content
                $deffinder = strstr($tokens[$i], '@', true);
    
                // filling up with variable
                if(($deffinder == 'GF')||($deffinder == 'LF')||($deffinder == 'TF')){
                    $instruction['arg'.$i][0] = $tokens[$i];
                    $instruction['arg'.$i][1] = 'var';
    
                // filling up with constant
                } elseif(($deffinder == 'int')||($deffinder == 'bool')||($deffinder == 'string')||($deffinder == 'nil')){
                    $instruction['arg'.$i][0] = substr(strstr($tokens[$i], '@', false), 1);
                    $instruction['arg'.$i][1] = $deffinder;
    
                // filling up with type
                } elseif(($tokens[$i] == 'int')||($tokens[$i] == 'bool')||($tokens[$i] == 'string')||($tokens[$i] == 'nil')){
                    $instruction['arg'.$i][0] = $tokens[$i];
                    $instruction['arg'.$i][1] = 'type';
    
                // filling up with label
                } else {
                    $instruction['arg'.$i][0] = $tokens[$i];
                    $instruction['arg'.$i][1] = 'label';
    
                }  
            }
        }
        
        syntax_check($instruction);

        // start constructing instruction element
        xmlwriter_start_element($xw, 'instruction');
        
        // start constructing order attribute
        xmlwriter_start_attribute($xw, 'order');
        xmlwriter_text($xw, $instruction['order']);
        xmlwriter_end_attribute($xw);

        // start constructing opcode attribute
        xmlwriter_start_attribute($xw, 'opcode');
        xmlwriter_text($xw, $instruction['instr']);
        xmlwriter_end_attribute($xw);

        // start constructing arguments
        for($j = 1; $j < 4; $j++){
            if(!empty($instruction['arg'.$j][1])){    
                xmlwriter_start_element($xw, 'arg'.$j);
                
                xmlwriter_start_attribute($xw, 'type');
                xmlwriter_text($xw, $instruction['arg'.$j][1]);
                xmlwriter_end_attribute($xw);

                xmlwriter_text($xw, $instruction['arg'.$j][0]);

                xmlwriter_end_element($xw);
            }
        }

        xmlwriter_end_element($xw);

    }
}

# MAIN

// write help to STDOUT
if($argc > 1){
    if($argv[1] == "--help"){
        echo("Filter script (parse.php in PHP 7.4) gets source code IPPcode21 from standard input, checks lexical and sintactic structure of the code and writes XML on standard output.\n");
        exit(0);
    } else {
        exit(10);
    }
}

// constructing beggining of xml
$xw = xmlwriter_open_memory();
xmlwriter_set_indent($xw, 1);
$res = xmlwriter_set_indent_string($xw, '  ');
xmlwriter_start_document($xw, '1.0', 'utf-8');

// first element
xmlwriter_start_element($xw, 'program');
xmlwriter_start_attribute($xw, 'language');
xmlwriter_text($xw, 'IPPcode21');
xmlwriter_end_attribute($xw);

// constructing xml out of instruction lines
get_instr($headerCheck, $xw);

// header checking didn't pass after the whole file was read
if($headerCheck){
    echo("error21");
    exit(21);
}

// end of main element and document
xmlwriter_end_element($xw);
xmlwriter_end_document($xw);

echo xmlwriter_output_memory($xw);

exit(0);

?>