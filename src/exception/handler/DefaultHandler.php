<?php

namespace tpr\exception\handler;

use Whoops\Handler\Handler;

class DefaultHandler extends Handler
{
    /**
     * @return null|int
     */
    public function handle()
    {
        echo "something error;";
        return Handler::DONE;
    }
}