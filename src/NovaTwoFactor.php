<?php

namespace Elbytes\NovaTwoFactor;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Menu\Menu;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class NovaTwoFactor extends Tool
{
    /**
     * Perform any tasks that need to happen when the tool is booted.
     *
     * @return void
     */
    public function boot()
    {
        Nova::script('nova-two-factor', __DIR__.'/../dist/js/tool.js');
        Nova::style('nova-two-factor', __DIR__.'/../dist/css/tool.css');

        Nova::userMenu(function (Request $request, Menu $menu) {
            $menu->prepend(
                MenuSection::make('Zwei-Faktor Authentifizierung')
                           ->path('/nova-two-factor')
                           ->icon('lock-closed')
            );

            return $menu;
        });

        $user = Auth::user();
        if (! $user->is2faRnabled() && $user->hasRole('Admin') && config('nova-two-factor.enabled')) {
            Nova::mainMenu(function (Request $request) {
                return [];
            });
        }
    }

    /**
     * Build the view that renders the navigation links for the tool.
     *
     * @return \Illuminate\View\View
     */
    public function menu(Request $request)
    {
        return '';
    }
}
