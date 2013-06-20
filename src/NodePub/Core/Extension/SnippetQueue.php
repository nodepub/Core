<?php

namespace NodePub\Core\Extension;

use NodePub\Core\Extension\DomManipulator;
use Silex\Application;

/**
 * Manages a queue of html snippets and their insertion points.
 */
class SnippetQueue
{
    protected $queue;
    protected $addjquery;
    protected $matchedComments;

    public function __construct()
    {
        $this->matchedComments = array();
    }

    public function addJquery()
    {
        $this->addjquery = true;
    }

    public function addCss($url)
    {
        $this->insertSnippet(
            DomManipulator::AFTER_CSS,
            sprintf('<link rel="stylesheet" href="%s" media="screen">', $url)
        );
    }

    public function addJavaScript($url)
    {
        $this->insertSnippet(
            DomManipulator::AFTER_JS,
            sprintf('<script src="%s"></script>', $url)
        );
    }

    public function add(array $snippets)
    {
        foreach ($snippets as $key => $value) {
            $this->insert($key, $value);
        }
    }

    public function insert($location, $callback)
    {
        $this->queue[] = array(
            'location' => $location,
            'callback' => $callback
        );
    }

    /**
     * @TODO: see if we really need $app injected
     */
    public function processAll(Application $app, $html)
    {
        // Replace html <!-- comments --> with placeholders
        $html = $this->replaceAndStashComments($html);

        $domManipulator = new DomManipulator();
        $insertionMethods = $domManipulator->getInsertionMethodsMap();

        foreach ($this->queue as $snippet) {

            // Get the snippet, either by using a callback function,
            // or else use the passed string as-is
            if (function_exists($snippet['callback'])) {
                $snippetString = call_user_func($snippet['callback'], $app);
            } else {
                $snippetString = $snippet['callback'];
            }

            if (isset($insertionMethods[$snippet['location']])
                && method_exists($domManipulator, $insertionMethods[$snippet['location']])) {

                $method = $insertionMethods[$snippet['location']];
                $html = call_user_func(array($domManipulator, $method), $snippetString, $html);
            } else {
                $html .= $snippetString."\n";
            }
        }

        if ($this->addjquery==true) {
            $html = $domManipulator->insertJquery($html);
        }

        $html = $this->restoreStashedComments($html);

        return $html;
    }

    /**
     * Callback for replacing HTML comments with a placeholder.
     * Stashes the HTML comment so it can be re-inserted after all snippets are processed.
     */
    protected function stashComment(array $pregMatches)
    {
        $key = "###npub-comment-".count($this->matchedComments)."###";
        // Add it to the array of matched comments..
        $this->matchedComments["/".$key."/"] = $pregMatches[0];

        return $key;
    }

    /**
     * Replaces all html <!-- comments --> with numbered placeholders
     * because they shouldn't be considered for snippet replacements.
     * The original comments are stashed for re-insertion after all snippets are processed.
     */
    protected function replaceAndStashComments($html)
    {
        // First, gather all html <!-- comments --> because they shouldn't be considered for replacements.
        // We use a callback in order to stash the comments for re-insertion.
        return preg_replace_callback('/<!--(.*)-->/Uis', array($this, 'stashComment'), $html);
    }

    /**
     * Replaces comment placeholders with their original html <!-- comments -->
     */
    protected function restoreStashedComments($html)
    {
        if (!empty($this->matchedComments)) {
            $html = preg_replace(array_keys($this->matchedComments), $this->matchedComments, $html, 1);
        }

        return $html;
    }
}
