<?php
namespace PhlyPaste\View;

use Zend\Json\Strategy\JsonStrategy as ZendJsonStrategy;

/**
 * Modified JsonStrategy to use applicatin/hal+json as type
 */
class JsonStrategy extends ZendJsonStrategy
{
    /**
     * Inject the response with the JSON payload and appropriate Content-Type header
     *
     * @param  ViewEvent $e
     * @return void
     */
    public function injectResponse(ViewEvent $e)
    {
        $renderer = $e->getRenderer();
        if ($renderer !== $this->renderer) {
            // Discovered renderer is not ours; do nothing
            return;
        }

        $result   = $e->getResult();
        if (!is_string($result)) {
            // We don't have a string, and thus, no JSON
            return;
        }

        // Populate response
        $response = $e->getResponse();
        $response->setContent($result);
        $headers = $response->getHeaders();
        if ($this->renderer->hasJsonpCallback()) {
            $headers->addHeaderLine('content-type', 'application/javascript');
        } else {
            $headers->addHeaderLine('content-type', 'application/hal+json');
        }
    }
}
