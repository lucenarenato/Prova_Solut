<?php

namespace RB\Sphinx\Hmac\Zend\Server;

use Zend\Mvc\MvcEvent;
use Zend\Http\Request;
use Zend\Authentication\Result;

use RB\Sphinx\Hmac\HMAC;
use RB\Sphinx\Hmac\Algorithm\HMACv1;
use RB\Sphinx\Hmac\Hash\Sha256;
use RB\Sphinx\Hmac\Key\StaticKey;
use RB\Sphinx\Hmac\Nonce\DummyNonce;
use RB\Sphinx\Hmac\Exception\HMACException;

class HMACUriAdapter extends HMACHeaderAdapter {
	
	/**
	 * Nome do header
	 *
	 * @var string
	 */
	const URI_PARAM_NAME = 'hmacauthentication';
	
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
		 * Se requisição tiver o parâmetro na URI, tratar com este Adapter
		 */
		return ($request->getQuery ( self::URI_PARAM_NAME, NULL ) !== NULL);
	}
	
	/**
	 * Recuperar parâmetros de autenticação HMAC
	 *
	 * @param Request $request        	
	 * @return mixed
	 */
	protected function _getAuthData($request) {
		/**
		 * VERIFICAR QUERY DO PROTOCOLO
		 */
		$uriParam = $request->getQuery ( self::URI_PARAM_NAME, NULL );
		if ($uriParam === null) {
			throw new HMACException ( 'Missing ' . self::URI_PARAM_NAME . ' parameter' );
		}
		
		$authData = explode ( ':', $uriParam );
		
		if (count ( $authData ) != 4) {
			throw new HMACException ( 'Incorrect ' . self::URI_PARAM_NAME . ' param' );
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
		$uriParam = $request->getUriString ();
		
		/**
		 * IDENTIFICAR URI_PARAM_NAME E SEU VALOR NA URI
		 */
		$pattern = '/[?&]' . self::URI_PARAM_NAME . '=[^&]+$|([?&])' . self::URI_PARAM_NAME . '=[^&]+&/';
		$uriParam = preg_replace ( $pattern, '$1', $uriParam );
		
		/**
		 * CHECAR ASSINATURA DA URI SEM O PARÂMETRO DE AUTENTICAÇÃO DO HMAC
		 */
		$this->hmac->validate ( $uriParam, $hmac );
	}
}