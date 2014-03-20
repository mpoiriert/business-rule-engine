<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nucleus\BusinessRuleEngineEngine\Tests;

use Nucleus\BusinessRuleEngine\BusinessRuleEngine;
use Nucleus\BusinessRuleEngine\RuleNotFoundException;

/**
 * Description of FileSystemLoader
 *
 * @author Martin
 */
class BusinessRuleEngineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BusinessRuleEngine
     */
    private $businessRuleEngine = null;

    public function setUp()
    {
        $test = $this;
        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $logger->expects($this->any())
            ->method('debug')
            ->will(
                $this->returnCallback(function($message) use ($test) {
                    //echo $message . "\n";
                })
            );

        $this->businessRuleEngine = new BusinessRuleEngine();
        $this->businessRuleEngine->setLogger($logger);
        $this->businessRuleEngine->getRuleProvider()->setRule('default',new TestDefaultTrueRule());
    }

    /**
     * @dataProvider provideRuleCompositions
     */
    public function testCheck($ruleCompositions, $expected)
    {
        $this->assertSame(
            $expected,
            $this->businessRuleEngine->check($ruleCompositions, array(new TestBoolean())
            )
        );
    }

    public function provideRuleCompositions()
    {
        return array(
            array(array('!default', 'default'), false),
            array('!default', false),
            array('default', true),
            array(array('default', 'default'), true),
            array(array(array('default', 'default')), true),
            array(array(array('!default', 'default')), true),
            array(array(array('!default', '!default')), false),
            array(array('default', array('!default', '!default')), false),
            array(array('default', array('!default', 'default')), true),
            array(array('!default', array('!default', 'default')), false),
        );
    }

    public function testGetFirstMatch()
    {
        list($firstTrueIndex, $rules) = $this->prepareMultipleCheck();

        $this->assertSame(
            $firstTrueIndex,
            $this->businessRuleEngine->getFirstMatch($rules, array(new TestBoolean()))
        );
    }

    public function testGetAllMatches()
    {
        list(, $rules, $trueIndexes) = $this->prepareMultipleCheck();

        $this->assertSame(
            $trueIndexes,
            $this->businessRuleEngine->getAllMatches($rules, array(new TestBoolean()))
        );
    }

    protected function prepareMultipleCheck()
    {
        $firstTrueIndex = null;
        $rules = array();
        $trueIndexes = array();
        foreach ($this->provideRuleCompositions() as $index => $parameter) {
            list($rule, $expected) = $parameter;
            $rules[] = $rule;
            if ($expected) {
                $trueIndexes[] = $index;
            }
            if (is_null($firstTrueIndex) && $expected) {
                $firstTrueIndex = $index;
            }
        }

        return array($firstTrueIndex, $rules, $trueIndexes);
    }

    /**
     * @dataProvider provideMixContext
     * 
     * @param type $ruleCompositions
     * @param type $expected
     */
    public function testMixContext($ruleCompositions, $expected)
    {
        $ruleProvider = $this->businessRuleEngine->getRuleProvider();

        $ruleProvider->setRule('true',new TestTrue());
        $ruleProvider->setRule('false',new TestFalse());

        $this->assertSame(
            $expected,
            $this->businessRuleEngine->check($ruleCompositions, array(new TestBoolean()))
        );
    }

    public function provideMixContext()
    {
        return array(
            array('true', true),
            array('false', false),
            array(array('false', 'default'), false),
            array(array('!false', 'default'), true),
            array(array('true', 'default'), true),
        );
    }

    /**
     * @dataProvider provideTestWithParameters
     * 
     * @param type $ruleCompositions
     * @param type $expected
     */
    public function testWithParameters($ruleCompositions, $realParameters)
    {
        $exactMatch = new TestExactMatchValidator($realParameters);

        $ruleProvider = $this->businessRuleEngine->getRuleProvider();
        $ruleProvider->setRule('allMatch', new TestExactMatchRule());

        $this->assertTrue(
            $this->businessRuleEngine->check($ruleCompositions, array($exactMatch))
        );
    }

    public function provideTestWithParameters()
    {
        return array(
            array(array('allMatch{toto:else}'), array('toto' => 'else')),
            array(array('allMatch{value:false}'), array('value' => false)),
            array(array('allMatch{param1:[],param2:10}'), array('param1' => array(), 'param2' => 10))
        );
    }

    public function testExceptionOnCheck()
    {
        $this->setExpectedException(
            'Nucleus\BusinessRuleEngine\RuleNotFoundException',
            RuleNotFoundException::formatMessage('notExistingRule')
        );

        $this->businessRuleEngine->check('notExistingRule');
    }

    public function testExceptionOnFirstMatch()
    {
        $this->setExpectedException(
            'Nucleus\BusinessRuleEngine\RuleNotFoundException',
            RuleNotFoundException::formatMessage('notExistingRule')
        );

        $this->businessRuleEngine->getFirstMatch(array('notExistingRule'));
    }

    public function testExceptionOnAllMatches()
    {
        $this->setExpectedException(
            'Nucleus\BusinessRuleEngine\RuleNotFoundException',
            RuleNotFoundException::formatMessage('notExistingRule')
        );

        $this->businessRuleEngine->getAllMatches(array('notExistingRule'));
    }
}

class TestExactMatchValidator
{
    public $parameters;

    public function __construct($realParameters)
    {
        $this->parameters = $realParameters;
    }
}

class TestExactMatchRule
{

    public function __invoke(TestExactMatchValidator $validator, $toto = null, $value = null, $param1 = null, $param2 = null)
    {
        $parameters = compact('toto', 'value', 'param1', 'param2');
        foreach ($parameters as $parameter => $value) {
            if (array_key_exists($parameter, $validator->parameters)) {
                if ($validator->parameters[$parameter] !== $value) {
                    return false;
                }
                unset($validator->parameters[$parameter]);
            } elseif (!is_null($value)) {
                return false;
            }
        }
        return count($validator->parameters) === 0;
    }
}

class TestBoolean
{
    public $value = true;

}

class TestFalse
{

    public function __invoke()
    {
        return false;
    }
}

class TestTrue
{

    public function __invoke()
    {
        return true;
    }
}

class TestDefaultTrueRule
{

    public function __invoke(TestBoolean $testBoolean)
    {
        return $testBoolean->value === true;
    }
}

class TestRuleNames
{
    public $rules;

    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }
}

class TestBlaRule
{

    public function __invoke($ruleName, TestRuleNames $testRuleNames)
    {
        return in_array($ruleName, $testRuleNames->rules);
    }
}
