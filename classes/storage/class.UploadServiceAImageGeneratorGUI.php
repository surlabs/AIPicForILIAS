<?php

declare(strict_types=1);
/**
 * Disclaimer: This file is part of the AImageGenerator Repository Object plugin for ILIAS.
 */


use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\Exception\IllegalStateException;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\ResourceStorage\Services as ResourceStorage;
use ILIAS\FileUpload\Handler\AbstractCtrlAwareUploadHandler;
use ILIAS\FileUpload\Handler\FileInfoResult;
use ILIAS\FileUpload\Handler\HandlerResult;
use ILIAS\FileUpload\Handler\BasicFileInfoResult;

/**
 * Class UploadServiceAImageGeneratorGUI
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 *
 * @ilCtrl_isCalledBy UploadServiceAImageGeneratorGUI : ilAImageGeneratorPluginGUI, ilObjPluginDispatchGUI, ilPCPluggedGUI
 * @ilCtrl_isCalledBy UploadServiceAImageGeneratorGUI : ilUIPluginRouterGUI, ilAImageGeneratorPluginGUI
 */
class UploadServiceAImageGeneratorGUI extends AbstractCtrlAwareUploadHandler
{
    private ResourceStorage $storage;
    private StorageStakeHolderAIGenerator $stakeholder;

    private ilLogger $logger;

    public function __construct()
    {
        global $DIC;

        parent::__construct();

        $this->storage = $DIC->resourceStorage();
        $this->stakeholder = new StorageStakeHolderAIGenerator();
        $this->logger = $DIC->logger()->root();
    }

    public function getFileIdentifierParameterName(): string
    {
        return "aig_file";
    }

    /**
     * @throws ilCtrlException
     */
    public function getUploadURL(): string
    {
        return $this->ctrl->getLinkTargetByClass(
            [ilUIPluginRouterGUI::class, self::class],
            self::CMD_UPLOAD,
            null,
            true
        );
    }

    /**
     * @throws ilCtrlException
     */
    public function getExistingFileInfoURL(): string
    {
        return $this->ctrl->getLinkTargetByClass(
            [ilUIPluginRouterGUI::class, self::class],
            self::CMD_INFO,
            null,
            true
        );
    }

    /**
     * @throws ilCtrlException
     */
    public function getFileRemovalURL(): string
    {
        return $this->ctrl->getLinkTargetByClass(
            [ilUIPluginRouterGUI::class, self::class],
            self::CMD_REMOVE,
            null,
            true
        );
    }

    /**
     * @throws IllegalStateException
     * @throws Exception
     */
    protected function getUploadResult(): HandlerResult
    {
        $this->upload->process();
        /**
         * @var $result UploadResult
         */
        $array = $this->upload->getResults();

        $result = end($array);
        if ($result instanceof UploadResult && $result->isOK()) {
            $i = $this->storage->manage()->upload($result, $this->stakeholder);
            $status = HandlerResult::STATUS_OK;
            $identifier = $i->serialize();
            $message = 'Upload ok';
        } else {
            $status = HandlerResult::STATUS_FAILED;
            $identifier = '';
            $message = $result->getStatus()->getMessage();
        }

        return new BasicHandlerResult($this->getFileIdentifierParameterName(), $status, $identifier, $message);
    }

    public function removeFromOutside(string $identifier): HandlerResult
    {
        return $this->getRemoveResult($identifier);
    }
    protected function getRemoveResult(string $identifier) : HandlerResult
    {
        if (null !== ($id = $this->storage->manage()->find($identifier))) {
            $this->storage->manage()->remove($id, $this->stakeholder);
            $status = HandlerResult::STATUS_OK;
            $message = "file removal OK";
            $this->logger->info($message);
        } else {
            $status = HandlerResult::STATUS_OK;
            $message = "file with identifier '$identifier' doesn't exist, nothing to do.";
            $this->logger->info($message);
        }

        return new BasicHandlerResult(
            $this->getFileIdentifierParameterName(),
            $status,
            $identifier,
            $message
        );
    }

    /** @noinspection DuplicatedCode */
    public function getInfoResult(string $identifier): ?FileInfoResult
    {
        $id = $this->storage->manage()->find($identifier);
        if ($id === null) {
            return new BasicFileInfoResult($this->getFileIdentifierParameterName(), 'unknown', 'unknown', 0, 'unknown');
        }
        $r = $this->storage->manage()->getCurrentRevision($id)->getInformation();

        return new BasicFileInfoResult(
            $this->getFileIdentifierParameterName(),
            $identifier,
            $r->getTitle(),
            $r->getSize(),
            $r->getMimeType()
        );
    }

    public function getInfoForExistingFiles(array $file_ids): array
    {
        $infos = [];
        foreach ($file_ids as $file_id) {
            $id = $this->storage->manage()->find($file_id);
            if ($id === null) {
                continue;
            }
            $r = $this->storage->manage()->getCurrentRevision($id)->getInformation();

            $infos[] = new BasicFileInfoResult($this->getFileIdentifierParameterName(), $file_id, $r->getTitle(), $r->getSize(), $r->getMimeType());
        }

        return $infos;
    }
}
