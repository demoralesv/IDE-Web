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

    public function deleteCourse($courseId, $teacherId): bool {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("
                DELETE FROM curso
                WHERE ID = :courseId
                AND profesorusuarioid = :teacherId
            ");

            $deleted = $stmt->execute([
                ":courseId" => $courseId,
                ":teacherId" => $teacherId
            ]);

            if (!$deleted) {
                $this->conn->rollBack();
                return false;
            }

            $this->conn->commit();
            return true;

        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            error_log("Error al eliminar curso: " . $e->getMessage());
            return false;
        }
    }

    public function getCourseStatistics($courseId) {
        $stmt = $this->conn->prepare("
            SELECT 
                (
                    SELECT COUNT(*) 
                    FROM estudiante_curso 
                    WHERE cursoID = :courseIdStudents
                ) AS total_students,

                (
                    SELECT COUNT(*) 
                    FROM evaluacion 
                    WHERE cursoid = :courseIdTasks
                ) AS total_tasks,

                (
                    SELECT COUNT(*) 
                    FROM entrega e
                    INNER JOIN grupo g ON g.ID = e.grupoid
                    INNER JOIN evaluacion ev ON ev.ID = g.evaluacionID
                    WHERE ev.cursoid = :courseIdSubmissions
                ) AS total_submissions
        ");

        $stmt->execute([
            ":courseIdStudents" => $courseId,
            ":courseIdTasks" => $courseId,
            ":courseIdSubmissions" => $courseId
        ]);

        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            "total_students" => (int) $stats["total_students"],
            "total_tasks" => (int) $stats["total_tasks"],
            "total_submissions" => (int) $stats["total_submissions"]
        ];
    }
    
    public function getStudentsByCourse(int $courseId): array {
        $stmt = $this->conn->prepare("
            SELECT 
                u.ID,
                u.nombre,
                u.apellido1,
                u.correo
            FROM estudiante_curso ec
            INNER JOIN estudiante e ON e.ID = ec.estudianteusuarioID
            INNER JOIN usuario u ON u.ID = e.ID
            WHERE ec.cursoID = :courseId
            ORDER BY u.nombre ASC, u.apellido1 ASC
        ");

        $stmt->execute([
            ":courseId" => $courseId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


}
