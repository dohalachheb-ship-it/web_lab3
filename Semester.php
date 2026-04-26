<?php
class Semester {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function create($label, $academic_year) {
        $stmt = $this->pdo->prepare("INSERT INTO semesters (label, academic_year) VALUES (?, ?)");
        return $stmt->execute([$label, $academic_year]);
    }

    public function getAll() {
        $stmt = $this->pdo->prepare("SELECT * FROM semesters ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>