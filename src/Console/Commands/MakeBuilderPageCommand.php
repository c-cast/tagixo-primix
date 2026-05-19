<?php

namespace Ccast\TagixoPrimix\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'make:primix-builder-page')]
class MakeBuilderPageCommand extends GeneratorCommand
{
    protected $name = 'make:primix-builder-page';

    protected $description = 'Create a new Primix Visual Builder page';

    protected $type = 'Builder Page';

    protected function getStub(): string
    {
        return __DIR__ . '/stubs/builder-page.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\\Primix\\Pages';
    }

    protected function buildClass($name): string
    {
        $stub    = parent::buildClass($name);
        $context = $this->option('context') ?? 'page';
        $title   = $this->option('title') ?? Str::headline(class_basename($name));

        return str_replace(
            ['{{ context }}', '{{ title }}'],
            [$context, $title],
            $stub
        );
    }

    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the page'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['context', 'c', InputOption::VALUE_OPTIONAL, 'The builder context (page, form, mail, pdf)', 'page'],
            ['title',   't', InputOption::VALUE_OPTIONAL, 'The page title'],
            ['force',   'f', InputOption::VALUE_NONE,     'Create the class even if the page already exists'],
        ];
    }

    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'name' => [
                'What should the builder page be named?',
                'E.g. MyPageBuilder',
            ],
        ];
    }
}
