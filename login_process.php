<?php
session_start();
include 'includes/db_config.php';

$username = mysqli_real_escape_string($conn, trim($_POST['username']));
$password = $_POST['password'];

$sql = "SELECT * FROM user WHERE Username = '$username'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 1) {
    $user = mysqli_fetch_assoc($result);
    $storedPassword = $user['Password'];
    $loginOk = false;

    // Hỗ trợ cả mật khẩu đã hash và mật khẩu cũ lưu thẳng
    if (password_verify($password, $storedPassword)) {
        $loginOk = true;
    } elseif ($password === $storedPassword) {
        $loginOk = true;
        // Tự động cập nhật tài khoản cũ sang hash mới
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE user SET Password='$newHash' WHERE ID={$user['ID']}");
    }

    if ($loginOk) {
        session_regenerate_id(true);

        unset($user['Password']);
        // Đảm bảo Avatar luôn có giá trị
        if (empty($user['Avatar']) || $user['Avatar'] === NULL || $user['Avatar'] === 'NULL') {
            $user['Avatar'] = 'default.png';
        }
        
        // Chuẩn hóa tên field 'role' (xử lý cả 'Role' và 'role')
        if (isset($user['Role']) && !isset($user['role'])) {
            $user['role'] = $user['Role'];
        }
        
        $_SESSION['user'] = $user;

        header("Location: index.php");
        exit();
    }

    echo "Sai mật khẩu!";
} else {
    echo "Không tồn tại user!";
}
