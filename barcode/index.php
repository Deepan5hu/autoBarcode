<?php
include '../db/db_connect.php';
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: /autobarcode/auth/login.php");
    exit;
}

$username = $_SESSION['username'];
$store_id = $_SESSION['store_id'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto Barcode</title>
    <link rel="icon" href="/autobarcode/assets/images/title.png" type="image/icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link href="/autobarcode/styles/dashboard.css" rel="stylesheet">
    <link href="/autobarcode/styles/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        label {
            margin-left: 20px;
        }


        .body {
            background-color: #f5f5dc;
        }

        .my-custom-class {
            background-color: #a20000;
        }

        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            padding: 30px;
            max-width: 500px;
            width: 100%;
            margin: 0 auto;
        }

        .card h2 {
            font-family: Helvetica;
            font-weight: 800;
            font-size: 28px;
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .form-group label {
            font-weight: 600;
            color: #555;
        }

        .form-control {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border: 1px solid #ddd;
            padding: 15px;
            transition: box-shadow 0.3s ease;
        }

        .form-control:focus {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>

<body class="body">
    <header class="navbar sticky-top flex-md-nowrap p-0 shadow my-custom-class">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 c-nav navbar" href="/autobarcode/barcode/index.php">
            <img src="/autobarcode/assets/images/Logo-white.png" alt="MRP Dashboard Logo" class="img-fluid"
                style="height:50px">
        </a>
        <div class="col-md-3 text-end">
            <div class="w-100 text-end" style="color:white;font-weight:500;">
                <div class="dropdown">
                    Welcome,
                    <?php echo $_SESSION['username'] . '&nbsp Store: ';
                    if ($store_id == 1) {
                        echo "67";
                    } elseif ($store_id == 2) {
                        echo "114";
                    } elseif ($store_id == 3) {
                        echo "IP II";
                    } elseif ($store_id == 4) {
                        echo "Bhiwani";
                    }
                    ?>
                </div>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="card">
            <h2>Barcode Printer</h2>

            <form method="POST" action="barcodePrint.php" id="printForm" style="display: block;">
                <div class="form-group">
                    <label for="barcodeLifter">Scan Barcode:</label>
                    <input type="text" class="form-control" id="barcodeLifter" name="barcode"
                        placeholder="Scan barcode here" required autofocus maxlength="19"
                        oninput="this.value = this.value.toUpperCase()"
                        onkeypress="checkBarcodeInput(event, 'printForm')">
                </div>

            </form>

        </div>
        <div style="text-align: center;">
            <div class="col">
                <form action="/autobarcode/auth/logout.php" method="post">
                    <button type="submit" class="btn btn-danger" style="margin-top:20px">Logout</button>
                </form>
            </div>
        </div>
    </main>

    <script>

        let barcodeScanned = false;
        let barcodeValue = "";
        let timeoutId = null;

        function checkBarcodeInput(event, formId) {
            if (barcodeScanned) {
                event.preventDefault();
                return;
            }

            const inputField = event.target;
            barcodeValue += event.key;

            clearTimeout(timeoutId);

            timeoutId = setTimeout(() => {
                barcodeScanned = true;
                barcodeValue = barcodeValue.trim().replace(/Enter/g, '');;
                barcodeValue = barcodeValue.substring(0, 19);
                inputField.value = barcodeValue;
                inputField.setAttribute('readonly', true);
                barcodeValue = "";
            }, 100);
        }

        <?php
        if (isset($_GET['status'])) {
            if ($_GET['status'] == 'success') {
                echo "Swal.fire({
                title: 'Success',
                text: 'Barcode Printed successfully!',
                icon: 'success',
                timer: 1000,
                showConfirmButton: false
            }).then(function() {
                window.location.href = 'index.php';
            });";
            } elseif ($_GET['status'] == 'error') {
                echo "Swal.fire({
                title: 'Error',
                text: 'Failed to Print!',
                icon: 'error',
                timer: 1000,
                showConfirmButton: false
            }).then(function() {
                window.location.href = 'index.php';
            });";
            } elseif ($_GET['status'] == 'duplicate') {
                echo "Swal.fire({
                title: 'Error',
                text: 'Barcode already printed! Within Last 48 Hours.',
                icon: 'error',
                timer: 1000,
                showConfirmButton: false
            }).then(function() {
                window.location.href = 'index.php';
            });";
            }
        }
        ?>
    </script>

</body>

</html>