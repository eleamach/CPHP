Note eco

<?php
require_once 'pathologie.php';

$conn = new PDO('pgsql:host=database;port=5432;dbname=pgtp', 'pgtp', 'tp');

function getAllMeridiens()
{
    global $conn;
    $sql = 'select * from meridien;';
    $resultset = $conn->prepare($sql);
    $resultset->execute();
    $data = $resultset->fetchAll();

    return $data;
}

function getMeridienWithCode($CodeMeridien)
{
    global $conn;
    $sql = 'SELECT * FROM meridien WHERE code = ?';
    $resultset = $conn->prepare($sql);
    $resultset->execute([$CodeMeridien]);

    return $resultset;
}

/* POUR TEST DES FONCTIONS REQUETE */

$resultat = getPathosWhithFiltre('TR', null, 'luo');
//$resultat = getMeridienWithCode('Rte');
//$resultat = getSymptomesFromPatho(2);
//$resultat = getAllpathos();

foreach ($resultat as $element) {
    foreach ($element as $key => $value) {
        if (!is_numeric($key)) { // Vérifiez si la clé n'est pas un numéro
            echo "$key: $value<br>";
        }
    }
    echo "<br>";
}