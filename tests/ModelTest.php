<?php

declare(strict_types=1);

namespace tpr\tests;

use PHPUnit\Framework\TestCase;
use tpr\tests\mock\MockModel;

/**
 * @internal
 * @coversNothing
 */
class ModelTest extends TestCase
{
    public function testUnmarshall()
    {
        $model = new MockModel();
        $model->unmarshall([
            'test'      => 1,
            'foo'       => 'bar',
            'data'      => [1, 2, 3],
            'sub_model' => [
                'foo' => 'test',
                'val' => 100,
            ],
        ]);
        $this->assertEquals(1, $model->test);
        $this->assertEquals('bar', $model->foo);
        $this->assertEquals([1, 2, 3], $model->data);
        $this->assertEquals('test', $model->sub_model->foo);
        $this->assertEquals(100, $model->sub_model->val);
    }

    public function testValidate()
    {
        $model      = new MockModel();
        $validation = $model->validate();
        $this->assertTrue($validation->fails());
        $error = $validation->errors()->firstOfAll();
        $this->assertEquals([
            'test' => 'required Test',
        ], $error);
    }

    public function testModelArrayOperation()
    {
        $meta  = [
            'test'      => 1,
            'foo'       => 'bar',
            'data'      => [1, 2, 3],
            'sub_model' => [
                'foo' => 'test',
                'val' => 100,
            ],
        ];
        $model = new MockModel($meta);
        $this->assertEquals($meta, $model->toArray());
        $this->assertTrue(isset($model['test']));

        unset($model['test']);
        $this->assertFalse(isset($model['test']));

        $model['test'] = 123;
        $this->assertEquals(123, $model['test']);
        $this->assertCount(4, $model);

        $this->assertEquals('{"foo":"bar","test":123,"data":[1,2,3],"sub_model":{"foo":"test","val":100}}', $model->toJson());
    }
}
