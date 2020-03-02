<?php

namespace Iblues\AnnotationTestUnit\Assert;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\Comparator\ComparisonFailure;

/**
 * 支持正则的json匹配
 * @author Blues
 * Class AssertAdvJson
 * @package Iblues\AnnotationTestUnit\Assert
 */
class AssertAdvJson extends Constraint
{
    /**
     * @var iterable
     */
    private $subset;

    /**
     * @var bool
     */
    private $strict;

    /**
     * assertAdvJson
     * @param $subset
     * @param $array
     * @param bool $checkForObjectIdentity
     * @param string $message
     * @author Blues
     */
    static public function assert($subset, $array, bool $checkForObjectIdentity = false, string $message = '')
    {
        if (!(is_array($subset) || $subset instanceof ArrayAccess)) {
            throw InvalidArgumentHelper::factory(1, 'array or ArrayAccess');
        }

        if (!(is_array($array) || $array instanceof ArrayAccess)) {
            throw InvalidArgumentHelper::factory(2, 'array or ArrayAccess');
        }
        $constraint = new AssertAdvJson($subset, $checkForObjectIdentity);

        Assert::assertThat($array, $constraint, $message);
    }

    public function __construct(iterable $subset, bool $strict = false)
    {
        parent::__construct();

        $this->strict = $strict;
        $this->subset = $subset;
    }

    /**
     * Evaluates the constraint for parameter $other
     *
     * If $returnResult is set to false (the default), an exception is thrown
     * in case of a failure. null is returned otherwise.
     *
     * If $returnResult is true, the result of the evaluation is returned as
     * a boolean value instead: true in case of success, false in case of a
     * failure.
     *
     * @param mixed $other value or object to evaluate
     * @param string $description Additional information about the test
     * @param bool $returnResult Whether to return a result or throw an exception
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function evaluate($other, $description = '', $returnResult = false)
    {
        //type cast $other & $this->subset as an array to allow
        //support in standard array functions.

        $other = $this->toArray($other);
        $this->subset = $this->toArray($this->subset);

        $patched = $this->walkCheck($other, $this->subset);
        $patched = \array_replace_recursive($other, $patched);

        if ($this->strict) {
            $result = $other === $patched;
        } else {
            $result = $other == $patched;
        }

        if ($returnResult) {
            return $result;
        }

        if (!$result) {
            $f = new ComparisonFailure(
                $patched,
                $other,
                \var_export($patched, true),
                \var_export($other, true)
            );

            $this->fail($other, $description, $f);
        }
    }

    /**
     * 循环正则判断
     * @param $target
     * @param $match
     * @return array
     * @author Blues
     *
     */
    public function walkCheck($target, $match)
    {
        if (is_array($target)) {
            foreach ($target as $key => $val) {
                $return = $this->walkCheck($val, $match[$key] ?? null);
                if (!is_null($return)) {
                    $target[$key] = $return;
                }
            }
            return $target;
        } else {
            //如果匹配到返回target
            if ($match === true || $target == $match) {
                return $target;
            } else {
                //正则
                if (@$match[0] == '/') {
                    if (preg_match($match, $target)) {
                        return $target;
                    }
                }

                //都没匹配到 返回$match
                return $match;
            }
        }
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function toString(): string
    {
        return 'has the subset ' . $this->exporter->export($this->subset);
    }

    /**
     * Returns the description of the failure
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * @param mixed $other evaluated value or object
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    protected function failureDescription($other): string
    {
        return 'an array ' . $this->toString();
    }

    private function toArray(iterable $other): array
    {
        if (\is_array($other)) {
            return $other;
        }

        if ($other instanceof \ArrayObject) {
            return $other->getArrayCopy();
        }

        if ($other instanceof \Traversable) {
            return \iterator_to_array($other);
        }

        // Keep BC even if we know that array would not be the expected one
        return (array)$other;
    }
}
