<?php

namespace Nucleus\BusinessRuleEngine;

/**
 * Class IBusinessRuleEngine
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 *
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
