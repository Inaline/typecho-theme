<?php
/**
 * Inaline 主题的主题设置项定义
 * @author Inaline Studio
 */
return [
    [
        'title'   => '欢迎使用',
        'content' => '<h1>Inaline - 功能强大、美观、简洁的 Typecho 主题</h1>'
    ],
    [
        'title'  => '常用组件示例',
        'fields' => [
            [
                'name'        => 'demo_text',
                'label'       => '文本输入框',
                'type'        => 'text',
                'default'     => 'Hello Typecho',
                'description' => '普通单行文本'
            ],
            [
                'name'        => 'demo_textarea',
                'label'       => '多行文本',
                'type'        => 'textarea',
                'default'     => "第一行\n第二行",
                'description' => '支持换行'
            ],
            [
                'name'    => 'demo_radio',
                'label'   => '单选',
                'type'    => 'radio',
                'default' => 'option2',
                'options' => ['option1' => '选项一', 'option2' => '选项二']
            ],
            [
                'name'    => 'demo_checkbox',
                'label'   => '多选',
                'type'    => 'checkbox',
                'default' => true
            ],
            [
                'name'    => 'demo_select',
                'label'   => '下拉选择',
                'type'    => 'select',
                'default' => 'apple',
                'options' => ['apple' => '苹果', 'banana' => '香蕉', 'orange' => '橙子']
            ]
        ]
    ]
];