
<!DOCTYPE html>
<html>
<head>
    <title>INTI International College Penang</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            color: #292929;
            margin: 0;
        }

        .header {
            background-color: white;
            color: #e30613;
            padding: 20px;
            border-bottom: 4px solid #e30613;
        }

        .logo {
            width: 150px;
            height: auto;
            margin-bottom: 10px;
        }

        .content {
            padding: 30px 15px 45px;
        }

        .banner {
            width: 85%;
            max-width: 1000px;
            height: 340px;
            object-fit: cover;
            border-radius: 10px;
            margin: 25px auto;
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }

        .portal-buttons {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 25px;
        }

        .portal-btn {
            min-width: 170px;
            padding: 13px 20px;
            border-radius: 7px;
            font-weight: bold;
            text-decoration: none;
            transition: 0.3s;
        }

        .register-btn {
            background-color: #e30613;
            color: white;
        }

        .register-btn:hover {
            background-color: #b80510;
            color: white;
            transform: translateY(-3px);
        }

        .student-btn {
            background-color: white;
            color: #e30613;
            border: 2px solid #e30613;
        }

        .student-btn:hover {
            background-color: #e30613;
            color: white;
            transform: translateY(-3px);
        }

        .admin-btn {
            background-color: #292929;
            color: white;
        }

        .admin-btn:hover {
            background-color: #444444;
            color: white;
            transform: translateY(-3px);
        }

        .lecturer-btn {
            background-color: #666666;
            color: white;
        }

        .lecturer-btn:hover {
            background-color: #444444;
            color: white;
            transform: translateY(-3px);
        }

        footer {
            background-color: #292929;
            color: white;
            padding: 20px;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .banner {
                width: 100%;
                height: 230px;
            }

            .portal-buttons {
                flex-direction: column;
                align-items: center;
            }

            .portal-btn {
                width: 90%;
                text-align: center;
            }
        }
    </style>
</head>

<body>

    <!-- Header -->
    <div class="header text-center">

        <img src="inti-logo.png" class="logo" alt="INTI Logo">

        <h2>INTI INTERNATIONAL COLLEGE PENANG</h2>

        <p class="mb-0">Student Registration System</p>

    </div>

    <!-- Main Content -->
    <div class="container text-center content">

        <h1>Welcome to INTI</h1>

        <p>Start your academic journey with us.</p>

        <!-- Campus Image -->
        <img src="inti-campus.jpg"
             class="banner img-fluid"
             alt="INTI Campus">

        <h3 class="mt-4">Access Your Portal</h3>

        <p>Select your portal below to continue.</p>

        <!-- Portal Buttons -->
        <div class="portal-buttons">

            <a href="student_register.php"
               class="portal-btn register-btn">
                Student Registration
            </a>

            <a href="student_login.php"
               class="portal-btn student-btn">
                Student Login
            </a>

            <a href="admin_login.php"
               class="portal-btn admin-btn">
                Admin Login
            </a>

            <a href="lecturer_login.php"
               class="portal-btn lecturer-btn">
                Lecturer Login
            </a>

        </div>

    </div>

    <!-- Footer -->
    <footer class="text-center">

        <h5>INTI International College Penang</h5>

        <p class="mb-1">Student Registration System</p>

        <small>© 2026 INTI International College Penang. All Rights Reserved.</small>

    </footer>

</body>
</html>
