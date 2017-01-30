<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config['view'] = [
    'page' => 'viewPanelSequent',
    'title' => 'Daftar Kelas',
    'head' => ['classroom','label_in_report','competence','grade','detail'],
    'action' => [
        'add', 'save',
    ],
    'dropdown' => [
        'primary' => [
            'name' => 'category',
            'href' => 'operator/grid',
            'arguments' => [
                'table' => 'classroom_categories cc',
                'select' => 'cc.*',
            ],
            'helper' => 'obj2option',
            'field' => ['id','name'],
            'label' => 'Pilih Kategori', 
        ],
    ],
];

$config['grid'] = [
    'session' => 'category',
    'arguments' => [
        'table' => 'classrooms c',
        'select' => "c.id, c.grade, c.code, c.title, c.competence, count(r.id) as num_room,if(count(r.id)=0,1,0) as removeable",
        'join' => [
            ['rooms r','r.classroom_id=c.id','left']
        ],
        'where' => [
            'c.category' => 'category|post',
        ],
        'group' => 'c.id',
        'order' => 'c.grade,c.title',
    ],
    'action' => [
        'select'
    ],
    'cell' => [
        [
            'type' => 'addText',
            'field' => 'code',
        ],
        [
            'type' => 'addText',
            'field' => 'title',
        ],
        [
            'type' => 'addSelect',
            'field' => 'competence',
            'option' => [
                'helper'    => 'obj2option',
                'field' => ['id','name'],
                'label' => 'Pilih Kelompok',
                'arguments' => [
                    'table' => 'course_competence',
                    'select' => '*',
                    'order' => 'name',
                ],
            ],
        ],
        [
            'type' => 'addNum',
            'field' => 'grade',
        ],
        [
            'type' => 'label',
            'field' => 'num_room',
        ],
    ],
];

$config['form/edit'] = [
    'table' => 'classrooms',
    'title' => 'Data Kelas',
    'cell' => [
        [
            'type' => 'text',
            'field' => 'code',
            'label' => 'classroom'
        ],
        [
            'type' => 'select',
            'field' => 'competence',
            'label' => 'competence',
            'option' => [
                'helper'    => 'obj2option',
                'field' => ['id','name'],
                'label' => 'Pilih Kelompok',
                'arguments' => [
                    'table' => 'course_competence',
                    'select' => '*',
                    'order' => 'name',
                ],
            ],
        ],         
        [
            'type' => 'number',
            'field' => 'grade',
            'label' => 'grade',
        ],
        [
            'type' => 'text',
            'field' => 'title',
            'label' => 'label_in_report'
        ],
    ],
    'saveAs' => true,
];

$config['submit/edit'] = [
    'table' => 'classrooms',
    'rules' => 'operator/classroom',
    'field' => [
        'grade',
        'title',
        'code',
        'competence'
    ],
    'session' => [
        'category',
    ],
    'key' => 'id',
];

$config['send'] = [
    'table' => ['classrooms'],
    'field' => [
        'grade',
        'title',
        'code',
        'competence'
    ],
    'mark' => 'title',
    'action' => ['update'],
    'key' => 'id',
];

$config['remove'] = [
    'table' => 'classrooms',
];