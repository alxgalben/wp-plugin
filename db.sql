-- Tabel criterii feedback
CREATE TABLE rt_feedback_criteria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

-- Tabel feedback
CREATE TABLE rt_feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transfer_code VARCHAR(50) NOT NULL,
    feedback_text TEXT
);

-- Tabel rating criterii feedback
CREATE TABLE rt_feedback_ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    feedback_id INT,
    criterion_id INT,
    rating INT,
    FOREIGN KEY (feedback_id) REFERENCES rt_feedback(id),
    FOREIGN KEY (criterion_id) REFERENCES rt_feedback_criteria(id)
);
