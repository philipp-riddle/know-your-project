<?php

namespace App\Tests\Application;

use App\Entity\Project;
use App\Entity\ProjectUser;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * This base class is used to create a client and an entity manager for the tests.
 * It also ensures that the database is cleared after each test.
 */
abstract class ApplicationTestCase extends WebTestCase
{
    protected static KernelBrowser $client;
    protected static EntityManagerInterface $em;

    /**
     * Add classes here in child classes to clear them after each test.
     */
    public static array $entityClassesToClear = [];

    public static function setUpBeforeClass(): void
    {
        self::$client ??= static::createClient(); // should only be created once on the first setup
    }

    protected function tearDown(): void
    {
        $entityClassesToClear = [
            ...static::$entityClassesToClear,

            // the base class always creates database entities - make sure those are all cleared
            ProjectUser::class,
            User::class,
            Project::class,
        ];
        self::$em->clear();

        foreach ($entityClassesToClear as $entityClass) {
            self::$em->getConnection()->executeQuery("SET FOREIGN_KEY_CHECKS=0"); // makes sure that deleting the entity does not fail due to foreign key constraints
            self::$em->createQuery("DELETE FROM $entityClass")->execute();
        }

        self::$em->clear();
    }

    /**
     * Before every test we log in a standard user, this makes sure credentials / special setup from one test does not affect another.
     */
    protected function setUp(): void
    {
        self::$em = self::$client->getContainer()->get('doctrine')->getManager();
        $this->tearDown(); // seems counter intuitive but works like a charm: this makes sure that the DB is cleared before even starting the test.
    }

    protected function createUser(?Project $selectedProject = null): User
    {
        $user = (new User())
            ->setEmail('test' . \uniqid() . '@test.io')
            ->setPassword('password')
            ->setRoles(['ROLE_USER'])
            ->setCreatedAt(new \DateTimeImmutable())
            ->setVerified(true);
        self::$em->persist($user);
        self::$em->flush();

        if (null === $selectedProject) {
            $selectedProject = (new Project())
                ->setName('Test Project')
                ->setOwner($user)
                ->setCreatedAt(new \DateTimeImmutable());
        }

        $user->setSelectedProject($selectedProject);
        self::$em->persist($selectedProject);

        if ($selectedProject->getProjectUser($user) === null) {
            $projectUser = (new ProjectUser())
                ->setUser($user)
                ->setCreatedAt(new \DateTime());
            $selectedProject->addProjectUser($projectUser);
            self::$em->persist($projectUser);
        }

        self::$em->flush();

        return $user;
    }
}