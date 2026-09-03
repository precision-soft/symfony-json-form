<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Example\Test\Utility;

use PrecisionSoft\Symfony\JsonForm\Example\Dto\ProductEditDto;

/** a draft is a product: the form accepts the subclass and populates it in place */
class ProductEditDraftDto extends ProductEditDto {}
