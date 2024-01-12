<?php

use Lernkarten\JsonApi\Routes;
use Lernkarten\JsonApi\Schemas;
use Lernkarten\StudIP\Datenschutz;
use JsonApi\Contracts\JsonApiPlugin;

require_once __DIR__ . '/vendor/autoload.php';

/**
 * @SuppressWarnings(StaticAccess)
 */
class LernkartenPlugin extends StudIPPlugin implements SystemPlugin, StandardPlugin, JsonApiPlugin
{
    use Routes;
    use Schemas;
    use Datenschutz;

    public function __construct()
    {
        parent::__construct();
        $this->addContentsNavigation();
    }

    /**
     * {@inheritdoc}
     */
    public function getTabNavigation($courseId)
    {
        return ['lernkarten' => $this->createNavigation($courseId)];
    }

    /**
     * {@inheritdoc}
     */
    public function getIconNavigation($courseId, $lastVisit, $userId)
    {
        $icon = new AutoNavigation(
            $this->_('Lernkarten'),
            PluginEngine::getURL($this, ['cid' => $courseId, 'iconnav' => 'true'], '', true)
        );
        $icon->setImage(Icon::create('dialog-cards', 'inactive', ['title' => 'Lernkarten']));

        return $icon;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $courseId
     * @return null|object
     */
    public function getInfoTemplate($courseId)
    {
        return null;
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(UnusedFormalParameter)
     * @param string $unconsumedPath
     */
    public function perform($unconsumedPath)
    {
        $cid = \Context::getId();
        if ($cid) {
            if (!$this->isActivated($cid) && $_SERVER['REQUEST_METHOD'] === 'GET') {
                throw new AccessDeniedException('This plugin not activated for this course.');
            }
            Navigation::activateItem('/course/lernkarten/index');
        } else {
            Navigation::activateItem('/contents/lernkarten/index');
        }

        PageLayout::setHelpKeyword('Lernkarten.Introduction');

        $pluginUrl = $this->getPluginURL();
        PageLayout::addStylesheet($pluginUrl . '/dist/style.css');
        PageLayout::addScript($pluginUrl . '/dist/lernkarten.js', ['type' => 'module']);

        $initialState = [
            'isTeacher' => Context::isCourse()
                ? $GLOBALS['perm']->have_studip_perm('tutor', Context::getId())
                : $GLOBALS['perm']->have_perm('tutor'),
        ];

        echo $GLOBALS['template_factory']->render('layouts/base', [
            'content_for_layout' => $this->bootstrapHtml($initialState),
        ]);
    }

    private function addContentsNavigation(): void
    {
        Navigation::addItem('/contents/lernkarten', $this->createNavigation());
    }

    /**
     * @param mixed $initialState
     */
    private function bootstrapHtml($initialState): string
    {
        ob_start(); ?>
        <div id="lernkarten-app"></div>
        <script>
            document.addEventListener(
                "DOMContentLoaded",
                function() {
                    if (window.STUDIP && window.STUDIP.mountLernkarten) {
                        STUDIP.domReady(() => {
                            const initialState = <?= json_encode($initialState) ?>;
                            const vm = window.STUDIP.mountLernkarten("#lernkarten-app", initialState);
                        })
                    }
                }
            )
        </script>
        <?php return ob_get_clean() ?: '';
    }

    private function createNavigation(string $cid = null): Navigation
    {
        $params = $cid ? ['cid' => $cid] : [];
        $navigation = new Navigation($this->_('Lernkarten'));
        $navigation->setDescription(
            // TODO
            $this->_('Eigene Kartensätze erstellen, teilen und in Kurse einbinden')
        );
        $navigation->setImage(Icon::create('dialog-cards', 'navigation'));
        $navigation->setURL(PluginEngine::getURL($this, $params, '', true));

        // subnavigation
        $navigation->addSubnavigation('index', clone $navigation);

        return $navigation;
    }
}
