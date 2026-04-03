<?php
declare(strict_types=1);

function load_env_file(string $path): void
{
	if (!is_file($path) || !is_readable($path)) {
		return;
	}

	$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	if ($lines === false) {
		return;
	}

	foreach ($lines as $line) {
		$line = trim($line);

		if ($line === '' || str_starts_with($line, '#') || str_starts_with($line, ';')) {
			continue;
		}

		$separatorPosition = strpos($line, '=');
		if ($separatorPosition === false) {
			continue;
		}

		$key = trim(substr($line, 0, $separatorPosition));
		$value = trim(substr($line, $separatorPosition + 1));

		if ($key === '') {
			continue;
		}

		$length = strlen($value);
		if ($length >= 2) {
			$firstChar = $value[0];
			$lastChar = $value[$length - 1];
			if (($firstChar === '"' && $lastChar === '"') || ($firstChar === '\'' && $lastChar === '\'')) {
				$value = substr($value, 1, -1);
			}
		}

		$_ENV[$key] = $value;
		$_SERVER[$key] = $value;
		putenv($key . '=' . $value);
	}
}

load_env_file(__DIR__ . '/.env');

function env(string $key, string $default = ''): string
{
	$value = getenv($key);
	if ($value === false || $value === '') {
		$value = $_ENV[$key] ?? $_SERVER[$key] ?? $default;
	}

	if ($value === '') {
		return $default;
	}

	return (string)$value;
}

const APP_TITLE = 'Proyecto de vacaciones';
const STUDENT_NAME = 'Efrain Uziel Acosta Rodriguez';
const VACATION_START_DATE = '2026-03-27';
const VACATION_END_DATE = '2026-04-10';

const DB_HOST = '127.0.0.1';
const DB_PORT = '5432';
const DB_NAME = 'tarea_uziel';
const DB_USER = 'root';
const DB_PASS = '';

function app_title(): string
{
	return env('APP_TITLE', APP_TITLE);
}

function student_name(): string
{
	return env('STUDENT_NAME', STUDENT_NAME);
}

function vacation_start_date(): string
{
	return env('VACATION_START_DATE', VACATION_START_DATE);
}

function vacation_end_date(): string
{
	return env('VACATION_END_DATE', VACATION_END_DATE);
}

function db_host(): string
{
	return env('DB_HOST', DB_HOST);
}

function db_port(): string
{
	return env('DB_PORT', DB_PORT);
}

function db_name(): string
{
	return env('DB_NAME', DB_NAME);
}

function db_user(): string
{
	return env('DB_USER', DB_USER);
}

function db_pass(): string
{
	return env('DB_PASS', DB_PASS);
}