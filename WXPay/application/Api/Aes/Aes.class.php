<?php
namespace Api\Aes;

header('content-type:text/html;charset=utf-8;');

class Aes
{
	// CRYPTO_CIPHER_BLOCK_SIZE 32

	private $_secret_key = 'hwm20160615';

	public function setKey($key)
	{
		$this->_secret_key = $key;
	}

	public function encode($data)
	{
		$td = mcrypt_module_open(MCRYPT_RIJNDAEL_256 , '' , MCRYPT_MODE_CBC , '');
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td) , MCRYPT_RAND);
		mcrypt_generic_init($td , $this->_secret_key , $iv);
		$encrypted = mcrypt_generic($td , $data);
		mcrypt_generic_deinit($td);

		return base64_encode($iv . $encrypted);
	}

	public function decode($data)
	{
		$data = base64_decode($data);
		$td = mcrypt_module_open(MCRYPT_RIJNDAEL_256 , '' , MCRYPT_MODE_CBC , '');
		$iv = mb_substr($data , 0 , 32 , 'latin1');
		mcrypt_generic_init($td , $this->_secret_key , $iv);
		$data = mb_substr($data , 32 , mb_strlen($data , 'latin1') , 'latin1');
		$data = mdecrypt_generic($td , $data);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);

		return trim($data);
	}
}
