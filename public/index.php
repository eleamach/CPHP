<?php
// Inclure le fichier d'autoloading de Composer
require_once __DIR__ . '/../vendor/autoload.php';
require("../src/model/pathologie.php");


// Utilisation des classes de Twig
use Twig\Environment;
use Twig\Loader\FilesystemLoader;


// Chemin absolu vers le répertoire des vues
$viewsDir = realpath(__DIR__ . '/../src/views'); // Mise à jour du chemin

// Initialisation de Twig
$loader = new FilesystemLoader($viewsDir);
$twig = new Environment($loader);

// Analysez l'URL demandée
$request_uri = $_SERVER['REQUEST_URI'];

// Supprimez les paramètres de requête de l'URL
$route = strtok($request_uri, '?');

// Définissez vos routes ici
switch ($route) {
    case '/':
        session_start();
        echo $twig->render('index.html.twig', ['firstname' => $_SESSION['pnom']]);
        break;

    case '/contact':
        session_start();
        echo $twig->render('apropos.html.twig', ['firstname' => $_SESSION['pnom']]);
        break;

    case '/api/':
        $data = [
            "api"=> "Bienvenue sur l'API de PiquaShu!",
            "version" => '1.0.0'
        ];

        echo(json_encode($data));
        break;

    case '/api/pathologies':
        if (isset($_GET['symptome']) || isset($_GET['carac']) || isset($_GET['type']) || isset($_GET['meridien'])) {
            $meridien = $_GET['meridien'];
            $carac = empty($_GET['carac']) ? null : $_GET['carac'];
            $type = empty($_GET['type']) ? null : $_GET['type'];
            $symptome = empty($_GET['symptome']) ? "" : $_GET['symptome'];

            $data = Pathologie::getPathosWhithFiltre($meridien, $carac, $type, $symptome);
        } else {
            $data = Pathologie::getAllpathos();
        }

        header('Content-Type: application/json');
        $data = json_encode($data);
        echo($data);
        break;

    case '/pathologies':
        session_start();
        if (isset($_GET['symptome']) && $_SESSION['valid']) {
            $symptome = $_GET['symptome'];
            $data = Pathologie::getPathosWhithFiltre("",null,null,$symptome);
        } 
        else if (isset($_GET['meridien'])||$_GET['type']||$_GET['cara']) {
            $meridien = $_GET['meridien'];
            $type = empty($_GET['type']) ? null : $_GET['type'];
            $carac = empty($_GET['cara']) ? null : $_GET['cara'];
            $data = Pathologie::getPathosWhithFiltre($meridien, $carac, $type, null);
        } else {
            $data = Pathologie::getAllpathos();
        }
        
        $datamerid = Pathologie::getMeridien();
        $datapatho = Pathologie::getPatho();
        $datacara = Pathologie::getCara();
        echo $twig->render('patho.html.twig', ['data' => $data, 'datamerid' => $datamerid, 'datapatho' => $datapatho, 'datacara'=> $datacara, 'firstname' => $_SESSION['pnom']]);
    break;
    
    
    case '/login':
        session_start();
        if($_SESSION['valid']){
            header('Location: /account');
        }
        echo $twig->render('login.html.twig', ['firstname' => $_SESSION['pnom']]);
        break;
    
    case '/signin':
        session_start();
        if($_SESSION['valid']){
            header('Location: /account');
        }

        echo $twig->render('signin.html.twig', ['firstname' => $_SESSION['pnom']]);
        break;


    case '/account':
        session_start();
        if ($_SESSION['valid']){
            $username = ucfirst(strtolower($_SESSION['pnom']))." ".ucfirst(strtolower($_SESSION['nom']));
            echo $twig->render('account.html.twig', ['username' => $username, 'firstname' => $_SESSION['pnom']]);
        }else{
            echo("no session");
        }
        break;

    // Ci dessous on trouve l'API qui gère le login/register

    case '/login-verify':
        if(isset($_POST['email']) && isset($_POST['password'])){
            include_once("../src/model/user.php");
            $email = $_POST["email"];
            $password = $_POST["password"];
        
            $user1 = new User();
            if($user1->UserExists($email)){
                if($user1->AuthUser($password, $user1->UserExists($email))){
                    header('Location: /account');
                    exit;
                }else{
                    header('Location: /login?fail');
                    exit;
                }
            }else{
                header('Location: /login?fail');
                exit;
            }
        }else{
            echo("no post data");
            exit;
        }
        break;

    case '/register':
        if (! empty($_POST['email']) && ! empty($_POST['password']) &&  ! empty($_POST['lastname']) && ! empty($_POST['firstname'])){
            include_once("../src/model/user.php");
            $user1 = new User();
            if(! $user1->UserExists($_POST['email'])){
                $user1->CreateUser($user1->returnAvailableId(), $_POST['email'], $_POST['password'], $_POST['lastname'], $_POST['firstname']);
            }
            if($user1->UserExists($_POST['email'])){
                if($user1->AuthUser($_POST['password'], $user1->UserExists($_POST['email']))){
                    header('Location: /account');
                    exit;
                }
            }else{
                echo("L'enregistrement à échoué :/ ");
                echo("user id : ".$user1->returnAvailableId());
                echo("user email :".$_POST['email']);
            }
        }else{
            header('Location: /signin?empty');
        }
        break;

    case '/change-password':
        session_start();
        
        include_once("../src/model/user.php");
        $user1 = new User();
        if($_POST['password'] === $_POST['confirm']){
            $user1->ChangePassword($user1->UserExists($_SESSION['user_email']),$_POST['oldpassword'],$_POST['password']);
            header('Location: /account');
        }else{
            header('Location: /account?match');
        }
        break;

    case '/disconnect':
        session_start();
        session_destroy();
        header('Location: /');
        break;

    default:
        // Route par défaut en cas de correspondance non trouvée
        if(strpos($route, "api")){
            http_response_code(404);
        } else {
            session_start();
            http_response_code(404);
            echo $twig->render('error.html.twig', ['firstname' => $_SESSION['pnom']]);
        }
        break;
}
