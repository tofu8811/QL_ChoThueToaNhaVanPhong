<?php

namespace Tests\Unit;

use App\Helpers\StorageUploadHelper;
use Aws\S3\S3Client;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\RejectedPromise;
use Illuminate\Filesystem\AwsS3V3Adapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class StorageUploadHelperTest extends TestCase
{
    public function test_upload_batch_handles_urls_and_local_fallback_when_supabase_key_missing(): void
    {
        Storage::fake('local');
        config(['filesystems.disks.supabase.key' => null]);

        $file = UploadedFile::fake()->image('photo1.jpg');
        $results = StorageUploadHelper::uploadBatch([
            0 => [
                'file' => 'https://cdn.example.com/existing.jpg',
                'path' => 'https://cdn.example.com/existing.jpg',
                'fallback_dir' => 'public/tickets/t1',
                'filename' => 'existing.jpg',
            ],
            1 => [
                'file' => $file,
                'path' => 'tickets/t1/photo1.jpg',
                'fallback_dir' => 'public/tickets/t1',
                'filename' => 'photo1.jpg',
            ],
        ], 'supabase', 'public', 'public');

        $this->assertCount(2, $results);
        $this->assertEquals('https://cdn.example.com/existing.jpg', $results[0]['url']);
        $this->assertStringStartsWith('/storage/', $results[1]['url']);
        $this->assertEquals('photo1.jpg', $results[1]['original_name']);
    }

    public function test_upload_batch_with_fake_supabase_disks(): void
    {
        Storage::fake('supabase');
        Storage::fake('supabase_private');
        config(['filesystems.disks.supabase.key' => 'test-public-key']);
        config(['filesystems.disks.supabase_private.key' => 'test-private-key']);

        $img1 = UploadedFile::fake()->image('b1.jpg');
        $img2 = UploadedFile::fake()->image('b2.png');

        $publicResults = StorageUploadHelper::uploadBatch([
            0 => [
                'file' => $img1,
                'path' => 'buildings/id1/b1.jpg',
                'fallback_dir' => 'public/buildings/id1',
                'filename' => 'b1.jpg',
            ],
            1 => [
                'file' => $img2,
                'path' => 'buildings/id1/b2.png',
                'fallback_dir' => 'public/buildings/id1',
                'filename' => 'b2.png',
            ],
        ], 'supabase', 'public', 'public');

        $this->assertCount(2, $publicResults);
        Storage::disk('supabase')->assertExists('buildings/id1/b1.jpg');
        Storage::disk('supabase')->assertExists('buildings/id1/b2.png');

        $pdf = UploadedFile::fake()->create('contract.pdf', 120, 'application/pdf');
        $privateResults = StorageUploadHelper::uploadBatch([
            0 => [
                'file' => $pdf,
                'path' => 'contracts/c1/contract.pdf',
                'fallback_dir' => 'contracts/c1',
                'filename' => 'contract.pdf',
            ],
        ], 'supabase_private', 'local', 'private');

        $this->assertCount(1, $privateResults);
        $this->assertEquals('contracts/c1/contract.pdf', $privateResults[0]['path']);
        Storage::disk('supabase_private')->assertExists('contracts/c1/contract.pdf');
    }

    public function test_upload_batch_runs_s3_upload_async_and_falls_back_only_failed_items(): void
    {
        Storage::fake('local');
        config([
            'filesystems.disks.supabase.key' => 'real-key',
            'filesystems.disks.supabase.bucket' => 'building-assets',
        ]);

        $mockS3Client = Mockery::mock(S3Client::class);
        $mockS3Client->shouldReceive('uploadAsync')
            ->once()
            ->with(
                'building-assets',
                'buildings/b1/ok.jpg',
                Mockery::type('resource'),
                'public-read',
                Mockery::type('array')
            )
            ->andReturn(new FulfilledPromise(['ObjectURL' => 'https://supabase.test/buildings/b1/ok.jpg']));

        $mockS3Client->shouldReceive('uploadAsync')
            ->once()
            ->with(
                'building-assets',
                'buildings/b1/fail.jpg',
                Mockery::type('resource'),
                'public-read',
                Mockery::type('array')
            )
            ->andReturn(new RejectedPromise(new \RuntimeException('Simulated network timeout')));

        $mockDisk = Mockery::mock(AwsS3V3Adapter::class);
        $mockDisk->shouldReceive('getClient')->once()->andReturn($mockS3Client);
        $mockDisk->shouldReceive('url')
            ->once()
            ->with('buildings/b1/ok.jpg')
            ->andReturn('https://supabase.test/buildings/b1/ok.jpg');

        Storage::set('supabase', $mockDisk);

        $fileOk = UploadedFile::fake()->image('ok.jpg');
        $fileFail = UploadedFile::fake()->image('fail.jpg');

        $results = StorageUploadHelper::uploadBatch([
            0 => [
                'file' => $fileOk,
                'path' => 'buildings/b1/ok.jpg',
                'fallback_dir' => 'public/buildings/b1',
                'filename' => 'ok.jpg',
            ],
            1 => [
                'file' => $fileFail,
                'path' => 'buildings/b1/fail.jpg',
                'fallback_dir' => 'public/buildings/b1',
                'filename' => 'fail.jpg',
            ],
        ], 'supabase', 'public', 'public');

        $this->assertCount(2, $results);
        $this->assertEquals('https://supabase.test/buildings/b1/ok.jpg', $results[0]['url']);
        $this->assertStringStartsWith('/storage/', $results[1]['url']);
        Storage::disk('local')->assertExists('public/buildings/b1/fail.jpg');
    }
}
