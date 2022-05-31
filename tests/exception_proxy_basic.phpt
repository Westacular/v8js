--TEST--
Test V8::setExceptionProxyFactory() : Simple test
--SKIPIF--
<?php require_once(dirname(__FILE__) . '/skipif.inc'); ?>
--FILE--
<?php
class myv8 extends V8Js
{
	public function throwException(string $message) {
		throw new Exception($message);
	}
}

class ExceptionProxy {
	private $ex;

	public function __construct(Throwable $ex) {
		echo "ExceptionProxy::__construct called!\n";
		var_dump($ex->getMessage());

		$this->ex = $ex;
	}

	public function getMessage() {
		echo "getMessage called\n";
		return $this->ex->getMessage();
	}
}

$v8 = new myv8();
$v8->setExceptionProxyFactory(function (Throwable $ex) {
	echo "exception proxy factory called.\n";
	return new ExceptionProxy($ex);
});

$v8->executeString('
	try {
		PHP.throwException("Oops");
	}
	catch (e) {
		var_dump(e.getMessage()); // calls ExceptionProxy::getMessage
		var_dump(typeof e.getTrace);
	}
', null, V8Js::FLAG_PROPAGATE_PHP_EXCEPTIONS);
?>
===EOF===
--EXPECT--
exception proxy factory called.
ExceptionProxy::__construct called!
string(4) "Oops"
getMessage called
string(4) "Oops"
string(9) "undefined"
===EOF===

