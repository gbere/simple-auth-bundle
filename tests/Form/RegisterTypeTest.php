<?php

declare(strict_types=1);

namespace Gbere\Security\Tests\Form;

use Gbere\Security\Entity\User;
use Gbere\Security\Form\RegisterType;
use Symfony\Component\Form\Test\TypeTestCase;

final class RegisterTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $email = 'test@test.com';
        $password = 'patata';
        $formData = [
            'email' => $email,
            'password' => [
                'first' => $password,
                'second' => $password,
            ],
        ];
        $userToCompare = new User();
        $form = $this->factory->create(RegisterType::class, $userToCompare);
        $user = new User();
        $user->setEmail($email);
        $user->setPassword($password);
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
