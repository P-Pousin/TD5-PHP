<?php

namespace sondage;

/**
 * Représente le "Model", c'est à dire l'accès à la base de
 * données pour l'application cinéma basé sur MySQL
 */
class Model {
    protected $pdo;

    public function __construct($host, $database, $user, $password)
    {
        try {
            $this->pdo = new \PDO(
                'mysql:dbname='.$database.';host='.$host,
                $user,
                $password
            );
            $this->pdo->exec('SET CHARSET UTF8');
        } catch (\PDOException $exception) {
            die('Impossible de se connecter au serveur MySQL');
        }
    }

    /**
     * Récupère un résultat exactement
     */
    protected function fetchOne(\PDOStatement $query)
    {
        if ($query->rowCount() != 1) {
            return false;
        } else {
            return $query->fetch();
        }
    }

    public function checkConnexion($login,$password) {
        $sql = 'SELECT * FROM users WHERE login="'.$login.'"';
        $query = $this->pdo->prepare($sql);
        $query->bindParam(":login",$login);
        $query->bindParam(":password",md5($password));
        $query->execute();
        if($query->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function getIdUser($login) {
        $sql = 'SELECT id FROM users WHERE login="'.$login.'"';
        $query = $this->pdo->prepare($sql);
        $query->execute();
        return $query->fetch();
    }

    public function checkRegister($login,$password) {
        $query = $this->pdo->prepare('SELECT COUNT(*) as nb FROM users WHERE login=:login');
        $query->bindParam(':login',$login);
        $query->execute();
        $count = $query->fetch();

        if ($count['nb'] == 0) {
            $query = $this->pdo->prepare('INSERT INTO users (login, password) VALUES (?,?)');
            $query->execute(array($login, md5($password)));
            return true;
        }
    }

    public function getAllPolls() {
        $sql ='SELECT * FROM polls';
        $query = $this->pdo->prepare($sql);
        $query->execute();
        return $query->fetchAll();
    }

    public function getPoll($id) {
        $sql ='SELECT * FROM polls WHERE id=:id';
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id',$id);
        $query->execute();
        return $query->fetch();
    }

    public function getAnswers($idPoll) {
        $answers = array();
        foreach (array(1,2,3) as $answer) {
            $sql = 'SELECT COUNT(*) as nb FROM answers WHERE poll_id=:idPoll AND answer=:answer';
            $query = $this->pdo->prepare($sql);
            $query->bindParam(':idPoll',$idPoll);
            $query->bindParam(':answer',$answer);
            $query->execute();
            $query = $query->fetch();
            $answers[$answer] = $query['nb'];
        }
        return $answers;
    }

    public function getLabelAnswers($idPoll) {
        $sql ='SELECT answer1, answer2, answer3 FROM polls WHERE id = ?';
        $query = $this->pdo->prepare($sql);
        $query->execute(array($idPoll));
        return $query->fetch();
    }

    public function getTotalAnswer($idPoll) {
        $sql ='SELECT COUNT(*) as nb FROM answers WHERE poll_id = :idPoll';
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':idPoll',$idPoll);
        $query->execute();
        $count = $query->fetch();
        return $count['nb'];
    }

    public function createPoll($question,$answer1,$answer2,$answer3,$user) {
        $sql = 'INSERT INTO polls (question,answer1,answer2,answer3,user) 
                VALUES (:question,:answer1,:answer2,:answer3,:user)';
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':question',$question);
        $query->bindParam(':answer1',$answer1);
        $query->bindParam(':answer2',$answer2);
        $query->bindParam(':answer3',$answer3);
        $query->bindParam(':user',$user);
        $query->execute();
        return true;
    }

    public function insertAnswer($idUser,$idPoll,$answer) {
        if (!empty($answer) && ($answer=='0' || $answer=='1' || $answer=='2')) {
            $sql = 'INSERT INTO answers (user_id, poll_id, answer)
                    VALUES (:idUser,:idPoll,:answer)';
            $query = $this->pdo->prepare($sql);
            $query->bindParam(':idUser',$idUser);
            $query->bindParam(':idPoll',$idPoll);
            $query->bindParam(':answer',$answer);
            $query->execute();
            return true;
        }
    }
}