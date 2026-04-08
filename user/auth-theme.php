<?php

function get_auth_color_scheme($conn): string
{
    $scheme = 'classic';

    if (!($conn instanceof mysqli)) {
        return $scheme;
    }

    $candidate = null;
    try {
        $res = $conn->query("SELECT setting_value AS v FROM site_settings WHERE setting_key='auth_color_scheme' LIMIT 1");
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $candidate = $row['v'] ?? null;
        }
    } catch (Throwable $e) {
    }

    if ($candidate === null) {
        try {
            $res = $conn->query("SELECT `value` AS v FROM site_settings WHERE `key`='auth_color_scheme' LIMIT 1");
            if ($res && $res->num_rows > 0) {
                $row = $res->fetch_assoc();
                $candidate = $row['v'] ?? null;
            }
        } catch (Throwable $e) {
        }
    }

    if (is_string($candidate) && preg_match('/^[a-z0-9_-]+$/', $candidate)) {
        $scheme = $candidate;
    } else {
        // Seed classic scheme if missing, try both schemas.
        try {
            $conn->query("INSERT INTO site_settings (setting_key,setting_value) VALUES ('auth_color_scheme','classic') ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");
        } catch (Throwable $e) {
            try {
                $conn->query("INSERT INTO site_settings (`key`,`value`) VALUES ('auth_color_scheme','classic') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");
            } catch (Throwable $e2) {
            }
        }
    }

    if ($scheme === 'default') {
        $scheme = 'classic';
    }

    return $scheme;
}

function get_auth_palette(string $scheme): array
{
    $palettes = [
        'classic' => [
            'navy' => '#173f6d',
            'navy2' => '#0d2847',
            'gold' => '#c6a65a',
            'gold2' => '#dec58c',
            'light' => '#f3f6fb',
            'muted' => '#526274',
            'border' => '#d7e0ec',
            'danger' => '#c0392b',
            'success' => '#1a7a4a'
        ],
        'ocean' => [
            'navy' => '#0f5d78',
            'navy2' => '#0a4357',
            'gold' => '#58a6c2',
            'gold2' => '#89bfd2',
            'light' => '#eef7fb',
            'muted' => '#28566a',
            'border' => '#c8e0eb',
            'danger' => '#bf3b3b',
            'success' => '#1f7a4a'
        ],
        'forest' => [
            'navy' => '#285642',
            'navy2' => '#19382a',
            'gold' => '#9aab75',
            'gold2' => '#bcca9e',
            'light' => '#eff6f0',
            'muted' => '#4e675c',
            'border' => '#d4dfd4',
            'danger' => '#b93a35',
            'success' => '#1f7a4a'
        ],
        'ember' => [
            'navy' => '#9b4f2f',
            'navy2' => '#69311c',
            'gold' => '#d69962',
            'gold2' => '#e4ba8f',
            'light' => '#fbf5f0',
            'muted' => '#77503a',
            'border' => '#ecd9cb',
            'danger' => '#bf3b3b',
            'success' => '#25764d'
        ],
        'royal' => [
            'navy' => '#3f3c89',
            'navy2' => '#292760',
            'gold' => '#9d8ed0',
            'gold2' => '#b7abd9',
            'light' => '#f5f4fb',
            'muted' => '#5c5680',
            'border' => '#ddd9ef',
            'danger' => '#bf3b3b',
            'success' => '#25764d'
        ],
        'graphite' => [
            'navy' => '#273444',
            'navy2' => '#16202d',
            'gold' => '#8b9db1',
            'gold2' => '#acbac8',
            'light' => '#f4f6f8',
            'muted' => '#546170',
            'border' => '#d7dde5',
            'danger' => '#bf3b3b',
            'success' => '#25764d'
        ],
        'sunset' => [
            'navy' => '#ab475b',
            'navy2' => '#742d3d',
            'gold' => '#d89ba7',
            'gold2' => '#e4b9c1',
            'light' => '#fcf3f2',
            'muted' => '#8b4a58',
            'border' => '#efd3d8',
            'danger' => '#c0392b',
            'success' => '#1f7a54'
        ],
        'teal-gold' => [
            'navy' => '#1e5f57',
            'navy2' => '#123f3a',
            'gold' => '#c6a65a',
            'gold2' => '#dec58c',
            'light' => '#eef7f5',
            'muted' => '#456863',
            'border' => '#d3e4df',
            'danger' => '#bf3b3b',
            'success' => '#1f7a54'
        ],
        'midnight' => [
            'navy' => '#3c7adf',
            'navy2' => '#244f9a',
            'gold' => '#7bc4d6',
            'gold2' => '#a7d7e2',
            'light' => '#0d1320',
            'muted' => '#9eacbe',
            'border' => '#2f3d55',
            'danger' => '#d86a6a',
            'success' => '#3cac76'
        ],
        'mint' => [
            'navy' => '#1f7964',
            'navy2' => '#155445',
            'gold' => '#7fb8a0',
            'gold2' => '#a5cdbb',
            'light' => '#edf6f1',
            'muted' => '#4a766a',
            'border' => '#d4e6db',
            'danger' => '#bf3b3b',
            'success' => '#1f7a4a'
        ],
        'sandstone' => [
            'navy' => '#c2410c',
            'navy2' => '#9a3412',
            'gold' => '#f59e0b',
            'gold2' => '#f6b955',
            'light' => '#fef7ed',
            'muted' => '#9a3412',
            'border' => '#fed7aa',
            'danger' => '#c0392b',
            'success' => '#1f7a4a'
        ],
        'plum' => [
            'navy' => '#70467b',
            'navy2' => '#4d2f55',
            'gold' => '#b18cbb',
            'gold2' => '#c9add0',
            'light' => '#f8f4fa',
            'muted' => '#6a4f72',
            'border' => '#e4d7e7',
            'danger' => '#bf3b3b',
            'success' => '#1f7a54'
        ]
    ];

    if (!isset($palettes[$scheme])) {
        $scheme = 'classic';
    }

    return $palettes[$scheme];
}
