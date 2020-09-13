<?php

declare(strict_types=1);

namespace tpr\core\response;

use Exception;
use InvalidArgumentException;

class Jsonp extends ResponseAbstract
{
    public string $content_type = 'application/javascript';

    public function output($data = null): string
    {
        try {
            $data = json_encode($data, $this->options->json_options);

            if (false === $data) {
                throw new InvalidArgumentException(json_last_error_msg());
            }

            return $this->options->jsonp_handler . '(' . $data . ');';
        } catch (Exception $e) {
            if ($e->getPrevious()) {
                throw $e->getPrevious();
            }

            throw $e;
        }
    }
}
