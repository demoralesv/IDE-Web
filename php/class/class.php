<?php

require_once __DIR__ . '/db_info.php';

try {
    $conn = new PDO(
        "mysql:host=$servername;port=$dbPort;dbname=$dbname;charset=utf8mb4",
        $username,
        $dbPassword
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// ***************************************************************************Login y signup

function login($email, $userPassword) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM usuario WHERE correo = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($userPassword, $user['password'])) {
            return true;
        }
    }

    return false;
}

function register($name, $lastname, $email, $userPassword) {
    global $conn;

    try {
        $checkStmt = $conn->prepare("SELECT ID FROM usuario WHERE correo = :correo");
        $checkStmt->execute([
            ":correo" => $email
        ]);

        if ($checkStmt->rowCount() > 0) {
            return false;
        }

        $hashedPassword = password_hash($userPassword, PASSWORD_DEFAULT);

        $idStmt = $conn->query("SELECT COALESCE(MAX(ID), 0) + 1 AS nextId FROM usuario");
        $nextId = $idStmt->fetch(PDO::FETCH_ASSOC)["nextId"];

        $stmt = $conn->prepare("
            INSERT INTO usuario (ID, nombre, apellido1, correo, password)
            VALUES (:id, :nombre, :apellido1, :correo, :password)
        ");

        return $stmt->execute([
            ":id" => $nextId,
            ":nombre" => $name,
            ":apellido1" => $lastname,
            ":correo" => $email,
            ":password" => $hashedPassword
        ]);

    } catch (PDOException $e) {
        error_log("Error en register: " . $e->getMessage());
        return false;
    }
}

// *************************************************************************** Funciones de profe

function addTeacher($email) {
    // agregar logica para agregar un profe
}

function addCurse($name, $teacherId) {
    // agregar logica para agregar un curso
}

function addEvaluation($name, $curseId) {
    // agregar logica para agregar una evaluacion
}

function getCoursesByTeacher($teacherId) {
    // agregar logica para obtener los cursos de un profe
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