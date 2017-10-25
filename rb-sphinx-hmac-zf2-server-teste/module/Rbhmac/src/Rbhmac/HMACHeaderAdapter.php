<?php

namespace RB\Sphinx\Hmac\Zend\Server;

use Zend\Mvc\MvcEvent;
use Zend\Authentication\Result;
use RB\Sphinx\Hmac\HMAC;
use RB\Sphinx\Hmac\Algorithm\HMACv1;
use RB\Sphinx\Hmac\Hash\Sha256;
use RB\Sphinx\Hmac\Key\StaticKey;
use RB\Sphinx\Hmac\Nonce\DummyNonce;
use RB\Sphinx\Hmac\Exception\HMACException;
use Zend\Http\Request;

class HMACHeaderAdapter extends HMACAbstractAdapter {
	
	/**
	 * Nome do header
	 *
	 * @var string
	 */
	const HEADER_NAME = 'HMAC-Authentication';
	
	/**
	 * Versão atual do Adapter
	 *
	 * @var number
	 */
	const VERSION = 1;
	
	/**
	 * Versão do protocolo em uso
	 *
	 * @var number
	 */
	protected $version = self::VERSION;
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \RB\Sphinx\Hmac\Zend\Server\HMACAbstractAdapter::canHandle()
	 */
	public static function canHandle(Request $request) {
		/**
		 * Se requisição tiver o HEADER, tratar com este Adapter
		 */
		return $request->getHeaders ()->has ( self::HEADER_NAME );
	}
	
	/**
	 * Recuperar parâmetros de autenticação HMAC
	 *
	 * @param Request $request        	
	 * @return mixed
	 */
	protected function _getAuthData($request) {
		/**
		 * VERIFICAR HEADERS DO PROTOCOLO
		 */
		$headers = $request->getHeaders ();
		if (! $headers->has ( self::HEADER_NAME )) {
			throw new HMACException ( 'Missing ' . self::HEADER_NAME . ' header' );
		}
		
		$authentication = $headers->get ( self::HEADER_NAME )->getFieldValue ();
		$authData = explode ( ':', $authentication );
		
		if (count ( $authData ) != 4) {
			throw new HMACException ( 'Incorrect ' . self::HEADER_NAME . ' header' );
		}
		
		return $authData;
	}
	
	/**
	 * Selecionar informações que entram na verificação do HMAC
	 * 
	 * @param Request $request
	 * @param string $hmac
	 */
	protected function _validate($request, $hmac) {
		$this->hmac->validate ( $request->getMethod () . $request->getUriString () . $request->getContent (), $hmac );
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \RB\Sphinx\Hmac\Zend\Server\HMACAbstractAdapter::authenticate()
	 */
	public function authenticate(Request $request, $selector, $services, MvcEvent $e = null) {
		/**
		 * VERIFICAR ESTRUTURA DO HEADER
		 *
		 * Esperado:
		 * version:keyid:nonce:hmac
		 */
		try {
			$authData = $this->_getAuthData ( $request );
		} catch ( HMACException $exception ) {
			return new Result ( Result::FAILURE, null, array (
					$exception->getMessage () 
			) );
		}
		
		try {
			/**
			 * Instanciar HMAC
			 */
			$this->_initHmac ( $services, $selector );
			
			/**
			 * Tratar HEADER de acordo com a versão do protocolo
			 * 0 - version
			 */
			switch ($authData [0]) {
				case 1 :
					/**
					 * 1 - keyid
					 * 2 - nonce
					 * 3 - hmac
					 */
					$this->hmac->setKeyId ( $authData [1] );
					$this->hmac->setNonceValue ( $authData [2] );
					/**
					 * Calcular HMAC para
					 * - METHOD
					 * - URI(string)
					 * - CONTENT
					 */
					$this->_validate ( $request, $authData [3] );
					break;
				default :
					return new Result ( Result::FAILURE, null, array (
							self::HEADER_NAME . ' version not supported' 
					) );
			}
		} catch ( \Exception $e ) {
			return new Result ( Result::FAILURE_UNCATEGORIZED, null, array (
					$e->getCode () . ' - ' . $e->getMessage () 
			) );
		}
		
		return new Result ( Result::SUCCESS, array (
				'keyid' => $authData [1] 
		) );
	}
	public function signResponse(MvcEvent $event) {
		/**
		 * Se $hmac não estiver inicializado, não acrescentar assinatura, pois não é uma requisição HMAC
		 */
		if ($this->hmac === NULL)
			return;
		
		/**
		 * Response do Controller
		 */
		$response = $event->getTarget ()->getResponse ();
		
		/**
		 * Calcular HMAC em toda a resposta
		 */
		$body = $response->getContent ();
		
		try {
			$hmac = $this->hmac->getHmac ( $body );
		} catch( HMACException $e ) {
			/**
			 * Exceção ao assinar resposta deve ser ignorada pelo server. Cliente irá tratar
			 * falta de assinatura na resposta.
			 */
			return;
		}
		
		/**
		 * Acrescentar header com HMAC na resposta
		 */
		$response->getHeaders ()->addHeaderLine ( self::HEADER_NAME, static::VERSION . ':' . $hmac );
	}
}