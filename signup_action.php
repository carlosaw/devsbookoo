<?php
require 'config.php';
require 'models/Auth.php';

$name = filter_input(INPUT_POST, 'name');
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$password = filter_input(INPUT_POST, 'password');
$birthdate = filter_input(INPUT_POST, 'birthdate');// 00/00/0000

if($name && $email && $password && $birthdate) {

    $auth = new Auth($pdo, $base);

    $birthdate = explode('/', $birthdate);// Formata a data
    if(count($birthdate) != 3) {// Verifica se tem 3 items no array
        $_SESSION['flash'] = 'Data de Nascimento Inválida!';
        header("Location: ".$base."/signup.php");// Volta para cadastro
        exit;   
    }
    // Verifica se a data é válida
    $birthdate = $birthdate[2].'-'.$birthdate[1].'-'.$birthdate[0];// Y-m-d
    if(strtotime($birthdate) === false) {// Se não é válida
        $_SESSION['flash'] = 'Data de Nascimento Inválida!';
        header("Location: ".$base."/signup.php");
        exit;
    }
    // Verifica se e-mail já existe
    if($auth->emailExists($email) === false) {

        $auth->registerUser($name, $email, $password, $birthdate);
        header("Location: ".$base);// Loga o usuario ao cadastrar
        exit;

    } else {
        $_SESSION['flash'] = 'E-mail já cadastrado!';
        header("Location: ".$base."/signup.php");
        exit; 
    }

} 
$_SESSION['flash'] = 'Preencha todos os campos!';
header("Location: ".$base."/signup.php");
exit;