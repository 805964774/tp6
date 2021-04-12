<?php

namespace ChengYi;

class App extends \think\App
{
    /**
     * 获取应用配置目录
     * @access public
     * @return string
     */
    public function getConfigPath(): string {
        $scene = $this->env->get('config_scene', 'prod');
        return $this->rootPath . 'config' . DIRECTORY_SEPARATOR . $scene . DIRECTORY_SEPARATOR;
    }
}
