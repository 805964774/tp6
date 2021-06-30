<?php


namespace ChengYi\abstracts;


use ArrayAccess;
use JsonSerializable;
use ReflectionClass;
use ReflectionProperty;
use think\contract\Arrayable;
use think\helper\Str;
use think\Request;

/**
 * Class PoPo
 * @package ChengYi\abstracts
 */
abstract class PoPo implements ArrayAccess, JsonSerializable, Arrayable
{
    private $data = [];
    protected $dataTypeMap = [];
    protected $validates = [];
    protected $autoValidate = true;

    public function __construct(Request $request, $param = []) {
        if (empty($param)) {
            $inputData = $request->param();
        } else {
            $inputData = $param;
        }
        $class = new ReflectionClass($this);
        $properties = $class->getProperties(ReflectionProperty::IS_PROTECTED);
        foreach ($properties as $property) {
            $propertySnakeName = Str::snake($property->getName());
            if ($property->isProtected() && isset($inputData[$propertySnakeName])) {
                $propertyValue = $inputData[$propertySnakeName];
                if (isset($this->dataTypeMap[$propertySnakeName])) {
                    $type = $this->dataTypeMap[$propertySnakeName];
                    $propertyValue = $this->typeCast($propertyValue, $type);
                }
                $propertyName = $property->getName();
                $this->$propertyName = $propertyValue;
                $this->data[$propertySnakeName] = $propertyValue;
            }
        }
        if (true == $this->autoValidate) {
            $this->validate();
        }
    }

    public function __get($propertyName) {
        return $this->$propertyName;
    }

    public function __set(string $propertyName, $propertyValue) {
        $this->$propertyName = $propertyValue;
        $this->data[Str::snake($propertyName)] = $propertyValue;
    }

    public function offsetExists($offset): bool {
        return !is_null($this->__get(Str::camel($offset)));
    }

    public function offsetGet($offset) {
        return $this->__get(Str::camel($offset));
    }

    public function offsetSet($offset, $value) {
        $this->__set(Str::camel($offset), $value);
    }

    public function offsetUnset($offset) {
        $offset = Str::camel($offset);
        unset($this->$offset);
    }

    public function jsonSerialize(): array {
        return $this->data;
    }

    public function validate() {
        foreach ($this->validates as $validate => $scene) {
            if (is_string($scene)) {
                validate($validate)->scene($scene)->check($this->data);
            } else if (is_array($scene)) {
                foreach ($scene as $item) {
                    validate($validate)->scene($item)->check($this->data);
                }
            }
        }
    }

    public function toArray(): array {
        return $this->data;
    }

    /**
     * 强制类型转换
     * @access public
     * @param mixed $data
     * @param string $type
     * @return mixed
     */
    private function typeCast($data, string $type) {
        switch (strtolower($type)) {
            // 数组
            case 'array':
                $data = (array)$data;
                break;
            // 数字
            case 'int':
                $data = (int)$data;
                break;
            // 浮点
            case 'float':
                $data = (float)$data;
                break;
            // 布尔
            case 'bool':
                $data = (boolean)$data;
                break;
            // 字符串
            case 'string':
                if (is_scalar($data)) {
                    $data = (string)$data;
                } else {
                    throw new \InvalidArgumentException('variable type error：' . gettype($data));
                }
                break;
        }
        return $data;
    }
}
