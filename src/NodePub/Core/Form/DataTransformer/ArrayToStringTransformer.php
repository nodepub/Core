<?php

namespace NodePub\Core\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\Common\Collections\ArrayCollection;

class ArrayToStringTransformer implements DataTransformerInterface
{
    protected $delimiter;
    
    public function __construct($delimiter = ',')
    {
        $this->delimiter = $delimiter;
    }

    /**
     * Transforms array to string
     */
    public function transform($val)
    {
        if (null === $val) {
            return '';
        }
        
        if ($val instanceof ArrayCollection) {
            $val = $val->toArray();
        }
        
        return is_array($val) ? implode($this->delimiter, $val) : '';
    }

    /**
     * Transforms string back to array
     */
    public function reverseTransform($val)
    {
        if (!$val) {
            return null;
        }

        return array_map(function($item) {
            return trim($item);
        }, explode($this->delimiter, $val));
    }
}