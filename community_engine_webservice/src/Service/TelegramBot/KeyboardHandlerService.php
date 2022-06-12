<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\TelegramBot;


use App\Service\TelegramBot\KeyboardHandler\KeyboardHandlerInterface;
use App\Service\TelegramBot\KeyboardHandler\ReadyToMatchKeyboardHandler;
use Psr\Container\ContainerInterface;
use TelegramBot\Api\Types\CallbackQuery;

/**
 * Class KeyboardHandlerService
 * @package App\Service\TelegramBot\KeyboardHandler
 */
class KeyboardHandlerService
{
    private $container;

    /**
     * KeyboardHandlerService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param null|CallbackQuery $callbackQuery
     * @return bool
     */
    public function handle(?CallbackQuery $callbackQuery): bool
    {
        if (!$callbackQuery) {
            return false;
        }
        $handler = $this->getHandler($callbackQuery);
        if ($handler) {
            $handler->handle($callbackQuery);
            return true;
        }

        return false;
    }

    /**
     * @param CallbackQuery $callbackQuery
     * @return KeyboardHandlerInterface|null
     */
    protected function getHandler(CallbackQuery $callbackQuery): ?KeyboardHandlerInterface
    {
        $name = $callbackQuery->getData();
        $handlers = $this->getHandlerClasses();
        if (isset($handlers[$name])) {
            return new $handlers[$name]($this->container);
        }
        return null;
    }

    /**
     * @return array
     */
    protected function getHandlerClasses(): array
    {
        return [
            ReadyToMatchKeyboardHandler::getCallbackName() => ReadyToMatchKeyboardHandler::class,
        ];
    }
}