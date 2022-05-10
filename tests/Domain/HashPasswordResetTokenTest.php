<?php

namespace Becklyn\Security\Tests\Domain;

use Becklyn\Security\Domain\HashPasswordResetToken;
use Becklyn\Security\Domain\UserTestTrait;
use PHPUnit\Framework\TestCase;

class HashPasswordResetTokenTest extends TestCase
{
    use UserTestTrait;

    public function testHashPasswordResetTokenReturnsSaltedSha1OfPlainToken(): void
    {
        $token = $this->givenAPasswordResetToken();
        $salt = uniqid();
        $fixture = new HashPasswordResetToken($salt);
        $this->assertEquals(sha1($salt . $token), $fixture->execute($token));
    }
}
