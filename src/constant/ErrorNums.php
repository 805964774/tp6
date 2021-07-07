<?php
declare(strict_types=1);

namespace ChengYi\constant;

/**
 * 错误码
 * Class ErrorNums
 * @package ChengYi\constant
 */
class ErrorNums
{
    // 参数错误
    const PARAM_ILLEGAL = 1;
    // 添加失败
    const ADD_FAIL = 2;
    // 过多的请求
    const TOO_MANY_REQUEST = 3;
    // 目录不存在
    const DIRECTORY_NOT_EXISTS = 4;
    // 类不存在
    const CLASS_NOT_EXISTS = 5;
    // 方法不存在
    const METHOD_NOT_EXISTS = 6;
    // 方法非共有
    const METHOD_NOT_PUBLIC = 7;
}
