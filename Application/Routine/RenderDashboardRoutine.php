<?php

namespace Harryn\Jacobn\ExtensionFrameworkBundle\Application\Routine;

use Symfony\Contracts\EventDispatcher\Event;
use Harryn\Jacobn\CoreFrameworkBundle\Dashboard\DashboardTemplate;
use Harryn\Jacobn\ExtensionFrameworkBundle\Application\RoutineInterface;
use Harryn\Jacobn\ExtensionFrameworkBundle\Definition\ApplicationInterface;

class RenderDashboardRoutine extends Event implements RoutineInterface
{
    const NAME = 'uvdesk_extensions.application_routine.prepare_dashboard';

    private $template;
    private $templateData = [];
    private $dashboardTemplate;

    public function __construct(DashboardTemplate $dashboardTemplate)
    {
        $this->dashboardTemplate = $dashboardTemplate;
    }

    public static function getName() : string
    {
        return self::NAME;
    }

    public function getDashboardTemplate() : DashboardTemplate
    {
        return $this->dashboardTemplate;
    }

    public function setTemplateReference($template)
    {
        $this->template = $template;

        return $this;
    }

    public function getTemplateReference()
    {
        return $this->template;
    }

    public function addTemplateData($name, $value)
    {
        $this->templateData[$name] = $value;

        return $this;
    }

    public function getTemplateData()
    {
        return $this->templateData;
    }
}
