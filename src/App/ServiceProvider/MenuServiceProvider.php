<?php

namespace App\ServiceProvider;

use Knp\Menu\MenuFactory;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class MenuServiceProvider implements ServiceProviderInterface {

    public function register(Container $app) {
        $app['admin_menu'] = function($app) {
            /** @var MenuFactory $factory */
            $factory = $app['knp_menu.factory'];

            $menu = $factory->createItem('Admin menu')
                ->setChildrenAttributes([
                    'class' => 'nav navbar-nav'
                ]);
            $menu->addChild('Dashboard', [
                'route' => 'dashboard'
            ])
                ->setAttribute('icon', 'fa fa-dashboard');
            $menu->addChild('Users', [
                'route' => 'users'
            ])
                ->setAttribute('icon', 'fa fa-users');
            $menu->addChild('Service providers', [
                'route' => 'service_providers'
            ])
                ->setAttribute('icon', 'fa fa-files-o');
            /*$menu->addChild('Settings', [
                'route' => 'admin_settings'
            ])
                ->setAttribute('icon', 'fa fa-cogs');*/

            return $menu;
        };

        $app['user_menu'] = function($app) {
            /** @var TokenInterface $token */
            $token = $app['security.token_storage']->getToken();

            /** @var UserInterface $user */
            $user = $token->getUser();

            /** @var MenuFactory $factory */
            $factory = $app['knp_menu.factory'];

            $menu = $factory->createItem('User menu')
                ->setChildrenAttributes([
                    'class' => 'nav navbar-nav navbar-right'
                ]);
            $menu->addChild($user->getUsername(), [
                'route' => 'profile'
            ])
                ->setAttribute('icon', 'fa fa-user');
            $menu->addChild('Logout', [
                'route' => 'logout'
            ])
                ->setAttribute('icon', 'fa sign-out');

            return $menu;
        };

        $app['knp_menu.menus'] = [
            'admin_menu' => 'admin_menu',
            'user_menu' => 'user_menu'
        ];
    }
}