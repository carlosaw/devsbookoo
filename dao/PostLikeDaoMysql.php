<?php
require_once 'models/PostLike.php';

class PostLikeDaoMysql implements PostLikeDAO {
    private $pdo;

    public function __construct(PDO $driver) {
        $this->pdo = $driver;
    }

    public function getLikeCount($id_post) {// Qt.likes daquele post
        $sql = $this->pdo->prepare("SELECT COUNT(*) as c FROM postlikes
        WHERE id_post = :id_post");

        $sql->bindValue(':id_post', $id_post);
        $sql->execute();

        $data = $sql->fetch();
        return $data['c'];

    }

    public function isLiked($id_post, $id_user) {// Se este post teve like de quem
        $sql = $this->pdo->prepare("SELECT * FROM postlikes
        WHERE id_post = :id_post AND id_user = :id_user");
        $sql->bindValue(':id_post', $id_post);
        $sql->bindValue(':id_user', $id_user);
        $sql->execute();

        if($sql->rowCount() > 0) {
            return true;
        } else {
            return false;
        }

    }

    public function likeToggle($id_post, $id_user) {// Dar o like
        if($this->isLiked($id_post, $id_user)) {// Se já deu like 
            // Delete
            $sql = $this->pdo->prepare("DELETE FROM postlikes
            WHERE id_post = :id_post AND id_user = :id_user");
        } else {// Se não deu like
            // Insere
            $sql = $this->pdo->prepare("INSERT INTO postlikes
            (id_post, id_user, created_at) VALUES
            (:id_post, :id_user, NOW())");
        }

        $sql->bindValue(':id_post', $id_post);
        $sql->bindValue(':id_user', $id_user);
        $sql->execute();        
    }

    public function deleteFromPost($id_post) {
        $sql = $this->pdo->prepare("DELETE FROM postlikes WHERE id_post = :id_post");
        $sql->bindValue(':id_post', $id_post);
        $sql->execute();
    }
}