<?php

declare(strict_types=1);

namespace ComposerDrupalLenient;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\PrePoolCreateEvent;

final class Plugin implements PluginInterface, EventSubscriberInterface
{
    private PackageRequiresAdjuster $packageRequiresAdjuster;
    private IOInterface $io;

    public function modifyPackages(PrePoolCreateEvent $event): void
    {
        $this->io->write('modifyPackages() begins.');
        $packages = $event->getPackages();
        foreach ($packages as $package) {
            if ($this->packageRequiresAdjuster->applies($package)) {
                $this->packageRequiresAdjuster->adjust($package);
                $this->io->write('modifyPackages() adjusted ' . $package->getName());
            }
        }
        $event->setPackages($packages);
        $this->io->write('modifyPackages() ends.');
    }

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->packageRequiresAdjuster = new PackageRequiresAdjuster($composer);
        $this->io = $io;
        $this->io->write('activate()');
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
        $io->write('deactivate()');
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
        $io->write('uninstall()');
    }

    public static function getSubscribedEvents(): array
    {
        $this->io->write('getSubscribedEvents()');
        return [
            PluginEvents::PRE_POOL_CREATE => 'modifyPackages',
        ];
    }
}
