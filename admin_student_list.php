<?php

session_start();

include 'config_database.php';

$result = $pdo->query(
    "SELECT * FROM students"
);
?>

<h2>Registered Students</h2>

<table border="1">

    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
    </tr>

    <?php
    foreach ($result as $row) {
    ?>
        <tr>

            <td>
                <?= $row['id'] ?>
            </td>

            <td>
                <?= $row['fullname'] ?>
            </td>

            <td>
                <?= $row['email'] ?>
            </td>

        </tr>
    <?php
    }
    ?>

</table>