<?php

declare(strict_types=1);

namespace App\Service;

use Composer\Semver\Comparator;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class LatestReleaseChecker
{
    const REQUIRED_PHP_VERSION_PER_RELEASE = [
        '1.0' => '7.2',
        '1.1' => '7.4',
        '1.2' => '8.0'
    ];

    /**
     * @var HttpClientInterface
     */
    private HttpClientInterface $client;

    /**
     * @var array|null
     */
    private ?array $latestReleaseData = null;

    /**
     * @var string|null
     */
    private ?string $koillectionRelease = null;

    public function __construct(HttpClientInterface $client, string $koillectionRelease)
    {
        $this->client = $client;
        $this->koillectionRelease = $koillectionRelease;
    }

    public function getLatestReleaseData() : ?array
    {
        if (!empty($this->latestReleaseData)) {
            return $this->latestReleaseData;
        }

        try {
            $response = $this->client->request('GET',
                'https://api.github.com/repos/koillection/koillection/tags',
                ['timeout' => 2.5]
            );
            if ($response->getStatusCode() !== 200) {
                throw new \Exception();
            }
            $content = json_decode($response->getContent(), true);
            $this->latestReleaseData = $content[0];
        } catch (\Exception $e) {

        }

        return $this->latestReleaseData;
    }

    public function getCurrentRelease() : ?string
    {
        return $this->koillectionRelease;
    }

    public function getLatestRelease() : ?string
    {
        $latestReleaseData = $this->getLatestReleaseData();
        if (empty($latestReleaseData)) {
            return null;
        }

        return $latestReleaseData['name'];
    }

    public function getRequiredPhpVersionForLatestRelease() : ?string
    {
        $latestRelease = $this->getLatestRelease();

        if ($latestRelease === null) {
            return null;
        }

        preg_match('/\d+.\d+/', $latestRelease, $output);
        $latestMinor = $output[0];

        foreach (self::REQUIRED_PHP_VERSION_PER_RELEASE as $koillectionRelease => $phpVersion) {
            if (Comparator::equalTo($koillectionRelease, $latestMinor)) {
                return $phpVersion;
            }
        }

        return null;
    }

    public function isRequiredPhpVersionForLatestReleaseOk()
    {
        return Comparator::greaterThanOrEqualTo(phpversion(), $this->getRequiredPhpVersionForLatestRelease());
    }
}
