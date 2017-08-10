<?php
class SystemHandlers
{
	private $displayErrors;


	public function __construct($displayErrors = false)
	{
		$this->displayErrors = $displayErrors;

		set_exception_handler([$this, 'handleException']);
		set_error_handler([$this, 'handleError'], error_reporting());
	}

	public function handleError($code, $message, $file, $line)
	{
		if ($code & error_reporting()) {
			restore_error_handler();
			restore_exception_handler();

			try {
				$this->displayError($code, $message, $file, $line);
			} catch(Except $e) {
				$this->displayException($e);
			}
		}
	}

	public function handleException($exception)
	{
		restore_error_handler();
		restore_exception_handler();

		$this->displayException($exception);
	}

	private function displayError($code, $message, $file, $line)
	{
		if ($this->displayErrors) {
			$content = '
				<h1>Ошибка PHP[' . $code . ']!</h1>
				<p>' . $message . ' (' . $file . ' : ' . $line . ')</p>
				<pre>';

			$trace = debug_backtrace();

			foreach ($trace as $key => $value) {
				$file = isset($value['file']) ? $value['file'] : 'unknown';
				$line = isset($value['line']) ? $value['line'] : 'unknown';
				$func = isset($value['function']) ? $value['function'] : 'unknown';
				
				$content .= '#' . $key . ' (' . $file . ' : ' . $line . '): ';

				$object = $value['object'];

				if (isset($object) && is_object($object)) {
					$content .= get_class($object) . ' => ';
				}
				
				$content .= $func . '<br/>';
			}
			
			$content .= '</pre>';
		}

		$this->sendError($content, 500, null);
	}

	private function displayException($except)
	{
		if ($except instanceof Except) {
			$text = $except->getStatusText();
			$code = $except->getStatusCode();
		} else {
			$text = $except->getMessage();
			$code = 500;
		}

		if ($this->displayErrors) {
			$content = '
				<h1>Ошибка ' . $code . '</h1>
				<h3>' . $text . '</h3>
				<p>(' . $except->getFile() . ' : ' . $except->getLine() . ')</p>
				<pre>' . $except->getTraceAsString() . '</pre>
			';
		}

		$this->sendError($content, $code, $text);
	}

	private function sendError($content, $code, $text)
	{
		if (!isset($content)) {
			$content = '
				<div style="width: 100%; margin-top: 100px; text-align: center; font-family: sans-serif;">
					<div style="font-size: 64px; color: red;">×</div>
					<h1>' . $code . '</h1>
					<p>' . $text . '</p>
				</div>
			';
		}

		$response = new Response($content, $code, $text);
		$response->send();
	}
}