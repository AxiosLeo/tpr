<?php
/**
 * @author  : axios
 *
 * @email   : axiosleo@foxmail.com
 * @blog    : http://hanxv.cn
 * @datetime: 2019-06-22 16:01
 */

namespace tpr\exception\handler;

use tpr\Response;
use Whoops\Exception\Formatter;
use Whoops\Handler\Handler;

class JsonpHandler extends Handler
{
    /**
     * @var bool
     */
    private $returnFrames = false;

    /**
     * @var bool
     */
    private $jsonApi = false;

    /**
     * Returns errors[[]] instead of error[] to be in compliance with the json:api spec
     *
     * @param bool $jsonApi Default is false
     *
     * @return $this
     */
    public function setJsonApi($jsonApi = false)
    {
        $this->jsonApi = (bool)$jsonApi;
        return $this;
    }

    /**
     * @param  bool|null $returnFrames
     *
     * @return bool|$this
     */
    public function addTraceToOutput($returnFrames = null)
    {
        if (func_num_args() == 0) {
            return $this->returnFrames;
        }

        $this->returnFrames = (bool)$returnFrames;
        return $this;
    }

    /**
     * @return int
     */
    public function handle()
    {
        if ($this->jsonApi === true) {
            $response = [
                'errors' => [
                    Formatter::formatExceptionAsDataArray(
                        $this->getInspector(),
                        $this->addTraceToOutput()
                    ),
                ]
            ];
        } else {
            $response = [
                'error' => Formatter::formatExceptionAsDataArray(
                    $this->getInspector(),
                    $this->addTraceToOutput()
                ),
            ];
        }

        echo Response::instance()->setResponseType("jsonp")->output($response);;

        return Handler::QUIT;
    }

    /**
     * @return string
     */
    public function contentType()
    {
        return 'application/javascript';
    }
}