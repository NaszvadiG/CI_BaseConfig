<?php defined('BASEPATH') OR exit('No direct script access allowed');
    
$config['operator'] = [
    'student' => [
        'student_grade_0' => '',
        'student_grade_1' => '',
        'student_grade_2' => '',
        'student_grade_3' => '',
        'student_grade' => '',
    ],
    'teacher' => [
        'teacher_list' => '',
        'teacher_job' => ''
    ],
    'classroom' => [
        'classroom_list' => '',
        'classroom_room' => '',
        'classroom_learner' => '',
    ],
    'course' => [
        'course_list' => '',
        'course_package' => '',
        'course_lecturer' => '',
    ],
    'exschool' => [
        'exschool_list' => '',
        'exschool_mentor' => '',
        'exschool_member' => '', 
    ],
    'mutation' => [
        'mutation_promotion' => '',
        'mutation_graduation' => '',
        'mutation_replacement' => '',
        'mutation_dropout' => '',
    ],
    'setting' => [
        'setting_layout' => '',
        'setting_time' => '',
        'setting_document' => '',
    ],
];

$config['getTeacher'] = [
   'session' => 'job_id',
    'arguments' => [
        'table' => 'users u',
        'select' => 'u.id, u.original_name',
        'join' => [
            ['users_jobs uj',"uj.user_id=u.id and (isnull(uj.job_id) or uj.job_id='%u')",'left',['job_id|post']],
            ['users_groups ug','u.id=ug.user_id','inner']
        ],
        'where' => [
            'uj.job_id' => '',
            'ug.group_id' => 3
        ],
        'order' => 'u.original_name',
    ],        
    'helper' => 'obj2option',
    'field' => ['id','original_name'],
    'label' => 'Pilih Guru', 
];

$config['getRoomByClassroom'] = [
   'session' => 'classroom_id',
    'arguments' => [
        'table' => 'room r',
        'select' => 'r.id, r.id as rombel',
        'join' => [
            ['rooms rs',"rs.room=r.id and (isnull(rs.classroom_id) or rs.classroom_id='%u')",'left',['classroom_id|post']],
        ],
        'where' => [
            "rs.room" => '',
        ],
        'order' => 'r.id',
    ],        
    'helper' => 'obj2option',
    'field' => ['id','rombel'],
    'label' => 'Pilih Rombel', 
];

$config['getRoomMutation'] = [
   'session' => 'classroom_id-competence-grade-next_grade',
    'arguments' => [
        'table' => 'rooms r',
        'select' => 'r.id, concat(c.title,"-",r.room) as room',
        'join' => [
            ['classrooms c','c.id=r.classroom_id','inner'],
        ],
        'bind' => [
            'c.id' => 0,
        ],
        'order' => 'c.title,r.room',
    ],        
    'helper' => 'obj2option',
    'field' => ['id','room'],
    'label' => 'Pilih Rombel', 
];

$config['getRoom'] = [
   'session' => 'exschool_id',
    'arguments' => [
        'table' => 'rooms r',
        'select' => 'r.id, c.title, concat(c.title,"-",r.room) as room',
        'join' => [
            ['classrooms c','c.id=r.classroom_id','inner'],
        ],
        'order' => 'r.id',
    ],        
    'helper' => 'obj2optionMulti',
    'field' => ['id','title','room'],
    'label' => 'Pilih Rombel', 
];

$config['getFreeStudent'] = [
   'session' => 'room_id',
    'arguments' => [
        'table' => 'users u',
        'select' => 'u.id, u.original_name',
        'join' => [
            ['users_groups ug',"ug.user_id=u.id",'inner'],
            ['learners l',"l.user_id=u.id",'left'],
        ],
        'where' => [
            "ug.group_id" => 4,
            "(l.room_id ='' or l.room_id is null)"=> '',
        ],
        'order' => 'u.original_name',
    ],        
    'helper' => 'obj2option',
    'field' => ['id','original_name'],
    'label' => 'Pilih Siswa', 
];

$config['getCourseFree'] = [
   'session' => 'classroom_id',
    'arguments' => [
        'table' => 'courses c',
        'select' => 'c.id, c.title',
        'join' => [
            ['packages p',"p.course_id=c.id and (isnull(p.classroom_id) or p.classroom_id='%u')",'left',['classroom_id|post']],
        ],
        'where' => [
            "p.classroom_id" => '',
        ],
        'order' => 'c.title',
    ],        
    'helper' => 'obj2option',
    'field' => ['id','title'],
    'label' => 'Pilih Pelajaran', 
];