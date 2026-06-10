<?php

function getPythonCommand(): ?string {
    $configured = trim((string)getenv('IA_MANAGER_PYTHON'));

    if ($configured !== '') {
        return escapeshellarg($configured);
    }

    if (PHP_OS_FAMILY === 'Windows') {
        if (commandExists('py')) {
            return 'py -3';
        }

        if (commandExists('python')) {
            return 'python';
        }

        return null;
    }

    if (commandExists('python3')) {
        return 'python3';
    }

    if (commandExists('python')) {
        return 'python';
    }

    return null;
}

function commandExists(string $command): bool {
    $lookup = PHP_OS_FAMILY === 'Windows'
        ? 'where ' . escapeshellarg($command) . ' 2>NUL'
        : 'command -v ' . escapeshellarg($command) . ' 2>/dev/null';

    return trim((string)shell_exec($lookup)) !== '';
}
