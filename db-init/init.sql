-- db-init/init.sql

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- still better than a printf or consol.log right ? ... right ?
INSERT INTO users (username, password) VALUES (
    'test',
    '$2y$10$IkpXKfbIN5ntjGu6qTHXee2HQTXMAAlVAlP7nYBilPUeG.JqaTgKi'
);
