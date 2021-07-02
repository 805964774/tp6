<?php


namespace ChengYi\abstracts;


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

    public function __construct(Request $request, $param = []) {
        if (empty($param)) {
            $inputData = $request->param();
        } else {
            $inputData = $param;
        }
        $this->reflectionClass = new ReflectionClass($this);
        $properties = $this->reflectionClass->getProperties(ReflectionProperty::IS_PRIVATE);
        foreach ($properties as $property) {
            $propertySnakeName = Str::snake($property->getName());
            if ($property->isPrivate() && isset($inputData[$propertySnakeName])) {
                $propertyValue = $inputData[$propertySnakeName];
                $propertyName = $property->getName();
                $setDataFuncName = 'set' . ucfirst($propertyName);
                $this->$setDataFuncName($propertyValue);
            }
        }
        if (true == $this->autoValidate) {
            $this->validate();
        }
    }

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

    public function toArray(): array {
        $properties = $this->reflectionClass->getProperties(ReflectionProperty::IS_PRIVATE);
        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $getDataFuncName = 'get' . ucfirst($propertyName);
            $this->data[Str::snake($propertyName)] = $this->$getDataFuncName();
        }
        return $this->data;
    }
}
