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
 * Class PoHo
 * @package ChengYi\guide
 */
abstract class PoHo implements ArrayAccess, JsonSerializable, Arrayable
{
    private $data = [];
    protected $validates = [];
    protected $autoValidate = true;

    public function __construct(Request $request) {
        $inputData = $request->param();
        $class = new ReflectionClass($this);
        $properties = $class->getProperties(ReflectionProperty::IS_PROTECTED);
        foreach ($properties as $property) {
            $propertySnakeName = Str::snake($property->getName());
            if ($property->isProtected() && isset($inputData[$propertySnakeName])) {
                $propertyName = $property->getName();
                $this->$propertyName = $inputData[$propertySnakeName];
                $this->data[$propertySnakeName] = $inputData[$propertySnakeName];
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
            validate($validate)->scene($scene)->check($this->data);
        }
    }

    public function toArray(): array {
        return $this->data;
    }
}

