<?php

declare(strict_types=1);

namespace tpr\core\response;

use Exception;
use InvalidArgumentException;

class Json extends ResponseAbstract
{
    public string    $content_type = 'application/json';

    public function output($data = null): string
    {
        try {
            $data = json_encode($data, $this->options->json_options);
            if (false === $data) {
                throw new InvalidArgumentException(json_last_error_msg());
            }

            return $data;
        } catch (Exception $e) {
            if ($e->getPrevious()) {
                throw $e->getPrevious();
            }

            throw $e;
        }
    }
}
