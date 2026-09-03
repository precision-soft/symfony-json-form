<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Form;

use PrecisionSoft\Symfony\JsonForm\Form\Action;
use PrecisionSoft\Symfony\Phpunit\MockDto;
use PrecisionSoft\Symfony\Phpunit\TestCase\AbstractTestCase;

/**
 * @internal
 */
final class ActionTest extends AbstractTestCase
{
    public static function getMockDto(): MockDto
    {
        return new MockDto(Action::class);
    }

    public function testRenderWithoutParameters(): void
    {
        $action = new Action('app_user_create');

        $result = $action->render();

        static::assertSame('app_user_create', $result['route']);
        static::assertNull($result['parameters']);
    }

    public function testRenderWithParameters(): void
    {
        $action = new Action('app_user_edit', ['id' => 42]);

        $result = $action->render();

        static::assertSame('app_user_edit', $result['route']);
        static::assertSame(['id' => 42], $result['parameters']);
    }

    public function testRenderStructure(): void
    {
        $action = new Action('route');

        $result = $action->render();

        static::assertArrayHasKey('route', $result);
        static::assertArrayHasKey('parameters', $result);
    }
}
