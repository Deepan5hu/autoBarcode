while ($true) {
    # Run the PHP script
    Start-Process -FilePath "D:\xampp\php\php.exe" -ArgumentList "D:\xampp\htdocs\autobarcode\dataUpload.php"

    # Wait for 5 minutes
    Start-Sleep -Seconds 300
}
