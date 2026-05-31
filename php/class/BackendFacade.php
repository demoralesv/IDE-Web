<?php

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/Teacher.php';
require_once __DIR__ . '/Course.php';
require_once __DIR__ . '/UserService.php';

class BackendFacade {
    private Auth $auth;
    private Teacher $teacher;
    private Course $course;
    private UserService $userService;

    public function __construct() {
        $database = new Database();
        $conn = $database->getConnection();

        $this->auth = new Auth($conn);
        $this->teacher = new Teacher($conn);
        $this->course = new Course($conn);
        $this->userService = new UserService($conn);
    }

    public function registerUser(
        string $nombre,
        string $apellido,
        string $correo,
        string $password
    ): array {
        return $this->auth->registerUser($nombre, $apellido, $correo, $password);
    }

    public function logSession(string $correo, string $password): bool {
        return $this->auth->login($correo, $password);
    }

    public function getTeacherId(string $correo): ?int {
        return $this->teacher->getTeacherId($correo);
    }

    public function getTeacherName(string $correo): ?string {
        return $this->teacher->getTeacherName($correo);
    }

    public function getCoursesByTeacher(int $teacherId): array {
        return $this->course->getCoursesByTeacher($teacherId);
    }

    public function getCourseByIdAndTeacher(int $courseId, int $teacherId): ?array {
        $course = $this->course->getCourseByIdAndTeacher($courseId, $teacherId);
        return $course ?: null;
    }

    public function addCourse(string $name, int $code, int $group, int $teacherId): bool {
        return $this->course->addCourse($name, $code, $group, $teacherId);
    }

    public function getTeacherStatistics(int $teacherId): array {
        return $this->teacher->getTeacherStatistics($teacherId);
    }

    public function getCourseStatistics(int $courseId): array {
        return $this->course->getCourseStatistics($courseId);
    }

    public function getStudentsByCourse(int $courseId): array {
        return $this->course->getStudentsByCourse($courseId);
    }

    public function deleteCourse(int $courseId, int $teacherId): bool {
        return $this->course->deleteCourse($courseId, $teacherId);
    }

    public function obtainUsers(): array {
        return $this->userService->obtainUsers();
    }

    public function getStudentsNotInCourse(int $courseId, string $search = ""): array {
        return $this->course->getStudentsNotInCourse($courseId, $search);
    }

    public function addStudentToCourse(int $courseId, int $studentId): bool {
        return $this->course->addStudentToCourse($courseId, $studentId);
    }

}