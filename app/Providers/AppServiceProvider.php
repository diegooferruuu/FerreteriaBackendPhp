<?php

namespace App\Providers;
use App\Models\Offline;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Schema::defaultStringLength(191);
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
//        Carbon::serializeUsing(function ($carbon) {
//            return $carbon->format('Y-m-d H:i:s');
//        });
        Relation::enforceMorphMap([
            'sucursal' => 'App\Models\Sucursal',
            'pos' => 'App\Models\PuntoVenta',
            'compras' => 'App\Models\Compra',
        ]);

//        Model::preventLazyLoading(true);
//        Builder::macro('whereLike', function($columns, $search) {
//            $this->where(function($query) use ($columns, $search) {
//                dd($columns,$search);
//                foreach(\Arr::wrap($columns) as $column) {
////                    dd( $column,$search);
//                    $query->orWhere($column, 'LIKE',strtolower("%{$search}%"));
//                }
//            });
//
//            return $this;
//        });

        Builder::macro('whereLike', function ($attributes, $searchTerm) {
            $this->where(function (Builder $query) use ($attributes, $searchTerm) {
                foreach (\Arr::wrap($attributes) as $attribute) {
                    $query->when(
                        str_contains($attribute, '.'),
                        function (Builder $query) use ($attribute, $searchTerm) {
                            [$relationName, $relationAttribute] = explode('.', $attribute);

                            $query->orWhereHas($relationName, function (Builder $query) use ($relationAttribute, $searchTerm) {
                                $query->whereRaw('LOWER('.$relationAttribute.') LIKE ?', ['%'.strtolower($searchTerm).'%']);
                            });
                        },
                        function (Builder $query) use ($attribute, $searchTerm) {
                            $query->orWhereRaw('LOWER('.$attribute.') LIKE ?', ['%'.strtolower($searchTerm).'%']);
                        }
                    );
                }
            });
            return $this;
        });
    }
}
