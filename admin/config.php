<?php
// Hata raporlama ayarları
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/error.log');

// Admin kullanıcı bilgileri
define('ADMIN_USERNAME', getenv('ADMIN_USERNAME') ?: 'admin');
define('ADMIN_PASSWORD_HASH', getenv('ADMIN_PASSWORD_HASH') ?: '$2y$10$YourDefaultHashHere'); // Varsayılan şifre: Nua2024!

$jawsdb_url = getenv("JAWSDB_URL");

if ($jawsdb_url) {
    $url = parse_url($jawsdb_url);
    $servername = $url["host"];
    $username = $url["user"];
    $password = $url["pass"];
    $dbname = ltrim($url["path"], '/');
} else {
    // Yerel geliştirme ortamı için
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "nuayapi";
}

// Veritabanı bağlantısı
$conn = new mysqli("p:" . $servername, $username, $password, $dbname);

// Bağlantı kontrolü
if ($conn->connect_error) {
    error_log("Database Connection Error: " . $conn->connect_error);
    die("Bağlantı hatası: " . $conn->connect_error);
}

// Türkçe karakter desteği için tek bir ayar yeterli
$conn->set_charset("utf8mb4");

// Test query to check if tables exist
$test_query = "SHOW TABLES LIKE 'properties'";
$result = $conn->query($test_query);
if ($result->num_rows == 0) {
    // Properties tablosu yoksa oluştur
    $create_table_query = "CREATE TABLE IF NOT EXISTS properties (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        price DECIMAL(15,2),
        status VARCHAR(50),
        room_count INT,
        location VARCHAR(255),
        neighborhood VARCHAR(255),
        description TEXT,
        property_type VARCHAR(50),
        gross_area DECIMAL(10,2),
        net_area DECIMAL(10,2),
        floor_location VARCHAR(50),
        total_floors INT,
        heating VARCHAR(100),
        bathroom_count INT,
        balcony VARCHAR(50),
        furnished VARCHAR(50),
        site_status VARCHAR(50),
        site_name VARCHAR(255),
        eligible_for_credit VARCHAR(50),
        building_age VARCHAR(50),
        living_room INT,
        parking VARCHAR(50),
        usage_status VARCHAR(50),
        video_call_available VARCHAR(50),
        video_file VARCHAR(255),
        zoning_status VARCHAR(100),
        block_no VARCHAR(50),
        parcel_no VARCHAR(50),
        sheet_no VARCHAR(50),
        floor_area_ratio VARCHAR(50),
        height_limit VARCHAR(50),
        deed_status VARCHAR(100),
        price_per_sqm DECIMAL(12,2),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (!$conn->query($create_table_query)) {
        error_log("Table creation error: " . $conn->error);
        die("Tablo oluşturma hatası: " . $conn->error);
    }
}

// Property Images tablosunu kontrol et ve oluştur
$test_query = "SHOW TABLES LIKE 'property_images'";
$result = $conn->query($test_query);
if ($result->num_rows == 0) {
    $create_images_table = "CREATE TABLE IF NOT EXISTS property_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        property_id INT NOT NULL,
        image_name VARCHAR(255) NOT NULL,
        is_featured BOOLEAN DEFAULT FALSE,
        display_order INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (!$conn->query($create_images_table)) {
        error_log("Images table creation error: " . $conn->error);
        die("Resim tablosu oluşturma hatası: " . $conn->error);
    }
}

// property_images tablosunda is_featured sütununu kontrol et ve ekle
$check_featured_column = "SHOW COLUMNS FROM property_images LIKE 'is_featured'";
$result = $conn->query($check_featured_column);
if ($result->num_rows == 0) {
    $add_featured_column = "ALTER TABLE property_images ADD COLUMN is_featured BOOLEAN DEFAULT FALSE AFTER image_name";
    if (!$conn->query($add_featured_column)) {
        error_log("Add is_featured column error: " . $conn->error);
        die("is_featured sütunu ekleme hatası: " . $conn->error);
    }
}

// Agents tablosunu kontrol et ve oluştur
$test_query = "SHOW TABLES LIKE 'agents'";
$result = $conn->query($test_query);
if ($result->num_rows == 0) {
    $create_agents_table = "CREATE TABLE IF NOT EXISTS agents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        agent_name VARCHAR(255) NOT NULL,
        agent_title VARCHAR(255) DEFAULT 'Gayrimenkul Danışmanı',
        agent_photo VARCHAR(255),
        agent_phone VARCHAR(20),
        agent_email VARCHAR(255),
        twitter_url VARCHAR(255),
        facebook_url VARCHAR(255),
        instagram_url VARCHAR(255),
        linkedin_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (!$conn->query($create_agents_table)) {
        error_log("Agents table creation error: " . $conn->error);
        die("Danışman tablosu oluşturma hatası: " . $conn->error);
    }

    // Örnek danışman ekle
    $insert_agent = "INSERT INTO agents (agent_name, agent_title, agent_phone, agent_email) 
                    VALUES ('Ahmet Yılmaz', 'Kıdemli Gayrimenkul Danışmanı', '0530 441 68 73', 'bilgi@didim.com')";
    $conn->query($insert_agent);
}

// Agents tablosundaki eksik sütunları kontrol et ve ekle
$required_agent_columns = [
    'agent_title' => "VARCHAR(255) DEFAULT 'Gayrimenkul Danışmanı'",
    'agent_photo' => 'VARCHAR(255)',
    'agent_phone' => 'VARCHAR(20)',
    'agent_email' => 'VARCHAR(255)',
    'twitter_url' => 'VARCHAR(255)',
    'facebook_url' => 'VARCHAR(255)',
    'instagram_url' => 'VARCHAR(255)',
    'linkedin_url' => 'VARCHAR(255)'
];

$check_agent_columns_query = "SHOW COLUMNS FROM agents";
$result = $conn->query($check_agent_columns_query);
$existing_agent_columns = [];
while($row = $result->fetch_assoc()) {
    $existing_agent_columns[] = $row['Field'];
}

foreach ($required_agent_columns as $column => $type) {
    if (!in_array($column, $existing_agent_columns)) {
        $alter_query = "ALTER TABLE agents ADD COLUMN $column $type";
        if (!$conn->query($alter_query)) {
            error_log("Alter agents table error for column $column: " . $conn->error);
            die("Danışman tablosu güncelleme hatası: " . $conn->error);
        }
    }
}

// Mevcut sütunları kontrol et ve eksik olanları ekle
$required_columns = [
    'status' => 'VARCHAR(50)',
    'room_count' => 'INT',
    'block_no' => 'VARCHAR(50)',
    'parcel_no' => 'VARCHAR(50)',
    'sheet_no' => 'VARCHAR(50)',
    'floor_area_ratio' => 'VARCHAR(50)',
    'height_limit' => 'VARCHAR(50)',
    'deed_status' => 'VARCHAR(100)',
    'price_per_sqm' => 'DECIMAL(12,2)',
    'video_call_available' => 'VARCHAR(50)',
    'video_file' => 'VARCHAR(255)',
    'site_name' => 'VARCHAR(255)',
    'living_room' => 'INT',
    'agent_id' => 'INT',
    'agent_name' => 'VARCHAR(255)',
    'agent_phone' => 'VARCHAR(20)',
    'agent_email' => 'VARCHAR(255)'
];

$check_columns_query = "SHOW COLUMNS FROM properties";
$result = $conn->query($check_columns_query);
$existing_columns = [];
while($row = $result->fetch_assoc()) {
    $existing_columns[] = $row['Field'];
}

foreach ($required_columns as $column => $type) {
    if (!in_array($column, $existing_columns)) {
        $alter_query = "ALTER TABLE properties ADD COLUMN $column $type";
        if (!$conn->query($alter_query)) {
            error_log("Alter table error for column $column: " . $conn->error);
            die("Tablo güncelleme hatası: " . $conn->error);
        }
    }
}
?> 