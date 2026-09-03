<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Example\Form;

use PrecisionSoft\Symfony\JsonForm\Contract\DtoInterface;
use PrecisionSoft\Symfony\JsonForm\Element\PasswordElement;
use PrecisionSoft\Symfony\JsonForm\Element\StringElement;
use PrecisionSoft\Symfony\JsonForm\Example\Dto\UserRegistrationDto;
use PrecisionSoft\Symfony\JsonForm\Form\Action;
use PrecisionSoft\Symfony\JsonForm\Form\Form;
use PrecisionSoft\Symfony\JsonForm\Service\Contract\AbstractFormService;
use Symfony\Component\HttpFoundation\Request;

class UserRegistrationForm extends AbstractFormService
{
    protected function getDtoClass(): string
    {
        return UserRegistrationDto::class;
    }

    protected function getMethod(): string
    {
        return Request::METHOD_POST;
    }

    protected function getAction(DtoInterface $dto): Action
    {
        return new Action('catalogue-user-register');
    }

    protected function build(Form $form, DtoInterface $dto): void
    {
        $form->addElement(new StringElement('email', 'email'))
            ->addElement(new PasswordElement('password', 'password'));
    }
}
