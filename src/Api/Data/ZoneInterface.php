<?php
/**
 * @author Tiarg Team
 * @copyright Copyright (c) 2021 Tiarg
 * @package Tiargsa_CorreoArgentino
 */
namespace Tiargsa\CorreoArgentino\Api\Data;

interface ZoneInterface
{
    const ZONE_ID = 'zona_id';
    const NAME = 'nombre';

    /**
     * @return int
     */
    public function getZoneId(): int;

    /**
     * @param int|null $zoneId
     * @return ZoneInterface
     */
    public function setZoneId(?int $zoneId): ZoneInterface;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     * @return ZoneInterface
     */
    public function setName(string $name): ZoneInterface;
}
