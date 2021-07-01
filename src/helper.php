<?php
declare(strict_types=1);

use ChengYi\constant\ErrorNums;
use ChengYi\exception\ChengYiException;
use think\facade\App;

/**
 * 数组转poPo对象
 */
function array_2_popo_obj(array $data, string $className) {
    if (!class_exists($className)) {
        throw new ChengYiException('class not exists!',ErrorNums::CLASS_NOT_EXISTS);
    }
    return new $className(App::getInstance()->request, $data);
}
