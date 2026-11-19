
<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - INTI</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f8f3ee;
            font-family: Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px 15px;
        }

        .dashboard {
            background: white;
            width: 100%;
            max-width: 850px;
            padding: 40px;
            border-radius: 14px;
            border-top: 5px solid #ed0010;
            box-shadow: 0 8px 22px rgba(0,0,0,0.08);
        }

        h2 {
            color: #ed0010;
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }

        .menu {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .menu a {
            background: #fff7f5;
            color: #262626;
            text-decoration: none;
            text-align: center;
            padding: 28px 15px;
            border: 1px solid #f1dada;
            border-radius: 10px;
            font-weight: bold;
            transition: 0.2s;
        }

        .menu a:hover {
            background: #ed0010;
            color: white;
            border-color: #ed0010;
        }

        .bottom-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .bottom-buttons a {
            flex: 1;
            text-align: center;
            padding: 13px;
            border-radius: 7px;
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        .logout {
            background: #ed0010;
        }

        .home {
            background: #262626;
        }

        .logout:hover {
            background: #c9000d;
        }

        .home:hover {
            background: #444;
        }

        @media (max-width: 550px) {
            .dashboard {
                padding: 25px 18px;
            }

            .menu {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <div class="dashboard">

        <h2>Admin Dashboard</h2>

        <p class="subtitle">
            Welcome, Admin. Manage your college system below.
        </p>

        <hr>

        <div class="menu">

            <a href="admin_add_subject.php">
                Add Subject
            </a>

            <a href="admin_add_course.php">
                Add Course
            </a>

            <a href="admin_add_lecturer.php">
                Add Lecturer
            </a>

            <a href="admin_student_list.php">
                View Students
            </a>

        </div>

        <div class="bottom-buttons">

            <a href="admin_logout.php" class="logout">
                Logout
            </a>

            <a href="index.php" class="home">
                Return to Home
            </a>

        </div>

    </div>

</body>
</html>
