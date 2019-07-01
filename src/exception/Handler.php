<?php

namespace tpr\exception;

use tpr\Container;
use tpr\exception\handler\JsonpHandler;
use tpr\App;
use tpr\exception\handler\DefaultHandler;
use Whoops\Handler\HandlerInterface;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\XmlResponseHandler;
use Whoops\Run;
use tpr\core\Response;

class Handler
{
    /**
     * @var Run
     */
    private static $run;

    private static $handle_list = [
        'default' => DefaultHandler::class,
        'html'    => PrettyPageHandler::class,
        'text'    => PlainTextHandler::class,
        'json'    => JsonResponseHandler::class,
        'jsonp'   => JsonpHandler::class,
        'xml'     => XmlResponseHandler::class,
    ];

    private static $handler_type = 'default';

    public static function init()
    {
        if (is_null(self::$run)) {
            self::$run = new Run();
            self::$run->allowQuit();

            if (!App::debug()) {
                self::$handler_type = 'default';
            } else {
                self::$handler_type = Container::response()->getResponseType();
            }
            self::addHandler(self::$handler_type);
            self::handleOperator()->register();
        }
    }

    /**
     * @param          $exception
     * @param Response $response
     */
    public static function render($exception, $response)
    {
        if (App::debug() && $response->getResponseType() !== self::$handler_type) {
            self::$handler_type = $response->getResponseType();
            if (isset(self::$handle_list[self::$handler_type])) {
                self::handleOperator()->clearHandlers();
                self::addHandler(self::$handler_type);
                self::handleOperator()->register();
            }
        }

        throw $exception;
    }

    public static function addHandler($handler)
    {
        if (is_string($handler)) {
            if (isset(self::$handle_list[$handler])) {
                $handler = self::$handle_list[$handler];
            }

            if (!class_exists($handler)) {
                throw new ClassNotExistException('Class Not Exist : ' . $handler);
            }

            $handler = new $handler();
        }

        if (is_object($handler)) {
            /*** @var HandlerInterface $handler ** */
            self::$run->appendHandler($handler);
        }
    }

    /**
     * @return Run
     */
    public static function handleOperator()
    {
        return self::$run;
    }
}
