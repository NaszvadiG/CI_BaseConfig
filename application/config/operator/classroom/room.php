<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config['view'] = [
    'page' => 'viewPanelIndependent',
    'title' => 'Daftar Rombongan Belajar',
    'head' => ['room','Wali Kelas','Ketua Kelas','Banyak Siswa'],
    'action' => [
        'save','trash',
    ],
    'dropdown' => [
        'primary' => [
            'name' => 'classroom_id',
            'href' => 'operator/grid',
            'arguments' => [
                'table' => 'classrooms c',
                'select' => 'c.*, cc.name',
                'join' => [
                    ['classroom_categories cc','cc.id=c.category','inner'],
                ],
            ],
            'helper' => 'obj2optionMulti',
            'field' => ['id','name','title'],
            'label' => 'Pilih Kelas', 
        ],
        'secondary' => [
            'name' => 'room',
            'type' => 'controller',
            'controller' => 'operator/dropdown/getRoomByClassroom',
            'label' => 'Pilih Rombel',
            'multiple' => 'multiple',
        ],
    ],
];

$config['grid'] = [
    'session' => 'classroom_id',
    'arguments' => [
        'table' => 'rooms r',
        'select' => "r.id, concat(c.title,'-',r.room) as room, r.teacher_id, u.original_name as leader, count(l.user_id) as num_student, if(count(l.user_id)=0,1,0) as removeable",
        'join' => [
            ['classrooms c','r.classroom_id=c.id','inner'],
            ['learners l','l.room_id=r.id','left'],
            ['users u','u.id=r.student_id','left']
        ],
        'where' => [
            'r.classroom_id' => 'classroom_id|post',
        ],
        'group' => 'r.id',
        'order' => 'c.title, r.room ',
    ],
    'action' => [
        'leader','select'
    ],
    'badge' => 1,
    'cell' => [
        [
            'type' => 'label',
            'field' => 'room',
        ],
        [
            'type' => 'addSelect',
            'field' => 'teacher_id',
            'option' => [
                'helper'    => 'obj2option',
                'field' => ['id','original_name'],
                'label' => 'Pilih Wali Kelas',
                'arguments' => [
                    'table' => 'users u',
                    'select' => 'u.id,u.original_name',
                    'join' => [
                        ['users_groups ug','ug.user_id=u.id','left'],
                        ['users_jobs uj','uj.user_id=u.id','inner'],
                    ],
                    'where' => [
                        'ug.group_id' => 3, //guru
                        'uj.job_id' => 2, //wali kelas
                    ],
                    'order' => 'original_name',
                ],
            ],
        ],
        [
            'type' => 'label',
            'field' => 'leader',
        ],
        [
            'type' => 'label',
            'field' => 'num_student',
        ],
    ],
];

$config['assignment'] = [
    'table' => 'rooms',
    'field' => [
        'room'
    ],
    'session' => 'classroom_id',
    'action' => ['insert'],
];

$config['form/leader'] = [
    'session' => 'id',
    'table' => 'rooms',
    'title' => 'Penentuan Ketua Kelas',
    'arguments' => [
        'table' => 'rooms',
        'select' => 'concat(classrooms.title,"-",rooms.room) as room, rooms.student_id',
        'join' => [
            ['classrooms','classrooms.id=rooms.classroom_id','inner']
        ],
    ],
    'cell' => [
       [
            'type' => 'label',
            'field' => 'room',
            'label' => 'room'
        ],
        [
            'type' => 'select',
            'field' => 'student_id',
            'option' => [
                'helper'    => 'obj2option',
                'field' => ['id','original_name'],
                'label' => 'Pilih Ketua Kelas',
                'arguments' => [
                    'table' => 'learners l',
                    'join' => [
                        ['users u','u.id=l.user_id','inner']
                    ],
                    'where' => [
                        'l.room_id'=> 'parameter|post',
                    ],
                    'select' => 'u.id,u.original_name',
                    'order' => 'original_name',
                ],
            ],
            'label' => 'room'
       ],
    ],
];

$config['submit/leader'] = [
    'table' => 'rooms',
    'rules' => 'operator/leader',
    'field' => [
        'student_id',
    ],
    'session' => [
        'id',
    ],
    'key' => 'id',
];

$config['send'] = [
    'table' => ['rooms'],
    'field' => [
        'teacher_id',
   ],
    'mark' => 'teacher_id',
    'action' => ['update'],
    'key' => 'id',
];

$config['remove'] = [
    'table' => 'rooms',
];