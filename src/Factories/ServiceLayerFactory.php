<?php

namespace Hageman\Wics\ServiceLayer\Factories;

use Faker\Generator;
use Hageman\Wics\ServiceLayer\Collections\ModelCollection;
use Hageman\Wics\ServiceLayer\Exceptions\MissingFactoryException;
use Hageman\Wics\ServiceLayer\Models\ServiceLayerModel;
use Hageman\Wics\ServiceLayer\ServiceLayer;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Str;

abstract class ServiceLayerFactory
{
    /**
     * The factory name resolver.
     *
     * @var callable
     */
    protected static $factoryNameResolver;
    
    /**
     * The class of the factory's corresponding model.
     *
     * @var class-string<ServiceLayerModel>
     */
    protected static string $model;
    
    /**
     * The current Faker instance.
     *
     * @var Generator
     */
    protected Generator $faker;

    /**
     * Create a new factory instance.
     *
     * @param int|null $count
     *
     * @return void
     *
     * @throws BindingResolutionException
     */
    public function __construct(
        protected int|null $count = null,
    )
    {
        $this->faker = $this->withFaker();
    }

    /**
     * Get a new Faker instance.
     *
     * @return Generator
     * @throws BindingResolutionException
     */
    protected function withFaker(): Generator
    {
        return Container::getInstance()->make(Generator::class);
    }

    /**
     * Create a single model or a collection of models, based on count.
     *
     * @param array $attributes
     *
     * @return ServiceLayerModel|ModelCollection|mixed
     */
    public function make(array $attributes = []): mixed
    {
        if ($this->count === null || $this->count < 1) {
            return new $this::$model($this->definition(...$attributes));
        }

        return ModelCollection::make(array_map(function () use ($attributes) {
            return new $this::$model($this->definition(...$attributes));
        }, range(1, $this->count)));
    }

    /**
     * Define the model.
     *
     * @param ...$attributes
     *
     * @return array<string, mixed>
     */
    abstract public function definition(...$attributes): array;

    /**
     * Get a new factory instance for the given model name.
     *
     * @param class-string<ServiceLayerModel> $modelName
     *
     * @return ServiceLayerFactory
     */
    public static function factoryForModel(string $modelName): ServiceLayerFactory
    {
        $factory = static::resolveFactoryName($modelName);

        return call_user_func([$factory, 'new']);
    }

    /**
     * Get the factory name for the given model name.
     *
     * @param class-string<ServiceLayerModel> $modelName
     *
     * @return class-string<ServiceLayerFactory>
     */
    public static function resolveFactoryName(string $modelName): string
    {
        $resolver = static::$factoryNameResolver ?? function (string $modelName) {
                $modelName = Str::startsWith($modelName, ServiceLayer::$namespace . 'Models\\')
                    ? Str::after($modelName, ServiceLayer::$namespace . 'Models\\')
                    : Str::after($modelName, ServiceLayer::$namespace);

                $factory = __NAMESPACE__ . "\\{$modelName}Factory";

                if (!class_exists($factory)) throw new MissingFactoryException("Could not auto-resolve Factory: '$factory' does not exist!");

                return $factory;
            };

        return $resolver($modelName);
    }

    /**
     * Get a new factory instance.
     *
     * @return static
     */
    public static function new(): static
    {
        return (new static);
    }

    /**
     * Get a new factory instance for the given number of models.
     *
     * @param int $count
     *
     * @return static
     */
    public static function times(int $count): static
    {
        return static::new()->count($count);
    }

    /**
     * Specify how many models should be generated.
     *
     * @param int|null $count
     *
     * @return static
     */
    public function count(int|null $count): static
    {
        $this->count = $count;

        return $this;
    }
}