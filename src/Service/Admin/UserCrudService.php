<?php

namespace App\Service\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserCrudService
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public function list(): array
    {
        $users = $this->userRepository->findAll();
        return array_map(fn(User $u) => $this->serialize($u), $users);
    }

    public function get(int $id): ?array
    {
        $user = $this->userRepository->find($id);
        return $user ? $this->serialize($user) : null;
    }

    public function create(array $data): array
    {
        if ($this->userRepository->findByEmail($data['email'])) {
            throw new \InvalidArgumentException('Email уже занят');
        }

        $user = new User();
        $user->setName($data['name']);
        $user->setEmail($data['email']);
        $user->setPhone($data['phone'] ?? null);
        $user->setPassword(password_hash($data['password'], PASSWORD_ARGON2ID));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->serialize($user);
    }

    public function update(int $id, array $data): ?array
    {
        $user = $this->userRepository->find($id);
        if (!$user) return null;

        if (isset($data['name'])) $user->setName($data['name']);
        if (isset($data['email'])) {
            $existing = $this->userRepository->findByEmail($data['email']);
            if ($existing && $existing->getId() !== $id) {
                throw new \InvalidArgumentException('Email уже занят');
            }
            $user->setEmail($data['email']);
        }
        if (isset($data['phone'])) $user->setPhone($data['phone']);
        if (isset($data['password']) && $data['password'] !== '') {
            $user->setPassword(password_hash($data['password'], PASSWORD_ARGON2ID));
        }

        $this->entityManager->flush();

        return $this->serialize($user);
    }

    public function delete(int $id): bool
    {
        $user = $this->userRepository->find($id);
        if (!$user) return false;

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return true;
    }

    private function serialize(User $user): array
    {
        return [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'phone' => $user->getPhone(),
            'created_at' => $user->getCreatedAt()?->format('c'),
            'updated_at' => $user->getUpdatedAt()?->format('c'),
        ];
    }
}
