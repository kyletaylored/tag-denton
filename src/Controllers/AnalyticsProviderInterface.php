<?php

namespace App\Controllers;

interface AnalyticsProviderInterface
{
    public function sendEvent(string $eventName, array $properties): bool;
    public function isEnabled(): bool;
}