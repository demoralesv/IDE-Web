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
}