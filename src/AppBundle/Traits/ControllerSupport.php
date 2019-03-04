<?php
declare(strict_types=1);

namespace AppBundle\Traits;

trait ControllerSupport
{
    protected $doctrine = false;

    /**
     * Unifies Json Array for every api method
     *
     * @param bool $success
     * @param string $message
     * @param array|null $data
     * @return array
     */
    public function prepareJsonArr(bool $success, string $message, array $data = null): array
    {
        $result = array('success' => $success, 'message' => $message);

        if (!is_null($data)) {
            $result = array_merge($result, $data);
        }

        return $result;
    }

    /**
     * Get repository
     *
     * @param string $repo
     * @param string $bundle
     * @return mixed
     */
    protected function getRepo(string $repo, string $bundle = 'AppBundle')
    {
        if (!$this->doctrine) {
            $this->doctrine = $this->getDoctrine();
        }

        return $this->doctrine->getRepository($bundle . ':' . $repo);
    }
}