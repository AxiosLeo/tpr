<?php

declare(strict_types=1);

namespace tpr\traits;

trait ParamTrait
{
    /**
     * @param string $name
     * @param null   $default
     *
     * @return mixed
     */
    public function param($name = null, $default = null)
    {
        $params = $this->getRequestData('params', function () {
            if ('POST' === $this->method()) {
                $params = $this->post();
            } else {
                $params = $this->put();
            }
            $params = array_merge($params, $this->get());

            return $this->setRequestData('params', $params);
        });

        return $params->get($name, $default);
    }
}
