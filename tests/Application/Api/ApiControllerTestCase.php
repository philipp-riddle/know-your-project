<?php

namespace App\Tests\Application\Api;

use App\Entity\Project;
use App\Entity\ProjectUser;
use App\Entity\User;
use App\Tests\Application\ApplicationTestCase;

abstract class ApiControllerTestCase extends ApplicationTestCase
{
    protected static User $loggedInUser;

    /**
     * Before every test we log in a standard user, this makes sure credentials / special setup from one test does not affect another.
     */
    protected function setUp(): void
    {
        parent::setUp();

        self::$loggedInUser = $this->createUser();
        self::$client->loginUser(self::$loggedInUser);
    }

    /**
     * Standard function to request an API endpoint in the test env and decode the JSON response.
     * Makes additional assertions if the request is expected to be successful.
     * 
     * @return array The decoded JSON response
     */
    protected function requestJsonApi(string $method, string $uri, array $data = [], int $expectStatusCode = 200): array
    {
        $response = $this->requestApi($method, $uri, $data, $expectStatusCode);
        $this->assertJson($response);

        return \json_decode($response, true);
    }

    /**
     * Standard function to request an API endpoint in the test env.
     * Makes additional assertions if the request is expected to be successful.
     * 
     * @return string The raw response content
     */
    protected function requestApi(string $method, string $uri, array $data = [], int $expectStatusCode = 200): string
    {
        $uri = \sprintf('/api/%s', \ltrim($uri, '/'));
        self::$client->request($method, $uri, content: \json_encode($data));

        if ($expectStatusCode === 200) {
            $this->assertResponseIsSuccessful();
        } else {
            $this->assertResponseStatusCodeSame($expectStatusCode);
        }

        return self::$client->getResponse()->getContent();
    }

    // helper functions to quickly create entities in test

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
        } else {
            // @todo throws an exception as the owner entity is new and not persisted according to the doctrine ORM
            // $managedOwner = self::$em->getRepository(User::class)->find($selectedProject->getOwner()->getId());
            // $selectedProject->setOwner($managedOwner);
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