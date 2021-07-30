<?php
declare(strict_types=1);

use ChengYi\constant\ErrorNums;
use ChengYi\exception\ChengYiException;
use Chengyi\abstracts\PoPo;
use think\facade\App;

/**
 * 数组转poPo对象
 * @param array $data
 * @param string $className
 * @return mixed
 * @throws \ChengYi\exception\ChengYiException
 */
function array_2_popo_obj(array $data, string $className) {
    if (!class_exists($className)) {
        throw new ChengYiException('class not exists!',ErrorNums::CLASS_NOT_EXISTS);
    }
    return new $className(App::getInstance()->request, $data);
}

/**
 * popo 对象1 转 popo对象2
 * @param \Chengyi\abstracts\PoPo $poPoClass
 * @param string $className
 * @return mixed
 * @throws \ChengYi\exception\ChengYiException
 */
function popo_obj_2_obj(PoPo $poPoClass ,string $className) {
    return array_2_popo_obj($poPoClass->toArray(), $className);
}
