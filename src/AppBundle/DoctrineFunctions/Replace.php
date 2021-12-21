<?php

namespace AppBundle\DoctrineFunctions;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\Parser;

/**
 * "REPLACE" "(" StringPrimary "," StringSecondary "," StringThird ")"
 */
class Replace extends FunctionNode {
    public $stringFirst;
    public $stringSecond;
    public $stringThird;

    public function getSql(SqlWalker $sqlWalker) {
        return 'replace(' . $this->stringFirst->dispatch($sqlWalker) . ','
                . $this->stringSecond->dispatch($sqlWalker) . ','
                . $this->stringThird->dispatch($sqlWalker) . ')';
    }

    public function parse(Parser $parser) {

        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->stringFirst = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->stringSecond = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->stringThird = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

}