<?php
/*
|--------------------------------------------------------------------------
| Permitir archivos estáticos
|--------------------------------------------------------------------------
*/

if (php_sapi_name() === 'cli-server') {

    $path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

    $file = __DIR__ . $path;

    if (is_file($file)) {
        return false;
    }
}
require_once __DIR__.'/router.php';

get('/', 'index.php');
post('/', 'index.php');

get('/panel', 'panel.php');

get('/signup', 'signup.php');
post('/signup', 'signup.php');

get('/addCourse', 'createCourse.php');
post('/addCourse', 'createCourse.php');

get('/courses/$ID', 'courses.php');
post('/courses/$ID/delete', 'courses.php');

get('/courses/$ID/students/add', 'addStudents.php');
post('/courses/$ID/students/add', 'addStudents.php');

get('/courses/$ID/assignments/create', 'createAssignment.php');
post('/courses/$ID/assignments/create', 'createAssignment.php');

get('/logout', 'logout.php');


// Dynamic GET. Example with 2 variables
// The $name will be available in full_name.php
// The $last_name will be available in full_name.php
// In the browser point to: localhost/user/X/Y
get('/user/$name/$last_name', 'views/full_name.php');

// Dynamic GET. Example with 2 variables with static
// In the URL -> http://localhost/product/shoes/color/blue
// The $type will be available in product.php
// The $color will be available in product.php
get('/product/$type/color/$color', 'product.php');

// A route with a callback
get('/callback', function(){
  echo 'Callback executed';
});

// A route with a callback passing a variable
// To run this route, in the browser type:
// http://localhost/user/A
get('/callback/$name', function($name){
  echo "Callback executed. The name is $name";
});

// Route where the query string happends right after a forward slash
get('/product', '');

// A route with a callback passing 2 variables
// To run this route, in the browser type:
// http://localhost/callback/A/B
get('/callback/$name/$last_name', function($name, $last_name){
  echo "Callback executed. The full name is $name $last_name";
});

// ##################################################
// ##################################################
// ##################################################
// Route that will use POST data
post('/user', '/api/save_user');

// ##################################################
// API STUDENTS
// ##################################################

// Register a student from the IDE
post('/api/register', 'api/students/registerStudent.php');

// Login of a student from the IDE, returns JWT token if successful
post('/api/login', 'api/students/loginStudent.php');

// Get the courses of a student, requires JWT token in Authorization header
get('/api/courses', 'api/students/getStudentCourses.php');

// ##################################################
// ##################################################
// ##################################################
// any can be used for GETs or POSTs

// For GET or POST
// The 404.php which is inside the views folder will be called
// The 404.php has access to $_GET and $_POST

