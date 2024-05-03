<?php
require_once 'connexion.php';
class User{
    public $email;
    public $password;
    public $id;
    public $nom;
    public $pnom;
    public function __construct(){
        $this->email = $email;
        $this->password = $password;
        $this->id = $id;
        $this->nom = $nom;
        $this->pnom = $pnom;
    }

    public static function UserExists($email){
        include('connexion.php');
        // Query to check if the user exists in the database
        $query = "SELECT id, email, password, nom, pnom FROM public.users WHERE email = :email";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if($user){
            return $user;
        }
        else{
            return(FALSE);
        }
    }

    public static function AuthUser($password, $user){
        if (password_verify($password, $user["password"])) {
            // Password is correct; log in the user
            session_start();
            $_SESSION['valid'] = TRUE;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['nom'] = $user["nom"];
            $_SESSION['pnom'] = $user["pnom"];
            return(TRUE);
        }else{
            return(FALSE);
        }
    }

    public static function returnAvailableId(){
        include('connexion.php');
        //// Query to check if the user exists in the database
        $query = "SELECT COUNT(id) FROM public.users;";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $id = $stmt->fetch(PDO::FETCH_ASSOC);
        return $id["count"]+1;
    }

    public static function CreateUser($id, $email, $password, $nom, $pnom){ 
        include('connexion.php');
        //// Query to check if the user exists in the database
        $query = "INSERT INTO public.users (id, email, password, nom, pnom) VALUES (?, ?, ?, ?, ?);";
        $stmt = $conn->prepare($query);
        //$stmt->bindParam("issss", $id, $email, $password, $nom, $pnom);

        $stmt->bindParam(1, $id, PDO::PARAM_STR);
        $stmt->bindParam(2, $email, PDO::PARAM_STR);
        $stmt->bindParam(3, password_hash($password, PASSWORD_BCRYPT) , PDO::PARAM_STR);
        $stmt->bindParam(4, ucfirst(strtolower($nom)), PDO::PARAM_STR);
        $stmt->bindParam(5, ucfirst(strtolower($pnom)), PDO::PARAM_STR);

        $stmt->execute();        
    }

    public static function ChangePassword($user, $oldpassword, $password){ 
        include('connexion.php');
        if (password_verify($oldpassword, $user["password"])){
            //// Query to check if the user exists in the database
            $query = "UPDATE public.users SET password = ? WHERE id = ?;";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(1, password_hash($password, PASSWORD_BCRYPT) , PDO::PARAM_STR);
            $stmt->bindParam(2, $user['id'] , PDO::PARAM_INT);
            $stmt->execute();
        }
    }
}
