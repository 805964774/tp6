<?php

/**
 * 数组转popo对象
 */
function array_2_popo_obj($data, $className) {
    return new $className(App::getInstance()->request, $data);
}
