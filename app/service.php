<?php
namespace Vralle\Lazyload\App;

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://github.com/vralle/VRALLE.Lazyload
 * @since      0.6.0
 * @package    Vralle_Lazyload
 * @subpackage Vralle_Lazyload/app
 * @author     Vitaliy Ralle <email4vit@gmail.com>
 */
class Service
{
    /**
     * Retrieve the html tag regular expression for searching.
     *
     * The regular expression contains 1 sub matche to help with parsing.
     *
     * 1 - The tag attribute list
     *
     * @since 0.6.0
     *
     * @param array $tagnames Optional.
     * @return string The html tag search regular expression
     */
    static function get_tag_regex($tag = 'img')
    {
        return
        '<\s*'                              // Opening bracket
        . "$tag"                            // Tag name
        . '(?![\\w-])'                      // Not followed by word character or hyphen
        . '('                               // 1: Unroll the loop: Inside the opening tag
        .     '[^>\\/]*'                    // Not a closing tag or forward slash
        .     '(?:'
        .         '\\/(?!>)'                // A forward slash not followed by a closing tag
        .         '[^>\\/]*'                // Not a closing tag or forward slash
        .     ')*?'
        . ')'
        . '\\/?>';                          // Self closing tag ...
    }
}
