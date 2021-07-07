<?php
declare(strict_types=1);

namespace ChengYi\abstracts;


use ChengYi\exception\PoPoException;
use ReflectionClass;
use ReflectionProperty;
use think\contract\Arrayable;
use think\helper\Str;
use think\Request;

/**
 * Class PoPo
 * @package ChengYi\abstracts
 */
abstract class PoPo implements Arrayable
{
    private $data = [];

    protected $validates = [];

    protected $autoValidate = true;

    /**
     * @var \ReflectionClass
     */
    private $reflectionClass;

    /**
     * PoPo constructor.
     * @param \think\Request $request
     * @param array $param
     * @throws \ChengYi\exception\PoPoException
     * @throws \ReflectionException
     */
    public function __construct(Request $request, array $param = []) {
        if (empty($param)) {
            $inputData = $request->param();
        } else {
            $inputData = $param;
        }
        $this->reflectionClass = new ReflectionClass($this);
        $this->setData($inputData);
        if (true == $this->autoValidate) {
            $this->validate();
        }
    }

    /**
     * 触发验证
     */
    public function validate() {
        foreach ($this->validates as $validate => $scene) {
            if (is_string($scene)) {
                validate($validate)->scene($scene)->check($this->toArray());
            } else if (is_array($scene)) {
                foreach ($scene as $item) {
                    validate($validate)->scene($item)->check($this->toArray());
                }
            }
        }
    }

    /**
     * 将类的私有属性转为数组
     * @return array
     */
    public function toArray(): array {
        $properties = $this->reflectionClass->getProperties(ReflectionProperty::IS_PRIVATE);
        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $getDataFuncName = 'get' . ucfirst($propertyName);
            $this->data[Str::snake($propertyName)] = $this->$getDataFuncName();
        }
        return $this->data;
    }

    /**
     * 设置数据
     * @param $inputData
     * @throws \ChengYi\exception\PoPoException
     * @throws \ReflectionException
     */
    private function setData($inputData) {
        $properties = $this->reflectionClass->getProperties(ReflectionProperty::IS_PRIVATE);
        foreach ($properties as $property) {
            $propertySnakeName = Str::snake($property->getName());
            if (isset($inputData[$propertySnakeName])) {
                $propertyValue = $inputData[$propertySnakeName];
                $propertyName = $property->getName();
                $setDataFuncName = 'set' . ucfirst($propertyName);
                if (!$this->reflectionClass->hasMethod($setDataFuncName)) {
                    throw new PoPoException('method '.$this->reflectionClass->getName() . '::' . $setDataFuncName . ' not exists!');
                }
                $reflectionMethod = $this->reflectionClass->getMethod($setDataFuncName);
                if (!$reflectionMethod->isPublic()) {
                    throw new PoPoException('method '.$this->reflectionClass->getName() . '::' . $setDataFuncName . ' is not public!');
                }
                $reflectionMethod->invokeArgs($this, [$propertyValue]);
            }
        }
    }
}
