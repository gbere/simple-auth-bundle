<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Tests\Form;

use Gbere\SimpleAuth\Entity\User;
use Gbere\SimpleAuth\Form\RegisterType;
use Symfony\Component\Form\Test\TypeTestCase;

final class RegisterTypeTest extends TypeTestCase
{
    private const EMAIL = 'test@test.com';
    private const NAME = 'Test';
    private const PASSWORD = 'patata';

    public function testSubmitValidData(): void
    {
        $formData = [
            'email' => self::EMAIL,
            'name' => self::NAME,
            'password' => [
                'first' => self::PASSWORD,
                'second' => self::PASSWORD,
            ],
        ];
        $userToCompare = new User();
        $form = $this->factory->create(RegisterType::class, $userToCompare);
        $user = new User();
        $user->setEmail(self::EMAIL);
        $user->setName(self::NAME);
        $user->setPassword(self::PASSWORD);
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($user, $userToCompare);
        $view = $form->createView();
        $children = $view->children;
        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
