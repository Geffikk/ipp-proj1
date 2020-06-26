<?php
/** Maros Geffert
 *  IPP - projekt 1 **/

const header_error = 21;
const error_code = 22;
const lex_parse_error = 23;
const arg_error = 10;

const my_variables = array("GF", "LF", "TF");
const my_constans = array("string", "int", "bool", "float", "nil");

class make_XML {
    // Count of comments/instruction number
    private $inst_number;
    private $comments_cnt;


    private $arg;
    private $xml;

    private $counter;
    private $head = true;

    public function __construct() {
        $this->inst_number = 0;
        $this->comments_cnt = 0;
        $this->arg = 0;
        $this->counter = 1;

        $this->xml = new XMLWriter();
        $this->xml->openMemory();
        $this->xml->setIndent(true);
        $this->xml->setIndentString("  ");
        $this->xml->startDocument('1.0', 'utf-8');
        $this->xml->startElement('program');
        $this->addXMLAtribute('language', 'IPPcode20');

        $this->make_lex_parse_analysis();

        $this->xml->endElement();
        $this->xml->endDocument();
    }

    function multiexplode ($delimiters, $string) {
        $ready = str_replace($delimiters, $delimiters[0], $string);
        $launch = explode($delimiters[0], $ready);
        return $launch;
    }

    public  function check_head() {

        if ($this->head === true)
        {
            $header = fgets(STDIN);
            $comment_char = substr($header, -strlen($header), 1);

            while ($comment_char == "#" || $comment_char == "\n")
            {
                $header = fgets(STDIN);
                $comment_char = substr($header, -strlen($header), 1);
            }
            // Remove (" ", \n, \r ...) from line
            $header = preg_replace('/\s+/', ' ', $header);
            $header = $this->multiexplode(array(" "), trim($header));
            $this->head = false;

            // Controling, if in head is commentar
            if(isset($header[1]))
                $comment_char = substr($header[1], -strlen($header[1]), 1);

            if($comment_char == "#") {
                ++$this->comments_cnt;
            }

            if (!($comment_char === "#" || $comment_char === "" || $comment_char == " " || $comment_char == ".")) {
                return false;
            }

            $header = $header[0];
            $header = explode("#", $header);
            if (strtolower($header[0]) === ".ippcode20") {
                return true;
            }
            else {
                return false;
            }
        }
    }

    public function check_body() {
        while (true) {
            $char = fgets(STDIN);

            if ($char == false) {
                break;
            }

            $char = preg_replace('/\s+/', ' ', $char);
            $char = $this->multiexplode(array(" "), trim($char));
            $comment_char = true;
            $i = 0;
            $remove = false;

            while ($comment_char) {
                if (isset($char[$i]))
                    $comment_char = substr($char[$i], -strlen($char[$i]), 1);
                else
                    $comment_char = false;

                if ($comment_char == "#") {
                    ++$this->comments_cnt;
                    $remove = true;
                }
                if ($remove) {
                    unset($char[$i]);
                }
                $i++;
            }

            $state_inst = "";
            if(isset($char[0]))
                $state_inst = strtolower($char[0]);

            if ($state_inst != "") {
                $this->arg = 0;
                $this->xml->startElement("instruction");
                $this->addXMLAtribute("order", ++$this->inst_number);


                switch ($state_inst) {
                    case "defvar":
                        $this->generate_XML($char, "V", "DEFVAR");
                        if (isset($char[2])) $this->cntParams($char[2]);
                        break;
                    case "write":
                        $this->generate_XML($char, "S", "WRITE");
                        if (isset($char[2])) $this->cntParams($char[2]);
                        break;
                    case "call":
                        $this->generate_XML($char, "L", "CALL");
                        if (isset($char[2])) $this->cntParams($char[2]);
                        break;
                    case "pushs":
                        $this->generate_XML($char, "S", "PUSH");
                        if (isset($char[2])) $this->cntParams($char[2]);
                        break;
                    case "pops":
                        $this->generate_XML($char, "V", "POPS");
                        if (isset($char[2])) $this->cntParams($char[2]);
                        break;
                    case "exit":
                        $this->generate_XML($char, "S", "EXIT");
                        if (isset($char[2])) $this->cntParams($char[2]);
                        break;
                    case "createframe":
                        $this->generate_XML($char, "", "CREATEFRAME");
                        if (isset($char[1])) $this->cntParams($char[1]);
                        break;
                    case "pushframe";
                        $this->generate_XML($char, "", "PUSHFRAME");
                        if (isset($char[1])) $this->cntParams($char[1]);
                        break;
                    case "popframe":
                        $this->generate_XML($char, "", "POPFRAME");
                        if (isset($char[1])) $this->cntParams($char[1]);
                        break;
                    case "return":
                        $this->generate_XML($char, "", "RETURN");
                        if (isset($char[1])) $this->cntParams($char[1]);
                        break;
                    case "int2char":
                        $this->generate_XML($char, "VS", "INT2CHAR");
                        if (isset($char[3])) $this->cntParams($char[3]);
                        break;
                    case "read":
                        $this->generate_XML($char, "VT", "READ");
                        if (isset($char[3])) $this->cntParams($char[3]);
                        break;
                    case "type":
                        $this->generate_XML($char, "VS", "TYPE");
                        if (isset($char[3])) $this->cntParams($char[3]);
                        break;
                    case "label":
                        $this->generate_XML($char, "L", "LABEL");
                        if (isset($char[2])) $this->cntParams($char[2]);
                        break;
                    case "jump":
                        $this->generate_XML($char, "L", "JUMP");
                        if (isset($char[2])) $this->cntParams($char[2]);
                        break;
                    case "dprint":
                        $this->generate_XML($char, "S", "DPRINT");
                        if (isset($char[2])) $this->cntParams($char[2]);
                        break;
                    case "break":
                        $this->generate_XML($char, "", "BREAK");
                        if (isset($char[1])) $this->cntParams($char[1]);
                        break;
                    case "move":
                        $this->generate_XML($char, "VS", "MOVE");
                        if (isset($char[3])) $this->cntParams($char[3]);
                        break;
                    case "add":
                        $this->generate_XML($char, "VSS", "ADD");
                        if (isset($char[4])) $this->cntParams($char[4]);
                        break;
                    case "sub":
                        $this->generate_XML($char, "VSS", "SUB");
                        if (isset($char[4])) $this->cntParams($char[4]);
                        break;
                    case "mul":
                        $this->generate_XML($char, "VSS", "MUL");
                        if (isset($char[4])) $this->cntParams($char[4]);
                        break;
                    case "idiv":
                        $this->generate_XML($char, "VSS", "IDIV");
                        if (isset($char[4])) $this->cntParams($char[4]);
                        break;
                    case "lt":
                        $this->generate_XML($char, "VSS", "LT");
                        if (isset($char[4])) $this->cntParams($char[4]);
                        break;
                    case "gt":
                        $this->generate_XML($char, "VSS", "GT");
                        if (isset($char[4])) $this->cntParams($char[4]);
                        break;
                    case "eq":
                        $this->generate_XML($char, "VSS", "EQ");
                        if (isset($char[4])) $this->cntParams($char[4]);
                        break;
                    case "and":
                        $this->generate_XML($char, "VSS", "AND");
                        if (isset($char[4])) $this->cntParams($char[4]);
                        break;
                    case "or":
                        $this->generate_XML($char, "VSS", "OR");
                        if (isset($char[4])) $this->cntParams($char[4]);
                        break;
                    case "not":
                        $this->generate_XML($char, "VS", "NOT");
                        if (isset($char[3])) $this->cntParams($char[3]);
                        break;
                    case "stri2int":
                        $this->generate_XML($char, "VSS", "STRI2INT");
                        if (isset($char[4])) $this->cntParams($char[4]);
                        break;
                    case "concat":
                        $this->generate_XML($char, "VSS", "CONCAT");
                        if (isset($char[4])) $this->cntParams($char[4]);
                        break;
                    case "strlen":
                        $this->generate_XML($char, "VS", "STRLEN");
                        if (isset($char[3])) $this->cntParams($char[3]);
                        break;
                    case "getchar":
                        $this->generate_XML($char, "VSS", "GETCHAR");
                        if (isset($char[4])) $this->cntParams($char[4]);
                        break;
                    case "setchar":
                        $this->generate_XML($char, "VSS", "SETCHAR");
                        if (isset($char[4])) $this->cntParams($char[4]);
                        break;
                    case "jumpifeq":
                        $this->generate_XML($char, "LSS", "JUMPIFEQ");
                        if (isset($char[4])) $this->cntParams($char[4]);
                        break;
                    case "jumpifneq":
                        $this->generate_XML($char, "LSS", "JUMPIFNEQ");
                        if (isset($char[4])) $this->cntParams($char[4]);
                        break;
                    case "";
                        break;
                    default:
                        fwrite(STDERR, "ERROR bad instruction");
                        $this->arg = 0;
                        exit(error_code);
                }
                $this->xml->endElement();
            }
        }

        if (fgets(STDIN) == false)
            return;

        fwrite(STDERR, "ERROR: Expected instruction, obtained something else! \n");
        exit(lex_parse_error);
    }

    public function generate_XML($infos, $choice, $val) {
        $this->counter = 1;
        switch($choice) {
            case "V":
                $this->addXMLAtribute("opcode", $val);
                $this->variable($infos);
                break;
            case "S":
                $this->addXMLAtribute("opcode", $val);
                $this->symb($infos);
                break;
            case "L":
                $this->addXMLAtribute("opcode", $val);
                $this->label($infos);
                break;
            case "VS":
                $this->addXMLAtribute("opcode", $val);
                $this->variable($infos);
                $this->symb($infos);
                break;
            case "VT":
                $this->addXMLAtribute("opcode", $val);
                $this->variable($infos);
                $this->type($infos);
                break;
            case "VSS":
                $this->addXMLAtribute("opcode", $val);
                $this->variable($infos);
                $this->symb($infos);
                $this->symb($infos);
                break;
            case "LSS":
                $this->addXMLAtribute("opcode", $val);
                $this->label($infos);
                $this->symb($infos);
                $this->symb($infos);
                break;
            case "":
                $this->addXMLAtribute("opcode", $val);
                break;
            default:
                fwrite(STDERR, "Something went wrong");
                $this->arg = 0;
                exit(99);
        }
    }

    public function cntParams($inst)
    {
        if (isset($inst))
        {
            fwrite(STDERR,"Error wrong parameters");
            exit(lex_parse_error);
        }
    }

    public function getXML() {
        return $this->xml->outputMemory();
    }

    public function getInstructionsNumber() {
        return $this->inst_number;
    }

    public  function getCommentsCount() {
        return $this->comments_cnt;
    }

    private function make_lex_parse_analysis() {
        $is_head_correct = $this->check_head();

        if ($is_head_correct == true) {
                $this->check_body();
        }
        else {
            fwrite(STDERR, "Header is incorrect ! (Must be '.IPPcode20')");
            exit(header_error);
        }
    }

    private function name($FRAME) {
        preg_match("/^[A-Za-zÁ-Žá-ž_$%&*!?#-]*[\w*%$&!?Á-Žá-ž#-]+$/",$FRAME, $match);

        if(isset($match[1]) || empty($match)) {
            fwrite(STDERR,"Error: unauthorized characters in name");
            exit(lex_parse_error);
        }
        $comment_match = explode("#", $match[0]);
        $second_match = $comment_match[0];
        return $second_match;
    }

    private function frame($FRAME) {
        return $FRAME;
    }

    private function separator($FRAME) {
        if ($FRAME == "@") {
            return $FRAME;
        }
        else {
            fwrite(STDERR, "Error uknown operation code");
            exit(error_code);
        }
    }

    private function type($FRAME) {
        if (isset($FRAME[$this->counter])) {
            if (in_array($FRAME[$this->counter], my_constans)) {
                $this->xml->startElement("arg" . (++$this->arg));
                $this->addXMLAtribute("type", "type");
                $this->xml->text($FRAME[2]);
                $this->xml->endElement();
                $this->counter++;
            }
            else{
                fwrite(STDERR, "Error: Expected type !\n");
                exit(lex_parse_error);
            }
        }
        else{
            fwrite(STDERR, "Error: Expected type !\n");
            exit(lex_parse_error);
        }

    }

    private function label($FRAME) {
        if (isset($FRAME[$this->counter]))
            $name = $this->name($FRAME[$this->counter]);
        else {
            fwrite(STDERR, "Error: Lexical/Syntax error");
            exit(lex_parse_error);
        }
        $this->xml->startElement("arg". (++$this->arg));
        $this->addXMLAtribute("type", "label");
        $this->xml->text($name);
        $this->xml->endElement();
        $this->counter++;
    }

    private function symb($FRAME) {
        if(isset($FRAME[$this->counter])) {
            preg_match('/[A-Za-z]+[@][\w_0-9+á-žÁ-Ž#\\\-]*/', $FRAME[$this->counter], $first_match);

            if ($FRAME[$this->counter] != isset($first_match[0]))
            {
                fwrite(STDERR, "Lexical/Syntax error: 23");
                exit(lex_parse_error);
            }
            if (empty(isset($first_match[0]))) {
                fwrite(STDERR, "Lexical or syntax error");
                exit(lex_parse_error);
            }
            else
            {
                $comment_match = explode("#", $first_match[0]);
                $second_match = $comment_match[0];
            }
            preg_match_all('/[A-Za-z]+|[@]|[\w_0-9+á-žÁ-Ž#\\\-]*/', $second_match, $matches);
            $this->counter++;
        }
        else
        {
            fwrite(STDERR, "Lexical/Syntax error: 23");
            exit(lex_parse_error);
        }

        if(in_array($matches[0][0], my_variables) || in_array($matches[0][0], my_constans)) {
            $frame = $this->frame($matches[0][0]);
            $sep = $this->separator($matches[0][1]);
            $this->xml->startElement("arg". (++$this->arg));

            if(in_array($matches[0][0], my_variables)) {
                if ($matches[0][2] == ""){
                    fwrite(STDERR, "Error: Lexical/Parse 23\n");
                    exit(lex_parse_error);
                }
                $name = $this->name($matches[0][2]);
                $this->addXMLAtribute("type", "var");
                $this->xml->text(strtoupper($frame).$sep.$name);
            }
            else {
                if(isset($matches[0][2])) {
                    $name = $matches[0][2];
                    if ($frame == "nil" and $name != "nil"){
                        fwrite(STDERR, "Error: Lexical/Parse 23\n");
                        exit(lex_parse_error);
                    }
                }
                else {
                    fwrite(STDERR, "Error: Lexical/Parse 23\n");
                    exit(lex_parse_error);
                }
                $this->addXMLAtribute("type", $frame);

                if($frame == "string") {
                    $char = array("<", ">", "&");
                    $replacement = array("&lt;", "&gt;", "&amp;");
                    //str_replace($name, $replacement, $char);
                }
                elseif ($frame == "bool") {
                    if ($name != "true" && $name != "false") {
                        fwrite(STDERR, "Error: Wrong boolean value\n");
                        exit(lex_parse_error);
                    }
                }
                ($name == false) ? $this->xml->text("") :$this->xml->text($name) ;
            }
            $this->xml->endElement();
        }
        else {
            fwrite(STDERR, "Lexical or syntax error");
            exit(lex_parse_error);
        }
    }

    private function variable($FRAME) {
        $name = "";
        if(isset($FRAME[$this->counter])) {
            preg_match('/[A-Za-z]+[@][\D][\w_]*/', $FRAME[$this->counter], $first_match);

            if ($FRAME[$this->counter] != isset($first_match[0]))
            {
                fwrite(STDERR, "Error: Lexical/Syntax error");
                exit(lex_parse_error);
            }
            if (empty(isset($first_match[0]))) {
                fwrite(STDERR, "Lexical or syntax error");
                exit(lex_parse_error);
            }
            else
            {
                $comment_match = explode("#", $first_match[0]);
                $second_match = $comment_match[0];
            }
            preg_match_all("/[A-Za-z]+|[@]|[\D][\w_]*/", $second_match, $matches);
            $this->counter++;
        }
        else
        {
            fwrite(STDERR, "Lexical/Syntax error: 23");
            exit(lex_parse_error);
        }

        if(in_array($matches[0][0], my_variables)) {
            $frame = $this->frame($matches[0][0]);
            $sep = $this->separator($matches[0][1]);
            if (isset($matches[0][2]))
                $name = $this->name($matches[0][2]);

            $this->xml->startElement("arg" . (++$this->arg));
            $this->addXMLAtribute("type", "var");
            $this->xml->text(strtoupper($frame) . $sep . $name);
            $this->xml->endElement();
        }
        else {
            fwrite(STDERR, "Lexical or syntax error");
            exit(lex_parse_error);
        }
    }

    private function addXMLAtribute($attribute, $value) {
        $this->xml->startAttribute($attribute);
        $this->xml->text($value);
        $this->xml->endAttribute();
    }

}

/********************************** Arguments PART [MAIN] *************************************/
$shortOptions = "";
$longOptions = array(
    "help",
    "stats:",
    "comments",
    "loc",
    "jumps",
    "labels",
    );

$options = getopt($shortOptions, $longOptions);

if ((empty($options) && $argc == 1) || (!empty($options) && isset($options["stats"])) || $argc >= 3) {
    $FileArgument = false;
    $LocArgument = false;
    $CommentsArgument = false;

    $BadCntArg = false;

    if (isset($options["stats"]))
        $FileArgument = $options["stats"];
        if (isset($options["loc"]))
            $LocArgument = true;
        if (isset($options["comments"]))
            $CommentsArgument = true;

    if ($FileArgument && !$LocArgument && !$CommentsArgument)
        $BadCntArg = true;
    if ($FileArgument && ($LocArgument && $CommentsArgument) && $argc != 4)
        $BadCntArg = true;
    if ($FileArgument && ((!$LocArgument || !$CommentsArgument) && $argc != 3))
        $BadCntArg = true;
    if ($BadCntArg) {
        fwrite(STDERR, "Error bad format arguments");
        exit(arg_error);
    }

    $parse = new make_XML();

    if($FileArgument) {
        $content = "";
        foreach($options as $key => $option) {
            if ($key == "loc") {
                $content .= $parse->getInstructionsNumber() . "\n";
            }
            elseif ($key == "comments") {
                $content .= $parse->getCommentsCount() . "\n";
            }
        }

        $writeStats = @file_put_contents($FileArgument, $content);
        if (!$writeStats) {
            fwrite(STDERR,"Internal error");
            exit(99);
        }
    }
    echo $parse->getXML();
    exit (0);
}
elseif(isset($options["help"]) && $argc == 2) {
    echo "\n---------------------- Program helper -------------------------- \n\nProgram read source code (IPPcode20), parse program and return 0,\nif program end succesffuly. Program print XML representation. \n";
}
else {
    fwrite(STDERR, "Error bad format arguments");
    exit(arg_error);
}

