<?php

namespace SbS\AdminLTEBundle\Twig;

use SbS\AdminLTEBundle\Event\NavbarMenuEvent;
use SbS\AdminLTEBundle\Event\NotificationListEvent;
use SbS\AdminLTEBundle\Event\TaskListEvent;
use SbS\AdminLTEBundle\Event\ThemeEvents;
use SbS\AdminLTEBundle\Event\UserEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\TwigFunction;

/**
 * Class NavBarExtension.
 */
class NavBarExtension extends AdminLTE_Extension
{
    /**
     * @var string
     */
    private $publicDir;

    /**
     * NavBarExtension constructor.
     *
     * @param EventDispatcherInterface $dispatcher
     * @param string                   $publicDir
     */
    public function __construct(EventDispatcherInterface $dispatcher, $publicDir)
    {
        $this->publicDir = $publicDir;
        parent::__construct($dispatcher);
    }

    /**
     * @return array<TwigFunction>
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'navbar_menu',
                [$this, 'navbarMenu'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new TwigFunction(
                'navbar_notifications',
                [$this, 'showNotifications'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new TwigFunction(
                'navbar_tasks',
                [$this, 'showTasks'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new TwigFunction(
                'navbar_user_account',
                [$this, 'showUserAccount'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new TwigFunction(
                'user_avatar',
                [$this, 'showAvatar'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
        ];
    }

    /**
     * @param Environment $environment
     * @param Request     $request
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     *
     * @return string
     */
    public function navbarMenu(Environment $environment, Request $request)
    {
        if (false === $this->checkListener(ThemeEvents::NAVBAR_MENU)) {
            return '';
        }

        /** @var NavbarMenuEvent $menuEvent */
        $menuEvent = $this->getDispatcher()->dispatch(ThemeEvents::NAVBAR_MENU, new NavbarMenuEvent($request));

        return $environment->render('@SbSAdminLTE/NavBar/menu.html.twig', ['menu' => $menuEvent->getItems()]);
    }

    /**
     * @param Environment $environment
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     *
     * @return string
     */
    public function showNotifications(Environment $environment)
    {
        if (false === $this->checkListener(ThemeEvents::NOTICES)) {
            return '';
        }

        /** @var NotificationListEvent $noticesEvent */
        $noticesEvent = $this->getDispatcher()->dispatch(ThemeEvents::NOTICES, new NotificationListEvent());

        return $environment->render('@SbSAdminLTE/NavBar/notifications.html.twig', [
            'notifications' => $noticesEvent->getNotifications(),
            'total'         => $noticesEvent->getTotal(),
        ]);
    }

    /**
     * @param Environment $environment
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     *
     * @return string
     */
    public function showTasks(Environment $environment)
    {
        if (false === $this->checkListener(ThemeEvents::TASKS)) {
            return '';
        }

        /** @var TaskListEvent $tasksEvent */
        $tasksEvent = $this->getDispatcher()->dispatch(ThemeEvents::TASKS, new TaskListEvent());

        return $environment->render('@SbSAdminLTE/NavBar/tasks.html.twig', [
            'tasks' => $tasksEvent->getTasks(),
            'total' => $tasksEvent->getTotal(),
        ]);
    }

    /**
     * @param Environment $environment
     *
     * @throws LoaderError
     * @throws SyntaxError
     * @throws RuntimeError
     *
     * @return string
     */
    public function showUserAccount(Environment $environment)
    {
        if (false === $this->checkListener(ThemeEvents::USER)) {
            return '';
        }

        /** @var UserEvent $userEvent */
        $userEvent = $this->getDispatcher()->dispatch(ThemeEvents::USER, new UserEvent());

        return $environment->render('@SbSAdminLTE/NavBar/user.html.twig', ['user' => $userEvent->getUser()]);
    }

    /**
     * @param Environment $environment
     * @param string      $image
     * @param string      $alt
     * @param string      $class
     *
     * @throws LoaderError
     * @throws SyntaxError
     *
     * @return string
     */
    public function showAvatar(Environment $environment, $image, $alt = '', $class = 'img-circle elevation-2')
    {
        if (!$image || !\file_exists($image)) {
            $image = $this->publicDir . '/bundles/sbsadminlte/img/avatar.png';
        }

        if ($image = \file_get_contents($image)) {
            $image = 'data:image/png;base64,' . \base64_encode($image);
        }

        return $environment
            ->createTemplate('<img src="{{ image }}" class="{{ class }}" alt="{{ alt }}"/>')
            ->render([
                'image' => $image,
                'class' => $class,
                'alt'   => $alt,
            ]);
    }
}
