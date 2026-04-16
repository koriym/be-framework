<?php

declare(strict_types=1);

namespace MyVendor\MyApp\Ldd\RegisterUser;

use Be\Framework\Exception\UnbecomingException;
use Be\Framework\SemanticLog\Been;
use MyVendor\MyApp\Ldd\RegisterUser\Context\EmailFormatAssertedContext;
use MyVendor\MyApp\Ldd\RegisterUser\Context\UserInsertedContext;
use Ray\Di\Di\Inject;
use Ray\InputQuery\Attribute\Input;

final class RegisteredUser
{
    public readonly int $userId;
    public readonly Been $been;

    public function __construct(
        #[Input]
        public readonly string $value,
        #[Inject]
        EmailVerifier $verifier,
        #[Inject]
        UserRepository $users,
        #[Inject]
        Been $been,
    ) {
        if (! $verifier->check($value)) {
            throw new UnbecomingException('email format failed');
        }

        $this->userId = $users->insert(['email' => $value]);
        $this->been = $been
            ->with(new EmailFormatAssertedContext(
                email: $value,
            ))
            ->with(new UserInsertedContext(
                userId: $this->userId,
                email: $value,
            ));
    }
}
