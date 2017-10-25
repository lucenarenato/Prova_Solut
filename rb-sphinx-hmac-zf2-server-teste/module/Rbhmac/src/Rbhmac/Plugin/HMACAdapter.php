<?php

namespace RB\Sphinx\Hmac\Zend\Server\Plugin;

use RB\Sphinx\Hmac\Zend\Server\HMACServerHelper;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\Controller\AbstractController;

class HMACAdapter extends AbstractPlugin {
	/**
	 * Retorna o Adapter HMAC utilizado no HMAC
	 */
	public function __invoke() {
		if ($this->getController() instanceof AbstractController)
			return HMACServerHelper::getHmacAdapter($this->getController()->getEvent());
		
		return NULL;
	}
}
