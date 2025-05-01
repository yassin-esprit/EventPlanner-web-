<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create admin user
        $admin = new User();
        $admin->setEmail('admin@admin.tn');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $admin->setFirstName('Admin');
        $admin->setLastName('User');
        $admin->setIsVerified(true);

        // Directly set the createdAt property since there is no setCreatedAt method
        $reflection = new \ReflectionClass($admin);
        $property = $reflection->getProperty('createdAt');
        $property->setAccessible(true);
        $property->setValue($admin, new \DateTimeImmutable('2025-05-01'));

        $manager->persist($admin);
        $manager->flush();
    }
}
