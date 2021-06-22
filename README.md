# Dcat Admin Extension

![](https://ghost-ai.com/images/original/file/16171727904222427.png "")
![](https://ghost-ai.com/images/original/file/1617172863498956751.png "")

### composer 安装

> composer require ghost/dcat-config

### dcat config 配置

> admin_setting 添加 ghost::admin_config 字段

> key=>value形式保存

> 可以替换系统config 或env 配置
> 使用 config("key");

### 国际化 

```php
//resources/lang/zh-CN/dcat-config

return [
    'Basic'=>'基本配置'
];

```


目前支持

```
	'text' => '文本',
	'select' => '下拉选框单选',
	'multipleSelect' => '下拉选框多选',
	'listbox' => '多选盒',
	'textarea' => '长文本',
	'radio' => '单选',
	'checkbox' => '多选',
	'email' => '邮箱',
	'password' => '密码',
	'url' => '链接',
	'ip' => 'IP',
	'mobile' => '手机',
	'color' => '颜色选择器',
	'time' => '时间',
	'date' => '日期',
	'datetime' => '时间日期',
	'file' => '文件上传',
	'image' => '图片上传',
	'multipleImage' => '多图上传',
	'multipleFile' => '多文件上传',
	'editor' => '富文本编辑器',
	'number' => '数字',
	'rate' => '费率',
	'array'=>'数组'
    ```
