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
use RB\Sphinx\Hmac\HMACSession;
use RB\Sphinx\Hmac\Exception\HMACAdapterInterruptException;
use Zend\Session\Container;
use Zend\Http\Response;
use Zend\Http\Request;

class HMACSessionHeaderAdapter extends HMACAbstractAdapter {
	
	/**
	 * Nome do header
	 *
	 * @var string
	 */
	const HEADER_NAME = 'HMAC-Authentication';
	const HEADER_NAME_SESSION = 'HMAC-Authentication-Session';
	
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
	 * Tipo da mensagem, utilizado para definir a formação da CHAVE HMAC
	 *
	 * @var number
	 */
	protected $dataType = NULL;
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \RB\Sphinx\Hmac\Zend\Server\HMACAbstractAdapter::canHandle()
	 */
	public static function canHandle(Request $request) {
		$headers = $request->getHeaders ();
		
		/**
		 * Se requisição não tiver o HEADER, retornar
		 */
		if (! $headers->has ( self::HEADER_NAME ))
			return false;
		
		/**
		 * VERIFICAR ESTRUTURA DO HEADER
		 *
		 * Esperado:
		 * version:keyid:nonce:hmac
		 */
		$authentication = $headers->get ( self::HEADER_NAME )->getFieldValue ();
		$authData = explode ( ':', $authentication );
		
		/**
		 * Início de sessão detectado pela presença do HEADER específico
		 */
		if (count ( $authData ) == 4 && $headers->has ( self::HEADER_NAME_SESSION ))
			return true;
		
		/**
		 * Mensagens após início da sessão tem apenas a versão e o HMAC
		 */
		if (count ( $authData ) == 2)
			return true;
		
		return false;
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \RB\Sphinx\Hmac\Zend\Server\HMACAbstractAdapter::authenticate()
	 */
	public function authenticate(Request $request, $selector, $services, MvcEvent $e = null) {
		
		/**
		 * VERIFICAR HEADERS DO PROTOCOLO
		 */
		$headers = $request->getHeaders ();
		if (! $headers->has ( self::HEADER_NAME )) {
			return new Result ( Result::FAILURE, null, array (
					'Missing ' . self::HEADER_NAME . ' header' 
			) );
		}
		
		/**
		 * VERIFICAR ESTRUTURA DO HEADER
		 *
		 * Esperado:
		 * version:keyid:nonce:hmac
		 */
		$authentication = $headers->get ( self::HEADER_NAME )->getFieldValue ();
		$authData = explode ( ':', $authentication );
		
		/**
		 * Início de sessão
		 */
		if (count ( $authData ) == 4 && $headers->has ( self::HEADER_NAME_SESSION ))
			$this->dataType = HMACSession::SESSION_REQUEST;
		
		/**
		 * Mensagem após início de sessão
		 */
		if (count ( $authData ) == 2)
			$this->dataType = HMACSession::SESSION_MESSAGE;
		
		/**
		 * HEADER em formato inválido
		 */
		if ($this->dataType === NULL) {
			return new Result ( Result::FAILURE, null, array (
					'Invalid ' . self::HEADER_NAME . ' header for HMAC Session' 
			) );
		}
		
		try {
			/**
			 * Instanciar HMAC ou recuperar da Sessão
			 */
			$session = new Container ( __CLASS__ );
			
			if (isset ( $session->hmac )) {
				$this->hmac = $session->hmac;
			} else {
				$this->_initHmac ( $services, $selector );
			}
			
			if( !method_exists($this->hmac, 'getNonce2Value') )
				throw new HMACException ( 'Protocolo com sessão requer HMACSession' );
				
			/**
			 * Verificar se é HMACSession
			 */
			if (! ($this->hmac instanceof HMACSession))
				throw new HMACException ( 'Protocolo com sessão requer HMACSession' );
			
			/**
			 * Tratar HEADER de acordo com a versão do protocolo
			 * 0 - version
			 */
			switch ($authData [0]) {
				case 1 : // Versão 1
					if ($this->dataType === HMACSession::SESSION_REQUEST) {
						/**
						 * 1 - keyid
						 * 2 - nonce
						 * 3 - hmac
						 */
						$this->hmac->setKeyId ( $authData [1] );
						$this->hmac->setNonceValue ( $authData [2] );
						$receivedHmac = $authData [3];
					} else {
						$receivedHmac = $authData [1]; // HMAC vem logo após a versão
					}
					
					/**
					 * Calcular HMAC para
					 * - METHOD
					 * - URI(string)
					 * - CONTENT
					 */
					$this->hmac->validate ( $request->getMethod () . $request->getUriString () . $request->getContent (), $receivedHmac, $this->dataType );
					
					if ($this->dataType == HMACSession::SESSION_REQUEST) {
						
						/**
						 * Não assinar a resposta APIGILITY REST neste momento (deixar para o onFinish)
						 */
						if( $e !== null ) {
							$response = $e->getTarget ()->getResponse ();
							$response->setContent ( $this->hmac->getDescription () );
							$response->getHeaders ()->addHeaderLine ( 'Content-Type', 'application/text' );
							
							/**
							 * Assinar NONCE2 para a resposta
							 */
							$this->signResponse ( $e );
						}
						
						/**
						 * Interromper requisição
						 */
						throw new HMACAdapterInterruptException ( 'Session Start Request Interrupt' );
					}
					
					break;
				default :
					return new Result ( Result::FAILURE, null, array (
							self::HEADER_NAME . ' version not supported' 
					) );
			}
		} catch ( HMACAdapterInterruptException $exception ) {
			throw $exception;
		} catch ( \Exception $e ) {
			return new Result ( Result::FAILURE_UNCATEGORIZED, null, array (
					$e->getCode () . ' - ' . $e->getMessage () 
			) );
		}
		
		return new Result ( Result::SUCCESS, array (
				'keyid' => $this->hmac->getKeyId () 
		) );
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \RB\Sphinx\Hmac\Zend\Server\HMACAbstractAdapter::signResponse()
	 */
	public function signResponse(MvcEvent $event) {
		/**
		 * Se $hmac não estiver inicializado, não acrescentar assinatura pois não é uma requisição HMAC
		 */
		if ($this->hmac === NULL)
			return;
		
		try {
		
			/**
			 * Response do Controller
			 */
			$response = $event->getTarget ()->getResponse ();
			
			/**
			 * Recuperar sessão
			 */
			$session = new Container ( __CLASS__ );
			
			switch ($this->dataType) {
				case HMACSession::SESSION_MESSAGE :
					/**
					 * Calcular HMAC em toda a resposta
					 */
					$body = $response->getContent ();
					$hmac = $this->hmac->getHmac ( $body, HMACSession::SESSION_MESSAGE );
					
					/**
					 * Após assinar mensagem atual, incrementar contador para aguardar próxima mensagem e salvar na sessão
					 * Apenas se for uma requisição válida
					 */
					if( $response->getStatusCode() >= 200 && $response->getStatusCode() <= 299) {
						$this->hmac->nextMessage ();
						$session->hmac = $this->hmac;
					}
					break;
				case HMACSession::SESSION_REQUEST :
				case HMACSession::SESSION_RESPONSE :
					/**
					 * Calcular HMAC apenas do NONCE2 para responder ao pedido de início de sessão
					 */
					if( method_exists($this->hmac, 'getNonce2Value') ) {
						$hmac = $this->hmac->getHmac ( $this->hmac->getNonce2Value (), HMACSession::SESSION_RESPONSE );
					
						/**
						 * Iniciar sessão HMAC e guardar na sessão PHP
						 */
						$this->hmac->startSession ();
						$session->hmac = $this->hmac;
					}
					
					break;
				default :
					throw new HMACException ( 'HMAC Message Type Error' );
			}
			
			/**
			 * Acrescentar header com HMAC na resposta
			 */
			if( method_exists($this->hmac, 'getNonce2Value') ) {
				if ($this->dataType == HMACSession::SESSION_REQUEST) {
					$response->getHeaders ()->addHeaderLine ( self::HEADER_NAME, static::VERSION . ':' . $this->hmac->getNonce2Value () . ':' . $hmac );
				} else {
					$response->getHeaders ()->addHeaderLine ( self::HEADER_NAME, static::VERSION . ':' . $hmac );
				}
			}
		} catch( HMACException $e ) {
			/**
			 * Exceção ao assinar resposta deve ser ignorada pelo server. Cliente irá tratar
			 * falta de assinatura na resposta.
			 */
			return;
		}
	}
}