<?php
include 'includes/db_connect.php';

if(isset($_GET['type']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $type = $_GET['type'];
    
    if($type == 'question') {
        $conn->query("DELETE FROM questions WHERE id=$id");
        header("Location: view_questions.php");
    }
    // Add other delete logic here if needed
}
?>