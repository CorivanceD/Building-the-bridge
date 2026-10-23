<?php
// One-off: convert short open tags "<?" (followed by whitespace) to "<?php".
// Usage: php -d short_open_tag=On tools/convert-short-tags.php <root>
// Idempotent: a second run reports 0 changes.

if (PHP_SAPI !== 'cli') {
    exit(1);
}

if ($argc < 2) {
    fwrite(STDERR, "usage: php -d short_open_tag=On convert-short-tags.php <root>\n");
    exit(1);
}

$root = rtrim(realpath($argv[1]), '/');
if ($root === false || !is_dir($root)) {
    fwrite(STDERR, "not a directory: {$argv[1]}\n");
    exit(1);
}

// Relative directories (from root) that are never touched.
$excluded = [
    'libs',
    'inc/PHPExcel',
    'inc/recaptcha',
    'inc/FeedWriter',
    'vendor',
    'docker',
    'tools',
    '.git',
];

$isExcluded = function (string $rel) use ($excluded): bool {
    foreach ($excluded as $dir) {
        if ($rel === $dir || strpos($rel, $dir . '/') === 0) {
            return true;
        }
    }
    return false;
};

$iterator = new RecursiveIteratorIterator(
    new RecursiveCallbackFilterIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        function (SplFileInfo $current) use ($root, $isExcluded): bool {
            $rel = ltrim(substr($current->getPathname(), strlen($root)), '/');
            if ($current->isDir()) {
                return !$isExcluded($rel);
            }
            return true;
        }
    )
);

$totalFiles = 0;
$totalTags = 0;

foreach ($iterator as $file) {
    /** @var SplFileInfo $file */
    if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
        continue;
    }

    $path = $file->getPathname();
    $rel = ltrim(substr($path, strlen($root)), '/');
    if ($isExcluded($rel)) {
        continue;
    }

    $source = file_get_contents($path);
    if ($source === false) {
        fwrite(STDERR, "cannot read $rel\n");
        exit(1);
    }

    $tokens = token_get_all($source);
    $out = '';
    $tags = 0;
    $count = count($tokens);
    for ($i = 0; $i < $count; $i++) {
        $token = $tokens[$i];
        if (!is_array($token)) {
            $out .= $token;
            continue;
        }
        [$id, $text] = $token;
        // Short open tag: the token text is exactly "<?" and the next token
        // starts with whitespace (the tokenizer emits that whitespace separately).
        if ($id === T_OPEN_TAG && $text === '<?' && $i + 1 < $count) {
            $next = $tokens[$i + 1];
            $nextText = is_array($next) ? $next[1] : $next;
            if (preg_match('/^\s/', $nextText)) {
                $text = '<?php';
                $tags++;
            }
        }
        $out .= $text;
    }

    if ($tags === 0 || $out === $source) {
        continue;
    }

    if (file_put_contents($path, $out) === false) {
        fwrite(STDERR, "cannot write $rel\n");
        exit(1);
    }

    echo "changed $rel ($tags tags)\n";
    $totalFiles++;
    $totalTags += $tags;
}

echo "total: $totalTags tags in $totalFiles files\n";
