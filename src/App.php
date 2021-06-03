<?php

namespace ChengYi;

use ChengYi\constant\ErrorNums;
use ChengYi\exception\ChengYiException;

/**
 * 框架不支持场景配置，继承重写getConfigPath
 * 依赖config_scene环境变量，读取对应场景的配置，方便开发
 * Class App
 * @package ChengYi
 */
class App extends \think\App
{
    /**
     * 获取应用配置目录
     * @access public
     * @return string
     * @throws \ChengYi\exception\ChengYiException
     */
    public function getConfigPath(): string {
        $scene = $this->env->get('config_scene', 'prod');
        $configPath = $this->rootPath . 'config' . DIRECTORY_SEPARATOR . $scene . DIRECTORY_SEPARATOR;
        if (is_dir($configPath)) {
            return $configPath;
        }
        throw new ChengYiException('场景配置文件不存在,scene:' . $scene, ErrorNums::DIRECTORY_NOT_EXISTS);
    }
}
