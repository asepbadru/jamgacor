<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN BIAR GACORR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color:rgb(105, 215, 225);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #ffffff;
        }

        header {
            background-color: #24292f;
            color: white;
            width: 100%;
            padding: 15px 0;
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            position: fixed;
            top: 0;
            z-index: 10;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .modal-container {
            background: #2d2d2d;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            width: 420px;
            max-width: 90%;
            padding: 30px;
            margin-top: 100px;
            transition: all 0.3s ease-in-out;
        }

        .modal-container h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 26px;
            color: #e1e1e1;
        }

        .modal-container form {
            display: flex;
            flex-direction: column;
        }

        .modal-container label {
            font-weight: bold;
            margin-bottom: 8px;
            color: #e1e1e1;
        }

        .modal-container input,
        .modal-container select,
        .modal-container textarea,
        .modal-container button {
            margin-bottom: 15px;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #444;
            border-radius: 5px;
            background-color: #333;
            color: #e1e1e1;
        }

        .modal-container button {
            background-color: #007bff;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .modal-container button:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }

        .modal-container p {
            text-align: center;
        }

        .modal-container p a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        .modal-container p a:hover {
            text-decoration: underline;
        }

        footer {
            background-color: #24292f;
            color: white;
            text-align: center;
            padding: 12px 0;
            width: 100%;
            position: fixed;
            bottom: 0;
            box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Styling for input focus */
        .modal-container input:focus, .modal-container select:focus, .modal-container textarea:focus {
            border-color: #007bff;
            outline: none;
        }
    </style>
</head>
<body>
   

    <!-- Login Modal -->
    <div class="modal-container" id="loginModal">
        <h2>Login</h2>
        <form id="loginForm" method="POST" action="login.php">
            <label for="username">Email:</label>
            <input type="text" id="username" name="username" placeholder="Masukkan email" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Masukkan password" required>
            <label for="role">Role:</label>
            <select id="role" name="role" required>
                <option value="customer">Customer</option>
                <option value="admin">Admin</option>
            </select>
            <button type="submit">Login</button>
        </form>
        <p id="registrationLink">Belum punya akun? <a href="#" onclick="toggleModal('registerModal')">Registrasi</a></p>
    </div>

    <!-- Register Modal -->
    <div class="modal-container" id="registerModal" style="display: none;">
        <h2>Registrasi</h2>
        <form method="POST" action="register.php">
            <label for="name">Nama:</label>
            <input type="text" id="name" name="name" placeholder="Masukkan nama" required>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Masukkan email" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Masukkan password" required>
            <label for="phone_number">Nomor HP:</label>
            <input type="text" id="phone_number" name="phone_number" placeholder="Masukkan nomor HP">
            <label for="address">Alamat:</label>
            <textarea id="address" name="address" placeholder="Masukkan alamat"></textarea>
            <button type="submit">Daftar</button>
        </form>
        <p>Sudah punya akun? <a href="#" onclick="toggleModal('loginModal')">Login</a></p>
    </div>

    

    <script>
    // Fungsi untuk mengatur tampilan link registrasi berdasarkan role
    function toggleRegistrationLink() {
        const role = document.getElementById('role').value;
        const registrationLink = document.getElementById('registrationLink');

        // Sembunyikan link jika role adalah admin
        if (role === 'admin') {
            registrationLink.style.display = 'none';
        } else {
            registrationLink.style.display = 'block';
        }
    }

    // Tambahkan event listener untuk perubahan dropdown role
    document.getElementById('role').addEventListener('change', toggleRegistrationLink);

    // Atur tampilan awal saat halaman dimuat
    window.onload = () => {
        toggleRegistrationLink();
    };

    function toggleModal(modalId) {
        document.getElementById('loginModal').style.display = 'none';
        document.getElementById('registerModal').style.display = 'none';
        document.getElementById(modalId).style.display = 'block';
    }
</script>
</body>
</html>
