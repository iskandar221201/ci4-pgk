<?php

namespace App\Libraries;

use CodeIgniter\HTTP\Files\UploadedFile;

/**
 * Standardized file uploader for CI4 modules.
 *
 * Example:
 * $uploader = new FileUploader([
 *     'max_size' => 1024,
 *     'allowed_types' => ['jpg', 'png', 'webp'],
 * ]);
 *
 * $result = $uploader->upload($file, 'avatar');
 *
 * In a service layer, you can call upload() directly and handle any
 * RuntimeException from the caller.
 *
 * Example on update:
 * $uploader->delete($oldFilePath);
 * $newFile = $uploader->upload($newFile, 'avatar');
 */
class FileUploader
{
    /**
     * @var array<string, mixed>
     */
    protected array $config;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $defaults = [
            'max_size'      => 2048,
            'allowed_types' => ['jpg', 'jpeg', 'png', 'pdf', 'webp'],
            'base_path'     => WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR,
            'use_uuid'      => true,
        ];

        $this->config = array_replace($defaults, $config);

        if (! isset($this->config['allowed_types']) || ! is_array($this->config['allowed_types'])) {
            $this->config['allowed_types'] = $defaults['allowed_types'];
        }

        if (! isset($this->config['base_path']) || ! is_string($this->config['base_path'])) {
            $this->config['base_path'] = $defaults['base_path'];
        }

        $this->config['base_path'] = rtrim($this->config['base_path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    public function upload(UploadedFile $file, string $module): array
    {
        if (! $file->isValid() || $file->hasMoved()) {
            throw new \RuntimeException('The uploaded file is not valid or has already been moved.');
        }

        $maxSizeBytes = (int) $this->config['max_size'] * 1024;
        if ($file->getSize() > $maxSizeBytes) {
            throw new \RuntimeException('The uploaded file exceeds the maximum allowed size.');
        }

        $extension = strtolower((string) $file->getClientExtension());
        $allowedTypes = array_map('strtolower', (array) $this->config['allowed_types']);
        if (! in_array($extension, $allowedTypes, true)) {
            throw new \RuntimeException('The uploaded file extension is not allowed.');
        }

        $year = date('Y');
        $month = date('m');
        $relativeDir = $module . '/' . $year . '/' . $month . '/';
        $destination = rtrim($this->config['base_path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativeDir);

        if (! is_dir($destination) && ! @mkdir($destination, 0777, true) && ! is_dir($destination)) {
            throw new \RuntimeException('Failed to create destination directory: ' . $destination);
        }

        $filename = $this->config['use_uuid']
            ? $this->generateUuid() . '.' . $extension
            : $file->getName();

        $movedFile = $file->move($destination, $filename);
        if (! $movedFile instanceof \CodeIgniter\Files\File) {
            throw new \RuntimeException('Failed to move file to the destination directory.');
        }

        $relativePath = 'uploads/' . $relativeDir . $filename;

        return [
            'path'      => $relativePath,
            'full_path' => $this->config['base_path'] . str_replace('/', DIRECTORY_SEPARATOR, $relativeDir) . $filename,
            'original'  => $file->getClientOriginalName(),
            'size'      => $file->getSize(),
            'mime'      => $file->getMimeType(),
            'extension' => $extension,
        ];
    }

    public function delete(string $relativePath): bool
    {
        try {
            $normalizedRelativePath = ltrim($relativePath, '/\\');

            if (strpos($normalizedRelativePath, 'uploads/') === 0) {
                $normalizedRelativePath = substr($normalizedRelativePath, strlen('uploads/'));
            }

            $fullPath = rtrim($this->config['base_path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $normalizedRelativePath;

            if (! file_exists($fullPath)) {
                return true;
            }

            if (! @unlink($fullPath)) {
                log_message('error', 'Failed to delete uploaded file: ' . $fullPath);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            log_message('error', 'Failed to delete uploaded file: ' . $e->getMessage());
            return false;
        }
    }

    private function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
