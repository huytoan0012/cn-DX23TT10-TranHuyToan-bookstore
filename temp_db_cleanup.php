<?php
$conn=new mysqli("127.0.0.1","root","","bookstore_db");
if($conn->connect_error){echo "CONNECTERROR: ".$conn->connect_error; exit(1);} 
$sql="DELETE FROM products WHERE name='Test Product'";
if($conn->query($sql)===TRUE){ echo "DELETED\n";} else { echo "ERROR: ".$conn->error; }
$res=$conn->query("SELECT COUNT(*) AS c FROM products"); $row=$res->fetch_assoc(); echo "COUNT|".$row['c']."\n";
?>
