<?php

declare(strict_types=1);
/**
 * Disclaimer: This file is part of the AIPic Repository Object plugin for ILIAS.
 */

use ILIAS\ResourceStorage\Stakeholder\AbstractResourceStakeholder;

/**
 * Class StorageStakeHolderAIGenerator
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class StorageStakeHolderAIPic extends AbstractResourceStakeholder
{
    public function __construct()
    {
    }

    public function getId(): string
    {
        return 'aip_file';
    }

    public function getOwnerOfNewResources(): int
    {
        return 6;
    }
}
