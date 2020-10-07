<?php

declare(strict_types=1);

namespace tpr;

use tpr\core\request\RequestInterface;
use tpr\core\Response;
use tpr\models\ResponseModel;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Class Controller.
 *
 * @method string        getType()
 * @method Response      setType(string $response_type)
 * @method ResponseModel options()
 * @method Response      addDriver(string $response_type, string $driver)
 * @method array         getHeaders()
 * @method Response      setHeaders(array $headers, bool $cover = false)
 * @method string        getHeader(string $key)
 * @method Response      setHeader(string $key, string $value)
 * @method Response      assign(string $key, $value)
 * @method void          addTemplateFunc(string $name, callable $func)
 * @method void          response($result = '', $status = 200, $msg = '', array $headers = [])
 * @method void          success($data = [])
 * @method void          error($code = 500, $msg = 'error')
 * @method string        fetch(string $template = '', array $vars = [])
 */
abstract class Controller
{
    protected ?RequestInterface $request = null;

    private Response $response;

    public function __construct()
    {
        if (Container::has('request')) {
            $this->request = Container::request();
        }
        Container::bindWithObj('response', new Response());
        $this->response = Container::response();
        if (!Container::has('template')) {
            Container::bindWithObj('template', new Environment(new FilesystemLoader(Path::views()), []));
        }
    }

    public function __call($name, $arguments)
    {
        return \call_user_func_array([$this->response, $name], $arguments);
    }

    protected function removeHeaders($headers = [])
    {
        if (\is_string($headers)) {
            $headers = [$headers];
        }
        if (empty($headers)) {
            $headers = App::drive()->getConfig()->remove_headers;
        }
        if (!headers_sent() && !empty($headers)) {
            foreach ($headers as $header) {
                header_remove($header);
            }
        }
    }

    protected function redirect(string $destination, bool $permanent = true)
    {
        if (false === strpos($destination, '://')) {
            $protocol    = 'https' === $this->request->scheme() ? 'https' : 'http';
            $destination = $protocol . '://' . $this->request->host() . $destination;
        }

        if (true === $permanent) {
            $code    = 301;
            $message = $code . ' Moved Permanently';
        } else {
            $code    = 302;
            $message = $code . ' Found';
        }

        header('HTTP/1.1 ' . $message, true, $code);
        header('Status: ' . $message, true, $code);

        header('Location: ' . $destination);
        exit();
    }
}
