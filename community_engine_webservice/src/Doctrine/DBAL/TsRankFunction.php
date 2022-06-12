<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Doctrine\DBAL;


use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Subselect;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Class TsRankFunction
 * @package App\Doctrine\DBAL
 */
class TsRankFunction extends FunctionNode
{
    /**
     * @var Subselect
     */
    private $tsvector;
    /**
     * @var Subselect
     */
    private $tsquery;

    /**
     * @param SqlWalker $sqlWalker
     * @return string
     * @throws \Doctrine\ORM\Query\AST\ASTException
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return sprintf(
            'ts_rank(%s, %s)',
            $this->tsvector->dispatch($sqlWalker),
            $this->tsquery->dispatch($sqlWalker)
        );
    }

    /**
     * @param Parser $parser
     * @return void
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->tsvector = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->tsquery = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}