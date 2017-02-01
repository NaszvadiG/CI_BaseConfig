###################
CI_BaseConfig
###################

Aplikasi ini merupakan pengembangan dari CodeIgniter. Aplikasi ini dijalankan dengan memanipulasi config, sehingga developer tidak perlu meakukan ubahan apapun pada controller dan model.  

*******************
CI-BaseConfig merupakan dikembangan dari Framework CodeIgniter V3. Ci_BaseConfig dirancang untuk memudahkan developer dalam mengembangkan aplikasi menyerupai HMVC, bedanya dalam HMVC hirarki disusun atas MVC sedangkan dalam CI_BaseConfig, hirarki disusun dalam config atau Hierarchical-Configuration. Sebagai ilstrasi dalam CI_BaseConfig misqalkan terdapat 3 role dalam aplikasi yaitu : admin, editor, dan author maka struktur hirarki  config sebagai berikut

application/config

admin.php
admin/
task_1.php
task_2/
sub_task_2.1.php
sub_task_2.2.php
task_3.php
editor/
task_1/
sub_task_1.1.php
sub_task_1.2/
sub_sub_task 1.2.1.php
sub_sub_task_1.2.2.php
sub_task_1.3.php
task_2.php
author/
task_1/
sub_task_1.1.php
sub_task_1.2/
sub_sub_task 1.2.1.php
sub_sub_task_1.2.2.php
sub_task_1.3.php
task_2.php
Dibangun menggunakan :
###################
CodeIgniter, JQuery, Bootstrap, AdminLTE, CKEditor
###################
