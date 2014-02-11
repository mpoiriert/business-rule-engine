<?php

namespace Nucleus\BusinessRuleEngine;

use Nucleus\Invoker\IInvoker;
use Nucleus\Invoker\Invoker;
use Symfony\Component\Yaml\Yaml;


class BusinessRuleEngine implements IBusinessRuleEngine
{
    /**
     * @var IRuleProvider
     */
    private $ruleProvider;

    /**
     * @var Yaml
     */
    private $yamlParser;

    /**
     * @var IInvoker
     */
    private $invoker;

    /**
     * @param IRuleProvider $ruleProvider
     * @param IInvoker $invoker
     * @param Yaml $yamlParser
     */
    public function __construct(
        IRuleProvider $ruleProvider = null,
        IInvoker $invoker = null,
        Yaml $yamlParser = null
    )
    {
        $this->invoker = $invoker ? $invoker : new Invoker();
        $this->yamlParser = $yamlParser ? $yamlParser : new Yaml();
        $this->ruleProvider = $ruleProvider ? $ruleProvider : new InMemoryRuleProvider();
    }

    /**
     * @param $ruleSpecifications
     * @param array $contextParameters
     * @return mixed|null
     */
    public function getFirstMatch($ruleSpecifications, array $contextParameters = array())
    {
        foreach ($ruleSpecifications as $index => $ruleSpecification) {
            if ($this->check($ruleSpecification, $contextParameters)) {
                return $index;
            }
        }

        return null;
    }

    /**
     * @param $rules
     * @param array $parameters
     * @return array
     */
    public function getAllMatches($ruleSpecifications, array $parameters = array())
    {
        $engine = $this;
        return array_keys(
            array_filter(
                $ruleSpecifications,
                function($ruleSpecification) use ($parameters, $engine) {
                    return $engine->check($ruleSpecification,$parameters);
                }
            )
        );
    }

    /**
     * @param $ruleSpecification
     * @param array $parameters
     *
     * @return bool
     */
    public function check($ruleSpecification, array $parameters = array())
    {
        $engine = $this;
        //This is to prevent the enforce method to have all the parameters
        //And also prevent to assign the parameter to the object
        $callback = function($rule) use ($engine, $parameters) {
            return $engine->verifyRule($rule, $parameters);
        };

        return $this->enforce($ruleSpecification, $callback);
    }

    /**
     * This method should not be called directly
     * 
     * @param string $rule
     * @param array $parameters
     *
     * @return boolean
     */
    public function verifyRule($ruleSpecification, array $parameters)
    {
        list($ruleName, $defaultParameters) = $this->extractRuleNameAndParameters($ruleSpecification);
        $ruleObject = $this->ruleProvider->getRule($ruleName);
        return $this->invoker->invoke($ruleObject, array_merge($defaultParameters,$parameters));
    }

    private function extractRuleNameAndParameters($ruleSpecification)
    {
        if (false !== $pos = strpos($ruleSpecification, '{')) {
            list($ruleName, $parameterString) = explode('{', $ruleSpecification, 2);
            $parameters = $this->yamlParser->parse('{' . $parameterString, true);
        } else {
            $ruleName = $ruleSpecification;
            $parameters = array();
        }
        return array($ruleName, $parameters);
    }

    private function enforce($checks, $callback, $useAnd = true)
    {
        if (!is_array($checks)) {
            $not = false;
            if ($checks{0} == '!') {
                $not = true;
                $checks = substr($checks, 1);
            }
            $result = $callback($checks);
            return $not ? !$result : $result;
        }

        $test = true;
        foreach ($checks as $rule) {
            // recursively check the rule with a switched AND/OR mode
            $test = $this->enforce($rule, $callback, $useAnd ? false : true);
            if (!$useAnd && $test) {
                return true;
            }

            if ($useAnd && !$test) {
                return false;
            }
        }
        return $test;
    }

    /**
     * Set the rule provider
     *
     * @param IRuleProvider $ruleProvider
     *
     * @return void
     */
    public function setRuleProvider(IRuleProvider $ruleProvider)
    {
        $this->ruleProvider = $ruleProvider;
    }

    /**
     * Return the current rule provider
     *
     * @return IRuleProvider
     */
    public function getRuleProvider()
    {
        return $this->ruleProvider;
    }
}
