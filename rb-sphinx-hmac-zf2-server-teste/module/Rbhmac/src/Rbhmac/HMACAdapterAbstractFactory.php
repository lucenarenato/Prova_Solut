<?php

namespace RB\Sphinx\Hmac\Zend\Server;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * EXEMPLO DE ABSTRACT FACTORY PARA HMAC
 *
 * Configuração do module.config.php do seu módulo/aplicação:
 * 	'service_manager' => array (
 * 		'abstract_factories' => array (
 * 			'Hmac\HMACAbstractFactory'
 * 		)
 * 	)
 *
 *
 * @author reinaldo
 *        
 */
abstract class HMACAdapterAbstractFactory implements AbstractFactoryInterface {
	
	/**
	 * Determine if we can create a service with name
	 *
	 * @param ServiceLocatorInterface $serviceLocator        	
	 * @param
	 *        	$name
	 * @param
	 *        	$requestedName
	 * @return bool
	 */
	public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName) {
		switch ($requestedName) {
			case 'MyApp\Factories\SimpleHMACFactory' :
			case 'MyApp\Factories\HMACSessionFactory' :
				return true;
		}
		
		return false;
	}
	
	/**
	 * Create service with name
	 *
	 * @param ServiceLocatorInterface $serviceLocator        	
	 * @param
	 *        	$name
	 * @param
	 *        	$requestedName
	 * @return mixed
	 */
	public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName) {
		switch ($requestedName) {
			case 'MyApp\Factories\SimpleHMACFactory' :
			// return new MyAppClass( ... )
		}
		
		return NULL;
	}
}