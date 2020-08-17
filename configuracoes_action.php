<?php
require_once 'config.php';
require_once 'models/Auth.php';
require_once 'dao/UserDaoMysql.php';

$auth = new Auth($pdo, $base);
$userInfo = $auth->checkToken();// Usuário que esta logado

$userDao = new UserDaoMysql($pdo);

$name = filter_input(INPUT_POST, 'name');
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$password = filter_input(INPUT_POST, 'password');
$birthdate = filter_input(INPUT_POST, 'birthdate');
$city = filter_input(INPUT_POST, 'city');
$work = filter_input(INPUT_POST, 'work');
$password_confirmation = filter_input(INPUT_POST, 'password_confirmation');

if($name && $email) {
    // Dados que não precisam ser verificados
    $userInfo->name = $name;
    $userInfo->city = $city;
    $userInfo->work = $work;

    // Verifica se digitou email e se ele não existe
    if($userInfo->email != $email) {
        if($userDao->findByEmail($email) === false) {
            $userInfo->email = $email;// envia email digitado
        } else {
            $_SESSION['flash'] = 'Email já existe!';
            header("Location: ".$base."/configuracoes.php");
            exit;
        }
    }

    // Verifica data de nascimento
    $birthdate = explode('/', $birthdate);// Formata a data
    if(count($birthdate) != 3) {// Verifica se tem 3 items no array
        $_SESSION['flash'] = 'Data de Nascimento Inválida!';
        header("Location: ".$base."/configuracoes.php");// Volta para cadastro
        exit;   
    }

    // nova verificação aqui
    if(strlen($birthdate[2]) < 4) {
        $_SESSION['flash'] = "Data de nascimento inválida";
        header("Location: ".$base."/configuracoes.php");
        exit;
    }

    // Verifica se a data é válida
    $birthdate = $birthdate[2].'-'.$birthdate[1].'-'.$birthdate[0];// Y-m-d
    if(strtotime($birthdate) === false) {// Se não é válida
        $_SESSION['flash'] = 'Data de Nascimento Inválida!';
        header("Location: ".$base."/configuracoes.php");
        exit;
    }
    
    $userInfo->birthdate = $birthdate;

    // Verifica se digitou a senha
    if(!empty($password)) {
        if($password === $password_confirmation) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $userInfo->password = $hash;
        } else {
            $_SESSION['flash'] = 'Senhas não conferem!';
            header("Location: ".$base."/configuracoes.php");
            exit;
        }
    }

    // AVATAR
    if(isset($_FILES['avatar']) && !empty($_FILES['avatar']['tmp_name'])) {
        $newAvatar = $_FILES['avatar'];

        if(in_array($newAvatar['type'], ['image/jpeg', 'image/jpg', 'image/png'])) {
            $avatarWidth = 200;
            $avatarHeight = 200;

            list($widthOrig, $heightOrig) = getimagesize($newAvatar['tmp_name']);
            $ratio = $widthOrig / $heightOrig;

            $newWidth = $avatarWidth;
            $newHeight = $newWidth / $ratio;

            if($newHeight < $avatarHeight) {
                $newHeight = $avatarHeight;
                $newWidth = $newHeight * $ratio;
            }
            //echo $newWidth.' x '.$newHeight;

            $x = $avatarWidth - $newWidth;
            $y = $avatarHeight - $newHeight;
            $x = $x<0 ? $x/2 : $x;
            $y = $y<0 ? $y/2 : $y; 

            $finalImage = imagecreatetruecolor($avatarWidth, $avatarHeight);
            switch($newAvatar['type']) {
                case 'image/jpeg':
                case 'image/jpg':
                    $image = imagecreatefromjpeg($newAvatar['tmp_name']);
                break;
                case 'image/png':
                    $image = imagecreatefrompng($newAvatar['tmp_name']);
                break;
            }
            
            imagecopyresampled(
                $finalImage, $image,
                $x, $y, 0, 0,
                $newWidth, $newHeight, $widthOrig, $heightOrig
            );

            $avatarName = md5(time().rand(0, 9999)).'.jpg';

            imagejpeg($finalImage, './media/avatars/'.$avatarName, 100);

            $userInfo->avatar = $avatarName;
        }
    }

    // COVER
    if(isset($_FILES['cover']) && !empty($_FILES['cover']['tmp_name'])) {
        $newCover = $_FILES['cover'];

        if(in_array($newCover['type'], ['image/jpeg', 'image/jpg', 'image/png'])) {
            $coverWidth = 850;
            $coverHeight = 310;

            list($widthOrig, $heightOrig) = getimagesize($newCover['tmp_name']);
            $ratio = $widthOrig / $heightOrig;

            $newWidth = $coverWidth;
            $newHeight = $newWidth / $ratio;

            if($newHeight < $coverHeight) {
                $newHeight = $coverHeight;
                $newWidth = $newHeight * $ratio;
            }
            //echo $newWidth.' x '.$newHeight;

            $x = $coverWidth - $newWidth;
            $y = $coverHeight - $newHeight;
            $x = $x<0 ? $x/2 : $x;
            $y = $y<0 ? $y/2 : $y; 

            $finalImage = imagecreatetruecolor($coverWidth, $coverHeight);
            switch($newCover['type']) {
                case 'image/jpeg':
                case 'image/jpg':
                    $image = imagecreatefromjpeg($newCover['tmp_name']);
                break;
                case 'image/png':
                    $image = imagecreatefrompng($newCover['tmp_name']);
                break;
            }
            
            imagecopyresampled(
                $finalImage, $image,
                $x, $y, 0, 0,
                $newWidth, $newHeight, $widthOrig, $heightOrig
            );

            $coverName = md5(time().rand(0, 9999)).'.jpg';

            imagejpeg($finalImage, './media/covers/'.$coverName, 100);

            $userInfo->cover = $coverName;
        }
    }
    
    $userDao->update($userInfo);
}

header("Location: ".$base."/configuracoes.php");
exit;