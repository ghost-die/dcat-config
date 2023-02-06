<?php

namespace Ghost\DcatConfig\Tools;

use Dcat\Admin\Actions\Action;
use Dcat\Admin\Form;
use Dcat\Admin\Widgets\Form as WidgetsForm;
use Ghost\DcatConfig\DcatConfigServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;

class Builder
{
    /**
     * @var \Dcat\Admin\Form
     */
    protected $form;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $model;

    protected $data;

    protected $option = [
        'text' => 'Text',
        'select' => 'Select',
        'multipleSelect' => 'MultipleSelect',
        'listbox' => 'ListBox',
        'textarea' => 'Textarea',
        'radio' => 'Radio',
        'checkbox' => 'CheckBox',
        'email' => 'E-mail',
        'password' => 'PassWord',
        'url' => 'Url',
        'ip' => 'IP',
        'mobile' => 'Mobile',
        'color' => 'Color',
        'time' => 'Time',
        'date' => 'Date',
        'datetime' => 'DateTime',
        'file' => 'File',
        'image' => 'Image',
        'multipleImage' => 'MultipleImage',
        'multipleFile' => 'MultipleFile',
        'editor' => 'Editor',
        //'markdown' => 'Markdown',
        'number' => 'Number',
        'rate' => 'Rate',
        'arrays' => "Arrays",
    ];

    protected $input;

    public function __construct($form)
    {
        $this->form = $form;
        $this->form->disableDeleteButton();
        $this->form->disableViewButton();
        $this->form->disableCreatingCheck();
        $this->form->disableListButton();
        $this->form->disableEditingCheck();
        $this->form->disableViewCheck();
        $this->model = $this->all();
    }

    /**
     * @return $this
     */
    public function form()
    {
        $this->data = $this->model->map(function ($model) {
            [$key, $value] = explode('.', $model['key'], 2);
            $model['tab'] = $key;
            $model['key'] = str_replace(".", '-', $value);

            return $model;
        })->groupBy('tab');

        $this->form->title($this->__('builder.config'));
        $this->form->action(admin_url('config.do'));
        $tab = collect($this->config('tab'))->pluck('value', 'key')->toArray();
        $this->data = $this->data->toArray();
        $this->data = collect(array_merge($tab, $this->data));

        $this->data->each(function ($value, $item) use ($tab) {
            $this->form->tab(admin_trans("dcat-config.".trim($tab[$item])), function () use ($value, $item) {
                if (is_array($value)) {
                    collect($value)->each(function ($model) {
                        Field::make($model, $this->form)->{$model['element']}();
                    });
                }
            });
        });

        $this->form->tools([new Create()]);

        return $this;
    }

    /**
     * @param $str
     *
     * @return false|string[]
     */
    protected function textToArray($str)
    {
        return explode("\n", str_replace("\r\n", "\n", $str));
    }

    /**
     * @param $id
     * @return $this
     */
    public function putEdit($id)
    {
        $request = request();

        $option = $request->get('option');
        $rule = $request->get('rule');

        if ($option) {
            $option = collect($this->textToArray($option))->map(function ($value, $k) {
                if (false === strpos($value, ':')) {
                    $attr = ['key' => $k, 'value' => $value];
                } else {
                    [$v1, $v2] = explode(':', $value);
                    $attr = ['key' => $v1, 'value' => $v2];
                }

                return $attr;
            });
        }
        if ($rule) {
            $rule = $this->textToArray($rule);
        }

        $options = [
            'rule' => $rule,
            'option' => $option,
        ];

        $data = [

            'key' => $id,
            'value' => null,
            'name' => $request->get('name'),
            'help' => $request->get('help'),
            'element' => $request->get('element'),
            'options' => $options,
        ];

        $this->model = $this->model->map(function ($value) use ($data) {

            if ($value['key'] === $data['key']) {
                $value['value'] = '';
                $value['name'] = $data['name'];
                $value['help'] = $data['help'];
                $value['element'] = $data['element'];
                $value['options'] = $data['options'];
            }

            return $value;
        });
        $this->save();

        return $this;
    }

    /**
     * @return $this
     */
    public function update(): Builder
    {
        $request = \request()->except('_token');
        $i = 0;
        $update = [];
        foreach ($request as $key => $value) {

            $update[$i]['key'] = str_replace("-", '.', $key);
            if (is_array($value)) {

                $update[$i]['value'] = collect($value)->map(function ($v) {

                    if (isset($v['_remove_'])) {
                        if ((int) $v['_remove_'] === 1) {
                            return 0;
                        }
                        unset($v['_remove_']);
                    }

                    return $v;
                })->filter(function ($v) {
                    return $v !== null;
                })->values();
            } else {
                $update[$i]['value'] = $value;
            }
            $i++;
        }
        $update = collect($update)->pluck('value', 'key');
        $this->model = $this->model->map(function ($value) use ($update) {
            $value['value'] = $update[$value['key']];

            return $value;
        });

        $this->save();
        Artisan::call('config:cache');

        return $this;
    }

    /**
     * @return $this
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store()
    {
        $tab = request()->get('tab');
        $attribute = request()->get('attribute');

        Validator::make([
            'tab' => $tab,
            'attribute'=>$attribute
        ], [
            'tab' =>'required',
            'attribute' =>'required'
        ],[
            'tab.required' => $this->__('builder.groups-required')
        ])->validate();

        $attribute = collect($attribute)->map(function ($data) {
            if ($data['option']) {
                $data['option'] = collect($this->textToArray($data['option']))->map(function ($value, $k) {
                    if (false === strpos($value, ':')) {
                        $attr = ['key' => $k, 'value' => $value];
                    } else {
                        [$v1, $v2] = explode(':', $value);
                        $attr = ['key' => $v1, 'value' => $v2];
                    }

                    return $attr;
                });
            }
            if ($data['rule']) {
                $data['rule'] = $this->textToArray($data['rule']);
            }
            return $data;
        })->values();

        $data = [];
        $attribute->each(function ($value, $item) use (&$data, $tab) {

            if (null !== $value['key'] &&  1 !== (int)$value['_remove_']) {
                $data[$item]['key'] = $tab.'.'.$value['key'];
                $data[$item]['name'] = $value['name'];
                $data[$item]['value'] = '';
                $data[$item]['help'] = $value['help'];
                $data[$item]['element'] = $value['element'];
                $data[$item]['options'] = [
                    'option' => $value['option'] ?? [],
                    'rule' => $value['rule'] ?? [],
                ];
                $data[$item]['order'] = $this->order() + $item + 1;
            }
        });

        $rules = [];
        $message = [];
        foreach ($data as $key => $val) {
            $rules[$key.'.key'] = [
                'required',
                'regex:/^[a-zA-Z_\.0-9]+$/',
                function ($attribute, $value, $fail) {
                    $res = $this->all()->where('key', $value)->first();

                    if ($res) {
                        return $fail($this->__('builder.key-existed'));
                    }
                },
            ];
            $rules[$key.'.name'] = 'required';
            $rules[$key.'.element'] = 'required';

            $message[$key.'.key.required'] = $this->__('builder.key-required');
            $message[$key.'.key.regex'] = $this->__('builder.key-regex');

            $message[$key.'.name.required'] = $this->__('builder.name-required');
            $message[$key.'.element.required'] = $this->__('builder.element-required');
        }

        Validator::make($data, $rules, $message)->validate();
        $this->model = $this->model->merge($data);
        $this->save();

        return $this;
    }

    /**
     * @param $id
     * @return $this
     */
    public function edit($id)
    {
        $tab = collect($this->config('tab'))->pluck('value', 'key');
        $this->model = $this->model->where('key', $id)->first();
        if (null === $this->model) {
            return $this;
        }
        $this->form->hidden('_method')->value('put');
        $this->form->select('tab', $this->__('builder.groups'))->options($this->trans($tab))->value(function (
        ) {
            return substr($this->model['key'], 0, strpos($this->model['key'], '.'));
        })->disable()->default($tab->keys()->first());

        $this->form->text('key', $this->__('builder.key'))->required()->value(substr($this->model['key'], strrpos($this->model['key'], ".") + 1))->disable();
        $this->form->text('name', $this->__('builder.name'))->required()->value($this->model['name']);
        $this->form->radio('element', $this->__('builder.element'))->required()->when([
            'select',
            'multipleSelect',
            'listbox',
            'radio',
            'checkbox',
        ], function ($form) {
            $form->textarea('option', $this->__('builder.option'))->value(function (
            ) {

                $d = collect($this->model['options']['option'])->pluck('value', 'key')->toArray();

                $text = '';
                foreach ($d as $k => $v) {

                    $text .= $k.':'.$v."\r\n";
                }

                return $text;
            })->placeholder($this->__('builder.option-help'));
        })->options($this->trans($this->option,true))->value($this->model['element'])->default('text');
        $this->form->textarea('rule', $this->__('builder.rule'))->value(function (
        ) {
            $d = $this->model['options']['rule'];
            $text = '';
            foreach ((array) $d as $k => $v) {
                $text .= $v."\r\n";
            }
            return $text;
        })->placeholder($this->__('builder.rule-help'));
        $this->form->text('help', $this->__('builder.help'))->value($this->model['help']);

        return $this;
    }

    protected function order()
    {
        $res = collect($this->model)->last();

        return $res ? $res['order'] : 0;
    }

    public function trans($array,$self = false)
    {

        if ($array instanceof Collection) {
            $array = $array->toArray();
        }


        foreach ($array as $item=>&$value)
        {
            if ($self) {
                $array[$item] = $this->__('Option.'.trim($value));
            }else{
                $array[$item] = admin_trans("dcat-config.".trim($value));
            }
        }

        return $array;
    }
    /**
     * @return $this
     */
    public function create()
    {

        $tab = collect($this->config('tab'))->pluck('value', 'key');
        $this->form->action(admin_url('config/addo'));

        $this->form->radio('tab', $this->__('builder.groups'))->options($this->trans($tab))->default($tab->keys()->first());
        $this->form->array('attribute', $this->__('builder.attribute'), function (
            WidgetsForm $form
        ) {
            $form->text('key', $this->__('builder.key'))->required()->help($this->__('builder.key-help'));
            $form->text('name', $this->__('builder.name'))->required();
            $form->radio('element', $this->__('builder.element'))->required()->when([
                'select',
                'multipleSelect',
                'listbox',
                'radio',
                'checkbox',
            ], function (WidgetsForm $form) {
                $form->textarea('option', $this->__('builder.option'))->placeholder($this->__('builder.option-help'));
            })->options($this->trans($this->option,true))->default('text');
            $form->textarea('rule', $this->__('builder.rule'))->placeholder($this->__('builder.rule-help'));
            $form->text('help', $this->__('builder.help'));
        });

        return $this;
    }

    /**
     * @param $id
     * @return $this
     */
    public function destroy($id)
    {
        $this->model = $this->model->map(function ($value) use ($id) {
            if ($value['key'] === $id) {
                return [];
            }

            return $value;
        })->filter();

        $this->save();

        return $this;
    }

    /**
     * @return \Dcat\Admin\Form
     */
    public function getForm(): Form
    {
        return $this->form;
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed
     */
    protected function config($key, $default = null)
    {
        return DcatConfigServiceProvider::setting($key, $default);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        return collect(admin_setting_array("ghost::admin_config"));
    }

    /**
     * @return \Dcat\Admin\Support\Setting|mixed
     */
    public function save()
    {
        return admin_setting(["ghost::admin_config" => $this->model]);
    }

    /**
     * @param $key
     * @param array $replace
     * @param null $locale
     * @return array|string|null
     */
    private function __($key, $replace = [], $locale = null)
    {
        return DcatConfigServiceProvider::trans('dcat-config.'.$key, $replace, $locale);
    }
}
