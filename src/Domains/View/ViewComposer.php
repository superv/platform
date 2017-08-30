<?php

namespace SuperV\Platform\Domains\View;

use Illuminate\View\View;

class ViewComposer
{
    /**
     * @var ViewTemplate
     */
    private $template;

    public function __construct(ViewTemplate $template)
    {
        $this->template = $template;
    }

    public function compose(View $view)
    {
        if (array_get($view->getData(), 'template')) {
            return;
        }

        if (!$this->template->isLoaded()) {
//            $this->events->fire(new RegisteringTwigPlugins($this->twig));
//            $this->events->fire(new TemplateDataIsLoading($this->template));

            $this->template->setLoaded(true);
        }

        if (array_merge($view->getFactory()->getShared(), $view->getData())) {
//            $view['template'] = (new Decorator())->decorate($this->template);
            $view['template'] = $this->template;
        }
    }
}
