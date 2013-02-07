<?php
namespace Everyman\Neo4j\Transport;
use Everyman\Neo4j\Transport as BaseTransport,
    Everyman\Neo4j\Version;

/**
 * Class for communicating with an HTTP JSON endpoint
 */
class Curl extends BaseTransport
{
	protected $handle = null;

	/**
	 * @inherit
	 */
	public function __construct($host='localhost', $port=7474)
	{
		if (! function_exists('curl_init')) {
			throw new Exception('cUrl extension not enabled/installed');
		}

		parent::__construct($host, $port);
	}

	/**
	 * Make sure the curl handle closes when we are done with the Transport
	 */
	public function __destruct()
	{
		if ($this->handle) {
			curl_close($this->handle);
		}
	}

	/**
	 * @inherit
	 */
	public function makeRequest($method, $path, $data=array())
	{
		$url = $this->getEndpoint().$path;

		$options = array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER => true,
			CURLOPT_HTTPHEADER => array(
				'Accept: application/json',
				'Content-type: application/json',
				'User-Agent: '.Version::userAgent(),
			),
			CURLOPT_CUSTOMREQUEST => self::GET,
			CURLOPT_POST => false,
			CURLOPT_POSTFIELDS => null,
		);

		if ($this->username && $this->password) {
			$options[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
			$options[CURLOPT_USERPWD] = $this->username.':'.$this->password;
		}

		switch ($method) {
			case self::DELETE :
				$options[CURLOPT_CUSTOMREQUEST] = self::DELETE;
				break;

			case self::POST :
				$dataString = $this->encodeData($data);
				$options[CURLOPT_CUSTOMREQUEST] = self::POST;
				$options[CURLOPT_POST] = true;
				$options[CURLOPT_POSTFIELDS] = $dataString;
				$options[CURLOPT_HTTPHEADER][] = 'Content-Length: '.strlen($dataString);
				break;

			case self::PUT :
				$dataString = $this->encodeData($data);
				$options[CURLOPT_CUSTOMREQUEST] = self::PUT;
				$options[CURLOPT_POSTFIELDS] = $dataString;
				$options[CURLOPT_HTTPHEADER][] = 'Content-Length: '.strlen($dataString);
				break;
		}

		$ch = $this->getHandle();
		curl_setopt_array($ch, $options);

		$response = curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

		if (!$code) {
			$code = 500;
			$headerSize = 0;
			$response = json_encode(array("error"=>curl_error($ch).' ['.curl_errno($ch).']'));
		}

		$bodyString = substr($response, $headerSize);
		$bodyData = json_decode($bodyString, true);

		$headerString = substr($response, 0, $headerSize);
		$headers = explode("\r\n",$headerString);
		foreach ($headers as $i => $header) {
			unset($headers[$i]);
			$parts = explode(':',$header);
			if (isset($parts[1])) {
				$name = trim(array_shift($parts));
				$value = join(':',$parts);
				$headers[$name] = $value;
			}
		}

		return array(
			'code' => $code,
			'headers' => $headers,
			'data' => $bodyData,
		);
	}

	/**
	 * Get the cURL handle
	 *
	 * @return resource cURL handle
	 */
	protected function getHandle()
	{
		if (!$this->handle) {
			$this->handle = curl_init();
		}
		return $this->handle;
	}
}
