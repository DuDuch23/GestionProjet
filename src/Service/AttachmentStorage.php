<?php

namespace App\Service;

use App\Entity\Attachment;
use App\Entity\Project;
use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Stores project files outside public/ so access can be checked by a controller
 * before streaming them (BinaryFileResponse).
 */
final readonly class AttachmentStorage
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/var/uploads/attachments')]
        private string $storageDir,
        private Filesystem $filesystem = new Filesystem(),
    ) {
    }

    /**
     * Moves the uploaded file to storage and returns the (not yet persisted) Attachment.
     */
    public function store(UploadedFile $file, Project $project, ?User $uploadedBy = null): Attachment
    {
        // Read metadata before moving: the temp file disappears afterwards.
        $mimeType = $file->getMimeType() ?? 'application/octet-stream';
        $size = (int) $file->getSize();
        $extension = $file->guessExtension() ?? 'bin';
        $fileName = bin2hex(random_bytes(16)).'.'.$extension;

        $file->move($this->storageDir, $fileName);

        $attachment = (new Attachment())
            ->setOriginalName($file->getClientOriginalName())
            ->setFileName($fileName)
            ->setMimeType($mimeType)
            ->setSize($size)
            ->setUploadedBy($uploadedBy);

        $project->addAttachment($attachment);

        return $attachment;
    }

    public function getPath(Attachment $attachment): string
    {
        return $this->storageDir.'/'.$attachment->getFileName();
    }

    public function delete(Attachment $attachment): void
    {
        $this->filesystem->remove($this->getPath($attachment));
    }
}
