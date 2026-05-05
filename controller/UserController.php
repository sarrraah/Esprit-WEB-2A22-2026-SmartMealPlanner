<?php
require_once __DIR__ . '/../model/UserModel.php';

class UserController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        return $this->userModel->getAllUsers();
    }

    public function store($data)
    {
        if (!empty($data['mot_de_passe'])) {
            $data['mot_de_passe'] = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
        }

        return $this->userModel->addUser($data);
    }

    public function show($id)
    {
        return $this->userModel->getUserById($id);
    }

    public function update($id, $data)
    {
        if (!empty($data['mot_de_passe'])) {
            $data['mot_de_passe'] = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
        }

        return $this->userModel->updateUser($id, $data);
    }

    public function delete($id)
    {
        return $this->userModel->deleteUser($id);
    }

    public function findByEmail($email)
    {
        return $this->userModel->getUserByEmail($email);
    }

    public function login($email, $password)
    {
        $user = $this->userModel->getUserByEmail($email);

        if ($user && password_verify($password, $user['mot_de_passe'])) {
            return $user;
        }

        return false;
    }
    public function saveRememberToken($userId, $hashedToken, $expires)
    {
        $sql = "UPDATE user 
            SET remember_token = :remember_token,
                remember_expires = :remember_expires
            WHERE id = :id";

        $db = config::getConnexion();

        $query = $db->prepare($sql);
        $query->execute([
            'remember_token' => $hashedToken,
            'remember_expires' => $expires,
            'id' => $userId
        ]);
    }
    public function getUserByRememberToken($token)
    {
        $hashedToken = hash('sha256', $token);

        $sql = "SELECT * FROM user 
            WHERE remember_token = :remember_token
            AND remember_expires > NOW()
            AND statut = 'active'
            LIMIT 1";

        $db = config::getConnexion();

        $query = $db->prepare($sql);
        $query->execute([
            'remember_token' => $hashedToken
        ]);

        return $query->fetch();
    }
    public function clearRememberToken($userId)
    {
        $sql = "UPDATE user 
            SET remember_token = NULL,
                remember_expires = NULL
            WHERE id = :id";

        $db = config::getConnexion();

        $query = $db->prepare($sql);
        $query->execute([
            'id' => $userId
        ]);
    }
}
