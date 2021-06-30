<?php

use think\facade\App;

/**
 * 数组转poPo对象
 */
function array_2_popo_obj($data, $className) {
    return new $className(App::getInstance()->request, $data);
}
