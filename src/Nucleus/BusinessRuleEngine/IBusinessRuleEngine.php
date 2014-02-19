<?php

namespace Nucleus\BusinessRuleEngine;

/**
 * Class IBusinessRuleEngine
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 *
 * A rule specification is a string concatenation of the rule name and the default parameter in Yaml. If you want to call the
 * rule toto with default parameter param1 = 1, param2 = array(1,2) it would look like this:
 *
 *     "toto{param1:1,param2[1,2]}"
 *
 * You can add a ! before the rule if you want to make it false so you don't have to pass a parameter to the rule to say
 * not match:
 *
 *      "!toto{param1:1,param2[1,2]}"
 *
 * If you want to check that 2 or more rules must validate you can pass a array of rule:
 *
 *      array("toto{param1:1,param2[1,2]}","!toto{param1:1,param2[1,2]}")
 *
 * Obviously the previous example will not pass since we are using the same rule with a not (!) in the second test.
 * This will result in (true && !true) or (false && !false) evaluation.
 *
 * If you want to do a or check you can put this in a second array:
 *
 *      array(array("toto{param1:1,param2[1,2]}","!toto{param1:1,param2[1,2]}"))
 *
 * In this case the ending result will always result in a true result since the evaluation will be ((true || !true) or
 * ((false || !false))
 *
 * You can mix those:
 *
 *      array(rule1{},array(rule2{},rule3{}))
 *
 * This will result in ($rule1Result && ($rule2Result || $rule3Result))
 *
 * This system is inspire from Symfony 1 credentials checks http://symfony.com/legacy/doc/reference/1_4/en/08-Security
 * and the specification pattern http://en.wikipedia.org/wiki/Specification_pattern
 *
 * Check the unit test for more example of usage
 */
interface IBusinessRuleEngine
{
    /**
     * Set the rule provider
     *
     * @param IRuleProvider $ruleProvider
     *
     * @return void
     */
    public function setRuleProvider(IRuleProvider $ruleProvider);

    /**
     * Return the current rule provider
     *
     * @return IRuleProvider
     */
    public function getRuleProvider();

    /**
     * Return the index of the first rule specification that match or null if none match
     *
     * @param $ruleSpecifications
     * @param array $parameters That will be pass to each rule evaluation
     *
     * @throws RuleNotFoundException
     *
     * @return mixed|null
     */
    public function getFirstMatch($ruleSpecifications, array $parameters = array());

    /**
     * Return a array of all the index that match the rule specifications pass. If none
     * match a empty array will be return
     *
     * @param $ruleSpecifications
     * @param array $parameters That will be pass to each rule evaluation
     *
     * @throws RuleNotFoundException
     *
     * @return array
     */
    public function getAllMatches($ruleSpecifications, array $parameters = array());

    /**
     * Check if the rule specification is true of false
     *
     * @param $ruleSpecification
     * @param array $parameters That will be pass to each rule evaluation
     *
     * @throws RuleNotFoundException
     *
     * @return boolean
     */
    public function check($ruleSpecification, array $parameters = array());
}
