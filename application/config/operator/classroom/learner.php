<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config['view'] = [
    'page' => 'viewPanelIndependent',
    'title' => 'Daftar Peserta Belajar',
    'head' => ['student_id','original_name','gender','password'],
    'action' => [
        'import','save','trash'
    ],
    'dropdown' => [
        'primary' => [
            'name' => 'room_id',
            'href' => 'operator/grid',
            'arguments' => [
                'table' => 'rooms r',
                'select' => 'r.id, c.title, concat(c.title,"-",r.room) as room',
                'join' => [
                    ['classrooms c','c.id=r.classroom_id','inner'],
                ],
                'where' => [
                    'c.category' => 1,
                ],
            ],
            'helper' => 'obj2optionMulti',
            'field' => ['id','title','room'],
            'label' => 'Pilih Rombel', 
        ],
        'secondary' => [
            'name' => 'user_id',
            'type' => 'controller',
            'controller' => 'operator/dropdown/getFreeStudent',
            'label' => 'Pilih Siswa',
            'multiple' => 'multiple',
        ],
    ],
];

$config['grid'] = [
    'session' => 'room_id',
    'arguments' => [
        'table' => 'learners l',
        'select' => "u.id,u.username, u.original_name, u.gender,  concat('') as password, if(id>0,1,0) as removeable ",
        'join' => [
            ['users u','u.id=l.user_id','inner']
        ],
        'where' => [
            'l.room_id' => 'room_id|post',
        ],
        'order' => 'original_name',
    ],
    'action' => [
        'select'
    ],
    'cell' => [
        [
            'type' => 'addText',
            'field' => 'username',
        ],
        [
            'type' => 'addText',
            'field' => 'original_name',
        ],
        [
            'type' => 'addSelect',
            'field' => 'gender',
            'option' => [
                'helper'    => 'obj2option',
                'field' => ['id','name'],
                'label' => 'Jenis Kelamin',
                'arguments' => [
                    'table' => 'gender',
                    'select' => '*',
                    'order' => 'name',
                ],
            ],
        ],
        [
            'type' => 'addText',
            'field' => 'password',
        ],
    ],
];

$config['assignment'] = [
    'table' => 'learners',
    'field' => [
        'user_id'
    ],
    'session' => 'room_id',
    'action' => ['insert'],
];

$config['form/import'] = [
    'title' => 'Daftar Siswa',
    'table' => 'users',    
    'cell' => [
        [
            'type' => 'number',
            'field' => 'usernameColumn',
            'label' => 'usernameColumn',
            'value' => 1,
        ],
        [
            'type' => 'number',
            'field' => 'nameColumn',
            'label' => 'nameColumn',
            'value' => 2,
        ],             
        [
            'type' => 'number',
            'field' => 'genderColumn',
            'label' => 'genderColumn',
            'value' => 3,
        ],             
        [
            'type' => 'number',
            'field' => 'passwordColumn',
            'label' => 'passwordColumn',
            'value' => 4,
        ],   
        [
            'type' => 'textarea',
            'field' => 'content',
        ],            
    ],
];

$config['submit/import'] = [
    'table' => 'users',
    'relation' => [
        'learners'=>'learners',
        'users_groups'=>'users_groups',
    ],
    'group' => 4,
    'rules' => 'operator/import',
    'field' => [
        'usernameColumn',
        'nameColumn',
        'genderColumn',
        'passwordColumn',
        'content',
    ],
    'session' => [
        'room_id',
    ],
    'key' => 'id',
];

$config['send'] = [
    'table' => ['users'],
    'field' => [
        'username',
        'original_name',
        'gender',
        'password'
    ],
    'mark' => 'username',
    'action' => ['update'],
    'key' => 'id',
];

$config['remove'] = [
    'table' => 'learners',
    'id' => 'user_id',
];