<?php
session_start();

$error = "";
$success = "";

// ================= LOGIN =================
if (isset($_POST["login"])) {
    $data = [
        "username" => $_POST["username"],
        "password" => $_POST["password"],
        "role" => $_POST["role"]
    ];

    $ch = curl_init("http://localhost:3000/api/login");

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result["user"])) {
        session_start();
        $_SESSION["username"] = $result["user"]["username"];
        $_SESSION["role"] = $result["user"]["role"];

        if ($result["user"]["role"] === "Admin" || $result["user"]["role"] === "Market Manager") {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: user_dashboard.php");
        }
        exit;
    } else {
        echo $result["error"];
    }
}

// ================= REGISTER =================
if (isset($_POST["register"])) {
    $data = [
        "username" => $_POST["reg_username"],
        "password" => $_POST["reg_password"],
        "role" => $_POST["reg_role"]
    ];

    $ch = curl_init("http://localhost:3000/api/login/register");

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result["message"])) {
        $success = $result["message"];
    } else {
        $error = $result["error"];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>MarketHub Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">

    <style>
        body {
            background: #111;
            color: white;
            font-family: 'Poppins', sans-serif;
        }

        .bg-overlay {
            position: fixed;
            inset: 0;
            z-index: -1;
        }

        .card-glass {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(248, 244, 244, 0.2);
        }

        .hidden {
            display: none;
        }

        .role-card {
            background: rgba(255, 255, 255, 0.06);
            border-radius: 16px;
            cursor: pointer;
            transition: 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .role-card:hover {
            transform: translateY(-8px);
            background: rgba(255, 255, 255, 0.12);
        }

        .icon-box {
            width: 70px;
            height: 70px;
            margin: auto;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-box i {
            font-size: 30px;
            color: white;
        }
    </style>
</head>

<body>

    <div class="bg-overlay">
        <img src="assets/publicmarket.jpg" style="width:100%; height:100%; object-fit:cover; opacity:0.5;">
    </div>

    <div class="container d-flex align-items-center justify-content-center min-vh-100">

        <!-- ROLE SELECT -->
        <div id="roleStep" class="w-100" style="max-width:700px;">

            <div class="text-center mb-5">
                <h1 class="fw-bold">MarketHub</h1>
                <p class="text-muted">Public Market Stall Management System</p>
            </div>

            <div class="row g-4">

                <div class="col-md-4">
                    <div class="role-card text-center p-4" onclick="selectRole('Admin')">
                        <div class="icon-box bg-danger">
                            <i class="ri-shield-user-fill"></i>
                        </div>
                        <h5 class="mt-3">Admin</h5>
                        <small class="text-muted">Full system control</small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="role-card text-center p-4" onclick="selectRole('Market Manager')">
                        <div class="icon-box bg-warning">
                            <i class="ri-building-2-fill"></i>
                        </div>
                        <h5 class="mt-3">Manager</h5>
                        <small class="text-muted">Manage stalls & vendors</small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="role-card text-center p-4" onclick="selectRole('User')">
                        <div class="icon-box bg-success">
                            <i class="ri-store-2-fill"></i>
                        </div>
                        <h5 class="mt-3">Stall Owner</h5>
                        <small class="text-muted">View & manage your stall</small>
                    </div>
                </div>

            </div>

            <!-- ✅ CREATE ACCOUNT BUTTON HERE -->
            <div class="text-center mt-4">
                <button class="btn btn-outline-light" onclick="showRegisterFromRole()">
                    Create Account
                </button>
            </div>
        </div>

        <!-- LOGIN -->
        <div id="loginStep" class="w-100 hidden" style="max-width:400px;">
            <div class="card-glass p-4 rounded">

                <button class="btn btn-sm btn-secondary mb-3" onclick="goBack()">← Back</button>
                <h5 id="roleTitle"></h5>

                <form method="POST">
                    <input type="hidden" name="role" id="roleInput">

                    <input type="text" name="username" required class="form-control mb-2 bg-dark text-white">
                    <input type="password" name="password" required class="form-control mb-2 bg-dark text-white">

                    <button name="login" class="btn btn-primary w-100">Sign In</button>

                    <?php if ($error)
                        echo "<div class='alert alert-danger mt-2'>$error</div>"; ?>
                    <?php if ($success)
                        echo "<div class='alert alert-success mt-2'>$success</div>"; ?>
                </form>
            </div>
        </div>

        <!-- REGISTER -->
        <div id="registerStep" class="w-100 hidden" style="max-width:400px;">
            <div class="card-glass p-4 rounded">

                <!-- ✅ BACK TO ROLE -->
                <button class="btn btn-sm btn-secondary mb-3" onclick="backToRole()">← Back</button>

                <form method="POST">
                    <input type="text" name="reg_username" required class="form-control mb-2 bg-dark text-white">
                    <input type="password" name="reg_password" required class="form-control mb-2 bg-dark text-white">

                    <select name="reg_role" class="form-control mb-2 bg-dark text-white">
                        <option>User</option>
                        <option>Market Manager</option>
                    </select>

                    <button name="register" class="btn btn-success w-100">Register</button>
                </form>
            </div>
        </div>

    </div>

    <script>
        function selectRole(role) {
            document.getElementById("roleStep").classList.add("hidden");
            document.getElementById("loginStep").classList.remove("hidden");
            document.getElementById("roleTitle").innerText = "Sign in as " + role;
            document.getElementById("roleInput").value = role;
        }

        function goBack() {
            document.getElementById("loginStep").classList.add("hidden");
            document.getElementById("roleStep").classList.remove("hidden");
        }

        function showRegisterFromRole() {
            document.getElementById("roleStep").classList.add("hidden");
            document.getElementById("registerStep").classList.remove("hidden");
        }

        function backToRole() {
            document.getElementById("registerStep").classList.add("hidden");
            document.getElementById("roleStep").classList.remove("hidden");
        }
    </script>

</body>

</html>