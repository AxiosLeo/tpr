<?php

declare(strict_types=1);

namespace tpr\tests\mock;

use tpr\Model;

final class MockModel extends Model
{
    public $foo;
    public $test;
    public $data = [];

    protected $_rules = [
        'test' => 'required',
    ];

    protected $_alias = [
        'data' => 'MockData',
    ];

    protected $_messages = [
        'required' => 'required :attribute',
    ];
}
