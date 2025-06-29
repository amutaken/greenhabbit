<?php
// Database configuration
$servername = "localhost";
$username = "your_db_username";
$password = "your_db_password"; 
$dbname = "greenhabbit_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$name = $conn->real_escape_string($_POST['name']);
$email = $conn->real_escape_string($_POST['email']);
$phone = $conn->real_escape_string($_POST['phone']);
$service = $conn->real_escape_string($_POST['service']);
$message = $conn->real_escape_string($_POST['message']);

// 1. Save to Database
$stmt = $conn->prepare("INSERT INTO messages (name, email, phone, service, message) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $name, $email, $phone, $service, $message);

if ($stmt->execute()) {
    
    // 2. Send Email Notification
    $to = "habittgreen@gmail.com";
    $subject = "📢 New Contact: $name - $service";
    $email_content = "
    <html>
    <body>
        <h2>New Website Enquiry</h2>
        <p><strong>Name:</strong> $name</p>
        <p><strong>Phone:</strong> <a href='tel:$phone'>$phone</a></p>
        <p><strong>Email:</strong> <a href='mailto:$email'>$email</a></p>
        <p><strong>Service:</strong> $service</p>
        <p><strong>Message:</strong><br>".nl2br($message)."</p>
        <p>Received: ".date('d/m/Y h:i A')."</p>
    </body>
    </html>
    ";
    
    $headers = "From: website@greenhabbit.com\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    mail($to, $subject, $email_content, $headers);
    
    // 3. Send SMS Notification (using SMS Gateway API)
    $sms_api_key = "YOUR_MSG91_API_KEY"; // Get from MSG91 dashboard
    $sms_numbers = "919630175855,918770261130"; // Your numbers with country code
    
    $sms_message = urlencode("New enquiry from $name ($phone) for $service. Check email for details.");
    
    $sms_url = "https://api.msg91.com/api/sendhttp.php?".
               "authkey=$sms_api_key".
               "&mobiles=$sms_numbers".
               "&message=$sms_message".
               "&sender=GREENH". // Your 6-digit sender ID
               "&route=4". // Transactional route
               "&country=91"; // India country code
    
    file_get_contents($sms_url); // Trigger SMS
    
    echo "Thank you! Your message has been sent.";
    
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>