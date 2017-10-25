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

/**
 * Autoloader Composer
 */
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * URI do servidor que requer HMAC
 */
$uri = "http://hmac-teste1.localdomain/application/api/api1";

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

/**
 * Conteúdo da requisição
 */
$cliente->setRawBody('Mensagem');

/**
 * Enviar requisição
 */
try {
	$cliente->send();
	
	/**
	 * Resposta
	 */
	echo "Request HMAC Header:", PHP_EOL;
	echo '     ', HMACHttpClient::HEADER_NAME, ' = ', $cliente->getHeader( HMACHttpClient::HEADER_NAME ), PHP_EOL, PHP_EOL;

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