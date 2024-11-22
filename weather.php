<?php
$serverName = "localhost";
$userName = "rooy";
$password = ""; // Replace with the password you set
$dbName = "prototype2";

// Establishing the connection to the MySQL server
$conn = mysqli_connect($serverName, $userName, $password);

if ($conn) {
    echo "Connection Successful <br>";
} else {
    echo "Failed to connect. " . mysqli_connect_error();
    exit();
}

// Creating the database if it does not exist
$createDataBase = "CREATE DATABASE IF NOT EXISTS $dbName";
if (mysqli_query($conn, $createDataBase)) {
    echo "Database Created or already Exists <br>";
} else {
    echo "Failed to create database <br> " . mysqli_error($conn);
    exit();
}

// Selecting the database
mysqli_select_db($conn, $dbName);

// Creating the table if it does not exist
$createTable = "CREATE TABLE IF NOT EXISTS weather (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city VARCHAR(100) NOT NULL,
    humidity FLOAT NOT NULL,
    wind FLOAT NOT NULL,
    pressure FLOAT NOT NULL,
    temperature FLOAT NOT NULL,
    icon VARCHAR(50),
    condition_details VARCHAR(100) NOT NULL,
    date_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $createTable)) {
    echo "Table Created or already Exists <br>";
} else {
    echo "Failed to create table <br>" . mysqli_error($conn);
    exit();
}

// Checking if 'q' parameter is set and fetching the data
if (isset($_GET['q'])) {
    $city = $_GET['q'];
} else {
    $city = "Pokhara";
}

// Function to fetch data from OpenWeatherMap API
function fetchData($city, $conn) {
    $apiKey = "f179ee6faca65584816cd5fcf9bd2903";
    $url = "https://api.openweathermap.org/data/2.5/weather?q=$city&units=metric&appid=$apiKey"; 
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if (!$data || $data['cod'] != 200) {
        echo "Failed to fetch data from API.";
        return null;
    }
    
    $humidity = $data['main']['humidity'];
    $wind = $data['wind']['speed'];
    $pressure = $data['main']['pressure'];
    $temperature = $data['main']['temp'];
    $icon = $data['weather'][0]['icon'];
    $condition = $data['weather'][0]['description'];
    $city = $data['name'];

    $insertData = "INSERT INTO weather (city, humidity, wind, pressure, temperature, icon, condition_details, date_time)
                   VALUES ('$city', '$humidity', '$wind', '$pressure', '$temperature', '$icon', '$condition', NOW())";
    if (mysqli_query($conn, $insertData)) {
        echo "Data inserted Successfully";
    } else {
        echo "Failed to insert data: " . mysqli_error($conn);
    }
    
    return $data;
}

// Query to select the latest weather data for the specified city
$selectAllData = "SELECT * FROM weather WHERE city = '$city' ORDER BY date_time DESC LIMIT 1";
$result = mysqli_query($conn, $selectAllData);

if (mysqli_num_rows($result) == 0) {
    $data = fetchData($city, $conn);
} else {
    $currentTime = time();
    $row = mysqli_fetch_assoc($result);
    $last = strtotime($row['date_time']);
    if ($currentTime - $last > 2 * 3600) { // 2 hours in seconds
        $deleteQuery = "DELETE FROM weather WHERE city='$city'";
        if (mysqli_query($conn, $deleteQuery)) {
            $data = fetchData($city, $conn);
        } else {
            echo "Error deleting old data: " . mysqli_error($conn);
        }
    } else {
        $data = $row;
    }
}

// Encoding the data as JSON and outputting it
$json_data = json_encode($data);
header('Content-Type: application/json');
echo $json_data;

mysqli_close($conn); 
?>
