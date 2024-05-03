<?php
require_once 'connexion.php';
// Dans le fichier "Symptome.php"
class Symptome
{
    public $ids;
    public $desc;

    public function __construct($ids, $desc)
    {
        $this->ids = $ids;
        $this->desc = $desc;
    }

    public static function getAllSymptomes()
    {
        global $conn;
        $sql = 'select * from symptome;';
        $resultset = $conn->prepare($sql);
        $resultset->execute();
        $data = $resultset->fetchAll();

        $symptomes = array();
        foreach ($data as $row) {
            $symptome = new Symptome(
                $row['ids'],
                $row['desc'],
            );
            $symptomes[] = $symptome;
        }

        return $symptomes;
    }
    public static function searchSymptome($symptome)
    {
        global $conn;
        $sql = 'SELECT *
        FROM symptome  
        WHERE LOWER(symptome.desc) LIKE ?';

        $resultset = $conn->prepare($sql);

        $symptome = strtolower($symptome);
        $recherche = '%' . $symptome . '%';

        $resultset->execute([$recherche]);
        $data = $resultset->fetchAll();

        $symptomes = array();
        foreach ($data as $row) {
            $symptome = new Symptome(
                $row['ids'],
                $row['desc'],
            );
            $symptomes[] = $symptome;
        }

        return $symptomes;
    }

    public static function getSymptomesFromPatho($idp)
    {
        global $conn;
        $sql = 'SELECT *
        FROM symptome 
        LEFT JOIN symptpatho ON symptpatho.ids = symptome.ids 
        LEFT JOIN patho ON patho.idp = symptpatho.idp 
        WHERE patho.idp = ?';

        $resultset = $conn->prepare($sql);
        $resultset->execute([$idp]);
        $data = $resultset->fetchAll();

        $symptomes = array();
        foreach ($data as $row) {
            $symptome = new Symptome(
                $row['ids'],
                $row['desc'],
            );
            $symptomes[] = $symptome;
        }

        return $symptomes;
    }
}
/* POUR TEST DES FONCTIONS REQUETE */
/*
$resultat = getSymptomesFromPatho('7');
$resultat = searchSymptome('fai');
$resultat = getAllSymptomes();

foreach ($resultat as $symptome) {
    echo "ids : " . $symptome->ids . "<br>";
    echo "desc : " . $symptome->desc . "<br>";
    echo "<br>";
}*/