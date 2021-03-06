<?php
namespace Vivo\InputFilter;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

class InputFilterFactoryFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $validatorPluginManager = $serviceLocator->get('validator_manager');
        $defaultValidatorChain  = new \Zend\Validator\ValidatorChain();
        $defaultValidatorChain->setPluginManager($validatorPluginManager);

        $filterPluginManager    = $serviceLocator->get('filter_manager');
        $defaultFilterChain     = new \Zend\Filter\FilterChain();
        $defaultFilterChain->setPluginManager($filterPluginManager);

        $conditionPluginManager = $serviceLocator->get('input_filter_conditions');

        $inputFilterFactory     = new Factory($conditionPluginManager);
        $inputFilterFactory->setDefaultValidatorChain($defaultValidatorChain);
        $inputFilterFactory->setDefaultFilterChain($defaultFilterChain);
        return $inputFilterFactory;
    }
}
