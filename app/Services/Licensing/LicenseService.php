<?php

namespace App\Services\Licensing;

class LicenseService
{
    private string $licensePath;
    private string $publicKeyPath;

    public function __construct()
    {
        $this->licensePath = storage_path('app/license.lic');
        $this->publicKeyPath = app_path('Services/Licensing/public.pem');
    }

    public function getMachineId(): string
    {
        $components = [];

        // 1. System UUID
        $uuid = shell_exec('wmic csproduct get uuid 2>&1');
        if ($uuid && strpos($uuid, 'UUID') !== false) {
            $val = trim(str_replace(['UUID', "\r", "\n", ' '], '', $uuid));
            if ($val && !str_contains(strtolower($val), 'not available') && !str_contains(strtolower($val), 'tobefilled')) {
                $components[] = $val;
            }
        }

        // 2. Motherboard Serial
        $baseboard = shell_exec('wmic baseboard get serialnumber 2>&1');
        if ($baseboard && strpos($baseboard, 'SerialNumber') !== false) {
            $val = trim(str_replace(['SerialNumber', "\r", "\n", ' '], '', $baseboard));
            if ($val && !str_contains(strtolower($val), 'not available') && !str_contains(strtolower($val), 'tobefilled')) {
                $components[] = $val;
            }
        }

        // 3. Disk Serial (First drive)
        $disk = shell_exec('wmic diskdrive get serialnumber 2>&1');
        if ($disk && strpos($disk, 'SerialNumber') !== false) {
            $lines = array_filter(explode("\n", str_replace("\r", "", $disk)));
            foreach($lines as $line) {
                $val = trim($line);
                if ($val && $val !== 'SerialNumber' && !str_contains(strtolower($val), 'not available')) {
                    $components[] = $val;
                    break; 
                }
            }
        }

        if (empty($components)) {
            return 'FALLBACK-ID-' . getenv('COMPUTERNAME');
        }

        // Create a unique SHA256 hash of the combined components
        return strtoupper(hash('sha256', implode('|', $components)));
    }

    public function isActivated(): bool
    {
        if (!file_exists($this->licensePath)) {
            return false;
        }

        $content = file_get_contents($this->licensePath);
        if (empty($content)) {
            return false;
        }

        return $this->verifyLicense($content);
    }

    /**
     * Helper to verify raw license content (JSON)
     */
    public function verifyLicense(string $licenseContent): bool
    {
        $license = json_decode($licenseContent, true);
        if (!$license || empty($license['payload']) || empty($license['signature'])) {
            return false;
        }

        $payload = base64_decode($license['payload']);
        $signature = base64_decode($license['signature']);

        if (!file_exists($this->publicKeyPath)) {
            return false;
        }

        // Trim the key to avoid hidden whitespace issues
        $publicKey = trim(file_get_contents($this->publicKeyPath));

        // openssl_verify returns 1 for valid signature
        if (openssl_verify($payload, $signature, $publicKey, OPENSSL_ALGO_SHA256) !== 1) {
            return false;
        }

        $data = json_decode($payload, true);
        if (!$data || !isset($data['machine_id'])) {
            return false;
        }

        // Strict comparison of Machine ID
        if (trim($data['machine_id']) !== $this->getMachineId()) {
            return false;
        }

        // Expiry check
        if (!empty($data['expires_at']) && strtotime($data['expires_at']) < time()) {
            return false;
        }

        return true;
    }

    public function activateFromFile(string $licenseContent): bool
    {
        // Validate before saving
        if ($this->verifyLicense($licenseContent)) {
            file_put_contents($this->licensePath, $licenseContent);
            return true;
        }

        return false;
    }
}
