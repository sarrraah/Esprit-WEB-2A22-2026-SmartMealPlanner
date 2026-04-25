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
}
