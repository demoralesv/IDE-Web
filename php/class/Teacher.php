<?php

class Teacher {
    private PDO $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function getTeacherName($email) {
        $stmt = $this->conn->prepare("
            SELECT nombre, apellido1 
            FROM usuario 
            WHERE correo = :email
        ");

        $stmt->execute([
            ":email" => $email
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            return $user["nombre"] . " " . $user["apellido1"];
        }

        return null;
    }

    public function getTeacherId($email) {
        $stmt = $this->conn->prepare("
            SELECT ID 
            FROM usuario 
            WHERE correo = :email
        ");

        $stmt->execute([
            ":email" => $email
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            return $user["ID"];
        }

        return null;
    }   
    public function getTeacherStatistics(int $teacherId): array {
        $stmt = $this->conn->prepare("
            SELECT 
                (
                    SELECT COUNT(*)
                    FROM curso
                    WHERE profesorusuarioid = :teacherIdCourses
                ) AS total_courses,

                (
                    SELECT COUNT(*)
                    FROM evaluacion ev
                    INNER JOIN curso c ON c.ID = ev.cursoid
                    WHERE c.profesorusuarioid = :teacherIdEvaluations
                ) AS total_evaluations,

                (
                    SELECT COUNT(*)
                    FROM entrega e
                    INNER JOIN grupo g ON g.ID = e.grupoid
                    INNER JOIN evaluacion ev ON ev.ID = g.evaluacionID
                    INNER JOIN curso c ON c.ID = ev.cursoid
                    WHERE c.profesorusuarioid = :teacherIdSubmissions
                ) AS total_submissions
        ");

        $stmt->execute([
            ":teacherIdCourses" => $teacherId,
            ":teacherIdEvaluations" => $teacherId,
            ":teacherIdSubmissions" => $teacherId
        ]);

        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            "total_courses" => (int) $stats["total_courses"],
            "total_evaluations" => (int) $stats["total_evaluations"],
            "total_submissions" => (int) $stats["total_submissions"]
        ];
    } 
}