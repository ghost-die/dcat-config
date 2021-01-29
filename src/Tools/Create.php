<?php

namespace Ghost\DcatConfig\Tools;

use Dcat\Admin\Form\AbstractTool;

class Create extends AbstractTool
{
    /**
     * 按钮标题
     *
     * @return string
     */
    protected $title = '<i class="feather icon-plus-square"></i>';

    /**
     * 如果只是a标签跳转，则在这里返回跳转链接即可
     *
     * @return string|void
     */
    protected function href()
    {
        return admin_url('config/add');
    }

    /**
     * 权限判断，如不需要可以删除此方法
     *
     *
     * @return bool
     */
    protected function authorize($user): bool
    {
        return true;
    }

    /**
     * 返回请求接口的参数，如不需要可以删除此方法
     *
     * @return array
     */
    protected function parameters()
    {
        return [];
    }
}