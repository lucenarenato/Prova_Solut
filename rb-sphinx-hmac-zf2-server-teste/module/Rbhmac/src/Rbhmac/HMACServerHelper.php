<?php

namespace RB\Sphinx\Hmac\Zend\Server;

/**
 *
 * @author reinaldo
 *        
 */
class HMACServerHelper {
	
	/**
	 * 
	 * @param \Zend\Mvc\MvcEvent|\ZF\Rest\ResourceEvent $event
	 * @return \RB\Sphinx\Hmac\Zend\Server\HMACAbstractAdapter|NULL
	 */
	static public function getHmacAdapter($event) {
		return $event->getParam ( 'RBSphinxHmacAdapter', NULL );
	}
	
	/**
	 * 
	 * @param \Zend\Mvc\MvcEvent|\ZF\Rest\ResourceEvent $event
	 * @return string|NULL
	 */
	static public function getHmacKeyId($event) {
		$keyId = $event->getParam ( 'RBSphinxHmacAdapterIdentity', NULL );
		if ($keyId !== NULL)
			return $keyId['keyid'];
		
		return NULL;
	}

}
