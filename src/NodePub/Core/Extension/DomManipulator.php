<?php

namespace NodePub\Core\Extension;

use Silex\Application;

/**
 * TODO: look into using https://github.com/wasinger/htmlpagedom
 */
class DomManipulator
{
    const START_HEAD = 'start_of_head';
    const END_HEAD   = 'end_of_head';
    const START_BODY = 'start_of_body';
    const END_BODY   = 'end_of_body';
    const END_HTML   = 'end_of_html';
    const AFTER_META = 'after_meta';
    const AFTER_CSS  = 'after_css';
    const BEFORE_JS  = 'before_js';
    const AFTER_JS   = 'after_js';

    protected $addjquery;
    protected $matchedComments;

    /**
     * Helper function to insert some HTML into thestart of the head section of
     * an HTML page, right after the <head> tag.
     *
     * @param  string $tag
     * @param  string $html
     * @return string
     */
    public function insertStartOfHead($tag, $html)
    {
        // first, attempt to insert it after the <head> tag, matching indentation..
        if (preg_match("~^([ \t]+)<head(.*)~mi", $html, $matches)) {
            // Try to insert it after <head>
            $replacement = sprintf("%s\n%s\t%s", $matches[0], $matches[1], $tag);
            $html = str_replace($matches[0], $replacement, $html);
        } else {
            // Since we're serving tag soup, just append it.
            $html .= $tag."\n";
        }

        return $html;
    }

    /**
     * Helper function to insert some HTML into thestart of the head section of
     * an HTML page, right after the <head> tag.
     *
     * @param  string $tag
     * @param  string $html
     * @return string
     */
    public function insertStartOfBody($tag, $html)
    {
        // first, attempt to insert it after the <body> tag, matching indentation.
        if (preg_match("~^([ \t]+)<body(.*)~mi", $html, $matches)) {
            // Try to insert it after <body>
            $replacement = sprintf("%s\n%s\t%s", $matches[0], $matches[1], $tag);
            $html = str_replace($matches[0], $replacement, $html);
        } else {
            // Since we're serving tag soup, just append it.
            $html .= $tag."\n";
        }

        return $html;
    }

    /**
     * Helper function to insert HTML into the head section of an HTML
     * page, right before the </head> tag.
     *
     * @param  string $tag
     * @param  string $html
     * @return string
     */
    public function insertEndOfHead($tag, $html)
    {
        // first, attempt to insert it before the </head> tag, matching indentation.
        if (preg_match("~^([ \t]+)</head~mi", $html, $matches)) {
            // Try to insert it just before </head>
            $replacement = sprintf("%s\t%s\n%s", $matches[1], $tag, $matches[0]);
            $html = str_replace($matches[0], $replacement, $html);
        } else {
            // Since we're serving tag soup, just append it.
            $html .= $tag."\n";
        }

        return $html;
    }

    /**
     * Helper function to insert HTML into the body section of an HTML
     * page, right before the </body> tag.
     *
     * @param  string $tag
     * @param  string $html
     * @return string
     */
    public function insertEndOfBodyX($tag, $html)
    {
        // first, attempt to insert it before the </body> tag, matching indentation.
        if (preg_match("~^([ \t]?)</body~mi", $html, $matches)) {
            // Try to insert it just before </body>
            $replacement = sprintf("%s\t%s\n%s", $matches[1], $tag, $matches[0]);
            $html = str_replace($matches[0], $replacement, $html);
        } else {
            // Since we're serving tag soup, just append it.
            $html .= $tag."\n";
        }

        return $html;
    }

    public function insertEndOfBody($tag, $html)
    {
        if (function_exists('mb_stripos')) {
            $posrFunction = 'mb_strripos';
            $substrFunction = 'mb_substr';
        } else {
            $posrFunction = 'strripos';
            $substrFunction = 'substr';
        }

        if (false !== $pos = $posrFunction($tag, '</body>')) {
            $tag = "\n".str_replace("\n", '', $tag)."\n";
            
            return $substrFunction($tag, 0, $pos).$tag.$substrFunction($tag, $pos);
        }
    }

    /**
     * Helper function to insert HTML into the html section of an HTML
     * page, right before the </html> tag.
     *
     * @param  string $tag
     * @param  string $html
     * @return string
     */
    public function insertEndOfHtml($tag, $html)
    {
        // first, attempt to insert it before the </body> tag, matching indentation.
        if (preg_match("~^([ \t]?)</html~mi", $html, $matches)) {
            // Try to insert it just before </html>
            $replacement = sprintf("%s\t%s\n%s", $matches[1], $tag, $matches[0]);
            $html = str_replace($matches[0], $replacement, $html);
        } else {
            // Since we're serving tag soup, just append it.
            $html .= $tag."\n";
        }

        return $html;
    }

    /**
     * Helper function to insert HTML into the head section of an HTML page.
     *
     * @param  string $tag
     * @param  string $html
     * @return string
     */
    public function insertAfterMeta($tag, $html)
    {
        // first, attempt to insert it after the last meta tag, matching indentation..
        if (preg_match_all("~^([ \t]+)<meta (.*)~mi", $html, $matches)) {
            // matches[0] has some elements, the last index is -1, because zero indexed.
            $last = count($matches[0])-1;
            $replacement = sprintf("%s\n%s%s", $matches[0][$last], $matches[1][$last], $tag);
            $html = str_replace($matches[0][$last], $replacement, $html);
        } else {
            $html = $this->insertEndOfHead($tag, $html);
        }

        return $html;
    }

    /**
     * Helper function to insert HTML into the head section of an HTML page.
     *
     * @param  string $tag
     * @param  string $html
     * @return string
     */
    public function insertAfterCss($tag, $html)
    {
        // first, attempt to insert it after the last <link> tag, matching indentation..
        if (preg_match_all("~^([ \t]+)<link (.*)~mi", $html, $matches)) {
            // matches[0] has some elements, the last index is -1, because zero indexed.
            $last = count($matches[0])-1;
            $replacement = sprintf("%s\n%s%s", $matches[0][$last], $matches[1][$last], $tag);
            $html = str_replace($matches[0][$last], $replacement, $html);

        } else {
            $html = $this->insertEndOfHead($tag, $html);
        }

        return $html;
    }

    /**
     * Helper function to insert HTML before the first javascript include in the page.
     *
     * @param  string $tag
     * @param  string $html
     * @return string
     */
    public function insertBeforeJs($tag, $html)
    {
        // first, attempt to insert it after the <body> tag, matching indentation.
        if (preg_match("~^([ \t]+)<script(.*)~mi", $html, $matches)) {
            // Try to insert it before the match
            $replacement = sprintf("%s%s\n%s\t%s", $matches[1], $tag, $matches[0], $matches[1]);
            $html = str_replace($matches[0], $replacement, $html);
        } else {
            // Since we're serving tag soup, just append it.
            $html .= $tag."\n";
        }

        return $html;
    }

    /**
     * Helper function to insert HTML after the last javascript include in the page.
     *
     * @param  string $tag
     * @param  string $html
     * @return string
     */
    public function insertAfterJs($tag, $html)
    {
        // first, attempt to insert it after the last <link> tag, matching indentation..
        if (preg_match_all("~^([ \t]+)<script (.*)~mi", $html, $matches)) {
            // matches[0] has some elements, the last index is -1, because zero indexed.
            $last = count($matches[0])-1;
            $replacement = sprintf("%s\n%s%s", $matches[0][$last], $matches[1][$last], $tag);
            $html = str_replace($matches[0][$last], $replacement, $html);
        } else {
            $html = $this->insertEndOfHead($tag, $html);
        }

        return $html;
    }

    /**
     * Insert jQuery, if it's not inserted already.
     *
     * @param string $html
     */
    private function insertJquery($html)
    {
        // check if jquery is not yet present. Some of the patterns that 'match' are:
        // jquery.js
        // jquery.min.js
        // jquery-latest.js
        // jquery-latest.min.js
        // jquery-1.8.2.min.js
        // jquery-1.5.js
        if (!preg_match('/<script(.*)jquery(-latest|-[0-9\.]*)?(\.min)?\.js/', $html)) {
            $jqueryfile = $this->app['paths']['app']."view/js/jquery-1.8.2.min.js";
            $html = $this->insertBeforeJs("<script src='$jqueryfile'></script>", $html);
            return $html;
        } else {
            // We've already got jQuery. Yay, us!
            return $html;
        }
    }
}
