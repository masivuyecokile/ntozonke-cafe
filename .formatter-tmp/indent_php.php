<?php
$root = realpath(__DIR__ . '/..');
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS));
foreach ($rii as $file) {
    if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
        continue;
    }

    $path = $file->getRealPath();
    $content = file_get_contents($path);
    if ($content === false) {
        fwrite(STDERR, "Failed to read $path\n");
        continue;
    }

    $formatted = indentPhpContent($content);
    if ($formatted !== $content) {
        file_put_contents($path, $formatted);
        echo "Formatted: $path\n";
    }
}

function indentPhpContent(string $content): string
{
    $tokens = token_get_all($content);
    $output = '';
    $indent = 0;
    $atLineStart = true;
    $inPhp = false;

    foreach ($tokens as $token) {
        if (is_array($token)) {
            [$type, $text] = [$token[0], $token[1]];

            if ($type === T_OPEN_TAG || $type === T_OPEN_TAG_WITH_ECHO) {
                $inPhp = true;
                $output .= $text;
                $atLineStart = substr($text, -1) === "\n";
                continue;
            }

            if ($type === T_CLOSE_TAG) {
                $inPhp = false;
                $output .= $text;
                $atLineStart = substr($text, -1) === "\n";
                continue;
            }

            if (!$inPhp) {
                $output .= $text;
                $atLineStart = substr($text, -1) === "\n";
                continue;
            }

            if ($type === T_WHITESPACE) {
                if (strpos($text, "\n") !== false) {
                    $output .= "\n";
                    $atLineStart = true;
                } elseif ($output !== '' && substr($output, -1) !== ' ' && substr($output, -1) !== "\n") {
                    $output .= ' ';
                }
                continue;
            }

            if ($type === T_COMMENT || $type === T_DOC_COMMENT) {
                $lines = explode("\n", $text);
                foreach ($lines as $index => $line) {
                    if ($atLineStart && $line !== '') {
                        $output .= str_repeat('    ', $indent);
                    }
                    $output .= $line;
                    if ($index !== count($lines) - 1) {
                        $output .= "\n";
                        $atLineStart = true;
                    } else {
                        $atLineStart = substr($line, -1) === "\n";
                    }
                }
                continue;
            }

            if ($text === '{') {
                if ($atLineStart) {
                    $output .= str_repeat('    ', $indent);
                }
                $output .= '{';
                $indent++;
                $output .= "\n";
                $atLineStart = true;
                continue;
            }

            if ($text === '}') {
                $indent = max(0, $indent - 1);
                if (!$atLineStart) {
                    $output .= "\n";
                }
                $output .= str_repeat('    ', $indent) . '}';
                $atLineStart = false;
                continue;
            }

            if ($text === ';') {
                if ($atLineStart) {
                    $output .= str_repeat('    ', $indent);
                    $atLineStart = false;
                }
                $output .= ';';
                $output .= "\n";
                $atLineStart = true;
                continue;
            }

            if ($text === "\n") {
                $output .= "\n";
                $atLineStart = true;
                continue;
            }

            if ($atLineStart) {
                $output .= str_repeat('    ', $indent);
                $atLineStart = false;
            }

            $output .= $text;
            continue;
        }

        if ($atLineStart && trim($token) !== '') {
            $output .= str_repeat('    ', $indent);
        }
        $output .= $token;
        $atLineStart = substr($token, -1) === "\n";
    }

    return rtrim($output, "\n") . "\n";
}
