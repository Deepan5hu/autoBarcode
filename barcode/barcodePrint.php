<?php

include '../db/db_connect.php';

session_start();

$store_id = $_SESSION['store_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $barcode = $_POST['barcode'];

    $stage = 'Stage Name';

    $sql_check = "SELECT COUNT(*) AS count FROM printed_barcodes 
              WHERE barcode = ? 
              AND CONCAT(date, ' ', time) >= NOW() - INTERVAL 48 HOUR";

    $stmt = $conn->prepare($sql_check);
    $stmt->bind_param("s", $barcode);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        $status = "duplicate";
        header("Location: index.php?status=" . urlencode($status));
        exit();
    } else {

        // Function to send data to the printer by writing ZPL to a file and then sending the file to the printer
        function sendToPrinter($printerName, $zpl)
        {
            $filename = 'barcode.zpl';

            global $printStatus;
            $printStatus = 'false';

            // Write the ZPL to a file
            file_put_contents($filename, $zpl);

            // Determine the operating system and set the appropriate command
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Windows command using print
                $command = 'print /D:' . escapeshellarg('\\\\localhost\\' . $printerName) . ' ' . escapeshellarg($filename);
            } else {
                // Unix-like command
                $command = 'lpr -P ' . escapeshellarg($printerName) . ' ' . escapeshellarg($filename);
            }

            // Execute the command
            exec($command, $output, $return_var);

            // Check if the command was successful
            if ($return_var !== 0) {
                echo "Failed to send to printer. Command output: " . implode("\n", $output) . "\n";
            } else {
                $printStatus = 'true';
            }
        }

        $zpl = "^XA
            ^LH0,0
    
            ^FO220,0^BY3
            ^BCN,50,N,N,N
            ^FD" . $barcode . "^FS
            ^FO360,55.^A0N,25,40^FD" . $barcode . "^FS
    
            ^XZ";

        // Send the ZPL to the printer
        
        // for ($i = 0; $i < 2; $i++) {
        //     sendToPrinter('TSC TTP-345', $zpl);
        // }

        sendToPrinter('TSC TTP-345', $zpl);

    }

    if ($printStatus == 'true') {

        $sql = "INSERT INTO printed_barcodes (barcode, stage, store_id) VALUES (?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $barcode, $stage, $store_id);
        $stmt->execute();

        $stmt->close();
        $conn->close();

        $status = "success";
        header("Location: index.php?status=" . urlencode($status));
    } else {
        $status = "error";
        header("Location: index.php?status=" . urlencode($status));
    }

}

?>