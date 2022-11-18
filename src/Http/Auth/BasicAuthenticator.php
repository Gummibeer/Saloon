<?php declare(strict_types=1);

namespace Saloon\Http\Auth;

use Saloon\Contracts\PendingRequest;
use Saloon\Contracts\Authenticator;

class BasicAuthenticator implements Authenticator
{
    /**
     * @param string $username
     * @param string $password
     */
    public function __construct(
        public string $username,
        public string $password,
    ) {
        //
    }

    /**
     * Apply the authentication to the request.
     *
     * @param PendingRequest $pendingRequest
     * @return void
     */
    public function set(PendingRequest $pendingRequest): void
    {
        $pendingRequest->config()->add('auth', [$this->username, $this->password]);
    }
}
