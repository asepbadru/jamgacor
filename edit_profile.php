<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'balap');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session and get user ID
session_start();
$userId = $_SESSION['user_id'] ?? 1; // Ganti default ID jika belum login

// Retrieve user data
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Update profile
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone_number'];
    $address = $_POST['address'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;

    // Handle file upload
    $profile_picture = $user['profile_picture']; // Default existing picture
    if (!empty($_FILES['profile_picture']['name'])) {
        $upload_dir = 'uploads_profile/'; // Change the upload directory
        $file_name = time() . '_' . basename($_FILES['profile_picture']['name']);
        $upload_file = $upload_dir . $file_name;

        // Check if the folder does not exist, create the folder
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Process file upload
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_file)) {
            $profile_picture = $upload_file; // Save file path to the database
        } else {
            $message = "Gagal mengunggah foto. Pastikan folder 'uploads_profile' memiliki izin menulis.";
        }
    }

    // Update query
    if ($password) {
        $sql = "UPDATE users SET name=?, email=?, password=?, phone_number=?, address=?, profile_picture=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $name, $email, $password, $phone, $address, $profile_picture, $userId);
    } else {
        $sql = "UPDATE users SET name=?, email=?, phone_number=?, address=?, profile_picture=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $name, $email, $phone, $address, $profile_picture, $userId);
    }

    if ($stmt->execute()) {
        $message = "Profil berhasil diperbarui!";
        header("Location: profile.php");
        exit;
    } else {
        $message = "Terjadi kesalahan: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <style>
        /* CSS Terintegrasi */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"], input[type="email"], input[type="password"], input[type="file"], textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        textarea {
            resize: vertical;
            height: 80px;
        }

        img {
            display: block;
            margin: 0 auto 15px auto;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #007bff;
        }

        .btn-container {
            display: flex;
            justify-content: space-between;
        }

        .btn {
            background-color: #007bff;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            width: 48%;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .btn-back {
            background-color: #6c757d;
        }

        .btn-back:hover {
            background-color: #5a6268;
        }

        .message {
            text-align: center;
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Profile</h2>
        <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="profile-picture">
                <img src="<?php echo $user['profile_picture'] ? $user['profile_picture'] : 'default-profile.png'; ?>" alt="Profile Picture">
            </div>

            <label for="name">Nama</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

            <label for="email">Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <label for="phone_number">Nomor Telepon</label>
            <input type="text" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>">

            <label for="address">Alamat</label>
            <textarea name="address"><?php echo htmlspecialchars($user['address']); ?></textarea>

            <label for="password">Password Baru (Kosongkan jika tidak diubah)</label>
            <input type="password" name="password">

            <label for="profile_picture">Foto Profil</label>
            <input type="file" name="profile_picture">

            <div class="btn-container">
                <button type="submit" class="btn">Simpan Perubahan</button>
                <a href="profile.php" class="btn btn-back">Kembali</a>
            </div>
        </form>
    </div>
</body>
</html>
