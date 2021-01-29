<?php

namespace Ghost\DcatConfig;

use Dcat\Admin\Extend\Setting as Form;

class Setting extends Form
{
    public function form()
    {
        $this->table('tab', function (\Dcat\Admin\Widgets\Form $form) {
            $form->text('key')->default('base');
            $form->text('value')->default('基本配置');
        });
    }
}
