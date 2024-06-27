<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include '../db.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['secretary', 'head_of_secretary'])) {
    header("Location: ../other/dashboard.php");
    exit();
}

$course_id = $_POST['course'];
$exam_name = $_POST['exam_name'];
$exam_date = $_POST['exam_date'];
$start_time = $_POST['start_time'];
$end_time = $_POST['end_time'];
$num_assistants = $_POST['num_assistants'];
$num_classes = $_POST['num_classes'];

$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Checking for assistant conflicts: assistants who are taking the course should not be assigned to proctor its exams
$sql = "
    SELECT assistants.id, assistants.name 
    FROM assistants 
    JOIN assistant_courses ON assistants.id = assistant_courses.assistant_id 
    WHERE assistant_courses.course_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $course_id);
$stmt->execute();
$conflict_result = $stmt->get_result();

$conflicted_assistants = [];
while ($row = $conflict_result->fetch_assoc()) {
    $conflicted_assistants[] = $row['id'];
}

// Determine the faculty or department constraint based on the user's role
$constraint_sql = $role === 'head_of_secretary' ? "faculty_id = (SELECT faculty_id FROM users WHERE username = ?)" : "department_id = (SELECT department_id FROM users WHERE username = ?)";

// Fetch available assistants
$sql = "
    SELECT id, name 
    FROM assistants 
    WHERE $constraint_sql
    AND id NOT IN (SELECT assistant_id FROM assistant_courses WHERE course_id = ?)
    ORDER BY score ASC 
    LIMIT ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('sii', $username, $course_id, $num_assistants);
$stmt->execute();
$assistants_result = $stmt->get_result();

$available_assistants = [];
while ($assistant = $assistants_result->fetch_assoc()) {
    $available_assistants[] = $assistant['id'];
}

// Check if there are enough available assistants
if (count($available_assistants) < $num_assistants) {
    // Combine conflicted and available assistants for the message
    $conflicted_names = [];
    if (!empty($conflicted_assistants)) {
        $in  = str_repeat('?,', count($conflicted_assistants) - 1) . '?';
        $conflicted_stmt = $conn->prepare("SELECT name FROM assistants WHERE id IN ($in)");
        $types = str_repeat('i', count($conflicted_assistants));
        $conflicted_stmt->bind_param($types, ...$conflicted_assistants);
        $conflicted_stmt->execute();
        $conflict_result = $conflicted_stmt->get_result();

        while ($row = $conflict_result->fetch_assoc()) {
            $conflicted_names[] = $row['name'];
        }
    }
    $message = "Not enough assistants available. The following assistants are already enrolled in this course: " . implode(", ", $conflicted_names);
    header("Location: ../errors_conflict/conflict_warning.php?message=" . urlencode($message));
    exit();
}

// Insert the exam
$sql = "
    INSERT INTO exams (course_id, name, exam_date, start_time, end_time, num_assistants, num_classes) 
    VALUES (?, ?, ?, ?, ?, ?, ?)
";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param('issssii', $course_id, $exam_name, $exam_date, $start_time, $end_time, $num_assistants, $num_classes);
if (!$stmt->execute()) {
    die("Execution failed: " . $stmt->error);
}

// Get the inserted exam ID
$exam_id = $stmt->insert_id;

// Assign assistants to the exam
foreach ($available_assistants as $assistant_id) {
    $sql = "INSERT INTO exam_assignments (exam_id, assistant_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $exam_id, $assistant_id);
    if (!$stmt->execute()) {
        die("Assignment execution failed: " . $stmt->error);
    }

    // Update the assistant's score
    $sql = "UPDATE assistants SET score = score + 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $assistant_id);
    if (!$stmt->execute()) {
        die("Score update failed: " . $stmt->error);
    }
}

// Redirect based on the user's role
if ($role == 'head_of_secretary') {
    header("Location: ../pages/head_of_secretary_page.php");
} else {
    header("Location: ../pages/secretary_page.php");
}
exit();
?>

















