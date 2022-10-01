<?php
namespace MODX\Revolution\Sources;

use Exception;
use League\Flysystem\PhpseclibV2\SftpConnectionProvider;
use League\Flysystem\PhpseclibV2\SftpAdapter;
use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\Ftp\FtpConnectionOptions;
use League\Flysystem\Visibility;
use xPDO\xPDO;

/**
 * Implements an SFTP-based media source.
 *
 * @package MODX\Revolution\Sources
 */
class modSFTPMediaSource extends modMediaSource
{
    protected $visibility_files = true;
    protected $visibility_dirs = false;


    /**
     * @return bool
     */
    public function initialize()
    {
        parent::initialize();
        $properties = $this->getPropertyList();

        $config = [
            'host' => trim($this->xpdo->getOption('host', $properties)),
            'username' => trim($this->xpdo->getOption('username', $properties)),
            'password' => trim($this->xpdo->getOption('password', $properties)),
            'privateKey' => trim($this->xpdo->getOption('key', $properties)),
            'passphrase' => trim($this->xpdo->getOption('key_passphrase', $properties)),
            'port' => (int)$this->xpdo->getOption('port', $properties, 22),
            'root' => trim($this->xpdo->getOption('root', $properties)),
            'useAgent' => !empty($this->xpdo->getOption('useAgent', $properties)),            
            'timeout' => (int)$this->xpdo->getOption('timeout', $properties),
            'maxTries' => (int)$this->xpdo->getOption('tries', $properties),
            'hostFingerprint' => trim($this->xpdo->getOption('host_fingerprint', $properties)),
        ];

        try {           
            $adapter = new SftpAdapter(SftpConnectionProvider::fromArray($config),$config['root']);
        } catch (Exception $e) {
            $this->xpdo->log(xPDO::LOG_LEVEL_ERROR,
                $this->xpdo->lexicon('source_err_init', ['source' => $this->get('name')]) . ' ' . $e->getMessage());

            return false;
        }
        $this->loadFlySystem($adapter);

        return true;
    }


    /**
     * @return null|string
     */
    public function getTypeName()
    {
        $this->xpdo->lexicon->load('source');

        return $this->xpdo->lexicon('source_type.ftp');
    }


    /**
     * @return null|string
     */
    public function getTypeDescription()
    {
        $this->xpdo->lexicon->load('source');

        return $this->xpdo->lexicon('source_type.ftp_desc');
    }


    /**
     * @return array
     */
    public function getDefaultProperties()
    {
        return [
            'host' => [
                'name' => 'host',
                'desc' => 'prop_sftp.host_desc',
                'type' => 'textfield',
                'options' => '',
                'value' => '',
                'lexicon' => 'core:source',
            ],
            'username' => [
                'name' => 'username',
                'desc' => 'prop_sftp.username_desc',
                'type' => 'textfield',
                'options' => '',
                'value' => 'anonymous',
                'lexicon' => 'core:source',
            ],
            'password' => [
                'name' => 'password',
                'desc' => 'prop_sftp.password_desc',
                'type' => 'password',
                'options' => '',
                'value' => '',
                'lexicon' => 'core:source',
            ],
           'privateKey' => [
                'name' => 'privateKey',
                'desc' => 'prop_sftp.privateKey_desc',
                'type' => 'password',
                'options' => '',
                'value' => '',
                'lexicon' => 'core:source',
            ],
            'passphrase' => [
                'name' => 'passphrase',
                'desc' => 'prop_sftp.passphrase_desc',
                'type' => 'password',
                'options' => '',
                'value' => '',
                'lexicon' => 'core:source',
            ],
            'root' => [
                'name' => 'root',
                'desc' => 'prop_sftp.root_desc',
                'type' => 'textfield',
                'options' => '',
                'value' => '',
                'lexicon' => 'core:source',
            ],
            'url' => [
                'name' => 'url',
                'desc' => 'prop_sftp.url_desc',
                'type' => 'textfield',
                'options' => '',
                'value' => '',
                'lexicon' => 'core:source',
            ],
            'port' => [
                'name' => 'port',
                'desc' => 'prop_sftp.port_desc',
                'type' => 'numberfield',
                'options' => '',
                'value' => 22,
                'lexicon' => 'core:source',
            ],
            'useAgent' => [
                'name' => 'useAgent',
                'desc' => 'prop_sftp.useAgent_desc',
                'type' => 'combo-boolean',
                'options' => '',
                'value' => false,
                'lexicon' => 'core:source',
            ],
            'timeout' => [
                'name' => 'timeout',
                'desc' => 'prop_sftp.timeout_desc',
                'type' => 'numberfield',
                'options' => '',
                'value' => 10,
                'lexicon' => 'core:source',
            ],
            'maxTries' => [
                'name' => 'timeout',
                'desc' => 'prop_sftp.maxTries_desc',
                'type' => 'numberfield',
                'options' => '',
                'value' => 4,
                'lexicon' => 'core:source',
            ],
            'hostFingerprint' => [
                'name' => 'hostFingerprint',
                'desc' => 'prop_sftp.hostFingerprint_desc',
                'type' => 'textfield',
                'options' => '',
                'value' => '',
                'lexicon' => 'core:source',
            ],
            'imageExtensions' => [
                'name' => 'imageExtensions',
                'desc' => 'prop_file.imageExtensions_desc',
                'type' => 'textfield',
                'value' => 'jpg,jpeg,png,gif,webp',
                'lexicon' => 'core:source',
            ],
            'skipFiles' => [
                'name' => 'skipFiles',
                'desc' => 'prop_file.skipFiles_desc',
                'type' => 'textfield',
                'options' => '',
                'value' => '.svn,.git,_notes,nbproject,.idea,.DS_Store',
                'lexicon' => 'core:source',
            ],
        ];
    }


    /**
     * @param string $src
     *
     * @return string
     */
    public function prepareSrcForThumb($src)
    {
        $properties = $this->getPropertyList();
        if (strpos($src, $properties['url']) === false) {
            $src = $properties['url'] . ltrim($src, '/');
        }

        return $src;
    }


    /**
     * @param string $object
     *
     * @return string
     */
    public function getBasePath($object = '')
    {
        return '';
    }


    /**
     * @param string $object
     *
     * @return mixed
     */
    public function getBaseUrl($object = '')
    {
        $properties = $this->getPropertyList();

        return $properties['url'];
    }


    /**
     * @param string $object
     *
     * @return bool|string
     */
    public function getObjectUrl($object = '')
    {
        $properties = $this->getPropertyList();

        return !empty($properties['url'])
            ? rtrim($properties['url'], '/') . '/' . $object
            : false;
    }


    /**
     * @param string $path
     * @param string $ext
     * @param int $width
     * @param int $height
     * @param array $bases
     * @param array $properties
     *
     * @return array
     */
    protected function buildManagerImagePreview($path, $ext, $width, $height, $bases, $properties = [])
    {
        if ($image = $this->getObjectUrl($path)) {
            if ($this->getVisibility($path) != Visibility::PUBLIC) {
                $image = false;
            }
        }

        return [
            'src' => $image,
            'width' => $width,
            'height' => $height,
        ];
    }
}
