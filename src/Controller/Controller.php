<?php

namespace App\Controller;

use App\Exception\PreconditionFailedException;
use App\Service\Helper\ApiControllerHelperService;
use App\Service\Helper\ApplicationEnvironment;
use App\Service\Integration\MercureIntegration;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Basic controller which makes sure any Controller which renders templates complies with our Content Security Policy.
 */
abstract class Controller extends AbstractController
{
    protected MercureIntegration $mercureIntegration;
    protected RequestStack $requestStack;

    public function __construct(
        ApiControllerHelperService $apiControllerHelperService,
    ) {
        $this->mercureIntegration = $apiControllerHelperService->mercureIntegration;
        $this->requestStack = $apiControllerHelperService->requestStack;
    }

    /**
     * This function overrides the render function of the parent class to add our Content Security Policy;
     * It first renders the view using the parent's render function and then adds the CSP and other security headers.
     */
    protected function render(string $view, array $parameters = [], ?Response $response = null): Response
    {
        if (null === $request = $this->requestStack->getCurrentRequest()) {
            throw new PreconditionFailedException('No request available.');
        }

        $response ??= parent::render($view, $parameters, $response);
        $parameters['user'] = $this->getUser();

        $allowedHosts = [
            \sprintf('%s://%s', $request->getScheme(), $request->getHttpHost()), // allow scripts from own domain
        ];
        $allowedConnectHosts = [
            ...$allowedHosts,
            $this->mercureIntegration->getMercurePublicHostUrl(), // allow connection to Mercure hub on given .env URL
        ];

        $allowedHosts = \implode(' ', $allowedHosts);
        $allowedConnectHosts = \implode(' ', $allowedConnectHosts);

        /**
         * Our content security policy can be summarized as follows:
         * We allow ONLY sources from our own domain with the addition of connecting to the Mercure hub on port 3001 with connect-src.
         * Furthermore, we allow inline styles for Vue.js and its components as they do not expose any security risks.
         */
        $response->headers->set('Content-Security-Policy', "
            default-src 'self' $allowedHosts;
            script-src 'self' $allowedHosts;
            style-src 'self' $allowedHosts 'unsafe-inline';
            font-src 'self' $allowedHosts;
            img-src 'self' $allowedHosts;
            frame-src 'self' $allowedHosts;
            connect-src 'self' $allowedConnectHosts;
        ");

        // Add HSTS header for production environment; enforces HTTPS on every channel
        if (ApplicationEnvironment::isProdEnv()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // prevents MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Prevents clickjacking attacks / Using our application in an iframe
        $response->headers->set('X-Frame-Options', 'DENY');

        // Prevents XSS attacks
        // NOTE: This is only for legacy browsers; it is worth adding it though
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Set Permission policy to prevent accidental/unsafe use of features, such as microphone, camera, etc.
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        return $response;
    }
}