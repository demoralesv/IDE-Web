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

get('/courses/$ID/assignments/$assignmentID', 'assignmentDetails.php');

get('/courses/$ID/assignments/$assignmentID/edit', 'editAssignment.php');
post('/courses/$ID/assignments/$assignmentID/edit', 'editAssignment.php');

get('/courses/$courseID/assignments/$evaluationID/groups', 'manageAssignmentGroups.php');
post('/courses/$courseID/assignments/$evaluationID/groups', 'manageAssignmentGroups.php');

get('/submissions/$ID/download', 'downloadSubmission.php');

get('/logout', 'logout.php');

// ##################################################
// API STUDENTS
// ##################################################

post('/api/register', 'api/students/registerStudent.php');
post('/api/login', 'api/students/loginStudent.php');
get('/api/courses', 'api/students/getStudentCourses.php');
get('/api/students/assignments/$evaluationID/group', 'api/students/getAssignmentGroup.php');
post('/api/students/assignments/$evaluationID/submit', 'api/students/submitAssignment.php');

// For GET or POST
// The 404.php which is inside the views folder will be called
// The 404.php has access to $_GET and $_POST

