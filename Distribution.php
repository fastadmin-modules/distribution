<?php

namespace addons\slide;

use app\common\library\Menu;
use think\Addons;
use think\addons\Service;

/**
 * 插件
 */
class Distribution extends Addons
{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        $menu = [
            [
                'name' => 'distribution',
                'title' => '分销管理',
                'icon' => 'fa fa-yelp',
                'sublist' => [
                    [
                        'name' => 'user/distribution',
                        'title' => '分销管理',
                        'icon' => 'fa fa-child',
                        'sublist' => []
                    ],
                    [
                        'name' => 'user/distribution',
                        'title' => '推广码',
                        'icon' => 'fa fa-qrcode',
                        'sublist' => []
                    ],
                ]
            ],
        ];
        Menu::create($menu);
        Service::refresh();
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        Menu::delete("distribution");
        Service::refresh();
        return true;
    }

    /**
     * 插件启用方法
     * @return bool
     */
    public function enable()
    {
        Menu::enable('distribution');
        return true;
    }

    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable()
    {
        Menu::disable("distribution");
        return true;
    }


}
