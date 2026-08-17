<?php

function getClientConnectionStatus(?string $lastHeartbeat): array
{
    $threshold = defined('CLIENT_ONLINE_THRESHOLD_SECONDS')
        ? (int)CLIENT_ONLINE_THRESHOLD_SECONDS
        : 45;

    if (!$lastHeartbeat) {
        return [
            'online' => false,
            'label' => 'No heartbeat',
            'badge_class' => 'bg-secondary',
            'last_seen' => 'Never'
        ];
    }

    $timestamp = strtotime($lastHeartbeat);

    if (!$timestamp) {
        return [
            'online' => false,
            'label' => 'Invalid heartbeat',
            'badge_class' => 'bg-secondary',
            'last_seen' => 'Unknown'
        ];
    }

    $secondsAgo = max(0, time() - $timestamp);
    $online = $secondsAgo <= $threshold;

    return [
        'online' => $online,
        'label' => $online ? 'Client Online' : 'Client Offline',
        'badge_class' => $online ? 'bg-success' : 'bg-danger',
        'last_seen' => formatSecondsAgo($secondsAgo)
    ];
}

function formatSecondsAgo(int $seconds): string
{
    if ($seconds < 60) {
        return $seconds . ' seconds ago';
    }

    $minutes = floor($seconds / 60);

    if ($minutes < 60) {
        return $minutes . ' minute' . ($minutes === 1 ? '' : 's') . ' ago';
    }

    $hours = floor($minutes / 60);

    if ($hours < 24) {
        return $hours . ' hour' . ($hours === 1 ? '' : 's') . ' ago';
    }

    $days = floor($hours / 24);

    return $days . ' day' . ($days === 1 ? '' : 's') . ' ago';
}