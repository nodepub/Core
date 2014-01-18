<?php

namespace NodePub\Core\Helper;

/**
 * Creates a helper for rendering html
 */
class HtmlHelper
{
    /**
     * Renders an html tag
     */
    public function renderTag($name, $options = array(), $open = false)
    {
        if (!$name) {
            return '';
        }
    
        return '<'.$name.$this->tagOptions($options).(($open) ? '>' : ' />');
    }
  
    /**
     * Renders an html content tag
     */
    public function renderContentTag($name, $content = '', $options = array())
    {
        if (!$name) {
            return '';
        }
  
        return '<'.$name.$this->tagOptions($options).'>'.$content.'</'.$name.'>';
    }
  
    /**
     * Splits blocks of text apart by the existence
     * of a double line break
     */
    public function paragraphsToArray($text)
    {
        return explode("\n\n", $text);
    }
    
    /**
     * Creates an unordered list populated by each array item.
     */
    public function arrayToUnorderedList($array, $attr=array())
    {
        return $this->renderContentTag('ul', $this->wrapArrayItems($array, 'li'), $attr);
    }
    
    /**
     * Breaks a string apart by each '\n\n' and wraps each
     * substring in <p> tags.
     */
    public function textToParagraphs($text)
    {
        return $this->wrapArrayItems($this->paragraphsToArray($text));
    }
  
    /**
     * Creates an HTML tag for each item in an array.
     * e.g. wrapArrayItems(array('foo', 'bar'), 'li');
     *      returns:
     *       <li>foo</li>
     *       <li>bar</li>
     */
    public function wrapArrayItems($array, $tag = 'p', $attr=array())
    {
        $ret = '';
        foreach ($array as $item) {
            $ret .= $this->renderContentTag($tag, $item, $attr);
        }
    
        return $ret;
    }
    
    /**
     * Formats an array of options into a string of key="value" pairs
     */
    protected function tagOptions($options = array())
    {
        $html = '';
        foreach ($options as $key => $value) {
            $html .= sprintf(' %s="%s"', $key, $value);
        }
  
        return $html;
    }
}