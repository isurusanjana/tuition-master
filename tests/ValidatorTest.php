<?php

use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    public function testRequiredFailsOnEmptyValue(): void
    {
        $v = new Validator(['name' => '']);
        $v->required('name');
        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('name', $v->errors());
    }

    public function testRequiredPassesOnPresentValue(): void
    {
        $v = new Validator(['name' => 'Jane Doe']);
        $v->required('name');
        $this->assertFalse($v->fails());
    }

    public function testEmailRejectsInvalidAddress(): void
    {
        $v = new Validator(['email' => 'not-an-email']);
        $v->email('email');
        $this->assertTrue($v->fails());
    }

    public function testEmailAcceptsValidAddress(): void
    {
        $v = new Validator(['email' => 'someone@example.com']);
        $v->email('email');
        $this->assertFalse($v->fails());
    }

    public function testMinLengthValidation(): void
    {
        $v = new Validator(['password' => '123']);
        $v->min('password', 6);
        $this->assertTrue($v->fails());

        $v2 = new Validator(['password' => '123456']);
        $v2->min('password', 6);
        $this->assertFalse($v2->fails());
    }

    public function testNumericValidation(): void
    {
        $v = new Validator(['amount' => 'abc']);
        $v->numeric('amount');
        $this->assertTrue($v->fails());

        $v2 = new Validator(['amount' => '123.45']);
        $v2->numeric('amount');
        $this->assertFalse($v2->fails());
    }

    public function testChainedRulesAccumulateErrors(): void
    {
        $v = new Validator(['email' => '', 'password' => '123']);
        $v->required('email')->email('email')->required('password')->min('password', 6);
        $this->assertTrue($v->fails());
        $this->assertCount(2, $v->errors());
    }

    public function testFirstErrorReturnsAMessage(): void
    {
        $v = new Validator(['name' => '']);
        $v->required('name');
        $this->assertIsString($v->firstError());
    }
}
