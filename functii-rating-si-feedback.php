function transferFeedbackListCriteria() {
    global $mysqli;
    $query = "SELECT * FROM rt_feedback_criteria";
    $result = $mysqli->query($query);

    $criteria = [];
    while ($row = $result->fetch_assoc()) {
        $criteria[] = $row;
    }

    return json_encode($criteria);
}

function transferFeedbackAddCriterion($name) {
    global $mysqli;
    $stmt = $mysqli->prepare("INSERT INTO rt_feedback_criteria (name) VALUES (?)");
    $stmt->bind_param("s", $name);
    return $stmt->execute();
}

function transferFeedbackEditCriterion($id, $newName) {
    global $mysqli;
    $stmt = $mysqli->prepare("UPDATE rt_feedback_criteria SET name = ? WHERE id = ?");
    $stmt->bind_param("si", $newName, $id);
    return $stmt->execute();
}

function transferFeedbackAddFeedback($transferCode, $ratings, $feedbackText = '') {
    global $mysqli;

    // inserare feedback
    $stmt = $mysqli->prepare("INSERT INTO rt_feedback (transfer_code, feedback_text) VALUES (?, ?)");
    $stmt->bind_param("ss", $transferCode, $feedbackText);
    $stmt->execute();
    $feedbackId = $stmt->insert_id;

    // inserare ratings pentru fiecare criteriu
    foreach ($ratings as $rating) {
        $stmt = $mysqli->prepare("INSERT INTO rt_feedback_ratings (feedback_id, criterion_id, rating) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $feedbackId, $rating['criterion_id'], $rating['rating']);
        $stmt->execute();
    }
    return true;
}

function transferFeedbackListFeedback($transferCode) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT * FROM rt_feedback WHERE transfer_code = ?");
    $stmt->bind_param("s", $transferCode);
    $stmt->execute();
    $result = $stmt->get_result();

    $feedbacks = [];
    while ($feedback = $result->fetch_assoc()) {
        $feedbacks[] = $feedback;
    }
    return json_encode($feedbacks);
}
