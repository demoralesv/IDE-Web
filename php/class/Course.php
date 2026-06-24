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

    public function addCourse($name, $code, $group, $teacherId): int|false {
        try {
            $this->conn->beginTransaction();

            $checkStmt = $this->conn->prepare("
                SELECT ID
                FROM curso
                WHERE nombre = :nombre
                AND grupo = :grupo
                LIMIT 1
            ");

            $checkStmt->execute([
                ":nombre" => $name,
                ":grupo" => $group,
                
            ]);

            $existingCourse = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existingCourse) {
                $this->conn->rollBack();
                return false;
            }

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

            $newCourseId = (int) $this->conn->lastInsertId();

            $this->conn->commit();
            return $newCourseId;

        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            error_log("Error al crear curso: " . $e->getMessage());
            return false;
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

    public function getStudentsNotInCourse(int $courseId, string $search = ""): array {
        $searchTerm = "%" . $search . "%";

        $stmt = $this->conn->prepare("
            SELECT 
                u.ID,
                u.apellido1,
                u.nombre,
                u.correo
            FROM estudiante e
            INNER JOIN usuario u ON u.ID = e.ID
            WHERE e.ID NOT IN (
                SELECT estudianteusuarioID
                FROM estudiante_curso
                WHERE cursoID = :courseId
            )
            AND (
                u.nombre LIKE :searchName
                OR u.apellido1 LIKE :searchLastName
                OR u.correo LIKE :searchEmail
            )
            ORDER BY u.apellido1 ASC, u.nombre ASC
        ");

        $stmt->execute([
            ":courseId" => $courseId,
            ":searchName" => $searchTerm,
            ":searchLastName" => $searchTerm,
            ":searchEmail" => $searchTerm
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addStudentToCourse(int $courseId, int $studentId): bool {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO estudiante_curso (cursoID, estudianteusuarioID)
                VALUES (:courseId, :studentId)
            ");

            return $stmt->execute([
                ":courseId" => $courseId,
                ":studentId" => $studentId
            ]);

        } catch (PDOException $e) {
            error_log("Error al agregar estudiante al curso: " . $e->getMessage());
            return false;
        }
    }

    public function getAssignmentByCourseAndTeacher(int $evaluationId, int $courseId, int $teacherId): ?array {
        $stmt = $this->conn->prepare("
            SELECT 
                ev.ID,
                ev.titulo,
                ev.descripcion,
                ev.adjunto,
                ev.fechaentrega,
                ev.cursoid,
                c.nombre AS cursoNombre,
                c.codigo AS cursoCodigo,
                c.grupo AS cursoGrupo
            FROM evaluacion ev
            INNER JOIN curso c ON c.ID = ev.cursoid
            WHERE ev.ID = :evaluationId
            AND ev.cursoid = :courseId
            AND c.profesorusuarioid = :teacherId
            LIMIT 1
        ");

        $stmt->execute([
            ":evaluationId" => $evaluationId,
            ":courseId" => $courseId,
            ":teacherId" => $teacherId
        ]);

        $assignment = $stmt->fetch(PDO::FETCH_ASSOC);

        return $assignment ?: null;
    }

    public function getAvailableStudentsForAssignment(int $courseId, int $evaluationId): array {
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
            AND NOT EXISTS (
                SELECT 1
                FROM estudiante_grupo eg
                INNER JOIN grupo g ON g.ID = eg.grupoID
                WHERE g.evaluacionID = :evaluationId
                AND eg.estudianteusuarioID = u.ID
            )
            ORDER BY u.nombre ASC, u.apellido1 ASC
        ");

        $stmt->execute([
            ":courseId" => $courseId,
            ":evaluationId" => $evaluationId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getGroupsByAssignment(int $evaluationId): array {
        $stmt = $this->conn->prepare("
            SELECT 
                g.ID AS grupoID,
                g.numero AS grupoNumero,
                u.ID AS estudianteID,
                u.nombre,
                u.apellido1,
                u.correo
            FROM grupo g
            LEFT JOIN estudiante_grupo eg ON eg.grupoID = g.ID
            LEFT JOIN usuario u ON u.ID = eg.estudianteusuarioID
            WHERE g.evaluacionID = :evaluationId
            ORDER BY g.numero ASC, u.nombre ASC
        ");

        $stmt->execute([
            ":evaluationId" => $evaluationId
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $groups = [];

        foreach ($rows as $row) {
            $groupId = (int) $row["grupoID"];

            if (!isset($groups[$groupId])) {
                $groups[$groupId] = [
                    "ID" => $groupId,
                    "numero" => (int) $row["grupoNumero"],
                    "estudiantes" => []
                ];
            }

            if ($row["estudianteID"] !== null) {
                $groups[$groupId]["estudiantes"][] = [
                    "ID" => (int) $row["estudianteID"],
                    "nombre" => $row["nombre"],
                    "apellido1" => $row["apellido1"],
                    "correo" => $row["correo"]
                ];
            }
        }

        return array_values($groups);
    }

    public function createAssignmentGroup(int $evaluationId, array $studentIds): int|false {
        try {
            $studentIds = array_unique(array_map("intval", $studentIds));

            if (empty($studentIds)) {
                return false;
            }

            $this->conn->beginTransaction();

            $evalStmt = $this->conn->prepare("
                SELECT cursoid
                FROM evaluacion
                WHERE ID = :evaluationId
                LIMIT 1
            ");

            $evalStmt->execute([
                ":evaluationId" => $evaluationId
            ]);

            $evaluation = $evalStmt->fetch(PDO::FETCH_ASSOC);

            if (!$evaluation) {
                $this->conn->rollBack();
                return false;
            }

            $courseId = (int) $evaluation["cursoid"];

            $placeholders = implode(",", array_fill(0, count($studentIds), "?"));

            $enrolledStmt = $this->conn->prepare("
                SELECT COUNT(*) AS total
                FROM estudiante_curso
                WHERE cursoID = ?
                AND estudianteusuarioID IN ($placeholders)
            ");

            $enrolledStmt->execute(array_merge([$courseId], $studentIds));

            $enrolledCount = (int) $enrolledStmt->fetch(PDO::FETCH_ASSOC)["total"];

            if ($enrolledCount !== count($studentIds)) {
                $this->conn->rollBack();
                return false;
            }

            $assignedStmt = $this->conn->prepare("
                SELECT COUNT(*) AS total
                FROM estudiante_grupo eg
                INNER JOIN grupo g ON g.ID = eg.grupoID
                WHERE g.evaluacionID = ?
                AND eg.estudianteusuarioID IN ($placeholders)
            ");

            $assignedStmt->execute(array_merge([$evaluationId], $studentIds));

            $assignedCount = (int) $assignedStmt->fetch(PDO::FETCH_ASSOC)["total"];

            if ($assignedCount > 0) {
                $this->conn->rollBack();
                return false;
            }

            $numberStmt = $this->conn->prepare("
                SELECT COALESCE(MAX(numero), 0) + 1 AS nextNumber
                FROM grupo
                WHERE evaluacionID = :evaluationId
            ");

            $numberStmt->execute([
                ":evaluationId" => $evaluationId
            ]);

            $groupNumber = (int) $numberStmt->fetch(PDO::FETCH_ASSOC)["nextNumber"];

            $groupStmt = $this->conn->prepare("
                INSERT INTO grupo (numero, evaluacionID)
                VALUES (:numero, :evaluacionID)
            ");

            $groupInserted = $groupStmt->execute([
                ":numero" => $groupNumber,
                ":evaluacionID" => $evaluationId
            ]);

            if (!$groupInserted) {
                $this->conn->rollBack();
                return false;
            }

            $groupId = (int) $this->conn->lastInsertId();

            $studentGroupStmt = $this->conn->prepare("
                INSERT INTO estudiante_grupo (grupoID, estudianteusuarioID)
                VALUES (:grupoID, :estudianteusuarioID)
            ");

            foreach ($studentIds as $studentId) {
                $inserted = $studentGroupStmt->execute([
                    ":grupoID" => $groupId,
                    ":estudianteusuarioID" => $studentId
                ]);

                if (!$inserted) {
                    $this->conn->rollBack();
                    return false;
                }
            }

            $this->conn->commit();

            return $groupId;

        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            error_log("Error al crear grupo: " . $e->getMessage());
            return false;
        }
    }

    public function getSubmissionForDownload(int $submissionId, int $teacherId): ?array {
        $stmt = $this->conn->prepare("
            SELECT 
                e.ID,
                e.numero,
                e.proyecto,
                e.fechaentrega,
                e.grupoid,
                g.numero AS grupoNumero
            FROM entrega e
            INNER JOIN grupo g ON g.ID = e.grupoid
            INNER JOIN evaluacion ev ON ev.ID = g.evaluacionID
            INNER JOIN curso c ON c.ID = ev.cursoid
            WHERE e.ID = :submissionId
            AND c.profesorusuarioid = :teacherId
            LIMIT 1
        ");

        $stmt->execute([
            ":submissionId" => $submissionId,
            ":teacherId" => $teacherId
        ]);

        $submission = $stmt->fetch(PDO::FETCH_ASSOC);

        return $submission ?: null;
    }



}
