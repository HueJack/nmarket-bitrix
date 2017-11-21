<?php
/**
 * User: Nikolay Mesherinov
 * Date: 17.11.2017
 * Time: 9:48
 */

namespace Fgsoft\Nmarket\Saver;

use Bitrix\Main\Diag\Debug;
use Fgsoft\Nmarket\ExternalId\ExternalId;
use Fgsoft\Nmarket\Node;
use \Bitrix\Main\IO;
use Fgsoft\Nmarket\Model\FilesTable;

class PictureSave implements Saver
{
    /**
     * @var Node\Node|Node\OfferNode
     */
    private $node;

    /**
     * Абсолютный путь
     * @var string
     */
    private $savePath;

    /**
     * @var ExternalId
     */
    private $externalId;

    private $fileId;

    private $imageUrl;

    public function __construct(Node\Node $node, ExternalId $externalId, $savePath = '')
    {
        if (empty($savePath)) {
            throw new \Exception('Не передан путь сохранения изображений');
        }

        $this->node = $node;
        $this->externalId = $externalId;
        $this->savePath = rtrim($savePath, '/') . '/files/' . $this->node->getInternalID();

        if (null == ($this->imageUrl = $this->node->getImage())) {
            return;
        }

        $this->fileId = self::getPidByUrl($this->imageUrl);

        $this->prepareIo();
    }

    public function save()
    {
        if ($this->isDouble()) {
            return false;
        }

        $file = \CFile::MakeFileArray($this->imageUrl);
        $extension = str_replace('image/', '', $file['type']);

        $filePath = $this->savePath . '/' . $this->fileId . '.' . $extension;

        if (!move_uploaded_file($file['tmp_name'], $filePath) && !copy($file['tmp_name'], $filePath)) {
            throw new \Exception('Невозможно сохранить файл! ' . $filePath);
        }

        FilesTable::add([
            'PID' => $this->fileId,
            'FILE_PATH' => $filePath,
            'ELEMENT_XML_ID' => $this->externalId->get()
        ]);
    }

    protected function prepareIo()
    {
        if (!IO\Directory::isDirectoryExists($this->savePath)) {
            IO\Directory::createDirectory($this->savePath);
        }
    }

    protected function isDouble()
    {
        if (
            ($fileParam = FilesTable::getList(['select' => ['ID', 'FILE_PATH'], 'filter' => ['=PID' => $this->fileId]])->fetch()) &&
            IO\File::isFileExists($fileParam['FILE_PATH'])
        ) {
            return true;
        }

        return false;
    }

    public static function getPidByUrl($imageUrl)
    {
        $queryImageUrl = parse_url($imageUrl, PHP_URL_QUERY);
        $output = '';
        parse_str(str_replace('&amp;', '&', $queryImageUrl), $output);
        return $output['pid'];
    }
}