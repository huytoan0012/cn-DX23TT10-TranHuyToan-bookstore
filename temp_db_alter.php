<?php
$conn=new mysqli("127.0.0.1","root","","bookstore_db");
if($conn->connect_error){echo "CONNECTERROR: ".$conn->connect_error; exit(1);} 
$columns = ['author','publisher'];
foreach ($columns as $col) {
    $res = $conn->query("SHOW COLUMNS FROM products LIKE '$col'");
    if ($res && $res->num_rows === 0) {
        $sql = "ALTER TABLE products ADD COLUMN $col VARCHAR(255) DEFAULT '' AFTER category";
        if ($conn->query($sql) === TRUE) {
            echo "ADDED $col\n";
        } else {
            echo "ERROR ADD $col: ".$conn->error."\n";
        }
    } else {
        echo "EXISTS $col\n";
    }
}
?>
