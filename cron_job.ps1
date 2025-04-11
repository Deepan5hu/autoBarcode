while ($true) {
    # Run the PHP script
    Start-Process -FilePath "C:\xampp\php\php.exe" -ArgumentList "C:\xampp\htdocs\autobarcode\dataUpload.php"

    # Wait for 5 minutes
    Start-Sleep -Seconds 300
}
