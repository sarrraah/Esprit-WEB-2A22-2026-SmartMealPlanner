<?php
require_once __DIR__ . '/../config.php';

class UserModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = config::getConnexion();
    }

    public function getAllUsers()
    {
        $sql = "SELECT 
                    id,
                    nom,
                    prenom,
                    date_naissance,
                    email,
                    role,
                    statut,
                    sexe,
                    experience,
                    speciality,
                    motivation
                FROM `user`
                ORDER BY id DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserById($id)
    {
        $sql = "SELECT 
                    id,
                    nom,
                    prenom,
                    date_naissance,
                    email,
                    mot_de_passe,
                    role,
                    statut,
                    sexe,
                    experience,
                    speciality,
                    motivation
                FROM `user`
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function emailExists($email)
    {
        $sql = "SELECT COUNT(*) FROM `user` WHERE email = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetchColumn() > 0;
    }

    public function emailExistsForAnotherUser($email, $id)
    {
        $sql = "SELECT COUNT(*) FROM `user` WHERE email = :email AND id != :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':email' => $email,
            ':id' => $id
        ]);
        return $stmt->fetchColumn() > 0;
    }

    public function addUser($data)
    {
        if ($this->emailExists($data['email'])) {
            throw new Exception("This email already exists.");
        }

        $sql = "INSERT INTO `user` (
            nom,
            prenom,
            date_naissance,
            email,
            mot_de_passe,
            role,
            statut,
            sexe,
            experience,
            speciality,
            motivation
        ) VALUES (
            :nom,
            :prenom,
            :date_naissance,
            :email,
            :mot_de_passe,
            :role,
            :statut,
            :sexe,
            :experience,
            :speciality,
            :motivation
        )";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':nom' => $data['nom'],
            ':prenom' => $data['prenom'],
            ':date_naissance' => $data['date_naissance'],
            ':email' => $data['email'],
            ':mot_de_passe' => $data['mot_de_passe'],
            ':role' => $data['role'],
            ':statut' => $data['statut'],
            ':sexe' => $data['sexe'],
            ':experience' => $data['experience'] ?? null,
            ':speciality' => $data['speciality'] ?? null,
            ':motivation' => $data['motivation'] ?? null
        ]);
    }

    public function updateUser($id, $data)
    {
        if ($this->emailExistsForAnotherUser($data['email'], $id)) {
            throw new Exception("This email already exists.");
        }

        $existingUser = $this->getUserById($id);

        if (!$existingUser) {
            throw new Exception("User not found.");
        }

        $finalPassword = trim($data['mot_de_passe'] ?? '') !== ''
            ? $data['mot_de_passe']
            : $existingUser['mot_de_passe'];

        $sql = "UPDATE `user`
                SET nom = :nom,
                    prenom = :prenom,
                    date_naissance = :date_naissance,
                    email = :email,
                    mot_de_passe = :mot_de_passe,
                    role = :role,
                    statut = :statut,
                    sexe = :sexe,
                    experience = :experience,
                    speciality = :speciality,
                    motivation = :motivation
                WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':nom' => $data['nom'],
            ':prenom' => $data['prenom'],
            ':date_naissance' => $data['date_naissance'],
            ':email' => $data['email'],
            ':mot_de_passe' => $finalPassword,
            ':role' => $data['role'],
            ':statut' => $data['statut'],
            ':sexe' => $data['sexe'],
            ':experience' => $data['experience'] ?? $existingUser['experience'],
            ':speciality' => $data['speciality'] ?? $existingUser['speciality'],
            ':motivation' => $data['motivation'] ?? $existingUser['motivation'],
            ':id' => $id
        ]);
    }

    public function deleteUser($id)
    {
        $user = $this->getUserById($id);

        if (!$user) {
            throw new Exception("User not found.");
        }

        $sql = "DELETE FROM `user` WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function getUserByEmail($email)
    {
        $sql = "SELECT * FROM `user` WHERE email = :email LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function authenticateUser($email, $password)
    {
        $sql = "SELECT * FROM `user`
                WHERE email = :email AND mot_de_passe = :mot_de_passe
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':email' => $email,
            ':mot_de_passe' => $password
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
