<?php

require_once 'includes/db.php';

session_start();

function auth_login($email, $password) {
    //1. Read email and password in plaintext from POST
    //2. Construct a SQL query
    //3. Execute sql query and read the results
    //4. If no results, show login failed error, if success write user to session
    $conn = db();
    $query = sprintf('SELECT * FROM users WHERE email=? AND password=?');
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $email, $password);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        if (isset($user) && password_verify($password, $user['password'])) { // if password matches // password_verify($password, $user['password'])
            $stmt->close();
            $_SESSION['id'] = $user['id'];
            $_SESSION['username'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['verified'] = $user['verified'];
            $_SESSION['message'] = "You are logged in!";
            $_SESSION['type'] = 'alert-success';
            return true;
        } else {
            $_SESSION['message'] = "Wrong username / password!";
            $_SESSION['type'] = "alert-danger";
            return false;
        }
    } else {
        $_SESSION['message'] = "Database error. Login failed!";
        $_SESSION['type'] = "alert-danger";
        return false;
    }

}

function do_logout() {
    //Delete user to session
}

function is_logged_in() {
    return get_user();
}


function auth_get_user() {
    return $_SESSION['user'] ?? null;
}
