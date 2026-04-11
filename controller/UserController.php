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
        return $this->userModel->addUser($data);
    }

    public function show($id)
    {
        return $this->userModel->getUserById($id);
    }

    public function update($id, $data)
    {
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
        return $this->userModel->authenticateUser($email, $password);
    }
}