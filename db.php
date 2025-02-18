
$host = 'pg';
$db = 'db';
$user = 'user';
$password = 'pass';

$dsn = "pgsql:host=$host;port=5432;dbname=$db;";

try {
    $conn = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>