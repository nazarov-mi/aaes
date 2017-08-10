<?php

class Response
{
	public static $statusTexts = array( // Позаимствовано у Symfony
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing',            // RFC2518
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-Status',          // RFC4918
		208 => 'Already Reported',      // RFC5842
		226 => 'IM Used',               // RFC3229
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',
		308 => 'Permanent Redirect',    // RFC7238
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Payload Too Large',
		414 => 'URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Range Not Satisfiable',
		417 => 'Expectation Failed',
		418 => 'I\'m a teapot',                                               // RFC2324
		421 => 'Misdirected Request',                                         // RFC7540
		422 => 'Unprocessable Entity',                                        // RFC4918
		423 => 'Locked',                                                      // RFC4918
		424 => 'Failed Dependency',                                           // RFC4918
		425 => 'Reserved for WebDAV advanced collections expired proposal',   // RFC2817
		426 => 'Upgrade Required',                                            // RFC2817
		428 => 'Precondition Required',                                       // RFC6585
		429 => 'Too Many Requests',                                           // RFC6585
		431 => 'Request Header Fields Too Large',                             // RFC6585
		451 => 'Unavailable For Legal Reasons',                               // RFC7725
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		506 => 'Variant Also Negotiates (Experimental)',                      // RFC2295
		507 => 'Insufficient Storage',                                        // RFC4918
		508 => 'Loop Detected',                                               // RFC5842
		510 => 'Not Extended',                                                // RFC2774
		511 => 'Network Authentication Required',                             // RFC6585
	);

	private $content;
	private $origin;
	private $version;
	private $statusCode;
	private $statusText;
	private $headers;
	private $charset;


	public function __construct($content = null, $code = 200, $text = null, $charset = 'UTF-8')
	{
		$this->setContent($content);
		$this->setStatus($code, $text);
		$this->setCharset($charset);
		$this->setProtocolVersion('1.0');

		$this->headers = [];
	}

	public function send()
	{
		if (array_key_exists('Content-Type', $this->headers)) {
			header('Content-Type: text/html; charset=' . $this->charset, false, $this->statusCode);
		}

		foreach ($this->headers as $name => $value) {
			header($name . ': ' . $value, false, $this->statusCode);
		}

		header(sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText), true, $this->statusCode);
		
		echo $this->content;
		exit();
	}


	// GETTERS / SETTERS


	public function setContent($content)
	{
		if (is_array($content) || is_object($content)) {
			$this->content = json_encode($content);
		} else {
			if ($content !== null && !is_string($content) && !is_numeric($content) && !is_callable(array($content, '__toString'))) {
				throw new Except('Content должен быть строкой, числом, массивом или объектом с функцией __toString');
			}

			$this->content = (string) $content;
		}

		$this->origin = $content;

		return $this;
	}

	public function getContent()
	{
		return $this->origin;
	}

	public function setStatus($code, $text = null)
	{
		if ($code < 100 || $code >= 600) {
			throw new Except('HTTP код ' . $code . ' невалидный');
		}

		if ($text === null) {
			$this->statusText = isset(self::$statusTexts[$code]) ? self::$statusTexts[$code] : 'unknown';
		} else
		if ($text === false) {
			$this->statusText = '';
		} else {
			$this->statusText = $text;
		}

		$this->statusCode = $code;

		return $this;
	}

	public function getStatusCode()
	{
		return $this->statusCode;
	}

	public function getStatusText()
	{
		return $this->statusText;
	}

	public function setCharset($charset)
	{
		$this->charset = $charset ?: 'UTF-8';

		return $this;
	}

	public function getCharset()
	{
		return $this->charset;
	}

	public function setProtocolVersion($version)
	{
		$this->version = $version;

		return $this;
	}

	public function getProtocolVersion()
	{
		return $this->version;
	}

	public function setHeader($name, $value)
	{
		$this->headers[$name] = $value;

		return $this;
	}

	public function setHeaders(array $headers)
	{
		foreach ($headers as $name => $value) {
			$this->setHeader($name, $value);
		}

		return $this;
	}

	public function getHeaders()
	{
		return $this->headers;
	}
}