<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Example\Service;

/** the nomenclator lists the forms offer; an application reads them from its database, the tests double this class */
class CatalogOptionProvider
{
    /** @return array<string, string> */
    public function getCurrencyOptions(): array
    {
        return ['EUR' => 'Euro', 'RON' => 'Romanian leu', 'USD' => 'United States dollar'];
    }

    /** @return array<string, string> */
    public function getChannelOptions(): array
    {
        return ['web' => 'Web shop', 'store' => 'Store', 'marketplace' => 'Marketplace'];
    }
}
