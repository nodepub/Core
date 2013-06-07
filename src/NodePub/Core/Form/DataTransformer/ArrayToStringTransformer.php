<?php

namespace NodePub\Core\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\Common\Collections\ArrayCollection;

class ArrayToStringTransformer implements DataTransformerInterface
{
    # TODO make this a configurable param?
    const SEPARATOR = ', ';

    /**
     * Transforms array to string
     */
    public function transform($val)
    {
        if ($val instanceof ArrayCollection) {
            $val = $val->toArray();
        }

        return is_array($val) ? implode(self::SEPARATOR, $val) : '';
    }

    /**
     * Transforms string back to array
     */
    public function reverseTransform($val)
    {
        if (empty($val)) {
            return array();
        }
        
        return explode(self::SEPARATOR, $val);
    }
}