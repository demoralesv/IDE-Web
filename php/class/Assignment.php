<?php

class Assignment {
    private PDO $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function createAssignment(
        int $courseId,
        string $title,
        string $description,
        string $attachment,
        string $dueDate
    ): bool {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO evaluacion (descripcion, adjunto, fechaentrega, titulo, cursoid)
                VALUES (:descripcion, :adjunto, :fechaentrega, :titulo, :cursoid)
            ");

            return $stmt->execute([
                ":descripcion" => $description,
                ":adjunto" => $attachment,
                ":fechaentrega" => $dueDate,
                ":titulo" => $title,
                ":cursoid" => $courseId
            ]);

        } catch (PDOException $e) {
            error_log("Error al crear evaluación: " . $e->getMessage());
            return false;
        }
    }

    public function getAssignmentsByCourse(int $courseId): array {
        $stmt = $this->conn->prepare("
            SELECT 
                ID,
                titulo,
                descripcion,
                adjunto,
                fechaentrega,
                cursoid
            FROM evaluacion
            WHERE cursoid = :courseId
            ORDER BY fechaentrega DESC
        ");

        $stmt->execute([
            ":courseId" => $courseId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAssignmentByIdAndCourse(int $assignmentId, int $courseId): ?array {
        $stmt = $this->conn->prepare("
            SELECT 
                ID,
                titulo,
                descripcion,
                adjunto,
                fechaentrega,
                cursoid
            FROM evaluacion
            WHERE ID = :assignmentId
            AND cursoid = :courseId
            LIMIT 1
        ");

        $stmt->execute([
            ":assignmentId" => $assignmentId,
            ":courseId" => $courseId
        ]);

        $assignment = $stmt->fetch(PDO::FETCH_ASSOC);

        return $assignment ?: null;
    }

    public function getSubmissionsByAssignment(int $assignmentId): array {
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
            WHERE g.evaluacionID = :assignmentId
            ORDER BY e.fechaentrega DESC
        ");

        $stmt->execute([
            ":assignmentId" => $assignmentId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateAssignment(
        int $assignmentId,
        int $courseId,
        string $title,
        string $description,
        string $attachment,
        string $dueDate
    ): bool {
        try {
            $stmt = $this->conn->prepare("
                UPDATE evaluacion
                SET 
                    titulo = :titulo,
                    descripcion = :descripcion,
                    adjunto = :adjunto,
                    fechaentrega = :fechaentrega
                WHERE ID = :assignmentId
                AND cursoid = :courseId
            ");

            return $stmt->execute([
                ":titulo" => $title,
                ":descripcion" => $description,
                ":adjunto" => $attachment,
                ":fechaentrega" => $dueDate,
                ":assignmentId" => $assignmentId,
                ":courseId" => $courseId
            ]);

        } catch (PDOException $e) {
            error_log("Error al actualizar evaluación: " . $e->getMessage());
            return false;
        }
    }
}