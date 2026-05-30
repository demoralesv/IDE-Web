<?php

class Student {
    private PDO $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function registerStudent($name, $lastname, $email, $userPassword) {
        try {
            $this->conn->beginTransaction();

            $checkStmt = $this->conn->prepare("
                SELECT ID 
                FROM usuario 
                WHERE correo = :correo
                LIMIT 1
            ");

            $checkStmt->execute([
                ":correo" => $email
            ]);

            $existingUser = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existingUser) {
                $this->conn->rollBack();
                return false;
            }

            $hashedPassword = password_hash($userPassword, PASSWORD_DEFAULT);

            $userStmt = $this->conn->prepare("
                INSERT INTO usuario (nombre, apellido1, correo, password)
                VALUES (:nombre, :apellido1, :correo, :password)
            ");

            $userInserted = $userStmt->execute([
                ":nombre" => $name,
                ":apellido1" => $lastname,
                ":correo" => $email,
                ":password" => $hashedPassword
            ]);

            if (!$userInserted) {
                $this->conn->rollBack();
                return false;
            }

            $userId = (int) $this->conn->lastInsertId();

            $ideStmt = $this->conn->prepare("
                INSERT INTO ide (ID, config)
                VALUES (:ID, :config)
            ");

            $ideInserted = $ideStmt->execute([
                ":ID" => $userId,
                ":config" => null
            ]);

            if (!$ideInserted) {
                $this->conn->rollBack();
                return false;
            }

            $studentStmt = $this->conn->prepare("
                INSERT INTO estudiante (ID, ideid)
                VALUES (:ID, :ideid)
            ");

            $studentInserted = $studentStmt->execute([
                ":ID" => $userId,
                ":ideid" => $userId
            ]);

            if (!$studentInserted) {
                $this->conn->rollBack();
                return false;
            }

            $this->conn->commit();

            return [
                "ID" => $userId,
                "nombre" => $name,
                "apellido1" => $lastname,
                "correo" => $email,
                "ideid" => $userId
            ];

        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            error_log("Error al registrar estudiante: " . $e->getMessage());
            return false;
        }
    }

    public function loginStudent($email, $userPassword) {
        $stmt = $this->conn->prepare("
            SELECT 
                u.ID,
                u.nombre,
                u.apellido1,
                u.correo,
                u.password,
                e.ideid,
                i.config
            FROM usuario u
            INNER JOIN estudiante e ON e.ID = u.ID
            LEFT JOIN ide i ON i.ID = e.ideid
            WHERE u.correo = :correo
            LIMIT 1
        ");

        $stmt->execute([
            ":correo" => $email
        ]);

        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            return false;
        }

        if (!password_verify($userPassword, $student["password"])) {
            return false;
        }

        unset($student["password"]);

        return $student;
    }

    public function getCoursesWithAssignments($studentId) {
        $stmt = $this->conn->prepare("
            SELECT 
                c.ID AS cursoID,
                c.nombre AS cursoNombre,
                c.codigo AS cursoCodigo,
                c.grupo AS cursoGrupo,

                ev.ID AS evaluacionID,
                ev.titulo AS evaluacionTitulo,
                ev.descripcion AS evaluacionDescripcion,
                ev.adjunto AS evaluacionAdjunto,
                ev.fechaentrega AS evaluacionFechaEntrega

            FROM curso c

            INNER JOIN estudiante_curso ec 
                ON ec.cursoID = c.ID

            LEFT JOIN evaluacion ev 
                ON ev.cursoid = c.ID

            WHERE ec.estudianteusuarioID = :studentId

            ORDER BY 
                c.nombre ASC,
                c.grupo ASC,
                ev.fechaentrega ASC
        ");

        $stmt->execute([
            ":studentId" => $studentId
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $courses = [];

        foreach ($rows as $row) {
            $courseId = (int) $row["cursoID"];

            if (!isset($courses[$courseId])) {
                $courses[$courseId] = [
                    "ID" => $courseId,
                    "nombre" => $row["cursoNombre"],
                    "codigo" => $row["cursoCodigo"],
                    "grupo" => $row["cursoGrupo"],
                    "tareas" => []
                ];
            }

            if ($row["evaluacionID"] !== null) {
                $courses[$courseId]["tareas"][] = [
                    "ID" => (int) $row["evaluacionID"],
                    "titulo" => $row["evaluacionTitulo"],
                    "descripcion" => $row["evaluacionDescripcion"],
                    "adjunto" => $row["evaluacionAdjunto"],
                    "fechaEntrega" => $row["evaluacionFechaEntrega"]
                ];
            }
        }

        return array_values($courses);
    }
}