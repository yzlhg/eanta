<?php
/**
 * @todo 当有未捕获的异常, 则控制流会流到这里
 */
class ErrorController extends Controller {
 /**
  * 也可通过$request->getException()获取到发生的异常
  */
	public function errorAction($exception) {
            assert($exception === $exception->getCode());
	}
}