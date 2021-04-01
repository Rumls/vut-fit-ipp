## Documentation for the 1st part of the project for IPP 2020/2021
### First and last name: Jakub Mlkvy
### Login: xmlkvy00

**parse.php** after checking ```--help``` argument uses **xmlwriter** to wrap code into the main XML element, then continues to the ```get_instr($headerCheck, $xw)``` where ```$headerCheck``` is a boolean type variable for confirming there is a header inside the source code after stripping new-lines and comments.
After stripping, we continue with parsing each line into temporary object ```$instruction```. It keeps track of instruction order, saves opcode, its arguments and type of arguments.
This object is parsed into the ```syntax_check($instruction)``` function where we check instruction syntax in switch and type correctness with regex.
After the check each instruction is constructed into XML from ```$instruction``` object.

### Summary

- ```order()``` simple function that counts on every call
- ```get_instr($headerCheck, $xw);``` function fills up instruction object and constructs its xml counterpart (calls ```syntax_check($instruction)```)
- ```syntax_check($instruction)``` checks instruction correctness
```
$instruction = [
    'order' => 0,
    'instr' => '',
    'arg1' => ['',''],
    'arg2' => ['',''],
    'arg3' => ['',''],];
    ```
    - ```order``` order of the instruction
    - ```instr``` opcode
    - ```argn``` n-th argument pair [content,type]
