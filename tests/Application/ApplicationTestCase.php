<?php

namespace App\Tests\Application;

use App\Entity\Project\Project;
use App\Entity\Project\ProjectUser;
use App\Entity\User\User;
use App\Tests\TestCase;
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
            $selectedProject = $this->createProject($user, flush: false);
            $user->setSelectedProject($selectedProject);
        } else {
            // the passed $selectedProject could be unmanaged; thus, fetch a new managed instance from Doctrine.
            $selectedProject = self::$em->getRepository(Project::class)->find($selectedProject->getId());
        }

        self::$em->flush();

        return $user;
    }

    protected function createProject(User $owner, bool $flush = true): Project
    {
        $project = (new Project())
            ->setName('Test Project')
            ->setOwner($owner)
            ->setCreatedAt(new \DateTimeImmutable());
        self::$em->persist($project);

        if ($project->getProjectUser($owner) === null) {
            $projectUser = (new ProjectUser())
                ->setUser($owner)
                ->setCreatedAt(new \DateTime());
            $project->addProjectUser($projectUser);
            self::$em->persist($projectUser);
        }

        if ($flush) {
            self::$em->flush();
        }

        return $project;
    }
}