<?php

namespace OCA\DashboardLauncher\Settings;

use OCP\Settings\IIconSection;
use OCP\IL10N;
use OCP\IURLGenerator;

class AdminSection implements IIconSection {

    public function __construct(
        private IL10N $l,
        private IURLGenerator $url
    ) {}

    /**
     * Unique identifier for the settings section.
     * Must match the return value of AdminSettings::getSection()
     */
    public function getID(): string {
        return 'dashboardlauncher';
    }

    /**
     * Label displayed in the admin menu sidebar
     */
    public function getName(): string {
        return $this->l->t('Dashboard Launcher');
    }

    /**
     * Priority order in the sidebar menu
     */
    public function getPriority(): int {
        return 50;
    }

    /**
     * Path to the section icon
     */
    public function getIcon(): string {
        return $this->url->imagePath('dashboardlauncher', 'app.svg');
    }
}