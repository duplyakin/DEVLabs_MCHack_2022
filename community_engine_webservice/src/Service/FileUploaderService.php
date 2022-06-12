<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Class FileUploaderService
 * @package App\Service
 */
class FileUploaderService
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var SluggerInterface
     */
    private $slugger;

    /**
     * FileUploaderService constructor.
     * @param SluggerInterface $slugger
     * @param ContainerInterface $container
     */
    public function __construct(SluggerInterface $slugger, ContainerInterface $container)
    {
        $this->slugger = $slugger;
        $this->container = $container;
    }

    public function uploadFromUrl(string $url)
    {
        $url = urldecode($url);
        $newFileName = tempnam(sys_get_temp_dir(), 'picture_');
        $newFileName .= '.' . pathinfo($url, PATHINFO_EXTENSION);
        $content = file_get_contents($url);
        file_put_contents($newFileName, $content);
        $file = new File($newFileName);
        return $this->move($file);
    }

    /**
     * @param File $file
     * @return string|null
     */
    public function move(File $file)
    {
        $originalFilename = pathinfo($file->getFilename(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            return null;
        }

        return $fileName;
    }

    /**
     * @param UserInterface $user
     */
    public function remove(UserInterface $user)
    {
        if ($user->getPicture()) {
            @unlink($this->getTargetDirectory() . DIRECTORY_SEPARATOR . $user->getPicture());
        }
    }

    /**
     * @return mixed
     */
    public function getTargetDirectory()
    {
        return $this->container->get('kernel')->getProjectDir() . '/public/upload';
    }
}