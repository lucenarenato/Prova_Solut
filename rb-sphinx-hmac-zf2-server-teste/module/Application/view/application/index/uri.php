<?php

/**
 * Biblioteca HMAC
 */
use RB\Sphinx\Hmac\HMAC;
use RB\Sphinx\Hmac\Algorithm\HMACv0;
use RB\Sphinx\Hmac\Hash\PHPHash;
use RB\Sphinx\Hmac\Key\StaticKey;
use RB\Sphinx\Hmac\Nonce\DummyNonce;

/**
 * Implementação para ZF2
 */
use RB\Sphinx\Hmac\Zend\Client\HMACHttpClient;
use RB\Sphinx\Hmac\Zend\Server\HMACHeaderAdapter;
use RB\Sphinx\Hmac\Hash\DummyHash;

/**
 * Autoloader Composer
 */
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * URI do servidor que requer HMAC
 */
$uri = "http://hmac-teste1.localdomain/application/api/api4";

/**
 * Configurar HMAC de acordo com a configuração do servidor
 */
$hmac = new HMAC(
			new HMACv0(),								// Protocolo de cálculo do HMAC
			new PHPHash('sha256'),						// Protocolo de Hash utilizado
			new StaticKey( '[PRE-SHARED KEY]' ),		// Tipo da chave
			new DummyNonce()							// Tipo do Nonce
		);

/**
 * Defina o identificador do cliente (identificador da chave de autenticação)
 */
$hmac->setKeyId( 'APPid123' );

/**
 * DummyNonce requer um valor fixo para o NONCE
 */
$hmac->setNonceValue('meuNonce');

/**
 * Iniciar HTTP Client
 * É uma extensão do Zend\Http\Client e pode ser usado da mesma forma, bastando apenas
 * informar o objeto HMAC.
 */
$cliente = new HMACHttpClient( $uri );
$cliente->setMethod('GET');
$cliente->setHmac($hmac);

$cliente->setParameterGet(['teste2'=>'sim2']);

/**
 * Definir modo de autenticação na URI
 */
$cliente->setHmacMode( HMACHttpClient::HMAC_URI );

/**
 * URI Autenticada
 */
$signedUri = $cliente->getSignedUri();

/**
 * Enviar requisição
 */
try {
	$cliente->send();
	
	/**
	 * Resposta
	 */
	echo "Request URI: ", $uri, PHP_EOL, PHP_EOL;
	
	echo "Request Signed URI: ", $signedUri, PHP_EOL, PHP_EOL;
	
	echo "Request Signed URI: ", $cliente->getSignedUri(), PHP_EOL, PHP_EOL;
	
} catch (Exception $e) {
	/**
	 * ERRO
	 */
	echo "##### ERRO #####", PHP_EOL;
	echo $e->getCode(), ' : ', $e->getMessage(), PHP_EOL;
	echo "##### ERRO #####", PHP_EOL, PHP_EOL;
}

$response = $cliente->getResponse();

echo "Response Status Code: ", $response->getStatusCode(), PHP_EOL, PHP_EOL;

echo "Response Headers: ";
print_r( $response->getHeaders()->toArray() );
echo PHP_EOL;

echo "Response Cookies:", PHP_EOL;
$cookies = $response->getCookie();
foreach ($cookies as $cookie) {
	echo '     ', $cookie->toString(), PHP_EOL;
}
echo PHP_EOL;

echo "Response Body:", PHP_EOL;
echo $response->getBody();
echo PHP_EOL, PHP_EOL;

// EOF