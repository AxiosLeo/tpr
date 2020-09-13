<?php

declare(strict_types=1);

namespace tpr\exception\handler;

use Whoops\Handler\Handler;

class DefaultHandler extends Handler
{
    /**
     * @return int
     */
    public function handle()
    {
        dump($this->getException());
        echo 'something error; TPR Version :' . TPR_FRAMEWORK_VERSION;

        return Handler::DONE;
    }
}
