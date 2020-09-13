<?php

declare(strict_types=1);

namespace tpr\exception\handler;

use tpr\Container;
use Whoops\Exception\Formatter;
use Whoops\Handler\Handler;

class JsonpHandler extends Handler
{
    private bool $returnFrames = false;

    private bool $jsonApi = false;

    /**
     * Returns errors[[]] instead of error[] to be in compliance with the json:api spec.
     *
     * @param bool $jsonApi Default is false
     *
     * @return $this
     */
    public function setJsonApi($jsonApi = false)
    {
        $this->jsonApi = (bool) $jsonApi;

        return $this;
    }

    /**
     * @param null|bool $returnFrames
     *
     * @return $this|bool
     */
    public function addTraceToOutput($returnFrames = null)
    {
        if (0 == \func_num_args()) {
            return $this->returnFrames;
        }

        $this->returnFrames = (bool) $returnFrames;

        return $this;
    }

    /**
     * @throws \Exception
     *
     * @return null|int
     */
    public function handle()
    {
        if (true === $this->jsonApi) {
            $response = [
                'errors' => [
                    Formatter::formatExceptionAsDataArray(
                        $this->getInspector(),
                        $this->addTraceToOutput()
                    ),
                ],
            ];
        } else {
            $response = [
                'error' => Formatter::formatExceptionAsDataArray(
                    $this->getInspector(),
                    $this->addTraceToOutput()
                ),
            ];
        }

        echo Container::response()->setType('jsonp')->output($response);

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
