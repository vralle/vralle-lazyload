<?php
namespace Vralle\Lazyload\App;

/**
 * Define the tag regex
 *
 * @link       https://github.com/vralle/VRALLE.Lazyload
 * @since      0.8.0
 * @package    Vralle_Lazyload
 * @subpackage Vralle_Lazyload/app
 */
class Util
{
    /**
     * Retrieve the html tag regular expression. Overweight, but makes the search bullet-proof.
     *
     * The regular expression contains 1 sub matche to help with parsing.
     *
     * 1 - The tag name
     * 2 - The tag attributes
     *
     * @since 0.8.0
     *
     * @param  string $tagnames Optional.
     * @return string The html tag search regular expression
     */
    public function getTagRegex($tags = null)
    {
        $tags = \join('|', array_map('preg_quote', $tags));

        return
        '<\s*'                              // Opening tag
        . "($tags)"                         // Tag name
        . '('
        .     '[^>\\/]*'                    // Not a closing tag or forward slash
        .     '(?:'
        .         '\\/(?!>)'                // A forward slash not followed by a closing tag
        .         '[^>\\/]*'                // Not a closing tag or forward slash
        .     ')*?'
        . ')'
        . '\\/?>';                          // Self closing tag ...
    }
}
