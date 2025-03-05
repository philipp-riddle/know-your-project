<?php

namespace App\Tests\Application\Api;

use App\Entity\Page\Page;
use App\Entity\Page\PageTab;
use App\Entity\Project\Project;
use App\Entity\Project\ProjectUser;
use App\Entity\User\User;
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

    protected function getPageTab(?User $user = null): PageTab
    {
        // fetch a managed version of the project entity
        $project = self::$em->getRepository(Project::class)->find(($user ?? self::$loggedInUser)->getSelectedProject()->getId());
        $page = (new Page())
            ->setName('Test Page')
            ->setProject($project)
            ->setUser($user ?? self::$loggedInUser)
            ->setCreatedAt(new \DateTime());
        self::$em->persist($page);

        $pageTab = (new PageTab())
            ->setName('Test Tab')
            ->setPage($page)
            ->setCreatedAt(new \DateTime());
        self::$em->persist($pageTab);

        self::$em->flush();

        return $pageTab;
    }
}