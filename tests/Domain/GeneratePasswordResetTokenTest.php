<?php

namespace Becklyn\Security\Tests\Domain;

use Becklyn\Security\Domain\GeneratePasswordResetToken;
use PHPUnit\Framework\TestCase;

class GeneratePasswordResetTokenTest extends TestCase
{
    public function testGeneratePasswordResetTokenReturnsNonEmptyString(): void
    {
        $fixture = new GeneratePasswordResetToken();
        $this->assertNotEmpty($fixture->execute());
    }
}
