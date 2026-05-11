<?php
$conn=new mysqli("127.0.0.1","root","","bookstore_db");
if($conn->connect_error){echo "CONNECTERROR: ".$conn->connect_error; exit(1);} 
$sql="INSERT INTO products (name,price,category,description,image) VALUES ('Test Product', 10000, 'van_phong_pham', 'Test insert', '');";
if($conn->query($sql)===TRUE){ echo "INSERTED\n";} else { echo "ERROR: ".$conn->error; }
$res=$conn->query("SELECT COUNT(*) AS c FROM products"); $row=$res->fetch_assoc(); echo "COUNT|".$row['c']."\n";
?>
