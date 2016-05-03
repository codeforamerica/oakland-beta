<?php
namespace Craft;

/**
 * Calendar output.
 *
 * @author    Tipping Media, LLC. <support@tippingmedia.com>
 * @copyright Copyright (c) 2015, Tipping Media, LLC.
 * @package   venti.twigextensions
 * @since     1.0
 */

class Calendar_TokenParser extends \Twig_TokenParser
{
    // Public Methods
    // =========================================================================

    /**
     * Parses a token and returns a node.
     *
     * @param \Twig_Token $token
     *
     * @return \Twig_NodeInterface
     */
    public function parse(\Twig_Token $token)
    {
        $lineno = $token->getLine();

        $nodes['criteria'] = $this->parser->getExpressionParser()->parseExpression();
        $this->parser->getStream()->expect('as');
        $targets = $this->parser->getExpressionParser()->parseAssignmentExpression();
        $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);

        if (count($targets) > 1)
        {
            $paginateTarget = $targets->getNode(0);
            $nodes['calendarTarget'] = new \Twig_Node_Expression_AssignName($paginateTarget->getAttribute('name'), $paginateTarget->getLine());
            $elementsTarget = $targets->getNode(1);

        }
        else
        {
            $nodes['calendarTarget'] = new \Twig_Node_Expression_AssignName('calendar', $lineno);
            $elementsTarget = $targets->getNode(0);
        }

        $nodes['elementsTarget'] = new \Twig_Node_Expression_AssignName($elementsTarget->getAttribute('name'), $elementsTarget->getLine());

        return new Calendar_Node($nodes, array(), $lineno, $this->getTag());
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'calendar';
    }
}
