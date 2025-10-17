<?php

	namespace App\Utilities\Blueprints;

	use App\Utilities\Config;
	use Aws\S3\S3Client;

	class BaseFileUpload
	{
		protected string $disk;
		protected ?S3Client $s3Client = null;
		protected string $bucket;
		protected string $basePath;
		protected static string $root = 'storage';

		public function __construct(string $disk)
		{
			$this->disk = $disk;
			$this->basePath = $this->resolveBasePath($disk);

			if ($this->isS3Disk()) {
				// Load AWS configuration (LocalStack or AWS)
				$_ENV['AWS_DEFAULT_REGION']     = Config::get('AWS_DEFAULT_REGION', 'us-east-1');
				$_ENV['AWS_ACCESS_KEY_ID']      = Config::get('AWS_ACCESS_KEY_ID', 'test');
				$_ENV['AWS_SECRET_ACCESS_KEY']  = Config::get('AWS_SECRET_ACCESS_KEY', 'test');
				$_ENV['AWS_BUCKET']             = Config::get('AWS_BUCKET', 'my-bucket');
				$_ENV['AWS_ENDPOINT']           = Config::get('AWS_ENDPOINT', null);

				$config = [
					'region'      => getenv('AWS_DEFAULT_REGION'),
					'version'     => 'latest',
					'credentials' => [
						'key'    => getenv('AWS_ACCESS_KEY_ID'),
						'secret' => getenv('AWS_SECRET_ACCESS_KEY'),
					],
				];

				// Use LocalStack endpoint if provided
				if (getenv('AWS_ENDPOINT')) {
					$config['endpoint'] = getenv('AWS_ENDPOINT');
					$config['use_path_style_endpoint'] = true; // required for LocalStack/MinIO
				}

				$this->s3Client = new S3Client($config);
				$this->bucket   = getenv('AWS_BUCKET');

				// Validate bucket exists (helpful for LocalStack)
				if (!$this->s3Client->doesBucketExist($this->bucket)) {
					throw new \RuntimeException("S3 bucket '{$this->bucket}' does not exist.");
				}
			}
		}

		protected function resolveBasePath(string $disk): string
		{
			$paths = [
				'local' => rtrim(self::$root, '/') . '/',
				's3'    => 's3://',
			];

			if ($disk === 'local') {
				if (!file_exists($paths['local'])) {
					mkdir($paths['local'], 0777, true);
				}
			}

			return $paths[$disk] ?? $paths['local'];
		}

		public function url(string $path): string
		{
			if ($this->isS3Disk()) {
				return $this->s3Client->getObjectUrl($this->bucket, $path);
			}

			return ltrim($path, '/');
		}

		public function temporaryUrl(string $path, \DateTimeInterface $expiration): string
		{
			if ($this->isS3Disk()) {
				$cmd = $this->s3Client->getCommand('GetObject', [
					'Bucket' => $this->bucket,
					'Key'    => $path,
				]);

				return (string)$this->s3Client->createPresignedRequest($cmd, $expiration)->getUri();
			}

			$timestamp = $expiration->getTimestamp();
			$signature = hash_hmac('sha256', $path . $timestamp, 'your-secret-key');

			return $this->url($path) . "?expires={$timestamp}&signature={$signature}";
		}

		public function put(string $path, string $contents): bool
		{
			if ($this->isS3Disk()) {
				$this->s3Client->putObject([
					'Bucket' => $this->bucket,
					'Key'    => $path,
					'Body'   => $contents,
				]);
				return true;
			}

			$fullPath = $this->basePath . ltrim($path, '/');
			return file_put_contents($fullPath, $contents) !== false;
		}

		public function get(string $path): ?string
		{
			if ($this->isS3Disk()) {
				$result = $this->s3Client->getObject([
					'Bucket' => $this->bucket,
					'Key'    => $path,
				]);
				return (string)$result['Body'];
			}

			$fullPath = $this->basePath . ltrim($path, '/');
			return file_exists($fullPath) ? file_get_contents($fullPath) : null;
		}

		public function exists(string $path): bool
		{
			if ($this->isS3Disk()) {
				return $this->s3Client->doesObjectExist($this->bucket, $path);
			}

			return file_exists($this->basePath . ltrim($path, '/'));
		}

		public function delete(string $path): bool
		{
			if ($this->isS3Disk()) {
				$this->s3Client->deleteObject([
					'Bucket' => $this->bucket,
					'Key'    => $path,
				]);
				return true;
			}

			$fullPath = $this->basePath . ltrim($path, '/');
			return file_exists($fullPath) && unlink($fullPath);
		}

		public function copy(string $from, string $to): bool
		{
			if ($this->isS3Disk()) {
				$this->s3Client->copyObject([
					'Bucket'     => $this->bucket,
					'Key'        => $to,
					'CopySource' => "{$this->bucket}/{$from}",
				]);
				return true;
			}

			$fullFromPath = $this->basePath . ltrim($from, '/');
			$fullToPath   = $this->basePath . ltrim($to, '/');
			return copy($fullFromPath, $fullToPath);
		}

		public function move(string $from, string $to): bool
		{
			if ($this->isS3Disk()) {
				$this->s3Client->copyObject([
					'Bucket'     => $this->bucket,
					'Key'        => $to,
					'CopySource' => "{$this->bucket}/{$from}",
				]);
				$this->delete($from);
				return true;
			}

			$fullFromPath = $this->basePath . ltrim($from, '/');
			$fullToPath   = $this->basePath . ltrim($to, '/');
			return rename($fullFromPath, $fullToPath);
		}

		public function size(string $path): int
		{
			if ($this->isS3Disk()) {
				$result = $this->s3Client->headObject([
					'Bucket' => $this->bucket,
					'Key'    => $path,
				]);
				return $result['ContentLength'];
			}

			$fullPath = $this->basePath . ltrim($path, '/');
			return file_exists($fullPath) ? filesize($fullPath) : 0;
		}

		public function lastModified(string $path): int
		{
			if ($this->isS3Disk()) {
				$result = $this->s3Client->headObject([
					'Bucket' => $this->bucket,
					'Key'    => $path,
				]);
				return strtotime($result['LastModified']);
			}

			$fullPath = $this->basePath . ltrim($path, '/');
			return file_exists($fullPath) ? filemtime($fullPath) : 0;
		}

		public function allFiles(string $directory = ''): array
		{
			if ($this->isS3Disk()) {
				$files = $this->s3Client->listObjectsV2([
					'Bucket'    => $this->bucket,
					'Prefix'    => $directory,
					'Delimiter' => '/',
				]);

				return array_map(fn($object) => $object['Key'], $files['Contents'] ?? []);
			}

			$fullDirectory = $this->basePath . ltrim($directory, '/');
			return glob($fullDirectory . '/*');
		}

		public function allDirectories(string $directory = ''): array
		{
			if ($this->isS3Disk()) {
				$objects = $this->s3Client->listObjectsV2([
					'Bucket'    => $this->bucket,
					'Prefix'    => $directory,
					'Delimiter' => '/',
				]);
				return array_map(fn($prefix) => $prefix['Prefix'], $objects['CommonPrefixes'] ?? []);
			}

			$fullDirectory = $this->basePath . ltrim($directory, '/');
			return glob($fullDirectory . '/*', GLOB_ONLYDIR);
		}

		public function makeDirectory(string $directory): bool
		{
			if ($this->isS3Disk()) {
				$this->put($directory . '/.empty', '');
				return true;
			}

			$fullPath = $this->basePath . ltrim($directory, '/');
			return mkdir($fullPath, 0777, true);
		}

		public function deleteDirectory(string $directory): bool
		{
			if ($this->isS3Disk()) {
				$objects = $this->s3Client->listObjectsV2([
					'Bucket' => $this->bucket,
					'Prefix' => $directory,
				]);

				foreach ($objects['Contents'] ?? [] as $object) {
					$this->delete($object['Key']);
				}
				return true;
			}

			$fullPath = $this->basePath . ltrim($directory, '/');
			return rmdir($fullPath);
		}

		protected function isS3Disk(): bool
		{
			return $this->disk === 's3' && class_exists('Aws\S3\S3Client');
		}

		public static function setStoragePath(string $path): void
		{
			self::$root = $path;

			if (!file_exists($path)) {
				mkdir($path, 0777, true);
			}
		}
	}