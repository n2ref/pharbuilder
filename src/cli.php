<?php

if (PHP_SAPI !== 'cli') return;

$options = getopt('s:o:c:b:h::', array(
    'source:',
    'outfile:',
    'compress:',
    'bootstrap:',
    'help::',
));

if (( ! $options && $argc == 1) || ! isset($argv[1]) || isset($options['h']) || isset($options['help'])) {
    echo <<< EOF
Phar Builder
Usage: php pharbuilder [OPTIONS]
Required arguments:
\t-s\t--source\tSource path\n
Optional arguments:
\t-o\t--outfile\tOut phar file name
\t-c\t--compress\tCompress type (GZ, BZ2), NONE - default
\t-b\t--bootstrap\tBootstrap phar file
\t-h\t--help\t\tHelp message\n
Example of usage:
php pharbuilder -s ~/source/my_lib -o ~/bin/my_lib.phar -b bootstrap.php -c gz\n
Author: Shabunin Igor <shabuninil24@gmail.com>\n
EOF;
    exit;
}



if ( ! class_exists('Phar')) {
    echo "Php extension \"Phar\" not found!\n";
    exit;
}



$home_path = getenv('HOME');
$pwd_path  = getenv('PWD');


// SOURCE
if (isset($options['s']) || isset($options['source'])) {
    $source_path = isset($options['s']) ? $options['s'] : $options['source'];
    $source_path = rtrim($source_path, '/');

    if ($source_path[0] == '~') {
        $source_path = $home_path . '/'  . substr($source_path, 1);

    } elseif ($source_path[0] != '/') {
        $source_path = $pwd_path . '/' . $source_path;
    }
}

if ( ! isset($source_path) || ! is_dir($source_path)) {
    echo "Invalid source path!\n";
    exit;
}



// COMPRESS
if (isset($options['c']) || isset($options['compress'])) {
    $input_compress = isset($options['c']) ? $options['c'] : $options['compress'];
    switch (strtolower($input_compress)) {
        case 'gz':   $compress = Phar::GZ; break;
        case 'bz2':  $compress = Phar::BZ2; break;
        case 'none': $compress = Phar::NONE; break;
        default: echo "Invalid compress type! Need gz, bz2 or none compress type.\n"; exit; break;
    }
}


// BOOTSTRAP
if (isset($options['b']) || isset($options['bootstrap'])) {
    $bootstrap = isset($options['b']) ? $options['b'] : $options['bootstrap'];

    if ($bootstrap[0] == '~' || $bootstrap[0] == '/' || ! is_file($source_path . '/' . $bootstrap)) {
        echo "Bootstrap file \"" . $source_path . '/' . $bootstrap . "\" not found!\n";
        exit;
    }
}



// OUTFILE
if (isset($options['o']) || isset($options['outfile'])) {
    $outfile = isset($options['o']) ? $options['o'] : $options['outfile'];

    if ($outfile[0] == '~') {
        $outfile = $home_path . '/' . substr($outfile, 1);

    } elseif ($outfile[0] != '/') {
        $outfile = $pwd_path . '/' . $outfile;
    }

} else {
    $outfile = $pwd_path . '/' . basename($source_path) . '.phar';
}




require_once 'PharBuilder.php';

try {
    $pharbuilder = new PharBuilder($source_path, $outfile);

    if (isset($bootstrap)) $pharbuilder->setBootstrap($bootstrap);
    if (isset($compress))  $pharbuilder->setCompress($compress);

    $is_success_build = $pharbuilder->buildPhar();

    if ($is_success_build) {
        echo "\e[92mSuccess build!\e[0m" . PHP_EOL;

    } else {
        throw new \Exception("Error build! \"{$outfile}\"");
    }

} catch (Exception $e) {
    echo "\e[91mERROR: " . $e->getMessage() . PHP_EOL .
        $e->getFile() . ":" .  $e->getLine() .  "\e[0m" . PHP_EOL;
}