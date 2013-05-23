<?php

namespace NodePub\Core\Extension;

use NodePub\Core\Extension\DomManipulator;
use Silex\Application;

class SnippetQueue
{
    protected $app;
    protected $queue;
    protected $addjquery;
    protected $matchedComments;

    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->matchedComments = array();

        if (isset($app['config']['general']['add_jquery']) && $app['config']['general']['add_jquery'] == true) {
            $this->addjquery = true;
        } else {
            $this->addjquery = false;
        }
    }

    public function addJquery()
    {
        $this->addjquery = true;
    }

    public function addCss($filename)
    {
        $this->insertSnippet(
            self::AFTER_CSS,
            sprintf('<link rel="stylesheet" href="%s" media="screen">', $filename);
        );
    }

    public function addJavaScript($filename)
    {
        $this->insertSnippet(
            self::AFTER_JS,
            sprintf('<script src="%s"></script>', $filename)
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

    public function processAll($html)
    {
        // First, gather all html <!-- comments --> because they shouldn't be considered for replacements.
        // We use a callback in order to stash the comments for re-insertion.
        $html = preg_replace_callback('/<!--(.*)-->/Uis', array($this, 'stashComment'), $html);

        foreach ($this->queue as $item) {

            // Get the snippet, either by using a callback function,
            // or else use the passed string as-is
            if (function_exists($item['callback'])) {
                $snippet = call_user_func($item['callback'], $this->app);
            } else {
                $snippet = $item['callback'];
            }

            $domManipulator = new DomManipulator();

            // then insert it into the HTML
            switch ($item['location']) {
                case DomManipulator::START_HEAD:
                    $html = $domManipulator->insertStartOfHead($snippet, $html);
                    break;
                case DomManipulator::END_HEAD:
                    $html = $domManipulator->insertEndOfHead($snippet, $html);
                    break;
                case DomManipulator::AFTER_META:
                    $html = $domManipulator->insertAfterMeta($snippet, $html);
                    break;
                case DomManipulator::AFTER_CSS:
                    $html = $domManipulator->insertAfterCss($snippet, $html);
                    break;
                case DomManipulator::BEFORE_JS:
                    $html = $domManipulator->insertBeforeJs($snippet, $html);
                    break;
                case DomManipulator::AFTER_JS:
                    $html = $domManipulator->insertAfterJs($snippet, $html);
                    break;
                case DomManipulator::START_BODY:
                    $html = $domManipulator->insertStartOfBody($snippet, $html);
                    break;
                case DomManipulator::END_BODY:
                    $html = $domManipulator->insertEndOfBody($snippet, $html);
                    break;
                case DomManipulator::END_HTML:
                    $html = $domManipulator->insertEndOfHtml($snippet, $html);
                    break;
                default:
                    $html .= $snippet."\n";
                    break;
            }
        }

        if ($this->addjquery==true) {
            $html = $domManipulator->insertJquery($html);
        }

        // Re-insert original comments by replacing the placeholders.
        if (!empty($this->matchedComments)) {
            $html = preg_replace(array_keys($this->matchedComments), $this->matchedComments, $html, 1);
        }

        return $html;
    }

    /**
     * Callback for replacing HTML comments with a placeholder.
     * Stashes the HTML comment so it can be re-inserted after all snippets are processed.
     */
    protected function stashComment(array $pregMatches) {
        $key = "###npub-comment-".count($this->matchedComments)."###";
        // Add it to the array of matched comments..
        $this->matchedComments["/".$key."/"] = $pregMatches[0];

        return $key;
    }
}
