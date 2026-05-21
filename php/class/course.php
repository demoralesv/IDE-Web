<?php

class Course {
    private PDO $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function getCoursesByTeacher($teacherId) {
        $stmt = $this->conn->prepare("
            SELECT *
            FROM curso
            WHERE profesorusuarioid = :teacherId
            ORDER BY nombre ASC, grupo ASC
        ");

        $stmt->execute([
            ":teacherId" => $teacherId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCourseByIdAndTeacher($courseId, $teacherId) {
        $stmt = $this->conn->prepare("
            SELECT 
                ID,
                nombre,
                codigo,
                grupo,
                profesorusuarioid
            FROM curso
            WHERE ID = :courseId
            AND profesorusuarioid = :teacherId
            LIMIT 1
        ");

        $stmt->execute([
            ":courseId" => $courseId,
            ":teacherId" => $teacherId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }



}    