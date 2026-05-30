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

    public function addCourse($name, $code, $group, $teacherId): bool {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("
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
                $this->conn->rollBack();
                return false;
            }

            $this->conn->commit();
            return true;

        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            error_log("Error al crear curso: " . $e->getMessage());
            return false;
        }
    }

    public function countRows(string $tableName): int {
        $allowedTables = ["curso", "profesor", "evaluacion", "entrega"];

        if (!in_array($tableName, $allowedTables)) {
            return 0;
        }

        try {
            $stmt = $this->conn->query("SELECT COUNT(*) AS total FROM $tableName");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result["total"] : 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

}