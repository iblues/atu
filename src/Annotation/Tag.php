<?php

namespace Iblues\AnnotationTestUnit\Annotation;

/**
 * 标记tag
 * @author Blues
 *
 * @link https://github.com/iblues/annotation-test-unit
 * @Annotation
 * Class Tag
 * @Target({"ANNOTATION"})
 * @package Iblues\AnnotationTestUnit
 */
class Tag
{
    protected $tag = [];

    function __construct($data)
    {
        if (!isset($data['value'])) {
            return;
        }
        if (!is_array($data['value'])) {
            $data['value'] = [$data['value']];
        }
        $this->tag = $data['value'];
    }

    function getTag()
    {
        return $this->tag;
    }
}