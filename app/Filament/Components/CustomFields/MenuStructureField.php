<?php

namespace App\Filament\Components\CustomFields;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Concerns\HasOptions;

class MenuStructureField extends Field
{
    use HasOptions;

    protected string $view = 'filament.components.menu-structure-field';

    protected array $descriptions = [];

    protected array $icons = [];

    public function descriptions(array $descriptions): static
    {
        $this->descriptions = $descriptions;
        return $this;
    }

    public function getDescriptions(): array
    {
        return $this->descriptions;
    }

    public function icons(array $icons): static
    {
        $this->icons = $icons;
        return $this;
    }

    public function getIcons(): array
    {
        return $this->icons;
    }

    public static function make(string $name): static
    {
        $static = app(static::class, ['name' => $name]);
        $static->configure();

        return $static;
    }
}