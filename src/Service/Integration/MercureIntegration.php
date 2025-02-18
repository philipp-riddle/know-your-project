<?php

namespace App\Service\Integration;

use App\Entity\Project\Project;
use App\Entity\User\User;
use App\Service\Helper\DefaultNormalizer;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\JwtFacade;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mercure\Exception\RuntimeException;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class MercureIntegration
{
    public function __construct(
        private HubInterface $mercureHub,
        private DefaultNormalizer $defaultNormalizer,
    ) { }

    public function createJWS(Request $request, Project $project, ?array $topics = null): string
    {
        $issuingTime = \date('Y-m-d.H:i:m'); // every minute a new token
        $token = [
            'project' => $project->getId(),
            'issuing_time' => $issuingTime,
        ];

        $key = InMemory::base64Encoded(\base64_encode($_ENV['MERCURE_JWT_SECRET']));
        $token = (new JwtFacade())->issue(
            new Sha256(),
            $key,
            fn (Builder $builder, \DateTimeImmutable $issuedAt): Builder => $builder
                ->issuedBy(\sprintf('%s://%s:%s', $request->getScheme(), $request->getHost(), $request->getPort()))
                ->withClaim('mercure.subscribe', \implode(',', $topics ?? $this->getDefaultTopicsToSubscribe($project)))
                ->permittedFor($this->getMercurePublicHostUrl())
                ->expiresAt($issuedAt->modify('+10 minutes')), // the JWS token is valid for 10 minutes
        );

        return $token->toString();
    }

    public function publishEntityEvent(object $entity, MercureEntityEvent $entityEvent, User $user): void
    {   
        $endpointName = (new \ReflectionClass($entity))->getShortName();

        // @todo this could be improved: getting the user's selected project is reliable as long as the user does not switch the project context in another browser session.
        // as soon as we allow switching the project context we should improve this.
        $this->publish($user->getSelectedProject(), $endpointName, [
            'action' => MercureEntityEvent::getName($entityEvent),
            'entity' => $this->defaultNormalizer->normalize($user, $entity),
            'user' => $user->getId(), // issuing event user; only ID is sufficient here to avoid handling events twice if the receiving user is the same as the issuing user.
        ]);
    }

    public function publish(Project $project, string $endpoint, array $data): void
    {
        $data['endpoint'] = $endpoint;
        $update = new Update(
            \sprintf('%s/%d/%s', $this->getMercurePublicHostUrl(), $project->getId(), $endpoint),
            \json_encode($data),
            // private: true, // @TODO: FIX LATER
        );

        try {
            $this->mercureHub->publish($update);
        } catch (RuntimeException $ex) {
            // mercure is not available
            // @todo add to logger to notice error somewhere else but to not break the application/request
        }
    }

    public function getMercurePublicHostUrl(): string
    {
        $publicUrl = $_ENV['MERCURE_PUBLIC_URL'];
        $host = \parse_url($publicUrl, PHP_URL_HOST);
        $scheme = \parse_url($publicUrl, PHP_URL_SCHEME);
        $port = \parse_url($publicUrl, PHP_URL_PORT);

        return \sprintf('%s://%s:%d', $scheme, $host, $port);
    }

    /**
     * Returns all the default topics to subscribe to.
     * 
     * @param Project $project the project is required to generate topics solely for this project; this way, we can avoid conflicts with other projects (multi tenancy).
     * 
     * @return string[] the default topics to subscribe to. Can be passed on to Mercure when creating a JWS token and connecting to the Hub.
     */
    public function getDefaultTopicsToSubscribe(Project $project): array
    {
        return [
            // this is the wildcard topic to subscribe to all events of the project,
            // e.g. 'localhost:3001/1/Page' to subscribe to all page updates of project 1.
            \sprintf('%s/%d/{+type}', $this->getMercurePublicHostUrl(), $project->getId()),
        ];
    }
}