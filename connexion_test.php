<?php 
include("connexion.php");

$sql = 'select * from symptome;';
$resultset = $conn->prepare($sql);
$resultset->execute();
$data = $resultset->fetchAll(); // récupére les resultats

var_dump($data);
