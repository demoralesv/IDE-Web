<?php

require_once __DIR__ . '/db_info.php';

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/course.php';
require_once __DIR__ . '/teacher.php';

$database = new Database();
$conn = $database->getConnection();

$auth = new Auth($conn);
$teacherModel = new Teacher($conn);
$courseModel = new Course($conn);

// *************************************************************************** Funciones de profe

function addCourse($name, $code, $group, $teacherId) {
    global $conn;

    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare("
            INSERT INTO curso (nombre, codigo, grupo, profesorusuarioid)
            VALUES (:nombre, :codigo, :grupo, :profesorusuarioid)
        ");

        $courseInserted = $stmt->execute([
            ":nombre" => $name,
            ":codigo" => $code,
            ":grupo" => $group,
            ":profesorusuarioid" => $teacherId
        ]);

        if (!$courseInserted) {
            $conn->rollBack();
            return false;
        }

        $conn->commit();
        return true;

    } catch (PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        error_log("Error al crear curso: " . $e->getMessage());
        return false;
    }
}

function addEvaluation($name, $curseId) {
    // agregar logica para agregar una evaluacion
}

function getCoursesByTeacher($teacherId) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM curso WHERE profesorusuarioid = :teacherId ORDER BY nombre ASC, grupo ASC" );
    $stmt->bindParam(':teacherId', $teacherId);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getEvaluationsByCourse($courseId) {
    // agregar logica para obtener las evaluaciones de un curso
}

function getSubmissionsByEvaluation($evaluationId) {
    // agregar logica para obtener las entregas de una evaluacion
}

function getStudentsByCourse($courseId) {
    // agregar logica para obtener los estudiantes de un curso
}


// *************************************************************************** Funciones generales

function getTeacherName($email) {
    global $conn;

    $stmt = $conn->prepare("SELECT nombre, apellido1 FROM usuario WHERE correo = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user['nombre'] . " " . $user['apellido1'];
    }

    return null;
}

function getTeacherId($email) {
    global $conn;

    $stmt = $conn->prepare("SELECT ID FROM usuario WHERE correo = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user['ID'];
    }

    return null;
}