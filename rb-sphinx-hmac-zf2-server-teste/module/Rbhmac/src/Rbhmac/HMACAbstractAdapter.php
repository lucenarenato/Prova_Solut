<?php

namespace RB\Sphinx\Hmac\Zend\Server;

use Zend\Mvc\MvcEvent;
use Zend\Http\Request;

abstract class HMACAbstractAdapter {
	
	/**
	 * Versão atual do Adapter
	 *
	 * @var number
	 */
	const VERSION = - 1;
	
	/**
	 *
	 * @var \RB\Sphinx\Hmac\HMAC
	 */
	protected $hmac;
	
	/**
	 * Informa se o adapter pode tratar a requisição.
	 *
	 * @param Request $request
	 * @return boolean
	 */
	public static function canHandle(Request $request) {
		return false;
	}
	
	/**
	 * Autentica mensagem HMAC recebida.
	 * Dispara exceção se a autenticação falhar.
	 * 
	 * @param Request $request
	 * @param unknown $selector
	 * @param unknown $services
	 * @param MvcEvent $e
	 */
	public abstract function authenticate(Request $request, $selector, $services, MvcEvent $e = null);
	
	/**
	 * Assina resposta HMAC.
	 *
	 * @param MvcEvent $e        	
	 * @param string $selector        	
	 */
	public abstract function signResponse(MvcEvent $e);
	
	/**
	 * Retorna descrição do HMAC (protocolo, hash, nonce)
	 * 
	 * @return string
	 */
	public function getHmacDescription() {
		if ($this->hmac !== NULL)
			return $this->hmac->getDescription ();
		return NULL;
	}
	
	/**
	 * Utiliza o ServiceManager para instanciar o HMAC
	 */
	public function _initHmac($services, $selector) {
		if ($this->hmac === NULL) {
			$this->hmac = $services->get ( $selector );
		}
	}
	
	/**
	 * Informa versão do protocolo.
	 * 
	 * @return string
	 */
	public function getVersion() {
		return static::VERSION;
	}
	
	/**
	 * Assinar mensagem antes de enviar.
	 *
	 * @param string $data        	
	 * @return string|NULL
	 */
	public function sign($data) {
		if ($this->hmac !== NULL)
			return $this->hmac->getHmac ( $data );
		return NULL;
	}
}