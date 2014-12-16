<?php

/**
 * @licence GNU GPL v2+
 * @author Shabuninil Igor <shabuninil24@gmail.com>
 */
class PharBuilder {

    protected $compress  = Phar::NONE;
    protected $bootstrap = '';
    protected $outfile   = '';
    protected $app       = '';


    /**
     * @param string $source
     * @param string $outfile
     * @param string $app
     */
    public function __construct($source, $outfile, $app = '') {
        $this->source  = $source;
        $this->outfile = $outfile;
        $this->app     = $app ? $app : basename($outfile);
    }


    /**
     * Указание метода сжатия
     * @param int $compress
     * @throws Exception
     */
    public function setCompress($compress) {
        switch ($compress) {
            case Phar::GZ :   $this->compress = Phar::GZ; break;
            case Phar::BZ2 :  $this->compress = Phar::BZ2; break;
            case Phar::NONE : $this->compress = Phar::NONE; break;
            default: throw new Exception('Invalid compress type!'); break;
        }
    }


    /**
     * Указание первоочередного файла
     * @param string $bootstrap
     */
    public function setBootstrap($bootstrap) {
        $this->bootstrap = $bootstrap;
    }


    /**
     * Сборка архива
     * @return Phar
     */
    public function buildPhar() {

        ini_set('phar.readonly', 0);
        $this->verifyCanBuild();

        $phar = new Phar(
            $this->outfile,
            Phar::CURRENT_AS_FILEINFO | Phar::KEY_AS_FILENAME,
            $this->app
        );

        $phar->buildFromDirectory($this->source);

        if ($this->bootstrap) $phar->setStub($this->getStub());

        if ($this->compress != Phar::NONE && Phar::canCompress($this->compress)) {
            $phar->compressFiles($this->compress);
        }


        return $phar;
    }


    /**
     * Проверка на возможность сборки архива
     * @throws RuntimeException
     */
    protected function verifyCanBuild() {
        if (ini_get('phar.readonly')) {
            throw new RuntimeException(
                'PHP init setting phar.readonly is set to true. Cannot construct phar archives. See '
                . 'http://php.net/manual/en/phar.configuration.php#ini.phar.readonly'
            );
        }
    }


    /**
     * Создание своего Stub файла
     * @return string
     */
    protected function getStub() {
        $entry_point = 'phar://' . $this->app . '/' . $this->bootstrap;
        return <<<EOF
<?php
Phar::mapPhar();
require_once '$entry_point';
__HALT_COMPILER();
EOF;
    }
}