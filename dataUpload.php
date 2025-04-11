<?php

// powershell -ExecutionPolicy Bypass -File "C:\xampp\htdocs\autobarcode\cron_job.ps1"

date_default_timezone_set('Asia/Kolkata');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "autobarcode";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$live_servername = "srv1326.hstgr.io";
$live_username = "u191152043_umanmrp";
$live_password = "882681Gautam";
$live_dbname = "u191152043_umanmrp";

$live_conn = new mysqli($live_servername, $live_username, $live_password, $live_dbname);

if ($live_conn->connect_error) {
    die("Live database connection failed: " . $live_conn->connect_error);
}

$sql_fetch = "SELECT sno, barcode, stage, date, time, store_id FROM printed_barcodes WHERE status = 1";
$result = $conn->query($sql_fetch);

if ($result && $result->num_rows > 0) {
    $sql_insert = $live_conn->prepare(
        "INSERT INTO barcodes_printed (barcode, stage, date, time, store_id, uploaded_timestamp) 
         VALUES (?, ?, ?, ?, ?, ?)"
    );

    if (!$sql_insert) {
        die("Failed to prepare live table insert statement: " . $live_conn->error);
    }

    // Loop through results and upload each entry
    while ($row = $result->fetch_assoc()) {
        $uploaded_timestamp = date('Y-m-d H:i:s');

        $sql_insert->bind_param(
            'ssssss',
            $row['barcode'],
            $row['stage'],
            $row['date'],
            $row['time'],
            $row['store_id'],
            $uploaded_timestamp
        );

        // Execute and check for successful upload
        if ($sql_insert->execute()) {
            $update_sql = "UPDATE printed_barcodes SET status = 2 WHERE sno = ?";
            $update_stmt = $conn->prepare($update_sql);
            if ($update_stmt) {
                $update_stmt->bind_param('i', $row['sno']);
                $update_stmt->execute();
                $update_stmt->close();
            } else {
                error_log("Failed to prepare update statement for sno: " . $row['sno']);
            }
        }
    }

    // Close the prepared statement
    $sql_insert->close();
} else {
    echo "No data to upload.";
}

// Close both connections
$conn->close();
$live_conn->close();

echo "Data upload complete.";
?>