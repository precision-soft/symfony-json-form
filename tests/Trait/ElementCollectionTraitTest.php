<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Trait;

use PHPUnit\Framework\TestCase;
use PrecisionSoft\Symfony\JsonForm\Element\StringElement;
use PrecisionSoft\Symfony\JsonForm\Exception\Exception;
use PrecisionSoft\Symfony\JsonForm\Form\Action;
use PrecisionSoft\Symfony\JsonForm\Form\Form;

/**
 * @internal
 */
final class ElementCollectionTraitTest extends TestCase
{
    public function testAddElementReturnsStaticForChaining(): void
    {
        $form = new Form('testform', 'GET', new Action('route'));

        $result = $form->addElement(new StringElement('title', 'Title'));

        static::assertInstanceOf(Form::class, $result);
    }

    public function testAddDuplicateElementNameThrowsException(): void
    {
        $form = new Form('testform', 'GET', new Action('route'));
        $form->addElement(new StringElement('title', 'Title'));

        static::expectException(Exception::class);
        static::expectExceptionMessageMatches('/duplicate element name/');

        $form->addElement(new StringElement('title', 'Other label'));
    }

    public function testRenderElementsIncludesAllElements(): void
    {
        $form = new Form('testform', 'GET', new Action('route'));
        $form->addElement(new StringElement('firstname', 'First name'));
        $form->addElement(new StringElement('lastname', 'Last name'));

        $result = $form->render(['firstname' => 'John', 'lastname' => 'Doe']);

        static::assertArrayHasKey('firstname', $result['elements']);
        static::assertArrayHasKey('lastname', $result['elements']);
    }

    public function testRenderElementsWithMissingValueUsesNull(): void
    {
        $form = new Form('testform', 'GET', new Action('route'));
        $form->addElement(new StringElement('title', 'Title'));

        $result = $form->render([]);

        static::assertNull($result['elements']['title']['value']);
    }
}
