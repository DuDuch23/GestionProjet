<?php

namespace App\Tests\Service;

use App\Entity\Project;
use App\Service\AttachmentStorage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class AttachmentStorageTest extends TestCase
{
    private string $storageDir;
    private Filesystem $filesystem;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $this->storageDir = sys_get_temp_dir().'/attachment_storage_test_'.bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->storageDir);
    }

    public function testStoreMovesFileAndBuildsAttachment(): void
    {
        // 1x1 transparent PNG
        $source = $this->filesystem->tempnam(sys_get_temp_dir(), 'upl');
        file_put_contents($source, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='));
        $upload = new UploadedFile($source, 'Logo Client.png', null, null, true);

        $project = (new Project())->setName('Demo');
        $storage = new AttachmentStorage($this->storageDir);

        $attachment = $storage->store($upload, $project);

        self::assertSame('Logo Client.png', $attachment->getOriginalName());
        self::assertSame('image/png', $attachment->getMimeType());
        self::assertTrue($attachment->isImage());
        self::assertGreaterThan(0, $attachment->getSize());
        self::assertMatchesRegularExpression('/^[a-f0-9]{32}\.png$/', $attachment->getFileName());
        self::assertSame($project, $attachment->getProject());
        self::assertTrue($project->getAttachments()->contains($attachment));

        self::assertFileExists($storage->getPath($attachment));
        self::assertFileDoesNotExist($source);

        $storage->delete($attachment);
        self::assertFileDoesNotExist($storage->getPath($attachment));
    }
}
