<?php
require_once 'connexion.php';
class Pathologie
{
    public $idp;
    public $meridien;
    public $type;
    public $descpatho;
    public $caracteristique;
    public $typeDePathologie;

    public function __construct($idp, $meridien, $type, $descpatho, $caracteristique, $typeDePathologie)
    {
        $this->idp = $idp;
        $this->meridien = $meridien;
        $this->type = $type;
        $this->descpatho = $descpatho;
        $this->caracteristique = $caracteristique;
        $this->typeDePathologie = $typeDePathologie;
    }



    public static function extractTypeDePathologie($type)
    {
        if (substr($type, 0, 2) == 'tf' || substr($type, 0, 2) == 'mv') {
            $type = substr($type, 0, 2);
        } else {
            $type = substr($type, 0, 1);
        }
        
        switch ($type) {
            case 'l':
                $type = "luo";
                break;
            case 'j':
                $type = "jing jin";
                break;
            case 'tf':
                $type = "organe/viscère";
                break;
            case 's':
                $type = "supérieur";
                break;
            case 'i':
                $type = "inférieur";
                break;
            case 'm':
                $type = "méridien";
                break;
            case 'mv':
                $type = "merveilleux vaisseaux";
                break;
            default:
                break;
        }
        return $type;
    }

    public static function translateCaracteristiques($type)
    {
        $tableauDeCaracteristique = array();
        foreach (str_split($type) as $caracteristique) {
            switch ($caracteristique) {
                case 'e':
                    $tableauDeCaracteristique[] = "externe";
                    break;
                case 'i':
                    $tableauDeCaracteristique[] = "interne";
                    break;
                case 'p':
                    $tableauDeCaracteristique[] = "plein";
                    break;
                case 'v':
                    $tableauDeCaracteristique[] = "vide";
                    break;
                case 'f':
                    $tableauDeCaracteristique[] = "froid";
                    break;
                case 'c':
                    $tableauDeCaracteristique[] = "chaud";
                    break;
                default:
                    break;
            }
        }
        return $tableauDeCaracteristique;
    }

    public static function addCarctAndTypeToPatho($resultat)
    {
        $type = $resultat['type'];
        $typeDePathologie = self::extractTypeDePathologie($type);
        $caracteristiques = self::translateCaracteristiques($type);

        $resultat["caracteristique"] = $caracteristiques;
        $resultat["typeDePathologie"] = $typeDePathologie;

        return $resultat;
    }


    public static function getAllpathos()
    {
        global $conn;
        $sql = 'SELECT DISTINCT patho.idp, meridien.nom as meridien, patho.type, patho.desc as descPatho
        FROM patho 
        LEFT JOIN meridien ON meridien.code = patho.mer 
        LEFT JOIN symptpatho ON symptpatho.idp = patho.idp';

        $resultset = $conn->prepare($sql);
        $resultset->execute();
        $data = $resultset->fetchAll();

        if (!empty($data)) {
            foreach ($data as $resultat) {
                $newValue = Pathologie::addCarctAndTypeToPatho($resultat);
                $newData[] = $newValue;
            }
        }

        $pathologies = array();
        foreach ($newData as $row) {
            $pathologie = new Pathologie(
                $row['idp'],
                $row['meridien'],
                $row['type'],
                $row['descpatho'],
                $row['caracteristique'],
                $row['typeDePathologie']
            );
            $pathologies[] = $pathologie;
        }
        sort($pathologies);
        return $pathologies;
    }

    public static function getMeridien()
    {
        global $conn;
        $sql = 'SELECT DISTINCT nom FROM meridien';

        $resultset = $conn->prepare($sql);
        $resultset->execute();
        $data = $resultset->fetchAll();

        $meridiens = array();
        foreach ($data as $row) {
            $meridiens[] = $row['nom'];
        }

        return $meridiens;
    }

    public static function getPatho()
    {
        global $conn;
        $sql = 'SELECT type FROM patho';

        $resultset = $conn->prepare($sql);
        $resultset->execute();
        $data = $resultset->fetchAll();

        $patho = array();

        foreach ($data as $row) {
            $type = Pathologie::extractTypeDePathologie($row['type']); 
            $patho[] = $type;
        }
        $patho = array_unique($patho); 
        return $patho;
    }

    public static function getCara()
    {
        global $conn;
        $sql = 'SELECT DISTINCT type FROM patho';

        $resultset = $conn->prepare($sql);
        $resultset->execute();
        $data = $resultset->fetchAll();

        $caracteristiques = array();

        foreach ($data as $resultat) {
            $newValue = Pathologie::addCarctAndTypeToPatho($resultat);
            $caracteristiques = array_merge($caracteristiques, Pathologie::translateCaracteristiques($newValue['type']));
        }

        $caracteristiques = array_unique($caracteristiques); // Supprimez les doublons

        return $caracteristiques;
    }

    //retour un tableau d'objet Pathologie
    public static function getPathosWhithFiltre($meridien = "", $filtreCarac = null, $filtreType = null, $searchSymptome = "")
    {
        global $conn;
        $sql = 'SELECT DISTINCT patho.idp, meridien.nom as meridien, patho.type, patho.desc as descPatho
        FROM patho 
        LEFT JOIN meridien ON meridien.code = patho.mer 
        LEFT JOIN symptpatho ON symptpatho.idp = patho.idp
        LEFT JOIN symptome ON symptome.ids = symptpatho.ids
        WHERE lower(meridien.nom) LIKE ? AND LOWER(symptome.desc) LIKE ?';

        $rechercheMeridien = '%' . strtolower($meridien) . '%';
        $symptome = strtolower($searchSymptome);
        $recherche = '%' . $symptome . '%';

        $resultset = $conn->prepare($sql);
        $resultset->execute([$rechercheMeridien, $recherche]);
        $data = $resultset->fetchAll();
        $newData = [];
        if (!empty($data)) {
            foreach ($data as $resultat) {
                $newValue = Pathologie::addCarctAndTypeToPatho($resultat);
                if ((in_array($filtreCarac, $newValue["caracteristique"]) || $filtreCarac == null) && ($filtreType == $newValue["typeDePathologie"] || $filtreType == null)) {
                    $newData[] = $newValue;
                }
            }
        }
        $pathologies = array();
        foreach ($newData as $row) {
            $pathologie = new Pathologie(
                $row['idp'],
                $row['meridien'],
                $row['type'],
                $row['descpatho'],
                $row['caracteristique'],
                $row['typeDePathologie']
            );
            $pathologies[] = $pathologie;
        }
        return $pathologies;
    }
}


//$resultat = Pathologie::getPathosWhithFiltre('TR', "plein", 'organe/viscère', "");
//$resultat = getMeridienWithCode('Rte');
//$resultat = getAllpathos();
/*
foreach ($resultat as $pathologie) {
    echo "ID : " . $pathologie->idp . "<br>";
    echo "Méridien : " . $pathologie->meridien . "<br>";
    echo "Type : " . $pathologie->type . "<br>";
    echo "Description : " . $pathologie->descpatho . "<br>";
    echo "Caractéristique : " . $pathologie->caracteristique . "<br>";
    echo "Type de pathologie : " . $pathologie->typeDePathologie . "<br>";
    echo "<br>";
}
*/